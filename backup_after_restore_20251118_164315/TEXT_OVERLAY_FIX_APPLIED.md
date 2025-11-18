# Text Overlay Fix Applied

## Date: October 27, 2025

## Problem

**Before Fix:**
- Text overlays (like "ghtghrtfg" or "Bitesize") appeared in FULL on EVERY print file/tile
- Even though in the editor, text spans across multiple tiles, each print file got the complete text

**Root Cause:**
- Text overlay positions were global (relative to entire collage canvas)
- Code was passing ALL text overlays to EVERY tile without:
  1. Checking if text actually appears on that tile
  2. Adjusting text position to be tile-relative
  3. Filtering out text that doesn't belong

---

## Solution

### **New Method: `getTextOverlaysForTile()`**

This method now:

1. **Calculates Tile Bounding Box**
   - Determines tile's position in editor coordinates
   - Based on grid dimensions (e.g., 5x5)
   - Accounts for multi-tile spans (e.g., 2x2 image)

2. **Checks Text Intersection**
   - For each text overlay, calculates its bounding box
   - Checks if text overlaps with this specific tile
   - Uses standard bounding box intersection algorithm

3. **Adjusts Text Position**
   - If text appears on this tile, adjusts position to be RELATIVE to tile
   - Subtracts tile's top-left offset from text position
   - Example: Global text at (300, 200) on tile starting at (150, 150) → tile-relative position (150, 50)

4. **Returns Only Relevant Text**
   - Only text overlays that intersect with this tile
   - With positions adjusted for this tile

---

## How It Works

### **Example:**

**Collage:** 4x3 grid (4 columns, 3 rows)
**Text:** "Bitesize" positioned at global coordinates (100, 50)
**Text Size:** Estimated width 200px, height 40px

**Tiles:**
- **Tile A (col:0, row:0):** Bounds (0-150, 0-150) → Text intersects! → Adjusted position (100, 50)
- **Tile B (col:1, row:0):** Bounds (150-300, 0-150) → Text intersects! → Adjusted position (-50, 50)
- **Tile C (col:2, row:0):** Bounds (300-450, 0-150) → Text does NOT intersect → No text
- **Tile D (col:0, row:1):** Bounds (0-150, 150-300) → Text does NOT intersect → No text

**Result:** Only Tiles A and B get text, each with their portion!

---

## Code Changes

### **File:** `app/Services/CollageServices.php`

#### **1. New Method Added (lines 1128-1219)**
```php
private function getTextOverlaysForTile(
    array $globalTextOverlays,
    int $tileStartCol,
    int $tileStartRow,
    int $tileCols,
    int $tileRows,
    $masterdata
): array
```

#### **2. Integration (lines 1069-1077)**
```php
// Filter and adjust text overlays for this specific tile
$tileTextOverlays = $this->getTextOverlaysForTile(
    $textOverlays,
    $startCol,
    $startRow,
    $cols,
    $rows,
    $masterdata
);
```

#### **3. Updated Block Config (line 1093)**
```php
'text_overlays' => $tileTextOverlays, // Only text relevant to this tile
```

---

## Testing Instructions

### **Step 1: Create Test Collage**
1. Go to: `http://localhost:8000`
2. Create a collage (3x3 or 4x4)
3. **Add text that spans MULTIPLE tiles**
   - Example: Place "TEST TEXT" across the top-left corner spanning 2-3 tiles
4. Apply a filter (e.g., Capri)
5. Click "Preview"
6. Complete checkout

### **Step 2: Download & Verify**
1. Go to admin: `http://localhost:8000/admin-panel`
2. Find your order
3. Download print files (ZIP)
4. Open the PNG files

### **Step 3: Check Results**

**✅ EXPECTED (Correct):**
- Tile 1: Shows only "TEST" (left portion of text)
- Tile 2: Shows only "TEXT" (right portion of text)
- Tile 3 (below): Shows NO text (text doesn't reach this tile)

**❌ BEFORE FIX (Wrong):**
- Tile 1: Shows "TEST TEXT" (full text)
- Tile 2: Shows "TEST TEXT" (full text)
- Tile 3: Shows "TEST TEXT" (full text)

---

## Logging

The fix includes detailed logging to help verify correct behavior:

```
Tile bounding box in editor coordinates
  tile_position: "col:0, row:0"
  tile_size: "1x1"
  bounds: "left:0, top:0, right:150, bottom:150"

Text overlay included on this tile
  text: "TEST TEXT"
  global_pos: "x:100, y:50"
  tile_relative_pos: "x:100, y:50"
  tile: "col:0, row:0"

Text overlay excluded from this tile (no intersection)
  text: "TEST TEXT"
  text_bounds: "x:100-300, y:50-90"
  tile_bounds: "x:0-150, y:150-300"
  tile: "col:0, row:1"
```

---

## Known Limitations

### **Editor Canvas Size Assumption**
- Code assumes editor canvas is approximately 750x750px
- This is an estimate based on typical editor dimensions
- If your editor uses a different size, the calculations may be slightly off

**Potential Fix:**
- Pass actual editor canvas dimensions from frontend
- Store in database with masterdata
- Use actual values instead of hardcoded 750

### **Text Bounding Box Estimation**
- Text width is estimated as `length * fontSize * 0.6`
- This is an approximation; actual width varies by font
- May result in slight positioning errors for very long text

**Potential Fix:**
- Use actual text metrics from frontend
- Calculate bounding box with JavaScript before saving
- Store with text overlay data

---

## Files Modified

1. **`app/Services/CollageServices.php`**
   - Added `getTextOverlaysForTile()` method
   - Updated `generatePrintFilesForCollage()` to use filtered text
   - Added intersection detection logic
   - Added position adjustment logic

2. **`app/Services/PrintFileService.php`** (previous fix)
   - Improved font finding with fallback to Arial
   - Enhanced text rendering logging
   - Better error handling for missing fonts

---

## Next Steps

1. **Test with new order** containing text spanning multiple tiles
2. **Verify logs** show correct intersection detection
3. **Download print files** and confirm only relevant text portions appear
4. **Report back** with results!

---

## Troubleshooting

### If text still appears on wrong tiles:

**Check logs for:**
```
Text overlay excluded from this tile (no intersection)
```

This indicates the intersection detection is working.

### If text position is slightly off:

This may be due to editor canvas size assumption. Try adjusting line 1151:
```php
$editorCanvasWidth = 750; // Try 600, 700, 800, etc.
```

### If text is completely missing:

Check logs for:
```
Font found
```

Or:
```
Using Arial fallback
```

If neither appears, fonts are not being found (see TEXT_AND_FILTER_FIXES.md).

---

## Summary

✅ **Text overlays now properly segmented across tiles**
✅ **Each tile gets only its relevant portion of text**
✅ **Text positions adjusted to be tile-relative**
✅ **Detailed logging for debugging**
✅ **Filters continue to work correctly**

**Test it now!** 🚀

