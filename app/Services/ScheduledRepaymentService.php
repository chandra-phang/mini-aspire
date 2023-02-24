<?php

namespace App\Services;

use App\Repositories\LoanRepository;
use App\Repositories\UserRepository;
use App\Repositories\ScheduledRepaymentRepository;

class ScheduledRepaymentService
{
    protected $currentUser;
    protected $loanRepository;
    protected $userRepository;
    protected $scheduledRepaymentRepository;

    public function __construct(
        LoanRepository $loanRepository,
        UserRepository $userRepository,
        ScheduledRepaymentRepository $scheduledRepaymentRepository)
    {
        $this->currentUser = auth()->user();
        $this->loanRepository = $loanRepository;
        $this->userRepository = $userRepository;
        $this->scheduledRepaymentRepository = $scheduledRepaymentRepository;
    }

    public function getByCustomerId(string $customerId)
    {
        return $this->scheduledRepaymentRepository->findByCustomerId($customerId);
    }

    public function pay(string $id, $data)
    {
        // Find ScheduledRepayment by id and customer_id
        $userId = $this->currentUser->id;
        $scheduledRepayments = $this->scheduledRepaymentRepository->findByIdAndCustomerId($id, $userId);

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
        $this->scheduledRepaymentRepository->pay($scheduledRepayment, $data->amount);

        // Update Customer cash_balance
        $customer = $this->userRepository->findById($loan->customer_id);
        $cashBalance = $customer->cash_balance + $data->amount;
        $this->userRepository->updateCashBalance($customer, $cashBalance);

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
            $this->loanRepository->pay($loan);
        }

        return [true, "ScheduledRepayment paid successfully"];
    }
}
?>