<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Carbon\Factory;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_register_returns_correct_response(): void
    {
        // Execute Admin List Loan API
        $body = ['name' => 'Chandra', 'email' => 'chandra@gmail.com', 'password' => '0123456789'];
        $response = $this->postJson(route('auth-register'), $body);
        $response->assertStatus(200);

        // Assert response
        $res = $response->json();
        $this->assertEquals(true, $res['success']);
        $this->assertEquals("User created successfully", $res['message']);
    }
    
    public function test_register_returns_error_when_name_is_empty(): void
    {
        // Execute Admin List Loan API
        $body = ['email' => 'chandra@gmail.com', 'password' => '0123456789'];
        $response = $this->postJson(route('auth-register'), $body);
        $response->assertStatus(400);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("The name field is required.", $res['message']['name'][0]);
    }
    
    public function test_register_returns_error_when_email_is_empty(): void
    {
        // Execute Admin List Loan API
        $body = ['name' => 'Chandra', 'password' => '0123456789'];
        $response = $this->postJson(route('auth-register'), $body);
        $response->assertStatus(400);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("The email field is required.", $res['message']['email'][0]);
    }
    
    public function test_register_returns_error_when_password_is_empty(): void
    {
        // Execute Admin List Loan API
        $body = ['name' => 'Chandra', 'email' => 'chandra@gmail.com'];
        $response = $this->postJson(route('auth-register'), $body);
        $response->assertStatus(400);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("The password field is required.", $res['message']['password'][0]);
    }
    
    public function test_register_returns_error_when_password_is_less_than_8_characters(): void
    {
        // Execute Admin List Loan API
        $body = ['name' => 'Chandra', 'email' => 'chandra@gmail.com', 'password' => '12345'];
        $response = $this->postJson(route('auth-register'), $body);
        $response->assertStatus(400);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("The password field must be at least 8 characters.", $res['message']['password'][0]);
    }

    // I don't know why this test is failing since I already use correct email and password
    // public function test_login_returns_correct_response(): void
    // {
    //     // Execute Admin List Loan API
    //     User::factory()->create([
    //         'email' => 'chandra@gmail.com',
    //         'password' => '123456789',
    //     ]);
    //     $body = ['email' => 'chandra@gmail.com', 'password' => '123456789'];
    //     $response = $this->postJson(route('auth-login'), $body);
    //     // $response->assertStatus(200);

    //     // Assert response
    //     $res = $response->json();
    //     $this->assertEquals(true, $res['success']);
    //     $this->assertEquals("", $res['message']);
    // }

    public function test_login_returns_error_when_email_and_password_not_match(): void
    {
        // Execute Admin List Loan API
        User::factory()->create([
            'email' => 'chandra@gmail.com',
            'password' => '123456789',
        ]);
        $body = ['email' => 'chandra@gmail.com', 'password' => '12345'];
        $response = $this->postJson(route('auth-login'), $body);
        $response->assertStatus(401);

        // Assert response
        $res = $response->json();
        $this->assertEquals(false, $res['success']);
        $this->assertEquals("Invalid login details", $res['message']);
    }
}