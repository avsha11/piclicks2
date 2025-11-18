<?php

namespace App\Services\PrintFile\Image;

use App\Services\PrintFile\Domain\DimensionCalculator;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Transforms images (scale, rotate, position)
 * 
 * Handles all image transformation operations
 */
class ImageTransformer
{
    /**
     * Rotate an image
     * 
     * @param GdImage $image Source image
     * @param string $rotateCode Rotation code (1=0°, 2=90°, 3=180°, 4=270°)
     * @return GdImage Rotated image (may be same as input if no rotation)
     */
    public function rotateImage(GdImage $image, string $rotateCode): GdImage
    {
        $degrees = $this->rotateCodeToDegrees($rotateCode);
        
        if ($degrees === 0) {
            return $image;
        }
        
        try {
            $rotated = imagerotate($image, -$degrees, 0);
            if ($rotated === false) {
                Log::warning('Image rotation failed, using original', ['degrees' => $degrees]);
                return $image;
            }
            
            Log::debug('Image rotated', ['degrees' => $degrees]);
            return $rotated;
        } catch (\Exception $e) {
            Log::error('Exception rotating image', [
                'degrees' => $degrees,
                'error' => $e->getMessage(),
            ]);
            return $image;
        }
    }
    
    /**
     * Calculate scaled dimensions for cover fit
     */
    public function calculateCoverDimensions(
        int $sourceW,
        int $sourceH,
        int $targetW,
        int $targetH,
        string $zoom
    ): array {
        // Parse zoom
        $zoomParts = explode('|', $zoom);
        $zoomX = floatval($zoomParts[0] ?? 0);
        $zoomY = floatval($zoomParts[1] ?? $zoomX);
        
        $result = DimensionCalculator::calculateCoverDimensions(
            $sourceW,
            $sourceH,
            $targetW,
            $targetH,
            $zoomX
        );
        
        // Apply Y zoom if different
        if ($zoomY != $zoomX && $zoomY > 0) {
            $result['height'] = (int) ($result['height'] * (1 + $zoomY) / (1 + $zoomX));
        }
        
        return $result;
    }
    
    /**
     * Scale and draw an image onto a canvas
     */
    public function drawScaledImage(
        GdImage $destCanvas,
        GdImage $sourceImage,
        int $destX,
        int $destY,
        int $destW,
        int $destH
    ): bool {
        try {
            $sourceW = imagesx($sourceImage);
            $sourceH = imagesy($sourceImage);
            
            $success = imagecopyresampled(
                $destCanvas,
                $sourceImage,
                $destX, $destY,
                0, 0,
                $destW, $destH,
                $sourceW, $sourceH
            );
            
            if (!$success) {
                Log::error('imagecopyresampled failed');
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception drawing scaled image', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Convert rotate code to degrees
     */
    private function rotateCodeToDegrees(string $rotateCode): int
    {
        return match ($rotateCode) {
            '2' => 90,
            '3' => 180,
            '4' => 270,
            default => 0,
        };
    }
}











