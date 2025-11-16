<?php

namespace App\Services\PrintFile\Frame;

use App\Services\PrintFile\Config\PrintConstants;
use Illuminate\Support\Facades\Log;

/**
 * Calculates frame geometry
 * 
 * Handles frame dimensions, inner hole calculations
 */
class FrameGeometry
{
    /**
     * Calculate frame dimensions for a block
     */
    public function calculateFrameDimensions(int $blockW, int $blockH): array
    {
        $frameDimensions = PrintConstants::getFrameDimensions();
        
        return [
            'outerWidth' => $blockW,
            'outerHeight' => $blockH,
            'thickness' => $frameDimensions['printThickness'],
            'innerX' => $frameDimensions['printThickness'],
            'innerY' => $frameDimensions['printThickness'],
            'innerWidth' => $blockW - (2 * $frameDimensions['printThickness']),
            'innerHeight' => $blockH - (2 * $frameDimensions['printThickness']),
            'outerRadius' => PrintConstants::getPrintTileDimensions()['cornerRadius'],
            'innerRadius' => $frameDimensions['innerCornerRadius'],
        ];
    }
    
    /**
     * Validate frame geometry
     */
    public function validateGeometry(array $geometry): bool
    {
        if ($geometry['innerWidth'] <= 0 || $geometry['innerHeight'] <= 0) {
            Log::error('Invalid frame geometry', [
                'innerWidth' => $geometry['innerWidth'],
                'innerHeight' => $geometry['innerHeight'],
            ]);
            return false;
        }
        
        return true;
    }
}









