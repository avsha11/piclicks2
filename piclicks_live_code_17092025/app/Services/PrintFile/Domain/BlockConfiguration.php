<?php

namespace App\Services\PrintFile\Domain;

/**
 * Value object for block configuration
 * 
 * Encapsulates all configuration needed to render a block/tile
 */
class BlockConfiguration
{
    private string $imagePath;
    private int $cols;
    private int $rows;
    private int $startCol;
    private int $startRow;
    private string $zoom;
    private string $rotate;
    private array $frameConfig;
    private ?string $filter;
    private array $textOverlays;
    
    public function __construct(array $config)
    {
        $this->imagePath = $config['image_path'] ?? '';
        $this->cols = (int) ($config['cols'] ?? 1);
        $this->rows = (int) ($config['rows'] ?? 1);
        $this->startCol = (int) ($config['start_col'] ?? 0);
        $this->startRow = (int) ($config['start_row'] ?? 0);
        $this->zoom = $config['zoom'] ?? '0';
        $this->rotate = $config['rotate'] ?? '1';
        $this->frameConfig = $config['frame'] ?? ['exists' => false];
        $this->filter = $config['filter'] ?? null;
        $this->textOverlays = $config['text_overlays'] ?? [];
    }
    
    public function getImagePath(): string
    {
        return $this->imagePath;
    }
    
    public function getCols(): int
    {
        return $this->cols;
    }
    
    public function getRows(): int
    {
        return $this->rows;
    }
    
    public function getStartCol(): int
    {
        return $this->startCol;
    }
    
    public function getStartRow(): int
    {
        return $this->startRow;
    }
    
    public function getZoom(): string
    {
        return $this->zoom;
    }
    
    public function getRotate(): string
    {
        return $this->rotate;
    }
    
    public function getFrameConfig(): array
    {
        return $this->frameConfig;
    }
    
    public function hasFrame(): bool
    {
        return $this->frameConfig['exists'] ?? false;
    }
    
    public function getFilter(): ?string
    {
        return $this->filter;
    }
    
    public function hasFilter(): bool
    {
        return !empty($this->filter);
    }
    
    public function getTextOverlays(): array
    {
        return $this->textOverlays;
    }
    
    public function hasTextOverlays(): bool
    {
        return !empty($this->textOverlays);
    }
    
    public function isSingleTile(): bool
    {
        return $this->cols === 1 && $this->rows === 1;
    }
    
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->imagePath)) {
            $errors[] = 'Image path is required';
        }
        
        if ($this->cols < 1 || $this->rows < 1) {
            $errors[] = 'Cols and rows must be at least 1';
        }
        
        if ($this->startCol < 0 || $this->startRow < 0) {
            $errors[] = 'Start position cannot be negative';
        }
        
        return $errors;
    }
}









