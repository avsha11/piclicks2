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
    private const FRAME_IMAGE_OVERSCAN_MM = 0.4;
    
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
    private int $frameVisibleThicknessPx;
    private int $frameInnerCornerRadiusPx;
    private int $frameImageOverscanPx;
    
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
        $this->frameVisibleThicknessPx = $this->mmToPx(self::FRAME_VISIBLE_THICKNESS_MM);
        $this->frameInnerCornerRadiusPx = $this->mmToPx(self::FRAME_INNER_CORNER_RADIUS_MM);
        $this->frameImageOverscanPx = max(1, $this->mmToPx(self::FRAME_IMAGE_OVERSCAN_MM));
        
        Log::debug("PrintFileService initialized", [
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
            Log::debug("generatePrintFiles started", [
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
            
            Log::debug("Block dimensions", [
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
            $this->renderImage($blockCanvas, $imagePath, $blockPrintW, $blockPrintH, $zoom, $rotate, $frameConfig);
            
            // Step 2: Apply Filter (on top of image)
            if (!empty($filter)) {
                $this->applyFilter($blockCanvas, $filter);
            }
            
            // Step 3: Render Text (on top of filter)
            if (!empty($textOverlays)) {
                $this->renderText($blockCanvas, $textOverlays, $blockClearW, $blockClearH, $frameConfig);
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
            
            Log::debug("generatePrintFiles completed", ['tiles' => count($tileExportPaths)]);
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
     * Generate preview-ready tiles (clear area, transparent background)
     * This reuses the print rendering logic but crops to the clear area and rescales
     *
     * @param array $blockConfig
     * @param float $targetTileWidthPx Desired tile width in the preview (defaults to 91px, editor size)
     * @return array{tiles: array<int, array{row:int,col:int,image:resource}>, tile_width:int, tile_height:int, preview_scale:float}
     */
    public function generatePreviewTiles(array $blockConfig, float $targetTileWidthPx = 91.0): array
    {
        try {
            Log::debug("generatePreviewTiles started", [
                'image' => $blockConfig['image_path'] ?? 'N/A',
                'span' => ($blockConfig['cols'] ?? 1) . 'x' . ($blockConfig['rows'] ?? 1),
            ]);

            if (empty($blockConfig['image_path']) || !File::exists(storage_path('app/public/' . $blockConfig['image_path']))) {
                Log::error("Preview image file not found", ['path' => $blockConfig['image_path'] ?? 'N/A']);
                return [
                    'tiles' => [],
                    'tile_width' => 0,
                    'tile_height' => 0,
                    'preview_scale' => 1.0,
                ];
            }

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
            $editorBlockW = $blockConfig['editor_block_width_px'] ?? ($cols * $this->clearTileWPx);
            $editorBlockH = $blockConfig['editor_block_height_px'] ?? ($rows * $this->clearTileHPx);

            // Calculate block dimensions (with bleed)
            $blockClearW = $cols * $this->clearTileWPx;
            $blockClearH = $rows * $this->clearTileHPx;
            $blockPrintW = $blockClearW + (2 * $this->bleedPx);
            $blockPrintH = $blockClearH + (2 * $this->bleedPx);

            // Create transparent block canvas
            $blockCanvas = imagecreatetruecolor($blockPrintW, $blockPrintH);
            imagealphablending($blockCanvas, false);
            imagesavealpha($blockCanvas, true);
            $transparent = imagecolorallocatealpha($blockCanvas, 0, 0, 0, 127);
            imagefilledrectangle($blockCanvas, 0, 0, $blockPrintW, $blockPrintH, $transparent);

            // Render image (pass editor dimensions for correct zoom calculation)
            $this->renderImage($blockCanvas, $imagePath, $blockPrintW, $blockPrintH, $zoom, $rotate, $frameConfig, true, $editorBlockW, $editorBlockH);

            // Apply filter (if any)
            if (!empty($filter)) {
                $this->applyFilter($blockCanvas, $filter);
            }

            // (Text overlays are rendered later for preview, skip here)

            // Render frame if needed (for preview, render at clear area scale)
            $frameCanvas = null;
            if ($frameConfig['exists'] ?? false) {
                // For preview, render frame at clear area dimensions with visible thickness
                $blockClearW = $cols * $this->clearTileWPx;
                $blockClearH = $rows * $this->clearTileHPx;
                $frameCanvas = $this->renderFrameForPreview($blockClearW, $blockClearH, $frameConfig);
            }

            // Determine preview scaling
            $previewTileWidth = max(1, intval(round($targetTileWidthPx)));
            $previewScale = $previewTileWidth / $this->clearTileWPx;
            $previewTileHeight = max(1, intval(round($this->clearTileHPx * $previewScale)));
            $previewCornerRadius = max(1, intval(round($this->clearCornerRadiusPx * $previewScale)));

            $tiles = [];

            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    $cropX = $this->bleedPx + ($col * $this->clearTileWPx);
                    $cropY = $this->bleedPx + ($row * $this->clearTileHPx);

                    $tileCanvas = imagecreatetruecolor($this->clearTileWPx, $this->clearTileHPx);
                    imagealphablending($tileCanvas, false);
                    imagesavealpha($tileCanvas, true);
                    imagefilledrectangle($tileCanvas, 0, 0, $this->clearTileWPx, $this->clearTileHPx, $transparent);

                    imagecopy(
                        $tileCanvas,
                        $blockCanvas,
                        0,
                        0,
                        $cropX,
                        $cropY,
                        $this->clearTileWPx,
                        $this->clearTileHPx
                    );

                    if ($frameCanvas !== null) {
                        // Frame is already at clear area scale, crop tile section directly
                        $frameCropX = $col * $this->clearTileWPx;
                        $frameCropY = $row * $this->clearTileHPx;
                        
                        $frameSection = imagecreatetruecolor($this->clearTileWPx, $this->clearTileHPx);
                        imagealphablending($frameSection, false);
                        imagesavealpha($frameSection, true);
                        imagefilledrectangle($frameSection, 0, 0, $this->clearTileWPx, $this->clearTileHPx, $transparent);

                        imagecopy(
                            $frameSection,
                            $frameCanvas,
                            0,
                            0,
                            $frameCropX,
                            $frameCropY,
                            $this->clearTileWPx,
                            $this->clearTileHPx
                        );

                        imagealphablending($tileCanvas, true);
                        imagecopy($tileCanvas, $frameSection, 0, 0, 0, 0, $this->clearTileWPx, $this->clearTileHPx);
                        imagealphablending($tileCanvas, false);
                        imagedestroy($frameSection);
                    }

                    $previewTile = imagecreatetruecolor($previewTileWidth, $previewTileHeight);
                    imagealphablending($previewTile, false);
                    imagesavealpha($previewTile, true);
                    imagefilledrectangle($previewTile, 0, 0, $previewTileWidth, $previewTileHeight, $transparent);

                    imagecopyresampled(
                        $previewTile,
                        $tileCanvas,
                        0,
                        0,
                        0,
                        0,
                        $previewTileWidth,
                        $previewTileHeight,
                        $this->clearTileWPx,
                        $this->clearTileHPx
                    );

                    $this->applyRoundedCorners($previewTile, $previewCornerRadius);

                    $tiles[] = [
                        'row' => $startRow + $row,
                        'col' => $startCol + $col,
                        'image' => $previewTile,
                    ];

                    imagedestroy($tileCanvas);
                }
            }

            imagedestroy($blockCanvas);
            if ($frameCanvas !== null) {
                imagedestroy($frameCanvas);
            }

            return [
                'tiles' => $tiles,
                'tile_width' => $previewTileWidth,
                'tile_height' => $previewTileHeight,
                'preview_scale' => $previewScale,
            ];
        } catch (\Exception $e) {
            Log::error("generatePreviewTiles error", [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return [
                'tiles' => [],
                'tile_width' => 0,
                'tile_height' => 0,
                'preview_scale' => 1.0,
            ];
        }
    }
    
    /**
     * Render image onto block canvas with proper scaling and transformations
     */
    private function renderImage($canvas, string $imagePath, int $blockW, int $blockH, string $zoom, string $rotate, array $frameConfig = [], bool $visibleAreaOnly = false, ?float $editorBlockW = null, ?float $editorBlockH = null): void
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        $sourceImage = imagecreatefromstring(file_get_contents($fullPath));
        
        if (!$sourceImage) {
            Log::error("Failed to load image", ['path' => $imagePath]);
            return;
        }
        
        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);
        
        // Parse zoom (format: "current|min" or single value)
        // Editor calculates minZoom as: max(viewportWidth/imgWidth, viewportHeight/imgHeight)
        // The zoom value is relative to this minZoom
        $zoomParts = explode('|', (string) $zoom);
        $currentZoom = null;
        $minZoom = null;

        if (isset($zoomParts[0]) && is_numeric($zoomParts[0])) {
            $currentZoom = max((float) $zoomParts[0], 0.0);
        }
        if (isset($zoomParts[1]) && is_numeric($zoomParts[1])) {
            $minZoom = max((float) $zoomParts[1], 0.0);
        }

        // Calculate zoom scale factor
        // If we have editor dimensions, recalculate minZoom based on actual target area
        // to ensure zoom is applied correctly regardless of scale differences
        $zoomScaleX = 1.0;
        $zoomScaleY = 1.0;
        
        if ($minZoom !== null && $minZoom > 0.0) {
            if ($currentZoom === null || $currentZoom <= 0.0) {
                $currentZoom = $minZoom;
            }
            // Zoom ratio is scale-independent, so we can use it directly
            $zoomRatio = max($currentZoom / $minZoom, 0.01);
            $zoomScaleX = $zoomRatio;
            $zoomScaleY = $zoomRatio;
        }
        
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
        
        // Calculate target area for image placement
        // Editor behavior: when frame exists, image is contained within frame's inner boundary (8mm inset)
        // For preview: image should be contained within frame's visible inner boundary (8mm) if frame exists
        // For print: image extends under frame with overscan (10mm print thickness)
        $frameInset = 0;
        $visibleInset = $visibleAreaOnly ? $this->bleedPx : 0;
        
        if ($frameConfig['exists'] ?? false) {
            if ($visibleAreaOnly) {
                // Preview: contain image within frame's visible inner boundary (8mm inset from clear area)
                $frameInset = $this->frameVisibleThicknessPx;
            } else {
                // Print: image extends under frame (10mm print thickness)
                $frameInset = $this->framePrintThicknessPx;
            }
        }
        
        $overscan = 0;
        $targetX = $frameInset + $visibleInset;
        $targetY = $frameInset + $visibleInset;
        $targetW = max(1, $blockW - ($frameInset * 2) - ($visibleInset * 2));
        $targetH = max(1, $blockH - ($frameInset * 2) - ($visibleInset * 2));
        
        if ($frameInset > 0 && !$visibleAreaOnly) {
            // Print only: add overscan so image extends slightly under frame
            $overscan = min($this->frameImageOverscanPx, $frameInset);
            $targetX = max(0, $targetX - $overscan);
            $targetY = max(0, $targetY - $overscan);
            $targetW = min($blockW - $targetX, $targetW + ($overscan * 2));
            $targetH = min($blockH - $targetY, $targetH + ($overscan * 2));
        }
        
        // Scale to cover target area - "cover" fit with zoom applied
        // Use standard cover fit calculation and apply zoom ratio directly
        // The zoom ratio (currentZoom / minZoom) is scale-independent, so it works for any target size
        $blockAspect = $targetW / $targetH;
        $srcAspect = $srcW / $srcH;
        
        if ($srcAspect > $blockAspect) {
            // Image is wider - fit height
            $scaledH = $targetH;
            $scaledW = intval($targetH * $srcAspect);
        } else {
            // Image is taller - fit width
            $scaledW = $targetW;
            $scaledH = intval($targetW / $srcAspect);
        }
        
        // Store base scaled size before zoom
        $baseScaledW = $scaledW;
        $baseScaledH = $scaledH;
        
        // Apply zoom scale (zoom ratio is scale-independent)
        $scaledW = max(1, (int) round($scaledW * $zoomScaleX));
        $scaledH = max(1, (int) round($scaledH * $zoomScaleY));
        
        // Log for debugging
        if ($visibleAreaOnly) {
            Log::debug("Preview zoom calculation (simplified)", [
                'src' => "{$srcW}x{$srcH}",
                'target' => "{$targetW}x{$targetH}",
                'editorBlock' => ($editorBlockW ?? 'N/A') . 'x' . ($editorBlockH ?? 'N/A'),
                'minZoom' => $minZoom,
                'currentZoom' => $currentZoom,
                'zoomRatio' => $zoomScaleX,
                'baseScaled' => "{$baseScaledW}x{$baseScaledH}",
                'finalScaled' => "{$scaledW}x{$scaledH}",
                'frameInset' => $frameInset,
            ]);
        }
        
        // Align to top-left by default (editor uses object-position: top left)
        $dstX = $targetX;
        $dstY = $targetY;
        
        // Enable alpha blending for proper image rendering
        imagealphablending($canvas, true);
        
        // Draw image
        imagecopyresampled(
            $canvas,
            $sourceImage,
            $dstX,
            $dstY,
            0,
            0,
            $scaledW,
            $scaledH,
            $srcW,
            $srcH
        );
        
        // Restore alpha blending state
        imagealphablending($canvas, false);
        
        imagedestroy($sourceImage);
        
        Log::debug("Image rendered", [
            'src' => "{$srcW}x{$srcH}px",
            'scaled' => "{$scaledW}x{$scaledH}px",
            'position' => "{$dstX},{$dstY}",
            'target_area' => "{$targetW}x{$targetH}px",
            'frame_inset' => $frameInset,
            'frame_overscan' => $overscan
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
        Log::debug("Applying filter", ['filter' => $filter]);
        
        switch ($filter) {
            case 'filter-noir':
                // CSS: grayscale(100%) contrast(1.2) = grayscale(100%) contrast(120%)
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // 120% contrast = -20 in GD
                Log::debug("Applied noir filter", ['steps' => 'grayscale + contrast(120%)']);
                break;
                
            case 'filter-stark':
                // CSS: grayscale(50%) brightness(100%) contrast(90%)
                // Apply partial grayscale by reducing saturation
                imagefilter($canvas, IMG_FILTER_CONTRAST, 10); // 90% contrast = +10 in GD
                imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 64); // 50% desaturation
                Log::debug("Applied stark filter", ['steps' => 'contrast(90%) + desaturate(50%)']);
                break;
                
            case 'filter-scandi':
                // CSS: brightness(120%) contrast(105%) grayscale(10%) hue-rotate(5deg)
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 20); // 120% brightness = +20
                imagefilter($canvas, IMG_FILTER_CONTRAST, -5); // 105% contrast = -5
                // Slight warm tint for hue-rotate simulation
                imagefilter($canvas, IMG_FILTER_COLORIZE, 15, 8, -5, 0);
                Log::debug("Applied scandi filter", ['steps' => 'brightness(120%) + contrast(105%) + warm tint']);
                break;
                
            case 'filter-capri':
                // CSS: contrast(120%) brightness(110%) saturate(150%) hue-rotate(-30deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20); // 120% contrast = -20
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 10); // 110% brightness = +10
                // Blue/cyan tint for hue-rotate(-30deg)
                imagefilter($canvas, IMG_FILTER_COLORIZE, -15, 5, 35, 0);
                Log::debug("Applied capri filter", ['steps' => 'contrast(120%) + brightness(110%) + cool tint']);
                break;
                
            case 'filter-nordic':
                // CSS: contrast(110%) brightness(80%) sepia(20%) hue-rotate(-15deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -10); // 110% contrast = -10
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -20); // 80% brightness = -20
                // Sepia + slight cool tint
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 30, 25, 15, 0); // Warm sepia tone
                Log::debug("Applied nordic filter", ['steps' => 'contrast(110%) + brightness(80%) + sepia']);
                break;
                
            case 'filter-belveder':
                // CSS: contrast(115%) brightness(90%) sepia(30%) hue-rotate(10deg)
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15); // 115% contrast = -15
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -10); // 90% brightness = -10
                // Stronger sepia tone
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 100, 60, 35, 0); // Rich sepia tone
                Log::debug("Applied belveder filter", ['steps' => 'contrast(115%) + brightness(90%) + rich sepia']);
                break;
                
            default:
                Log::warning("Unknown filter", ['filter' => $filter]);
                break;
        }
        
        Log::debug("Filter application completed", ['filter' => $filter]);
    }
    
    /**
     * Render text overlays on block canvas
     */
    private function renderText($canvas, array $textOverlays, int $blockClearW, int $blockClearH, array $frameConfig): void
    {
        if (empty($textOverlays)) {
            return;
        }
        
        // Editor tile dimensions taken from front-end tool.js actual tile sizes
        $editorTileWidth = 91.0;
        $editorTileHeight = 80.0;
        
        $blockCols = max(1, (int) round($blockClearW / $this->clearTileWPx));
        $blockRows = max(1, (int) round($blockClearH / $this->clearTileHPx));
        
        $editorBlockW = $blockCols * $editorTileWidth;
        $editorBlockH = $blockRows * $editorTileHeight;
        
        $scaleFactorX = $editorBlockW > 0 ? $blockClearW / $editorBlockW : 1.0;
        $scaleFactorY = $editorBlockH > 0 ? $blockClearH / $editorBlockH : 1.0;
        $avgScale = ($scaleFactorX + $scaleFactorY) / 2.0;
        
        $frameOffset = (!empty($frameConfig['exists'])) ? $this->framePrintThicknessPx : 0;
        
        Log::debug("Text rendering scale factors", [
            'block_cols' => $blockCols,
            'block_rows' => $blockRows,
            'editor_block_px' => "{$editorBlockW}x{$editorBlockH}",
            'print_block_px' => "{$blockClearW}x{$blockClearH}",
            'scale_x' => round($scaleFactorX, 4),
            'scale_y' => round($scaleFactorY, 4),
            'frame_offset_px' => $frameOffset
        ]);
        
        foreach ($textOverlays as $idx => $textOverlay) {
            $text = $textOverlay['text'] ?? '';
            if ($text === '') {
                continue;
            }
            
            $editorX = floatval($textOverlay['x'] ?? 0);
            $editorY = floatval($textOverlay['y'] ?? 0);
            $editorFontSize = floatval($textOverlay['font_size'] ?? 16);
            $color = $textOverlay['color'] ?? '#000000';
            $fontFamily = $textOverlay['font_family'] ?? 'Arial';
            $rotation = floatval($textOverlay['rotation'] ?? 0);
            $translateX = $textOverlay['translate_x'] ?? '-50%';
            $translateY = $textOverlay['translate_y'] ?? '-50%';
            
            // Scale CSS font-size (editor px) into print pixels, then convert to points for GD
            // Editor font size is in CSS pixels (96 DPI), scale to print pixels (300 DPI) using the same scale as positions
            // Use uniform scale factor (average of X and Y) for fonts to maintain aspect ratio
            $uniformScale = $avgScale;
            $printFontSizePx = max(1.0, $editorFontSize * $uniformScale);
            
            // Convert print pixels to points for imagettftext()
            // GD's imagettftext() expects font size in points (1 point = 1/72 inch)
            // At 300 DPI: 1 pixel = 1/300 inch
            // 1 point = 1/72 inch = (300/72) pixels = 4.167 pixels at 300 DPI
            // So: points = printPixels / (300/72) = printPixels * (72/300)
            // However, we need to account for the fact that CSS pixels are at 96 DPI
            // CSS 1px at 96 DPI = 1/96 inch = (72/96) points = 0.75 points
            // Print 1px at 300 DPI = 1/300 inch = (72/300) points = 0.24 points
            // The scale factor already accounts for the pixel size difference
            // So we just need: gdFontSize = printFontSizePx * (72 / DPI)
            $gdFontSize = $printFontSizePx * (72.0 / self::DPI);
            // CSS rotation: positive = clockwise
            // GD rotation: positive = counter-clockwise  
            // To match CSS visual rotation, we need to negate
            $cssRotationDegrees = $rotation;
            $gdRotationDegrees = 0 - $cssRotationDegrees;
            
            // Convert tile-relative editor coordinates to print coordinates
            // Editor coordinates are relative to the tile's top-left (0,0) in editor pixels
            // Print coordinates are relative to the block canvas top-left (0,0) in print pixels
            // The editorX/Y are already tile-relative center positions from CollageServices
            // Scale to print pixels and add bleed offset
            $centerX = (int) round($editorX * $scaleFactorX) + $this->bleedPx;
            $centerY = (int) round($editorY * $scaleFactorY) + $this->bleedPx;
            
            $fontPath = $this->getFontPath($fontFamily);
            if (!$fontPath || !file_exists($fontPath)) {
                Log::error("Font not found for text overlay", [
                    'font_family' => $fontFamily,
                    'resolved_path' => $fontPath,
                    'text_sample' => substr($text, 0, 20)
                ]);
                continue;
            }
            
            // Note: translate is already applied in CollageServices when calculating centerX/centerY
            // The x/y values passed here are already the center position with translate included
            // So we don't need to apply translate again here
            
            // Check if text center is within reasonable bounds of the tile (in editor coordinates)
            // If position is too far outside (more than 2x text size), skip rendering
            // This handles cases where text center is outside tile but text still intersects
            $maxTextSizeEditor = max($editorFontSize * 2, 200); // At least 200px buffer in editor coords
            $tileRelativeX = $editorX; // Already tile-relative from CollageServices (in editor pixels)
            $tileRelativeY = $editorY;
            
            // Skip if center is way outside tile bounds (text won't be visible)
            // $editorBlockW and $editorBlockH are already calculated above
            if ($tileRelativeX < -$maxTextSizeEditor || $tileRelativeX > $editorBlockW + $maxTextSizeEditor ||
                $tileRelativeY < -$maxTextSizeEditor || $tileRelativeY > $editorBlockH + $maxTextSizeEditor) {
                Log::debug("Skipping text overlay - center too far outside tile bounds", [
                    'text' => substr($text, 0, 20),
                    'tile_relative_pos' => "{$tileRelativeX},{$tileRelativeY}",
                    'editor_block_size' => "{$editorBlockW}x{$editorBlockH}",
                    'max_text_size' => $maxTextSizeEditor
                ]);
                continue;
            }
            
            $rgb = $this->hexToRgb($color);
            $textColor = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
            
            // Calculate expected print position for verification
            $expectedPrintX = $editorX * $scaleFactorX + $this->bleedPx;
            $expectedPrintY = $editorY * $scaleFactorY + $this->bleedPx;
            
            Log::debug("Rendering text overlay", [
                'index' => $idx,
                'text' => substr($text, 0, 30),
                'editor_font_size_px' => $editorFontSize,
                'editor_pos' => "{$editorX},{$editorY}",
                'scale_factors' => "X:{$scaleFactorX}, Y:{$scaleFactorY}, avg:{$avgScale}",
                'print_font_size_px' => round($printFontSizePx, 2),
                'print_font_size_pt' => round($gdFontSize, 2),
                'font_size_calc' => "{$editorFontSize} * {$avgScale} = {$printFontSizePx}px, * (72/300) = {$gdFontSize}pt",
                'print_center' => "{$centerX},{$centerY}",
                'expected_print_center' => round($expectedPrintX, 1) . "," . round($expectedPrintY, 1),
                'position_calc' => "({$editorX} * {$scaleFactorX} + {$this->bleedPx}, {$editorY} * {$scaleFactorY} + {$this->bleedPx})",
                'translate' => "{$translateX},{$translateY}",
                'rotation_css' => $cssRotationDegrees,
                'rotation_gd' => $gdRotationDegrees,
                'font' => basename($fontPath),
                'block_size' => "{$blockClearW}x{$blockClearH}",
                'editor_block_size' => "{$editorBlockW}x{$editorBlockH}",
                'bleed_px' => $this->bleedPx
            ]);
            
            $bboxRotated = imagettfbbox($gdFontSize, $gdRotationDegrees, $fontPath, $text);
            $minX = min($bboxRotated[0], $bboxRotated[2], $bboxRotated[4], $bboxRotated[6]);
            $maxX = max($bboxRotated[0], $bboxRotated[2], $bboxRotated[4], $bboxRotated[6]);
            $minY = min($bboxRotated[1], $bboxRotated[3], $bboxRotated[5], $bboxRotated[7]);
            $maxY = max($bboxRotated[1], $bboxRotated[3], $bboxRotated[5], $bboxRotated[7]);
            $bboxCenterX = ($minX + $maxX) / 2;
            $bboxCenterY = ($minY + $maxY) / 2;
            
            $baselineX = $centerX - $bboxCenterX;
            $baselineY = $centerY - $bboxCenterY;
            
            imagettftext($canvas, $gdFontSize, $gdRotationDegrees, (int) round($baselineX), (int) round($baselineY), $textColor, $fontPath, $text);
        }
        
        Log::debug("Text rendering completed", ['total_overlays' => count($textOverlays)]);
    }
    
    private function convertCssTranslateToPixels($value, float $referenceSize, int $fallbackSize): float
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return 0.0;
        }
        
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        $value = trim((string) $value, " \"'");
        
        if ($value === '') {
            return 0.0;
        }
        
        if (str_ends_with($value, '%')) {
            $percent = floatval(rtrim($value, '%'));
            $reference = $referenceSize > 0 ? $referenceSize : $fallbackSize;
            return ($percent / 100.0) * $reference;
        }
        
        if (str_ends_with($value, 'px')) {
            return floatval(rtrim($value, 'px'));
        }
        
        return 0.0;
    }
    
    /**
     * Render frame for preview (clear area scale, 8mm visible thickness)
     */
    private function renderFrameForPreview(int $blockClearW, int $blockClearH, array $frameConfig)
    {
        $frameColor = $this->hexToRgb($frameConfig['color_hex'] ?? '#000000');
        
        // Create frame canvas at clear area dimensions
        $frameCanvas = imagecreatetruecolor($blockClearW, $blockClearH);
        imagealphablending($frameCanvas, false);
        imagesavealpha($frameCanvas, true);
        $transparent = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
        imagefilledrectangle($frameCanvas, 0, 0, $blockClearW, $blockClearH, $transparent);
        
        // Draw full clear area rounded rect with R8
        $frameColorAllocated = imagecolorallocate($frameCanvas, $frameColor[0], $frameColor[1], $frameColor[2]);
        $this->drawFilledRoundedRect($frameCanvas, 0, 0, $blockClearW, $blockClearH, $this->clearCornerRadiusPx, $frameColorAllocated);
        
        // Punch inner hole inset by visible frame thickness (8mm) with R2 inner radius
        $innerX = $this->frameVisibleThicknessPx;
        $innerY = $this->frameVisibleThicknessPx;
        $innerW = $blockClearW - (2 * $this->frameVisibleThicknessPx);
        $innerH = $blockClearH - (2 * $this->frameVisibleThicknessPx);
        if ($innerW > 0 && $innerH > 0) {
            $innerTransparent = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
            $this->drawFilledRoundedRect($frameCanvas, $innerX, $innerY, $innerW, $innerH, $this->frameInnerCornerRadiusPx, $innerTransparent);
        }
        
        return $frameCanvas;
    }
    
    /**
     * Render frame for the entire block (print scale)
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
        
        // Punch inner hole inset by frame thickness with R2 inner radius
        $innerX = $this->framePrintThicknessPx;
        $innerY = $this->framePrintThicknessPx;
        $innerW = $blockW - (2 * $this->framePrintThicknessPx);
        $innerH = $blockH - (2 * $this->framePrintThicknessPx);
        if ($innerW > 0 && $innerH > 0) {
            $innerTransparent = imagecolorallocatealpha($frameCanvas, 0, 0, 0, 127);
            $this->drawFilledRoundedRect($frameCanvas, $innerX, $innerY, $innerW, $innerH, $this->frameInnerCornerRadiusPx, $innerTransparent);
        }
        
        Log::debug("Frame rendered", [
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
            Log::debug("Tile saved", ['path' => $fileName, 'row' => $row, 'col' => $col]);
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
                        Log::debug("Font found", ['font' => $fontFamily, 'path' => $fontPath]);
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
                        Log::debug("Using Arial fallback", ['path' => $fontPath]);
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

