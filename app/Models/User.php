<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'user';

    protected $fillable = [
        'firebase_uid',
        'name',
        'email',
        'role',
        'password',
        'payment_customer_id',
        'shipping_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'shipping_address'  => 'array',
    ];

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}