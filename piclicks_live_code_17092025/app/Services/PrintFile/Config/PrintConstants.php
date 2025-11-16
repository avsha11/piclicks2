<?php

namespace App\Services\PrintFile\Config;

/**
 * Centralized constants for print file generation
 * 
 * All dimensional constants from SPEC.md and constants.json
 */
class PrintConstants
{
    // Base units
    public const MM_PER_IN = 25.4;
    public const DPI = 300;
    public const BLEED_MM = 2.0;
    
    // Clear area (visible in editor/preview)
    public const CLEAR_TILE_W_MM = 143.7;
    public const CLEAR_TILE_H_MM = 126.0;
    public const CLEAR_CORNER_RADIUS_MM = 8.0;
    
    // Print tile (with bleed)
    public const PRINT_TILE_W_MM = 147.7; // 143.7 + 2*2
    public const PRINT_TILE_H_MM = 130.0; // 126.0 + 2*2
    public const PRINT_CORNER_RADIUS_MM = 10.0;
    
    // Frame specifications
    public const FRAME_PRINT_THICKNESS_MM = 10.0;
    public const FRAME_VISIBLE_THICKNESS_MM = 8.0;
    public const FRAME_INNER_CORNER_RADIUS_MM = 2.0;
    
    // Editor dimensions (from tool.js)
    public const EDITOR_TILE_WIDTH_PX = 91;
    public const EDITOR_TILE_HEIGHT_PX = 80;
    public const EDITOR_CANVAS_WIDTH_PX = 750;
    public const EDITOR_CANVAS_HEIGHT_PX = 750;
    
    // Rendering limits
    public const MAX_FONT_SIZE_PX = 4000;
    public const MAX_TILE_COLS = 10;
    public const MAX_TILE_ROWS = 10;
    public const MEMORY_THRESHOLD = 0.8; // 80% of memory limit
    
    // Render order (bottom to top)
    public const RENDER_ORDER = ['image', 'filter', 'text', 'frame'];
    
    /**
     * Calculate pixels per millimeter
     */
    public static function getPxPerMm(): float
    {
        return self::DPI / self::MM_PER_IN;
    }
    
    /**
     * Convert millimeters to pixels
     */
    public static function mmToPx(float $mm): int
    {
        return (int) round($mm * self::getPxPerMm());
    }
    
    /**
     * Convert pixels to millimeters
     */
    public static function pxToMm(int $px): float
    {
        return $px / self::getPxPerMm();
    }
    
    /**
     * Get all clear tile dimensions in pixels
     */
    public static function getClearTileDimensions(): array
    {
        return [
            'width' => self::mmToPx(self::CLEAR_TILE_W_MM),
            'height' => self::mmToPx(self::CLEAR_TILE_H_MM),
            'cornerRadius' => self::mmToPx(self::CLEAR_CORNER_RADIUS_MM),
        ];
    }
    
    /**
     * Get all print tile dimensions in pixels
     */
    public static function getPrintTileDimensions(): array
    {
        return [
            'width' => self::mmToPx(self::PRINT_TILE_W_MM),
            'height' => self::mmToPx(self::PRINT_TILE_H_MM),
            'cornerRadius' => self::mmToPx(self::PRINT_CORNER_RADIUS_MM),
        ];
    }
    
    /**
     * Get bleed in pixels
     */
    public static function getBleedPx(): int
    {
        return self::mmToPx(self::BLEED_MM);
    }
    
    /**
     * Get frame dimensions in pixels
     */
    public static function getFrameDimensions(): array
    {
        return [
            'printThickness' => self::mmToPx(self::FRAME_PRINT_THICKNESS_MM),
            'visibleThickness' => self::mmToPx(self::FRAME_VISIBLE_THICKNESS_MM),
            'innerCornerRadius' => self::mmToPx(self::FRAME_INNER_CORNER_RADIUS_MM),
        ];
    }
}









