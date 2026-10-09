<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperTransactions
 */
class Transactions extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'base_price' => 'integer',
        'sell_price' => 'integer',
        'iak_response' => 'array',
        'midtrans_response' => 'array',
        'refund_amount' => 'integer',
        'refunded_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Method
    |--------------------------------------------------------------------------
    */

    public function isWalletPayment(): bool
    {
        return $this->payment_method === 'wallet';
    }

    public function isMidtransPayment(): bool
    {
        return $this->payment_method === 'midtrans';
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /*
    |--------------------------------------------------------------------------
    | IAK Status
    |--------------------------------------------------------------------------
    */

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /*
    |--------------------------------------------------------------------------
    | Refund Status
    |--------------------------------------------------------------------------
    */

    public function requiresRefund(): bool
    {
        return $this->refund_status === 'pending';
    }

    public function isRefunded(): bool
    {
        return $this->refund_status === 'refunded';
    }
}
