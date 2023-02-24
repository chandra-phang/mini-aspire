<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_index_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        Loan::factory()->count(2)->create(['customer_id' => $customer->id]);

        // Execute Admin List Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->getJson(route('loan.admin-list'));
        $response->assertStatus(200);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals(2, count($res['data']));
    }

    public function test_admin_index_returns_error_if_user_is_not_admin(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        Loan::factory()->count(2)->create(['customer_id' => $customer->id]);

        // Execute Admin List Loan API
        $response = $this->actingAs($customer)->getJson(route('loan.admin-list'));
        $response->assertStatus(403);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('You are not authorized to access this page', $res['message']);
    }

    public function test_customer_index_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        Loan::factory()->create(['customer_id' => $customer->id]);
        
        $customer2 = User::factory()->create();
        Loan::factory()->create(['customer_id' => $customer2->id]);

        // Execute Customer List Loan API
        $response = $this->actingAs($customer)->getJson(route('loan.customer-list'));
        $response->assertStatus(200);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals(1, count($res['data']));
    }

    public function test_store_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();

        // Execute Store Loan API
        $body = ['total_amount' => 10000, 'loan_term' => 3];
        $response = $this->actingAs($customer)->postJson(route('loan.store'), $body);
        $response->assertStatus(201);
        
        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals(10000, $res['data']["total_amount"]);

        // Assert created loan
        $loan = Loan::First();
        $this->assertEquals(10000, $loan->total_amount);
        $this->assertEquals(3, $loan->loan_term);

        // Assert created scheduledRepayment
        $scheduledRepayments = $loan->scheduled_repayments;
        $this->assertEquals(3, count($scheduledRepayments));
        $this->assertEquals(3333.33, $scheduledRepayments[0]->payable_amount);
        $this->assertEquals(3333.33, $scheduledRepayments[1]->payable_amount);
        $this->assertEquals(3333.34, $scheduledRepayments[2]->payable_amount);
    }

    public function test_store_returns_error_when_body_is_empty(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();

        // Execute Store Loan API
        $body = [];
        $response = $this->actingAs($customer)->postJson(route('loan.store'), $body);
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals(2, count($res['message']));
        $this->assertEquals("The total amount field is required.", $res['message']["total_amount"][0]);
        $this->assertEquals("The loan term field is required.", $res['message']["loan_term"][0]);
    }

    public function test_show_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Show Loan API
        $response = $this->actingAs($customer)->getJson(route('loan.show', ['id' => $loan->id]));
        $response->assertStatus(200);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals(10000, $res['data']["total_amount"]);
    }

    public function test_show_returns_error_when_not_owned_by_customer(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Show Loan API
        $customer2 = User::factory()->create();
        $response = $this->actingAs($customer2)->getJson(route('loan.show', ['id' => $loan->id]));
        $response->assertStatus(404);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Loan not found', $res['message']);
    }

    public function test_show_returns_correct_response_when_user_is_admin(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Show Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->getJson(route('loan.show', ['id' => $loan->id]));
        $response->assertStatus(200);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals(10000, $res['data']['total_amount']);
    }

    public function test_approve_returns_correct_response(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Approve Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->patchJson(route('loan.approve', ['id' => $loan->id]));
        $response->assertStatus(200);

        $updatedLoan = Loan::Find($loan->id);
        $updatedCustomer = User::Find($customer->id);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals('Loan approved succesfully!', $res['message']);

        // Assert updated loan
        $this->assertEquals('APPROVED', $updatedLoan->status);
        $this->assertEquals($admin->id, $updatedLoan->approver_id);
        $this->assertNotEmpty($updatedLoan->approved_at);

        // assert updated customer
        $this->assertEquals(-10000, $updatedCustomer->cash_balance);
    }

    public function test_approve_returns_error_when_user_is_not_admin(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Approve Loan API
        $response = $this->actingAs($customer)->patchJson(route('loan.approve', ['id' => $loan->id]));
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('You are not authorized to access this page', $res['message']);
    }

    public function test_approve_returns_error_when_loan_not_found(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        Loan::factory()->create(['customer_id' => $customer->id]);

        // Execute Approve Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->patchJson(route('loan.approve', ['id' => 'invalid-id']));
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Loan not found', $res['message']);
    }

    public function test_approve_returns_error_when_customer_id_equals_user_id(): void
    {
        // Create customer and loans
        $admin = User::factory()->admin()->create();
        $loan = Loan::factory()->create(['customer_id' => $admin->id]);

        // Execute Approve Loan API
        $response = $this->actingAs($admin)->patchJson(route('loan.approve', ['id' => $loan->id]));
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("You can't approve your own loan", $res['message']);
    }

    public function test_approve_returns_error_when_loan_is_already_approved(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->approved()->create(['customer_id' => $customer->id]);

        // Execute Approve Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->patchJson(route('loan.approve', ['id' => $loan->id]));
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Loan already in APPROVED status', $res['message']);
    }

    public function test_approve_returns_error_when_loan_is_already_paid(): void
    {
        // Create customer and loans
        $customer = User::factory()->create();
        $loan = Loan::factory()->paid()->create(['customer_id' => $customer->id]);

        // Execute Approve Loan API
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->patchJson(route('loan.approve', ['id' => $loan->id]));
        $response->assertStatus(422);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals('Loan already PAID', $res['message']);
    }
}
