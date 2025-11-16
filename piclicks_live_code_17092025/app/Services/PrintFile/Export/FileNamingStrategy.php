<?php

namespace App\Services\PrintFile\Export;

/**
 * Strategy for generating file names for tiles
 * 
 * Handles naming conventions for single and multi-tile blocks
 */
class FileNamingStrategy
{
    private string $baseDirectory = 'designCollageImages';
    
    /**
     * Generate a filename for a tile
     */
    public function generateFilename(
        int $row,
        int $col,
        int $totalRows,
        int $totalCols
    ): string {
        $timestamp = time();
        $uniqueId = uniqid();
        
        if ($totalRows === 1 && $totalCols === 1) {
            // Single tile
            return sprintf('%s/tile_%s_%s.png', 
                $this->baseDirectory, 
                $timestamp, 
                $uniqueId
            );
        } else {
            // Multi-tile block
            return sprintf('%s/tile_r%d_c%d_%s_%s.png',
                $this->baseDirectory,
                $row + 1, // 1-based for human readability
                $col + 1,
                $timestamp,
                $uniqueId
            );
        }
    }
    
    /**
     * Get the full storage path for a file
     */
    public function getFullPath(string $relativePath): string
    {
        return storage_path('app/public/' . $relativePath);
    }
    
    /**
     * Set the base directory for files
     */
    public function setBaseDirectory(string $directory): void
    {
        $this->baseDirectory = $directory;
    }
    
    /**
     * Get the base directory
     */
    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }
}










