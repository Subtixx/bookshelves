<?php

namespace App\Engines\Book\Converter;

use App\Engines\Book\Converter\Modules\AuthorConverter;
use App\Engines\Book\Converter\Modules\CoverConverter;
use App\Engines\Book\Converter\Modules\FileConverter;
use App\Engines\Book\Converter\Modules\IdentifiersConverter;
use App\Engines\Book\Converter\Modules\LanguageConverter;
use App\Engines\Book\Converter\Modules\PublisherConverter;
use App\Engines\Book\Converter\Modules\SerieConverter;
use App\Engines\Book\Converter\Modules\TagConverter;
use App\Enums\BookTypeEnum;
use App\Models\Book;
use Illuminate\Support\Carbon;
use Kiwilan\Ebook\Ebook;

/**
 * Create or improve a `Book` and relations.
 */
class BookConverter
{
    protected function __construct(
        protected Ebook $ebook,
        protected ?Book $book = null,
    ) {
    }

    /**
     * Set Book from Ebook.
     */
    public static function make(Ebook $ebook, BookTypeEnum $type, ?Book $book = null): self
    {
        $self = new self($ebook);
        $self->parse($type, $book);

        return $self;
    }

    public function book(): ?Book
    {
        return $this->book;
    }

    private function parse(BookTypeEnum $type, ?Book $book): self
    {
        if ($book) {
            $this->checkBook($type);
        }

        $identifiers = IdentifiersConverter::toCollection($this->ebook);

        if (! $book) {
            $this->book = Book::create([
                'title' => $this->ebook->title(),
                'uuid' => uniqid(),
                'slug' => $this->ebook->metaTitle()->slugLang(),
                'slug_sort' => $this->ebook->metaTitle()->slugSortWithSerie(),
                'contributor' => $this->ebook->extra('contributor'),
                'released_on' => $this->ebook->publishDate()?->format('Y-m-d'),
                'description' => $this->ebook->description(2000),
                'rights' => $this->ebook->copyright(255),
                'volume' => $this->ebook->volume(),
                'type' => $type,
                'page_count' => $this->ebook->pagesCount(),
                'physical_path' => $this->ebook->path(),
                'isbn10' => $identifiers->get('isbn10') ?? null,
                'isbn13' => $identifiers->get('isbn13') ?? null,
                'identifiers' => json_encode($identifiers),
            ]);
        }

        if (empty($this->book?->title)) {
            $this->book = null;

            return $this;
        }

        $this->syncAuthors();
        $this->syncTags();
        $this->syncPublisher();
        $this->syncLanguage();
        $this->syncSerie($type);
        $this->syncIdentifiers();
        $this->syncCover($this->ebook);
        // $this->syncFile($this->ebook);

        return $this;
    }

    private function syncAuthors(): self
    {
        $authors = AuthorConverter::toCollection($this->ebook);

        if ($authors->isNotEmpty()) {
            $this->book->authorMain()->associate($authors->first());
            $this->book?->authors()->sync($authors->pluck('id'));
        }

        return $this;
    }

    private function syncTags(): self
    {
        $tags = TagConverter::toCollection($this->ebook);

        if ($tags->isNotEmpty()) {
            $this->book?->tags()->sync($tags->pluck('id'));
        }

        return $this;
    }

    private function syncPublisher(): self
    {
        $publisher = PublisherConverter::toModel($this->ebook);
        $this->book?->publisher()->associate($publisher);
        $this->book?->save();

        return $this;
    }

    private function syncLanguage(): self
    {
        $language = LanguageConverter::toModel($this->ebook);
        $this->book?->language()->associate($language);
        $this->book?->save();

        return $this;
    }

    private function syncSerie(BookTypeEnum $type): self
    {
        $serie = SerieConverter::toModel($this->ebook, $type)
            ->associate($this->book)
        ;

        if ($serie) {
            $this->book?->serie()->associate($serie);
            $this->book?->save();
        }

        return $this;
    }

    private function syncIdentifiers(): self
    {
        $identifiers = IdentifiersConverter::toCollection($this->ebook);

        $this->book->isbn10 = $identifiers->get('isbn10') ?? null;
        $this->book->isbn13 = $identifiers->get('isbn13') ?? null;
        $this->book->identifiers = $identifiers;
        $this->book->save();

        return $this;
    }

    private function syncCover(Ebook $ebook): void
    {
        CoverConverter::make($ebook, $this->book);
    }

    private function syncFile(Ebook $ebook): void
    {
        FileConverter::make($ebook, $this->book);
    }

    private function checkBook(BookTypeEnum $type): self
    {
        if (! $this->book) {
            return $this;
        }

        if (! $this->book->slug_sort && $this->ebook->series() && ! $this->book->serie) {
            $this->book->slug_sort = $this->ebook->metaTitle()->serieSlugSort();
        }

        if (! $this->book->contributor) {
            $this->book->contributor = $this->ebook->extra('contributor') ?? null;
        }

        if (! $this->book->released_on) {
            $this->book->released_on = Carbon::parse($this->ebook->publishDate());
        }

        if (! $this->book->rights) {
            $this->book->rights = $this->ebook->copyright();
        }

        if (! $this->book->description) {
            $this->book->description = $this->ebook->description();
        }

        if (! $this->book->volume) {
            $this->book->volume = $this->ebook->volume();
        }

        if (null === $this->book->type) {
            $this->book->type = $type;
        }

        return $this;
    }

    // public static function setDescription(Book $book, ?string $language_slug, ?string $description): Book
    // {
    //     if (null !== $description && null !== $language_slug && '' === $book->getTranslation('description', $language_slug)) {
    //         $book->setTranslation('description', $language_slug, $description);
    //     }

    //     return $book;
    // }
}
