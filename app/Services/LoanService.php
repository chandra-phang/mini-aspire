<?php

namespace App\Services;

use Carbon\Carbon;

use App\Models\Loan;

use App\Repositories\LoanRepository;
use App\Repositories\UserRepository;
use App\Repositories\ScheduledRepaymentRepository;

class LoanService
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

    public function all()
    {
        return $this->loanRepository->all();
    }

    public function getByCustomerId(string $customerId)
    {
        return $this->loanRepository->findByCustomerId($customerId);
    }

    public function create($data) : Loan
    {
        $userID = $this->currentUser->id;
        $data['customer_id'] = $userID;

        $loan = $this->loanRepository->create($data);

        return $loan;
    }

    public function createScheduledRepayment(Loan $loan) : void
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

            $data = [
                'loan_id' => $loan->id,
                'customer_id' => $this->currentUser->id,
                'payable_amount' => $scheduledPayableAmount,
                'due_date' => $dueDate,
            ];

            $this->scheduledRepaymentRepository->create($data);
        }
    }

    public function find(string $id) : Loan|null
    {
        $loan = null;
        // Admin allowed to see all loans but customer only can see their own loans
        if ($this->currentUser->is_admin) {
            $loan = $this->loanRepository->findById($id);
        } else {
            $customerId = $this->currentUser->id;
            $loans = $this->loanRepository->findByIdAndCustomerId($id, $customerId);

            if (count($loans) > 0) {
                $loan = $loans[0];
            }
        }
        return $loan;
    }

    public function approve(string $id)
    {
        $user = $this->currentUser;

        if (!$user->is_admin){   
            return [false, "You are not authorized to access this page"];
        }

        // Validate whether loan is exist 
        $loan = $this->loanRepository->findById($id);
        if (!$loan) {
            return [false, "Loan not found"];
        }

        // Validate customer and approver can't be same persone
        if ($loan->customer_id == $user->id){
            return [false, "You can't approve your own loan"];
        }

        // Validate loan status is PENDING
        if ($loan->status == 'APPROVED') {
            return [false, "Loan already in APPROVED status"];
        } else if ($loan->status == 'PAID') {
            return [false, "Loan already PAID"];
        } 

        // Update loan status to APPROVED
        $this->loanRepository->approve($loan, $user->id);

        // Update customer cash balance        
        $customer = $this->userRepository->findById($loan->customer_id);
        $cashBalance = $customer->cash_balance - $loan->total_amount;
        $this->userRepository->updateCashBalance($customer, $cashBalance);

        return [true, "Loan approved succesfully!"];
    }
}
?>