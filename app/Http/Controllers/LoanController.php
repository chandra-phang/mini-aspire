<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Services\LoanService;

use App\Models\Loan;


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
    public function store(Request $request, LoanService $service)
    {
        // Validated request body
        $validate = Validator::make($request->all(), 
        [
            'total_amount' => 'required',
            'loan_term' => 'required',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => $validate->errors()
            ], 422);
        }

        // Create loan
        $loan = $service->create($request);
        if (!$loan) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create loan',
            ], 422);
        }

        // Create ScheduledRepayment
        $service->createScheduledRepayment($loan);

        return response()->json([
            'status' => true,
            'data' => $loan,
        ], 200);
    }

    // Display the specified loan.
    public function show(string $id, LoanService $service)
    {   
        $loan = $service->find($id);
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
    public function approve(string $id, LoanService $service)
    {
        list($success, $message) = $service->approve($id);
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
