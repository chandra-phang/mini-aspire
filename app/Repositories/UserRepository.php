<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRepository
{
    public function findById($id)
    {
        return User::find($id);
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function create($data)
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'is_admin' => $data->isAdmin,
            'password' => Hash::make($data->password)
        ]);

        return $user;
    }

    public function updateCashBalance($user, $cashBalance)
    {
        return $user->Update(['cash_balance' => $cashBalance]);
    }
}