<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\SubjectController;

Route::get('/books', [BookController::class, 'index']);
Route::get('/subjects', [SubjectController::class, 'index']);
