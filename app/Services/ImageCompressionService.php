<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Compress and resize an image, saving it as a JPEG.
     * Used by Machine Passport upload workflow.
     *
     * @param string $sourcePath Path to the source image file
     * @param string $targetPath Path where the compressed image should be saved
     * @param int $maxSide Maximum length of the longest side in pixels
     * @param int $quality Compression quality (1-100)
     * @return string Target path
     * @throws \Exception
     */
    public function compress(string $sourcePath, string $targetPath, int $maxSide = 1600, int $quality = 65): string
    {
        // 1. Detect image format
        $info = getimagesize($sourcePath);
        if (!$info) {
            throw new \Exception("Format file gambar tidak didukung atau file rusak.");
        }

        $mime = $info['mime'];

        // 2. Load image based on MIME type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/pjpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception("Format gambar tidak didukung: {$mime}");
        }

        if (!$image) {
            throw new \Exception("Gagal memuat data gambar.");
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // 3. Compute new dimensions if necessary (maintain aspect ratio)
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxSide || $height > $maxSide) {
            if ($width >= $height) {
                $newWidth = $maxSide;
                $newHeight = (int) round(($height / $width) * $maxSide);
            } else {
                $newHeight = $maxSide;
                $newWidth = (int) round(($width / $height) * $maxSide);
            }
        }

        // 4. Create destination image resource
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Fill with white background (to avoid black background when flattening transparent PNG/WEBP to JPEG)
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $white);

        // Copy and resize
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Free source memory
        imagedestroy($image);

        // 5. Ensure output directory exists
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Save as JPEG
        $success = imagejpeg($newImage, $targetPath, $quality);
        imagedestroy($newImage);

        if (!$success) {
            throw new \Exception("Gagal menyimpan gambar terkompresi ke disk.");
        }

        return $targetPath;
    }

    /**
     * Compress an uploaded image using GD library, downscale to max 1000px, and save as JPEG at 60% quality.
     * Used by Mobile Maintenance Execution.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string Relative path to public storage (e.g., 'photos/maintenance/filename.jpg')
     */
    public function compressAndStore(UploadedFile $file, string $directory = 'photos/maintenance'): string
    {
        $mime = $file->getMimeType();
        $sourceImage = null;

        // 1. Create source image resource based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($file->getRealPath());
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($file->getRealPath());
                break;
            default:
                // Fallback to try loading it anyway
                $sourceImage = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                break;
        }

        if (!$sourceImage) {
            // If GD creation fails, store raw file as fallback
            return $file->store($directory, 'public');
        }

        // 2. Calculate dimensions to fit inside a 1000x1000 box
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        $maxSize = 1000;

        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxSize || $origHeight > $maxSize) {
            if ($origWidth > $origHeight) {
                $newWidth = $maxSize;
                $newHeight = (int) round(($origHeight * $maxSize) / $origWidth);
            } else {
                $newHeight = $maxSize;
                $newWidth = (int) round(($origWidth * $maxSize) / $origHeight);
            }
        }

        // 3. Resample image
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNGs and WEBPs
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        // 4. Save to temporary file as JPEG with 60% quality
        $tempPath = tempnam(sys_get_temp_dir(), 'compressed_img_');
        imagejpeg($targetImage, $tempPath, 60);

        // 5. Store in public disk using Laravel Storage
        $filename = Str::random(40) . '.jpg';
        $targetPath = $directory . '/' . $filename;
        
        Storage::disk('public')->put($targetPath, fopen($tempPath, 'r'));

        // Clean up
        @unlink($tempPath);
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $targetPath;
    }

    /**
     * Compress an uploaded gallery image, downscaling main photo to max 1000px and thumbnail to max 300px at 60% quality.
     * Uses UUID for filenames for security.
     *
     * @param UploadedFile $file
     * @param string $directory Storage subdirectory, e.g. 'machines/CNC-01/photos'
     * @return array ['file_path' => '...', 'thumbnail_path' => '...']
     */
    public function compressAndStoreGalleryPhoto(UploadedFile $file, string $directory): array
    {
        $mime = $file->getMimeType();
        $sourceImage = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($file->getRealPath());
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($file->getRealPath());
                break;
            default:
                $sourceImage = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                break;
        }

        if (!$sourceImage) {
            $uuid = (string) Str::uuid();
            $path = $file->storeAs($directory, $uuid . '.jpg', 'public');
            return [
                'file_path' => $path,
                'thumbnail_path' => $path,
            ];
        }

        $uuid = (string) Str::uuid();
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Helper function for resizing & saving JPEG
        $resampleAndSave = function(int $maxSize, string $targetRelPath) use ($sourceImage, $origWidth, $origHeight) {
            $newWidth = $origWidth;
            $newHeight = $origHeight;

            if ($origWidth > $maxSize || $origHeight > $maxSize) {
                if ($origWidth >= $origHeight) {
                    $newWidth = $maxSize;
                    $newHeight = (int) round(($origHeight * $maxSize) / $origWidth);
                } else {
                    $newHeight = $maxSize;
                    $newWidth = (int) round(($origWidth * $maxSize) / $origHeight);
                }
            }

            $targetImage = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($targetImage, 255, 255, 255);
            imagefill($targetImage, 0, 0, $white);

            imagecopyresampled(
                $targetImage,
                $sourceImage,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $origWidth,
                $origHeight
            );

            $tempPath = tempnam(sys_get_temp_dir(), 'gallery_img_');
            imagejpeg($targetImage, $tempPath, 60);

            Storage::disk('public')->put($targetRelPath, fopen($tempPath, 'r'));

            @unlink($tempPath);
            imagedestroy($targetImage);
        };

        // 1. Generate Main Compressed Photo (Max 1000px)
        $mainPath = "{$directory}/{$uuid}.jpg";
        $resampleAndSave(1000, $mainPath);

        // 2. Generate Thumbnail (Max 300px)
        $thumbPath = "{$directory}/thumbs/{$uuid}.jpg";
        $resampleAndSave(300, $thumbPath);

        imagedestroy($sourceImage);

        return [
            'file_path' => $mainPath,
            'thumbnail_path' => $thumbPath,
        ];
    }
}
