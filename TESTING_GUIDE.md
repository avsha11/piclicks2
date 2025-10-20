# Print File Fix - Testing Guide

## Quick Start

The print file generation has been completely rewritten to follow the SPEC.md and ALGORITHM.md specifications. This guide helps you test and verify the fix.

## What Changed?

### ✅ Fixed Issues
1. **Correct dimensions**: 143.7mm × 126.0mm clear, 147.7mm × 130.0mm with bleed
2. **Proper bleed**: 2mm on all sides
3. **Correct corner radius**: R10 for print files (was R8)
4. **Frame thickness**: 10mm in print files (visible 8mm after trim)
5. **Frame inner radius**: R2 (was missing)
6. **Render order**: Image → Filter → Text → Frame (was wrong)
7. **Stretched images**: Rendered as continuous blocks (was broken)

### ✅ Unchanged (Safe)
- Editor functionality
- Preview functionality
- Upload functionality
- Database schema
- Frontend JavaScript

## Testing Steps

### 1. Test Print File Generation

#### A. Single Tile (1×1)
1. Go to Admin → Orders
2. Select an order with a single photo tile
3. Click "Download Print Files" or similar action
4. **Expected**:
   - Single PNG file generated
   - Dimensions: 1742px × 1535px (147.7mm × 130.0mm at 300 DPI)
   - Corner radius visible
   - If frame applied: 10mm thick frame with 2mm inner corner radius

#### B. Stretched Image (2×2, 3×2, etc.)
1. Create a collage with a stretched image (multiple tiles)
2. Generate print files
3. **Expected**:
   - Multiple PNG files (one per tile in the span)
   - Each tile: 1742px × 1535px
   - Frame continuous across tiles (if applied)
   - Images align perfectly when assembled
   - No gaps or misalignments

#### C. With Frame
1. Create collage with black or white frame
2. Generate print files
3. **Expected**:
   - Frame thickness: 118px (10mm at 300 DPI)
   - Frame wraps entire block for stretched images
   - Inner corner radius: 24px (2mm at 300 DPI)

#### D. With Text
1. Add text overlay in editor
2. Generate print files
3. **Expected**:
   - Text appears on print files
   - Text positioned correctly
   - Text included in bleed area if near edges

#### E. With Filter
1. Apply a filter (noir, stark, scandi, capri, nordic, belveder)
2. Generate print files
3. **Expected**:
   - Filter applied to entire image
   - Filter consistent across stretched image tiles

### 2. Verify Editor Unchanged

1. **Open Editor**:
   - Navigate to collage editor
   - Should load normally

2. **Upload Images**:
   - Upload test images
   - Images should appear in library

3. **Position & Zoom**:
   - Drag images to tiles
   - Zoom and pan images
   - Should work as before

4. **Apply Frame**:
   - Add black or white frame
   - Preview should show frame
   - Frame should appear immediately

5. **Apply Filter**:
   - Apply various filters
   - Preview should update
   - Filters should look correct

6. **Add Text**:
   - Add text overlays
   - Position and style text
   - Text should display correctly

7. **Stretch Images**:
   - Create stretched images (2×2, etc.)
   - Should work as before
   - Preview should show correctly

### 3. Verify Preview Unchanged

1. **Open Preview**:
   - Click preview button
   - Preview should load

2. **Visual Check**:
   - Preview should match editor
   - Clear area (143.7mm × 126.0mm) displayed
   - Corner radius R8 visible
   - Frame preview correct (if applied)

3. **No Bleed in Preview**:
   - Preview should NOT show bleed
   - Only the clear area should be visible

## Verification Checklist

### Print Files
- [ ] Dimensions: 1742px × 1535px per tile (147.7mm × 130.0mm at 300 DPI)
- [ ] Corner radius: ~118px (10mm at 300 DPI)
- [ ] Bleed: ~24px (2mm) on all sides
- [ ] Frame thickness (if applied): ~118px (10mm)
- [ ] Frame inner radius (if applied): ~24px (2mm)
- [ ] Stretched images align perfectly across tiles
- [ ] Text renders correctly
- [ ] Filters apply correctly
- [ ] No white edges after virtual trim

### Editor (Must Be Unchanged)
- [ ] Editor loads successfully
- [ ] Images upload correctly
- [ ] Images can be positioned and zoomed
- [ ] Frames apply and preview correctly
- [ ] Filters apply and preview correctly
- [ ] Text overlays work correctly
- [ ] Stretched images can be created
- [ ] Preview shows correct appearance

## Pixel Calculations (at 300 DPI)

| Measurement | mm | Pixels @ 300 DPI |
|-------------|-----|------------------|
| Clear Width | 143.7 | 1694 |
| Clear Height | 126.0 | 1487 |
| Bleed | 2.0 | 24 |
| Print Width | 147.7 | 1742 |
| Print Height | 130.0 | 1535 |
| Clear Radius (R8) | 8.0 | 94 |
| Print Radius (R10) | 10.0 | 118 |
| Frame Print Thickness | 10.0 | 118 |
| Frame Inner Radius (R2) | 2.0 | 24 |

## Common Issues & Solutions

### Issue: Print files have wrong dimensions
**Check**: Verify the PrintFileService constants
**Expected**: 1742px × 1535px (147.7mm × 130.0mm at 300 DPI)

### Issue: Stretched images don't align
**Check**: Ensure block-based processing is working
**Look in logs**: Search for "Processing tile/block" messages

### Issue: Frame appears incorrect
**Check**: Frame should be 118px thick (~10mm at 300 DPI)
**Check**: Inner corners should have 24px radius (~2mm)

### Issue: Text doesn't appear
**Check**: Text overlays should be parsed from `text_editor` JSON
**Look in logs**: Search for "Text rendered" messages

### Issue: Filter not applied
**Check**: Filter name should match one of the supported filters
**Supported**: noir, stark, scandi, capri, nordic, belveder

## Log Monitoring

Key log messages to look for:

```
=== getDesignCollageImages started ===
PrintFileService initialized
Processing tile/block
generatePrintFiles started
Block dimensions
Image rendered
Filter applied
Text rendered
Frame rendered
Tile saved
generatePrintFiles completed
=== getDesignCollageImages completed ===
```

## Rollback Instructions

If issues occur, use the checkpoint system:

```powershell
cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor"
.\checkpoint_manager_dev.ps1
# Select "Restore checkpoint"
# Choose the checkpoint before this fix
```

Or manually:
1. Restore `app/Http/Controllers/Admin/OrderController.php` from previous version
2. Delete `app/Services/PrintFileService.php`

## Support Files

- **SPEC.md**: Technical specification
- **ALGORITHM.md**: Implementation algorithm
- **constants.json**: Constants reference
- **PRINT_FILE_FIX_SUMMARY.md**: Detailed fix summary
- **TESTING_GUIDE.md**: This file

## Contact & Notes

- Implementation date: 2025-10-09
- All changes only affect print file generation
- Editor and preview are completely unchanged
- Safe to test in production (only affects admin print file downloads)

## Success Indicators

✅ Print files have exact dimensions per spec
✅ Bleed calculated correctly
✅ Frames render with proper thickness and inner radius
✅ Stretched images align perfectly
✅ Text and filters apply correctly
✅ Editor functions normally
✅ Preview displays correctly

---

**Ready to test!** Start with simple single-tile collages, then test stretched images and complex layouts.

