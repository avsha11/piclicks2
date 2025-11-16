# Image Scale Fix and Text Debugging - Implementation Summary

## Date
November 6, 2025

## Problem Identified

### Image Framing Issue
The print files showed images that were:
- Zoomed in too much (30-40% more than editor)
- Positioned incorrectly for stretched images (especially horizontal stretches)
- Frame overlaying image instead of containing it

**Root Cause**: The code was re-applying `object-fit: cover` logic at print time, instead of scaling the already-rendered editor output proportionally.

### Text Disappearance Issue
All text overlays were completely missing from print files.

**Status**: Cause unknown - extensive debugging logging added to diagnose.

## Changes Made

### 1. PrintFileService.php - Image Scaling Fix

**File**: `piclicks_live_code_17092025/app/Services/PrintFileService.php`  
**Method**: `renderImage()` (lines 255-325)

**What Changed**:
- **BEFORE**: Code was applying `object-fit: cover` directly to source image dimensions vs print dimensions
- **AFTER**: Code now:
  1. Calculates how the editor scaled the image (using editor dimensions)
  2. Applies zoom to editor-scaled dimensions
  3. Scales the result proportionally to print dimensions

**Key Logic**:
```php
// Calculate editor's object-fit:cover result
$editorAspect = $editorBlockW / $editorBlockH;
if ($srcAspect > $editorAspect) {
    $editorScaledH = $editorBlockH;
    $editorScaledW = $editorBlockH * $srcAspect;
} else {
    $editorScaledW = $editorBlockW;
    $editorScaledH = $editorBlockW / $srcAspect;
}

// Apply zoom to editor dimensions
if ($zoomX > 0 || $zoomY > 0) {
    $editorScaledW = $editorScaledW * (1 + $zoomX);
    $editorScaledH = $editorScaledH * (1 + $zoomY);
}

// Scale from editor to print
$scaleFactorX = $blockClearW / $editorBlockW;
$scaleFactorY = $blockClearH / $editorBlockH;
$printScaledW = intval($editorScaledW * $scaleFactorX);
$printScaledH = intval($editorScaledH * $scaleFactorY);
```

This ensures the print files show EXACTLY what the editor shows, just at higher resolution.

### 2. PrintFileService.php - Text Debugging

**Method**: `renderText()` (lines 407-554)

**Debugging Features Added**:
1. **Canvas dimension logging** - shows full canvas size
2. **Scale factor logging** - X, Y, and average scale factors
3. **Position calculation logging** - tracks every transformation step:
   - Editor coordinates
   - Scale to print coordinates
   - Translate transform application
   - Bleed offset addition
   - Final position
4. **Font path resolution logging** - shows what font file was found (or not)
5. **Visual debug markers** - **MAGENTA DOTS** drawn at every text position
6. **Bounds checking** - detects if text is rendering outside canvas
7. **Error indicators** - red rectangles if font is missing

**Key Addition**:
```php
// Draw debug marker ALWAYS (colored dot to show where text should be)
$markerColor = imagecolorallocate($canvas, 255, 0, 255); // Magenta
imagefilledellipse($canvas, $printX, $printY, 20, 20, $markerColor);
```

### 3. CollageServices.php - Text Overlay Debugging

**File**: `piclicks_live_code_17092025/app/Services/CollageServices.php`

**Added Logging**:
1. Before `getTextOverlaysForTile()` - shows total overlays and tile info
2. After `getTextOverlaysForTile()` - shows how many overlays were included
3. Inside `getTextOverlaysForTile()` - detailed intersection checking for each overlay:
   - Global text position
   - Text bounding box
   - Tile bounding box
   - Whether they intersect
   - Calculated relative position

## Backups Created

- `app/Services/PrintFileService.php.backup_image_scale_fix`
- `app/Services/CollageServices.php.backup_image_scale_fix`

## Restore Script

**File**: `restore_image_scale_fix.ps1`

Run this if the fix causes issues:
```powershell
.\restore_image_scale_fix.ps1
```

## Testing Instructions

### 1. Restart Development Server (CRITICAL)
The server must be restarted for changes to take effect.

If using `php artisan serve`:
1. Press Ctrl+C in the terminal running the server
2. Run: `C:\xampp\php\php.exe artisan serve`

### 2. Clear Browser Cache
Hard refresh (Ctrl+Shift+R) before testing.

### 3. Generate New Print Files
1. Create a NEW collage (or edit existing)
2. Add some images (including stretched ones)
3. Add text overlays
4. Save and order the collage
5. Run queue worker: `C:\xampp\php\php.exe artisan queue:work --once`

### 4. Visual Inspection

**For Images**:
- Compare each print file to its corresponding tile in the editor screenshot
- Check if framing matches exactly
- Verify stretched images (especially 1x2, 2x1, 2x3) are correct
- Confirm color frames contain the image (not overlay it)

**For Text**:
- Look for **MAGENTA DOTS** (20px circles) - these show where text should render
- Check if text appears (any text at all)
- If no text but dots are visible: font path issue
- If dots are outside tile bounds: position calculation issue
- If no dots visible at all: text overlay not being passed to tile

### 5. Check Logs

The Laravel log will contain extensive debugging information:

**Location**: `piclicks_live_code_17092025/storage/logs/laravel.log`

**What to Look For**:

#### Image Rendering Logs:
```
Image rendered - proportional scaling from editor to print
- source_image: WxH
- editor_container: WxH
- editor_scaled_image: WxH
- print_container: WxH
- print_scaled_image: WxH
- scale_factors: X.XXX x Y.YYY
```

#### Text Rendering Logs:
```
=== TEXT RENDERING DEBUG START ===
TEXT OVERLAY #N - Position Calculation
TEXT OVERLAY #N - Font Path Resolution
TEXT OVERLAY #N - About to render
TEXT OVERLAY #N - RENDERED successfully
```

Or errors:
```
TEXT OVERLAY #N - FONT NOT FOUND!
TEXT OVERLAY #N - OUT OF CANVAS BOUNDS!
```

## Expected Outcomes

### Image Framing
- ✅ Print files should match editor visual output exactly
- ✅ Stretched images should have correct proportions
- ✅ No more "zoomed in" or "aimed lower" issues
- ✅ Frames should contain images, not overlay them

### Text Overlays
- 🔍 Magenta dots will show where text should be rendering
- 🔍 Logs will reveal:
  - If font files are found
  - If positions are calculated correctly
  - If text is rendering outside canvas
  - Exactly why text is not appearing

## Next Steps After Testing

1. **If images look correct**: Image fix successful! ✅
2. **If text still missing**: Review the detailed logs to identify root cause
3. **If issues persist**: Run `.\restore_image_scale_fix.ps1` to rollback

## Technical Notes

### Why This Fix Works

The editor uses `object-fit: cover` and `object-position: top left` to render images. This means:
1. The image is scaled to completely fill the container (maintaining aspect ratio)
2. The image is aligned to the top-left corner
3. Overflow is hidden (cropped)

The previous code tried to re-implement this logic at print time, but made a critical error: it calculated cover fitting based on the source image aspect ratio vs the PRINT area aspect ratio.

The correct approach is to recognize that the EDITOR has already done the cover fitting. We just need to scale up that result proportionally. This is what the new code does.

### Gap Handling

For stretched images (e.g., 2x1 spanning two tiles), the editor dimensions include the 2px gaps between tiles. This is correct because:
- The canvas in the editor INCLUDES the gaps
- The visual output shows the image stretched across tiles AND gaps
- The print files need to replicate this EXACT visual output
- Therefore, we include gaps in dimension calculations









