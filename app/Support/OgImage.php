<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Social preview images: any uploaded picture becomes a 1200×630 JPEG (the size and format
 * every network and messenger accepts; WebP is not shown by all of them), made once and cached.
 */
class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    public const DEFAULT = 'images/og-default.jpg';

    /**
     * Public URL of the preview for a file on the public disk, or the site-wide default.
     */
    public static function url(?string $path, bool $contain = false): string
    {
        if (filled($path) && Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $generated = filled($path) ? self::generate($path, $contain) : null;

        return asset($generated ? 'storage/'.$generated : self::DEFAULT);
    }

    /**
     * Path (on the public disk) of the cached 1200×630 JPEG, or null when the source can't be used.
     * $contain fits the whole picture with margins (cut-out cars) instead of filling the frame.
     */
    public static function generate(string $path, bool $contain = false): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $target = 'og/'.md5($path.'|'.$disk->lastModified($path).'|'.(int) $contain).'.jpg';

        if ($disk->exists($target)) {
            return $target;
        }

        // GD keeps the whole bitmap in memory; an out-of-memory error can't be caught, so check first.
        $size = @getimagesize($disk->path($path));
        if (! $size || $size[0] * $size[1] > 40_000_000) {
            return null;
        }
        if ($size[0] * $size[1] > 8_000_000) {
            ini_set('memory_limit', '512M');
        }

        try {
            $source = imagecreatefromstring($disk->get($path));
        } catch (Throwable) {
            return null;
        }

        if (! $source) {
            return null;
        }

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        // Transparent pictures (car cards) are shown on white.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        $width = imagesx($source);
        $height = imagesy($source);

        if ($contain) {
            // Whole picture, centred, with a 10% margin.
            $scale = min(self::WIDTH * 0.8 / $width, self::HEIGHT * 0.8 / $height);
            $targetWidth = (int) round($width * $scale);
            $targetHeight = (int) round($height * $scale);
            imagecopyresampled(
                $canvas, $source,
                intdiv(self::WIDTH - $targetWidth, 2), intdiv(self::HEIGHT - $targetHeight, 2), 0, 0,
                $targetWidth, $targetHeight, $width, $height,
            );
        } else {
            // Cover: scale to fill the frame, crop the overflow evenly.
            $scale = max(self::WIDTH / $width, self::HEIGHT / $height);
            $cropWidth = (int) round(self::WIDTH / $scale);
            $cropHeight = (int) round(self::HEIGHT / $scale);
            imagecopyresampled(
                $canvas, $source,
                0, 0, intdiv($width - $cropWidth, 2), intdiv($height - $cropHeight, 2),
                self::WIDTH, self::HEIGHT, $cropWidth, $cropHeight,
            );
        }

        ob_start();
        imagejpeg($canvas, null, 85);
        $disk->put($target, ob_get_clean());

        return $target;
    }
}
