<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index(Request $request)
    {
        
        $q = Book::with(['authors', 'formats', 'languages', 'subjects', 'bookshelves']);
        // filter by comma-separated gutenberg ids or book ids
        if ($request->filled('ids')) {
            $ids = array_filter(array_map('trim', explode(',', $request->ids)));
            // try gutenberg_id first
            $q->whereIn('gutenberg_id', $ids);
        }

        // title search
        if ($request->filled('title')) {
            $q->where('title', 'ILIKE', '%' . $request->title . '%');
        }

        // author name search
        if ($request->filled('author')) {
            $author = $request->author;
            $q->whereHas('authors', function($qb) use ($author) {
                $qb->where('name', 'ILIKE', '%' . $author . '%');
            });
        }

        // language code (comma separated)
        if ($request->filled('languages')) {
            $codes = array_filter(array_map('trim', explode(',', $request->languages)));
            $q->whereHas('languages', function($qb) use ($codes) {
                $qb->whereIn('code', $codes);
            });
        }

        // subject/topic search
        if ($request->filled('topic')) {
            $topics = array_filter(array_map('trim', explode(',', $request->topic)));
            $q->whereHas('subjects', function($qb) use ($topics) {
                foreach ($topics as $t) {
                    $qb->orWhere('name', 'ILIKE', '%' . $t . '%');
                }
            });
        }

        // media_type filter
        if ($request->filled('media_type')) {
            $q->where('media_type', 'ILIKE', $request->media_type);
        }

        // mime_type check in formats
        if ($request->filled('mime_type')) {
            $mime = $request->mime_type;
            $q->whereHas('formats', function($qb) use ($mime) {
                $qb->where('mime_type', 'ILIKE', $mime . '%');
            });
        }

        // only books that have an image format (cover)
        if ($request->boolean('has_image')) {
            $q->whereHas('formats', function($qb) {
                $qb->where('mime_type', 'ILIKE', 'image/%');
            });
        }

        // ordering
        $q->orderByDesc('download_count');

        // pagination
        $page = max(1, (int) $request->get('page', 1));
        $perPage = max(1, min(100, (int) $request->get('page_size', 25)));
       
        try {
            $total = $q->count();
        } catch (QueryException $e) {
            // Log full exception for debugging
            Log::error('Books count query failed', [
                'message' => $e->getMessage(),
                'errorInfo' => $e->errorInfo ?? null,
            ]);

            // Return a helpful JSON response in debug mode so the client sees details
            if (config('app.debug')) {
                return response()->json([
                    'error' => 'Database query failed',
                    'message' => $e->getMessage(),
                    'errorInfo' => $e->errorInfo ?? null,
                ], 500);
            }

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
        $results = $q->forPage($page, $perPage)->get();
        // shape results similar to Gutendex API (lightweight)
        $data = $results->map(function($book) {
            return [
                'id' => $book->gutenberg_id,
                'title' => $book->title,
                'media_type' => $book->media_type,
                'download_count' => $book->download_count,
                'authors' => $book->authors->map(function($a){ return ['id' => $a->id, 'name' => $a->name]; })->values(),
                'languages' => $book->languages->pluck('code')->values(),
                'bookshelves' => $book->bookshelves->pluck('name')->values(),
                // limit subjects to avoid sending very large arrays to the client
                'subjects' => $book->subjects->pluck('name')->slice(0, 10)->values(),
                'formats' => $book->formats->mapWithKeys(function($f){ return [$f->mime_type => $f->url]; })->toArray(),
            ];
        });

        $baseUrl = $request->url();
        $queryParams = $request->query();
        $makeUrl = function($p) use ($baseUrl, $queryParams, $perPage) {
            $params = array_merge($queryParams, ['page' => $p, 'page_size' => $perPage]);
            return $baseUrl . '?' . http_build_query($params);
        };

        $next = ($page * $perPage) < $total ? $makeUrl($page + 1) : null;
        $previous = $page > 1 ? $makeUrl($page - 1) : null;

        return response()->json([
            'count' => $total,
            'next' => $next,
            'previous' => $previous,
            'results' => $data,
        ]);
    }
    
}
