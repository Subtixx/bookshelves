<?php

namespace App\Http\Controllers\Webreader;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Providers\CommonMarkProvider;

/**
 * @hideFromAPIDocumentation
 */
class WebreaderController extends Controller
{
    public function index()
    {
        $random_book = Book::inRandomOrder()->first();
        $cover = $random_book->getCoverThumbnailAttribute();
        $route = route('features.webreader.reader', ['author' => $random_book->meta_author, 'book' => $random_book->slug]);

        $markdown = CommonMarkProvider::generate('webreader/content/index.md');
        $content = $markdown->content;

        return view('pages.features.webreader.index', compact('random_book', 'cover', 'route', 'content'));
    }

    public function reader(string $author, string $book, ?string $page = null)
    {
        $author = Author::whereSlug($author)->firstOrFail();
        $book = Book::whereRelation('authors', 'name', '=', $author->name)->whereSlug($book)->firstOrFail();
        $epub = $book->getFirstMediaUrl('epubs');
        $epub_download = $epub;
        $epub_path = str_replace(config('app.url'), '', $epub);

        $title = $book->title;
        $title .= $book->serie ? ' ('.$book->serie->title.', vol. '.$book->volume.')' : '';
        $title .= ' by '.$book->authors_names;

        return view('pages.features.webreader.reader', compact('epub_path', 'epub_download', 'book', 'title'));
    }
}
