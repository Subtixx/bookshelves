<?php

namespace App\Models;

use App\Engines\ParserEngine;
use App\Enums\BookTypeEnum;
use App\Models\Traits\HasAuthors;
use App\Models\Traits\HasBooksCollection;
use App\Models\Traits\HasClassName;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasCovers;
use App\Models\Traits\HasFavorites;
use App\Models\Traits\HasLanguage;
use App\Models\Traits\HasSelections;
use App\Models\Traits\HasTagsAndGenres;
use App\Models\Traits\HasWikipediaItem;
use App\Utils\BookshelvesTools;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property null|int $books_count
 */
class Serie extends Model implements HasMedia
{
    use HasFactory;
    use HasClassName;
    use HasCovers;
    use HasAuthors;
    use HasFavorites;
    use HasComments;
    use HasSelections;
    use HasLanguage;
    use HasTagsAndGenres;
    use Searchable;
    use HasWikipediaItem;
    use HasBooksCollection;

    protected $fillable = [
        'title',
        'slug_sort',
        'slug',
        'type',
        'description',
        'link',
    ];

    protected $casts = [
        'type' => BookTypeEnum::class,
    ];

    protected $with = [
        'language',
        'authors',
        'media',
    ];

    public function getShowOpdsLinkAttribute(): string
    {
        return route('front.opds.series.show', [
            'version' => 'v1.2',
            'author' => $this->meta_author,
            'serie' => $this->slug,
        ]);
    }

    public function getContentOpdsAttribute(): string
    {
        return $this->books->count().' books';
    }

    public function getShowBooksLinkAttribute(): string
    {
        return route('api.series.show.books', [
            'author_slug' => $this->meta_author,
            'serie_slug' => $this->slug,
        ]);
    }

    public function getSizesAttribute(): object
    {
        $serie = Serie::whereSlug($this->slug)
            ->with('books.media')
            ->first()
        ;

        return $this->getSizesList($serie);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'picture' => $this->cover_thumbnail,
            'author' => $this->authors_names,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get Books into Serie, by volume order.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class)->orderBy('volume');
    }

    /**
     * Get available Books into Serie, by volume order.
     */
    public function booksAvailable(): HasMany
    {
        return $this->hasMany(Book::class)
            ->where('disabled', false)
            ->orderBy('volume')
        ;
    }

    public function wikipediaItem(): BelongsTo
    {
        return $this->belongsTo(WikipediaItem::class);
    }

    public function updateSlug()
    {
        $this->slug = Str::slug("{$this->title} {$this->language_slug}");
        $this->slug_sort = ParserEngine::generateSortTitle($this->title);
        $this->save();
    }
}
