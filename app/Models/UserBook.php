<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'progress',
        'score',
        'state'
    ];

    public function book(): belongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
