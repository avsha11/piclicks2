<?php

namespace App\Services\PrintFile\Export;

use App\Services\PrintFile\Contracts\TileExporterInterface;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Exports tiles to disk
 * 
 * Handles file naming, saving, and compression
 */
class TileExporter implements TileExporterInterface
{
    private FileNamingStrategy $namingStrategy;
    private CompressionManager $compressionManager;
    
    public function __construct(
        FileNamingStrategy $namingStrategy,
        CompressionManager $compressionManager
    ) {
        $this->namingStrategy = $namingStrategy;
        $this->compressionManager = $compressionManager;
    }
    
    /**
     * Save a tile canvas to disk
     */
    public function saveTile(
        GdImage $canvas,
        int $row,
        int $col,
        int $totalRows,
        int $totalCols
    ): ?string {
        try {
            $filename = $this->generateFilename($row, $col, $totalRows, $totalCols);
            $fullPath = $this->namingStrategy->getFullPath($filename);
            
            $success = $this->compressionManager->savePng($canvas, $fullPath);
            
            if ($success) {
                $size = $this->compressionManager->getFileSize($fullPath);
                Log::info('Tile saved', [
                    'path' => $filename,
                    'row' => $row,
                    'col' => $col,
                    'size_kb' => $size['kb'],
                ]);
                return $filename;
            } else {
                Log::error('Failed to save tile', [
                    'path' => $filename,
                    'row' => $row,
                    'col' => $col,
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception saving tile', [
                'error' => $e->getMessage(),
                'row' => $row,
                'col' => $col,
            ]);
            return null;
        }
    }
    
    /**
     * Crop a tile from a block canvas
     */
    public function cropTileFromBlock(
        GdImage $blockCanvas,
        int $col,
        int $row,
        int $tileW,
        int $tileH,
        int $clearTileW,
        int $clearTileH
    ): ?GdImage {
        $cropper = new TileCropper();
        return $cropper->cropTileFromBlock(
            $blockCanvas,
            $col,
            $row,
            $tileW,
            $tileH,
            $clearTileW,
            $clearTileH
        );
    }
    
    /**
     * Generate a filename for a tile
     */
    public function generateFilename(
        int $row,
        int $col,
        int $totalRows,
        int $totalCols
    ): string {
        return $this->namingStrategy->generateFilename($row, $col, $totalRows, $totalCols);
    }
}




