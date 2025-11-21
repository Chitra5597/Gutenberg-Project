<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    protected $table = 'books_format';
    protected $guarded = [];

    public $timestamps = false;

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
