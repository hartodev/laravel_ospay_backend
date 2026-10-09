<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperWalletTransaction
 */
class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'reference_type',
        'reference_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallets::class, 'wallet_id');
    }
}
