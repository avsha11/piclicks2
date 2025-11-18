# Text Coordinate System Redesign Analysis

## Current Understanding

### How the 8 Points are Selected (imagettfbbox)

The 8 points come from `imagettfbbox()` which returns the 4 corners of the text's bounding box:
- Each corner has (x, y) coordinates
- Total: 8 values (4 corners × 2 coordinates)
- These are relative to the **baseline point** (0,0) where text would be drawn

**The 8 points represent:**
1. Lower-left corner: `($bbox[0], $bbox[1])`
2. Lower-right corner: `($bbox[2], $bbox[3])`
3. Upper-right corner: `($bbox[4], $bbox[5])`
4. Upper-left corner: `($bbox[6], $bbox[7])`

These form a rectangle (or rotated rectangle) that bounds the text.

### Current Coordinate System

**Editor stores:**
- `left: Xpx, top: Ypx` - Absolute position in the collage container (`.middle`)
- `transform: translate(-50%, -50%) rotate(...deg)` - CSS transforms
- The collage container is the grid container

**Current approach:**
- Coordinates are **grid-relative** (absolute positions in the collage container)
- Text position is independent of image blocks
- Each collage with different layout gets different absolute coordinates

## User's Insight: Container-Relative Coordinates

### The Problem with Grid-Relative Coordinates

If text is positioned at `left: 300px, top: 200px`:
- In a 5×5 grid: This might be over tile (3,2)
- In a 3×3 grid: This might be off-canvas or over a different tile
- **The coordinate is not stable across different collage layouts**

### Proposed: Container-Relative Coordinates

**Concept:** Position text relative to the **occupied image containers** (blocks), not the grid.

**Benefits:**
1. **Stable across layouts**: Text position relative to image blocks stays consistent
2. **Unique per collage**: The coordinate system is defined by the actual content layout
3. **Renderer can identify**: If renderer knows which blocks exist and their positions, it can reconstruct the same coordinate system

### How Container-Relative Would Work

**Option A: Block-Anchor System**
- Text position: `block_id: 2, offset_x: 50px, offset_y: 30px`
- Meaning: "50px right, 30px down from the top-left of block 2"
- Renderer: Finds block 2, calculates its position, adds offset

**Option B: Multi-Block Coordinate System**
- Define coordinate system based on union of all occupied blocks
- Zero point: Top-left of the bounding box of all blocks
- Text position: Relative to this "content bounding box"
- Renderer: Calculates content bounding box, applies text position

**Option C: Block Grid System**
- Treat occupied blocks as a "virtual grid"
- Text position: `block_col: 1, block_row: 0, offset_x: 50px, offset_y: 30px`
- Renderer: Maps block grid to actual positions

## The Zero Point Question

### Current: Grid-Based Zero Point
- Zero point: Top-left of the collage grid container
- Text `(x, y)` = absolute position in grid
- **Problem**: Changes when grid size changes

### Proposed: Container-Based Zero Point
- Zero point: Top-left of the **content bounding box** (union of all occupied blocks)
- Text `(x, y)` = position relative to content
- **Benefit**: Stable across different grid sizes

## Implementation Strategy

### Phase 1: Understand Current Editor Behavior
1. Check if editor stores block IDs or positions
2. Determine if we can identify which block(s) text is "anchored" to
3. See if we can calculate content bounding box from saved data

### Phase 2: Coordinate System Transformation
1. Calculate content bounding box from all image blocks
2. Transform text coordinates from grid-relative to content-relative
3. Store both coordinate systems (for backward compatibility)

### Phase 3: Renderer Alignment
1. Renderer calculates same content bounding box
2. Applies content-relative coordinates
3. Maps to print coordinates using same logic

## Questions to Answer

1. **Does the editor have access to block/container information?**
   - Can we identify which image block a text is "near" or "over"?
   - Do we store block positions/IDs?

2. **What makes a coordinate system "unique to the collage"?**
   - Is it the content bounding box?
   - Is it the specific arrangement of blocks?
   - Is it something else?

3. **How should the renderer "identify and relate to the points"?**
   - Should it recalculate the content bounding box from block data?
   - Should we store the coordinate system definition with the collage?
   - Should we store the 8 bounding box points directly?

## Recommendation

Before implementing, we should:
1. **Dump actual editor data** to see what's stored
2. **Map the coordinate system** - understand exactly what `(x, y)` represents
3. **Test with different layouts** - see if coordinates are stable
4. **Design the transformation** - how to convert grid-relative to container-relative

The key insight: **If the renderer can reconstruct the same coordinate system as the editor, positioning will be accurate regardless of grid size or layout.**

