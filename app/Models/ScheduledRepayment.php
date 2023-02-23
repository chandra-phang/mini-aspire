<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ScheduledRepayment extends Model
{
    use HasFactory;

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    protected $table = 'scheduled_repayments';
    protected $fillable = [
        'loan_id',
        'customer_id',
        'payable_amount',
        'paid_amount',
        'paid_at',
        'due_date',
        'status',
    ];
}
