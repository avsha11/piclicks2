# The logic for generating print-ready files

**Goal**: Define how the collage editor exports *print‑ready* per‑tile PNGs that visually match the editor, with production bleed.

## TL;DR (for Cursor)
- **Clear area (visible)**: Visual content in single tiles users see in **Editor/Preview**, excluding the gaps between tiles.  
- **With bleed (print)**: Clear area **+ 2 mm hidden bleed** around each tile used only in the print file.  
- **Dimensions**: Clear tile = **143.7×126.0 mm, R8**. Print tile (with bleed) = **147.7×130.0 mm, R10**.  
- **Flattening**: On export, flatten pan/crop, filters, text, frames, and stretched images **per tile**.  
- **Color frames (optional)**: If user adds a frame, print frame thickness = **10 mm**; visible after hiding bleed = **8 mm**; **inner radius R2**; outer print radius **R10**. For a stretched block, the frame wraps the **whole block**, not tiles individually.  
- **Containers**: **300 dpi PNGs** for every occupied tile only; canvas is up to 300 dpi max (sources below 300 dpi are placed without exceeding their native detail).

**container** = The visual content block. Size can varry based on how many tiles display one image: One image on one tile or one image stretched on several tiles.
**Tile** = One print file unit. Fixed size.
Understood?

See `SPEC.md` for implementable details.
