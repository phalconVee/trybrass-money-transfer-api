<?php


namespace App\Http\Controllers;


use App\Library\Master;
use App\Model\Recipient;
use App\Model\Transaction;
use Illuminate\Support\Facades\Auth;

class RecipientController extends Controller
{
    /**
     * Fetch Recipients linked to Authorised User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllRecipients()
    {
        $user = Auth::user()->id;

        $recipients = Recipient::userId($user)->with('recipient_account')->get();

        return Master::successResponse('Transfers Fetched', $recipients);
    }
}
