<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalAccesTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      DB::table('personal_access_tokens')->insert([
        'id' => '1',
        'tokenable_type' => 'App\Models\User',
        'tokenable_id' => '1',
        'name' => 'remember_token',
        'token' => '12345',
        'abilities' => '["*"]',
        'last_used_at' => now(),
        'expires_at' => now()->addMonth(),
        'created_at' => now(),
        'updated_at' => now()
      ]);
      DB::table('personal_access_tokens')->insert([
        'id' => '2',
        'tokenable_type' => 'App\Models\User',
        'tokenable_id' => '1',
        'name' => 'remember_token',
        'token' => '1111',
        'abilities' => '["*"]',
        'last_used_at' => now(),
        'expires_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
      ]);
    }
}
