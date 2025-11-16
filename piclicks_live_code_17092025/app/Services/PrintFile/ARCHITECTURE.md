# PrintFile Service Architecture

## Overview

The PrintFile service is a professionally architected, modular system for generating print-ready PNG files. It replaces the monolithic 854-line `PrintFileService.php` with a clean, testable, and maintainable architecture.

## Key Benefits

- **Modularity**: 35+ focused files vs 1 monolithic file
- **Testability**: Each component can be unit tested independently
- **Maintainability**: Clear separation of concerns, average 100-150 lines per file
- **Reliability**: Isolated error handling prevents cascading failures
- **Performance**: Supports parallel processing and memory optimization
- **Extensibility**: Easy to add new filters, fonts, or export formats

## Architecture Overview

```
PrintFileServiceFacade (Main Entry Point)
├── ImageProcessor (Image loading, rotation, scaling)
│   ├── ImageLoader (File I/O)
│   ├── ImageTransformer (Transformations)
│   └── ImageCache (LRU cache)
├── FilterRegistry (Filter management)
│   └── [6 Filter implementations]
├── TextRenderer (Text overlay)
│   ├── FontResolver (Platform-agnostic fonts)
│   ├── TextLayoutEngine (Position scaling)
│   └── RotationHandler (Rotated text)
├── FrameRenderer (Frame generation)
│   ├── FrameGeometry (Calculations)
│   └── FrameCompositor (Application)
├── TileExporter (File export)
│   ├── TileCropper (Tile extraction)
│   └── FileNamingStrategy (Naming)
├── CanvasFactory (Canvas creation)
├── RoundedCornerApplicator (Geometry)
└── ResourceManager (Memory management)
    ├── MemoryMonitor (Tracking)
    └── GarbageCollector (Cleanup)
```

## Directory Structure

```
app/Services/PrintFile/
├── PrintFileServiceFacade.php      # Main orchestrator
├── Contracts/                      # Interfaces (8 files)
├── Domain/                         # Value objects & business logic
│   ├── TileSpecification.php
│   ├── BlockConfiguration.php
│   ├── ColorValue.php
│   ├── DimensionCalculator.php
│   └── ValidationRules.php
├── Config/                         # Configuration
│   ├── PrintConstants.php         # All constants
│   └── PlatformConfig.php         # Platform-specific
├── Image/                          # Image subsystem (4 files)
├── Filters/                        # Filter subsystem (8 files)
├── Text/                           # Text subsystem (5 files)
├── Frame/                          # Frame subsystem (3 files)
├── Geometry/                       # Geometry operations (3 files)
├── Canvas/                         # Canvas management (3 files)
├── Export/                         # Export subsystem (4 files)
└── Resources/                      # Resource management (3 files)
```

## Design Patterns Used

1. **Facade Pattern**: `PrintFileServiceFacade` provides simple interface
2. **Strategy Pattern**: Filters are interchangeable strategies
3. **Factory Pattern**: `CanvasFactory` creates canvases
4. **Dependency Injection**: All dependencies injected via constructor
5. **Interface Segregation**: Small, focused interfaces
6. **Single Responsibility**: Each class has one clear purpose

## Usage

### Basic Usage

```php
use App\Services\PrintFile\PrintFileServiceFacade;

$facade = app(PrintFileServiceFacade::class);

$blockConfig = [
    'image_path' => 'images/photo.jpg',
    'cols' => 2,
    'rows' => 2,
    'zoom' => '0',
    'rotate' => '1',
    'filter' => 'filter-noir',
    'frame' => ['exists' => true, 'color_hex' => '#000000'],
    'text_overlays' => [
        [
            'text' => 'Hello World',
            'x' => 45,
            'y' => 40,
            'font_size' => 40,
            'font_family' => 'Arial',
            'color' => '#FFFFFF',
            'rotation' => 0,
        ]
    ],
    'start_col' => 0,
    'start_row' => 0,
];

[$success, $tilePaths] = $facade->generatePrintFiles($blockConfig);

if ($success) {
    // $tilePaths contains array of saved tile paths
    foreach ($tilePaths as $path) {
        echo "Tile saved: $path\n";
    }
}
```

