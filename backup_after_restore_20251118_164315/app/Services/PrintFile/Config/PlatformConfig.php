<?php

namespace App\Services\PrintFile\Config;

/**
 * Platform-specific configuration
 * 
 * Handles platform differences (Windows, Mac, Linux)
 */
class PlatformConfig
{
    /**
     * Get font directories for the current platform
     */
    public static function getFontDirectories(): array
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => [
                'C:/Windows/Fonts/',
            ],
            'Darwin' => [
                '/System/Library/Fonts/',
                '/Library/Fonts/',
                '~/Library/Fonts/',
            ],
            'Linux' => [
                '/usr/share/fonts/',
                '/usr/local/share/fonts/',
                '~/.fonts/',
            ],
            default => [
                storage_path('fonts/'),
            ]
        };
    }
    
    /**
     * Get fallback font file names
     */
    public static function getFallbackFonts(): array
    {
        return [
            'arial.ttf',
            'Arial.ttf',
            'ARIAL.TTF',
            'arial.ttc',
            'Arial.ttc',
        ];
    }
    
    /**
     * Get font file mappings for common fonts
     */
    public static function getFontMappings(): array
    {
        return [
            'Arial' => ['arial.ttf', 'Arial.ttf', 'ARIAL.TTF', 'arial.ttc'],
            'Helvetica' => ['Helvetica.ttf', 'helvetica.ttf', 'HelveticaNeueMed.ttc'],
            'Times New Roman' => ['times.ttf', 'Times.ttf', 'times.ttc', 'TIMES.TTF'],
            'Georgia' => ['Georgia.ttf', 'georgia.ttf', 'GEORGIA.TTF'],
            'Verdana' => ['verdana.ttf', 'Verdana.ttf', 'VERDANA.TTF'],
            'Courier New' => ['cour.ttf', 'Courier.ttf', 'COUR.TTF'],
            'Brush Script MT' => ['BRUSHSCI.TTF', 'brushsci.ttf', 'BrushScriptMT.ttf'],
            'Comic Sans MS' => ['comic.ttf', 'Comic.ttf', 'COMIC.TTF'],
            'Impact' => ['impact.ttf', 'Impact.ttf', 'IMPACT.TTF'],
            'Tahoma' => ['tahoma.ttf', 'Tahoma.ttf', 'TAHOMA.TTF'],
        ];
    }
    
    /**
     * Get the temp directory for the current platform
     */
    public static function getTempDirectory(): string
    {
        return sys_get_temp_dir();
    }
    
    /**
     * Get memory limit in bytes
     */
    public static function getMemoryLimit(): int
    {
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }
        
        return self::convertToBytes($memoryLimit);
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private static function convertToBytes(string $value): int
    {
        $value = trim($value);
        $lastChar = strtolower($value[strlen($value) - 1]);
        $numValue = (int) $value;
        
        return match ($lastChar) {
            'g' => $numValue * 1024 * 1024 * 1024,
            'm' => $numValue * 1024 * 1024,
            'k' => $numValue * 1024,
            default => $numValue,
        };
    }
    
    /**
     * Check if GD library is available
     */
    public static function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }
    
    /**
     * Get GD version info
     */
    public static function getGdInfo(): array
    {
        if (!self::isGdAvailable()) {
            return [];
        }
        
        return gd_info();
    }
}











