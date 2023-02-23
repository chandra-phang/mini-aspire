<?php

namespace App\Services;

use Carbon\Carbon;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\User;

class ScheduledRepaymentService
{
    public function pay(string $id, $data)
    {
        // Find ScheduledRepayment by id and customer_id
        $scheduledRepayments = ScheduledRepayment::Where([
            'id' => $id,
            'customer_id' => auth()->user()->id,
        ])->get();

        // Validate if ScheduledRepayment exist 
        if (count($scheduledRepayments) == 0) {
            return [false, "ScheduledRepayment not found"];
        }

        $scheduledRepayment = $scheduledRepayments[0];

        // Validate if loan status is APPROVED
        $loan = $scheduledRepayment->loan;
        if ($loan->status == "PENDING") {
            return [false, "Loan not approved yet"];
        } else if ($loan->status == "PAID") {
            return [false, "Loan is already PAID"];
        }

        // Validate if ScheduledRepayment is PENDING
        if ($scheduledRepayment->status == "PAID") {
            return [false, "ScheduledRepayment is already PAID"];
        }

        // Validate if repayment amount is greater or equal to payable_amount
        if ($data->amount < $scheduledRepayment->payable_amount) {
            return [false, "Amount is not enough"];
        }

        // Update ScheduleRepayment status to PAID
        $scheduledRepayment->Update([
            'status' => 'PAID',
            'paid_amount' => $data->amount,
            'paid_at' => Carbon::now(),
        ]);

        // Update Customer cash_balance
        $customer = User::Find($scheduledRepayment->customer_id);
        $cashBalance = $customer->cash_balance + $data->amount;
        $customer->Update(['cash_balance' => $cashBalance]);

        // Check if all ScheduledRepayments has been PAID
        // If yes, update loan status to PAID
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

        return [true, "ScheduledRepayment paid successfully"];
    }
}
?>