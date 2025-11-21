<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'books_author';
    protected $guarded = [];

    public $timestamps = false;

    public function books()
    {
        return $this->belongsToMany(Book::class, 'books_book_authors', 'author_id', 'book_id');
    }
}
