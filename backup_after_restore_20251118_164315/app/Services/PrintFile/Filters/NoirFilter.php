<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Noir Filter
 * 
 * CSS: grayscale(100%) contrast(1.2)
 * Effect: Classic black and white with increased contrast
 */
class NoirFilter extends AbstractFilter
{
    protected string $name = 'filter-noir';
    protected string $description = 'Classic black and white with high contrast';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply grayscale
        if (!imagefilter($canvas, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        
        // Apply contrast boost (120% = -20 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, -20)) {
            return false;
        }
        
        return true;
    }
}











