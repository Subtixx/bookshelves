<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Book\BookCollection;
use App\Http\Resources\EntityResource;
use App\Http\Resources\Serie\SerieResource;
use App\Models\Author;
use App\Models\Serie;
use Illuminate\Http\Request;
use Kiwilan\Steward\Queries\HttpQuery;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('series')]
class SerieController extends Controller
{
    #[Get('/', name: 'series.index')]
    public function index(Request $request)
    {
        return HttpQuery::make(Serie::class, $request)
            ->with(['media', 'authors', 'books', 'language'])
            ->collection()
        ;
    }

    #[Get('/{author_slug}/{serie_slug}', name: 'series.show')]
    public function show(Request $request, Author $author, Serie $serie)
    {
        return SerieResource::make($serie);
    }

    // #[Get('/{author_slug}/{serie_slug}/books', name: 'series.show.books')]
    // public function books(Request $request, Author $author, Serie $serie)
    // {
    //     $first = $request->boolean('first');
    //     $next = $request->get('next');

    //     if ($next) {
    //         $books = $serie->books->filter(fn ($book) => $book->volume > intval($next));

    //         if ($first) {
    //             $nextBook = $books->first();
    //             $nextBook?->load(['authors', 'media', 'language', 'serie']);

    //             if ($nextBook) {
    //                 return EntityResource::make($nextBook);
    //             }
    //         }

    //         if ($books->isNotEmpty()) {
    //             return EntityResource::collection($books);
    //         }

    //         // return abort(404);
    //         return response()->json(
    //             data: [
    //                 'data' => [],
    //             ],
    //             status: 200,
    //         );
    //     }

    //     $this->getLang($request);

    //     $books = $serie->books()
    //         ->with(['authors', 'media', 'language', 'serie'])
    //         ->orderBy('volume')
    //     ;
    //     $limit = $this->getPaginationLimit($request);
    //     $books = $this->getFull($request) ? $books->get() : $books->paginate($limit);

    //     dd($books);

    //     return BookCollection::collection($books);
    // }
}
