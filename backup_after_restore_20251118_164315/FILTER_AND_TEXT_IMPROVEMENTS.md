# Filter and Text Improvements Applied

## Date: October 27, 2025

## Summary
Improved the print file generation to better match the editor's visual appearance for filters and text overlays.

---

## 🎨 Filter Improvements

### CSS vs GD Conversion Formula

**Contrast:**
- CSS `contrast(100%)` = GD `0` (no change)
- CSS `contrast(120%)` = GD `-20` (more contrast)
- CSS `contrast(90%)` = GD `+10` (less contrast)

**Brightness:**
- CSS `brightness(100%)` = GD `0` (no change)
- CSS `brightness(120%)` = GD `+20` (brighter)
- CSS `brightness(80%)` = GD `-20` (darker)

### Filter Implementations

#### 1. **Noir Filter**
- **CSS:** `grayscale(100%) contrast(1.2)`
- **GD:** Grayscale + Contrast(-20)
- **Result:** Full grayscale with high contrast

#### 2. **Stark Filter**
- **CSS:** `grayscale(50%) brightness(100%) contrast(90%)`
- **GD:** Contrast(+10) + Colorize desaturation(50%)
- **Result:** Partial grayscale with reduced contrast

#### 3. **Scandi Filter**
- **CSS:** `brightness(120%) contrast(105%) grayscale(10%) hue-rotate(5deg)`
- **GD:** Brightness(+20) + Contrast(-5) + Warm colorize(15,8,-5)
- **Result:** Bright, slightly warm tone

#### 4. **Capri Filter**
- **CSS:** `contrast(120%) brightness(110%) saturate(150%) hue-rotate(-30deg)`
- **GD:** Contrast(-20) + Brightness(+10) + Cool colorize(-15,5,35)
- **Result:** Vibrant with blue/cyan tint

#### 5. **Nordic Filter**
- **CSS:** `contrast(110%) brightness(80%) sepia(20%) hue-rotate(-15deg)`
- **GD:** Contrast(-10) + Brightness(-20) + Grayscale + Sepia colorize(30,25,15)
- **Result:** Darker with warm sepia tone

#### 6. **Belveder Filter**
- **CSS:** `contrast(115%) brightness(90%) sepia(30%) hue-rotate(10deg)`
- **GD:** Contrast(-15) + Brightness(-10) + Grayscale + Rich sepia colorize(100,60,35)
- **Result:** Rich sepia with higher contrast

---

## ✍️ Text Rendering Improvements

### Current Implementation

1. **Font Size Scaling:** 
   - Editor font size × 2.5 = Print font size
   - Example: 40px (editor) → 100px (print)
   - **Note:** This may need adjustment based on visual comparison

2. **Text Rotation:**
   - Extracted from CSS `transform: rotate(Xdeg)`
   - Applied using `imagerotate()` with temporary canvas
   - Maintains alpha transparency

3. **Text Color:**
   - Extracted from CSS `color: rgb()` or `color: #hex`
   - Converted to GD color allocation

4. **Font Support:**
   - Primary: TrueType fonts (TTF) from system fonts
   - Fallback: GD built-in fonts (no rotation support)
   - Search paths:
     - `C:/Windows/Fonts/` (Windows)
     - `/usr/share/fonts/` (Linux)
     - `/System/Library/Fonts/` (macOS)
     - `storage/fonts/` (Custom fonts)

### Enhanced Logging

Added detailed logging for each text overlay:
- Text content (truncated to 20 chars)
- Position (x, y with bleed offset)
- Font size
- Color (hex)
- Rotation angle
- Font family
- Font path (if found)

---

## 🧪 Testing Instructions

### 1. Create Test Order
1. Go to: `http://localhost:8000`
2. Create a simple collage (3x3)
3. **Add text overlay** with rotation
4. **Apply a filter** (e.g., Capri, Noir, Scandi)
5. Click "Preview"
6. Complete checkout

### 2. Check Logs
```powershell
cd "C:\Users\Sveta\Dropbox\...\piclicks_live_code_17092025"
Get-Content "storage\logs\laravel.log" | Select-Object -Last 100 | Select-String -Pattern "Rendering text|Applied.*filter|Filter application"
```

Look for:
- ✅ Filter applied with correct steps
- ✅ Text rendered with position and rotation
- ✅ Font path found (TTF)

### 3. Download & Compare
1. Go to admin: `http://localhost:8000/admin-panel`
2. Find the order
3. Download print files (PNG)
4. **Visually compare** with editor screenshot
5. Check:
   - ✅ Filter matches editor appearance
   - ✅ Text size looks proportional
   - ✅ Text rotation matches
   - ✅ Text color matches

---

## 🔧 Fine-Tuning

If text or filters still don't match perfectly:

### Adjust Font Size Scaling
Edit: `app/Services/CollageServices.php` line 1270
```php
$fontProperties['font_size'] = intval($editorFontSize * 2.5); // Try 2.0, 3.0, etc.
```

### Adjust Filter Strength
Edit: `app/Services/PrintFileService.php` lines 310-367
- Increase/decrease contrast values
- Adjust brightness values
- Tune colorize RGB values

---

## 📊 Known Limitations

### Filters
- **Hue-rotate:** Not directly supported by GD. Approximated with colorize.
- **Saturate:** GD's saturation is binary (full or partial desaturation only).
- **Multiple filter chain:** GD applies filters sequentially, not as a single operation.

### Text
- **Font rendering:** GD's text anti-aliasing differs from browser rendering.
- **Kerning/spacing:** Browser's text engine has superior spacing.
- **Web fonts:** Only system fonts (TTF/OTF) are supported.

---

## ✅ Expected Results

After these improvements:
- ✅ Filters should be **visually similar** (not pixel-perfect but close)
- ✅ Text should have **correct color**
- ✅ Text **rotation** should match
- ✅ Text **size** should be proportional
- ✅ All print files should be **PNG** with **correct dimensions** (147.7mm × 130.0mm)

---

## 🎯 Next Steps

1. **Test** with a new order
2. **Compare** print files visually with editor
3. **Adjust** font scaling if needed (line 1270 in CollageServices.php)
4. **Fine-tune** specific filter values if colors are off
5. **Report** visual comparison results

---

## Files Modified

1. `app/Services/PrintFileService.php`
   - Improved filter approximations
   - Enhanced text rendering logging
   
2. `app/Services/CollageServices.php`
   - Font size scaling (already exists, may need tuning)
   - Rotation extraction from CSS

