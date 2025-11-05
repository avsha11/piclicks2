<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts
use App\Services\PrintFile\Contracts\{
    ImageProcessorInterface,
    FilterInterface,
    TextRendererInterface,
    FrameRendererInterface,
    GeometryInterface,
    CanvasFactoryInterface,
    TileExporterInterface,
    ResourceManagerInterface
};

// Implementations
use App\Services\PrintFile\Image\{
    ImageProcessor,
    ImageLoader,
    ImageTransformer,
    ImageCache
};
use App\Services\PrintFile\Filters\FilterRegistry;
use App\Services\PrintFile\Text\{
    TextRenderer,
    FontResolver,
    TextLayoutEngine,
    RotationHandler,
    FontCache
};
use App\Services\PrintFile\Frame\{
    FrameRenderer,
    FrameGeometry,
    FrameCompositor
};
use App\Services\PrintFile\Geometry\{
    RoundedCornerApplicator,
    MaskGenerator,
    ShapeDrawer
};
use App\Services\PrintFile\Canvas\{
    CanvasFactory,
    AlphaBlendingManager,
    CanvasPool
};
use App\Services\PrintFile\Export\{
    TileExporter,
    TileCropper,
    FileNamingStrategy,
    CompressionManager
};
use App\Services\PrintFile\Resources\{
    ResourceManager,
    MemoryMonitor,
    GarbageCollector
};
use App\Services\PrintFile\PrintFileServiceFacade;

/**
 * Service Provider for Print File subsystem
 * 
 * Binds all interfaces to their implementations
 */
class PrintFileServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register singletons for stateful services
        $this->app->singleton(FilterRegistry::class);
        $this->app->singleton(ImageCache::class, function ($app) {
            return new ImageCache(10); // Max 10 cached images
        });
        $this->app->singleton(FontCache::class);
        $this->app->singleton(MemoryMonitor::class);
        
        // Canvas Pool (singleton for reuse)
        $this->app->singleton(CanvasPool::class, function ($app) {
            return new CanvasPool($app->make(CanvasFactory::class), 10);
        });
        
        // Resource Management
        $this->app->bind(ResourceManagerInterface::class, function ($app) {
            return new ResourceManager($app->make(MemoryMonitor::class));
        });
        
        $this->app->singleton(GarbageCollector::class, function ($app) {
            return new GarbageCollector($app->make(MemoryMonitor::class));
        });
        
        // Canvas Factory
        $this->app->bind(CanvasFactoryInterface::class, CanvasFactory::class);
        $this->app->singleton(AlphaBlendingManager::class);
        
        // Geometry Services
        $this->app->bind(GeometryInterface::class, RoundedCornerApplicator::class);
        $this->app->singleton(MaskGenerator::class);
        $this->app->singleton(ShapeDrawer::class);
        
        // Image Processing
        $this->app->singleton(ImageLoader::class);
        $this->app->singleton(ImageTransformer::class);
        $this->app->bind(ImageProcessorInterface::class, function ($app) {
            return new ImageProcessor(
                $app->make(ImageLoader::class),
                $app->make(ImageTransformer::class),
                $app->make(ImageCache::class)
            );
        });
        
        // Text Rendering
        $this->app->singleton(FontResolver::class);
        $this->app->singleton(TextLayoutEngine::class);
        $this->app->singleton(RotationHandler::class);
        $this->app->bind(TextRendererInterface::class, function ($app) {
            return new TextRenderer(
                $app->make(FontResolver::class),
                $app->make(TextLayoutEngine::class),
                $app->make(RotationHandler::class)
            );
        });
        
        // Frame Rendering
        $this->app->singleton(FrameGeometry::class);
        $this->app->singleton(FrameCompositor::class);
        $this->app->bind(FrameRendererInterface::class, function ($app) {
            return new FrameRenderer(
                $app->make(FrameGeometry::class),
                $app->make(FrameCompositor::class),
                $app->make(RoundedCornerApplicator::class)
            );
        });
        
        // Export Subsystem
        $this->app->singleton(FileNamingStrategy::class);
        $this->app->singleton(CompressionManager::class);
        $this->app->singleton(TileCropper::class);
        $this->app->bind(TileExporterInterface::class, function ($app) {
            return new TileExporter(
                $app->make(FileNamingStrategy::class),
                $app->make(CompressionManager::class)
            );
        });
        
        // Main Facade
        $this->app->singleton(PrintFileServiceFacade::class, function ($app) {
            return new PrintFileServiceFacade(
                $app->make(ImageProcessorInterface::class),
                $app->make(FilterRegistry::class),
                $app->make(TextRendererInterface::class),
                $app->make(FrameRendererInterface::class),
                $app->make(TileExporterInterface::class),
                $app->make(CanvasFactory::class),
                $app->make(RoundedCornerApplicator::class),
                $app->make(ResourceManagerInterface::class)
            );
        });
    }
    
    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Nothing to bootstrap currently
    }
}




