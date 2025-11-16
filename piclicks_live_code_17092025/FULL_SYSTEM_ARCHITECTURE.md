# Piclicks System Architecture - Complete Overview

## System Purpose

Piclicks allows users to create custom photo collages on an editor and order professional prints. The system ensures **what you see on the editor matches what you get in print** while providing professional print quality (300 DPI, proper bleed, color accuracy).

## Core Principle

**EDITOR ↔ PRINT CONSISTENCY**: Every pixel on the editor must translate accurately to the print file, maintaining visual fidelity while adding technical print requirements (bleed, high resolution, proper dimensions).

---

## Main Functions & Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    PICLICKS SYSTEM FLOW                         │
└─────────────────────────────────────────────────────────────────┘

1. UPLOAD IMAGES
   ↓
   [User uploads photos] → [Store in storage] → [Generate thumbnails]
   
2. EDIT COLLAGE & IMAGES (Editor)
   ↓
   [Visual Editor] → [Real-time preview] → [Save to database]
   ├─ Position images
   ├─ Apply filters
   ├─ Add text
   ├─ Add frames
   └─ Zoom/rotate
   
3. PREVIEW COLLAGE
   ↓
   [Preview mode] → [Verify design] → [User confirmation]
   
4. CHECKOUT
   ↓
   [Add to cart] → [Payment] → [Create order] → [Trigger print generation]
   
5. ADMIN - FULFILL ORDER
   ↓
   [View order] → [Generate print files] → [Download] → [Send to printer]
   └─── ⭐ PrintFileService operates here ⭐
```

---

## Function 1: Upload Images

**Purpose**: Allow users to upload their photos to create collages

### Components
```
app/Http/Controllers/
├── OptimizedUploadController.php      # Handles image uploads
└── HomeController.php                 # Gallery management

app/Traits/
└── UploadImageTrait.php              # Image upload utilities

Storage:
└── storage/app/public/images/        # Uploaded images
```

### Flow
1. User selects photos from device
2. Frontend uploads via AJAX
3. Backend validates (size, format, dimensions)
4. Store original + generate thumbnail
5. Save metadata to database
6. Return file paths to frontend

### Database
```sql
-- Images table
CREATE TABLE design_collage (
    id INT PRIMARY KEY,
    unique_id VARCHAR,
    image_original VARCHAR,    -- Original upload
    image_edited VARCHAR,      -- After editor changes
    seq INT,
    empty BOOLEAN
);
```

---

## Function 2: Edit Collage & Images (Editor)

**Purpose**: Visual editor where users design their collage

### Frontend Components
```
resources/views/
├── collage-editor.blade.php          # Main editor view
└── partials/
    ├── image-grid.blade.php          # Tile grid
    ├── filter-selector.blade.php     # Filter options
    ├── text-editor.blade.php         # Text overlay tool
    └── frame-selector.blade.php      # Frame options

public/js/
├── tool.js                           # Editor logic
├── collage-editor.js                 # Event handlers
└── preview.js                        # Preview functionality
```

### Backend Components
```
app/Services/
└── CollageServices.php               # Saves editor state

app/Http/Controllers/
└── CollageController.php             # Editor endpoints
```

### Editor Specifications (tool.js)

**Critical Constants:**
```javascript
actualWidth: 91px      // Tile width in editor
actualHeight: 80px     // Tile height in editor
canvasWidth: 750px     // Editor canvas width
canvasHeight: 750px    // Editor canvas height
```

**Editor → Print Mapping:**
- Editor uses small dimensions for web performance
- Print uses high resolution (300 DPI)
- **Scaling factor**: ~18.6× from editor to print
  - Editor tile: 91×80px
  - Print tile: ~1697×1489px (at 300 DPI)

### Editor Features

**1. Image Positioning**
- Drag & drop images onto grid
- Multi-tile blocks (stretch images across tiles)
- Zoom and pan within tiles
- Rotation (0°, 90°, 180°, 270°)

**2. Filters**
- Noir (B&W high contrast)
- Stark (desaturated)
- Scandi (bright warm)
- Capri (cool blue)
- Nordic (sepia dark)
- Belveder (rich sepia)

**3. Text Overlays**
- Add text anywhere on collage
- Font selection (Arial, Georgia, etc.)
- Size, color, rotation
- Position stored in editor coordinates

**4. Frames**
- Optional decorative frames
- Black or white color
- 8mm visible thickness
- Applied to entire block

### Database Storage
```sql
-- Master collage data
CREATE TABLE design_collage_master (
    id INT PRIMARY KEY,
    unique_id VARCHAR,
    grid_columns INT,           -- Grid size (e.g., 5×5)
    grid_rows INT,
    filter VARCHAR,             -- Applied filter
    frame VARCHAR,              -- Frame settings
    text_editor TEXT,           -- Text overlays JSON
    created_at TIMESTAMP
);

