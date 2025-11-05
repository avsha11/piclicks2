<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for text rendering operations
 * 
 * Handles text overlay rendering with fonts, rotation, and positioning
 */
interface TextRendererInterface
{
    /**
     * Render text overlays onto a canvas
     * 
     * @param GdImage $canvas The destination canvas
     * @param array $textOverlays Array of text overlay configurations
     * @param int $blockClearW Clear block width in pixels
     * @param int $blockClearH Clear block height in pixels
     * @param int $bleedPx Bleed offset in pixels
     * @return bool Success status
     */
    public function renderTextOverlays(
        GdImage $canvas,
        array $textOverlays,
        int $blockClearW,
        int $blockClearH,
        int $bleedPx
    ): bool;
    
    /**
     * Render a single text overlay with rotation
     * 
     * @param GdImage $canvas The destination canvas
     * @param string $text The text to render
     * @param string $fontPath Path to the font file
     * @param int $fontSize Font size in pixels
     * @param int $textColor GD color identifier
     * @param int $x X position
     * @param int $y Y position
     * @param float $rotation Rotation in degrees
     * @return bool Success status
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
    ): bool;
}




