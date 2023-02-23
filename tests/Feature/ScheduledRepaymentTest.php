<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Loan;
use App\Models\ScheduledRepayment;
use Tests\TestCase;

class ScheduledRepaymentTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_index_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);
        ScheduledRepayment::factory()->count(2)->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($customer)->getJson(route('scheduled-repayment.list'));
        $res = $response->json();

        $this->assertEquals(true, $res['success']);
        $this->assertEquals(2, count($res['data']));
    }
    
    public function test_pay_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(true, $res['success']);
        $this->assertEquals("ScheduledRepayment paid successfully", $res['message']);

        // Assert updated scheduledPayment
        $updatedScheduledPayment = ScheduledRepayment::Find($scheduledRepayment->id);
        $this->assertEquals('PAID', $updatedScheduledPayment->status);

        // Assert updated loan
        $updatedLoan = Loan::Find($loan->id);
        $this->assertEquals('PAID', $updatedLoan->status);

        // Assert customer cash_balance
        $updatedUser = User::Find($customer->id);
        $this->assertEquals(0, $updatedUser->cash_balance);
    }

    public function test_pay_not_updating_loan_status_to_paid_if_there_is_pending_scheduled_repayment(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);
        $scheduledRepayment2 = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(true, $res['success']);
        $this->assertEquals("ScheduledRepayment paid successfully", $res['message']);

        // Assert updated loan
        $updatedLoan = Loan::Find($loan->id);
        $this->assertEquals('APPROVED', $updatedLoan->status);
    }

    public function test_pay_returns_error_when_scheduled_repayment_not_found(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);
        ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => 'invalid-id']), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("ScheduledRepayment not found", $res['message']);
    }

    public function test_pay_returns_error_when_loan_status_is_pending(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("Loan not approved yet", $res['message']);
    }

    public function test_pay_returns_error_when_loan_status_is_paid(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->paid()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("Loan is already PAID", $res['message']);
    }

    public function test_pay_returns_error_when_scheduled_repayment_status_is_paid(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->paid()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 1000];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("ScheduledRepayment is already PAID", $res['message']);
    }

    public function test_pay_returns_error_when_amount_is_less_than_payable_amount(): void
    {
        // Create customer and loans
        $customer = User::factory()->create(['cash_balance' => -1000]);
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);
        $scheduledRepayment = ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'customer_id' => $customer->id,
        ]);

        // Pay scheduledPayment
        $body = ['amount' => 100];
        $response = $this->actingAs($customer)->postJson(route('scheduled-repayment.pay', ['id' => $scheduledRepayment->id]), $body);
        $res = $response->json();

        // Assert response
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("Amount is not enough", $res['message']);
    }
}
