<?php

namespace App\Services\PrintFile\Domain;

/**
 * Pure functions for dimension calculations
 * 
 * Stateless calculator for converting between units and calculating sizes
 */
class DimensionCalculator
{
    /**
     * Convert millimeters to pixels at a given DPI
     */
    public static function mmToPx(float $mm, float $pxPerMm): int
    {
        return (int) round($mm * $pxPerMm);
    }
    
    /**
     * Convert pixels to millimeters at a given DPI
     */
    public static function pxToMm(int $px, float $pxPerMm): float
    {
        return $px / $pxPerMm;
    }
    
    /**
     * Calculate pixels per mm for a given DPI
     */
    public static function calculatePxPerMm(int $dpi, float $mmPerInch = 25.4): float
    {
        return $dpi / $mmPerInch;
    }
    
    /**
     * Calculate scaled dimensions to cover an area (like CSS object-fit: cover)
     */
    public static function calculateCoverDimensions(
        int $sourceW,
        int $sourceH,
        int $targetW,
        int $targetH,
        float $zoom = 0
    ): array {
        $targetAspect = $targetW / $targetH;
        $sourceAspect = $sourceW / $sourceH;
        
        if ($sourceAspect > $targetAspect) {
            // Image is wider - fit height
            $scaledH = $targetH;
            $scaledW = (int) ($targetH * $sourceAspect);
        } else {
            // Image is taller - fit width
            $scaledW = $targetW;
            $scaledH = (int) ($targetW / $sourceAspect);
        }
        
        // Apply zoom if present
        if ($zoom > 0) {
            $scaledW = (int) ($scaledW * (1 + $zoom));
            $scaledH = (int) ($scaledH * (1 + $zoom));
        }
        
        return ['width' => $scaledW, 'height' => $scaledH];
    }
    
    /**
     * Calculate scaled dimensions to contain within an area (like CSS object-fit: contain)
     */
    public static function calculateContainDimensions(
        int $sourceW,
        int $sourceH,
        int $targetW,
        int $targetH
    ): array {
        $targetAspect = $targetW / $targetH;
        $sourceAspect = $sourceW / $sourceH;
        
        if ($sourceAspect > $targetAspect) {
            // Image is wider - fit width
            $scaledW = $targetW;
            $scaledH = (int) ($targetW / $sourceAspect);
        } else {
            // Image is taller - fit height
            $scaledH = $targetH;
            $scaledW = (int) ($targetH * $sourceAspect);
        }
        
        return ['width' => $scaledW, 'height' => $scaledH];
    }
    
    /**
     * Calculate rotated bounding box dimensions
     */
    public static function calculateRotatedBounds(
        int $width,
        int $height,
        float $rotationDegrees
    ): array {
        $rotRad = deg2rad(abs($rotationDegrees));
        $rotatedWidth = abs($width * cos($rotRad)) + abs($height * sin($rotRad));
        $rotatedHeight = abs($width * sin($rotRad)) + abs($height * cos($rotRad));
        
        return [
            'width' => (int) ceil($rotatedWidth),
            'height' => (int) ceil($rotatedHeight)
        ];
    }
}




