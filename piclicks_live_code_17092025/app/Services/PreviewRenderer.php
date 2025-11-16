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
    private const TEXT_SIZE_ADJUST = 0.74; // fine-tuned GD vs CSS size
    private const TEXT_ADJUST_DX = -6;     // small empirical offset in editor px (left)
    private const TEXT_ADJUST_DY = -6;     // nudge up a bit to match editor

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

        $canvasWidth = $colsCount * $tileWidthPx + ($colsCount - 1) * $gapPx;
        $canvasHeight = $rowsCount * $tileHeightPx + ($rowsCount - 1) * $gapPx;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        $maskCanvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
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

        $relativePath = 'temp/preview_' . ($master['unique_id'] ?? uniqid()) . '_' . time() . '.png';
        $fullPath = storage_path('app/public/' . $relativePath);

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

        $contentWidth = $cols * $tileWidth;
        $contentHeight = $rows * $tileHeight;

        // object-fit: cover scaling based on editor dimensions
        $scaleFactorX = $contentWidth / ($editorWidth * self::SCALE);
        $scaleFactorY = $contentHeight / ($editorHeight * self::SCALE);
        // fallback to using content width/height if editor dims not reliable
        $scaleFactor = max($contentWidth / $srcW, $contentHeight / $srcH);
        $scaledW = intval($srcW * $scaleFactor);
        $scaledH = intval($srcH * $scaleFactor);

        // Apply zoom
        $zoomStr = $blockInfo['settings']['zoom'] ?? '0';
        $zoomParts = explode('|', $zoomStr);
        $zoomX = floatval($zoomParts[0] ?? 0);
        $zoomY = floatval($zoomParts[1] ?? $zoomX);
        if ($zoomX > 0 || $zoomY > 0) {
            $scaledW = intval($scaledW * (1 + $zoomX));
            $scaledH = intval($scaledH * (1 + $zoomY));
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

                $srcX = intval(($tileWidth * $c) + ($scaledW - $contentWidth) / 2);
                $srcY = intval(($tileHeight * $r) + ($scaledH - $contentHeight) / 2);

                imagecopy($tileCanvas, $scaledImage, 0, 0, $srcX, $srcY, $tileWidth, $tileHeight);

                // Apply rounded corners
                $this->applyRoundedCorners($tileCanvas, intval(self::CORNER_RADIUS * self::SCALE));
                $this->drawRoundedRectangle($maskCanvas, $destX, $destY, $tileWidth, $tileHeight, intval(self::CORNER_RADIUS * self::SCALE), $maskOpaqueColor, true);

                imagecopy($canvas, $tileCanvas, $destX, $destY, 0, 0, $tileWidth, $tileHeight);
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

        imagecopy($canvas, $frameImg, $x, $y, 0, 0, $w, $h);
        imagedestroy($frameImg);
    }

    private function drawRoundedRectangle($canvas, int $x, int $y, int $w, int $h, int $r, int $color, bool $fill = false): void
    {
        $width = $fill ? $w : $w - 1;
        $height = $fill ? $h : $h - 1;
        imagefilledrectangle($canvas, $x + $r, $y, $x + $width - $r, $y + $height, $color);
        imagefilledrectangle($canvas, $x, $y + $r, $x + $width, $y + $height - $r, $color);
        imagefilledellipse($canvas, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $width - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $r, $y + $height - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $width - $r, $y + $height - $r, $r * 2, $r * 2, $color);
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

            // Adjust origin: subtract padding and occupied tile offset
            $relativeX = ($overlay['x'] ?? 0)
                - self::CONTAINER_PADDING
                - ($minCol * (self::TILE_WIDTH + self::TILE_GAP));
            $relativeY = ($overlay['y'] ?? 0)
                - self::CONTAINER_PADDING
                - ($minRow * (self::TILE_HEIGHT + self::TILE_GAP));

            $x = $relativeX * $scaleFactor;
            $y = $relativeY * $scaleFactor;

            [$textWidth, $textHeight, $minX, $maxX, $minY, $maxY] = $this->calculateTextBoundingBox($fontSize, $rotation, $fontPath, $text);

            $translateX = $this->parseTranslateValue($overlay['translate_x'] ?? 0, $scaleFactor, $textWidth);
            $translateY = $this->parseTranslateValue($overlay['translate_y'] ?? 0, $scaleFactor, $textHeight);

            $centerX = $x + ($textWidth / 2) + $translateX + (self::TEXT_ADJUST_DX * $scaleFactor);
            $centerY = $y + ($textHeight / 2) + $translateY + (self::TEXT_ADJUST_DY * $scaleFactor);

            // Convert center to baseline point for imagettftext
            $drawX = $centerX - ($textWidth / 2) - $minX;
            $drawY = $centerY + ($textHeight / 2) - $maxY;

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
        $xs = [$bbox[0], $bbox[2], $bbox[4], $bbox[6]];
        $ys = [$bbox[1], $bbox[3], $bbox[5], $bbox[7]];
        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);
        $width = $maxX - $minX;
        $height = $maxY - $minY;
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





