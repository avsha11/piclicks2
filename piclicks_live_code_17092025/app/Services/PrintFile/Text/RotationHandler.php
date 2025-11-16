<?php

namespace App\Services\PrintFile\Text;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Handles rotated text rendering
 * 
 * Creates temporary canvases for rotation to prevent truncation
 */
class RotationHandler
{
    /**
     * Render rotated text using a temporary canvas
     */
    public function renderRotatedText(
        GdImage $canvas,
        string $text,
        string $fontPath,
        int $fontSize,
        int $textColor,
        int $x,
        int $y,
        float $rotation
    ): bool {
        try {
            // Get text bounding box to determine size
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox === false) {
                Log::error('Failed to get text bounding box');
                return false;
            }
            
            $textWidth = $bbox[4] - $bbox[0];
            $textHeight = $bbox[1] - $bbox[5];
            
            // Calculate rotated bounding box dimensions
            $rotRad = deg2rad(abs($rotation));
            $rotatedWidth = abs($textWidth * cos($rotRad)) + abs($textHeight * sin($rotRad));
            $rotatedHeight = abs($textWidth * sin($rotRad)) + abs($textHeight * cos($rotRad));
            
            // Use padding to prevent truncation (max 500px)
            $padding = min(max($rotatedWidth, $rotatedHeight), 500);
            $tempWidth = (int) ($textWidth + $padding * 2);
            $tempHeight = (int) ($textHeight + $padding * 2);
            
            // Create temporary canvas for text
            $tempCanvas = imagecreatetruecolor($tempWidth, $tempHeight);
            if ($tempCanvas === false) {
                Log::error('Failed to create temp canvas for rotation');
                return false;
            }
            
            imagealphablending($tempCanvas, false);
            imagesavealpha($tempCanvas, true);
            $transparent = imagecolorallocatealpha($tempCanvas, 0, 0, 0, 127);
            imagefilledrectangle($tempCanvas, 0, 0, $tempWidth, $tempHeight, $transparent);
            
            // Draw text on temporary canvas (centered with padding)
            imagealphablending($tempCanvas, true);
            $textX = (int) $padding;
            $textY = (int) ($textHeight + $padding);
            imagettftext($tempCanvas, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $text);
            imagealphablending($tempCanvas, false);
            
            // Rotate the temporary canvas
            $rotatedCanvas = imagerotate($tempCanvas, -$rotation, $transparent);
            if ($rotatedCanvas === false) {
                imagedestroy($tempCanvas);
                Log::error('Failed to rotate canvas');
                return false;
            }
            
            imagesavealpha($rotatedCanvas, true);
            
            // Calculate position to place the rotated text
            $rotatedW = imagesx($rotatedCanvas);
            $rotatedH = imagesy($rotatedCanvas);
            $centerX = $x - (int) ($rotatedW / 2);
            $centerY = $y - (int) ($rotatedH / 2);
            
            // Copy rotated text to main canvas
            imagealphablending($canvas, true);
            imagecopy($canvas, $rotatedCanvas, $centerX, $centerY, 0, 0, $rotatedW, $rotatedH);
            imagealphablending($canvas, false);
            
            // Cleanup
            imagedestroy($tempCanvas);
            imagedestroy($rotatedCanvas);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception rendering rotated text', [
                'error' => $e->getMessage(),
                'rotation' => $rotation,
            ]);
            return false;
        }
    }
}