### Service Provider Registration

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\PrintFileServiceProvider::class,
],
```

## Subsystem Details

### Image Processing Subsystem

**Purpose**: Load, transform, and render images

**Components**:
- `ImageLoader`: Loads images from disk with validation
- `ImageTransformer`: Applies rotation, scaling
- `ImageCache`: LRU cache for loaded images
- `ImageProcessor`: Facade for image operations

**Error Handling**: Graceful degradation - missing images don't crash pipeline

### Filter Subsystem

**Purpose**: Apply visual filters to images

**Components**:
- `AbstractFilter`: Base class for all filters
- 6 filter implementations: Noir, Stark, Scandi, Capri, Nordic, Belveder
- `FilterRegistry`: Manages available filters

**Extensibility**: Add new filters by extending `AbstractFilter`

### Text Rendering Subsystem

**Purpose**: Render text overlays with fonts and rotation

**Components**:
- `FontResolver`: Platform-agnostic font loading
- `TextLayoutEngine`: Coordinate transformation (editor → print)
- `RotationHandler`: Rotated text without truncation
- `FontCache`: Caches font paths
- `TextRenderer`: Orchestrates text rendering

**Error Handling**: Falls back to Arial if requested font not found

### Frame Rendering Subsystem

**Purpose**: Generate decorative frames

**Components**:
- `FrameGeometry`: Calculates frame dimensions
- `FrameRenderer`: Creates frame canvases
- `FrameCompositor`: Applies frames to tiles

**Features**: Rounded corners, inner radius, proper bleed handling

### Export Subsystem

**Purpose**: Save tiles to disk

**Components**:
- `TileCropper`: Extracts tiles from block canvas
- `TileExporter`: Saves tiles with proper naming
- `FileNamingStrategy`: Generates filenames
- `CompressionManager`: PNG compression

**Output**: 300 DPI PNG files with optimized compression

### Resource Management

**Purpose**: Prevent memory leaks and exhaustion

**Components**:
- `ResourceManager`: Tracks all GD resources
- `MemoryMonitor`: Monitors memory usage
- `GarbageCollector`: Forces cleanup when needed

**Benefits**: Explicit cleanup prevents memory issues

## Configuration

All constants centralized in `PrintConstants.php`:

```php
// Dimensions
const CLEAR_TILE_W_MM = 143.7;
const CLEAR_TILE_H_MM = 126.0;
const PRINT_TILE_W_MM = 147.7;
const PRINT_TILE_H_MM = 130.0;

// DPI
const DPI = 300;

// Limits
const MAX_FONT_SIZE_PX = 4000;
const MAX_TILE_COLS = 10;
const MAX_TILE_ROWS = 10;
```

## Testing

### Unit Testing

Each subsystem can be tested independently:

```php
// Test a filter
$filter = new NoirFilter();
$canvas = imagecreatetruecolor(100, 100);
$result = $filter->apply($canvas);
$this->assertTrue($result);
```

### Integration Testing

Test subsystem interactions:

```php
$imageProcessor = new ImageProcessor($loader, $transformer, $cache);
$success = $imageProcessor->renderImageToCanvas(...);
$this->assertTrue($success);
```

### End-to-End Testing

Test complete pipeline with sample data.

## Error Handling Strategy

Each subsystem has isolated error handling:

- **Image subsystem**: Missing image → returns null, doesn't crash
- **Filter subsystem**: Invalid filter → skips filter, logs warning
- **Text subsystem**: Missing font → uses Arial fallback
- **Frame subsystem**: Frame error → continues without frame

**Principle**: Graceful degradation - never crash entire batch for one tile issue.

## Performance Optimization

### Memory Management

- Canvas pooling via `CanvasPool`
- Resource tracking via `ResourceManager`
- Automatic garbage collection when threshold reached

### Caching

- Image cache: Avoids re-reading same image
- Font cache: Avoids re-resolving font paths

### Future: Parallel Processing

Architecture supports parallel tile processing via Laravel Queues:

```php
foreach ($tiles as $tile) {
    RenderSingleTileJob::dispatch($tile);
}
```

## Migration from Old System

### Backward Compatibility

Old `PrintFileService` remains available during transition.

### Migration Steps

1. Register `PrintFileServiceProvider`
2. Update `GeneratePrintFilesJob` to use new facade
3. Test with sample collages
4. Run parallel with old system for validation
5. Switch production traffic
6. Remove old `PrintFileService.php`

### API Compatibility

New facade accepts same configuration array as old service, ensuring drop-in replacement.

## Extending the System

### Adding a New Filter

1. Create class extending `AbstractFilter`
2. Implement `applyFilter()` method
3. Register in `FilterRegistry` constructor

```php
class MyFilter extends AbstractFilter
{
    protected string $name = 'filter-my';
    protected string $description = 'My custom filter';
    
    protected function applyFilter(GdImage $canvas): bool
    {
        return imagefilter($canvas, IMG_FILTER_GRAYSCALE);
    }
}
```

### Adding a New Font

Update `PlatformConfig::getFontMappings()`:

```php
'My Font' => ['myfont.ttf', 'MyFont.ttf']
```

### Custom Export Formats

Implement `TileExporterInterface` with custom logic.

## Troubleshooting

### Memory Issues

Check `MemoryMonitor` logs:

```php
$monitor = app(MemoryMonitor::class);
$usage = $monitor->getUsage();
Log::info('Memory usage', $usage);
```

### Font Not Found

Check font directories:

```php
$directories = PlatformConfig::getFontDirectories();
// Ensure your fonts are in one of these directories
```

### Tile Generation Fails

Check logs for specific subsystem errors:

```
[2025-10-31] Image rendering failed: path/to/image.jpg not found
```

## Best Practices

1. **Always use dependency injection** - don't instantiate directly
2. **Log important operations** - aids debugging
3. **Validate inputs early** - use `ValidationRules`
4. **Clean up resources** - use `ResourceManager`
5. **Test each subsystem** - unit tests catch issues early

## Support

For issues or questions:
- Check logs in `storage/logs/laravel.log`
- Review this documentation
- Examine relevant subsystem code (small, focused files)