-- Individual tiles
CREATE TABLE design_collage (
    id INT PRIMARY KEY,
    unique_id VARCHAR,
    seq INT,                    -- Position in grid
    image_edited VARCHAR,       -- Image path
    other_settings JSON,        -- Zoom, rotate, position
    image_with_bleed TEXT,      -- Print file paths (JSON array)
    empty BOOLEAN,
    is_deleted BOOLEAN
);
```

### Editor State JSON Examples

**Text Overlays:**
```json
[
  {
    "text": "Hello World",
    "styles": "left: 375px; top: 375px; font-size: 24px; color: #FFFFFF; transform: rotate(45deg)"
  }
]
```

**Other Settings:**
```json
{
  "zoom": "0.5|0.5",
  "rotate": "2",
  "imageDivDataMargin": "2|2",
  "imageDivStyle": "left: 93px; top: 0px;"
}
```

---

## Function 3: Preview Collage

**Purpose**: Allow users to see final design before ordering

### Components
```
app/Http/Controllers/
└── CollageController.php
    └── previewCollage()          # Generate preview

resources/views/
└── preview.blade.php             # Preview page
```

### Flow
1. User clicks "Preview"
2. Editor state saved to database
3. Render preview page
4. Show collage as it will appear
5. User can:
   - Go back to edit
   - Proceed to checkout

### Preview Features
- Shows exact editor design
- Same filters, text, frames
- Grid layout with correct positioning
- No print-specific elements (bleed not visible)

---

## Function 4: Checkout

**Purpose**: Process payment and create order

### Components
```
app/Http/Controllers/FrontEnd/
├── Cart/CartController.php       # Cart management
└── CheckoutController.php        # Checkout process

app/Livewire/
└── CartComponent.php            # Cart UI component

app/Models/
├── Cart.php
├── Order.php
└── OrderDetail.php
```

### Flow
1. User adds collage to cart
2. Apply coupons/discounts
3. Enter shipping information
4. Process payment
5. Create order in database
6. **Trigger print file generation** (background job)

### Order Creation
```php
// When order is placed
Order::create([
    'user_id' => $userId,
    'total_amount' => $total,
    'status' => 'pending',
]);

// Dispatch print generation job
GeneratePrintFilesJob::dispatch($uniqueId, $masterData);
```

### Background Job
```
app/Jobs/
└── GeneratePrintFilesJob.php
    ├── Reads collage data from database
    ├── Parses editor coordinates
    ├── Scales to print dimensions
    └── Calls PrintFileService
```

---

## Function 5: Admin - Fulfill Order & Download Print Files

**Purpose**: Admin downloads print-ready files to send to printer

### Components
```
app/Http/Controllers/Admin/
└── OrderController.php
    └── downloadPrintFiles()      # Download endpoint

⭐ app/Services/PrintFile/
└── PrintFileServiceFacade.php    # Our new architecture!
```

### Admin Flow
1. Admin logs into admin panel
2. Views list of orders
3. Selects an order
4. Clicks "Download Print Files"
5. System:
   - Retrieves print files from database
   - OR generates on-demand if missing
   - Zips all tile PNGs
   - Returns download

### Print File Generation (Our New Architecture)

**When it happens:**
- Automatically after order placement (background job)
- On-demand if admin requests and files don't exist

**What it does:**
```
PrintFileServiceFacade::generatePrintFiles([
    'image_path' => 'images/photo.jpg',
    'cols' => 2,
    'rows' => 2,
    'zoom' => '0.5|0.5',
    'rotate' => '2',
    'filter' => 'filter-noir',
    'frame' => ['exists' => true, 'color_hex' => '#000000'],
    'text_overlays' => [...],
]);

