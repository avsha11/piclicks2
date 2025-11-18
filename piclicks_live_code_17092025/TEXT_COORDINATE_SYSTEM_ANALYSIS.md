# Text Coordinate System Analysis

## Current Implementation

### 1. Text Container Borders (Bounding Box)

The text bounding box is calculated using `imagettfbbox()` which returns 8 coordinates:
- `$bbox[0]` = lower-left X (relative to baseline)
- `$bbox[1]` = lower-left Y (relative to baseline)
- `$bbox[2]` = lower-right X
- `$bbox[3]` = lower-right Y
- `$bbox[4]` = upper-right X
- `$bbox[5]` = upper-right Y
- `$bbox[6]` = upper-left X
- `$bbox[7]` = upper-left Y

**Important**: These coordinates are relative to the BASELINE point (0,0), NOT absolute canvas coordinates.

### 2. Zero Point for Coordinates

**In Editor (CSS)**:
- Zero point: Top-left of the collage container
- Text position: `left: Xpx, top: Ypx` (CSS positioning)
- Transform: `translate(-50%, -50%)` shifts text by half its width/height
- **Effective center**: `(X, Y)` after transform

**In PrintFileService (GD)**:
- Zero point: Top-left of the block canvas (including bleed)
- Text center: `centerX = (editorX * scaleX) + bleedPx + translateX`
- Baseline: Calculated from bounding box center offset

### 3. Current Calculation Flow

```
Editor → CollageServices → PrintFileService
```

**Step 1: Editor stores**
- `x` = CSS `left` position
- `y` = CSS `top` position  
- `translate_x` = "-50%" (typically)
- `translate_y` = "-50%" (typically)

**Step 2: CollageServices.getTextOverlaysForTile()**
```php
// Estimate text size in editor pixels
$textWidth = strlen($text) * $fontSize * 0.6;  // Rough estimate
$textHeight = $fontSize;

// Convert translate percentages to pixels
$translateXPx = convertTranslateToPixels($translateX, $textWidth, $fontSize);
$translateYPx = convertTranslateToPixels($translateY, $textHeight, $fontSize);

// Calculate center (accounting for translate)
$centerX = $textX + $translateXPx + ($textWidth / 2);
$centerY = $textY + $translateYPx + ($textHeight / 2);

// Make tile-relative
$adjustedTextOverlay['x'] = $centerX - $tileLeft;  // Center X relative to tile
$adjustedTextOverlay['y'] = $centerY - $tileTop;   // Center Y relative to tile
$adjustedTextOverlay['translate_x'] = 0;  // Already applied
$adjustedTextOverlay['translate_y'] = 0;  // Already applied
```

**Step 3: PrintFileService.renderText()**
```php
// Get center from tile-relative coordinates
$centerX = (editorX * scaleX) + bleedPx;
$centerY = (editorY * scaleY) + bleedPx;

// Measure text at print scale (unrotated)
$bbox = imagettfbbox($gdFontSize, 0, $fontPath, $text);
$textWidthPx = abs($bbox[4] - $bbox[0]);
$textHeightPx = abs($bbox[5] - $bbox[1]);

// Apply translate (but translate_x/y are already 0 from CollageServices!)
$translateXPx = convertCssTranslateToPixels($translateX, $textWidthPx, $printFontSizePx);
$translateYPx = convertCssTranslateToPixels($translateY, $textHeightPx, $printFontSizePx);
$centerX += $translateXPx;  // This does nothing if translate is 0
$centerY += $translateYPx;  // This does nothing if translate is 0

// Calculate rotated bounding box
$bboxRotated = imagettfbbox($gdFontSize, $gdRotationDegrees, $fontPath, $text);
$minX = min($bboxRotated[0..7]);
$maxX = max($bboxRotated[0..7]);
$minY = min($bboxRotated[1,3,5,7]);
$maxY = max($bboxRotated[1,3,5,7]);
$bboxCenterX = ($minX + $maxX) / 2;
$bboxCenterY = ($minY + $maxY) / 2;

// Calculate baseline position to center the text
$baselineX = $centerX - $bboxCenterX;
$baselineY = $centerY - $bboxCenterY;

// Draw text
imagettftext($canvas, $gdFontSize, $gdRotationDegrees, $baselineX, $baselineY, ...);
```

## Problems Identified

### Problem 1: Bounding Box Coordinate System
`imagettfbbox()` returns coordinates relative to the BASELINE point (where text would be drawn at 0,0). The bounding box is NOT a rectangle in canvas space - it's the text's bounding box in its own coordinate system.

### Problem 2: Text Size Estimation Mismatch
In `CollageServices`, we estimate text size using:
```php
$textWidth = strlen($text) * $fontSize * 0.6;  // Rough estimate
$textHeight = $fontSize;
```

But in `PrintFileService`, we measure the actual bounding box:
```php
$bbox = imagettfbbox($gdFontSize, 0, $fontPath, $text);
$textWidthPx = abs($bbox[4] - $bbox[0]);
$textHeightPx = abs($bbox[5] - $bbox[1]);
```

**These don't match!** Different fonts have different character widths, and the estimate is just a guess.

### Problem 3: Font-Specific Metrics
Different fonts have different:
- Character widths (monospace vs proportional)
- Ascenders/descenders (affects height)
- Baseline position
- Kerning (affects total width)

The estimate `strlen($text) * $fontSize * 0.6` is a generic approximation that doesn't account for font-specific metrics.

### Problem 4: Coordinate System Mismatch
The editor's `(x, y)` represents the CSS `left/top` position, which after `translate(-50%, -50%)` becomes the center. But we're calculating the center in `CollageServices` using an estimated bounding box, then passing it to `PrintFileService` which measures the actual bounding box. If the estimate is wrong, the center will be wrong.

## Solution

We need to:
1. Use the ACTUAL bounding box from `imagettfbbox()` in both places, or
2. Pass the original CSS position and let PrintFileService calculate everything, or
3. Ensure the estimate in CollageServices matches the actual measurement in PrintFileService

The best approach: **Measure the actual bounding box in CollageServices** using the same font and size that will be used in PrintFileService, so the center calculation is accurate.

