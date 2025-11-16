<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Nordic Filter
 * 
 * CSS: contrast(110%) brightness(80%) sepia(20%) hue-rotate(-15deg)
 * Effect: Darker with warm sepia tones
 */
class NordicFilter extends AbstractFilter
{
    protected string $name = 'filter-nordic';
    protected string $description = 'Darker with warm sepia tones';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply contrast boost (110% = -10 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, -10)) {
            return false;
        }
        
        // Apply brightness reduction (80% = -20 in GD)
        if (!imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -20)) {
            return false;
        }
        
        // Create sepia effect
        if (!imagefilter($canvas, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        
        // Add warm sepia tone
        if (!imagefilter($canvas, IMG_FILTER_COLORIZE, 30, 25, 15, 0)) {
            return false;
        }
        
        return true;
    }
}









