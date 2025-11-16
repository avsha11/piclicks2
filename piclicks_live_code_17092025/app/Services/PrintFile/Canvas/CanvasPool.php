<?php

namespace App\Services\PrintFile\Canvas;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Canvas pooling for memory optimization
 * 
 * Reuses canvases to reduce GC pressure and improve performance
 */
class CanvasPool
{
    private array $pool = [];
    private int $maxPoolSize;
    private CanvasFactory $factory;
    
    public function __construct(CanvasFactory $factory, int $maxPoolSize = 10)
    {
        $this->factory = $factory;
        $this->maxPoolSize = $maxPoolSize;
    }
    
    /**
     * Get a canvas from the pool or create a new one
     */
    public function acquire(int $width, int $height, bool $transparent = true): ?GdImage
    {
        $key = $this->getKey($width, $height, $transparent);
        
        // Check if we have a canvas in the pool
        if (isset($this->pool[$key]) && count($this->pool[$key]) > 0) {
            $canvas = array_pop($this->pool[$key]);
            
            // Clear the canvas
            $this->clearCanvas($canvas, $width, $height, $transparent);
            
            Log::debug('Canvas acquired from pool', [
                'key' => $key,
                'remaining' => count($this->pool[$key] ?? []),
            ]);
            
            return $canvas;
        }
        
        // Create new canvas
        $canvas = $this->factory->createCanvas($width, $height, $transparent);
        
        Log::debug('New canvas created (pool empty)', ['key' => $key]);
        
        return $canvas;
    }
    
    /**
     * Return a canvas to the pool
     */
    public function release(GdImage $canvas, int $width, int $height, bool $transparent = true): void
    {
        $key = $this->getKey($width, $height, $transparent);
        
        // Initialize pool for this size if needed
        if (!isset($this->pool[$key])) {
            $this->pool[$key] = [];
        }
        
        // Only add to pool if under max size
        if (count($this->pool[$key]) < $this->maxPoolSize) {
            $this->pool[$key][] = $canvas;
            
            Log::debug('Canvas returned to pool', [
                'key' => $key,
                'pool_size' => count($this->pool[$key]),
            ]);
        } else {
            // Pool full, destroy the canvas
            imagedestroy($canvas);
            
            Log::debug('Canvas destroyed (pool full)', ['key' => $key]);
        }
    }
    
    /**
     * Destroy all canvases in the pool
     */
    public function clear(): int
    {
        $count = 0;
        
        foreach ($this->pool as $key => $canvases) {
            foreach ($canvases as $canvas) {
                imagedestroy($canvas);
                $count++;
            }
        }
        
        $this->pool = [];
        
        Log::info('Canvas pool cleared', ['destroyed' => $count]);
        
        return $count;
    }
    
    /**
     * Get pool statistics
     */
    public function getStats(): array
    {
        $stats = [
            'total_canvases' => 0,
            'sizes' => [],
        ];
        
        foreach ($this->pool as $key => $canvases) {
            $count = count($canvases);
            $stats['total_canvases'] += $count;
            $stats['sizes'][$key] = $count;
        }
        
        return $stats;
    }
    
    /**
     * Generate a key for the pool
     */
    private function getKey(int $width, int $height, bool $transparent): string
    {
        return sprintf('%dx%d_%s', $width, $height, $transparent ? 't' : 'o');
    }
    
    /**
     * Clear a canvas for reuse
     */
    private function clearCanvas(GdImage $canvas, int $width, int $height, bool $transparent): void
    {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        
        if ($transparent) {
            $trans = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $trans);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        }
    }
}










