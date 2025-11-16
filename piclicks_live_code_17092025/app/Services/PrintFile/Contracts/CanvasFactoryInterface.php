<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for canvas creation and management (Factory Pattern)
 * 
 * Centralizes canvas creation with proper initialization
 */
interface CanvasFactoryInterface
{
    /**
     * Create a new canvas with proper initialization
     * 
     * @param int $width Canvas width in pixels
     * @param int $height Canvas height in pixels
     * @param bool $transparent Whether to initialize with transparency
     * @param array|null $backgroundColor RGB array [r, g, b] or null for transparent
     * @return GdImage|null The created canvas or null on failure
     */
    public function createCanvas(
        int $width,
        int $height,
        bool $transparent = true,
        ?array $backgroundColor = null
    ): ?GdImage;
    
    /**
     * Create a canvas with white background (for photo tiles)
     * 
     * @param int $width Canvas width in pixels
     * @param int $height Canvas height in pixels
     * @return GdImage|null The created canvas or null on failure
     */
    public function createWhiteCanvas(int $width, int $height): ?GdImage;
    
    /**
     * Configure alpha blending for a canvas
     * 
     * @param GdImage $canvas The canvas to configure
     * @param bool $blending Enable alpha blending
     * @param bool $saveAlpha Save alpha channel
     * @return bool Success status
     */
    public function configureAlphaBlending(
        GdImage $canvas,
        bool $blending,
        bool $saveAlpha
    ): bool;
}










