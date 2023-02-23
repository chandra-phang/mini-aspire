<?php

namespace App\Http\Controllers;

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
            return response()->json([
                'success' => false,
                'message' => $validate->errors()
            ], 422);
        }

        list($success, $message) = $service->pay($id, $request);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => $message,
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => $message,
            ], 422);  
        }
    }
}