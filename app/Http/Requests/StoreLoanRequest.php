<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreLoanRequest
{
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:10',
        ]);
        // Return errors if validation error occur.
        if ($validator->fails()) {
            return [false, $validator->errors()];
        } else {
            return [true, null];
        }
    }
}
