# Preview Image Framing Solution Summary

## Problem Statement

Preview images were not matching the editor's display:
1. Images appeared "zoomed in" at right and bottom borders (focal point: top-left)
2. Thin gray frame inside color frames (image not fully contained)
3. Broken continuity in stretched images (gaps between tile segments)

## Root Causes Identified

### Issue 1: Incorrect Target Area Calculation
- **Problem**: Images were scaled to full `contentWidth × contentHeight` instead of frame's inner boundary
- **Symptom**: Images extended beyond frame's visible area, appearing zoomed in
- **Location**: Scaling calculations used `$contentWidth`/`$contentHeight` instead of `$targetWidth`/`$targetHeight`

### Issue 2: Missing Gap Calculations
- **Problem**: Block canvas dimensions didn't account for gaps between tiles in stretched images
- **Symptom**: Gray frame inside color frame (4px gap for 2×1 stretched image)
- **Location**: `$contentWidth = $cols * $tileWidth` (missing `+ ($cols - 1) * $gap`)

### Issue 3: Incorrect Tile Extraction
- **Problem**: Tiles extracted from block canvas without accounting for gaps
- **Symptom**: Broken continuity in stretched images (woman's head split with gap)
- **Location**: `$srcX = $c * $tileWidth` (should be `$c * ($tileWidth + $gap)`)

## Solution Architecture

### Key Principle: Block-Level Rendering (Following PrintFileService Pattern)

1. **Create a single block canvas** for the entire stretched image (all tiles combined)
2. **Scale image to frame's inner boundary** (accounting for frame thickness)
3. **Render scaled image to block canvas** at frame inset position
4. **Extract tiles as simple grid copies** from the continuous block canvas
5. **No per-tile manipulation** - all scaling/positioning happens at block level

### Why This Works

- **Continuity**: One continuous image on one canvas = automatic continuity
- **Frame Containment**: Image scaled to fit frame's inner boundary, positioned at inset
- **No Gaps**: Block canvas size matches frame size (includes gaps between tiles)

## Implementation Details

### File: `app/Services/PreviewRenderer.php`

#### 1. Frame Thickness Calculation
```php
// Calculate frame thickness for image positioning (14px when frame exists, 0 when no frame)
$frameThicknessPx = $frameColorHex ? intval(self::FRAME_THICKNESS * self::SCALE) : 0;
```

#### 2. Content Dimensions (MUST Include Gaps)
```php
// Content dimensions MUST account for gaps between tiles in stretched images
// This ensures block canvas size matches the frame dimensions
$contentWidth = $cols * $tileWidth + ($cols - 1) * $gap;
$contentHeight = $rows * $tileHeight + ($rows - 1) * $gap;
```

#### 3. Target Area for Scaling (Frame's Inner Boundary)
```php
// Target area for image scaling: frame's inner boundary if frame exists
$targetWidth = $frameThicknessPx > 0 ? max(1, $contentWidth - ($frameThicknessPx * 2)) : $contentWidth;
$targetHeight = $frameThicknessPx > 0 ? max(1, $contentHeight - ($frameThicknessPx * 2)) : $contentHeight;
```

#### 4. Scaling to Target Area (Not Full Content)
```php
// Now scale from editor's viewport to preview's target area (inside frame if frame exists)
$scaleToTargetX = $targetWidth / $viewportW;
$scaleToTargetY = $targetHeight / $viewportH;
$scaleToTarget = max($scaleToTargetX, $scaleToTargetY); // object-fit: cover behavior
```

#### 5. Block Canvas Creation
```php
// Create a block canvas for the entire block (like PrintFileService does)
$blockCanvas = imagecreatetruecolor($contentWidth, $contentHeight);
// ... alpha setup ...

// Render scaled image to block canvas at frame inset position
$dstX = $frameThicknessPx; // 14px when frame exists, 0 when no frame
$dstY = $frameThicknessPx;
imagecopy($blockCanvas, $scaledImage, $dstX, $dstY, 0, 0, $scaledW, $scaledH);
```

#### 6. Tile Extraction (Account for Gaps)
```php
// Extract tiles from block canvas - account for gaps between tiles
$srcX = $c * ($tileWidth + $gap);
$srcY = $r * ($tileHeight + $gap);
imagecopy($tileCanvas, $blockCanvas, 0, 0, $srcX, $srcY, $tileWidth, $tileHeight);
```

## Critical Rules

1. **Block canvas size = Frame size** (includes gaps: `cols * tileWidth + (cols-1) * gap`)
2. **Target area = Frame's inner boundary** (content size minus frame thickness × 2)
3. **Image scaled to target area** (not full content area)
4. **Tile extraction accounts for gaps** (`$c * ($tileWidth + $gap)`)
5. **No per-tile manipulation** - all work done at block level

## Example Calculation

For a 2×1 stretched image with frame:
- `tileWidth = 182px`, `tileHeight = 160px`, `gap = 4px`
- `frameThicknessPx = 14px`

**Content dimensions:**
- `contentWidth = 2 * 182 + 1 * 4 = 368px` ✅ (includes gap)
- `contentHeight = 1 * 160 + 0 * 4 = 160px`

**Target area (frame's inner boundary):**
- `targetWidth = 368 - (14 * 2) = 340px`
- `targetHeight = 160 - (14 * 2) = 132px`

**Block canvas:**
- Size: `368 × 160px` (matches frame size)
- Image rendered at: `(14, 14)` with size scaled to cover `340 × 132px`

**Tile extraction:**
- Tile 0: `srcX = 0 * (182 + 4) = 0`
- Tile 1: `srcX = 1 * (182 + 4) = 186` ✅ (accounts for gap)

## What NOT to Do

❌ **Don't** scale to full content area when frame exists  
❌ **Don't** forget gaps in content dimensions  
❌ **Don't** extract tiles without accounting for gaps  
❌ **Don't** manipulate images per-tile (breaks continuity)  
❌ **Don't** use `imagecopyresampled` when copying already-scaled images

## Reference Implementation

The solution follows the same architectural pattern as `PrintFileService.php`:
- Block-level rendering
- Frame inset calculation
- Target area scaling
- Simple tile extraction

## Date
Solution implemented: 2025-01-26

