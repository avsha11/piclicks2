<?php

namespace App\Services\PrintFile\Image;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Caches loaded images to avoid re-reading
 * 
 * Implements simple in-memory cache for image resources
 */
class ImageCache
{
    private array $cache = [];
    private int $maxCacheSize;
    private array $accessTimes = [];
    
    public function __construct(int $maxCacheSize = 10)
    {
        $this->maxCacheSize = $maxCacheSize;
    }
    
    /**
     * Get an image from cache
     */
    public function get(string $key): ?GdImage
    {
        if (!isset($this->cache[$key])) {
            return null;
        }
        
        // Update access time
        $this->accessTimes[$key] = microtime(true);
        
        Log::debug('Image cache hit', ['key' => $key]);
        
        return $this->cache[$key];
    }
    
    /**
     * Put an image in cache
     */
    public function put(string $key, GdImage $image): void
    {
        // Evict if cache is full
        if (count($this->cache) >= $this->maxCacheSize && !isset($this->cache[$key])) {
            $this->evictLeastRecentlyUsed();
        }
        
        $this->cache[$key] = $image;
        $this->accessTimes[$key] = microtime(true);
        
        Log::debug('Image cached', [
            'key' => $key,
            'cache_size' => count($this->cache),
        ]);
    }
    
    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }
    
    /**
     * Clear the entire cache
     */
    public function clear(): int
    {
        $count = 0;
        foreach ($this->cache as $image) {
            imagedestroy($image);
            $count++;
        }
        
        $this->cache = [];
        $this->accessTimes = [];
        
        Log::info('Image cache cleared', ['images_destroyed' => $count]);
        
        return $count;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'size' => count($this->cache),
            'max_size' => $this->maxCacheSize,
            'keys' => array_keys($this->cache),
        ];
    }
    
    /**
     * Evict the least recently used item
     */
    private function evictLeastRecentlyUsed(): void
    {
        if (empty($this->accessTimes)) {
            return;
        }
        
        asort($this->accessTimes);
        $lruKey = array_key_first($this->accessTimes);
        
        if (isset($this->cache[$lruKey])) {
            imagedestroy($this->cache[$lruKey]);
            unset($this->cache[$lruKey]);
            unset($this->accessTimes[$lruKey]);
            
            Log::debug('Evicted LRU image from cache', ['key' => $lruKey]);
        }
    }
}




