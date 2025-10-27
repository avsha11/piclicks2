<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * PrintFileService
 * 
 * Generates print-ready PNG files following SPEC.md and ALGORITHM.md
 * Implements proper bleed, frame, text, and filter rendering
 */
class PrintFileService
{
    // Constants from SPEC.md and constants.json
    private const MM_PER_IN = 25.4;
    private const DPI = 300;
    private const BLEED_MM = 2.0;
    
    // Clear area (visible in editor/preview)
    private const CLEAR_TILE_W_MM = 143.7;
    private const CLEAR_TILE_H_MM = 126.0;
    private const CLEAR_CORNER_RADIUS_MM = 8.0;
    
    // Print tile (with bleed)
    private const PRINT_TILE_W_MM = 147.7; // 143.7 + 2*2
    private const PRINT_TILE_H_MM = 130.0; // 126.0 + 2*2
    private const PRINT_CORNER_RADIUS_MM = 10.0;
    
    // Frame
    private const FRAME_PRINT_THICKNESS_MM = 10.0;
    private const FRAME_VISIBLE_THICKNESS_MM = 8.0;
    private const FRAME_INNER_CORNER_RADIUS_MM = 2.0;
    
    // Calculated pixel values
    private float $pxPerMm;
    private int $clearTileWPx;
    private int $clearTileHPx;
    private int $printTileWPx;
    private int $printTileHPx;
    private int $bleedPx;
    private int $clearCornerRadiusPx;
    private int $printCornerRadiusPx;
    private int $framePrintThicknessPx;
    private int $frameInnerCornerRadiusPx;
    
    public function __construct()
    {
        // Calculate pixel values at 300 DPI
        $this->pxPerMm = self::DPI / self::MM_PER_IN;
        $this->clearTileWPx = $this->mmToPx(self::CLEAR_TILE_W_MM);
        $this->clearTileHPx = $this->mmToPx(self::CLEAR_TILE_H_MM);
        $this->printTileWPx = $this->mmToPx(self::PRINT_TILE_W_MM);
        $this->printTileHPx = $this->mmToPx(self::PRINT_TILE_H_MM);
        $this->bleedPx = $this->mmToPx(self::BLEED_MM);
        $this->clearCornerRadiusPx = $this->mmToPx(self::CLEAR_CORNER_RADIUS_MM);
        $this->printCornerRadiusPx = $this->mmToPx(self::PRINT_CORNER_RADIUS_MM);
        $this->framePrintThicknessPx = $this->mmToPx(self::FRAME_PRINT_THICKNESS_MM);
        $this->frameInnerCornerRadiusPx = $this->mmToPx(self::FRAME_INNER_CORNER_RADIUS_MM);
        
        Log::info("PrintFileService initialized", [
            'clearTile' => "{$this->clearTileWPx}x{$this->clearTileHPx}px",
            'printTile' => "{$this->printTileWPx}x{$this->printTileHPx}px",
            'bleed' => "{$this->bleedPx}px",
            'printRadius' => "{$this->printCornerRadiusPx}px",
            'pxPerMm' => $this->pxPerMm,
            'expectedPrintSize' => "147.7mm x 130.0mm = {$this->printTileWPx}px x {$this->printTileHPx}px"
        ]);
    }
    
