<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name'  => 'super admin',
            'email' => 'admin@mail.com',
            'password'  => Hash::make('admin123'),
            'user_type' => 'admin',
        ]);

    }
}
