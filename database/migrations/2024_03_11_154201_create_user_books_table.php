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
            $table->id();
            
            $table->unsignedBigInteger('user_id');
            $table->string('book_id');

            $table->integer('progress');
            $table->integer('score');
            $table->enum('state', ['reading','completed','pending','paused','dropped']);
            $table->timestamps();
    
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onDelete('cascade');

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
