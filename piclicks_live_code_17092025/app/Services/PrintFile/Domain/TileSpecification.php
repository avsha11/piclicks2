<?php

namespace App\Services\PrintFile\Domain;

/**
 * Value object for tile dimensions and specifications
 * 
 * Immutable object that encapsulates tile size calculations
 */
class TileSpecification
{
    private int $clearTileWPx;
    private int $clearTileHPx;
    private int $printTileWPx;
    private int $printTileHPx;
    private int $bleedPx;
    private int $clearCornerRadiusPx;
    private int $printCornerRadiusPx;
    private float $pxPerMm;
    
    public function __construct(
        int $clearTileWPx,
        int $clearTileHPx,
        int $printTileWPx,
        int $printTileHPx,
        int $bleedPx,
        int $clearCornerRadiusPx,
        int $printCornerRadiusPx,
        float $pxPerMm
    ) {
        $this->clearTileWPx = $clearTileWPx;
        $this->clearTileHPx = $clearTileHPx;
        $this->printTileWPx = $printTileWPx;
        $this->printTileHPx = $printTileHPx;
        $this->bleedPx = $bleedPx;
        $this->clearCornerRadiusPx = $clearCornerRadiusPx;
        $this->printCornerRadiusPx = $printCornerRadiusPx;
        $this->pxPerMm = $pxPerMm;
    }
    
    public function getClearTileWidth(): int
    {
        return $this->clearTileWPx;
    }
    
    public function getClearTileHeight(): int
    {
        return $this->clearTileHPx;
    }
    
    public function getPrintTileWidth(): int
    {
        return $this->printTileWPx;
    }
    
    public function getPrintTileHeight(): int
    {
        return $this->printTileHPx;
    }
    
    public function getBleedPixels(): int
    {
        return $this->bleedPx;
    }
    
    public function getClearCornerRadius(): int
    {
        return $this->clearCornerRadiusPx;
    }
    
    public function getPrintCornerRadius(): int
    {
        return $this->printCornerRadiusPx;
    }
    
    public function getPixelsPerMm(): float
    {
        return $this->pxPerMm;
    }
    
    /**
     * Calculate block dimensions for a multi-tile block
     */
    public function calculateBlockDimensions(int $cols, int $rows): array
    {
        $blockClearW = $cols * $this->clearTileWPx;
        $blockClearH = $rows * $this->clearTileHPx;
        $blockPrintW = $blockClearW + (2 * $this->bleedPx);
        $blockPrintH = $blockClearH + (2 * $this->bleedPx);
        
        return [
            'clearWidth' => $blockClearW,
            'clearHeight' => $blockClearH,
            'printWidth' => $blockPrintW,
            'printHeight' => $blockPrintH,
        ];
    }
}










