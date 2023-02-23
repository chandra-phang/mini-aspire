<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledRepayment extends Model
{
    use HasFactory;

    protected $table = 'scheduled_repayments';
    protected $fillable = [
        'loan_id',
        'amount_required',
        'amount_paid',
        'due_date',
        'status',
    ];
}
