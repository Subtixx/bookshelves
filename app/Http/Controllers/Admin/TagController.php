<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Queries\TagQuery;
use App\Http\Resources\Admin\TagResource;
use App\Models\TagExtend;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        return app(TagQuery::class)->make()
            ->paginateOrExport(fn ($data) => Inertia::render('tags/Index', $data))
        ;
    }

    public function fetch(Request $request)
    {
        return TagResource::collection(
            TagExtend::query()
                ->where('name', 'like', "%{$request->input('filter.q')}%")
                ->ordered()->get()
        );
    }

    // public function create()
    // {
    //     return Inertia::render('series/Create');
    // }

    // public function edit(Serie $serie)
    // {
    //     return Inertia::render('series/Edit', [
    //         'serie' => SerieResource::make($serie->load('media', 'books', 'tags', 'authors')),
    //     ]);
    // }

    // public function store(PostStoreRequest $request)
    // {
    //     $serie = Serie::create($request->all());

    //     return redirect()->route('admin.series')->with('flash.success', __('Serie created.'));
    // }

    // public function update(Book $book, PostUpdateRequest $request)
    // {
    //     $book->update($request->all());

    //     $book->syncTags($request->tags);

    //     if ($request->featured_image_delete) {
    //         $book->clearMediaCollection('featured-image');
    //     }

    //     if ($request->featured_image_file) {
    //         $book->addMediaFromRequest('featured_image_file')
    //             ->toMediaCollection('featured-image')
    //         ;
    //     }

    //     return redirect()->route('admin.books')->with('flash.success', __('Book updated.'));
    // }

    // public function toggle(Book $book, Request $request)
    // {
    //     $request->validate([
    //         'pin' => 'sometimes|boolean',
    //         'promote' => 'sometimes|boolean',
    //     ]);

    //     $book->update($request->only('pin', 'promote'));

    //     return redirect()->route('admin.books')->with('flash.success', __('Book updated.'));
    // }

    // public function types(Request $request)
    // {
    //     return TagResource::collection(
    //         TagExtend::query()
    //             ->where('name', 'like', "%{$request->input('filter.q')}%")
    //             ->ordered()->withCount('posts')->get()
    //     );
    // }

    public function destroy(TagExtend $tag)
    {
        $tag->delete();

        return redirect()->route('admin.tags')->with('flash.success', __('Tag deleted.'));
    }

    public function bulkDestroy(Request $request)
    {
        $count = TagExtend::query()->findMany($request->input('ids'))
            ->each(fn (TagExtend $tag) => $tag->delete())
            ->count()
        ;

        return redirect()->route('admin.tags')->with('flash.success', __(':count tags deleted.', ['count' => $count]));
    }
}
