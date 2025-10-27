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
     */
    private function applyFilter($canvas, string $filter): void
    {
        Log::info("Applying filter", ['filter' => $filter]);
        
        switch ($filter) {
            case 'filter-noir':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -10);
                Log::info("Applied noir filter: grayscale + contrast");
                break;
            case 'filter-stark':
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15);
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 5);
                Log::info("Applied stark filter: contrast + brightness");
                break;
            case 'filter-scandi':
                imagefilter($canvas, IMG_FILTER_COLORIZE, 20, 10, 0, 0);
                Log::info("Applied scandi filter: warm colorize");
                break;
            case 'filter-capri':
                imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 10, 25, 0);
                Log::info("Applied capri filter: blue colorize");
                break;
            case 'filter-nordic':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 25, 20, 15, 0);
                Log::info("Applied nordic filter: grayscale + warm colorize");
                break;
            case 'filter-belveder':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 90, 55, 30, 0);
                Log::info("Applied belveder filter: grayscale + sepia colorize");
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
        foreach ($textOverlays as $textOverlay) {
            $text = $textOverlay['text'] ?? '';
            if (empty($text)) continue;
            
            $x = intval($textOverlay['x'] ?? 0) + $this->bleedPx;
            $y = intval($textOverlay['y'] ?? 0) + $this->bleedPx;
            $fontSize = intval($textOverlay['font_size'] ?? 40);
            $color = $textOverlay['color'] ?? '#000000';
            $fontFamily = $textOverlay['font_family'] ?? 'Arial';
            $rotation = floatval($textOverlay['rotation'] ?? 0);
            
            // Convert hex color to RGB
            $rgb = $this->hexToRgb($color);
            $textColor = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
            
            // Get font path
            $fontPath = $this->getFontPath($fontFamily);
            
            if ($fontPath && file_exists($fontPath)) {
                if ($rotation != 0) {
                    // Render rotated text
                    $this->renderRotatedText($canvas, $text, $fontPath, $fontSize, $textColor, $x, $y, $rotation);
                } else {
                    imagettftext($canvas, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
                }
            } else {
                // Fallback to built-in font (no rotation support)
                imagestring($canvas, 5, $x, $y, $text, $textColor);
            }
        }
        
        Log::info("Text rendered", ['count' => count($textOverlays)]);
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
     */
    private function getFontPath(string $fontFamily): ?string
    {
        $fontDirectories = [
            '/usr/share/fonts/',
            '/usr/local/share/fonts/',
            '/System/Library/Fonts/',
            'C:/Windows/Fonts/',
            storage_path('fonts/'),
        ];
        
        $fontMappings = [
            'Arial' => ['arial.ttf', 'Arial.ttf', 'arial.ttc'],
            'Helvetica' => ['Helvetica.ttf', 'helvetica.ttf'],
            'Times New Roman' => ['times.ttf', 'Times.ttf', 'times.ttc'],
            'Georgia' => ['Georgia.ttf', 'georgia.ttf'],
            'Verdana' => ['verdana.ttf', 'Verdana.ttf'],
            'Courier New' => ['cour.ttf', 'Courier.ttf'],
        ];
        
        $fontFiles = $fontMappings[$fontFamily] ?? [
            strtolower($fontFamily) . '.ttf',
            $fontFamily . '.ttf',
            strtolower($fontFamily) . '.otf',
            $fontFamily . '.otf',
        ];
        
        foreach ($fontDirectories as $directory) {
            if (is_dir($directory)) {
                foreach ($fontFiles as $fontFile) {
                    $fontPath = $directory . $fontFile;
                    if (file_exists($fontPath)) {
                        return $fontPath;
                    }
                }
            }
        }
        
        return null;
    }
}

