<?php

namespace App\Services\PrintFile\Text;

use Illuminate\Support\Facades\Log;

/**
 * Caches font file handles and metadata
 * 
 * Reduces repeated font file access
 */
class FontCache
{
    private array $cache = [];
    
    /**
     * Get cached font path
     */
    public function get(string $fontFamily): ?string
    {
        return $this->cache[$fontFamily] ?? null;
    }
    
    /**
     * Cache a font path
     */
    public function put(string $fontFamily, string $fontPath): void
    {
        $this->cache[$fontFamily] = $fontPath;
        
        Log::debug('Font path cached', [
            'family' => $fontFamily,
            'path' => $fontPath,
        ]);
    }
    
    /**
     * Check if font is cached
     */
    public function has(string $fontFamily): bool
    {
        return isset($this->cache[$fontFamily]);
    }
    
    /**
     * Clear the cache
     */
    public function clear(): void
    {
        $count = count($this->cache);
        $this->cache = [];
        
        Log::info('Font cache cleared', ['count' => $count]);
    }
    
    /**
     * Get cache stats
     */
    public function getStats(): array
    {
        return [
            'size' => count($this->cache),
            'fonts' => array_keys($this->cache),
        ];
    }
}









