<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Loan;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = Loan::all();
        return [
            "success" => true,
            "data" => $loans,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            //Validated
            $validateLoan = Validator::make($request->all(), 
            [
                'amount_required' => 'required',
                'loan_term' => 'required',
            ]);

            if($validateLoan->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'validation error',
                    'errors' => $validateLoan->errors()
                ], 401);
            }

            $user = Loan::create([
                'amount_required' => $request->amount_required,
                'loan_term' => $request->loan_term,
                'status' => 'PENDING',
                'customer_id' => auth()->user()->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Loan Created Successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
}
