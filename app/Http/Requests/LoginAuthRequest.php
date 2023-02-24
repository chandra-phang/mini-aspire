<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAuthRequest
{
    public function validate(Request $request)
    {
        // Validate request body
        if (!Auth::attempt($request->only('email', 'password'))) {
            return [false, 'Invalid login details'];
        } else {
            return [true, null];
        }
    }
}
