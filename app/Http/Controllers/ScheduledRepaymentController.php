<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Helpers\ApiFormatter;
use App\Http\Requests\PayScheduledRepaymentRequest;
use App\Services\ScheduledRepaymentService;

class ScheduledRepaymentController extends Controller
{
    // Admin approve specified ScheduledRepayment
    public function pay(Request $request, string $id, ScheduledRepaymentService $service, PayScheduledRepaymentRequest $validator)
    {
        // Validated request body
        list($valid, $errorMsg) = $validator->validate($request);
        if (!$valid) {
            return ApiFormatter::response(false, $errorMsg, 422);
        }

        list($success, $message) = $service->pay($id, $request);

        if ($success) {
            return ApiFormatter::response(true, $message);
        } else {
            return ApiFormatter::response(false, $message, 422);
        }
    }
}