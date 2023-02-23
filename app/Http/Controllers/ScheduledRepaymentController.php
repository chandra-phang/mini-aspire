<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Services\ScheduledRepaymentService;

class ScheduledRepaymentController extends Controller
{
    // Admin approve specified ScheduledRepayment
    public function pay(Request $request, string $id, ScheduledRepaymentService $service)
    {
        // Validated request body
        $validate = Validator::make($request->all(), ['amount' => 'required']);

        if($validate->fails()){
            return ApiFormatter::response(false, $validate->errors(), 422);
        }

        list($success, $message) = $service->pay($id, $request);

        if ($success) {
            return ApiFormatter::response(true, $message);
        } else {
            return ApiFormatter::response(false, $message, 422);
        }
    }
}