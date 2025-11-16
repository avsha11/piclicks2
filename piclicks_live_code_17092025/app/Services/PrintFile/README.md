# PrintFile Service

A professional, modular architecture for generating print-ready tile images.

## Quick Start

```php
use App\Services\PrintFile\PrintFileServiceFacade;

$facade = app(PrintFileServiceFacade::class);

[$success, $tilePaths] = $facade->generatePrintFiles([
    'image_path' => 'images/photo.jpg',
    'cols' => 2,
    'rows' => 2,
    'filter' => 'filter-noir',
    // ... more options
]);
```

## Installation

1. Register the service provider in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\PrintFileServiceProvider::class,
],
```

2. Ensure GD library is enabled:

```bash
php -m | grep gd
```

3. Verify font directories exist (see `PlatformConfig`)

## Configuration Options

```php
[
    'image_path' => 'path/to/image.jpg',  // Required
    'cols' => 1,                          // Tile columns (1-10)
    'rows' => 1,                          // Tile rows (1-10)
    'start_col' => 0,                     // Grid start column
    'start_row' => 0,                     // Grid start row
    'zoom' => '0',                        // Zoom factor "zoomX|zoomY"
    'rotate' => '1',                      // Rotation: 1=0°, 2=90°, 3=180°, 4=270°
    'filter' => null,                     // Filter name or null
    'frame' => [
        'exists' => false,                // Enable frame
        'color_hex' => '#000000',         // Frame color
    ],
    'text_overlays' => [                  // Array of text overlays
        [
            'text' => 'Hello',
            'x' => 45,                    // Position in editor space
            'y' => 40,
            'font_size' => 40,            // Font size in editor space
            'font_family' => 'Arial',
            'color' => '#FFFFFF',
            'rotation' => 0,              // Degrees
            'translate_x_percent' => -50, // For centering
            'translate_y_percent' => -50,
        ],
    ],
]
```

## Available Filters

- `filter-noir`: Black and white with high contrast
- `filter-stark`: Slightly desaturated
- `filter-scandi`: Bright and slightly warm
- `filter-capri`: High contrast with cool tones
- `filter-nordic`: Darker with warm sepia
- `filter-belveder`: Rich sepia with contrast

## Output

- Format: PNG
- DPI: 300
- Color space: sRGB
- Tile size: 147.7mm × 130.0mm (with 2mm bleed)
- Corner radius: 10mm

## Architecture

See [ARCHITECTURE.md](./ARCHITECTURE.md) for detailed documentation.

## Key Features

- ✅ Modular design (35+ focused classes)
- ✅ Dependency injection
- ✅ Comprehensive error handling
- ✅ Memory management
- ✅ Image caching
- ✅ Font fallbacks
- ✅ Platform-agnostic
- ✅ Fully testable

## Error Handling

The system uses graceful degradation:
- Missing image → logged error, returns failure
- Missing font → falls back to Arial
- Invalid filter → continues without filter
- Frame error → continues without frame

## Memory Management

Automatic memory tracking and cleanup:
```php
$manager = app(ResourceManagerInterface::class);
$stats = $manager->getMemoryUsage();
```

## Testing

```bash
# Run tests (when implemented)
php artisan test --filter=PrintFile
```

## Extending

Add custom filters:
```php
class MyFilter extends AbstractFilter {
    protected string $name = 'my-filter';
    protected function applyFilter(GdImage $canvas): bool {
        // Your filter logic
    }
}
```

Register in `FilterRegistry`.

## Troubleshooting

### Memory limit errors
- Increase PHP memory_limit
- Reduce tile size or disable features
- Check MemoryMonitor logs

### Font not found
- Verify font files exist in system directories
- Check `PlatformConfig::getFontDirectories()`
- System falls back to Arial automatically

### Output quality issues
- Verify source image resolution
- Check DPI settings in PrintConstants
- Review log files for warnings

## Support

Check logs: `storage/logs/laravel.log`

System automatically logs:
- Memory usage
- Resource creation/destruction
- Filter application
- Font resolution
- Error details

## License

[Your License Here]









