<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Return a list of subjects (categories).
     * Supports optional ?popular=1 to order by number of books.
     */
    public function index(Request $request)
    {
        $q = Subject::query();

        // include count of related books to allow ordering by popularity
        $q->withCount('books');

        if ($request->boolean('popular')) {
            $q->orderByDesc('books_count');
        } else {
            $q->orderBy('name');
        }

        // pagination parameters (client may request a page to avoid returning huge lists)
        $page = max(1, (int) $request->get('page', 1));
        $perPage = max(1, min(200, (int) $request->get('page_size', 25)));

        try {
            $total = $q->count();
        } catch (\Exception $e) {
            $total = 0;
        }

        $results = $q->forPage($page, $perPage)->get()->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'books_count' => (int) ($s->books_count ?? 0),
            ];
        });

        return response()->json([
            'count' => $total,
            'page' => $page,
            'page_size' => $perPage,
            'results' => $results,
        ]);
    }
}