    /**
     * Generate print files for a block (single tile or stretched image)
     * 
     * @param array $blockConfig Configuration for the block
     * @return array [success, relativePaths]
     */
    public function generatePrintFiles(array $blockConfig): array
    {
        try {
            Log::info("generatePrintFiles started", [
                'image' => $blockConfig['image_path'] ?? 'N/A',
                'span' => ($blockConfig['cols'] ?? 1) . 'x' . ($blockConfig['rows'] ?? 1),
            ]);
            
            // Validate config
            if (empty($blockConfig['image_path']) || !File::exists(storage_path('app/public/' . $blockConfig['image_path']))) {
                Log::error("Image file not found", ['path' => $blockConfig['image_path'] ?? 'N/A']);
                return [false, []];
            }
            
            // Extract block configuration
            $imagePath = $blockConfig['image_path'];
            $rows = $blockConfig['rows'] ?? 1;
            $cols = $blockConfig['cols'] ?? 1;
            $zoom = $blockConfig['zoom'] ?? '0';
            $rotate = $blockConfig['rotate'] ?? '1';
            $frameConfig = $blockConfig['frame'] ?? ['exists' => false];
            $filter = $blockConfig['filter'] ?? null;
            $textOverlays = $blockConfig['text_overlays'] ?? [];
            $startRow = $blockConfig['start_row'] ?? 0;
            $startCol = $blockConfig['start_col'] ?? 0;
            
            // Calculate block dimensions (ALGORITHM.md step 1 & 2)
            $blockClearW = $cols * $this->clearTileWPx;
            $blockClearH = $rows * $this->clearTileHPx;
            $blockPrintW = $blockClearW + (2 * $this->bleedPx);
            $blockPrintH = $blockClearH + (2 * $this->bleedPx);
            
            Log::info("Block dimensions", [
                'clear' => "{$blockClearW}x{$blockClearH}px",
                'withBleed' => "{$blockPrintW}x{$blockPrintH}px",
            ]);
            
            // Create block canvas with bleed (white background for photos)
            $blockCanvas = imagecreatetruecolor($blockPrintW, $blockPrintH);
            imagealphablending($blockCanvas, false);
            imagesavealpha($blockCanvas, true);
            
            // Fill with white background (photos print on white paper)
            $white = imagecolorallocate($blockCanvas, 255, 255, 255);
            imagefilledrectangle($blockCanvas, 0, 0, $blockPrintW, $blockPrintH, $white);
            
            // RENDER ORDER (bottom to top): Image → Filter → Text → Frame
            // Following constants.json RENDER_ORDER: frame, text, filter, image
            
            // Step 1: Render Image (base layer)
            $this->renderImage($blockCanvas, $imagePath, $blockPrintW, $blockPrintH, $zoom, $rotate);
            
            // Step 2: Apply Filter (on top of image)
            if (!empty($filter)) {
                $this->applyFilter($blockCanvas, $filter);
            }
            
            // Step 3: Render Text (on top of filter)
            if (!empty($textOverlays)) {
                $this->renderText($blockCanvas, $textOverlays, $blockClearW, $blockClearH);
            }
            
            // Step 4: Render Frame (top layer)
            $frameCanvas = null;
            if ($frameConfig['exists'] ?? false) {
                $frameCanvas = $this->renderFrame($blockPrintW, $blockPrintH, $frameConfig);
            }
            
            // Step 5: Crop tiles and save (ALGORITHM.md step 8)
            $tileExportPaths = [];
            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    // Calculate tile crop area in block coordinates
                    // Block canvas already has bleed included, so each tile position needs to account for it
                    $cropX = $col * $this->clearTileWPx; // Position in clear coordinates
                    $cropY = $row * $this->clearTileHPx;
                    
                    // Create tile canvas (with bleed size)
                    $tileCanvas = imagecreatetruecolor($this->printTileWPx, $this->printTileHPx);
                    imagealphablending($tileCanvas, false);
                    imagesavealpha($tileCanvas, true);
                    $tileTrans = imagecolorallocatealpha($tileCanvas, 0, 0, 0, 127);
                    imagefilledrectangle($tileCanvas, 0, 0, $this->printTileWPx, $this->printTileHPx, $tileTrans);
                    
                    // Copy tile subsection from block canvas
                    // The block canvas is blockPrintW x blockPrintH (with bleed)
                    // Each tile should be printTileWPx x printTileHPx (with bleed)
                    // cropX and cropY are in clear coordinates, so each tile starts at:
                    // col * clearTileWPx, row * clearTileHPx
                    imagecopy(
                        $tileCanvas, 
                        $blockCanvas, 
                        0, 0, // Destination: top-left of tile canvas
                        $cropX, $cropY, // Source: position in block (clear coordinates)
                        $this->printTileWPx, $this->printTileHPx // Size: print tile with bleed
                    );
                    
                    // Apply frame section if exists
                    if ($frameCanvas !== null) {
                        $this->applyFrameSection($tileCanvas, $frameCanvas, $cropX, $cropY);
                    }
                    
                    // Apply rounded corners (R10 for print)
                    $this->applyRoundedCorners($tileCanvas, $this->printCornerRadiusPx);
                    
                    // Save tile
                    $tilePath = $this->saveTile($tileCanvas, $row + $startRow, $col + $startCol, $rows, $cols);
                    if ($tilePath) {
                        $tileExportPaths[] = $tilePath;
                    }
                    
                    imagedestroy($tileCanvas);
                }
            }
            
