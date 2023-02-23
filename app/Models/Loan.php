<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    public function scheduled_repayments(): HasMany
    {
        return $this->hasMany(ScheduledRepayment::class);
    }

    protected $table = 'loans';
    protected $fillable = [
        'total_amount',
        'loan_term',
        'status',
        'customer_id',
        'approver_id',
        'approved_at',
    ];
}
