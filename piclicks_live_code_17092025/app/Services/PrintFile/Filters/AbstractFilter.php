<?php

namespace App\Services\PrintFile\Filters;

use App\Services\PrintFile\Contracts\FilterInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Base class for all filters
 * 
 * Provides common functionality and error handling
 */
abstract class AbstractFilter implements FilterInterface
{
    protected string $name;
    protected string $description;
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Apply the filter to a canvas
     * Template method pattern - subclasses implement applyFilter()
     */
    public function apply(GdImage $canvas): bool
    {
        try {
            Log::debug('Applying filter', ['filter' => $this->name]);
            
            $result = $this->applyFilter($canvas);
            
            if ($result) {
                Log::info('Filter applied successfully', ['filter' => $this->name]);
            } else {
                Log::warning('Filter application returned false', ['filter' => $this->name]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Filter application failed', [
                'filter' => $this->name,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Subclasses implement the actual filter logic
     */
    abstract protected function applyFilter(GdImage $canvas): bool;
}










