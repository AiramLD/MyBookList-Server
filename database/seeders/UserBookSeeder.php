<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_books')->insert([
            'user_id' => '1',
            'book_id' => '6ms8DgAAQBAJ',
            'progress' => '776',
            'score' => '5',
            'state' => 'completed',
            'created_at' => now()
        ]);
    }
}
