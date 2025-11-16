<?php

namespace App\Services\PrintFile\Frame;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Composites frame sections onto tiles
 * 
 * Handles applying frame sections to individual tiles
 */
class FrameCompositor
{
    /**
     * Apply a section of a frame to a tile canvas
     */
    public function applyFrameSection(
        GdImage $tileCanvas,
        GdImage $frameCanvas,
        int $cropX,
        int $cropY,
        int $tileW,
        int $tileH
    ): bool {
        try {
            // Composite frame onto tile with alpha blending
            imagealphablending($tileCanvas, true);
            
            $success = imagecopy(
                $tileCanvas,
                $frameCanvas,
                0, 0,
                $cropX, $cropY,
                $tileW, $tileH
            );
            
            imagealphablending($tileCanvas, false);
            
            if (!$success) {
                Log::error('Failed to copy frame section to tile');
                return false;
            }
            
            Log::debug('Frame section applied', [
                'crop_pos' => "({$cropX},{$cropY})",
                'tile_size' => "{$tileW}x{$tileH}",
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception applying frame section', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}









