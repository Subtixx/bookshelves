<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Serie;
use App\Models\Author;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\SearchBookCollection;
use App\Http\Resources\SearchSerieCollection;
use App\Http\Resources\SearchAuthorCollection;

class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/search",
     *     tags={"search"},
     *     summary="List of search results",
     *     description="Search",
     *     @OA\Parameter(
     *         name="terms",
     *         in="query",
     *         description="String to search books",
     *         required=true,
     *         example="refuges",
     *         @OA\Schema(
     *           type="string",
     *         ),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $searchTermRaw = $request->input('terms');
        $searchTerm = mb_convert_encoding($searchTermRaw, 'UTF-8', 'UTF-8');
        if ($searchTermRaw) {
            $authors = Author::whereLike(['name', 'firstname', 'lastname'], $searchTerm)->get();
            $series = Serie::whereLike(['title'], $searchTerm)->get();
            $books = Book::whereLike(['title', 'authors.name', 'serie.title'], $searchTerm)->orderBy('serie_id')->orderBy('serie_number')->get();

            $authors = SearchAuthorCollection::collection($authors);
            $series = SearchSerieCollection::collection($series);
            $books = SearchBookCollection::collection($books);
            $collection = $authors->merge($series);
            $collection = $collection->merge($books);
            $collection->all();

            return response()->json([
                'data' => $collection,
            ]);
        }

        return response()->json(['error' => 'Need to have terms query parameter'], 401);
    }

    public function byBook(Request $request)
    {
        $searchTerm = $request->input('search-term');
        $books = Book::whereLike(['title'], $searchTerm)->orderBy('serie_id')->orderBy('serie_number')->get();

        return BookResource::collection($books);
    }

    public function byAuthor(Request $request)
    {
        $searchTerm = $request->input('search-term');
        $books = Book::whereLike(['author.name', 'author.firstname', 'author.lastname'], $searchTerm)->orderBy('serie_id')->orderBy('serie_number')->get();

        return BookResource::collection($books);
    }

    public function bySerie(Request $request)
    {
        $searchTerm = $request->input('search-term');
        $books = Book::whereLike(['serie.title'], $searchTerm)->orderBy('serie_id')->orderBy('serie_number')->get();

        return BookResource::collection($books);
    }
}
