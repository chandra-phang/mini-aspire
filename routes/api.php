<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ScheduledRepaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Models\ScheduledRepayment;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('auth/register', [AuthController::class, 'register'])->name('auth-register');
Route::post('auth/login', [AuthController::class, 'login'])->name('auth-login');
Route::get('home', [AuthController::class, 'home'])->name('home');

Route::middleware('auth:sanctum')->group(function () {
    // AuthController
    Route::get('/me', [AuthController::class, 'me'])->name('auth-me');

    // LoanController - Customer
    Route::get('loans', [LoanController::class, 'customer_index'])->name('loan.customer-list');
    Route::get('loans/{id}', [LoanController::class, 'show'])->name('loan.show');
    Route::post('loans', [LoanController::class, 'store'])->name('loan.store');

    // LoanController - Admin
    Route::get('admin/loans', [LoanController::class, 'admin_index'])->name('loan.admin-list');
    Route::patch('admin/loans/{id}/approve', [LoanController::class, 'approve'])->name('loan.approve');

    // ScheduledRepayment
    Route::get('scheduled_repayments', [ScheduledRepaymentController::class, 'index'])->name('scheduled-repayment.list');
    Route::post('scheduled_repayments/{id}/pay', [ScheduledRepaymentController::class, 'pay'])->name('scheduled-repayment.pay');
});
