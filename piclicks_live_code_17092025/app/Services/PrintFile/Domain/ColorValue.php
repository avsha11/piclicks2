<?php

namespace App\Services\PrintFile\Domain;

/**
 * Value object for color representation
 * 
 * Handles color conversion and validation
 */
class ColorValue
{
    private int $red;
    private int $green;
    private int $blue;
    
    private function __construct(int $red, int $green, int $blue)
    {
        $this->red = max(0, min(255, $red));
        $this->green = max(0, min(255, $green));
        $this->blue = max(0, min(255, $blue));
    }
    
    /**
     * Create from hex color string
     */
    public static function fromHex(string $hexColor): self
    {
        $hexColor = ltrim($hexColor, '#');
        
        // Handle shorthand hex (e.g., #FFF)
        if (strlen($hexColor) === 3) {
            $hexColor = $hexColor[0] . $hexColor[0] 
                      . $hexColor[1] . $hexColor[1] 
                      . $hexColor[2] . $hexColor[2];
        }
        
        $red = hexdec(substr($hexColor, 0, 2));
        $green = hexdec(substr($hexColor, 2, 2));
        $blue = hexdec(substr($hexColor, 4, 2));
        
        return new self($red, $green, $blue);
    }
    
    /**
     * Create from RGB values
     */
    public static function fromRgb(int $red, int $green, int $blue): self
    {
        return new self($red, $green, $blue);
    }
    
    /**
     * Create from RGB string (e.g., "rgb(255, 0, 0)")
     */
    public static function fromRgbString(string $rgbString): self
    {
        if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $rgbString, $matches)) {
            return new self(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            );
        }
        
        // Fallback to black
        return new self(0, 0, 0);
    }
    
    public function getRed(): int
    {
        return $this->red;
    }
    
    public function getGreen(): int
    {
        return $this->green;
    }
    
    public function getBlue(): int
    {
        return $this->blue;
    }
    
    public function toArray(): array
    {
        return [$this->red, $this->green, $this->blue];
    }
    
    public function toHex(): string
    {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }
    
    public function allocateColor($canvas): int
    {
        return imagecolorallocate($canvas, $this->red, $this->green, $this->blue);
    }
}




