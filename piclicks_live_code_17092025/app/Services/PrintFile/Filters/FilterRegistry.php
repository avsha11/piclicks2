<?php

namespace App\Services\PrintFile\Filters;

use App\Services\PrintFile\Contracts\FilterInterface;
use Illuminate\Support\Facades\Log;

/**
 * Registry for managing available filters
 * 
 * Provides filter lookup and validation
 */
class FilterRegistry
{
    private array $filters = [];
    
    public function __construct()
    {
        // Register all filters
        $this->register(new NoirFilter());
        $this->register(new StarkFilter());
        $this->register(new ScandiFilter());
        $this->register(new CapriFilter());
        $this->register(new NordicFilter());
        $this->register(new BelvederFilter());
    }
    
    /**
     * Register a filter
     */
    public function register(FilterInterface $filter): void
    {
        $this->filters[$filter->getName()] = $filter;
        
        Log::debug('Filter registered', [
            'name' => $filter->getName(),
            'description' => $filter->getDescription(),
        ]);
    }
    
    /**
     * Get a filter by name
     */
    public function get(string $name): ?FilterInterface
    {
        return $this->filters[$name] ?? null;
    }
    
    /**
     * Check if a filter exists
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }
    
    /**
     * Get all available filters
     */
    public function all(): array
    {
        return $this->filters;
    }
    
    /**
     * Get filter names
     */
    public function getNames(): array
    {
        return array_keys($this->filters);
    }
    
    /**
     * Apply a filter by name to a canvas
     */
    public function applyFilter(string $name, \GdImage $canvas): bool
    {
        $filter = $this->get($name);
        
        if ($filter === null) {
            Log::warning('Filter not found', ['name' => $name]);
            return false;
        }
        
        return $filter->apply($canvas);
    }
}









