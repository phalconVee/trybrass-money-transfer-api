<?php


namespace App\Http\Controllers;


use App\Library\Master;
use App\Model\Bank;
use App\Model\Recipient;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function fetchAllBanks()
    {
        $banks = Bank::orderBy('name', 'asc')->get();

        return Master::successResponse('Banks Fetched', $banks);
    }
}
