<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',         
        'zipcode',        
        'address',        
        'building',       
        'is_profile_set', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function comments()
        {
            return $this->hasMany(Comment::class);
        }
        // 出品商品
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // いいね
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function orders()
        {
            return $this->hasMany(\App\Models\Order::class);
        }
}
