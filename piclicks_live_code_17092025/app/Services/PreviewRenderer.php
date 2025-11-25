<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PreviewRenderer
{
    private const TILE_WIDTH = 91;   // editor clear width in px
    private const TILE_HEIGHT = 80;  // editor clear height in px
    private const TILE_GAP = 2;      // gap between tiles in px
    private const CORNER_RADIUS = 8; // editor radius in px
    private const FRAME_THICKNESS = 7; // editor frame border width in px
    private const CONTAINER_PADDING = 10; // .tool-inner padding in px
    private const SCALE = 2;         // upscale factor for better quality
    private const TEXT_SIZE_ADJUST = 0.70; // fine-tuned GD vs CSS size (reduced for better match)
    // Text overlay element has padding that affects getBoundingClientRect()
    private const TEXT_OVERLAY_PADDING_X = 10; // horizontal padding (left + right) in px
    private const TEXT_OVERLAY_PADDING_Y = 5;  // vertical padding (top + bottom) in px

    private const FRAME_CLASSES = [
        'success-black-outlined' => '#000000',
        'success-white-outlined' => '#ffffff',
    ];

    /**
     * Render collage preview PNG and return metadata.
     *
     * @return array{path:string,width:int,height:int,cols:int,rows:int,tile_width:int,tile_height:int,gap:int}|null
     */
    public function render(array $master, array $tiles): ?array
    {
        $occupied = $this->mapOccupiedTiles($tiles);
        if (empty($occupied['tiles'])) {
            Log::warning('PreviewRenderer: no occupied tiles');
            return null;
        }

        $minCol = $occupied['minCol'];
        $maxCol = $occupied['maxCol'];
        $minRow = $occupied['minRow'];
        $maxRow = $occupied['maxRow'];

        $tileWidthPx = self::TILE_WIDTH * self::SCALE;
        $tileHeightPx = self::TILE_HEIGHT * self::SCALE;
        $gapPx = self::TILE_GAP * self::SCALE;

        $colsCount = ($maxCol - $minCol + 1);
        $rowsCount = ($maxRow - $minRow + 1);

        // Calculate canvas size with small safety margin to account for rounding errors
        // and ensure rounded corners/ellipses don't get clipped
        $canvasWidth = $colsCount * $tileWidthPx + ($colsCount - 1) * $gapPx;
        $canvasHeight = $rowsCount * $tileHeightPx + ($rowsCount - 1) * $gapPx;
        
        // Add small safety margin (2 pixels) to prevent clipping from rounding errors
        // This ensures ellipses at rounded corners don't get cut off
        $canvasWidth += 2;
        $canvasHeight += 2;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        $maskCanvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        
        // Store canvas dimensions for bounds checking
        $canvasMaxX = $canvasWidth - 1;
        $canvasMaxY = $canvasHeight - 1;
        imagealphablending($maskCanvas, false);
        imagesavealpha($maskCanvas, true);
        $maskTransparent = imagecolorallocatealpha($maskCanvas, 0, 0, 0, 127);
        imagefill($maskCanvas, 0, 0, $maskTransparent);
        $maskOpaque = imagecolorallocatealpha($maskCanvas, 0, 0, 0, 0);

        $tileData = $occupied['tiles'];
        foreach ($tileData as $tileInfo) {
            $this->renderTileBlock($canvas, $maskCanvas, $maskOpaque, $tileInfo, $master, $minCol, $minRow, $tileWidthPx, $tileHeightPx, $gapPx);
        }

        // Apply mask to remove gaps and respect rounded corners
        $this->applyMaskToCanvas($canvas, $maskCanvas, $canvasWidth, $canvasHeight, $transparent);

        // Draw text overlays on top
        $this->renderTextOverlays($canvas, $maskCanvas, $canvasWidth, $canvasHeight, $master, $minCol, $minRow, $tileWidthPx, $tileHeightPx, $gapPx);

        // Use consistent filename (without timestamp) so we can reuse the same preview image
        // This ensures admin/cart thumbnails use the exact same image as the Preview page
        $uniqueId = $master['unique_id'] ?? uniqid();
        $relativePath = 'temp/preview_' . $uniqueId . '.png';
        $fullPath = storage_path('app/public/' . $relativePath);
        
        // Delete old preview image if it exists (in case collage was updated)
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!imagepng($canvas, $fullPath, 9)) {
            Log::error('PreviewRenderer: failed to save preview image', ['path' => $fullPath]);
            imagedestroy($canvas);
            return null;
        }

        imagedestroy($canvas);
        imagedestroy($maskCanvas);

        Log::info('PreviewRenderer: preview generated', [
            'path' => $relativePath,
            'size' => [$canvasWidth, $canvasHeight],
        ]);

        return [
            'path' => $relativePath,
            'width' => $canvasWidth,
            'height' => $canvasHeight,
            'cols' => $colsCount,
            'rows' => $rowsCount,
            'tile_width' => $tileWidthPx,
            'tile_height' => $tileHeightPx,
            'gap' => $gapPx,
        ];
    }

    /**
     * Build map of occupied tiles & block metadata.
     */
    private function mapOccupiedTiles(array $tiles): array
    {
        $result = [
            'tiles' => [],
            'minRow' => PHP_INT_MAX,
            'maxRow' => 0,
            'minCol' => PHP_INT_MAX,
            'maxCol' => 0,
        ];

        foreach ($tiles as $tile) {
            if (($tile['empty'] ?? 1) == 1) {
                continue;
            }
            $otherSettings = json_decode($tile['other_settings'] ?? '{}', true) ?: [];
            [$cols, $rows] = $this->parseBlockSize($otherSettings['imageDivDataMargin'] ?? null);
            [$startCol, $startRow] = $this->parsePositionFromStyle($otherSettings['imageDivStyle'] ?? '');
            $dimensions = $this->parseActualDimensionsFromStyle($otherSettings['imageDivStyle'] ?? '');

            $result['minCol'] = min($result['minCol'], $startCol);
            $result['minRow'] = min($result['minRow'], $startRow);
            $result['maxCol'] = max($result['maxCol'], $startCol + $cols - 1);
            $result['maxRow'] = max($result['maxRow'], $startRow + $rows - 1);

            $result['tiles'][] = [
                'tile' => $tile,
                'cols' => $cols,
                'rows' => $rows,
                'startCol' => $startCol,
                'startRow' => $startRow,
                'editorWidth' => $dimensions['width'],
                'editorHeight' => $dimensions['height'],
                'settings' => $otherSettings,
            ];
        }

        if ($result['minCol'] === PHP_INT_MAX) {
            $result['minCol'] = 0;
            $result['minRow'] = 0;
        }

        return $result;
    }

    /**
     * Render tile or stretched block onto main canvas.
     */
    private function renderTileBlock($canvas, $maskCanvas, int $maskOpaqueColor, array $blockInfo, array $master, int $minCol, int $minRow, int $tileWidth, int $tileHeight, int $gap): void
    {
        $tile = $blockInfo['tile'];
        $cols = $blockInfo['cols'];
        $rows = $blockInfo['rows'];
        $startCol = $blockInfo['startCol'];
        $startRow = $blockInfo['startRow'];
        $editorWidth = max($blockInfo['editorWidth'], self::TILE_WIDTH * $cols);
        $editorHeight = max($blockInfo['editorHeight'], self::TILE_HEIGHT * $rows);

        $imagePath = $this->resolveImagePath($tile);
        if (!$imagePath) {
            Log::warning('PreviewRenderer: tile image missing', ['tile_id' => $tile['id'] ?? null]);
            return;
        }

        $sourceImage = @imagecreatefromstring(file_get_contents($imagePath));
        if (!$sourceImage) {
            Log::warning('PreviewRenderer: unable to load image', ['path' => $imagePath]);
            return;
        }

        // Apply rotation
        $rotate = $blockInfo['settings']['rotate'] ?? '1';
        $rotationDegrees = 0;
        switch ($rotate) {
            case '2': $rotationDegrees = 90; break;
            case '3': $rotationDegrees = 180; break;
            case '4': $rotationDegrees = 270; break;
        }
        if ($rotationDegrees !== 0) {
            $rotated = imagerotate($sourceImage, -$rotationDegrees, 0);
            imagedestroy($sourceImage);
            $sourceImage = $rotated;
        }

        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);

        // Frame color per master frame class
        $frameColorHex = null;
        if (!empty($master['frame']) && isset(self::FRAME_CLASSES[$master['frame']])) {
            $frameColorHex = self::FRAME_CLASSES[$master['frame']];
        }

        $contentWidth = $cols * $tileWidth;
        $contentHeight = $rows * $tileHeight;
        
        // When frame exists, image should be contained within frame's inner boundary
        // Frame thickness is 7px at editor scale, 14px at preview scale (7 * 2)
        $frameThicknessPx = 0;
        if ($frameColorHex) {
            $frameThicknessPx = intval(self::FRAME_THICKNESS * self::SCALE); // 7 * 2 = 14px
        }
        
        // Target area for image scaling: frame's inner boundary if frame exists, otherwise full content area
        $targetWidth = $frameThicknessPx > 0 ? max(1, $contentWidth - ($frameThicknessPx * 2)) : $contentWidth;
        $targetHeight = $frameThicknessPx > 0 ? max(1, $contentHeight - ($frameThicknessPx * 2)) : $contentHeight;

        // Parse zoom correctly: format is "currentZoom|minZoom"
        // Editor calculates: scaleMin = max(viewportWidth/imgWidth, viewportHeight/imgHeight) = minZoom
        // Editor applies: finalScale = scaleMin * (currentZoom / minZoom)
        $zoomStr = $blockInfo['settings']['zoom'] ?? '0';
        $zoomParts = explode('|', $zoomStr);
        $currentZoom = null;
        $minZoom = null;
        
        if (isset($zoomParts[0]) && is_numeric($zoomParts[0])) {
            $currentZoom = max((float) $zoomParts[0], 0.0);
        }
        if (isset($zoomParts[1]) && is_numeric($zoomParts[1])) {
            $minZoom = max((float) $zoomParts[1], 0.0);
        }

        // Calculate editor's viewport dimensions (matching editor logic)
        // Editor uses different viewport logic for single tiles (91x80) vs stretched tiles
        $viewportW = null;
        $viewportH = null;
        
        // Check if this is a single-tile block (91x80 in editor pixels)
        if (abs($editorWidth - self::TILE_WIDTH) < 1.0 && abs($editorHeight - self::TILE_HEIGHT) < 1.0) {
            // Single tile - try fixed viewport sizes (editor uses 342x300, 456x400, or 570x500)
            // Try to match stored minZoom to determine which viewport was used
            $fixedViewports = [
                [342.0, 300.0],
                [456.0, 400.0],
                [570.0, 500.0]
            ];
            
            if ($minZoom !== null && $minZoom > 0.0) {
                // Try each viewport and pick the one that matches minZoom best
                $bestMatch = null;
                $bestDiff = PHP_FLOAT_MAX;
                
                foreach ($fixedViewports as [$vw, $vh]) {
                    $testMinZoom = max($vw / $srcW, $vh / $srcH);
                    $diff = abs($testMinZoom - $minZoom);
                    if ($diff < $bestDiff) {
                        $bestDiff = $diff;
                        $bestMatch = [$vw, $vh];
                    }
                }
                
                if ($bestMatch !== null) {
                    $viewportW = $bestMatch[0];
                    $viewportH = $bestMatch[1];
                }
            }
            
            // Fallback to middle viewport if no match found
            if ($viewportW === null || $viewportH === null) {
                $viewportW = 456.0;
                $viewportH = 400.0;
            }
        } else {
            // Stretched tiles: viewport = min(blockSize * 2, maxViewport) maintaining aspect ratio
            $maxViewportW = 650.0;
            $maxViewportH = 570.0;
            $editorAspect = $editorWidth / $editorHeight;
            
            $viewportW = min($editorWidth * 2.0, $maxViewportW);
            $viewportH = min($editorHeight * 2.0, $maxViewportH);
            
            // Maintain aspect ratio
            if ($viewportW / $viewportH > $editorAspect) {
                $viewportW = $viewportH * $editorAspect;
            } else {
                $viewportH = $viewportW / $editorAspect;
            }
        }

        // Calculate scale using editor's method
        if ($minZoom !== null && $minZoom > 0.0 && $currentZoom !== null && $currentZoom > 0.0) {
            // We have stored zoom values - use editor's exact calculation
            // Calculate what the image size would be in the editor's viewport
            $editorFinalScale = $minZoom * ($currentZoom / $minZoom); // This equals currentZoom
            $editorScaledW = $srcW * $editorFinalScale;
            $editorScaledH = $srcH * $editorFinalScale;
            
            // Now scale from editor's viewport to preview's target area (inside frame if frame exists)
            // This maintains the same relative crop/position
            $scaleToTargetX = $targetWidth / $viewportW;
            $scaleToTargetY = $targetHeight / $viewportH;
            
            // Use max() to match CSS object-fit: cover behavior (ensures image covers container)
            $scaleToTarget = max($scaleToTargetX, $scaleToTargetY);
            
            // Apply scaling to get final dimensions
            $scaledW = max(1, (int) round($editorScaledW * $scaleToTarget));
            $scaledH = max(1, (int) round($editorScaledH * $scaleToTarget));
        } else {
            // Fallback: standard cover fit calculation for target area (inside frame if frame exists)
            $scaleFactor = max($targetWidth / $srcW, $targetHeight / $srcH);
            $scaledW = intval($srcW * $scaleFactor);
            $scaledH = intval($srcH * $scaleFactor);
        }

        $scaledImage = imagecreatetruecolor($scaledW, $scaledH);
        imagealphablending($scaledImage, false);
        imagesavealpha($scaledImage, true);
        $trans = imagecolorallocatealpha($scaledImage, 0, 0, 0, 127);
        imagefill($scaledImage, 0, 0, $trans);
        imagealphablending($scaledImage, true);

        imagecopyresampled($scaledImage, $sourceImage, 0, 0, 0, 0, $scaledW, $scaledH, $srcW, $srcH);
        imagedestroy($sourceImage);

        // Apply filter if needed
        $filter = $master['filter'] ?? null;
        if (!empty($filter) && $filter !== 'filter-original') {
            $this->applyFilter($scaledImage, $filter);
        }

        // Frame color per master frame class
        $frameColorHex = null;
        if (!empty($master['frame']) && isset(self::FRAME_CLASSES[$master['frame']])) {
            $frameColorHex = self::FRAME_CLASSES[$master['frame']];
        }

        // For each tile inside block, copy the respective portion
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $targetCol = $startCol + $c;
                $targetRow = $startRow + $r;
                $destX = ($targetCol - $minCol) * ($tileWidth + $gap);
                $destY = ($targetRow - $minRow) * ($tileHeight + $gap);

                $tileCanvas = imagecreatetruecolor($tileWidth, $tileHeight);
                imagealphablending($tileCanvas, false);
                imagesavealpha($tileCanvas, true);
                $tileTrans = imagecolorallocatealpha($tileCanvas, 0, 0, 0, 127);
                imagefill($tileCanvas, 0, 0, $tileTrans);
                imagealphablending($tileCanvas, true);

                // Calculate source position in scaled image for this tile
                // The scaled image is sized to targetWidth x targetHeight (inside frame if frame exists)
                // Each tile's portion in the scaled image is proportional
                $tileTargetWidth = $targetWidth / $cols;
                $tileTargetHeight = $targetHeight / $rows;
                
                // Source position in scaled image (proportional to tile position in block)
                // This maintains continuity across tiles
                $srcX = intval(($scaledW / $targetWidth) * ($tileTargetWidth * $c));
                $srcY = intval(($scaledH / $targetHeight) * ($tileTargetHeight * $r));
                
                // Amount to copy from scaled image for this tile
                $copyWidth = intval(($scaledW / $targetWidth) * $tileTargetWidth);
                $copyHeight = intval(($scaledH / $targetHeight) * $tileTargetHeight);
                
                // Destination position on tile canvas
                // If frame exists, center the image portion within the tile (accounting for frame thickness)
                // If no frame, position at top-left
                $dstX = $frameThicknessPx;
                $dstY = $frameThicknessPx;

                imagecopy($tileCanvas, $scaledImage, $dstX, $dstY, $srcX, $srcY, $copyWidth, $copyHeight);

                // Apply rounded corners
                $this->applyRoundedCorners($tileCanvas, intval(self::CORNER_RADIUS * self::SCALE));
                $this->drawRoundedRectangle($maskCanvas, $destX, $destY, $tileWidth, $tileHeight, intval(self::CORNER_RADIUS * self::SCALE), $maskOpaqueColor, true);

                // Clamp destination coordinates to prevent clipping
                $canvasWidth = imagesx($canvas);
                $canvasHeight = imagesy($canvas);
                $destX = max(0, min($destX, $canvasWidth - 1));
                $destY = max(0, min($destY, $canvasHeight - 1));
                $copyWidth = min($tileWidth, $canvasWidth - $destX);
                $copyHeight = min($tileHeight, $canvasHeight - $destY);
                
                if ($copyWidth > 0 && $copyHeight > 0) {
                    imagecopy($canvas, $tileCanvas, $destX, $destY, 0, 0, $copyWidth, $copyHeight);
                }
                imagedestroy($tileCanvas);
            }
        }

        // Draw one frame per stretched image block (or single tile)
        if ($frameColorHex) {
            $frameColor = $this->allocateHex($canvas, $frameColorHex);
            $frameThickness = intval(self::FRAME_THICKNESS * self::SCALE);
            $blockX = ($startCol - $minCol) * ($tileWidth + $gap);
            $blockY = ($startRow - $minRow) * ($tileHeight + $gap);
            $blockW = $cols * $tileWidth + ($cols - 1) * $gap;
            $blockH = $rows * $tileHeight + ($rows - 1) * $gap;
            $this->drawFrame($canvas, $blockX, $blockY, $blockW, $blockH, intval(self::CORNER_RADIUS * self::SCALE), $frameThickness, $frameColor);
        }

        imagedestroy($scaledImage);
    }

    private function drawFrame($canvas, int $x, int $y, int $w, int $h, int $radius, int $thickness, int $color): void
    {
        // Draw frame into its own transparent layer, then overlay,
        // so we don't erase the underlying tile content.
        $frameImg = imagecreatetruecolor($w, $h);
        imagealphablending($frameImg, false);
        imagesavealpha($frameImg, true);
        $transparent = imagecolorallocatealpha($frameImg, 0, 0, 0, 127);
        imagefill($frameImg, 0, 0, $transparent);
        // Outer shape (frame color)
        $this->drawRoundedRectangle($frameImg, 0, 0, $w, $h, $radius, $color, true);

        // Punch inner hole (fully transparent)
        $innerX = $thickness;
        $innerY = $thickness;
        $innerW = $w - 2 * $thickness;
        $innerH = $h - 2 * $thickness;
        if ($innerW > 0 && $innerH > 0) {
            $this->drawRoundedRectangle($frameImg, $innerX, $innerY, $innerW, $innerH, max(0, $radius - $thickness), $transparent, true);
        }

        // Clamp frame coordinates to canvas bounds to prevent clipping
        $canvasWidth = imagesx($canvas);
        $canvasHeight = imagesy($canvas);
        $x = max(0, min($x, $canvasWidth - 1));
        $y = max(0, min($y, $canvasHeight - 1));
        $copyWidth = min($w, $canvasWidth - $x);
        $copyHeight = min($h, $canvasHeight - $y);
        
        if ($copyWidth > 0 && $copyHeight > 0) {
            imagecopy($canvas, $frameImg, $x, $y, 0, 0, $copyWidth, $copyHeight);
        }
        imagedestroy($frameImg);
    }

    private function drawRoundedRectangle($canvas, int $x, int $y, int $w, int $h, int $r, int $color, bool $fill = false): void
    {
        $canvasWidth = imagesx($canvas);
        $canvasHeight = imagesy($canvas);
        
        // Clamp coordinates to canvas bounds to prevent clipping
        $x = max(0, min($x, $canvasWidth - 1));
        $y = max(0, min($y, $canvasHeight - 1));
        $w = min($w, $canvasWidth - $x);
        $h = min($h, $canvasHeight - $y);
        
        if ($w <= 0 || $h <= 0) {
            return; // Nothing to draw
        }
        
        $width = $fill ? $w : $w - 1;
        $height = $fill ? $h : $h - 1;
        
        // Ensure width and height don't exceed remaining canvas space
        $width = min($width, $canvasWidth - $x);
        $height = min($height, $canvasHeight - $y);
        
        // Clamp radius to fit within dimensions
        $r = min($r, intval(min($width, $height) / 2));
        
        if ($r <= 0) {
            // No rounded corners, just draw rectangle
            imagefilledrectangle($canvas, $x, $y, $x + $width, $y + $height, $color);
            return;
        }
        
        imagefilledrectangle($canvas, $x + $r, $y, $x + $width - $r, $y + $height, $color);
        imagefilledrectangle($canvas, $x, $y + $r, $x + $width, $y + $height - $r, $color);
        
        // Clamp ellipse centers to canvas bounds
        $topLeftX = max($r, min($x + $r, $canvasWidth - $r));
        $topLeftY = max($r, min($y + $r, $canvasHeight - $r));
        $topRightX = max($r, min($x + $width - $r, $canvasWidth - $r));
        $bottomLeftY = max($r, min($y + $height - $r, $canvasHeight - $r));
        $bottomRightX = max($r, min($x + $width - $r, $canvasWidth - $r));
        $bottomRightY = max($r, min($y + $height - $r, $canvasHeight - $r));
        
        imagefilledellipse($canvas, $topLeftX, $topLeftY, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $topRightX, $topLeftY, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $topLeftX, $bottomLeftY, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $bottomRightX, $bottomRightY, $r * 2, $r * 2, $color);
    }

    private function applyRoundedCorners($image, int $radius): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $mask = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);
        $color = imagecolorallocate($mask, 0, 0, 0);
        imagefilledrectangle($mask, $radius, 0, $width - $radius, $height, $color);
        imagefilledrectangle($mask, 0, $radius, $width, $height - $radius, $color);
        imagefilledellipse($mask, $radius, $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($mask, $width - $radius, $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($mask, $radius, $height - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($mask, $width - $radius, $height - $radius, $radius * 2, $radius * 2, $color);

        imagealphablending($image, false);
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $maskPixel = imagecolorat($mask, $x, $y) & 0xFF;
                if ($maskPixel === 0) {
                    // keep
                    continue;
                }
                imagesetpixel($image, $x, $y, $transparent);
            }
        }
        imagealphablending($image, true);
        imagedestroy($mask);
    }

    private function applyFilter($canvas, string $filter): void
    {
        switch ($filter) {
            case 'filter-noir':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20);
                break;
            case 'filter-stark':
                imagefilter($canvas, IMG_FILTER_CONTRAST, 10);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 64);
                break;
            case 'filter-scandi':
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 20);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -5);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 15, 8, -5, 0);
                break;
            case 'filter-capri':
                imagefilter($canvas, IMG_FILTER_CONTRAST, -20);
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 10);
                imagefilter($canvas, IMG_FILTER_COLORIZE, -15, 5, 35, 0);
                break;
            case 'filter-nordic':
                imagefilter($canvas, IMG_FILTER_CONTRAST, -10);
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -20);
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 30, 25, 15, 0);
                break;
            case 'filter-belveder':
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15);
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -10);
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 100, 60, 35, 0);
                break;
            default:
                break;
        }
    }

    private function renderTextOverlays($canvas, $maskCanvas, int $canvasWidth, int $canvasHeight, array $master, int $minCol, int $minRow, int $tileWidth, int $tileHeight, int $gap): void
    {
        $overlays = $this->parseTextOverlays($master['text_editor'] ?? null);
        if (empty($overlays)) {
            return;
        }

        $textCanvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagealphablending($textCanvas, false);
        imagesavealpha($textCanvas, true);
        $textTransparent = imagecolorallocatealpha($textCanvas, 0, 0, 0, 127);
        imagefill($textCanvas, 0, 0, $textTransparent);
        imagealphablending($textCanvas, true);

        foreach ($overlays as $overlay) {
            Log::info('PreviewRenderer text overlay', [
                'raw' => $overlay,
                'canvas_width' => $canvasWidth,
                'canvas_height' => $canvasHeight,
                'min_col' => $minCol,
                'min_row' => $minRow,
            ]);
            $text = $overlay['text'];
            if (trim($text) === '') {
                continue;
            }

            $scaleFactor = $tileWidth / self::TILE_WIDTH;

            $fontSize = max(1, intval(($overlay['font_size'] ?? 40) * $scaleFactor * self::TEXT_SIZE_ADJUST));
            $rotation = floatval($overlay['rotation'] ?? 0);
            $fontPath = $this->getFontPath($overlay['font_family'] ?? 'Arial');
            if (!$fontPath) {
                continue;
            }

            $color = $this->allocateHex($textCanvas, $overlay['color'] ?? '#000000');

            // COORDINATE SYSTEM TRANSFORMATION:
            // Editor structure:
            //   .middle (text is positioned relative to this)
            //     .middle-top (height ~46px when visible, but hidden when saving)
            //     .tool-inner (padding: 10px, contains the grid)
            //       #preview-grid (the actual grid)
            //     .middle-bottom
            //
            // When saving, text position is stored with .middle-top hidden, so:
            // - Text stored (x, y) = center point relative to .middle
            // - But grid is inside .tool-inner which has padding: 10px
            // - Grid's top-left corner in .middle coordinates: (10, 10 + middle-top-height)
            // - Since middle-top is hidden when saving, grid top = 10px from .middle top
            //
            // Canvas: (0,0) = top-left of occupied tiles (after cropping to minCol/minRow)
            //
            // Transformation steps:
            // 1. Editor position (center, relative to .middle): (x, y)
            // 2. Grid position (center, relative to grid origin in .middle coords): (x - 10, y - 10)
            // 3. Canvas position (center, relative to canvas origin):
            //    - Subtract occupied tile offset in grid pixel coordinates
            //    - minCol starts at: minCol * (TILE_WIDTH + TILE_GAP) pixels in grid
            //    - minRow starts at: minRow * (TILE_HEIGHT + TILE_GAP) pixels in grid
            // 4. Scale to canvas coordinates: multiply by scaleFactor
            
            $editorX = floatval($overlay['x'] ?? 0);
            $editorY = floatval($overlay['y'] ?? 0);
            
            // CRITICAL: Text is positioned relative to .middle (not .tool-inner)
            // Tiles are positioned relative to the grid (which is inside .tool-inner)
            // .tool-inner has padding: 10px, so grid's top-left in .middle coords is at (10, 10)
            // When saving, .middle-top is hidden, so no additional vertical offset
            //
            // Convert from .middle coordinates to grid pixel coordinates
            $gridX = $editorX - self::CONTAINER_PADDING;
            $gridY = $editorY - self::CONTAINER_PADDING;
            
            // Convert from grid pixel coordinates to canvas coordinates (before scaling)
            // minCol/minRow represent the leftmost/topmost occupied tiles
            // Each tile is (TILE_WIDTH + TILE_GAP) wide and (TILE_HEIGHT + TILE_GAP) tall
            $canvasXBeforeScale = $gridX - ($minCol * (self::TILE_WIDTH + self::TILE_GAP));
            $canvasYBeforeScale = $gridY - ($minRow * (self::TILE_HEIGHT + self::TILE_GAP));
            
            // Scale to canvas pixel coordinates (canvas is scaled by SCALE factor)
            $canvasCenterX = $canvasXBeforeScale * $scaleFactor;
            $canvasCenterY = $canvasYBeforeScale * $scaleFactor;
            
            Log::info('PreviewRenderer text coordinate transformation', [
                'text' => substr($text, 0, 20),
                'editor_x' => $editorX,
                'editor_y' => $editorY,
                'grid_x' => $gridX,
                'grid_y' => $gridY,
                'min_col' => $minCol,
                'min_row' => $minRow,
                'canvas_x_before_scale' => $canvasXBeforeScale,
                'canvas_y_before_scale' => $canvasYBeforeScale,
                'canvas_center_x' => $canvasCenterX,
                'canvas_center_y' => $canvasCenterY,
                'scale_factor' => $scaleFactor,
            ]);

            // CRITICAL UNDERSTANDING:
            // The browser stores (left, top) as the element's top-left corner position
            // With CSS transform: translate(-50%, -50%), the browser:
            // 1. Gets the element's bounding box via getBoundingClientRect()
            // 2. Shifts it by -50% of width and -50% of height
            // 3. This places the VISUAL CENTER at (left, top)
            //
            // So the stored (x, y) IS the visual center position.
            // We need to position our text so its bounding box center matches this.
            
            // Calculate text bounding box using imagettfbbox
            // This gives us the font's bounding box relative to the baseline
            [$textWidth, $textHeight, $minX, $maxX, $minY, $maxY] = $this->calculateTextBoundingBox($fontSize, $rotation, $fontPath, $text);

            // ROOT CAUSE ANALYSIS:
            // The browser stores (left, top) as the element's CSS position
            // With transform: translate(-50%, -50%), the browser:
            // 1. Calculates getBoundingClientRect() which returns the axis-aligned bounding box
            //    of the element AFTER all transforms, including:
            //    - The text's actual rendered bounding box
            //    - Element padding (5px top/bottom, 10px left/right) 
            //    - The rotation transform
            // 2. The translate(-50%, -50%) shifts the element by -50% of its bounding box width/height
            // 3. The VISUAL CENTER of the element (including padding) ends up at (left, top)
            //
            // CRITICAL INSIGHT: Since padding is symmetric (5px top/bottom, 10px left/right),
            // the center of the element (text + padding) is the SAME as the center of just the text.
            // So the stored (x, y) IS the visual center of the text itself.
            //
            // imagettfbbox() gives us the font's bounding box for rotated text
            // It should match the browser's text bounding box (without padding)
            // The difference might be due to:
            // - Font rendering differences (hinting, subpixel rendering)
            // - Browser text metrics vs GD font metrics  
            // - Rotated text bounding box calculation differences
            //
            // The stored (x, y) IS the visual center of the text (after translate(-50%, -50%))
            // We position our text so its bounding box center matches this exactly
            
            // Target center position (stored position = visual center of text)
            $targetCenterX = $canvasCenterX;
            $targetCenterY = $canvasCenterY;

            // Calculate the text's bounding box center from imagettfbbox
            // This is the geometric center of the font's bounding box (rotated)
            $bboxCenterX = ($minX + $maxX) / 2;
            $bboxCenterY = ($minY + $maxY) / 2;
            
            // Position the text's bounding box center at the target center
            // This matches what the browser does: center of text bbox = stored (x, y)
            // imagettftext expects baseline position, so we convert:
            // baselineX = targetCenterX - bboxCenterX
            // baselineY = targetCenterY - bboxCenterY
            $drawX = $targetCenterX - $bboxCenterX;
            $drawY = $targetCenterY - $bboxCenterY;
            
            Log::info('PreviewRenderer text final positioning', [
                'text' => substr($text, 0, 20),
                'target_center_x' => $targetCenterX,
                'target_center_y' => $targetCenterY,
                'bbox_center_x' => $bboxCenterX,
                'bbox_center_y' => $bboxCenterY,
                'bbox_min_x' => $minX,
                'bbox_max_x' => $maxX,
                'bbox_min_y' => $minY,
                'bbox_max_y' => $maxY,
                'draw_x' => $drawX,
                'draw_y' => $drawY,
                'font_size' => $fontSize,
                'rotation' => $rotation,
            ]);

            imagettftext($textCanvas, $fontSize, -$rotation, intval($drawX), intval($drawY), $color, $fontPath, $text);
        }

        $this->applyMaskToCanvas($textCanvas, $maskCanvas, $canvasWidth, $canvasHeight, $textTransparent);

        imagealphablending($canvas, true);
        imagecopy($canvas, $textCanvas, 0, 0, 0, 0, $canvasWidth, $canvasHeight);
        imagealphablending($canvas, false);
        imagedestroy($textCanvas);
    }

    private function allocateHex($canvas, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocatealpha($canvas, $r, $g, $b, 0);
    }

    private function parseTranslateValue($value, float $scaleFactor, float $referenceSize): float
    {
        if (is_string($value)) {
            $value = trim($value);
            if (str_contains($value, '%')) {
                $val = floatval(str_replace('%', '', $value));
                return ($val / 100) * $referenceSize;
            }
            if (str_ends_with($value, 'px')) {
                return floatval(str_replace('px', '', $value)) * $scaleFactor;
            }
        }
        return floatval($value) * $scaleFactor;
    }

    private function resolveImagePath(array $tile): ?string
    {
        $path = $tile['image_edited'] ?? null;
        if (empty($path) || $path === 'null') {
            $path = $tile['image'] ?? null;
        }
        if (empty($path)) {
            return null;
        }
        $full = storage_path('app/public/' . ltrim($path, '/'));
        return File::exists($full) ? $full : null;
    }

    private function parseBlockSize(?string $margin): array
    {
        $margin = trim((string) $margin, '"\'');
        if ($margin === '' || $margin === 'null') {
            return [1, 1];
        }
        $parts = explode('|', $margin);
        if (count($parts) !== 2) {
            return [1, 1];
        }
        return [$this->totalCount(intval($parts[0])), $this->totalCount(intval($parts[1]))];
    }

    private function parsePositionFromStyle(string $style): array
    {
        $left = 0;
        $top = 0;
        if (preg_match('/left:\s*(\d+)px/', $style, $matches)) {
            $left = intval($matches[1]);
        }
        if (preg_match('/top:\s*(\d+)px/', $style, $matches)) {
            $top = intval($matches[1]);
        }
        $col = intval(round($left / (self::TILE_WIDTH + self::TILE_GAP)));
        $row = intval(round($top / (self::TILE_HEIGHT + self::TILE_GAP)));
        return [$col, $row];
    }

    private function parseActualDimensionsFromStyle(string $style): array
    {
        $width = self::TILE_WIDTH;
        $height = self::TILE_HEIGHT;
        if (preg_match('/width:\s*(\d+(?:\.\d+)?)px/', $style, $matches)) {
            $width = intval($matches[1]);
        }
        if (preg_match('/height:\s*(\d+(?:\.\d+)?)px/', $style, $matches)) {
            $height = intval($matches[1]);
        }
        return [
            'width' => max($width, self::TILE_WIDTH),
            'height' => max($height, self::TILE_HEIGHT),
        ];
    }

    private function totalCount(int $count): int
    {
        return match ($count) {
            0, 1 => 1,
            2 => 2,
            3 => 3,
            4 => 3,
            6 => 4,
            8 => 5,
            10 => 6,
            12 => 7,
            14 => 8,
            16 => 9,
            18 => 10,
            20 => 11,
            default => 1,
        };
    }

    private function parseTextOverlays(?string $json): array
    {
        if (empty($json)) {
            return [];
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }
        $result = [];
        foreach ($decoded as $overlay) {
            if (!is_array($overlay)) {
                $overlay = (array) $overlay;
            }
            $styles = $overlay['styles'] ?? '';
            Log::info('PreviewRenderer raw text styles', ['styles' => $styles]);
            $position = $this->parseCssPosition($styles);
            $fontProps = $this->parseCssFont($styles);
            $result[] = [
                'text' => $overlay['text'] ?? '',
                'x' => $position['x'],
                'y' => $position['y'],
                'font_size' => $fontProps['font_size'],
                'color' => $fontProps['color'],
                'font_family' => $fontProps['font_family'],
                'rotation' => $fontProps['rotation'],
                'translate_x' => $fontProps['translate_x'],
                'translate_y' => $fontProps['translate_y'],
            ];
        }
        return $result;
    }

    private function applyMaskToCanvas($canvas, $maskCanvas, int $width, int $height, int $transparentColor): void
    {
        imagealphablending($canvas, false);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $maskAlpha = (imagecolorat($maskCanvas, $x, $y) & 0x7F000000) >> 24;
                if ($maskAlpha === 127) {
                    imagesetpixel($canvas, $x, $y, $transparentColor);
                }
            }
        }
        imagealphablending($canvas, true);
    }

    private function calculateTextBoundingBox(int $fontSize, float $rotation, string $fontPath, string $text): array
    {
        $angle = -$rotation;
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);
        
        if ($bbox === false) {
            // Fallback if bbox calculation fails
            Log::warning('imagettfbbox failed', ['fontSize' => $fontSize, 'rotation' => $rotation, 'text' => substr($text, 0, 20)]);
            return [0, 0, 0, 0, 0, 0];
        }
        
        // imagettfbbox returns 8 coordinates for the 4 corners of the rotated text:
        // [0,1] = lower-left corner (x,y) relative to baseline
        // [2,3] = lower-right corner (x,y) relative to baseline
        // [4,5] = upper-right corner (x,y) relative to baseline
        // [6,7] = upper-left corner (x,y) relative to baseline
        // All coordinates are relative to the baseline (y=0 at baseline)
        // For rotated text, these corners form the axis-aligned bounding box
        
        $xs = [$bbox[0], $bbox[2], $bbox[4], $bbox[6]];
        $ys = [$bbox[1], $bbox[3], $bbox[5], $bbox[7]];
        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);
        $width = $maxX - $minX;
        $height = $maxY - $minY;
        
        // The bounding box center relative to baseline:
        // X center: (minX + maxX) / 2
        // Y center: (minY + maxY) / 2
        // Note: minY is negative (above baseline), maxY is positive (below baseline)
        // This center point should match the browser's getBoundingClientRect() center
        // when the text is rendered with the same font, size, and rotation
        
        return [$width, $height, $minX, $maxX, $minY, $maxY];
    }

    private function parseCssPosition(string $styles): array
    {
        $position = ['x' => 0.0, 'y' => 0.0];

        if (preg_match('/top:\s*calc\(([^)]+)\)/', $styles, $matches)) {
            $position['y'] = $this->evaluateLengthExpression($matches[1]);
        } elseif (preg_match('/top:\s*(-?\d+(?:\.\d+)?)px/', $styles, $matches)) {
            $position['y'] = (float) $matches[1];
        }

        if (preg_match('/left:\s*calc\(([^)]+)\)/', $styles, $matches)) {
            $position['x'] = $this->evaluateLengthExpression($matches[1]);
        } elseif (preg_match('/left:\s*(-?\d+(?:\.\d+)?)px/', $styles, $matches)) {
            $position['x'] = (float) $matches[1];
        }

        return $position;
    }

    private function parseCssFont(string $styles): array
    {
        $font = [
            'font_size' => 16,
            'color' => '#000000',
            'font_family' => 'Arial',
            'rotation' => 0,
            'translate_x' => 0,
            'translate_y' => 0,
        ];
        if (preg_match('/font-size:\s*(\d+)px/', $styles, $matches)) {
            $font['font_size'] = intval($matches[1]);
        }
        if (preg_match('/transform:[^;]*rotate\(([^)]+)\)/', $styles, $matches)) {
            $font['rotation'] = floatval(str_replace('deg', '', $matches[1]));
        }
        if (preg_match('/transform:[^;]*translate\(([^)]+)\)/', $styles, $matches)) {
            $values = array_map('trim', explode(',', $matches[1]));
            if (count($values) >= 2) {
                $font['translate_x'] = $values[0];
                $font['translate_y'] = $values[1];
            }
        }
        if (preg_match('/color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $styles, $matches)) {
            $font['color'] = sprintf('#%02x%02x%02x', intval($matches[1]), intval($matches[2]), intval($matches[3]));
        } elseif (preg_match('/color:\s*(#[0-9a-fA-F]{6})/', $styles, $matches)) {
            $font['color'] = $matches[1];
        }
        if (preg_match('/font-family:\s*([^;]+)/', $styles, $matches)) {
            $family = trim(explode(',', $matches[1])[0], " '\"");
            $font['font_family'] = $family;
        }
        return $font;
    }

    private function evaluateLengthExpression(string $expression): float
    {
        $clean = str_replace(['px', ' '], '', $expression);
        preg_match_all('/[+\-]?\d+(?:\.\d+)?/', $clean, $matches);
        if (empty($matches[0])) {
            return 0.0;
        }
        $sum = 0.0;
        foreach ($matches[0] as $number) {
            $sum += (float) $number;
        }
        return $sum;
    }

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

        $fontFiles = $fontMappings[$fontFamily] ?? [
            str_replace(' ', '', strtolower($fontFamily)) . '.ttf',
            str_replace(' ', '', $fontFamily) . '.ttf',
            strtolower($fontFamily) . '.ttf',
            $fontFamily . '.ttf',
            strtoupper(str_replace(' ', '', $fontFamily)) . '.TTF',
            str_replace(' ', '', strtolower($fontFamily)) . '.otf',
            $fontFamily . '.otf',
        ];

        foreach ($fontDirectories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            foreach ($fontFiles as $fontFile) {
                $path = $directory . $fontFile;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }
}





