<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commissions extends Model
{
    use HasFactory;
    
     protected $guarded = [];
    
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }
 
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
 
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}