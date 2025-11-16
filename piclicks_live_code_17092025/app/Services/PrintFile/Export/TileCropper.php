<?php

namespace App\Services\PrintFile\Export;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Crops tiles from block canvases
 * 
 * Handles extracting individual tiles from larger block images
 */
class TileCropper
{
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
        try {
            // Calculate crop position in clear coordinates
            $cropX = $col * $clearTileW;
            $cropY = $row * $clearTileH;
            
            // Create tile canvas
            $tileCanvas = imagecreatetruecolor($tileW, $tileH);
            if ($tileCanvas === false) {
                Log::error('Failed to create tile canvas', [
                    'size' => "{$tileW}x{$tileH}",
                ]);
                return null;
            }
            
            imagealphablending($tileCanvas, false);
            imagesavealpha($tileCanvas, true);
            
            // Fill with transparent background
            $transparent = imagecolorallocatealpha($tileCanvas, 0, 0, 0, 127);
            imagefilledrectangle($tileCanvas, 0, 0, $tileW, $tileH, $transparent);
            
            // Copy tile section from block canvas
            $success = imagecopy(
                $tileCanvas,
                $blockCanvas,
                0, 0,  // Destination: top-left of tile
                $cropX, $cropY,  // Source: position in block
                $tileW, $tileH  // Size: tile dimensions
            );
            
            if (!$success) {
                Log::error('Failed to copy tile from block', [
                    'crop_pos' => "({$cropX},{$cropY})",
                    'tile_size' => "{$tileW}x{$tileH}",
                ]);
                imagedestroy($tileCanvas);
                return null;
            }
            
            Log::debug('Tile cropped successfully', [
                'row' => $row,
                'col' => $col,
                'crop_pos' => "({$cropX},{$cropY})",
            ]);
            
            return $tileCanvas;
        } catch (\Exception $e) {
            Log::error('Exception cropping tile', [
                'error' => $e->getMessage(),
                'row' => $row,
                'col' => $col,
            ]);
            return null;
        }
    }
    
    /**
     * Crop multiple tiles from a block
     */
    public function cropAllTiles(
        GdImage $blockCanvas,
        int $totalCols,
        int $totalRows,
        int $tileW,
        int $tileH,
        int $clearTileW,
        int $clearTileH
    ): array {
        $tiles = [];
        
        for ($row = 0; $row < $totalRows; $row++) {
            for ($col = 0; $col < $totalCols; $col++) {
                $tile = $this->cropTileFromBlock(
                    $blockCanvas,
                    $col,
                    $row,
                    $tileW,
                    $tileH,
                    $clearTileW,
                    $clearTileH
                );
                
                if ($tile !== null) {
                    $tiles[] = [
                        'canvas' => $tile,
                        'row' => $row,
                        'col' => $col,
                    ];
                }
            }
        }
        
        return $tiles;
    }
}