Returns:
[
    'designCollageImages/tile_r1_c1_1234567890_abc123.png',
    'designCollageImages/tile_r1_c2_1234567890_abc124.png',
    'designCollageImages/tile_r2_c1_1234567890_abc125.png',
    'designCollageImages/tile_r2_c2_1234567890_abc126.png',
]
```

### Print File Specifications

**Technical Requirements:**
- **Resolution**: 300 DPI
- **Format**: PNG with transparency
- **Size**: 147.7mm × 130.0mm (with 2mm bleed)
- **Visible area**: 143.7mm × 126.0mm
- **Corner radius**: 10mm (print), 8mm (visible)
- **Color space**: sRGB (converts to CMYK at printer)

**Editor → Print Coordinate Transformation:**
```
Editor Tile: 91px × 80px
Print Tile:  1697px × 1489px (~143.7mm × 126.0mm at 300 DPI)

Scale Factor: 18.6×

Example:
- Text at (45, 40) in editor
- Becomes (837, 744) in print
- Plus bleed offset (+24px)
- Final position: (861, 768) in print file
```

---

## Data Dependencies Between Functions

```
┌──────────────────────────────────────────────────────────────┐
│                    DATA FLOW DIAGRAM                         │
└──────────────────────────────────────────────────────────────┘

Function 1: UPLOAD
    ↓ (stores images)
    database: design_collage.image_original

Function 2: EDITOR
    ↓ (reads images, saves edits)
    database: design_collage_master (filter, frame, text)
    database: design_collage (zoom, rotate, position)

Function 3: PREVIEW
    ↓ (reads editor state)
    Reads: design_collage_master + design_collage
    Displays: Visual representation

Function 4: CHECKOUT
    ↓ (creates order, triggers generation)
    Creates: orders table
    Dispatches: GeneratePrintFilesJob
    Job reads: design_collage_master + design_collage

Function 5: ADMIN
    ↓ (generates/downloads print files)
    Reads: design_collage.image_with_bleed (stored paths)
    OR Calls: PrintFileService (on-demand generation)
    Returns: ZIP of PNG files
```

---

## Critical Integration Points

### 1. Editor Coordinate System
**File**: `public/js/tool.js`

Defines tile dimensions that **must match** backend scaling:
```javascript
const EDITOR_TILE_W = 91;
const EDITOR_TILE_H = 80;
```

### 2. Backend Coordinate Scaling
**File**: `app/Jobs/GeneratePrintFilesJob.php`

Parses editor coordinates and scales to print:
```php
// From editor 750px canvas
$editorTileWidth = 750 / $gridCols;
$toolJsTileWidth = 91;
$scaleFactor = $clearTileWPx / $toolJsTileWidth;  // ~18.6

// Scale text position
$printX = $editorX * $scaleFactor + $bleedPx;
$printY = $editorY * $scaleFactor + $bleedPx;
```

### 3. PrintFileService Integration
**File**: `app/Services/PrintFile/PrintFileServiceFacade.php`

Receives scaled coordinates from Job and generates final files:
```php
public function generatePrintFiles(array $blockConfig): array
{
    // Validates input
    // Creates canvas with bleed
    // Renders: Image → Filter → Text → Frame
    // Crops tiles
    // Applies rounded corners
    // Saves PNG files
    // Returns file paths
}
```

### 4. Database Storage
**Table**: `design_collage`
**Column**: `image_with_bleed`

Stores array of print file paths as JSON:
```json
[
  "designCollageImages/tile_r1_c1_1234567890_abc123.png",
  "designCollageImages/tile_r1_c2_1234567890_abc124.png"
]
```

---

## System Architecture by Layer

### Frontend Layer
```
Browser
├── HTML/Blade Templates (views)
├── JavaScript (tool.js, editor.js)
└── AJAX calls to backend
```

### Application Layer
```
Laravel Controllers
├── OptimizedUploadController (uploads)
├── CollageController (editor, preview)
├── CheckoutController (payment)
└── Admin/OrderController (fulfillment)
```

### Service Layer
```
Services
├── CollageServices.php (editor logic)
└── PrintFile/
    └── PrintFileServiceFacade.php (print generation)
        ├── ImageProcessor
        ├── FilterRegistry
        ├── TextRenderer
        ├── FrameRenderer
        └── TileExporter
```

### Job Layer
```
Jobs
└── GeneratePrintFilesJob.php
    ├── Reads database
    ├── Transforms coordinates
    └── Calls PrintFileService
```

### Data Layer
```
Database Tables
├── users
├── design_collage_master (collage metadata)
├── design_collage (tiles)
├── orders
└── order_details

