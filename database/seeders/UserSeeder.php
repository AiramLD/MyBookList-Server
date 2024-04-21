<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'pepe',
            'email' => 'pepe@gmail.com',
            'password' => Hash::make('1234567a'),
            'email_verified_at' => now(),
            'created_at' => now()
        ]);
    }
}
