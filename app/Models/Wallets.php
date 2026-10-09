<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperWallets
 */
class Wallets extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'held_balance',
    ];

    protected $casts = [
        'balance' => 'integer',
        'held_balance' => 'integer',
    ];

    /*
     * ================================================================
     * OWNER
     * ================================================================
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /*
     * ================================================================
     * AVAILABLE BALANCE
     * ================================================================
     *
     * Saldo yang benar-benar bisa digunakan.
     *
     * available =
     * balance - held_balance
     */

    public function getAvailableBalanceAttribute(): int
    {
        return max(
            0,
            $this->balance - $this->held_balance
        );
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
}
}
