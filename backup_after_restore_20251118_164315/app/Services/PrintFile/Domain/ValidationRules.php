<?php

namespace App\Services\PrintFile\Domain;

use Illuminate\Support\Facades\File;

/**
 * Validation rules for print file generation
 * 
 * Centralized validation logic for input data
 */
class ValidationRules
{
    /**
     * Validate block configuration
     */
    public static function validateBlockConfig(array $config): array
    {
        $errors = [];
        
        // Validate image path
        if (empty($config['image_path'])) {
            $errors[] = 'Image path is required';
        } elseif (!File::exists(storage_path('app/public/' . $config['image_path']))) {
            $errors[] = 'Image file does not exist: ' . $config['image_path'];
        }
        
        // Validate dimensions
        $cols = $config['cols'] ?? 1;
        $rows = $config['rows'] ?? 1;
        
        if ($cols < 1 || $cols > 10) {
            $errors[] = 'Cols must be between 1 and 10';
        }
        
        if ($rows < 1 || $rows > 10) {
            $errors[] = 'Rows must be between 1 and 10';
        }
        
        // Validate start position
        $startCol = $config['start_col'] ?? 0;
        $startRow = $config['start_row'] ?? 0;
        
        if ($startCol < 0) {
            $errors[] = 'Start column cannot be negative';
        }
        
        if ($startRow < 0) {
            $errors[] = 'Start row cannot be negative';
        }
        
        // Validate rotation
        $rotate = $config['rotate'] ?? '1';
        if (!in_array($rotate, ['1', '2', '3', '4'])) {
            $errors[] = 'Invalid rotation value: ' . $rotate;
        }
        
        return $errors;
    }
    
    /**
     * Validate text overlay configuration
     */
    public static function validateTextOverlay(array $overlay): array
    {
        $errors = [];
        
        if (empty($overlay['text'])) {
            $errors[] = 'Text content is required';
        }
        
        if (!isset($overlay['x']) || !is_numeric($overlay['x'])) {
            $errors[] = 'Invalid X position';
        }
        
        if (!isset($overlay['y']) || !is_numeric($overlay['y'])) {
            $errors[] = 'Invalid Y position';
        }
        
        $fontSize = $overlay['font_size'] ?? 0;
        if ($fontSize < 1 || $fontSize > 5000) {
            $errors[] = 'Font size must be between 1 and 5000';
        }
        
        return $errors;
    }
    
    /**
     * Validate frame configuration
     */
    public static function validateFrameConfig(array $frameConfig): array
    {
        $errors = [];
        
        if (empty($frameConfig['color_hex'])) {
            $errors[] = 'Frame color is required';
        } elseif (!self::isValidHexColor($frameConfig['color_hex'])) {
            $errors[] = 'Invalid frame color hex: ' . $frameConfig['color_hex'];
        }
        
        return $errors;
    }
    
    /**
     * Check if a string is a valid hex color
     */
    public static function isValidHexColor(string $color): bool
    {
        return (bool) preg_match('/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }
    
    /**
     * Validate zoom format
     */
    public static function validateZoom(string $zoom): bool
    {
        // Zoom can be "0" or "zoomX|zoomY"
        if ($zoom === '0') {
            return true;
        }
        
        $parts = explode('|', $zoom);
        foreach ($parts as $part) {
            if (!is_numeric($part)) {
                return false;
            }
        }
        
        return true;
    }
}











