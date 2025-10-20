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

For each tile `(r,c)` that belongs to block `b`:
1. **Block size (clear px)**  
   `BW = (c1-c0+1) * SAFE_W`, `BH = (r1-r0+1) * SAFE_H`.
2. **Block size (with bleed)**  
   `BW_OUT = BW + 2*BLEED`, `BH_OUT = BH + 2*BLEED`.
3. Create tile canvas `PRINT_W×PRINT_H`, clip to rounded rect `R_OUT`.
4. **Image**: scale to **cover** `(BW_OUT, BH_OUT)`; apply rotate/flip/pan relative to block center; draw.
5. **Filter**: apply on the image draw call.
6. **Text**: draw centered in block coordinates (clipped to `R_OUT`).
7. **Frame (optional)**:
   - Offscreen `BW_OUT×BH_OUT`; fill rounded rect `R_OUT` with frame color.
   - Punch inner hole inset **8 mm**, inner radius **R2 = 2 mm**.
   - Blit the tile’s subsection into `PRINT_W×PRINT_H`.
8. Encode PNG. **Skip** tiles with no image.
