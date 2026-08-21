<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallets extends Model
{
    use HasFactory;

    protected $guarded = [];
      protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'held_balance' => 'integer',
        ];
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    // Saldo yang benar-benar bisa dipakai user (belum termasuk yang sedang di-hold)
    public function getAvailableBalanceAttribute(): int
    {
        return $this->balance - $this->held_balance;
    }

}