<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Helpers\ApiFormatter;
use App\Http\Requests\PayScheduledRepaymentRequest;
use App\Http\Controllers\Controller;
use App\Services\ScheduledRepaymentService;

class ScheduledRepaymentController extends Controller
{
    protected $scheduledRepaymentService;

    public function __construct(ScheduledRepaymentService $scheduledRepaymentService)
    {
        $this->scheduledRepaymentService = $scheduledRepaymentService;
    }

    // list ScheduledRepayment by customer_id
    public function index(Request $request)
    {
        $userID = auth()->user()->id;
        $scheduledRepayments = $this->scheduledRepaymentService->getByCustomerId($userID);

        return ApiFormatter::responseWithData(true, $scheduledRepayments);
    }

    // Admin approve specified ScheduledRepayment
    public function pay(Request $request, string $id, PayScheduledRepaymentRequest $validator)
    {
        // Validated request body
        list($valid, $errorMsg) = $validator->validate($request);
        if (!$valid) {
            return ApiFormatter::response(false, $errorMsg, 422);
        }

        list($success, $message) = $this->scheduledRepaymentService->pay($id, $request);

        if ($success) {
            return ApiFormatter::response(true, $message);
        } else {
            return ApiFormatter::response(false, $message, 422);
        }
    }
}