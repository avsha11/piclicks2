# The logic for generating print-ready files

**Goal**: Define how the collage editor exports *print‑ready* per‑tile PNGs that visually match the editor, with production bleed.

## TL;DR (for Cursor)
- **Clear area (visible)**: What users see in **Editor/Preview**.  
- **With bleed (print)**: Clear area **+ 2 mm hidden bleed** around each tile used only in the print file.  
- **Dimensions**: Clear tile = **143.7×126.0 mm, R8**. Print tile (with bleed) = **147.7×130.0 mm, R10**.  
- **Flattening**: On export, flatten pan/crop, filters, text, frames, and stretched images **per tile**.  
- **Frames (optional)**: If user adds a frame, print frame thickness = **10 mm**; visible after hiding bleed = **8 mm**; **inner radius R2**; outer print radius **R10**. For a stretched block, the frame wraps the **whole block**, not tiles individually.  
- **Output (uniform)**: **300 dpi PNGs** for every occupied tile only; canvas is 300 dpi max (sources below 300 dpi are placed without exceeding their native detail).

See `SPEC.md` and `ALGORITHM.md` for implementable details.
