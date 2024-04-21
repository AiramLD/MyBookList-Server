<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'pageCount'
    ];

    protected $primaryKey = 'id';

    public function userBooks(): hasMany
    {
        return $this->hasMany(UserBook::class, 'book_id', 'id');
    }
    
    public function users(): belongsToMany
    {
        return $this->belongsToMany(User::class, 'user_book');
    }
}

