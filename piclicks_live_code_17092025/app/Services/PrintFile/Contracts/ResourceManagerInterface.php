<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for GD resource management
 * 
 * Tracks and manages GD image resources to prevent memory leaks
 */
interface ResourceManagerInterface
{
    /**
     * Track a GD resource for later cleanup
     * 
     * @param GdImage $resource The resource to track
     * @param string $identifier Optional identifier for debugging
     * @return void
     */
    public function track(GdImage $resource, string $identifier = ''): void;
    
    /**
     * Untrack and destroy a resource
     * 
     * @param GdImage $resource The resource to destroy
     * @return bool Success status
     */
    public function destroy(GdImage $resource): bool;
    
    /**
     * Destroy all tracked resources
     * 
     * @return int Number of resources destroyed
     */
    public function destroyAll(): int;
    
    /**
     * Get the current memory usage
     * 
     * @return array Memory usage statistics
     */
    public function getMemoryUsage(): array;
    
    /**
     * Check if memory is approaching limits
     * 
     * @param float $threshold Threshold percentage (0.0 to 1.0)
     * @return bool True if approaching limit
     */
    public function isMemoryLimitApproaching(float $threshold = 0.8): bool;
}









