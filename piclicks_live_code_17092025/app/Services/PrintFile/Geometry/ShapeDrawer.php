<?php

namespace App\Services\PrintFile\Geometry;

use GdImage;
use Illuminate\Support\Facades\Log;

/**
 * Draws basic shapes on canvases
 * 
 * Primitive shape drawing utilities
 */
class ShapeDrawer
{
    /**
     * Draw a circle
     */
    public function drawCircle(
        GdImage $canvas,
        int $centerX,
        int $centerY,
        int $radius,
        int $color,
        bool $filled = true
    ): bool {
        try {
            if ($filled) {
                imagefilledellipse($canvas, $centerX, $centerY, $radius * 2, $radius * 2, $color);
            } else {
                imageellipse($canvas, $centerX, $centerY, $radius * 2, $radius * 2, $color);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to draw circle', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Draw a rectangle
     */
    public function drawRectangle(
        GdImage $canvas,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $color,
        bool $filled = true
    ): bool {
        try {
            if ($filled) {
                imagefilledrectangle($canvas, $x1, $y1, $x2, $y2, $color);
            } else {
                imagerectangle($canvas, $x1, $y1, $x2, $y2, $color);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to draw rectangle', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Draw a line
     */
    public function drawLine(
        GdImage $canvas,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $color,
        int $thickness = 1
    ): bool {
        try {
            imagesetthickness($canvas, $thickness);
            imageline($canvas, $x1, $y1, $x2, $y2, $color);
            imagesetthickness($canvas, 1); // Reset
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to draw line', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Draw a polygon
     */
    public function drawPolygon(
        GdImage $canvas,
        array $points,
        int $color,
        bool $filled = true
    ): bool {
        try {
            if ($filled) {
                imagefilledpolygon($canvas, $points, $color);
            } else {
                imagepolygon($canvas, $points, $color);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to draw polygon', ['error' => $e->getMessage()]);
            return false;
        }
    }
}










