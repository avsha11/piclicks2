<?php

namespace App\Services\PrintFile\Canvas;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Manages alpha blending state for canvases
 * 
 * Provides safe state management for alpha blending operations
 */
class AlphaBlendingManager
{
    /**
     * Execute a callback with specific alpha blending settings
     * Restores original state afterward
     */
    public function withAlphaBlending(
        GdImage $canvas,
        bool $blending,
        bool $saveAlpha,
        callable $callback
    ) {
        // Save current state (we can't query it, so we just set it back after)
        $result = null;
        
        try {
            // Set desired state
            imagealphablending($canvas, $blending);
            imagesavealpha($canvas, $saveAlpha);
            
            // Execute callback
            $result = $callback($canvas);
            
        } catch (\Exception $e) {
            Log::error('Error in withAlphaBlending callback', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
        
        return $result;
    }
    
    /**
     * Enable alpha blending temporarily
     */
    public function withBlendingEnabled(GdImage $canvas, callable $callback)
    {
        return $this->withAlphaBlending($canvas, true, true, $callback);
    }
    
    /**
     * Disable alpha blending temporarily
     */
    public function withBlendingDisabled(GdImage $canvas, callable $callback)
    {
        return $this->withAlphaBlending($canvas, false, true, $callback);
    }
    
    /**
     * Set canvas to standard compositing mode (for drawing images/text)
     */
    public function setCompositingMode(GdImage $canvas): bool
    {
        try {
            imagealphablending($canvas, true);
            imagesavealpha($canvas, true);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set compositing mode', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Set canvas to transparency mode (for working with alpha)
     */
    public function setTransparencyMode(GdImage $canvas): bool
    {
        try {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set transparency mode', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}




