# Testing Steps - Print File Generation Fix

## Prerequisites

1. Ensure your Laravel app is running
2. Clear cache: `php artisan cache:clear`
3. Check that `storage/app/public/designCollageImages/` directory exists and is writable

## Test Scenario 1: Basic Collage with Frame and Filter

### Steps:

1. **Create New Collage**:
   - Go to upload photos page
   - Upload 4-6 images
   - Click "Continue to Editor"

2. **Design the Collage**:
   - Arrange images in grid
   - Select a frame (black or white)
   - Apply a filter (e.g., "Noir" or "Scandi")
   - Add text overlay: "Summer 2024"

3. **Save and Preview**:
   - Click the "Preview" button (green button with checkmark)
   - Wait for save to complete
   - Note the collage unique_id from the URL

4. **Verify Print Files Generated**:
   - Check folder: `storage/app/public/designCollageImages/`
   - Look for files matching pattern: `tile_r*_c*_*.png`
   - Count should match number of tiles in your collage
   - Open one file to verify:
     - Has frame around edges
     - Filter is applied
     - Text overlay appears
     - Rounded corners visible

5. **Check Database**:
   ```sql
   SELECT id, seq, image_with_bleed 
   FROM design_collage 
   WHERE unique_id = 'YOUR_UNIQUE_ID' 
   AND empty = 0 
   AND is_deleted = 0;
   ```
   - `image_with_bleed` should contain JSON array of file paths

6. **Complete Order**:
   - Click "Checkout"
   - Complete the purchase flow
   - Note the order ID

## Test Scenario 2: Admin Area Verification

### Steps:

1. **Open Order in Admin**:
   - Login to admin panel
   - Go to Orders → View order details
   - Find the order you just created

2. **Verify Preview Image**:
   - Look for the collage preview thumbnail in product details table
   - It should display correctly (not broken image)
   - The image should show the full collage as designed

3. **Download Print Files**:
   - Click "Download Zip" button
   - Wait for download to complete
   - Unzip the file

4. **Verify Zip Contents**:
   - Check that zip contains all tiles
   - File naming: `tile_1.png`, `tile_2.png`, etc.
   - Open each file and verify:
     - Frame is rendered correctly
     - Filter is applied
     - Text overlays appear in correct position
     - Images are high resolution (300 DPI)
     - Rounded corners (R10)
     - Proper bleed (2mm around edges)

## Test Scenario 3: Multi-Tile Stretched Image

### Steps:

1. **Create Collage with Stretched Image**:
   - Upload images
   - Drag one image to span 2×2 or 3×2 tiles
   - Apply frame and filter
   - Click "Preview"

2. **Verify Print Files**:
   - Check `designCollageImages/` folder
   - Should see multiple files: `tile_r1_c1_*.png`, `tile_r1_c2_*.png`, etc.
   - Each tile should show its portion of the stretched image
   - Frame should align correctly across tile boundaries

3. **Test Download**:
   - Complete order
   - Download zip from admin
   - Verify all tiles are present
   - Print tiles should align when placed together

## Test Scenario 4: Text Overlay Positioning

### Steps:

1. **Create Collage with Multiple Text Overlays**:
   - Add text in different positions (top-left, center, bottom-right)
   - Use different fonts and colors
   - Click "Preview"

2. **Verify Text Rendering**:
   - Open generated print files
   - Verify text appears in correct positions
   - Check font rendering quality
   - Verify colors are correct

## Expected Results

✅ **Success Criteria**:

1. Print files generate automatically when clicking "Preview"
2. No broken images in admin order details
3. Download zip contains all print files
4. Print files have:
   - Correct dimensions (147.7mm × 130.0mm at 300 DPI)
   - Frame rendered correctly (if selected)
   - Filter applied (if selected)
   - Text overlays in correct positions
   - Rounded corners (R10)
   - Proper bleed (2mm)
5. Logs show successful generation (check `storage/logs/laravel.log`)

## Troubleshooting

### Issue: No print files generated

**Check**:
- Logs in `storage/logs/laravel.log` for errors
- Folder permissions on `storage/app/public/designCollageImages/`
- GD library installed: `php -m | grep gd`

### Issue: Broken preview images

**Check**:
- Database `design_collage_master.image_path` field is populated
- File exists at `storage/app/public/{image_path}`
- Symlink exists: `php artisan storage:link`

### Issue: Text not rendering

**Check**:
- Font files available in `C:/Windows/Fonts/` (Windows) or `/usr/share/fonts/` (Linux)
- Check logs for font path resolution errors

### Issue: Frame/Filter not applied

**Check**:
- `design_collage_master.frame` and `filter` fields are set correctly
- PrintFileService is receiving correct config
- Check logs for rendering errors

## Log File Locations

- Application logs: `storage/logs/laravel.log`
- Look for:
  - `generatePrintFilesForCollage started`
  - `Processing tile/block`
  - `Print files generated successfully`
  - `generatePrintFilesForCollage completed`

## Sample Log Output (Success)

```
[2025-10-16 10:30:15] local.INFO: generatePrintFilesForCollage started {"unique_id":"123456789"}
[2025-10-16 10:30:15] local.INFO: Processing tile/block {"tile_id":45,"image":"designCollageImages/collage_123.png","span":"1x1","position":"row:0, col:0"}
[2025-10-16 10:30:16] local.INFO: Image rendered {"src":"2048x1536px","scaled":"1744x1308px","position":"0,113"}
[2025-10-16 10:30:16] local.INFO: Filter applied {"filter":"filter-noir"}
[2025-10-16 10:30:16] local.INFO: Text rendered {"count":2}
[2025-10-16 10:30:16] local.INFO: Frame rendered {"size":"1744x1535px","thickness":"118px"}
[2025-10-16 10:30:16] local.INFO: Tile saved {"path":"designCollageImages/tile_r1_c1_167913...png","row":0,"col":0}
[2025-10-16 10:30:16] local.INFO: Print files generated successfully {"tile_id":45,"files_count":1}
[2025-10-16 10:30:16] local.INFO: generatePrintFilesForCollage completed {"unique_id":"123456789","processed":6,"total_tiles":6}
```

---

**Next Steps**: 
1. Run through all test scenarios
2. Report any issues found
3. If all tests pass, mark TODOs 2-4 as completed