Storage
├── storage/app/public/images/ (uploaded photos)
└── storage/app/public/designCollageImages/ (print files)
```

---

## Editor → Print Quality Assurance

### Matching Requirements

**1. Visual Fidelity**
- Filters must look identical (CSS filter → GD filter mapping)
- Text position must be accurate (coordinate scaling)
- Frame thickness must match (8mm visible = 10mm print with bleed)
- Image cropping must match (cover fit algorithm)

**2. Technical Specifications**
- Resolution: 300 DPI (vs 72 DPI on screen)
- Bleed: 2mm extra on all sides (hidden in editor, required for print)
- Color: Maintain color accuracy through sRGB
- Corner radius: Scale properly (8mm editor → 10mm print with bleed)

**3. Scaling Algorithms**

**Image Scaling (Cover Fit):**
```php
// Calculate scaled dimensions
if ($srcAspect > $targetAspect) {
    // Image wider - fit height
    $scaledH = $targetH;
    $scaledW = $targetH * $srcAspect;
} else {
    // Image taller - fit width
    $scaledW = $targetW;
    $scaledH = $targetW / $srcAspect;
}

// Position at top-left (matches editor)
$dstX = $bleedPx;
$dstY = $bleedPx;
```

**Text Position Scaling:**
```php
$editorTileW = 91;  // From tool.js
$printTileW = 1697; // At 300 DPI
$scaleFactor = $printTileW / $editorTileW; // 18.6

$printX = $editorX * $scaleFactor + $bleedPx;
$printY = $editorY * $scaleFactor + $bleedPx;
```

---

## Complete System File Map

```
piclicks/
├── app/
│   ├── Http/Controllers/
│   │   ├── OptimizedUploadController.php    [F1: Upload]
│   │   ├── CollageController.php            [F2: Editor]
│   │   ├── FrontEnd/
│   │   │   ├── CheckoutController.php       [F4: Checkout]
│   │   │   └── Cart/CartController.php      [F4: Cart]
│   │   └── Admin/
│   │       └── OrderController.php          [F5: Admin]
│   ├── Services/
│   │   ├── CollageServices.php              [F2: Editor logic]
│   │   └── PrintFile/                       [F5: Print generation]
│   │       ├── PrintFileServiceFacade.php   ⭐ Main orchestrator
│   │       ├── Image/                       (4 files)
│   │       ├── Filters/                     (8 files)
│   │       ├── Text/                        (5 files)
│   │       ├── Frame/                       (3 files)
│   │       ├── Export/                      (4 files)
│   │       └── ... (11 subdirectories)
│   ├── Jobs/
│   │   └── GeneratePrintFilesJob.php        [F4→F5: Background]
│   ├── Models/
│   │   ├── DesignCollageMaster.php
│   │   ├── DesignCollageModel.php
│   │   ├── Order.php
│   │   └── OrderDetail.php
│   └── Providers/
│       └── PrintFileServiceProvider.php     [DI Container]
├── resources/views/
│   ├── collage-editor.blade.php             [F2: Editor UI]
│   ├── preview.blade.php                    [F3: Preview]
│   └── admin/orders/
│       └── show.blade.php                   [F5: Admin UI]
├── public/js/
│   ├── tool.js                              [F2: Editor logic]
│   └── collage-editor.js                    [F2: Events]
└── storage/app/public/
    ├── images/                              [F1: Uploads]
    └── designCollageImages/                 [F5: Print files]
```

---

## Key Success Metrics

**1. Visual Accuracy**
- Editor preview matches print output: ✓
- Coordinate transformation accuracy: ±1px
- Color consistency: ΔE < 3 (perceptually identical)

**2. Technical Quality**
- Print resolution: 300 DPI ✓
- Bleed: Exactly 2mm ✓
- File format: PNG with transparency ✓
- Tile dimensions: 147.7×130.0mm ✓

**3. System Performance**
- Image upload: < 3 seconds
- Editor responsiveness: < 100ms
- Print generation: < 30 seconds per order
- Admin download: < 10 seconds

**4. Reliability**
- Print generation success rate: > 99%
- Memory management: No leaks
- Error recovery: Graceful degradation

---

## Conclusion

The Piclicks system is built around **5 core functions** that work together:

1. **Upload** → Stores user photos
2. **Editor** → Designs collage (position, filters, text, frames)
3. **Preview** → Shows final design
4. **Checkout** → Processes payment, triggers print generation
5. **Admin** → Downloads print-ready files

**PrintFileService** (our new architecture) operates in **Function 5**, transforming editor state into professional print files while maintaining perfect visual fidelity with the editor.

The system ensures **WYSIWYG** (What You See Is What You Get) through careful coordinate transformation, color mapping, and dimension scaling from the 750px editor canvas to 300 DPI print files.










