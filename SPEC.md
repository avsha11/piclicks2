# SPEC

## Terms
- **Clear area**: The visible rounded rectangle in the Editor/Preview.  
- **With bleed**: Clear area plus **2 mm hidden bleed** (used in print files).

## Key constants
- `MM_PER_IN = 25.4`
- `DPI = 300`
- `PX_PER_MM = DPI / MM_PER_IN`
- `BLEED_MM = 2.0`
- Clear tile (visible): `W=143.7 mm`, `H=126.0 mm`, `R=8 mm`
- Print tile (with bleed): `W=147.7 mm`, `H=130.0 mm`, `R=10 mm`
- Frame (optional): `print_thickness=10 mm` (visible 8 mm after bleed is hidden), `inner_corner_radius=2 mm`

## Behavior
1. **Clear vs. With bleed**
   - Editor/Preview: clip content to the **clear** rounded rect (R8).  
   - Export: clip to **with‑bleed** rounded rect (R10), adding **2 mm** each side.

2. **Frames (optional)**
   - If `frame ∈ {black, white}`: draw a **full‑bleed** rounded rect (R10) in that color.  
   - Punch an inner hole inset by **8 mm** (inner radius **R2**). Effective visible border after trim = **8 mm**.  
   - For stretched blocks: render a **single continuous frame** for the block; each tile gets its section.

3. **Stretched images**
   - Treat the stretched image as one **block** with span `{r0..r1, c0..c1}`.  
   - Export per tile by rendering the **block** over its full span (with bleed), then **crop** the tile’s subsection.

4. **Render order (top→bottom)**
   1) Frame (incl. bleed)  
   2) Text (incl. bleed)  
   3) Style filter (applied to image)  
   4) Image (incl. bleed)

5. **Output (uniform)**
   - Per‑tile **PNG** at **300 dpi** (max).  
   - Skip tiles with no image content.  
   - Sources below 300 dpi are placed as‑is; canvas stays **300 dpi** for consistent print specs.  
   - Target color: **CMYK** downstream (runtime canvas may be sRGB).

## Acceptance criteria
- No white edges after trimming (2 mm bleed present).  
- Radii: **R8** (clear) in editor/preview; **R10** (with bleed) in print.  
- Frames: continuous across stretched blocks; visible 8 mm after trim; inner R2.  
- Per‑tile PNG matches editor inside the clear area.
