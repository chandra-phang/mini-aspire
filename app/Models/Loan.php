<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount_required',
        'loan_term',
        'status',
        'customer_id',
        'approver_id',
        'approved_at',
    ];
}
