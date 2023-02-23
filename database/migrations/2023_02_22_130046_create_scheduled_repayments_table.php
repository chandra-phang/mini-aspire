<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_repayments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('loan_id')->length(20)->unsigned();
            $table->bigInteger('customer_id')->length(20)->unsigned();
            $table->float('payable_amount', 20, 2);
            $table->float('paid_amount', 20, 2);
            $table->dateTime('due_date', $precision = 0);
            $table->dateTime('paid_at', $precision = 0)->nullable();
            $table->string('status', 20);
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans');
            $table->foreign('customer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_repayments');
    }
};
