<?php

namespace App\Services\PrintFile\Filters;

use GdImage;

/**
 * Capri Filter
 * 
 * CSS: contrast(120%) brightness(110%) saturate(150%) hue-rotate(-30deg)
 * Effect: High contrast with cool blue tones
 */
class CapriFilter extends AbstractFilter
{
    protected string $name = 'filter-capri';
    protected string $description = 'High contrast with cool blue tones';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        // Apply contrast boost (120% = -20 in GD)
        if (!imagefilter($canvas, IMG_FILTER_CONTRAST, -20)) {
            return false;
        }
        
        // Apply brightness boost (110% = +10 in GD)
        if (!imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 10)) {
            return false;
        }
        
        // Add cool blue/cyan tint to simulate hue-rotate(-30deg)
        if (!imagefilter($canvas, IMG_FILTER_COLORIZE, -15, 5, 35, 0)) {
            return false;
        }
        
        return true;
    }
}









