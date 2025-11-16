# ALGORITHM (export per tile)

Constants:
```
PX_PER_MM = 300 / 25.4
SAFE_W  = 143.7 * PX_PER_MM     # clear area width  in px
SAFE_H  = 126.0 * PX_PER_MM     # clear area height in px
BLEED   = 2.0   * PX_PER_MM
PRINT_W = SAFE_W + 2*BLEED      # with bleed width
PRINT_H = SAFE_H + 2*BLEED      # with bleed height
R_OUT   = 10.0 * PX_PER_MM      # with bleed outer radius
R_CLEAR =  8.0 * PX_PER_MM      # clear area radius (editor/preview)
```

Terms:
- `container`: visual content block (may span multiple tiles).
- `tile`: fixed-size print unit (`PRINT_W×PRINT_H` canvas).

For each tile `(r,c)` inside a container spanning rows `r0..r1`, cols `c0..c1`:
1. **Container size (clear px)**  
   `CW = (c1-c0+1) * SAFE_W`, `CH = (r1-r0+1) * SAFE_H`.
2. **Container size (with bleed)**  
   `CW_OUT = CW + 2*BLEED`, `CH_OUT = CH + 2*BLEED`.
3. Create tile canvas `PRINT_W×PRINT_H`, clip to `R_OUT`.
4. **Image + style filter** – render container content with bleed:  
   - Scale source to **cover** `CW_OUT×CH_OUT`; apply rotate/flip/pan relative to container center.  
   - Apply the selected style filter while drawing.  
   - Blit the tile’s subsection into `PRINT_W×PRINT_H`.
5. **Frame overlay (optional)** – render once per container, then crop:  
   - Draw full container frame on offscreen `CW_OUT×CH_OUT` surface (rounded rect `R_OUT`).  
   - Punch inner hole inset **8 mm**, inner radius **R2 = 2 mm**.  
   - Composite the tile’s subsection of that frame **after** the image so the border sits on top without covering the interior (the punched hole keeps the image contained).
6. **Text** – draw on tile using container coordinates (clipped to `R_OUT`); sits above frame and image.
7. Encode PNG. **Skip** tiles without container content (empty tiles stay out of the export set).

Composite order (top→bottom, per SPEC): text → frame → image (with style filter applied during image draw).
