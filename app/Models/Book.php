<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // matches the restored dump's table name
    protected $table = 'books_book';
    protected $guarded = [];

    // relations
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'books_book_authors', 'book_id', 'author_id');
    }

    public function bookshelves()
    {
        return $this->belongsToMany(Bookshelf::class, 'books_book_bookshelves', 'book_id', 'bookshelf_id');
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'books_book_languages', 'book_id', 'language_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'books_book_subjects', 'book_id', 'subject_id');
    }

    // formats are stored per-book (one-to-many)
    public function formats()
    {
        return $this->hasMany(Format::class, 'book_id');
    }
}
