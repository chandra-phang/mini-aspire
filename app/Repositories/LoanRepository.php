<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Loan;

class LoanRepository
{
    public function create($data)
    {
        $loan = Loan::Create([
            'total_amount' => $data->total_amount,
            'loan_term' => $data->loan_term,
            'status' => 'PENDING',
            'customer_id' => $data->customer_id,
        ]);

        return $loan;
    }

    public function all()
    {
        return Loan::all();
    }

    public function findById(string $id)
    {
        return Loan::Find($id);
    }

    public function findByCustomerId(string $customerId)
    {
        return Loan::Where(['customer_id' => $customerId])->get();
    }

    public function findByIdAndCustomerId(string $id, string $customerId)
    {
        return Loan::Where(['id' => $id,'customer_id' => $customerId])->get();
    }

    public function approve(Loan $loan, string $approverId) {
        $loan = $loan->Update([
            'approver_id' => $approverId,
            'approved_at' => Carbon::now(),
            'status' => 'APPROVED',
        ]);

        return $loan;
    }

    public function pay(Loan $loan) {
        return $loan->Update(['status' => 'PAID']);
    }
}