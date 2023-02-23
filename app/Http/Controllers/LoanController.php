<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Helpers\ApiFormatter;
use App\Services\LoanService;
use App\Models\Loan;


class LoanController extends Controller
{
    // Display a listing of the loans.
    public function admin_index()
    {
        if (!auth()->user()->is_admin){
            $message = "You are not authorized to access this page";
            return ApiFormatter::response(false, $message, 401);
        }

        $loans = Loan::all();
        return ApiFormatter::responseWithData(true, $loans);
    }

    // Display a listing of the loans by customer_id.
    public function customer_index()
    {
        $loans = Loan::Where('customer_id', auth()->user()->id)->get();
        return ApiFormatter::responseWithData(true, $loans);
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
            return ApiFormatter::response(false, $validate->errors(), 422);
        }

        // Create loan
        $loan = $service->create($request);
        if (!$loan) {
            return ApiFormatter::response(false, 'Failed to create loan', 422);
        }

        // Create ScheduledRepayment
        $service->createScheduledRepayment($loan);
        return ApiFormatter::responseWithData(true, $loan);
    }

    // Display the specified loan.
    public function show(string $id, LoanService $service)
    {   
        $loan = $service->find($id);
        if ($loan) {
            return ApiFormatter::responseWithData(true, $loan);
        } else {
            return ApiFormatter::response(false, "Loan not found", 404);
        }
    }
    
    // Admin approve specified loan
    public function approve(string $id, LoanService $service)
    {
        list($success, $message) = $service->approve($id);
        if ($success) {
            return ApiFormatter::response(true, $message);
        } else {
            return ApiFormatter::response(false, $message, 422);
        }
    }
}