            // Cleanup
            imagedestroy($blockCanvas);
            if ($frameCanvas !== null) {
                imagedestroy($frameCanvas);
            }
            
            Log::info("generatePrintFiles completed", ['tiles' => count($tileExportPaths)]);
            return [true, $tileExportPaths];
            
        } catch (\Exception $e) {
            Log::error("generatePrintFiles error", [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return [false, []];
        }
    }
    
    /**
     * Render image onto block canvas with proper scaling and transformations
     */
    private function renderImage($canvas, string $imagePath, int $blockW, int $blockH, string $zoom, string $rotate): void
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        $sourceImage = imagecreatefromstring(file_get_contents($fullPath));
        
        if (!$sourceImage) {
            Log::error("Failed to load image", ['path' => $imagePath]);
            return;
        }
        
        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);
        
        // Parse zoom (format: "zoomX|zoomY" or "0")
        $zoomParts = explode('|', $zoom);
        $zoomX = floatval($zoomParts[0] ?? 0);
        $zoomY = floatval($zoomParts[1] ?? $zoomX);
        
        // Parse rotation (1=0°, 2=90°, 3=180°, 4=270°)
        $rotationDegrees = 0;
        switch ($rotate) {
            case '2': $rotationDegrees = 90; break;
            case '3': $rotationDegrees = 180; break;
            case '4': $rotationDegrees = 270; break;
        }
        
        // Apply rotation if needed
        if ($rotationDegrees > 0) {
            $rotated = imagerotate($sourceImage, -$rotationDegrees, 0);
            imagedestroy($sourceImage);
            $sourceImage = $rotated;
            $srcW = imagesx($sourceImage);
            $srcH = imagesy($sourceImage);
        }
        
        // Scale to cover block (with bleed) - "cover" fit
        $blockAspect = $blockW / $blockH;
        $srcAspect = $srcW / $srcH;
        
        if ($srcAspect > $blockAspect) {
            // Image is wider - fit height
            $scaledH = $blockH;
            $scaledW = intval($blockH * $srcAspect);
        } else {
            // Image is taller - fit width
            $scaledW = $blockW;
            $scaledH = intval($blockW / $srcAspect);
        }
        
        // Apply zoom if present
        if ($zoomX > 0 || $zoomY > 0) {
            $scaledW = intval($scaledW * (1 + $zoomX));
            $scaledH = intval($scaledH * (1 + $zoomY));
        }
        
        // Center the image (pan would be applied here if available in data)
        $dstX = intval(($blockW - $scaledW) / 2);
        $dstY = intval(($blockH - $scaledH) / 2);
        
        // Enable alpha blending for proper image rendering
        imagealphablending($canvas, true);
        
        // Draw image
        imagecopyresampled(
            $canvas, $sourceImage,
            $dstX, $dstY,
            0, 0,
            $scaledW, $scaledH,
            $srcW, $srcH
        );
        
        // Restore alpha blending state
        imagealphablending($canvas, false);
        
        imagedestroy($sourceImage);
        
        Log::info("Image rendered", [
            'src' => "{$srcW}x{$srcH}px",
            'scaled' => "{$scaledW}x{$scaledH}px",
            'position' => "{$dstX},{$dstY}",
        ]);
    }
    
    /**
     * Apply filter to the canvas (SPEC.md filters)
     * Improved approximations to match CSS filters as closely as possible
     * 
     * CSS to GD mapping notes:
     * - contrast(X%) → IMG_FILTER_CONTRAST: 100% = 0, >100% = negative, <100% = positive
     * - brightness(X%) → IMG_FILTER_BRIGHTNESS: 100% = 0, >100% = positive, <100% = negative
     * - GD range: -255 to 255 for both
     */
    private function applyFilter($canvas, string $filter): void
    {
        Log::info("Applying filter", ['filter' => $filter]);
        
        switch ($filter) {
            case 'filter-noir':
                // CSS: grayscale(100%) contrast(1.2) = grayscale(100%) contrast(120%)
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // 120% contrast = -20 in GD
                Log::info("Applied noir filter", ['steps' => 'grayscale + contrast(120%)']);
                break;
                
            case 'filter-stark':
                // CSS: grayscale(50%) brightness(100%) contrast(90%)
                // Apply partial grayscale by reducing saturation
                imagefilter($canvas, IMG_FILTER_CONTRAST, 10); // 90% contrast = +10 in GD
                imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 64); // 50% desaturation
                Log::info("Applied stark filter", ['steps' => 'contrast(90%) + desaturate(50%)']);
                break;
                
            case 'filter-scandi':
                // CSS: brightness(120%) contrast(105%) grayscale(10%) hue-rotate(5deg)
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 20); // 120% brightness = +20
                imagefilter($canvas, IMG_FILTER_CONTRAST, -5); // 105% contrast = -5
                // Slight warm tint for hue-rotate simulation
                imagefilter($canvas, IMG_FILTER_COLORIZE, 15, 8, -5, 0);
                Log::info("Applied scandi filter", ['steps' => 'brightness(120%) + contrast(105%) + warm tint']);
                break;
                
            case 'filter-capri':
                // CSS: contrast(120%) brightness(110%) saturate(150%) hue-rotate(-30deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // 120% contrast = -20
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 10); // 110% brightness = +10
                // Blue/cyan tint for hue-rotate(-30deg)
                imagefilter($canvas, IMG_FILTER_COLORIZE, -15, 5, 35, 0);
                Log::info("Applied capri filter", ['steps' => 'contrast(120%) + brightness(110%) + cool tint']);
                break;
                
            case 'filter-nordic':
                // CSS: contrast(110%) brightness(80%) sepia(20%) hue-rotate(-15deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -10); // 110% contrast = -10
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -20); // 80% brightness = -20
                // Sepia + slight cool tint
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 30, 25, 15, 0); // Warm sepia tone
                Log::info("Applied nordic filter", ['steps' => 'contrast(110%) + brightness(80%) + sepia']);
                break;
                
            case 'filter-belveder':
                // CSS: contrast(115%) brightness(90%) sepia(30%) hue-rotate(10deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15); // 115% contrast = -15
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -10); // 90% brightness = -10
                // Stronger sepia tone
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 100, 60, 35, 0); // Rich sepia tone
                Log::info("Applied belveder filter", ['steps' => 'contrast(115%) + brightness(90%) + rich sepia']);
                break;
                
            default:
                Log::warning("Unknown filter", ['filter' => $filter]);
                break;
        }
        
        Log::info("Filter application completed", ['filter' => $filter]);
    }
    
    /**
     * Render text overlays on block canvas
     */
    private function renderText($canvas, array $textOverlays, int $blockClearW, int $blockClearH): void
    {
        foreach ($textOverlays as $idx => $textOverlay) {
            $text = $textOverlay['text'] ?? '';
            if (empty($text)) continue;
            
            $x = intval($textOverlay['x'] ?? 0) + $this->bleedPx;
            $y = intval($textOverlay['y'] ?? 0) + $this->bleedPx;
            $fontSize = intval($textOverlay['font_size'] ?? 40);
            $color = $textOverlay['color'] ?? '#000000';
            $fontFamily = $textOverlay['font_family'] ?? 'Arial';
            $rotation = floatval($textOverlay['rotation'] ?? 0);
            
            Log::info("Rendering text overlay", [
                'index' => $idx,
                'text' => substr($text, 0, 20) . (strlen($text) > 20 ? '...' : ''),
                'position' => "{$x},{$y} (with bleed: +{$this->bleedPx}px)",
                'font_size' => $fontSize,
                'color' => $color,
                'rotation' => "{$rotation}°",
                'font_family' => $fontFamily
            ]);
            
            // Convert hex color to RGB
            $rgb = $this->hexToRgb($color);
            $textColor = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
            
            // Get font path
            $fontPath = $this->getFontPath($fontFamily);
            
            if ($fontPath && file_exists($fontPath)) {
                if ($rotation != 0) {
                    // Render rotated text
                    $this->renderRotatedText($canvas, $text, $fontPath, $fontSize, $textColor, $x, $y, $rotation);
                    Log::info("Text rendered with rotation", [
                        'text' => substr($text, 0, 20), 
                        'rotation' => "{$rotation}°",
                        'font' => $fontPath
                    ]);
                } else {
                    imagettftext($canvas, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
                    Log::info("Text rendered (no rotation)", [
                        'text' => substr($text, 0, 20),
                        'font' => basename($fontPath)
                    ]);
                }
            } else {
                // CRITICAL ERROR: No TTF font available at all
                // Draw a visible error message
                $errorText = "FONT ERROR: " . $text;
                imagestring($canvas, 5, $x, $y, $errorText, $textColor);
                
                // Also draw a red rectangle to make it obvious there's a problem
                $red = imagecolorallocate($canvas, 255, 0, 0);
                imagerectangle($canvas, $x - 5, $y - 5, $x + (strlen($errorText) * 8), $y + 15, $red);
                
                Log::error("CRITICAL: No TTF font found, using GD built-in (will be invisible)", [
                    'text' => substr($text, 0, 20),
                    'requested_font' => $fontFamily,
                    'searched_path' => $fontPath ?? 'N/A',
                    'message' => 'Check that C:/Windows/Fonts/ contains arial.ttf'
                ]);
            }
        }
        
        Log::info("Text rendering completed", ['total_overlays' => count($textOverlays)]);
    }
    
    /**
     * Render rotated text using a temporary canvas
     */
    private function renderRotatedText($canvas, string $text, string $fontPath, int $fontSize, int $textColor, int $x, int $y, float $rotation): void
    {
        // Get text bounding box to determine size
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        
        // Add padding for rotation
        $padding = max($textWidth, $textHeight) * 0.5;
        $tempWidth = intval($textWidth + $padding * 2);
        $tempHeight = intval($textHeight + $padding * 2);
        
        // Create temporary canvas for text
        $tempCanvas = imagecreatetruecolor($tempWidth, $tempHeight);
        imagealphablending($tempCanvas, false);
        imagesavealpha($tempCanvas, true);
        $transparent = imagecolorallocatealpha($tempCanvas, 0, 0, 0, 127);
        imagefilledrectangle($tempCanvas, 0, 0, $tempWidth, $tempHeight, $transparent);
        
        // Draw text on temporary canvas
        imagealphablending($tempCanvas, true);
        imagettftext($tempCanvas, $fontSize, 0, intval($padding), intval($textHeight + $padding), $textColor, $fontPath, $text);
        imagealphablending($tempCanvas, false);
        
        // Rotate the temporary canvas
        $rotatedCanvas = imagerotate($tempCanvas, -$rotation, $transparent);
        
        // Calculate position to center the rotated text
        $rotatedWidth = imagesx($rotatedCanvas);
        $rotatedHeight = imagesy($rotatedCanvas);
        $centerX = $x - intval($rotatedWidth / 2);
        $centerY = $y - intval($rotatedHeight / 2);
        
        // Copy rotated text to main canvas
        imagealphablending($canvas, true);
        imagecopy($canvas, $rotatedCanvas, $centerX, $centerY, 0, 0, $rotatedWidth, $rotatedHeight);
        imagealphablending($canvas, false);
        
        // Cleanup
        imagedestroy($tempCanvas);
        imagedestroy($rotatedCanvas);
    }
    
    /**
     * Render frame for the entire block
     * Returns a frame canvas that will be applied per-tile
     */
    private function renderFrame(int $blockW, int $blockH, array $frameConfig)
    {
        $frameColor = $this->hexToRgb($frameConfig['color_hex'] ?? '#000000');
        
        // Create frame canvas (same size as block with bleed)
        $frameCanvas = imagecreatetruecolor($blockW, $blockH);
        imagealphablending($frameCanvas, false);
        imagesavealpha($frameCanvas, true);
        $transparent = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
        imagefilledrectangle($frameCanvas, 0, 0, $blockW, $blockH, $transparent);
        
        // Draw full-bleed rounded rect with R10
        $frameColorAllocated = imagecolorallocate($frameCanvas, $frameColor[0], $frameColor[1], $frameColor[2]);
        $this->drawFilledRoundedRect($frameCanvas, 0, 0, $blockW, $blockH, $this->printCornerRadiusPx, $frameColorAllocated);
        
        // Punch inner hole inset by 8mm (frame thickness) with R2 inner radius
        $innerX = $this->framePrintThicknessPx;
        $innerY = $this->framePrintThicknessPx;
        $innerW = $blockW - (2 * $this->framePrintThicknessPx);
        $innerH = $blockH - (2 * $this->framePrintThicknessPx);
        
        // Create inner mask
        $innerMask = imagecreatetruecolor($blockW, $blockH);
        imagealphablending($innerMask, false);
        imagesavealpha($innerMask, true);
        imagefilledrectangle($innerMask, 0, 0, $blockW, $blockH, $transparent);
        
        $maskOpaque = imagecolorallocate($innerMask, 255, 255, 255);
        $this->drawFilledRoundedRect($innerMask, $innerX, $innerY, $innerW, $innerH, $this->frameInnerCornerRadiusPx, $maskOpaque);
        
        // Punch the hole
        for ($x = 0; $x < $blockW; $x++) {
            for ($y = 0; $y < $blockH; $y++) {
                $maskColor = imagecolorat($innerMask, $x, $y);
                if (($maskColor & 0xFF) === 255) {
                    imagesetpixel($frameCanvas, $x, $y, $transparent);
                }
            }
        }
        
        imagedestroy($innerMask);
        
        Log::info("Frame rendered", [
            'size' => "{$blockW}x{$blockH}px",
            'thickness' => "{$this->framePrintThicknessPx}px",
        ]);
        
        return $frameCanvas;
    }
    
    /**
     * Apply frame section to tile
     */
    private function applyFrameSection($tileCanvas, $frameCanvas, int $cropX, int $cropY): void
    {
        // Composite frame onto tile
        imagealphablending($tileCanvas, true);
        imagecopy(
            $tileCanvas,
            $frameCanvas,
            0, 0,
            $cropX, $cropY,
            $this->printTileWPx, $this->printTileHPx
        );
        imagealphablending($tileCanvas, false);
    }
    
    /**
     * Apply rounded corners to a canvas
     */
    private function applyRoundedCorners(&$canvas, int $radius): void
    {
        $w = imagesx($canvas);
        $h = imagesy($canvas);
        
        // Create mask
        $mask = imagecreatetruecolor($w, $h);
        imagealphablending($mask, false);
        imagesavealpha($mask, true);
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefilledrectangle($mask, 0, 0, $w, $h, $transparent);
        
        $opaque = imagecolorallocatealpha($mask, 0, 0, 0, 0);
        
        // Draw rounded rectangle
        imagefilledrectangle($mask, $radius, 0, $w - $radius, $h, $opaque);
        imagefilledrectangle($mask, 0, $radius, $w, $h - $radius, $opaque);
        imagefilledellipse($mask, $radius, $radius, $radius * 2, $radius * 2, $opaque);
        imagefilledellipse($mask, $w - $radius, $radius, $radius * 2, $radius * 2, $opaque);
        imagefilledellipse($mask, $radius, $h - $radius, $radius * 2, $radius * 2, $opaque);
        imagefilledellipse($mask, $w - $radius, $h - $radius, $radius * 2, $radius * 2, $opaque);
        
        // Apply mask
        $result = imagecreatetruecolor($w, $h);
        imagealphablending($result, false);
        imagesavealpha($result, true);
        $resultTrans = imagecolorallocatealpha($result, 0, 0, 0, 127);
        imagefilledrectangle($result, 0, 0, $w, $h, $resultTrans);
        
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $alpha = (imagecolorat($mask, $x, $y) & 0x7F000000) >> 24;
                if ($alpha === 0) {
                    $col = imagecolorat($canvas, $x, $y);
                    imagesetpixel($result, $x, $y, $col);
                }
            }
        }
        
        imagecopy($canvas, $result, 0, 0, 0, 0, $w, $h);
        imagedestroy($mask);
        imagedestroy($result);
    }
    
    /**
     * Draw a filled rounded rectangle
     */
    private function drawFilledRoundedRect($canvas, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        // Middle rectangles
        imagefilledrectangle($canvas, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($canvas, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        
        // Four corner circles
        imagefilledellipse($canvas, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }
    
    /**
     * Save tile to file
     */
    private function saveTile($canvas, int $row, int $col, int $totalRows, int $totalCols): ?string
    {
        $basePath = storage_path('app/public/');
        
        if ($totalRows === 1 && $totalCols === 1) {
            $fileName = 'designCollageImages/tile_' . time() . '_' . uniqid() . '.png';
        } else {
            $fileName = 'designCollageImages/tile_r' . ($row + 1) . '_c' . ($col + 1) . '_' . time() . '_' . uniqid() . '.png';
        }
        
        $fullPath = $basePath . $fileName;
        
        if (imagepng($canvas, $fullPath)) {
            Log::info("Tile saved", ['path' => $fileName, 'row' => $row, 'col' => $col]);
            return $fileName;
        }
        
        Log::error("Failed to save tile", ['path' => $fileName]);
        return null;
    }
    
    /**
     * Convert millimeters to pixels at 300 DPI
     */
    private function mmToPx(float $mm): int
    {
        return intval(round($mm * $this->pxPerMm));
    }
    
    /**
     * Convert hex color to RGB array
     */
    private function hexToRgb(string $hexColor): array
    {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) === 3) {
            $hexColor = $hexColor[0] . $hexColor[0] . $hexColor[1] . $hexColor[1] . $hexColor[2] . $hexColor[2];
        }
        return [
            hexdec(substr($hexColor, 0, 2)),
            hexdec(substr($hexColor, 2, 2)),
            hexdec(substr($hexColor, 4, 2)),
        ];
    }
    
    /**
     * Get font file path for a given font family
     * Always returns a valid font path (fallback to Arial if font not found)
     */
    private function getFontPath(string $fontFamily): ?string
    {
        $fontDirectories = [
            'C:/Windows/Fonts/',
            '/usr/share/fonts/',
            '/usr/local/share/fonts/',
            '/System/Library/Fonts/',
            storage_path('fonts/'),
        ];
        
        $fontMappings = [
            'Arial' => ['arial.ttf', 'Arial.ttf', 'ARIAL.TTF', 'arial.ttc'],
            'Helvetica' => ['Helvetica.ttf', 'helvetica.ttf'],
            'Times New Roman' => ['times.ttf', 'Times.ttf', 'times.ttc', 'TIMES.TTF'],
            'Georgia' => ['Georgia.ttf', 'georgia.ttf', 'GEORGIA.TTF'],
            'Verdana' => ['verdana.ttf', 'Verdana.ttf', 'VERDANA.TTF'],
            'Courier New' => ['cour.ttf', 'Courier.ttf', 'COUR.TTF'],
            'Brush Script MT' => ['BRUSHSCI.TTF', 'brushsci.ttf', 'BrushScriptMT.ttf'],
            'Comic Sans MS' => ['comic.ttf', 'Comic.ttf', 'COMIC.TTF'],
            'Impact' => ['impact.ttf', 'Impact.ttf', 'IMPACT.TTF'],
            'Tahoma' => ['tahoma.ttf', 'Tahoma.ttf', 'TAHOMA.TTF'],
        ];
        
        // Try to find the requested font
        $fontFiles = $fontMappings[$fontFamily] ?? [
            // Remove spaces and try common patterns
            str_replace(' ', '', strtolower($fontFamily)) . '.ttf',
            str_replace(' ', '', $fontFamily) . '.ttf',
            strtolower($fontFamily) . '.ttf',
            $fontFamily . '.ttf',
            strtoupper(str_replace(' ', '', $fontFamily)) . '.TTF',
            str_replace(' ', '', strtolower($fontFamily)) . '.otf',
            $fontFamily . '.otf',
        ];
        
        foreach ($fontDirectories as $directory) {
            if (is_dir($directory)) {
                foreach ($fontFiles as $fontFile) {
                    $fontPath = $directory . $fontFile;
                    if (file_exists($fontPath)) {
                        Log::info("Font found", ['font' => $fontFamily, 'path' => $fontPath]);
                        return $fontPath;
                    }
                }
            }
        }
        
        // FALLBACK: Try to find Arial (should exist on Windows/Mac/Linux)
        Log::warning("Requested font not found, trying Arial fallback", ['requested' => $fontFamily]);
        $arialFiles = ['arial.ttf', 'Arial.ttf', 'ARIAL.TTF', 'arial.ttc'];
        foreach ($fontDirectories as $directory) {
            if (is_dir($directory)) {
                foreach ($arialFiles as $fontFile) {
                    $fontPath = $directory . $fontFile;
                    if (file_exists($fontPath)) {
                        Log::info("Using Arial fallback", ['path' => $fontPath]);
                        return $fontPath;
                    }
                }
            }
        }
        
        // If even Arial doesn't exist, return null (will use GD built-in font)
        Log::error("No TTF fonts found (not even Arial!)", ['directories_checked' => $fontDirectories]);
        return null;
    }
}

