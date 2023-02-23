<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();
 
        // Create Customer 1
        User::create(array(
            'name' => 'Customer 1',
            'email' => 'customer1@gmail.com',
            'is_admin' => false,
            'password' => Hash::make('0123456789'),
        ));

        // Create Customer 2
        User::create(array(
            'name' => 'Customer 2',
            'email' => 'customer2@gmail.com',
            'is_admin' => false,
            'password' => Hash::make('0123456789'),
        ));

        // Create Admin 1
        User::create(array(
            'name' => 'Admin 1',
            'email' => 'admin1@gmail.com',
            'is_admin' => true,
            'password' => Hash::make('0123456789'),
        ));

        // Create Admin 2
        User::create(array(
            'name' => 'Admin 2',
            'email' => 'admin2@gmail.com',
            'is_admin' => true,
            'password' => Hash::make('0123456789'),
        ));
    }
}
