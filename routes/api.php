<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');
Route::post('register', 'Auth\RegisterController@create');

Route::group(['middleware' => 'jwt.auth'], function () {

    // transfers
    Route::post('transfer/send', 'TransactionController@sendMoneyToNewCustomer');
    Route::post('transfer/sendtorecipient', 'TransactionController@sendMoneyToRecipient');
    Route::post('transfer/finalize', 'TransactionController@finalizeTransferWithOTP');
    Route::post('transfer/list', 'TransactionController@fetchAllTransfers');
    Route::get('transfer/verify/{reference}', 'TransactionController@verifyTransfer');

    // recipients
    Route::get('recipients/list', 'RecipientController@fetchAllRecipients');
});

// banks
Route::get('banks/list', 'BankController@fetchAllBanks');
