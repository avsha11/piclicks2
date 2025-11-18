<?php

namespace App\Services\PrintFile\Resources;

use App\Services\PrintFile\Contracts\ResourceManagerInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Manages GD image resources to prevent memory leaks
 * 
 * Tracks all GD resources and provides cleanup functionality
 */
class ResourceManager implements ResourceManagerInterface
{
    private array $resources = [];
    private MemoryMonitor $memoryMonitor;
    
    public function __construct(MemoryMonitor $memoryMonitor)
    {
        $this->memoryMonitor = $memoryMonitor;
    }
    
    /**
     * Track a GD resource for later cleanup
     */
    public function track(GdImage $resource, string $identifier = ''): void
    {
        $id = spl_object_id($resource);
        
        $this->resources[$id] = [
            'resource' => $resource,
            'identifier' => $identifier,
            'created_at' => microtime(true),
            'memory_at_creation' => memory_get_usage(true),
        ];
        
        Log::debug('Resource tracked', [
            'id' => $id,
            'identifier' => $identifier,
            'total_tracked' => count($this->resources),
        ]);
    }
    
    /**
     * Untrack and destroy a resource
     */
    public function destroy(GdImage $resource): bool
    {
        $id = spl_object_id($resource);
        
        if (!isset($this->resources[$id])) {
            Log::warning('Attempted to destroy untracked resource', ['id' => $id]);
            return false;
        }
        
        try {
            imagedestroy($resource);
            unset($this->resources[$id]);
            
            Log::debug('Resource destroyed', [
                'id' => $id,
                'remaining' => count($this->resources),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to destroy resource', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Destroy all tracked resources
     */
    public function destroyAll(): int
    {
        $count = 0;
        
        foreach ($this->resources as $id => $data) {
            try {
                imagedestroy($data['resource']);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to destroy resource during cleanup', [
                    'id' => $id,
                    'identifier' => $data['identifier'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->resources = [];
        
        Log::info('All resources destroyed', ['count' => $count]);
        
        return $count;
    }
    
    /**
     * Get the current memory usage
     */
    public function getMemoryUsage(): array
    {
        return $this->memoryMonitor->getUsage();
    }
    
    /**
     * Check if memory is approaching limits
     */
    public function isMemoryLimitApproaching(float $threshold = 0.8): bool
    {
        return $this->memoryMonitor->isLimitApproaching($threshold);
    }
    
    /**
     * Get statistics about tracked resources
     */
    public function getStats(): array
    {
        $stats = [
            'total_resources' => count($this->resources),
            'memory_usage' => $this->memoryMonitor->getUsage(),
            'resources' => [],
        ];
        
        foreach ($this->resources as $id => $data) {
            $stats['resources'][] = [
                'id' => $id,
                'identifier' => $data['identifier'],
                'age_seconds' => microtime(true) - $data['created_at'],
                'memory_at_creation' => $data['memory_at_creation'],
            ];
        }
        
        return $stats;
    }
    
    /**
     * Force garbage collection and resource cleanup
     */
    public function forceCleanup(): void
    {
        // Destroy all resources
        $this->destroyAll();
        
        // Force PHP garbage collection
        gc_collect_cycles();
        
        Log::info('Forced cleanup completed', [
            'memory_after' => memory_get_usage(true),
        ]);
    }
}











