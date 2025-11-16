<?php

namespace App\Services\PrintFile\Canvas;

use App\Services\PrintFile\Contracts\CanvasFactoryInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Factory for creating GD canvases with proper initialization
 * 
 * Centralizes canvas creation logic
 */
class CanvasFactory implements CanvasFactoryInterface
{
    /**
     * Create a new canvas with proper initialization
     */
    public function createCanvas(
        int $width,
        int $height,
        bool $transparent = true,
        ?array $backgroundColor = null
    ): ?GdImage {
        try {
            $canvas = imagecreatetruecolor($width, $height);
            
            if ($canvas === false) {
                Log::error('Failed to create canvas', [
                    'width' => $width,
                    'height' => $height,
                ]);
                return null;
            }
            
            // Configure alpha blending
            $this->configureAlphaBlending($canvas, false, true);
            
            // Set background
            if ($transparent) {
                $trans = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefilledrectangle($canvas, 0, 0, $width, $height, $trans);
            } elseif ($backgroundColor !== null) {
                $bgColor = imagecolorallocate(
                    $canvas,
                    $backgroundColor[0],
                    $backgroundColor[1],
                    $backgroundColor[2]
                );
                imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);
            }
            
            Log::debug('Canvas created', [
                'width' => $width,
                'height' => $height,
                'transparent' => $transparent,
            ]);
            
            return $canvas;
        } catch (\Exception $e) {
            Log::error('Exception creating canvas', [
                'error' => $e->getMessage(),
                'width' => $width,
                'height' => $height,
            ]);
            return null;
        }
    }
    
    /**
     * Create a canvas with white background (for photo tiles)
     */
    public function createWhiteCanvas(int $width, int $height): ?GdImage
    {
        return $this->createCanvas($width, $height, false, [255, 255, 255]);
    }
    
    /**
     * Configure alpha blending for a canvas
     */
    public function configureAlphaBlending(
        GdImage $canvas,
        bool $blending,
        bool $saveAlpha
    ): bool {
        try {
            imagealphablending($canvas, $blending);
            imagesavealpha($canvas, $saveAlpha);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to configure alpha blending', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}










