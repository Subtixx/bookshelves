<?php

namespace App\Http\Resources\Search;

use App\Utils\BookshelvesTools;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Serie $resource
 */
class SearchSerieResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'meta' => [
                'entity' => 'serie',
                'author' => $this->resource->meta_author,
                'slug' => $this->resource->slug,
            ],
            'title' => $this->resource->title,
            'authors' => $this->resource->authors,
            'cover' => [
                'thumbnail' => $this->resource->cover_thumbnail,
                'og' => $this->resource->cover_og,
                'simple' => $this->resource->cover_simple,
                'color' => $this->resource->cover_color,
            ],
            'text' => BookshelvesTools::stringLimit($this->resource->description, 140),
            'language' => $this->resource->language->slug,
        ];
    }
}
