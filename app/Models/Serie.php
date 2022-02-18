<?php

namespace App\Models;

use App\Models\Traits\HasAuthors;
use App\Models\Traits\HasClassName;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasCovers;
use App\Models\Traits\HasFavorites;
use App\Models\Traits\HasLanguage;
use App\Models\Traits\HasSelections;
use App\Models\Traits\HasTagsAndGenres;
use App\Models\Traits\HasWikipediaItem;
use App\Services\ParserEngine\ParserTools;
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

    protected $fillable = [
        'title',
        'slug_sort',
        'slug',
        'description',
        'link',
    ];

    protected $with = [
        'language',
        'authors',
        'media',
    ];

    public function getContentOpdsAttribute(): string
    {
        return $this->books->count().' books';
    }

    public function getShowBooksLinkAttribute(): string
    {
        return route('api.v1.series.show.books', [
            'author_slug' => $this->meta_author,
            'serie_slug' => $this->slug,
        ]);
    }

    public function getSizeAttribute(): string
    {
        $size = [];
        $serie = Serie::whereSlug($this->slug)->with('books.media')->first();
        $books = $serie->books;
        foreach ($books as $key => $book) {
            array_push($size, $book->epub->size);
        }
        $size = array_sum($size);

        return BookshelvesTools::humanFilesize($size);
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

    public function wikipediaItem(): BelongsTo
    {
        return $this->belongsTo(WikipediaItem::class);
    }

    public function updateSlug()
    {
        $this->slug = Str::slug("{$this->title} {$this->language_slug}");
        $this->slug_sort = ParserTools::getSortString($this->title);
        $this->save();
    }
}
