# Console Command Tools for Text Masking Debug

This file contains all the console debugging commands available for troubleshooting the text masking system.

## 🎨 Visual Debugging Commands

### **Primary Debug Commands**

#### `showFullMaskDebug()`
**Effect**: Shows both red rectangles (occupied tiles) and green overlay (actual mask) simultaneously
**Use**: Compare red and green areas to see if mask alignment is correct
**Expected**: Red rectangles should match green overlay perfectly

#### `showMaskDebug()`
**Effect**: Shows only red rectangles representing occupied tile areas
**Use**: See where the system thinks occupied tiles are located

#### `showClipPathMask()`
**Effect**: Shows only green overlay representing the CSS clip-path mask
**Use**: See what the actual mask allows to be visible

### **Coordinate Analysis Commands**

#### `debugMaskAlignment()`
**Effect**: Console output showing grid, container, and text layer dimensions
**Use**: Check if positioning calculations are correct
**Output**: Grid rect, container rect, text layer rect, and first 3 occupied tile coordinates

#### `showPolygonCoords()`
**Effect**: Detailed console output of all occupied tile coordinates
**Use**: See exact polygon coordinates being calculated for each tile
**Output**: Tile index, tileRect, relativeToGrid coordinates, and polygon string

#### `refreshMask()`
**Effect**: Forces complete mask refresh with detailed debugging
**Use**: Get fresh debugging data after making changes
**Output**: Clears overlays, recreates mask, shows debug overlays, detailed console logs

### **Text Layer Analysis Commands**

#### `checkTextVisibility()`
**Effect**: Console output showing text layer properties
**Use**: Check if text layer is properly configured
**Output**: Display, visibility, opacity, clip-path properties, and child count

#### `showTextWithoutMask()`
**Effect**: Temporarily removes mask to show text fully
**Use**: Test if text is properly positioned when unmasked
**Note**: Stops any running force intervals to prevent infinite loops

### **Cleanup Commands**

#### `clearAllDebug()`
**Effect**: Removes all debug overlays from the page
**Use**: Clean up visual debugging elements

#### `hideMaskDebug()`
**Effect**: Removes only the red rectangle debug overlay
**Use**: Clean up specific debug elements

## 🔧 Advanced Debugging Commands

### **Force Functions**

#### `forceClipPath()`
**Effect**: Continuously reapplies clip-path every 50ms to prevent external overrides
**Use**: When clip-path keeps getting cleared by other code
**Note**: Creates an interval that runs until stopped

#### `stopForceClipPath()`
**Effect**: Stops the force clip-path interval
**Use**: Clean up after using forceClipPath()

#### `nuclearMask()`
**Effect**: Injects CSS with !important rules to force mask application
**Use**: When other methods fail to apply the mask

### **Rebuild Functions**

#### `rebuildMask()`
**Effect**: Completely removes and recreates the text layer and mask system
**Use**: When the mask system is completely broken
**Process**: Removes text layer, creates new one, moves text, applies mask

#### `forceAddText()`
**Effect**: Finds all text overlays and forces them into the text layer
**Use**: When text isn't being moved to the text layer properly

#### `applySimpleMask()`
**Effect**: Applies mask cleanly without interference
**Use**: Simple mask application without complex logic

## 📋 Troubleshooting Workflow

### **Step 1: Basic Visual Check**
```javascript
showFullMaskDebug()
```
- Check if red rectangles align with photo tiles
- Check if green overlay matches red rectangles
- If mismatch, proceed to Step 2

### **Step 2: Coordinate Analysis**
```javascript
refreshMask()
```
- Review detailed console output
- Check grid dimensions and tile coordinates
- Look for tiles being skipped

### **Step 3: Text Layer Check**
```javascript
checkTextVisibility()
```
- Verify text layer properties
- Check if text is in the text layer
- Verify clip-path is applied

### **Step 4: Force Application (if needed)**
```javascript
clearAllDebug()
forceClipPath()
```
- Use when mask won't stay applied
- Monitor console for any errors

### **Step 5: Nuclear Option (if all else fails)**
```javascript
clearAllDebug()
rebuildMask()
```
- Complete system rebuild
- Last resort when everything is broken

## 🎯 Expected Results

### **Correct Mask Behavior**
- Red rectangles should perfectly align with photo tiles
- Green overlay should match red rectangles exactly
- Text should only be visible over occupied tiles
- Console should show proper tile coordinates (not tiny values like 1.1px)

### **Common Issues and Solutions**

#### **Tiny Mask (1.1px coordinates)**
- **Cause**: Wrong coordinate calculation reference
- **Solution**: Check grid positioning and tile coordinate calculations

#### **Missing Tiles (only 12 instead of ~29)**
- **Cause**: Tiles not being detected as occupied
- **Solution**: Check image source detection logic

#### **Misaligned Mask**
- **Cause**: Text layer and grid positioning mismatch
- **Solution**: Verify text layer positioning relative to grid

#### **Text Not Masked**
- **Cause**: Clip-path not being applied or being overridden
- **Solution**: Use forceClipPath() or nuclearMask()

## 📝 Quick Reference

| Command | Purpose | When to Use |
|---------|---------|-------------|
| `showFullMaskDebug()` | Visual comparison | First check for alignment issues |
| `refreshMask()` | Detailed analysis | When you need fresh debugging data |
| `debugMaskAlignment()` | Position check | When coordinates seem wrong |
| `checkTextVisibility()` | Text layer check | When text isn't appearing |
| `forceClipPath()` | Force application | When mask keeps getting cleared |
| `clearAllDebug()` | Cleanup | After debugging session |

## 🚨 Important Notes

- Always run `clearAllDebug()` before starting a new debugging session
- Use `refreshMask()` to get the most current debugging information
- The `forceClipPath()` function creates an interval - remember to stop it with `stopForceClipPath()`
- If you see tiny coordinates (like 1.1px), there's a fundamental calculation error
- Red rectangles should match photo tiles exactly - any mismatch indicates a problem

## 🔄 Complete Debugging Session Example

```javascript
// 1. Clear any existing debug elements
clearAllDebug()

// 2. Get fresh debugging data
refreshMask()

// 3. If mask is tiny or misaligned, check coordinates
showPolygonCoords()

// 4. If text isn't masked, force application
forceClipPath()

// 5. Clean up when done
clearAllDebug()
stopForceClipPath()
```


