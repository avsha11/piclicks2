<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Belveder Filter
 * 
 * CSS: contrast(115%) brightness(90%) sepia(30%) hue-rotate(10deg)
 * Effect: Rich sepia with enhanced contrast
 */
class BelvederFilter extends AbstractFilter
{
    protected string $name = 'filter-belveder';
    protected string $description = 'Rich sepia with enhanced contrast';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply contrast boost (115% = -15 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, -15)) {
            return false;
        }
        
        // Apply slight brightness reduction (90% = -10 in GD)
        if (!imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -10)) {
            return false;
        }
        
        // Create sepia effect
        if (!imagefilter($canvas, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        
        // Add rich sepia tone
        if (!imagefilter($canvas, IMG_FILTER_COLORIZE, 100, 60, 35, 0)) {
            return false;
        }
        
        return true;
    }
}




