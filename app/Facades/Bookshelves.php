<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Bookshelves
 *
 * @method static string superAdminEmail()
 * @method static string superAdminPassword()
 * @method static bool apiGoogleBooks()
 * @method static bool apiOpenLibrary()
 * @method static bool apiComicVine()
 * @method static bool apiIsbn()
 * @method static bool apiWikipedia()
 * @method static string appVersion()
 * @method static string analyzerEngine()
 * @method static string analyzerDebug()
 * @method static bool authorWikipediaExact()
 * @method static string exceptionParserLog()
 * @method static bool convertCovers()
 * @method static string imageDisk()
 * @method static string imageCollection()
 * @method static string imageDriver()
 * @method static string imageFormat()
 * @method static int imageMaxHeight()
 * @method static bool imageConversion()
 * @method static array{width: int, height: int} imageCoverStandard(bool $square = false)
 * @method static array{width: int, height: int} imageCoverThumbnail(bool $square = false)
 * @method static array{width: int, height: int} imageCoverSocial(bool $square = false)
 * @method static array{width: int, height: int} imageCoverOpds(bool $square = false)
 */
class Bookshelves extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Bookshelves::class;
    }
}
