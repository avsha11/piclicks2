<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for filter implementations (Strategy Pattern)
 * 
 * Each filter implements this interface to provide consistent behavior
 */
interface FilterInterface
{
    /**
     * Apply the filter to a canvas
     * 
     * @param GdImage $canvas The canvas to apply the filter to
     * @return bool Success status
     */
    public function apply(GdImage $canvas): bool;
    
    /**
     * Get the filter name
     * 
     * @return string Filter identifier (e.g., 'filter-noir')
     */
    public function getName(): string;
    
    /**
     * Get a description of what the filter does
     * 
     * @return string Human-readable description
     */
    public function getDescription(): string;
}










