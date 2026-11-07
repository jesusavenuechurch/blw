<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores partner photos privately as small square JPEGs, sized for the
 * scanner's result screen (about 30–60 KB each).
 */
class PartnerPhotoService
{
    public const SIZE = 480;

    private const DISK = 'local';

    public function store(Partner $partner, UploadedFile $file, bool $consented = true): void
    {
        $jpeg = $this->process($file->getRealPath());

        $path = "partner-photos/{$partner->id}-".Str::random(12).'.jpg';
        Storage::disk(self::DISK)->put($path, $jpeg);

        $old = $partner->photo_path;

        $partner->forceFill([
            'photo_path' => $path,
            'photo_updated_at' => now(),
            'photo_consent_at' => $consented ? ($partner->photo_consent_at ?? now()) : $partner->photo_consent_at,
        ])->saveQuietly();

        if ($old && $old !== $path) {
            Storage::disk(self::DISK)->delete($old);
        }
    }

    public function remove(Partner $partner): void
    {
        if ($partner->photo_path) {
            Storage::disk(self::DISK)->delete($partner->photo_path);
        }

        $partner->forceFill(['photo_path' => null, 'photo_updated_at' => now(), 'photo_consent_at' => null])->saveQuietly();
    }

    public function exists(Partner $partner): bool
    {
        return $partner->photo_path && Storage::disk(self::DISK)->exists($partner->photo_path);
    }

    public function absolutePath(Partner $partner): string
    {
        return Storage::disk(self::DISK)->path($partner->photo_path);
    }

    /** Centre-crops to a square, fixes phone rotation, resizes and re-encodes as JPEG. */
    private function process(string $path): string
    {
        $data = file_get_contents($path);
        $source = $data !== false ? @imagecreatefromstring($data) : false;

        if (! $source) {
            throw new RuntimeException('That file could not be read as an image.');
        }

        $source = $this->applyExifOrientation($source, $path);

        $w = imagesx($source);
        $h = imagesy($source);
        $side = min($w, $h);
        $target = min(self::SIZE, $side);

        $canvas = imagecreatetruecolor($target, $target);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2), $target, $target, $side, $side);

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = (string) ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        return $jpeg;
    }

    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $rotated = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        return $rotated ?: $image;
    }
}
