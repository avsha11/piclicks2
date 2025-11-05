# Text Overlay Print Size Doubled - Applied

## Date: October 29, 2025

## Changes Made

### File: `piclicks_live_code_17092025/app/Services/PrintFileService.php`

All changes made to the `renderText()` method (lines 404-436):

#### 1. Font Size Doubled (line 408)
```php
// BEFORE:
$printFontSize = intval($editorFontSize * $fontScaleFactor);

// AFTER:
$printFontSize = intval($editorFontSize * $fontScaleFactor * 2);
```

**Effect**: All text in print files will now be exactly 2x larger than before.

---

#### 2. Position Adjustment Variables Added (lines 410-413)
```php
// Position adjustments (can be fine-tuned after testing)
// Positive values move text right/down, negative values move left/up
$positionAdjustX = 0;  // Horizontal adjustment in print pixels
$positionAdjustY = 0;  // Vertical adjustment in print pixels
```

**Purpose**: Allows easy fine-tuning of text position without affecting scaling logic.

---

#### 3. Position Calculation Updated (lines 416-417)
```php
// BEFORE:
$printX = intval($editorX * $scaleFactorX) + $this->bleedPx;
$printY = intval($editorY * $scaleFactorY) + $this->bleedPx;

// AFTER:
$printX = intval($editorX * $scaleFactorX) + $this->bleedPx + $positionAdjustX;
$printY = intval($editorY * $scaleFactorY) + $this->bleedPx + $positionAdjustY;
```

**Effect**: Position now includes adjustment offsets for fine-tuning.

---

#### 4. Logging Enhanced (lines 419-425)
```php
Log::info("Font size scaling in PrintFileService", [
    'tool_js_font_px' => $editorFontSize,
    'scale_factor' => round($fontScaleFactor, 3),
    'multiplier' => 2,                              // NEW
    'calculated_print_font' => $printFontSize,
    'will_cap' => $printFontSize > 4000 ? 'YES' : 'NO'  // Updated threshold
]);
```

**Effect**: Logs now show the 2x multiplier and updated cap threshold.

---

#### 5. Font Size Cap Doubled (lines 427-436)
```php
// BEFORE:
if ($printFontSize > 2000) {
    ...
    $printFontSize = 2000;
}

// AFTER:
if ($printFontSize > 4000) {
    Log::warning("Font size capped to prevent memory exhaustion", [
        'calculated' => $printFontSize,
        'capped_to' => 4000,
        'original_tool_js_size' => $editorFontSize
    ]);
    $printFontSize = 4000;
}
```

**Effect**: Allows fonts up to 4000px before capping (was 2000px).

---

## Testing Instructions

### Step 1: Generate Print Files
1. Open a collage in the editor
2. Add or modify text overlays
3. Save the collage
4. Click "Preview" to generate print files
5. Download the ZIP file from Admin → Orders

### Step 2: Check Text Size
- Text should now be **2x larger** than in previous print files
- Compare with editor to verify proportions

### Step 3: Check Text Position (if needed)
If text position is incorrect, adjust the offset values:

**To move text RIGHT**: Set `$positionAdjustX = 100;` (or desired pixels)  
**To move text LEFT**: Set `$positionAdjustX = -100;`  
**To move text DOWN**: Set `$positionAdjustY = 100;`  
**To move text UP**: Set `$positionAdjustY = -100;`

**Location to edit**: Line 412-413 in `PrintFileService.php`

### Step 4: Iterate
After adjusting position values:
1. Save the file
2. Regenerate print files (Preview again)
3. Download and verify
4. Repeat until position is correct

---

## Expected Results

✅ **Text is 2x larger** than before  
✅ **Text quality** remains sharp (up to 4000px font size)  
✅ **Text rotation** still works correctly  
✅ **Multi-tile text** still splits correctly  
✅ **Position can be fine-tuned** using adjustment variables

---

## Adjustment Examples

### Example 1: Text slightly too far left
```php
$positionAdjustX = 50;  // Move 50px right
$positionAdjustY = 0;   // Keep Y unchanged
```

### Example 2: Text slightly too high
```php
$positionAdjustX = 0;   // Keep X unchanged
$positionAdjustY = 30;  // Move 30px down
```

### Example 3: Text needs both adjustments
```php
$positionAdjustX = -20; // Move 20px left
$positionAdjustY = 40;  // Move 40px down
```

---

## Troubleshooting

### Text is too large
- Cannot reduce below 2x without changing the multiplier
- To reduce: Change line 408 from `* 2` to `* 1.5` or desired multiplier

### Text position is way off
- Check if `$positionAdjustX` and `$positionAdjustY` values are reasonable
- Values are in **print pixels** (tile is ~1697px wide)
- Start with small adjustments (±50 to ±100)

### Text is cut off
- Verify font size isn't hitting the 4000px cap (check logs)
- If capped, text may extend beyond tile boundaries

---

## Technical Notes

- **Scale Factor**: ~18.6x from editor (91px) to print (1697px)
- **Multiplier**: Additional 2x applied on top of scale factor
- **Total scaling**: ~37.2x from editor pixels to print pixels
- **Position units**: All adjustments in print pixels (1697px wide tile)
- **Bleed offset**: Still applied correctly (24px = 2mm at 300 DPI)

---

## Files Modified

1. `piclicks_live_code_17092025/app/Services/PrintFileService.php`
   - Method: `renderText()` (lines 404-436)

---

## Validation

✅ No linter errors introduced  
✅ All existing rotation/translate logic preserved  
✅ Logging enhanced for debugging  
✅ Position adjustment mechanism added








