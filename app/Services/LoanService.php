<?php

namespace App\Services;

use Carbon\Carbon;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\User;

class LoanService
{
    public function create($data) : Loan
    {
        $userID = auth()->user()->id;
        $loan = Loan::Create([
            'total_amount' => $data->total_amount,
            'loan_term' => $data->loan_term,
            'status' => 'PENDING',
            'customer_id' => $userID,
        ]);

        return $loan;
    }

    public function createScheduledRepayment(Loan $loan)
    {
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
                'customer_id' => auth()->user()->id,
                'payable_amount' => $scheduledPayableAmount,
                'paid_amount' => 0,
                'due_date' => $dueDate,
                'status' => 'PENDING',
            ]);
        }
    }

    public function find(string $id) : Loan
    {
        // Admin allowed to see all loans but customer only can see their own loans
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

        return $loan;
    }

    public function approve(string $id)
    {
        if (!auth()->user()->is_admin){   
            return [false, "You are not authorized to access this page"];
        }

        $loans = Loan::Where('id', $id)->get();
        // Validate whether loan is exist 
        if (count($loans) == 0) {
            return [false, "Loan not found"];
        }

        $loan = $loans[0];

        // Validate customer and approver can't be same persone
        if ($loan->customer_id == auth()->user()->id){
            return [false, "You can't approve your own loan"];
        }

        // Validate loan status is PENDING
        if ($loan->status == 'APPROVED') {
            return [false, "Loan already in APPROVED status"];
        } else if ($loan->status == 'PAID') {
            return [false, "Loan already PAID"];
        } 

        // Update loan status to APPROVED
        $loan->Update([
            'approver_id' => auth()->user()->id,
            'approved_at' => Carbon::now(),
            'status' => 'APPROVED',
        ]);

        // Update customer cash balance
        $customer = User::Find($loan->customer_id);
        $cashBalance = $customer->cash_balance - $loan->total_amount;
        $customer->Update(['cash_balance' => $cashBalance]);

        return [true, "Loan approved succesfully!"];
    }
}
?>