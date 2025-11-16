<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for geometric operations on canvases
 * 
 * Handles shapes, masks, and corner rounding
 */
interface GeometryInterface
{
    /**
     * Apply rounded corners to a canvas
     * 
     * @param GdImage $canvas The canvas to apply rounded corners to
     * @param int $radius Corner radius in pixels
     * @return bool Success status
     */
    public function applyRoundedCorners(GdImage $canvas, int $radius): bool;
    
    /**
     * Draw a filled rounded rectangle
     * 
     * @param GdImage $canvas The destination canvas
     * @param int $x X position
     * @param int $y Y position
     * @param int $w Width
     * @param int $h Height
     * @param int $r Corner radius
     * @param int $color GD color identifier
     * @return bool Success status
     */
    public function drawFilledRoundedRect(
        GdImage $canvas,
        int $x,
        int $y,
        int $w,
        int $h,
        int $r,
        int $color
    ): bool;
    
    /**
     * Create an alpha mask from a canvas
     * 
     * @param int $width Mask width
     * @param int $height Mask height
     * @return GdImage|null The created mask or null on failure
     */
    public function createMask(int $width, int $height): ?GdImage;
}









