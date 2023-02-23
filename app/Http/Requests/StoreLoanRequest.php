<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreLoanRequest
{
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required',
            'loan_term' => 'required',
        ]);
        // Return errors if validation error occur.
        if ($validator->fails()) {
            return [false, $validator->errors()];
        } else {
            return [true, null];
        }
    }
}
