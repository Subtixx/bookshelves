<?php

namespace App\Models;

use App\Enums\BookTypeEnum;
use App\Models\Traits\HasAuthors;
use App\Models\Traits\HasClassName;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasCovers;
use App\Models\Traits\HasFavorites;
use App\Models\Traits\HasLanguage;
use App\Models\Traits\HasSelections;
use App\Models\Traits\HasTagsAndGenres;
use App\Services\ParserEngine\ParserTools;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;

class Book extends Model implements HasMedia
{
    use HasFactory;
    use HasTags;
    use HasClassName;
    use HasCovers;
    use HasAuthors;
    use HasFavorites;
    use HasComments;
    use HasSelections;
    use HasLanguage;
    use HasTagsAndGenres;
    use Searchable;

    protected $fillable = [
        'title',
        'slug_sort',
        'slug',
        'contributor',
        'description',
        'released_on',
        'rights',
        'volume',
        'page_count',
        'maturity_rating',
        'disabled',
        'type',
        'identifier_isbn',
        'identifier_isbn13',
        'identifiers',
        'language_slug',
        'serie_id',
        'publisher_id',
    ];
    protected $with = [
        'language',
        'authors',
        'media',
    ];
    protected $appends = [
        'epub',
        'isbn',
    ];
    protected $casts = [
        'released_on' => 'datetime',
        'disabled' => 'boolean',
        'type' => BookTypeEnum::class,
        'identifiers' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('epubs');
    }

    /**
     * Manage EPUB files with spatie/laravel-medialibrary.
     */
    public function getEpubAttribute(): ?MediaExtended
    {
        // @phpstan-ignore-next-line
        return $this->getMedia('epubs')->first() ?? null;
    }

    public function getShowRelatedLinkAttribute(): string
    {
        return route('api.v1.books.related', [
            'author_slug' => $this->meta_author,
            'book_slug' => $this->slug,
        ]);
    }

    // public function getShowLinkAttribute(): string
    // {
    //     return route('api.v1.users.show', [
    //         'slug' => $this->slug,
    //     ]);
    // }

    public function scopeWhereDisallowSerie(Builder $query, string $has_not_serie): Builder
    {
        $has_not_serie = filter_var($has_not_serie, FILTER_VALIDATE_BOOLEAN);

        return $has_not_serie ? $query->whereDoesntHave('serie') : $query;
    }

    public function scopePublishedBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query
            ->whereBetween('released_on', [Carbon::parse($startDate), Carbon::parse($endDate)])
        ;
    }

    /**
     * Define sort name for `/api/books` with serie-volume-book.
     */
    public function getSortNameAttribute(): string
    {
        $serie = null;
        if ($this->serie) {
            // @phpstan-ignore-next-line
            $volume = strlen($this->volume) < 2 ? '0'.$this->volume : $this->volume;
            $serie = $this->serie->slug_sort.' '.$volume;
            $serie = Str::slug($serie).'_';
        }
        $title = Str::slug($this->slug_sort);

        return "{$serie}{$title}";
    }

    public function getIsbnAttribute(): ?string
    {
        return $this->identifier_isbn13 ?? $this->identifier_isbn;
    }

    public function scopeWhereSerieTitleIs(Builder $query, $title): Builder
    {
        return $query
            ->whereRelation('serie', 'title', '=', $title)
        ;
    }

    public function scopeWhereIsbnIs(Builder $query, $isbn): Builder
    {
        $query->where('identifier_isbn13', 'LIKE', "%{$isbn}%");
        $query->orWhere('identifier_isbn', 'LIKE', "%{$isbn}%");

        return $query;
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'picture' => $this->cover_thumbnail,
            'released_on' => $this->released_on,
            'author' => $this->authors_names,
            'isbn' => $this->identifier_isbn,
            'isbn13' => $this->identifier_isbn13,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }

    public function googleBook(): BelongsTo
    {
        return $this->belongsTo(GoogleBook::class);
    }

    public function updateSlug()
    {
        $serie_title = $this->serie ? $this->serie->title : '';

        $this->slug = Str::slug("{$this->title} {$this->language_slug}");
        $this->slug_sort = ParserTools::sortTitleWithSerie($this->title, $this->volume, $serie_title);
        $this->save();
    }
}
