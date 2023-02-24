<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterAuthRequest
{
    public function validate(Request $request)
    {
        // Validate request body
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:8',
        ]);

        // Return errors if validation error occur.
        if ($validator->fails()) {
            return [false, $validator->errors()];
        } else {
            return [true, null];
        }
    }
}
