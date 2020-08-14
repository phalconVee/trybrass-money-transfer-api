<?php


namespace App\Http\Controllers;

use App\Library\Master;
use App\Library\Paystack;
use App\Model\Recipient;
use App\Model\RecipientAccount;
use App\Model\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $paystack;

    public function __construct()
    {
        $this->paystack = new Paystack();
    }

    /**
     * Send Money to New Account
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function sendMoneyToNewCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_code' => 'bail|required|numeric',
            'account_no' => 'bail|required|numeric',
            'amount' => 'bail|required|numeric',
            'reason' => 'bail|string',
        ]);

        if ($validator->fails()) {
            return Master::failureResponse('Validation Error', $validator->errors()->all(), 422);
        }

        $user_id = Auth::user()->id;

        $bank_code = $request->input('bank_code');
        $acct_no = $request->input('account_no');
        $amount = $request->input('amount')*100; //for kobo NGN transaction * 100
        $reason = $request->input('reason');

        DB::beginTransaction();
        try {

            // resolve account number
            $resolve = $this->paystack->resolveAccountNo($acct_no, $bank_code);

            if(!$resolve['status']) {
                return Master::failureResponse('Unable to Resolve Account Number', $resolve, 404);
            }

            $recipient_name = $resolve['response']['data']['account_name'];  // hold recipient name

            // create transfer recipient
            $pingRecipient = $this->paystack->createTransferRecipient($recipient_name, $acct_no, $bank_code);

            if(!$pingRecipient['status']) {
                return Master::failureResponse('Failed to Create Recipient', $pingRecipient, 403);
            }

            // store transfer recipient to db
            $recipient = new Recipient();
            $recipient->user_id = $user_id;
            $recipient->recipient_name = $pingRecipient['response']['data']['name'];
            $recipient->recipient_code = $pingRecipient['response']['data']['recipient_code'];
            $recipient->save();

            // store recipient account to db
            RecipientAccount::create([
                'recipient_id' => $recipient->id,
                'authorization_code' => $pingRecipient['response']['data']['details']['authorization_code'],
                'account_number' => $pingRecipient['response']['data']['details']['account_number'],
                'bank_code' => $pingRecipient['response']['data']['details']['bank_code'],
                'bank_name' => $pingRecipient['response']['data']['details']['bank_name']
            ]);

            // initiate transfer to recipient
            $transfer = $this->paystack->transferToRecipient($amount, $recipient->recipient_code, $reason);

            if(!$transfer['status']) {
                return Master::failureResponse('Failed to Initiate Transfer', $transfer, 403);
            }

            // store transactions to db
            Transaction::create([
                'user_id' => $user_id,
                'amount' => $amount,
                'transfer_code' => $transfer['response']['data']['transfer_code'],
                'reference' => $transfer['response']['data']['reference'],
                'trans_ref_id' => $transfer['response']['data']['id'],
                'status' => $transfer['response']['data']['status']
            ]);

            $res = [
                'status' => $transfer['response']['data']['status'],
                'transfer_code' => $transfer['response']['data']['transfer_code'],
                'reference' => $transfer['response']['data']['reference'],
                'amount' => $transfer['response']['data']['amount'],
                'created_at' => $transfer['response']['data']['createdAt']
            ];

            DB::commit();
            return Master::successResponse('Money Transfer Successful', $res);
        }
        catch (\Exception $e) {
            DB::rollback();
            if (Master::hasDebug())
                return Master::exceptionResponse($e, 'Money Transfer Error');
        }

        return Master::failureResponse();
    }

    /**
     * Transfer to Recipient/Beneficiary
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function sendMoneyToRecipient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_code' => 'bail|required|string',
            'amount' => 'bail|required|numeric',
            'reason' => 'bail|string',
        ]);

        if ($validator->fails()) {
            return Master::failureResponse('Validation Error', $validator->errors()->all(), 422);
        }

        $amount = $request->amount*100;

        DB::beginTransaction();
        try {

            // check if recipient code is valid
            $recipient = Recipient::recipientCode($request->recipient_code)->first();

            if(!$recipient) {
                return Master::failureResponse('Invalid Recipient Code', ['recipient does not exist'], 404);
            }

            // initiate transfer to recipient
            $transfer = $this->paystack->transferToRecipient($amount, $recipient->recipient_code, $request->reason);

            if(!$transfer['status']) {
                return Master::failureResponse('Failed to Initiate Transfer', $transfer, 403);
            }

            // store transactions to db
            Transaction::create([
                'user_id' => Auth::user()->id,
                'amount' => $amount,
                'transfer_code' => $transfer['response']['data']['transfer_code'],
                'reference' => $transfer['response']['data']['reference'],
                'trans_ref_id' => $transfer['response']['data']['id'],
                'status' => $transfer['response']['data']['status']
            ]);

            $resp= [
                'status' => $transfer['response']['data']['status'],
                'transfer_code' => $transfer['response']['data']['transfer_code'],
                'reference' => $transfer['response']['data']['reference'],
                'amount' => $transfer['response']['data']['amount'],
                'created_at' => $transfer['response']['data']['createdAt']
            ];

            DB::commit();
            return Master::successResponse('Money Transfer to Recipient Successful', $resp);
        }
        catch (\Exception $e) {
            DB::rollback();
            if (Master::hasDebug())
                return Master::exceptionResponse($e, 'Money Transfer to Recipient Error');
        }

        return Master::failureResponse();
    }

    /**
     * Finalize Transfer After
     * Validating OTP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function finalizeTransferWithOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transfer_code' => 'bail|required|string',
            'otp' => 'bail|required|numeric'
        ]);

        if ($validator->fails()) {
            return Master::failureResponse('Validation Error', $validator->errors()->all(), 422);
        }

        $transfer_code = $request->input('transfer_code');
        $otp = $request->input('otp');

        DB::beginTransaction();
        try {

            // finalize transfer
            $finalize = $this->paystack->finalizeTransfer($transfer_code, $otp);

            if(!$finalize['status']) {
                return Master::failureResponse('OTP Validation Error', $finalize, 422);
            }

            // update transfer transaction db if status==success
            if($finalize['response']['data']['status']==='success') {

                $reference = $finalize['response']['data']['reference'];
                $status = $finalize['response']['data']['status'];

                $transaction = Transaction::reference($reference)
                    ->update([
                       'status' => $status
                    ]);

                return Master::successResponse('Transfer Finalized Successfully', $transaction);
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            if (Master::hasDebug())
                return Master::exceptionResponse($e, 'OTP Validation Error');
        }

        return Master::failureResponse();
    }

    /**
     * Verify Transfer|Check Status
     *
     * @param $reference
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function verifyTransfer($reference)
    {
        try {
            // fetch transfer status
            $checkStatus = $this->paystack->verifyTransfer($reference);

            if (!$checkStatus['status']) {
                return Master::failureResponse('Unable to Verify Transfer', $checkStatus, 422);
            }

            $res = [
                'status' => $checkStatus['response']['data']['status'],
                'name' => $checkStatus['response']['data']['recipient']['name'],
                'details' => $checkStatus['response']['data']['recipient']['details']
            ];

            return Master::successResponse('Transfer Verification', $res);
        }
        catch (\Exception $e) {
            if (Master::hasDebug())
                return Master::exceptionResponse($e, 'Transfer Verification Error');
        }

        return Master::failureResponse();
    }

    /**
     * Fetch All Transfers linked to Authorised User
     * Filter Option Available
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllTransfers(Request $request)
    {
        $user = Auth::user()->id;

        $transfers = Transaction::userId($user)
            ->transferCode($request->transfer_code)
            ->reference($request->reference)
            ->status($request->status)
            ->fromDate($request->from)
            ->toDate($request->to)
            ->orderBy('created_at', 'desc')
            ->get();

        return Master::successResponse('Transfers Fetched', $transfers);
    }
}
