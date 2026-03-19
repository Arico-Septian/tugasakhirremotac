<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'password',
        'role',
        'is_active',
        'is_online',
        'last_login_at',
        'last_logout_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOperator()
    {
        return $this->role === 'operator';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }
}
