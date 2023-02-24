<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Helpers\ApiFormatter;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Controllers\Controller;
use App\Services\LoanService;

class LoanController extends Controller
{
    protected $loanService;
    protected $currentUser;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
        $this->currentUser = auth()->user();
    }

    // Display a listing of the loans.
    public function admin_index()
    {
        if (!$this->currentUser->is_admin){
            $message = "You are not authorized to access this page";
            return ApiFormatter::response(false, $message, 403);
        }

        $loans = $this->loanService->all();
        return ApiFormatter::responseWithData(true, $loans);
    }

    // Display a listing of the loans by customer_id.
    public function customer_index()
    {
        $loans = $this->loanService->getByCustomerId($this->currentUser->id);
        return ApiFormatter::responseWithData(true, $loans);
    }

    // Store a newly created loan in storage.
    public function store(Request $request, LoanService $service, StoreLoanRequest $validator)
    {
        // Validate request body
        list($valid, $errorsMsg) = $validator->validate($request);
        if (!$valid) {
            return ApiFormatter::response(false, $errorsMsg, 400);
        }
            
        // Create loan
        $loan = $service->create($request);
        if (!$loan) {
            return ApiFormatter::response(false, 'Failed to create loan', 422);
        }

        // Create ScheduledRepayment
        $service->createScheduledRepayment($loan);
        return ApiFormatter::responseWithData(true, $loan, 201);
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
