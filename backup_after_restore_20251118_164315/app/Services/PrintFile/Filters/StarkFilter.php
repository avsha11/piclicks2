<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Stark Filter
 * 
 * CSS: grayscale(50%) brightness(100%) contrast(90%)
 * Effect: Slightly desaturated with reduced contrast
 */
class StarkFilter extends AbstractFilter
{
    protected string $name = 'filter-stark';
    protected string $description = 'Slightly desaturated with reduced contrast';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply reduced contrast first (90% = +10 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, 10)) {
            return false;
        }
        
        // Partial desaturation via colorize
        if (!imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 64)) {
            return false;
        }
        
        return true;
    }
}











