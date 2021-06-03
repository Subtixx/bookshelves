<?php

namespace App\Http\Controllers;

use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\Image\Exceptions\InvalidManipulation;

class ImageController extends Controller
{
    /**
     * @param bool $crop = true
     *
     * @throws InvalidManipulation
     */
    public static function thumbnail(string $size, string $path, bool $crop = true): mixed
    {
        $dimensions = config("image.thumbnails.$size");

        if (! $dimensions) {
            return response()->json(['error' => 'Dimensions not found'], 401);
        }

        $thumbnail = get_thumbnail($path, $size);

        // dump(pathinfo($thumbnail['filepath']));
        $thumbnail['filepath'] = str_replace('.jpeg', '.jpg', $thumbnail['filepath']);
        // dd($thumbnail['filepath']);
        if (! $thumbnail['resolved']) {
            if ($crop) {
                Image::load("$path")
                    ->fit(Manipulations::FIT_MAX, $dimensions['width'], $dimensions['height'])
                    ->save($thumbnail['filepath']);
            } else {
                Image::load("$path")
                    ->save($thumbnail['filepath']);
            }
        }

        return response()->file($thumbnail['filepath']);
    }
}
