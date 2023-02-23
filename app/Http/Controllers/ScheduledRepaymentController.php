<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\ScheduledRepayment;
use App\Models\User;

class ScheduledRepaymentController extends Controller
{
    // Admin approve specified ScheduledRepayment
    public function pay(Request $request, string $id)
    {
        try {
            // Validated request body
            $validate = Validator::make($request->all(), 
            [
                'amount' => 'required',
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $scheduledRepayments = ScheduledRepayment::Where([
                'id' => $id,
                'customer_id' => auth()->user()->id,
            ])->get();

            // Validate if scheduledRepayment exist 
            if (count($scheduledRepayments) == 0) {
                return response()->json([
                    "success" => false,
                    "message" => "ScheduledRepayment not found",
                ], 404);
            }

            $scheduledRepayment = $scheduledRepayments[0];

            // Validate if loan status is APPROVED
            $loan = $scheduledRepayment->loan;
            if ($loan->status == "PENDING") {
                return response()->json([
                    "success" => false,
                    "message" => "Loan not approved yet",
                ], 422);
            } else if ($loan->status == "PAID") {
                return response()->json([
                    "success" => false,
                    "message" => "Loan is already PAID",
                ], 422);
            }

            // Validate if ScheduledRepayment is PENDING
            if ($scheduledRepayment->status == "PAID") {
                return response()->json([
                    "success" => false,
                    "message" => "ScheduledRepayment is already PAID",
                ], 422);
            }

            // Validate if repayment amount is greater or equal to payable_amount
            if ($request->amount < $scheduledRepayment->payable_amount) {
                return response()->json([
                    "success" => false,
                    "message" => "Amount is not enough",
                ], 422);
            }

            $scheduledRepayment->Update([
                'status' => 'PAID',
                'paid_amount' => $request->amount,
                'paid_at' => Carbon::now(),
            ]);

            $customer = User::Find($scheduledRepayment->customer_id);
            $cashBalance = $customer->cash_balance + $request->amount;
            $customer->Update(['cash_balance' => $cashBalance]);

            $allRepayments = $loan->scheduled_repayments;
            $isAllRepaymentsPaid = true;

            for ($i = 0; $i<count($allRepayments); $i++) {
                if ($allRepayments[$i]->status == 'PENDING') {
                    $isAllRepaymentsPaid = false;
                }
            }

            if ($isAllRepaymentsPaid) {
                $loan->Update(['status' => 'PAID']);
            }

            return response()->json([
                'status' => true,
                'message' => 'ScheduledRepayment paid successfully!',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}