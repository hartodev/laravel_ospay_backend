<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transactions extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'sell_price' => 'integer',
            'iak_response' => 'array',
            'checked_at' => 'datetime',
        ];
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }
}