<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBook extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'book_title', 'progress', 'num_pages', 'score'];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
