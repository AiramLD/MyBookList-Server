<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('books')->insert([
            'id' => '6ms8DgAAQBAJ',
            'title' => 'Los jardines de la Luna (Malaz: El Libro de los Caídos 1)',
            'publishedDate' => '2017-03-22',
            'pageCount' => '776',
            'created_at' => now()
        ]);
    }
}
