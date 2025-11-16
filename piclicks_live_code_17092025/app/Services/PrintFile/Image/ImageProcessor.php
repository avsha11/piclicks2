<?php

namespace App\Services\PrintFile\Image;

use App\Services\PrintFile\Contracts\ImageProcessorInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Main image processor (Facade for image subsystem)
 * 
 * Coordinates image loading, transformation, and rendering
 */
class ImageProcessor implements ImageProcessorInterface
{
    private ImageLoader $loader;
    private ImageTransformer $transformer;
    private ImageCache $cache;
    
    public function __construct(
        ImageLoader $loader,
        ImageTransformer $transformer,
        ImageCache $cache
    ) {
        $this->loader = $loader;
        $this->transformer = $transformer;
        $this->cache = $cache;
    }
    
    /**
     * Load an image from a file path
     */
    public function loadImage(string $imagePath): ?GdImage
    {
        // Check cache first
        if ($this->cache->has($imagePath)) {
            return $this->cache->get($imagePath);
        }
        
        // Load image
        $image = $this->loader->loadImage($imagePath);
        
        // Cache it
        if ($image !== null) {
            $this->cache->put($imagePath, $image);
        }
        
        return $image;
    }
    
    /**
     * Render an image onto a canvas with transformations
     */
    public function renderImageToCanvas(
        GdImage $canvas,
        string $imagePath,
        int $blockW,
        int $blockH,
        string $zoom,
        string $rotate,
        int $bleedPx
    ): bool {
        try {
            // Load source image
            $sourceImage = $this->loadImage($imagePath);
            if ($sourceImage === null) {
                Log::error('Failed to load source image', ['path' => $imagePath]);
                return false;
            }
            
            // Apply rotation
            $rotatedImage = $this->transformer->rotateImage($sourceImage, $rotate);
            $srcW = imagesx($rotatedImage);
            $srcH = imagesy($rotatedImage);
            
            // Calculate scaled dimensions to cover CLEAR area only
            $blockClearW = $blockW - (2 * $bleedPx);
            $blockClearH = $blockH - (2 * $bleedPx);
            
            $scaled = $this->transformer->calculateCoverDimensions(
                $srcW,
                $srcH,
                $blockClearW,
                $blockClearH,
                $zoom
            );
            
            $scaledW = $scaled['width'];
            $scaledH = $scaled['height'];
            
            // Position at top-left (with bleed offset)
            $dstX = $bleedPx;
            $dstY = $bleedPx;
            
            // Enable alpha blending for proper image rendering
            imagealphablending($canvas, true);
            
            // Draw image
            $success = $this->transformer->drawScaledImage(
                $canvas,
                $rotatedImage,
                $dstX,
                $dstY,
                $scaledW,
                $scaledH
            );
            
            // Restore alpha blending state
            imagealphablending($canvas, false);
            
            // Cleanup rotated image if it's different from source
            if ($rotatedImage !== $sourceImage) {
                imagedestroy($rotatedImage);
            }
            
            if ($success) {
                Log::info('Image rendered to canvas', [
                    'path' => $imagePath,
                    'source' => "{$srcW}x{$srcH}px",
                    'scaled' => "{$scaledW}x{$scaledH}px",
                    'position' => "({$dstX},{$dstY})",
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('Exception rendering image to canvas', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Calculate scaled dimensions
     */
    public function calculateScaledDimensions(
        GdImage $sourceImage,
        int $targetW,
        int $targetH,
        float $zoom = 0
    ): array {
        $sourceW = imagesx($sourceImage);
        $sourceH = imagesy($sourceImage);
        
        return $this->transformer->calculateCoverDimensions(
            $sourceW,
            $sourceH,
            $targetW,
            $targetH,
            (string) $zoom
        );
    }
}









