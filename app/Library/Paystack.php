<?php


namespace App\Library;

use App\Traits\GuzzleRequestTrait;
use GuzzleHttp\Exception\RequestException;

class Paystack
{
    use GuzzleRequestTrait;

    private $header;
    private $base_url;

    public function __construct()
    {
        $this->base_url = config('services.paystack.api_url');
        $this->header = [
            'Authorization' => 'Bearer '.config('services.paystack.secret_key'),
        ];
    }

    public function guzzleWrapper($method, $endpoint, $body = [])
    {
        try {
            $api_url = $this->base_url.$endpoint;

            $response = $this->makeHttpRequest($method, $api_url, $this->header, $body, 'json');
            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {

            if (in_array($e->getResponse()->getStatusCode(), [400, 401, 404], true)) {
                return ['status' => false, 'msg' => json_decode($e->getResponse()->getBody())->message, 'response' => []];
            }
            if (in_array($e->getResponse()->getStatusCode(), [500, 501, 502, 503, 504], true)) {
                return ['status' => false, 'msg' => '500+ Error. Contact Third Party.', 'response' => []];
            }
        }
    }

    public function resolveAccountNo($acct_no, $bank_code)
    {
        $endpoint = 'bank/resolve?account_number=' . $acct_no . '&bank_code=' . $bank_code;

        $response = $this->guzzleWrapper('GET', $endpoint);

        if(!$response['status']) {
            $status = false;
            return ['status' => $status, 'response' => $response];
        }
        $status = true;
        return ['status' => $status, 'response' => $response];
    }

    public function createTransferRecipient($name, $acct_no, $bank_code)
    {
        $endpoint = 'transferrecipient';

        $params = [
            'type' => "nuban",
            'name' => $name,
            'account_number' => $acct_no,
            'bank_code' => $bank_code,
            'currency' => "NGN"
        ];

        $response = $this->guzzleWrapper('POST', $endpoint, $params);

        if(!$response['status']) {
            $status = false;
            return ['status' => $status, 'response' => $response];
        }
        $status = true;
        return ['status' => $status, 'response' => $response];
    }

    public function transferToRecipient($amount, $recipient, $reason)
    {
        $endpoint = 'transfer';

        $params = [
            'source' => "balance",
            'amount' => $amount,
            'recipient' => $recipient,
            'reason' => $reason
        ];

        $response = $this->guzzleWrapper('POST', $endpoint, $params);

        if(!$response['status']) {
            $status = false;
            return ['status' => $status, 'response' => $response];
        }
        $status = true;
        return ['status' => $status, 'response' => $response];
    }

    public function finalizeTransfer($transfer_code, $otp)
    {
        $endpoint = 'transfer/finalize_transfer';

        $params = [
            'transfer_code' => $transfer_code,
            'otp' => $otp
        ];

        $response = $this->guzzleWrapper('POST', $endpoint, $params);

        if(!$response['status']) {
            $status = false;
            return ['status' => $status, 'response' => $response];
        }
        $status = true;
        return ['status' => $status, 'response' => $response];
    }

    public function verifyTransfer($reference)
    {
        $endpoint = 'transfer/verify/'. $reference;

        $response = $this->guzzleWrapper('GET', $endpoint);

        if(!$response['status']) {
            $status = false;
            return ['status' => $status, 'response' => $response];
        }
        $status = true;
        return ['status' => $status, 'response' => $response];
    }

}
