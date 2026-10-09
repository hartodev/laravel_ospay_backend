<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCommissions
 */
class Commissions extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
            'amount' => 'integer',
            'paid_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transactions::class);
    }
}
