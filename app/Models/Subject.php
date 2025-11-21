<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'books_subject';
    protected $guarded = [];

    public $timestamps = false;

    public function books()
    {
        return $this->belongsToMany(Book::class, 'books_book_subjects', 'subject_id', 'book_id');
    }
}
