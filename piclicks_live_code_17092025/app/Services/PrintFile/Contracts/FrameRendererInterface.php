<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for frame rendering operations
 * 
 * Handles frame generation and composition onto tiles
 */
interface FrameRendererInterface
{
    /**
     * Render a frame for an entire block
     * 
     * @param int $blockW Block width in pixels
     * @param int $blockH Block height in pixels
     * @param array $frameConfig Frame configuration (color, thickness, etc.)
     * @return GdImage|null Frame canvas or null on failure
     */
    public function renderFrame(int $blockW, int $blockH, array $frameConfig): ?GdImage;
    
    /**
     * Apply a section of a frame to a tile canvas
     * 
     * @param GdImage $tileCanvas The tile canvas to apply frame to
     * @param GdImage $frameCanvas The full frame canvas
     * @param int $cropX X position in the frame to crop from
     * @param int $cropY Y position in the frame to crop from
     * @param int $tileW Tile width
     * @param int $tileH Tile height
     * @return bool Success status
     */
    public function applyFrameSection(
        GdImage $tileCanvas,
        GdImage $frameCanvas,
        int $cropX,
        int $cropY,
        int $tileW,
        int $tileH
    ): bool;
}










