<?php

namespace App\Http\Resources\Search;

use App\Utils\BookshelvesTools;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Book $resource
 */
class SearchBookResource extends JsonResource
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
                'entity' => 'book',
                'author' => $this->resource->meta_author,
                'slug'   => $this->resource->slug,
            ],
            'title'    => $this->resource->title,
            'subtitle' => $this->resource->serie?->title,
            'serie'    => [
                'title'  => $this->resource->serie?->title,
                'number' => $this->resource->volume,
            ],
            'picture' => [
                'base'      => $this->resource->image_thumbnail,
                'openGraph' => $this->resource->image_open_graph,
                'simple'    => $this->resource->image_simple,
                'color'     => $this->resource->image_color,
            ],
            'text' => BookshelvesTools::stringLimit($this->resource->description, 140),
        ];
    }
}
