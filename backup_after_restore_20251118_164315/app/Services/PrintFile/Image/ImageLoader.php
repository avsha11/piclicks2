<?php

namespace App\Services\PrintFile\Image;

use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Loads images from the filesystem
 * 
 * Handles image validation and format detection
 */
class ImageLoader
{
    /**
     * Load an image from a file path
     */
    public function loadImage(string $imagePath): ?GdImage
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        
        if (!File::exists($fullPath)) {
            Log::error('Image file not found', ['path' => $imagePath]);
            return null;
        }
        
        try {
            $contents = file_get_contents($fullPath);
            if ($contents === false) {
                Log::error('Failed to read image file', ['path' => $imagePath]);
                return null;
            }
            
            $image = imagecreatefromstring($contents);
            if ($image === false) {
                Log::error('Failed to create image from file', ['path' => $imagePath]);
                return null;
            }
            
            Log::debug('Image loaded successfully', [
                'path' => $imagePath,
                'size' => imagesx($image) . 'x' . imagesy($image),
            ]);
            
            return $image;
        } catch (\Exception $e) {
            Log::error('Exception loading image', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Validate that an image file exists and is readable
     */
    public function validateImagePath(string $imagePath): bool
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        return File::exists($fullPath) && File::isReadable($fullPath);
    }
    
    /**
     * Get image dimensions without loading the full image
     */
    public function getImageDimensions(string $imagePath): ?array
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        
        if (!File::exists($fullPath)) {
            return null;
        }
        
        $size = getimagesize($fullPath);
        if ($size === false) {
            return null;
        }
        
        return [
            'width' => $size[0],
            'height' => $size[1],
            'type' => $size[2],
            'mime' => $size['mime'],
        ];
    }
}











