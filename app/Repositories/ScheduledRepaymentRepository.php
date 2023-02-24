<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\ScheduledRepayment;

class ScheduledRepaymentRepository
{
    public function findByCustomerId(string $customerId){
        return ScheduledRepayment::Where(['customer_id' => $customerId])->get();
    }

    public function findByIdAndCustomerId(string $id, string $customerId) {
        $scheduledRepayments = ScheduledRepayment::Where([
            'id' => $id,
            'customer_id' => $customerId,
        ])->get();

        return $scheduledRepayments;
    }

    public function create($data)
    {
        $ScheduledRepayment = ScheduledRepayment::Create([
            'loan_id' => $data['loan_id'],
            'customer_id' => $data['customer_id'],
            'payable_amount' => $data['payable_amount'],
            'due_date' => $data['due_date'],
            'paid_amount' => 0,
            'status' => 'PENDING',
        ]);

        return $ScheduledRepayment;
    }

    public function pay(ScheduledRepayment $scheduledRepayment, float $paidAmount)
    {
        $scheduledRepayment->Update([
            'status' => 'PAID',
            'paid_amount' => $paidAmount,
            'paid_at' => Carbon::now(),
        ]);
    }

}