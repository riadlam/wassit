<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class WebpImageService
{
    public function __construct(
        private int $quality = 82,
        private int $maxDimension = 1920,
    ) {}

    /**
     * Convert an uploaded image to compressed WebP and store it under public/storage.
     *
     * @return string Relative path under the public disk (e.g. account_images/foo.webp)
     */
    public function storeUploadedAsWebp(UploadedFile $file, string $directory, ?int $maxDimension = null): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Invalid image upload.');
        }

        $mime = (string) $file->getMimeType();
        if (! str_starts_with($mime, 'image/')) {
            throw new RuntimeException('File is not an image.');
        }

        $directory = trim(str_replace('\\', '/', $directory), '/');
        $absoluteDir = public_path('storage/'.$directory);
        if (! is_dir($absoluteDir) && ! mkdir($absoluteDir, 0755, true) && ! is_dir($absoluteDir)) {
            throw new RuntimeException('Could not create image directory.');
        }

        $filename = time().'_'.Str::lower(Str::random(10)).'.webp';
        $absolutePath = $absoluteDir.DIRECTORY_SEPARATOR.$filename;
        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        $this->convertPathToWebp($sourcePath, $absolutePath, $maxDimension ?? $this->maxDimension);

        return $directory.'/'.$filename;
    }

    public function convertPathToWebp(string $sourcePath, string $destinationPath, ?int $maxDimension = null): void
    {
        $maxDimension = $maxDimension ?? $this->maxDimension;

        if ($this->supportsImagick()) {
            $this->convertWithImagick($sourcePath, $destinationPath, $maxDimension);

            return;
        }

        if ($this->supportsGd()) {
            $this->convertWithGd($sourcePath, $destinationPath, $maxDimension);

            return;
        }

        throw new RuntimeException(
            'WebP conversion requires the PHP GD or Imagick extension. Enable one of them on the server.'
        );
    }

    public function supportsWebp(): bool
    {
        return $this->supportsGd() || $this->supportsImagick();
    }

    private function supportsGd(): bool
    {
        return extension_loaded('gd')
            && function_exists('imagewebp')
            && function_exists('imagecreatefromstring');
    }

    private function supportsImagick(): bool
    {
        return extension_loaded('imagick') && class_exists(\Imagick::class);
    }

    private function convertWithGd(string $sourcePath, string $destinationPath, int $maxDimension): void
    {
        $binary = @file_get_contents($sourcePath);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Could not read uploaded image.');
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new RuntimeException('Could not decode uploaded image.');
        }

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }
        if (function_exists('imagealphablending')) {
            imagealphablending($image, true);
        }
        if (function_exists('imagesavealpha')) {
            imagesavealpha($image, true);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $resized = $this->resizeGd($image, $width, $height, $maxDimension);

        $ok = imagewebp($resized, $destinationPath, $this->quality);
        imagedestroy($resized);
        if ($resized !== $image) {
            imagedestroy($image);
        }

        if (! $ok || ! is_file($destinationPath)) {
            throw new RuntimeException('Could not write WebP image.');
        }
    }

    /**
     * @param  \GdImage|resource  $image
     * @return \GdImage|resource
     */
    private function resizeGd($image, int $width, int $height, int $maxDimension)
    {
        $longest = max($width, $height);
        if ($longest <= $maxDimension || $maxDimension <= 0) {
            return $image;
        }

        $scale = $maxDimension / $longest;
        $newW = max(1, (int) round($width * $scale));
        $newH = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($newW, $newH);
        if ($canvas === false) {
            return $image;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $newW, $newH, $transparent);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);

        return $canvas;
    }

    private function convertWithImagick(string $sourcePath, string $destinationPath, int $maxDimension): void
    {
        $image = new \Imagick($sourcePath);
        if (method_exists($image, 'autoOrient')) {
            $image->autoOrient();
        }

        $image->setImageBackgroundColor('transparent');
        $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($this->quality);
        $image->setOption('webp:method', '6');

        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $longest = max($width, $height);
        if ($maxDimension > 0 && $longest > $maxDimension) {
            $image->thumbnailImage(
                $width >= $height ? $maxDimension : 0,
                $height > $width ? $maxDimension : 0,
                true
            );
        }

        if (! $image->writeImage($destinationPath)) {
            $image->clear();
            $image->destroy();
            throw new RuntimeException('Could not write WebP image.');
        }

        $image->clear();
        $image->destroy();
    }
}
