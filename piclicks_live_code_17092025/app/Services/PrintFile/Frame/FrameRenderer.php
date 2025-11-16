<?php

namespace App\Services\PrintFile\Frame;

use App\Services\PrintFile\Contracts\FrameRendererInterface;
use App\Services\PrintFile\Domain\ColorValue;
use App\Services\PrintFile\Geometry\RoundedCornerApplicator;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Main frame renderer (Facade for frame subsystem)
 * 
 * Renders frames with proper geometry and compositing
 */
class FrameRenderer implements FrameRendererInterface
{
    private FrameGeometry $geometry;
    private FrameCompositor $compositor;
    private RoundedCornerApplicator $cornerApplicator;
    
    public function __construct(
        FrameGeometry $geometry,
        FrameCompositor $compositor,
        RoundedCornerApplicator $cornerApplicator
    ) {
        $this->geometry = $geometry;
        $this->compositor = $compositor;
        $this->cornerApplicator = $cornerApplicator;
    }
    
    /**
     * Render a frame for an entire block
     */
    public function renderFrame(int $blockW, int $blockH, array $frameConfig): ?GdImage
    {
        try {
            // Calculate frame geometry
            $geometry = $this->geometry->calculateFrameDimensions($blockW, $blockH);
            
            if (!$this->geometry->validateGeometry($geometry)) {
                Log::error('Invalid frame geometry for block size', [
                    'blockSize' => "{$blockW}x{$blockH}",
                ]);
                return null;
            }
            
            // Get frame color
            $colorHex = $frameConfig['color_hex'] ?? '#000000';
            $color = ColorValue::fromHex($colorHex);
            
            // Create frame canvas
            $frameCanvas = imagecreatetruecolor($blockW, $blockH);
            if ($frameCanvas === false) {
                Log::error('Failed to create frame canvas');
                return null;
            }
            
            imagealphablending($frameCanvas, false);
            imagesavealpha($frameCanvas, true);
            
            // Fill with transparent background
            $transparent = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
            imagefilledrectangle($frameCanvas, 0, 0, $blockW, $blockH, $transparent);
            
            // Draw full-bleed rounded rect with outer radius
            $frameColor = $color->allocateColor($frameCanvas);
            $this->cornerApplicator->drawFilledRoundedRect(
                $frameCanvas,
                0, 0,
                $blockW, $blockH,
                $geometry['outerRadius'],
                $frameColor
            );
            
            // Punch inner hole
            $this->punchInnerHole($frameCanvas, $geometry, $blockW, $blockH);
            
            Log::info('Frame rendered', [
                'size' => "{$blockW}x{$blockH}px",
                'thickness' => $geometry['thickness'] . 'px',
                'color' => $colorHex,
            ]);
            
            return $frameCanvas;
        } catch (\Exception $e) {
            Log::error('Exception rendering frame', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
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
        return $this->compositor->applyFrameSection(
            $tileCanvas,
            $frameCanvas,
            $cropX,
            $cropY,
            $tileW,
            $tileH
        );
    }
    
    /**
     * Punch inner hole in the frame
     */
    private function punchInnerHole(GdImage $frameCanvas, array $geometry, int $blockW, int $blockH): bool
    {
        try {
            // Create inner mask
            $innerMask = imagecreatetruecolor($blockW, $blockH);
            imagealphablending($innerMask, false);
            imagesavealpha($innerMask, true);
            
            $transparent = imagecolorallocatealpha($innerMask, 0, 0, 0, 127);
            imagefilledrectangle($innerMask, 0, 0, $blockW, $blockH, $transparent);
            
            $maskOpaque = imagecolorallocate($innerMask, 255, 255, 255);
            $this->cornerApplicator->drawFilledRoundedRect(
                $innerMask,
                $geometry['innerX'],
                $geometry['innerY'],
                $geometry['innerWidth'],
                $geometry['innerHeight'],
                $geometry['innerRadius'],
                $maskOpaque
            );
            
            // Punch the hole pixel by pixel
            for ($x = 0; $x < $blockW; $x++) {
                for ($y = 0; $y < $blockH; $y++) {
                    $maskColor = imagecolorat($innerMask, $x, $y);
                    if (($maskColor & 0xFF) === 255) {
                        $trans = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
                        imagesetpixel($frameCanvas, $x, $y, $trans);
                    }
                }
            }
            
            imagedestroy($innerMask);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception punching inner hole', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}










