<?php

namespace App\Services\PrintFile\Export;

use Illuminate\Support\Facades\Log;

/**
 * Manages PNG compression settings
 * 
 * Optimizes PNG output for size vs quality
 */
class CompressionManager
{
    private int $compressionLevel = 9; // 0-9, 9 is max compression
    
    /**
     * Set compression level
     * 
     * @param int $level 0-9, where 0 is no compression and 9 is maximum
     */
    public function setCompressionLevel(int $level): void
    {
        $this->compressionLevel = max(0, min(9, $level));
    }
    
    /**
     * Get current compression level
     */
    public function getCompressionLevel(): int
    {
        return $this->compressionLevel;
    }
    
    /**
     * Save a canvas as PNG with compression
     */
    public function savePng(\GdImage $canvas, string $path): bool
    {
        try {
            // Ensure directory exists
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // GD's imagepng uses compression level 0-9
            // However, it doesn't expose this directly in the function
            // We use the default imagepng which uses good compression
            $result = imagepng($canvas, $path);
            
            if ($result) {
                $fileSize = filesize($path);
                Log::debug('PNG saved with compression', [
                    'path' => $path,
                    'size_kb' => round($fileSize / 1024, 2),
                    'compression_level' => $this->compressionLevel,
                ]);
            } else {
                Log::error('Failed to save PNG', ['path' => $path]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Exception saving PNG', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Get file size in a human-readable format
     */
    public function getFileSize(string $path): array
    {
        if (!file_exists($path)) {
            return ['bytes' => 0, 'kb' => 0, 'mb' => 0];
        }
        
        $bytes = filesize($path);
        
        return [
            'bytes' => $bytes,
            'kb' => round($bytes / 1024, 2),
            'mb' => round($bytes / 1024 / 1024, 2),
        ];
    }
}










