<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    use HasFactory;

    protected $guarded = [];
     protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'price_user' => 'integer',
            'price_agent' => 'integer',
            'raw_response' => 'array',
        ];
    }
 
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
 
    // Ambil harga jual sesuai role pembeli, biar controller tinggal panggil ini
    public function priceForRole(string $role): int
    {
        return $role === 'agen' ? $this->price_agent : $this->price_user;
    }

    
}