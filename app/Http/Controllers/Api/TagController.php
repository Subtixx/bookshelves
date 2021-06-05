<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Spatie\Tags\Tag;
use App\Models\Author;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookLightResource;
use App\Http\Resources\Tag\TagLightResource;
use App\Http\Resources\Tag\TagResource;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::whereType('tag')->orderBy('name->en')->get();

        return TagLightResource::collection($tags);
    }

    public function show(string $tag)
    {
        $tag = Tag::whereSlug($tag)->first();

        return TagLightResource::make($tag);
    }

    public function genres()
    {
        $genres = Tag::whereType('genre')->get();

        return TagResource::collection($genres);
    }
}
