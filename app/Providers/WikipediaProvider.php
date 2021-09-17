<?php

namespace App\Providers;

use App\Utils\HttpTools;
use App\Utils\BookshelvesTools;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Use Wikipedia to get some data about authors and series.
 * Doc in french: https://korben.info/comment-utiliser-lapi-de-recherche-de-wikipedia.html.
 * @package App\Providers
 */
class WikipediaProvider
{
    public function __construct(
        public ?string $model_name = null,
        public ?string $search_url = null,
        public ?string $search_query = null,
        public ?string $lang = 'en',
        public ?int $model_id = 0,
        public ?string $page_id = null,
        // public array $response,
        public ?string $extract = null,
        public ?string $page_url = null,
        public ?string $picture_url = null
    ) {
    }

    /**
     * Request to Wikipedia from query.
     * - First request is search to get a list of pages
     * - Second request is first result of pages
     *
     * @return WikipediaProvider
     */
    public static function create(string $query, string $lang = 'en')
    {
        $search = self::getSearchUrl($query, $lang);
        $response = Http::get($search);
        $provider = self::getSearchProvider($response);

        $data = self::getDataUrl($lang, $provider->page_id);
        $response = Http::get($data);
        $provider = self::getDataProvider($response, $provider);

        return $provider;
    }
    
    /**
     * Request to Wikipedia from Collection of Model with Guzzle Http Pool.
     * It's async method.
     *
     * @param Collection $list
     * @param string $attribute which used to search
     * @return WikipediaProvider[]
     */
    public static function make(Collection $list, string $attribute): array
    {
        $providers = WikipediaProvider::createSearchAsync($list, $attribute);
        $providers = WikipediaProvider::createDataAsync($providers);

        return $providers;
    }

    /**
     * Async Wikipedia API calls
     *
     * @param Collection $list
     * @return WikipediaProvider[]
     */
    public static function createSearchAsync(Collection $list, string $attribute): array
    {
        $urlList = [];
        $model_name = $list[0];
        $model_name = $model_name->getClassNamespace();
        foreach ($list as $key => $model) {
            $url = self::getSearchUrl($model->$attribute, $model->language_slug ? $model->language_slug : 'en');
            $urlList[$model->id] = $url;
        }
        $responses = HttpTools::async($urlList);
        
        $providers = [];
        foreach ($responses as $ID => $response) {
            $provider = self::getSearchProvider($response, $ID, $model_name);
            $providers[$ID] = $provider;
        }
        
        return $providers;
    }

    public static function getSearchUrl(string $query, string $lang = 'en'):string
    {
        $query = str_replace(' ', '%20', $query);
        $url = "https://$lang.wikipedia.org/w/api.php?action=query&list=search&srsearch=$query&format=json";
        
        return $url;
    }
    
    /**
     * Get results from query URL
     *
     * @param Response $response
     * @return WikipediaProvider
     */
    public static function getSearchProvider(Response $response, ?int $id = 0, ?string $model_name = null): WikipediaProvider
    {
        if ($model_name) {
            $model = $model_name::find($id);
        }
        $uri = $response->transferStats->getRequest()->getUri();
        parse_str($uri->getQuery(), $params);

        $url = HttpTools::getQueryFromResponse($response);
        $provider = new WikipediaProvider(
            lang: $model->language_slug ? $model->language_slug : 'en',
            model_name: $model_name,
            search_url: $url,
            search_query: $params['srsearch'],
            model_id: $id,
        );
        
        $pageId = false;
        $results = $response->json();
        // try to get writer
        if (array_key_exists('query', $results) && array_key_exists('search', $results['query'])) {
            $search = $results['query']['search'];
            // keep first results
            $search = array_slice($search, 0, 5);
            // search if writer exist
            foreach ($search as $key => $result) {
                if (strpos($result['title'], '(writer)')) {
                    $pageId = $result['pageid'];
    
                    break;
                }
            }
        }
        // default method: first result
        if (! $pageId && array_key_exists(0, $search)) {
            $pageId = $search[0]['pageid'];
        }

        if ($pageId) {
            $provider->page_id = $pageId;
        }

        return $provider;
    }

    /**
     * Get Data from page
     * @param WikipediaProvider[] $providers
     * @return WikipediaProvider[]
     */
    public static function createDataAsync(array $providers): array
    {
        $urlList = [];
        foreach ($providers as $key => $provider) {
            if ($provider->page_id) {
                $url = self::getDataUrl($provider->lang, $provider->page_id);
                $urlList[$provider->model_id] = $url;
            }
        }
        $responses = HttpTools::async($urlList);
        
        $providers = collect($providers);
        $providers_data = [];
        foreach ($responses as $ID => $response) {
            $current = $providers->firstWhere('model_id', $ID);
            $provider = self::getDataProvider($response, $current);
            array_push($providers_data, $provider);
        }
        
        return $providers_data;
    }
    
    public static function getDataUrl(string $lang, string $pageId)
    {
        $url = "http://$lang.wikipedia.org/w/api.php?action=query&prop=info&pageids=$pageId&inprop=url&format=json&prop=info|extracts|pageimages&pithumbsize=512";

        return $url;
    }

    public static function getDataProvider(Response $response, WikipediaProvider $provider): WikipediaProvider
    {
        $response = $response->json();
        
        if (array_key_exists('query', $response) && array_key_exists('pages', $response['query']) && array_key_exists($provider->page_id, $response['query']['pages'])) {
            $page = $response['query']['pages'][$provider->page_id];
            if (array_key_exists('extract', $page)) {
                $provider->extract = BookshelvesTools::stringLimit($page['extract'], 2000);
            }
            if (array_key_exists('thumbnail', $page) && array_key_exists('source', $page['thumbnail'])) {
                $provider->picture_url = $page['thumbnail']['source'];
            }
            if (array_key_exists('fullurl', $page)) {
                $provider->page_url = $page['fullurl'];
            }
        }

        return $provider;
    }

    /**
     * GETTERS
     */

    /**
     * Get picture from WikipediaProvider picture_url
     *
     * @return string|null
     */
    public function getPictureFile(): string | null
    {
        $picture = null;
        try {
            $picture = Http::get($this->picture_url)->body();
        } catch (\Throwable $th) {
            // BookshelvesTools::console(__METHOD__, $th);
        }

        return $picture;
    }
}
