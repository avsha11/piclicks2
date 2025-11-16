<?php

namespace App\Services\PrintFile\Text;

use App\Services\PrintFile\Contracts\TextRendererInterface;
use App\Services\PrintFile\Domain\ColorValue;
use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Main text renderer (Facade for text subsystem)
 * 
 * Coordinates font resolution, layout, and rendering
 */
class TextRenderer implements TextRendererInterface
{
    private FontResolver $fontResolver;
    private TextLayoutEngine $layoutEngine;
    private RotationHandler $rotationHandler;
    
    public function __construct(
        FontResolver $fontResolver,
        TextLayoutEngine $layoutEngine,
        RotationHandler $rotationHandler
    ) {
        $this->fontResolver = $fontResolver;
        $this->layoutEngine = $layoutEngine;
        $this->rotationHandler = $rotationHandler;
    }
    
    /**
     * Render text overlays onto a canvas
     */
    public function renderTextOverlays(
        GdImage $canvas,
        array $textOverlays,
        int $blockClearW,
        int $blockClearH,
        int $bleedPx
    ): bool {
        if (empty($textOverlays)) {
            return true;
        }
        
        Log::info('Rendering text overlays', [
            'count' => count($textOverlays),
            'block_size' => "{$blockClearW}x{$blockClearH}",
        ]);
        
        foreach ($textOverlays as $idx => $overlay) {
            $text = $overlay['text'] ?? '';
            if (empty($text)) {
                continue;
            }
            
            $success = $this->renderSingleOverlay(
                $canvas,
                $overlay,
                $blockClearW,
                $blockClearH,
                $bleedPx
            );
            
            if (!$success) {
                Log::warning('Failed to render text overlay', ['index' => $idx]);
            }
        }
        
        return true;
    }
    
    /**
     * Render a single text overlay
     */
    private function renderSingleOverlay(
        GdImage $canvas,
        array $overlay,
        int $blockClearW,
        int $blockClearH,
        int $bleedPx
    ): bool {
        $text = $overlay['text'];
        $editorX = (float) ($overlay['x'] ?? 0);
        $editorY = (float) ($overlay['y'] ?? 0);
        $editorFontSize = (int) ($overlay['font_size'] ?? 40);
        $fontFamily = $overlay['font_family'] ?? 'Arial';
        $color = $overlay['color'] ?? '#000000';
        $rotation = (float) ($overlay['rotation'] ?? 0);
        
        // Scale position and font size
        $scaledPos = $this->layoutEngine->scalePosition($editorX, $editorY, $blockClearW, $blockClearH);
        $printX = $scaledPos['x'] + $bleedPx;
        $printY = $scaledPos['y'] + $bleedPx;
        $printFontSize = $this->layoutEngine->scaleFontSize($editorFontSize, $blockClearW, $blockClearH);
        
        // Resolve font
        $fontPath = $this->fontResolver->resolveFontPathWithFallback($fontFamily);
        if ($fontPath === null) {
            Log::error('No font available', ['requested' => $fontFamily]);
            return false;
        }
        
        // Handle translate offset
        $translateXPercent = (float) ($overlay['translate_x_percent'] ?? 0);
        $translateYPercent = (float) ($overlay['translate_y_percent'] ?? 0);
        
        if ($translateXPercent != 0 || $translateYPercent != 0) {
            $offset = $this->layoutEngine->calculateTranslateOffset(
                $text,
                $fontPath,
                $printFontSize,
                $translateXPercent,
                $translateYPercent
            );
            $printX += $offset['x'];
            $printY += $offset['y'];
        }
        
        // Allocate color
        $colorValue = ColorValue::fromHex($color);
        $textColor = $colorValue->allocateColor($canvas);
        
        Log::info('Rendering text', [
            'text' => substr($text, 0, 30),
            'position' => "({$printX},{$printY})",
            'fontSize' => $printFontSize,
            'rotation' => "{$rotation}°",
            'font' => $fontFamily,
        ]);
        
        // Render with or without rotation
        if ($rotation != 0) {
            return $this->renderRotatedText(
                $canvas,
                $text,
                $fontPath,
                $printFontSize,
                $textColor,
                $printX,
                $printY,
                $rotation
            );
        } else {
            imagealphablending($canvas, true);
            $result = imagettftext($canvas, $printFontSize, 0, $printX, $printY, $textColor, $fontPath, $text);
            imagealphablending($canvas, false);
            return $result !== false;
        }
    }
    
    /**
     * Render a single text overlay with rotation
     */
    public function renderRotatedText(
        GdImage $canvas,
        string $text,
        string $fontPath,
        int $fontSize,
        int $textColor,
        int $x,
        int $y,
        float $rotation
    ): bool {
        return $this->rotationHandler->renderRotatedText(
            $canvas,
            $text,
            $fontPath,
            $fontSize,
            $textColor,
            $x,
            $y,
            $rotation
        );
    }
}










