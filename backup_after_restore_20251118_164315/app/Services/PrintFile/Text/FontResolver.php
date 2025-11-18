<?php

namespace App\Services\PrintFile\Text;

use App\Services\PrintFile\Config\PlatformConfig;
use Illuminate\Support\Facades\Log;

/**
 * Resolves font file paths
 * 
 * Platform-agnostic font loading with fallbacks
 */
class FontResolver
{
    private array $fontCache = [];
    
    /**
     * Get font file path for a given font family
     */
    public function resolveFontPath(string $fontFamily): ?string
    {
        // Check cache
        if (isset($this->fontCache[$fontFamily])) {
            return $this->fontCache[$fontFamily];
        }
        
        $fontPath = $this->findFontFile($fontFamily);
        
        // Cache the result (even if null)
        $this->fontCache[$fontFamily] = $fontPath;
        
        if ($fontPath !== null) {
            Log::info('Font resolved', [
                'font' => $fontFamily,
                'path' => $fontPath,
            ]);
        } else {
            Log::warning('Font not found, will try fallback', [
                'requested' => $fontFamily,
            ]);
        }
        
        return $fontPath;
    }
    
    /**
     * Get font path with automatic fallback to Arial
     */
    public function resolveFontPathWithFallback(string $fontFamily): ?string
    {
        $fontPath = $this->resolveFontPath($fontFamily);
        
        if ($fontPath !== null) {
            return $fontPath;
        }
        
        // Try Arial fallback
        Log::warning('Using Arial fallback', ['requested' => $fontFamily]);
        return $this->resolveFontPath('Arial');
    }
    
    /**
     * Find a font file in system directories
     */
    private function findFontFile(string $fontFamily): ?string
    {
        $directories = PlatformConfig::getFontDirectories();
        $mappings = PlatformConfig::getFontMappings();
        
        // Get possible file names for this font
        $fontFiles = $mappings[$fontFamily] ?? $this->generateFontFileNames($fontFamily);
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            
            foreach ($fontFiles as $fontFile) {
                $fontPath = $directory . $fontFile;
                if (file_exists($fontPath)) {
                    return $fontPath;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Generate possible font file names
     */
    private function generateFontFileNames(string $fontFamily): array
    {
        $normalized = str_replace(' ', '', $fontFamily);
        
        return [
            strtolower($normalized) . '.ttf',
            $normalized . '.ttf',
            strtoupper($normalized) . '.TTF',
            strtolower($normalized) . '.otf',
            $normalized . '.otf',
            strtolower($fontFamily) . '.ttf',
            $fontFamily . '.ttf',
        ];
    }
    
    /**
     * Clear the font cache
     */
    public function clearCache(): void
    {
        $this->fontCache = [];
    }
}











