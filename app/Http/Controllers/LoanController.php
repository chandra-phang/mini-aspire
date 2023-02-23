<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\User;

class LoanController extends Controller
{
    // Display a listing of the loans.
    public function admin_index()
    {
        if (!auth()->user()->is_admin){   
            return response()->json([
                "success" => false,
                "message" => "You are not authorized to access this page",
            ], 401);
        }

        $loans = Loan::all();
        return response()->json([
            "success" => true,
            "data" => $loans,
        ], 200);
    }

    // Display a listing of the loans by customer_id.
    public function customer_index()
    {
        $loans = Loan::Where('customer_id', auth()->user()->id)->get();
        return response()->json([
            "success" => true,
            "data" => $loans,
        ], 200);
    }

    // Store a newly created loan in storage.
    public function store(Request $request)
    {
        try {
            // Validated request body
            $validate = Validator::make($request->all(), 
            [
                'total_amount' => 'required',
                'loan_term' => 'required',
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $userID = auth()->user()->id;

            $loan = Loan::Create([
                'total_amount' => $request->total_amount,
                'loan_term' => $request->loan_term,
                'status' => 'PENDING',
                'customer_id' => $userID,
            ]);

            $data = Loan::FindOrFail($loan->id);
            if ($data) {
                $scheduledPayableAmount = $loan->total_amount/$loan->loan_term;
                $scheduledPayableAmount = round($scheduledPayableAmount, 2);

                for($i = 0; $i<$loan->loan_term; $i++)
                {
                    $todayDate = date("Y-m-d");
                    $todayDate = Carbon::parse($todayDate);
                    $dueDate = $todayDate->addWeeks($i+1);

                    if (($i + 1) == $loan->loan_term) {
                        $scheduledPayableAmount = $loan->total_amount - ($i * $scheduledPayableAmount);
                    };

                    ScheduledRepayment::Create([
                        'loan_id' => $loan->id,
                        'customer_id' => $userID,
                        'payable_amount' => $scheduledPayableAmount,
                        'paid_amount' => 0,
                        'due_date' => $dueDate,
                        'status' => 'PENDING',
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'data' => $loan,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create loan',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Display the specified loan.
    public function show(string $id)
    {
        // Admin allowed to see all loans but customer only can see their own loans
        $loan = NULL;
        if (auth()->user()->is_admin) {
            $loan = Loan::Find($id);
        } else {
            $loans = Loan::Where([
                'id' => $id,
                'customer_id' => auth()->user()->id,
            ])->get();
            if (count($loans) > 0) {
                $loan = $loans[0];
            }
        }
        
        if ($loan) {
            return response()->json([
                "success" => true,
                "data" => $loan,
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Loan not found",
            ], 404);
        }
    }
    
    // Admin approve specified loan
    public function approve(string $id)
    {
        if (!auth()->user()->is_admin){   
            return response()->json([
                "success" => false,
                "message" => "You are not authorized to access this page",
            ], 401);
        }

        try {
            $loans = Loan::Where('id', $id)->get();
            // Validate whether loan is exist 
            if (count($loans) == 0) {
                return response()->json([
                    "success" => false,
                    "message" => "Loan not found",
                ], 404);
            }

            $loan = $loans[0];

            // Validate customer and approver can't be same persone
            if ($loan->customer_id == auth()->user()->id){
                return response()->json([
                    "success" => false,
                    "message" => "You can't approve your own loan",
                ], 422);  
            }

            // Validate loan status is PENDING
            if ($loan->status == 'PENDING') {
                $loan->Update([
                    'approver_id' => auth()->user()->id,
                    'approved_at' => Carbon::now(),
                    'status' => 'APPROVED',
                ]);

                // Update customer cash balance
                $customer = User::Find($loan->customer_id);
                $cashBalance = $customer->cash_balance - $loan->total_amount;
                $customer->Update(['cash_balance' => $cashBalance]);

                return response()->json([
                    'status' => true,
                    'message' => $loan,
                ], 200);
            } else if ($loan->status == 'APPROVED') {
                return response()->json([
                    'status' => false,
                    'message' => 'Loan already in APPROVED status',
                ], 422);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to approve loan',
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
