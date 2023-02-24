<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Helpers\ApiFormatter;
use App\Http\Requests\PayScheduledRepaymentRequest;
use App\Http\Controllers\Controller;
use App\Models\ScheduledRepayment;
use App\Services\ScheduledRepaymentService;

class ScheduledRepaymentController extends Controller
{
    // list ScheduledRepayment by customer_id
    public function index(Request $request)
    {
        $userID = auth()->user()->id;
        $scheduledRepayment = ScheduledRepayment::Where(['customer_id' => $userID])->get();

        return ApiFormatter::responseWithData(true, $scheduledRepayment);
    }

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