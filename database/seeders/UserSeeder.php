<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
       public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@angel-lounge.com',
            'password' => Hash::make('password123'), // Change this!
            'email_verified_at' => now(),
        ]);

        // Add more users if needed
        User::create([
            'name' => 'Test User',
            'email' => 'test@angel-lounge.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }
}
