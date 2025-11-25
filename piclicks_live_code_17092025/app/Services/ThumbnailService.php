<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * ThumbnailService
 * 
 * Generates thumbnails for gallery images to improve loading performance
 */
class ThumbnailService
{
    private const THUMBNAIL_WIDTH = 400;
    private const THUMBNAIL_HEIGHT = 400;
    private const THUMBNAIL_QUALITY = 85;
    
    /**
     * Generate thumbnail for an image
     * 
     * @param string $imagePath Relative path from storage/app/public
     * @return string|null Thumbnail path or null on failure
     */
    public function generateThumbnail(string $imagePath): ?string
    {
        try {
            $fullPath = storage_path('app/public/' . $imagePath);
            
            if (!File::exists($fullPath)) {
                Log::warning('ThumbnailService: Image not found', ['path' => $imagePath]);
                return null;
            }
            
            // Generate thumbnail path
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . ($pathInfo['extension'] ?? 'jpg');
            $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);
            
            // Check if thumbnail already exists
            if (File::exists($thumbnailFullPath)) {
                return $thumbnailPath;
            }
            
            // Create thumbnail directory if it doesn't exist
            $thumbnailDir = dirname($thumbnailFullPath);
            if (!File::isDirectory($thumbnailDir)) {
                File::makeDirectory($thumbnailDir, 0755, true);
            }
            
            // Generate thumbnail using Intervention Image
            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($fullPath);
                
                // Resize maintaining aspect ratio
                $image->scale(width: self::THUMBNAIL_WIDTH, height: self::THUMBNAIL_HEIGHT);
                
                // Save thumbnail
                $image->save($thumbnailFullPath, quality: self::THUMBNAIL_QUALITY);
                
                return $thumbnailPath;
            } catch (\Exception $e) {
                Log::error('ThumbnailService: Failed to generate thumbnail', [
                    'path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
            
        } catch (\Exception $e) {
            Log::error('ThumbnailService: Error generating thumbnail', [
                'path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get thumbnail path, generating if needed
     * 
     * @param string $imagePath Original image path
     * @return string Thumbnail path or original path if thumbnail generation fails
     */
    public function getThumbnailPath(string $imagePath): string
    {
        $thumbnailPath = $this->generateThumbnail($imagePath);
        return $thumbnailPath ?? $imagePath; // Fallback to original if thumbnail fails
    }
    
    /**
     * Batch generate thumbnails for multiple images
     * 
     * @param array $imagePaths Array of image paths
     * @return array Array of thumbnail paths
     */
    public function generateThumbnails(array $imagePaths): array
    {
        $thumbnails = [];
        foreach ($imagePaths as $imagePath) {
            $thumbnail = $this->generateThumbnail($imagePath);
            if ($thumbnail) {
                $thumbnails[$imagePath] = $thumbnail;
            }
        }
        return $thumbnails;
    }
}

