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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->float('total_amount', 20, 2);
            $table->integer('loan_term');
            $table->string('status', 20);
            $table->bigInteger('customer_id')->length(20)->unsigned();
            $table->bigInteger('approver_id')->length(20)->unsigned()->nullable();
            $table->dateTime('approved_at', $precision = 0)->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
