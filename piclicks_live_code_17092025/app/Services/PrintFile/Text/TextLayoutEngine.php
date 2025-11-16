<?php

namespace App\Services\PrintFile\Text;

use App\Services\PrintFile\Config\PrintConstants;
use Illuminate\Support\Facades\Log;

/**
 * Calculates text layout positions and scaling
 * 
 * Handles coordinate transformation from editor to print space
 */
class TextLayoutEngine
{
    /**
     * Scale position from editor tile space to print space
     */
    public function scalePosition(float $editorX, float $editorY, int $clearTileW, int $clearTileH): array
    {
        $editorTileW = PrintConstants::EDITOR_TILE_WIDTH_PX;
        $editorTileH = PrintConstants::EDITOR_TILE_HEIGHT_PX;
        
        $scaleFactorX = $clearTileW / $editorTileW;
        $scaleFactorY = $clearTileH / $editorTileH;
        
        return [
            'x' => (int) ($editorX * $scaleFactorX),
            'y' => (int) ($editorY * $scaleFactorY),
            'scaleX' => $scaleFactorX,
            'scaleY' => $scaleFactorY,
        ];
    }
    
    /**
     * Scale font size from editor to print space
     */
    public function scaleFontSize(int $editorFontSize, int $clearTileW, int $clearTileH): int
    {
        $editorTileW = PrintConstants::EDITOR_TILE_WIDTH_PX;
        $editorTileH = PrintConstants::EDITOR_TILE_HEIGHT_PX;
        
        $scaleFactorX = $clearTileW / $editorTileW;
        $scaleFactorY = $clearTileH / $editorTileH;
        $scaleFactor = ($scaleFactorX + $scaleFactorY) / 2;
        
        $printFontSize = (int) ($editorFontSize * $scaleFactor * 2);
        
        // Cap at maximum to prevent memory issues
        if ($printFontSize > PrintConstants::MAX_FONT_SIZE_PX) {
            Log::warning('Font size capped', [
                'calculated' => $printFontSize,
                'capped_to' => PrintConstants::MAX_FONT_SIZE_PX,
            ]);
            $printFontSize = PrintConstants::MAX_FONT_SIZE_PX;
        }
        
        return $printFontSize;
    }
    
    /**
     * Calculate text offset for centering
     */
    public function calculateTranslateOffset(
        string $text,
        string $fontPath,
        int $fontSize,
        float $translateXPercent,
        float $translateYPercent
    ): array {
        if ($translateXPercent == 0 && $translateYPercent == 0) {
            return ['x' => 0, 'y' => 0];
        }
        
        try {
            // Calculate text bounding box
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox === false) {
                return ['x' => 0, 'y' => 0];
            }
            
            $textWidth = $bbox[2] - $bbox[0];
            $textHeight = $bbox[1] - $bbox[7];
            
            $offsetX = (int) ($textWidth * ($translateXPercent / 100));
            $offsetY = (int) ($textHeight * ($translateYPercent / 100));
            
            return ['x' => $offsetX, 'y' => $offsetY];
        } catch (\Exception $e) {
            Log::error('Failed to calculate translate offset', [
                'error' => $e->getMessage(),
            ]);
            return ['x' => 0, 'y' => 0];
        }
    }
}









