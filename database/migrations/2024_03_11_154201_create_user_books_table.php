<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_books', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('book_id')->constrained()->onDelete('cascade'); // Cambiar el tipo de dato a 'string'
            $table->integer('progress');
            $table->integer('score');
            $table->string('status');
            $table->timestamps();
    
            $table->unique(['user_id', 'book_id']);
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_books');
    }
};
