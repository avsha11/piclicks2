<?php

namespace App\Services\PrintFile\Resources;

use App\Services\PrintFile\Config\PlatformConfig;
use Illuminate\Support\Facades\Log;

/**
 * Monitors memory usage to prevent exhaustion
 * 
 * Provides early warning when memory is getting low
 */
class MemoryMonitor
{
    private int $memoryLimit;
    private int $peakUsage = 0;
    
    public function __construct()
    {
        $this->memoryLimit = PlatformConfig::getMemoryLimit();
    }
    
    /**
     * Get current memory usage statistics
     */
    public function getUsage(): array
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        
        // Update peak tracking
        if ($peak > $this->peakUsage) {
            $this->peakUsage = $peak;
        }
        
        $percentage = $this->memoryLimit > 0 
            ? ($current / $this->memoryLimit) * 100 
            : 0;
        
        return [
            'current' => $current,
            'current_mb' => round($current / 1024 / 1024, 2),
            'peak' => $peak,
            'peak_mb' => round($peak / 1024 / 1024, 2),
            'limit' => $this->memoryLimit,
            'limit_mb' => round($this->memoryLimit / 1024 / 1024, 2),
            'percentage' => round($percentage, 2),
            'available' => $this->memoryLimit - $current,
            'available_mb' => round(($this->memoryLimit - $current) / 1024 / 1024, 2),
        ];
    }
    
    /**
     * Check if memory usage is approaching the limit
     */
    public function isLimitApproaching(float $threshold = 0.8): bool
    {
        if ($this->memoryLimit <= 0) {
            return false; // Unlimited memory
        }
        
        $current = memory_get_usage(true);
        $percentage = $current / $this->memoryLimit;
        
        $approaching = $percentage >= $threshold;
        
        if ($approaching) {
            Log::warning('Memory limit approaching', [
                'current_mb' => round($current / 1024 / 1024, 2),
                'limit_mb' => round($this->memoryLimit / 1024 / 1024, 2),
                'percentage' => round($percentage * 100, 2),
                'threshold' => round($threshold * 100, 2),
            ]);
        }
        
        return $approaching;
    }
    
    /**
     * Check if there's enough memory for an operation
     */
    public function hasEnoughMemory(int $requiredBytes): bool
    {
        if ($this->memoryLimit <= 0) {
            return true; // Unlimited memory
        }
        
        $current = memory_get_usage(true);
        $available = $this->memoryLimit - $current;
        
        // Keep a safety margin
        $safetyMargin = $this->memoryLimit * 0.1; // 10% safety margin
        $effectiveAvailable = $available - $safetyMargin;
        
        return $effectiveAvailable >= $requiredBytes;
    }
    
    /**
     * Estimate memory required for a canvas
     */
    public function estimateCanvasMemory(int $width, int $height): int
    {
        // GD truecolor images use 4 bytes per pixel (RGBA)
        // Add overhead for PHP structures (roughly 50%)
        return (int) (($width * $height * 4) * 1.5);
    }
    
    /**
     * Log current memory status
     */
    public function logStatus(string $context = ''): void
    {
        $usage = $this->getUsage();
        
        Log::info('Memory status' . ($context ? " ($context)" : ''), [
            'current_mb' => $usage['current_mb'],
            'peak_mb' => $usage['peak_mb'],
            'limit_mb' => $usage['limit_mb'],
            'percentage' => $usage['percentage'],
            'available_mb' => $usage['available_mb'],
        ]);
    }
}




