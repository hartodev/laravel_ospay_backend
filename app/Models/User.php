<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
  protected $hidden = [
        'password',
        'remember_token',
    ];
 
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
 
    // Role helper, dipakai di middleware & controller biar gak salah ketik string role
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }
 
    public function isAgen(): bool
    {
        return $this->role === 'agen';
    }
 
    public function isUser(): bool
    {
        return $this->role === 'user';
    }
 
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
 
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
 
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
 
    // Agen yang menaungi user ini (kalau ada)
    public function parentAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_agent_id');
    }
 
    // User-user yang dinaungi agen ini
    public function subUsers(): HasMany
    {
        return $this->hasMany(User::class, 'parent_agent_id');
    }
 
    // Komisi yang didapat kalau role-nya agen
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'agent_id');
    }
}