<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Sistem',
                'email' => 'admin@warkopkayu.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Dzaky Poke',
                'email' => 'dzaky.poke@warkopkayu.test',
                'role' => 'owner',
            ],
            [
                'name' => 'Letoy',
                'email' => 'letoy@warkopkayu.test',
                'role' => 'staff',
            ],
            [
                'name' => 'Ketoy',
                'email' => 'ketoy@warkopkayu.test',
                'role' => 'staff',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
