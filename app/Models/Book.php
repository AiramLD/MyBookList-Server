<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public function userBooks()
    {
        return $this->hasMany(UserBook::class, 'book_id', 'id');
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_book');
    }
}

