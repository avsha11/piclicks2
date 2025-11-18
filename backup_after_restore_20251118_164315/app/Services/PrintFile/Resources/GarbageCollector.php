<?php

namespace App\Services\PrintFile\Resources;

use Illuminate\Support\Facades\Log;

/**
 * Handles garbage collection and cleanup operations
 * 
 * Provides utilities for forcing cleanup when needed
 */
class GarbageCollector
{
    private MemoryMonitor $memoryMonitor;
    private array $cleanupCallbacks = [];
    
    public function __construct(MemoryMonitor $memoryMonitor)
    {
        $this->memoryMonitor = $memoryMonitor;
    }
    
    /**
     * Register a cleanup callback
     */
    public function registerCleanupCallback(callable $callback, string $identifier = ''): void
    {
        $this->cleanupCallbacks[] = [
            'callback' => $callback,
            'identifier' => $identifier,
        ];
    }
    
    /**
     * Force garbage collection
     */
    public function collect(): array
    {
        $beforeMemory = memory_get_usage(true);
        
        // Execute all cleanup callbacks
        $callbackCount = 0;
        foreach ($this->cleanupCallbacks as $item) {
            try {
                $item['callback']();
                $callbackCount++;
            } catch (\Exception $e) {
                Log::error('Cleanup callback failed', [
                    'identifier' => $item['identifier'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Clear callbacks after execution
        $this->cleanupCallbacks = [];
        
        // Force PHP garbage collection
        $cycles = gc_collect_cycles();
        
        $afterMemory = memory_get_usage(true);
        $freed = $beforeMemory - $afterMemory;
        
        $result = [
            'callbacks_executed' => $callbackCount,
            'gc_cycles' => $cycles,
            'memory_before' => $beforeMemory,
            'memory_after' => $afterMemory,
            'memory_freed' => $freed,
            'memory_freed_mb' => round($freed / 1024 / 1024, 2),
        ];
        
        Log::info('Garbage collection completed', $result);
        
        return $result;
    }
    
    /**
     * Collect if memory threshold is reached
     */
    public function collectIfNeeded(float $threshold = 0.8): bool
    {
        if ($this->memoryMonitor->isLimitApproaching($threshold)) {
            Log::info('Memory threshold reached, triggering garbage collection', [
                'threshold' => $threshold * 100 . '%',
            ]);
            
            $this->collect();
            return true;
        }
        
        return false;
    }
    
    /**
     * Schedule automatic cleanup at script end
     */
    public function scheduleShutdownCleanup(): void
    {
        register_shutdown_function(function () {
            Log::info('Shutdown cleanup triggered');
            $this->collect();
        });
    }
    
    /**
     * Get pending cleanup callback count
     */
    public function getPendingCallbackCount(): int
    {
        return count($this->cleanupCallbacks);
    }
}











