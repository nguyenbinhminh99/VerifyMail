<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username' => 'admin',
            'password' => 'admin@123',
            'email' => 'admin@gmail.com',
            'firstname' => 'Minh',
            'lastname' => 'Nguyen',
            'phone_number' => '0834966966',
            'gender' => 1,
        ])->assignRole('admin');

        User::create([
            'username' => 'user',
            'password' => 'user@123',
            'email' => 'user@gmail.com',
            'firstname' => 'Minh',
            'lastname' => 'Nguyen',
            'phone_number' => '0834966966',
            'gender' => 1,
        ])->assignRole('user');


    }
}
