<?php

namespace App\Providers\Bookshelves;

use App\Models\Book;

class CoverGenerator
{
    /**
     * Generate Book image from original cover string file.
     * Manage by spatie/laravel-medialibrary.
     *
     * @param array $metadata
     *
     * @return Book
     */
    public static function run(array $metadata): Book
    {
        $book = $metadata['book'];
        $cover = $metadata['cover'];

        if (! $book->image) {
            $disk = 'books';
            $book->addMediaFromString($cover)
                ->setName($book->slug)
                ->setFileName($book->slug.'.'.config('bookshelves.cover_extension'))
                ->toMediaCollection($disk, $disk);

            $book = $book->refresh();
        }

        return $book;
    }
}
