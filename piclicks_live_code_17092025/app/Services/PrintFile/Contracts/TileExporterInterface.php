<?php

namespace App\Services\PrintFile\Contracts;

use GdImage;

/**
 * Interface for tile export operations
 * 
 * Handles saving tiles to disk with proper naming and compression
 */
interface TileExporterInterface
{
    /**
     * Save a tile canvas to disk
     * 
     * @param GdImage $canvas The tile canvas to save
     * @param int $row Tile row index
     * @param int $col Tile column index
     * @param int $totalRows Total rows in the block
     * @param int $totalCols Total columns in the block
     * @return string|null Relative file path or null on failure
     */
    public function saveTile(
        GdImage $canvas,
        int $row,
        int $col,
        int $totalRows,
        int $totalCols
    ): ?string;
    
    /**
     * Crop a tile from a block canvas
     * 
     * @param GdImage $blockCanvas The source block canvas
     * @param int $col Column index
     * @param int $row Row index
     * @param int $tileW Tile width including bleed
     * @param int $tileH Tile height including bleed
     * @param int $clearTileW Clear tile width (without bleed)
     * @param int $clearTileH Clear tile height (without bleed)
     * @return GdImage|null Cropped tile canvas or null on failure
     */
    public function cropTileFromBlock(
        GdImage $blockCanvas,
        int $col,
        int $row,
        int $tileW,
        int $tileH,
        int $clearTileW,
        int $clearTileH
    ): ?GdImage;
    
    /**
     * Generate a filename for a tile
     * 
     * @param int $row Tile row index
     * @param int $col Tile column index
     * @param int $totalRows Total rows in the block
     * @param int $totalCols Total columns in the block
     * @return string Generated filename
     */
    public function generateFilename(
        int $row,
        int $col,
        int $totalRows,
        int $totalCols
    ): string;
}










