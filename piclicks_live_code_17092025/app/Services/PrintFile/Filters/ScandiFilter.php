<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Scandi Filter
 * 
 * CSS: brightness(120%) contrast(105%) grayscale(10%) hue-rotate(5deg)
 * Effect: Bright, slightly warm, minimal desaturation
 */
class ScandiFilter extends AbstractFilter
{
    protected string $name = 'filter-scandi';
    protected string $description = 'Bright and slightly warm tone';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply brightness boost (120% = +20 in GD)
        if (!imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 20)) {
            return false;
        }
        
        // Apply slight contrast boost (105% = -5 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, -5)) {
            return false;
        }
        
        // Add slight warm tint to simulate hue-rotate
        if (!imagefilter($canvas, IMG_FILTER_COLORIZE, 15, 8, -5, 0)) {
            return false;
        }
        
        return true;
    }
}










