<?php

namespace App\Services\PrintFile\Geometry;

use App\Services\PrintFile\Contracts\GeometryInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Applies rounded corners to canvases
 * 
 * Handles the rounded corner masking algorithm
 */
class RoundedCornerApplicator implements GeometryInterface
{
    /**
     * Apply rounded corners to a canvas
     */
    public function applyRoundedCorners(GdImage $canvas, int $radius): bool
    {
        try {
            $w = imagesx($canvas);
            $h = imagesy($canvas);
            
            // Create mask
            $mask = $this->createMask($w, $h);
            if ($mask === null) {
                return false;
            }
            
            $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
            $opaque = imagecolorallocatealpha($mask, 0, 0, 0, 0);
            
            imagefilledrectangle($mask, 0, 0, $w, $h, $transparent);
            
            // Draw rounded rectangle on mask
            imagefilledrectangle($mask, $radius, 0, $w - $radius, $h, $opaque);
            imagefilledrectangle($mask, 0, $radius, $w, $h - $radius, $opaque);
            imagefilledellipse($mask, $radius, $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $w - $radius, $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $radius, $h - $radius, $radius * 2, $radius * 2, $opaque);
            imagefilledellipse($mask, $w - $radius, $h - $radius, $radius * 2, $radius * 2, $opaque);
            
            // Apply mask to canvas
            $result = imagecreatetruecolor($w, $h);
            imagealphablending($result, false);
            imagesavealpha($result, true);
            $resultTrans = imagecolorallocatealpha($result, 0, 0, 0, 127);
            imagefilledrectangle($result, 0, 0, $w, $h, $resultTrans);
            
            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $alpha = (imagecolorat($mask, $x, $y) & 0x7F000000) >> 24;
                    if ($alpha === 0) {
                        $col = imagecolorat($canvas, $x, $y);
                        imagesetpixel($result, $x, $y, $col);
                    }
                }
            }
            
            imagecopy($canvas, $result, 0, 0, 0, 0, $w, $h);
            imagedestroy($mask);
            imagedestroy($result);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply rounded corners', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Draw a filled rounded rectangle
     */
    public function drawFilledRoundedRect(
        GdImage $canvas,
        int $x,
        int $y,
        int $w,
        int $h,
        int $r,
        int $color
    ): bool {
        try {
            // Middle rectangles
            imagefilledrectangle($canvas, $x + $r, $y, $x + $w - $r, $y + $h, $color);
            imagefilledrectangle($canvas, $x, $y + $r, $x + $w, $y + $h - $r, $color);
            
            // Four corner circles
            imagefilledellipse($canvas, $x + $r, $y + $r, $r * 2, $r * 2, $color);
            imagefilledellipse($canvas, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
            imagefilledellipse($canvas, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
            imagefilledellipse($canvas, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to draw filled rounded rect', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Create an alpha mask
     */
    public function createMask(int $width, int $height): ?GdImage
    {
        try {
            $mask = imagecreatetruecolor($width, $height);
            if ($mask === false) {
                return null;
            }
            
            imagealphablending($mask, false);
            imagesavealpha($mask, true);
            
            return $mask;
        } catch (\Exception $e) {
            Log::error('Failed to create mask', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}










