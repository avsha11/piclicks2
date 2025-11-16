<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for image processing operations
 * 
 * Responsible for loading, transforming, and rendering images onto canvases
 */
interface ImageProcessorInterface
{
    /**
     * Load an image from a file path
     * 
     * @param string $imagePath Path to the image file
     * @return GdImage|null The loaded image resource or null on failure
     */
    public function loadImage(string $imagePath): ?GdImage;
    
    /**
     * Render an image onto a canvas with transformations
     * 
     * @param GdImage $canvas The destination canvas
     * @param string $imagePath Path to the source image
     * @param int $blockW Block width in pixels
     * @param int $blockH Block height in pixels
     * @param string $zoom Zoom factor (format: "zoomX|zoomY" or "0")
     * @param string $rotate Rotation code (1=0°, 2=90°, 3=180°, 4=270°)
     * @param int $bleedPx Bleed offset in pixels
     * @return bool Success status
     */
    public function renderImageToCanvas(
        GdImage $canvas,
        string $imagePath,
        int $blockW,
        int $blockH,
        string $zoom,
        string $rotate,
        int $bleedPx
    ): bool;
    
    /**
     * Scale an image to cover a specific area
     * 
     * @param GdImage $sourceImage Source image
     * @param int $targetW Target width
     * @param int $targetH Target height
     * @param float $zoom Zoom factor
     * @return array [scaledW, scaledH] Calculated scaled dimensions
     */
    public function calculateScaledDimensions(
        GdImage $sourceImage,
        int $targetW,
        int $targetH,
        float $zoom = 0
    ): array;
}









