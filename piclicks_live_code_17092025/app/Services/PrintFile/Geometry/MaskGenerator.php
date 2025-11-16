<?php

namespace App\Services\PrintFile\Geometry;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Generates alpha masks for various purposes
 * 
 * Specialized mask generation utilities
 */
class MaskGenerator
{
    /**
     * Create a transparent mask
     */
    public function createTransparentMask(int $width, int $height): ?GdImage
    {
        try {
            $mask = imagecreatetruecolor($width, $height);
            if ($mask === false) {
                return null;
            }
            
            imagealphablending($mask, false);
            imagesavealpha($mask, true);
            
            $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
            imagefilledrectangle($mask, 0, 0, $width, $height, $transparent);
            
            return $mask;
        } catch (\Exception $e) {
            Log::error('Failed to create transparent mask', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Create a rounded rectangle mask
     */
    public function createRoundedRectangleMask(
        int $width,
        int $height,
        int $radius
    ): ?GdImage {
        try {
            $mask = $this->createTransparentMask($width, $height);
            if ($mask === null) {
                return null;
            }
            
            $opaque = imagecolorallocatealpha($mask, 255, 255, 255, 0);
            
            // Draw rounded rectangle
            imagefilledrectangle($mask, $radius, 0, $width - $radius, $height, $opaque);
            imagefilledrectangle($mask, 0, $radius, $width, $height - $radius, $opaque);
            imagefilledellipse($mask, $radius, $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $width - $radius, $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $radius, $height - $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $width - $radius, $height - $radius, $radius * 2, $radius * 2, $opaque);
            
            return $mask;
        } catch (\Exception $e) {
            Log::error('Failed to create rounded rectangle mask', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Apply a mask to a canvas
     */
    public function applyMask(GdImage $canvas, GdImage $mask): bool
    {
        try {
            $w = imagesx($canvas);
            $h = imagesy($canvas);
            
            $result = imagecreatetruecolor($w, $h);
            imagealphablending($result, false);
            imagesavealpha($result, true);
            
            $transparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
            imagefilledrectangle($result, 0, 0, $w, $h, $transparent);
            
            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $maskAlpha = (imagecolorat($mask, $x, $y) & 0x7F000000) >> 24;
                    if ($maskAlpha < 127) {
                        $color = imagecolorat($canvas, $x, $y);
                        imagesetpixel($result, $x, $y, $color);
                    }
                }
            }
            
            imagecopy($canvas, $result, 0, 0, 0, 0, $w, $h);
            imagedestroy($result);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply mask', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}









