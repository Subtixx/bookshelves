<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\Publisher;
use App\Models\Serie;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Spatie\Tags\Tag;

class ApiController extends Controller
{
    /**
     * @hideFromAPIDocumentation
     */
    public function __construct()
    {
        Route::bind(
            'author_slug',
            fn ($slug) => Author::whereSlug($slug)
                ->withCount('books', 'series')
                ->firstOrFail()
        );

        Route::bind('book_slug', fn ($slug) => Book::whereSlug($slug)->firstOrFail());

        Route::bind(
            'serie_slug',
            fn ($slug) => Serie::whereSlug($slug)
                ->withCount('books')
                ->firstOrFail()
        );

        Route::bind('tag_slug', fn ($slug) => Tag::where('slug->en', $slug)->firstOrFail());

        Route::bind(
            'publisher_slug',
            fn ($slug) => Publisher::whereSlug($slug)
                ->withCount('books')
                ->firstOrFail()
        );

        Route::bind('language_slug', fn ($slug) => Language::whereSlug($slug)->firstOrFail());

        Route::bind('post_slug', fn ($slug) => Post::whereSlug($slug)->firstOrFail());

        Route::bind('page_slug', fn ($slug) => Page::whereSlug($slug)->firstOrFail());

        Route::bind('user_slug', fn ($slug) => User::whereSlug($slug)->firstOrFail());
    }

    /**
     * @hideFromAPIDocumentation
     */
    public function home()
    {
        $list = RouteService::getList();

        return response()->json([
            'name' => config('app.name').' API',
            'version' => 'v1',
            'routes' => [
                'application' => $this->getRouteData(config('app.front_url'), 'Main application', false),
                'catalog' => $this->getRouteData('front.catalog', 'UI for eReader browser to get eBooks on it'),
                'opds' => $this->getRouteData('front.opds', 'OPDS API for application which use it'),
                'webreader' => $this->getRouteData('front.webreader', 'UI to read directly an eBook into browser'),
                'admin' => $this->getRouteData('admin.dashboard', 'For admin to manage data.'),
                'documentation' => $this->getRouteData(config('app.documentation_url'), 'Documentation for developers', false),
                'api-doc' => $this->getRouteData(route('scribe'), 'API documentation to use data on others applications', false),
                'repository' => $this->getRouteData(config('app.repository_url'), 'Repository of this application', false),
            ],
            'api' => $list,
        ], 200);
    }

    protected function getLang(Request $request)
    {
        $lang = $request->lang ? $request->lang : config('app.locale');
        App::setLocale($lang);
    }

    protected function getPaginationSize(Request $request): int
    {
        return $request->size ? $request->size : 32;
    }

    protected function getFull(Request $request): bool
    {
        return $request->parseBoolean('full');
    }

    /**
     * @hideFromAPIDocumentation
     *
     * @param mixed $isLaravelRoute
     */
    private function getRouteData(string $route, string $description, $isLaravelRoute = true)
    {
        return [
            'route' => $isLaravelRoute ? route($route) : $route,
            'description' => $description,
        ];
    }
}
