<?php

namespace App\Http\Controllers\Auth;

use App\Library\Master;
use App\Model\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Create User Account
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|max:200|string',
            'email' => 'bail|required|email|max:128|unique:users',
            'password' => 'bail|required|min:4'
        ]);

        if ($validator->fails()) {
            return Master::failureResponse('Validation Error', $validator->errors()->all(), 422);
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();

        return Master::successResponse('User account created', $user);
    }
}
