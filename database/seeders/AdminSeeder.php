<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'admin',
    'password' => Hash::make('123456'),
    'role' => 'admin',
    'is_active' => 1
]);
