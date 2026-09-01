<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Downscales and re-encodes every uploaded image to WebP with GD before it
 * touches disk, so the guest menu never serves a raw phone photo. A menu screen
 * shows many photos at once on a phone — weight is what makes it lag, so this is
 * load-bearing, not cosmetic (ported from the main project's VenueImage/DishImage).
 *
 * Wired into Filament FileUpload via ->saveUploadedFileUsing(): the callback
 * returns the stored relative path, which Filament persists on the model. A
 * random filename per save doubles as cache-busting — the guest's browser and
 * any CDN see a new URL the instant a photo changes.
 *
 * Requires php-gd built with WebP support (gd_info()['WebP Support']).
 */
class ImageOptimizer
{
    public const MODE_CONTAIN = 'contain';   // downscale so the long edge fits $maxEdge
    public const MODE_SQUARE = 'square';      // centre-crop to a square, then cap at $maxEdge

    private const DISK = 'public';

    /**
     * Encode the upload to WebP under $directory and return the relative path.
     * Pass this straight to FileUpload::saveUploadedFileUsing().
     */
    public static function store(
        TemporaryUploadedFile $file,
        string $directory,
        string $mode = self::MODE_CONTAIN,
        int $maxEdge = 1600,
        int $quality = 82,
    ): string {
        return self::storeBinary($file->get(), $directory, $mode, $maxEdge, $quality);
    }

    /**
     * Same pipeline as store() but starting from raw image bytes already in
     * memory rather than a Livewire upload — used by the demo photo seeder,
     * which pulls stock images over HTTP.
     */
    public static function storeBinary(
        string $bytes,
        string $directory,
        string $mode = self::MODE_CONTAIN,
        int $maxEdge = 1600,
        int $quality = 82,
    ): string {
        $binary = self::encode($bytes, $mode, $maxEdge, $quality);

        $path = trim($directory, '/').'/'.Str::random(16).'.webp';
        Storage::disk(self::DISK)->put($path, $binary);

        return $path;
    }

    /**
     * Resize/crop $source (raw image bytes) and return WebP bytes, never
     * upscaling and preserving alpha so a transparent logo stays transparent.
     */
    private static function encode(string $source, string $mode, int $maxEdge, int $quality): string
    {
        // imagecreatefromstring auto-detects JPEG/PNG/WebP/GIF — one path for all.
        $image = imagecreatefromstring($source);

        if ($image === false) {
            // Filament validation already guaranteed an image; guard the edge anyway.
            abort(422, 'Unsupported image.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($mode === self::MODE_SQUARE) {
            $square = min($width, $height);
            $srcX = (int) (($width - $square) / 2);
            $srcY = (int) (($height - $square) / 2);
            $edge = min($square, $maxEdge);
            $dstW = $dstH = $edge;
            $srcW = $srcH = $square;
        } else {
            $srcX = $srcY = 0;
            $srcW = $width;
            $srcH = $height;
            $scale = min(1, $maxEdge / max($width, $height));
            $dstW = max(1, (int) round($width * $scale));
            $dstH = max(1, (int) round($height * $scale));
        }

        $canvas = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $transparent);

        imagecopyresampled($canvas, $image, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        imagewebp($canvas, null, $quality);
        $binary = (string) ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        return $binary;
    }
}
