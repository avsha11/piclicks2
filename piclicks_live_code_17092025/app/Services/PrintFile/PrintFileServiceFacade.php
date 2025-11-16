<?php

namespace App\Services\PrintFile;

use App\Services\PrintFile\Canvas\CanvasFactory;
use App\Services\PrintFile\Contracts\{
    ImageProcessorInterface,
    FrameRendererInterface,
    TextRendererInterface,
    TileExporterInterface,
    ResourceManagerInterface
};
use App\Services\PrintFile\Domain\{BlockConfiguration, TileSpecification, ValidationRules};
use App\Services\PrintFile\Filters\FilterRegistry;
use App\Services\PrintFile\Geometry\RoundedCornerApplicator;
use App\Services\PrintFile\Config\PrintConstants;
use Illuminate\Support\Facades\Log;

/**
 * Main facade for print file generation
 * 
 * Orchestrates all subsystems to generate print-ready tiles
 */
class PrintFileServiceFacade
{
    private ImageProcessorInterface $imageProcessor;
    private FilterRegistry $filterRegistry;
    private TextRendererInterface $textRenderer;
    private FrameRendererInterface $frameRenderer;
    private TileExporterInterface $tileExporter;
    private CanvasFactory $canvasFactory;
    private RoundedCornerApplicator $cornerApplicator;
    private ResourceManagerInterface $resourceManager;
    private TileSpecification $tileSpec;
    
    public function __construct(
        ImageProcessorInterface $imageProcessor,
        FilterRegistry $filterRegistry,
        TextRendererInterface $textRenderer,
        FrameRendererInterface $frameRenderer,
        TileExporterInterface $tileExporter,
        CanvasFactory $canvasFactory,
        RoundedCornerApplicator $cornerApplicator,
        ResourceManagerInterface $resourceManager
    ) {
        $this->imageProcessor = $imageProcessor;
        $this->filterRegistry = $filterRegistry;
        $this->textRenderer = $textRenderer;
        $this->frameRenderer = $frameRenderer;
        $this->tileExporter = $tileExporter;
        $this->canvasFactory = $canvasFactory;
        $this->cornerApplicator = $cornerApplicator;
        $this->resourceManager = $resourceManager;
        
        // Initialize tile specification
        $this->initializeTileSpecification();
    }
    
    /**
     * Generate print files for a block
     * 
     * @param array $blockConfig Block configuration array
     * @return array [success: bool, paths: array]
     */
    public function generatePrintFiles(array $blockConfig): array
    {
        try {
            Log::info('=== PrintFileServiceFacade: Starting print file generation ===', [
                'image' => $blockConfig['image_path'] ?? 'N/A',
                'span' => ($blockConfig['cols'] ?? 1) . 'x' . ($blockConfig['rows'] ?? 1),
            ]);
            
            // Validate configuration
            $config = new BlockConfiguration($blockConfig);
            $errors = $config->validate();
            
            if (!empty($errors)) {
                Log::error('Invalid block configuration', ['errors' => $errors]);
                return [false, []];
            }
            
            // Calculate block dimensions
            $dimensions = $this->tileSpec->calculateBlockDimensions(
                $config->getCols(),
                $config->getRows()
            );
            
            Log::info('Block dimensions calculated', $dimensions);
            
            // Create block canvas
            $blockCanvas = $this->canvasFactory->createWhiteCanvas(
                $dimensions['printWidth'],
                $dimensions['printHeight']
            );
            
            if ($blockCanvas === null) {
                Log::error('Failed to create block canvas');
                return [false, []];
            }
            
            $this->resourceManager->track($blockCanvas, 'block_canvas');
            
            // RENDER PIPELINE: Image → Filter → Text → Frame
            $this->renderPipeline($blockCanvas, $config, $dimensions);
            
            // Crop and export tiles
            $tilePaths = $this->exportTiles(
                $blockCanvas,
                $config,
                $dimensions
            );
            
            // Cleanup
            $this->resourceManager->destroyAll();
            
            Log::info('=== PrintFileServiceFacade: Generation completed ===', [
                'tiles_generated' => count($tilePaths),
            ]);
            
            return [true, $tilePaths];
            
        } catch (\Exception $e) {
            Log::error('Exception in generatePrintFiles', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Ensure cleanup
            $this->resourceManager->destroyAll();
            
            return [false, []];
        }
    }
    
    /**
     * Execute the rendering pipeline
     */
    private function renderPipeline($blockCanvas, BlockConfiguration $config, array $dimensions): void
    {
        // Step 1: Render Image
        Log::info('Pipeline step 1: Rendering image');
        $this->imageProcessor->renderImageToCanvas(
            $blockCanvas,
            $config->getImagePath(),
            $dimensions['printWidth'],
            $dimensions['printHeight'],
            $config->getZoom(),
            $config->getRotate(),
            $this->tileSpec->getBleedPixels()
        );
        
        // Step 2: Apply Filter
        if ($config->hasFilter()) {
            Log::info('Pipeline step 2: Applying filter', ['filter' => $config->getFilter()]);
            $this->filterRegistry->applyFilter($config->getFilter(), $blockCanvas);
        }
        
        // Step 3: Render Text
        if ($config->hasTextOverlays()) {
            Log::info('Pipeline step 3: Rendering text', ['count' => count($config->getTextOverlays())]);
            $this->textRenderer->renderTextOverlays(
                $blockCanvas,
                $config->getTextOverlays(),
                $dimensions['clearWidth'],
                $dimensions['clearHeight'],
                $this->tileSpec->getBleedPixels()
            );
        }
        
        // Step 4: Render Frame (will be applied per-tile)
        // Frame rendering happens in exportTiles() method
    }
    
    /**
     * Export tiles from block canvas
     */
    private function exportTiles($blockCanvas, BlockConfiguration $config, array $dimensions): array
    {
        $tilePaths = [];
        
        // Render frame if needed
        $frameCanvas = null;
        if ($config->hasFrame()) {
            Log::info('Rendering frame for block');
            $frameCanvas = $this->frameRenderer->renderFrame(
                $dimensions['printWidth'],
                $dimensions['printHeight'],
                $config->getFrameConfig()
            );
            
            if ($frameCanvas !== null) {
                $this->resourceManager->track($frameCanvas, 'frame_canvas');
            }
        }
        
        // Crop and save each tile
        for ($row = 0; $row < $config->getRows(); $row++) {
            for ($col = 0; $col < $config->getCols(); $col++) {
                $tilePath = $this->processTile(
                    $blockCanvas,
                    $frameCanvas,
                    $row,
                    $col,
                    $config
                );
                
                if ($tilePath !== null) {
                    $tilePaths[] = $tilePath;
                }
            }
        }
        
        return $tilePaths;
    }
    
    /**
     * Process a single tile
     */
    private function processTile(
        $blockCanvas,
        $frameCanvas,
        int $row,
        int $col,
        BlockConfiguration $config
    ): ?string {
        try {
            // Crop tile from block
            $tileCanvas = $this->tileExporter->cropTileFromBlock(
                $blockCanvas,
                $col,
                $row,
                $this->tileSpec->getPrintTileWidth(),
                $this->tileSpec->getPrintTileHeight(),
                $this->tileSpec->getClearTileWidth(),
                $this->tileSpec->getClearTileHeight()
            );
            
            if ($tileCanvas === null) {
                Log::error('Failed to crop tile', ['row' => $row, 'col' => $col]);
                return null;
            }
            
            $this->resourceManager->track($tileCanvas, "tile_{$row}_{$col}");
            
            // Apply frame section if exists
            if ($frameCanvas !== null) {
                $cropX = $col * $this->tileSpec->getClearTileWidth();
                $cropY = $row * $this->tileSpec->getClearTileHeight();
                
                $this->frameRenderer->applyFrameSection(
                    $tileCanvas,
                    $frameCanvas,
                    $cropX,
                    $cropY,
                    $this->tileSpec->getPrintTileWidth(),
                    $this->tileSpec->getPrintTileHeight()
                );
            }
            
            // Apply rounded corners
            $this->cornerApplicator->applyRoundedCorners(
                $tileCanvas,
                $this->tileSpec->getPrintCornerRadius()
            );
            
            // Save tile
            $tilePath = $this->tileExporter->saveTile(
                $tileCanvas,
                $row + $config->getStartRow(),
                $col + $config->getStartCol(),
                $config->getRows(),
                $config->getCols()
            );
            
            // Cleanup tile canvas
            $this->resourceManager->destroy($tileCanvas);
            
            return $tilePath;
            
        } catch (\Exception $e) {
            Log::error('Exception processing tile', [
                'row' => $row,
                'col' => $col,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Initialize tile specification from constants
     */
    private function initializeTileSpecification(): void
    {
        $pxPerMm = PrintConstants::getPxPerMm();
        
        $this->tileSpec = new TileSpecification(
            PrintConstants::mmToPx(PrintConstants::CLEAR_TILE_W_MM),
            PrintConstants::mmToPx(PrintConstants::CLEAR_TILE_H_MM),
            PrintConstants::mmToPx(PrintConstants::PRINT_TILE_W_MM),
            PrintConstants::mmToPx(PrintConstants::PRINT_TILE_H_MM),
            PrintConstants::getBleedPx(),
            PrintConstants::mmToPx(PrintConstants::CLEAR_CORNER_RADIUS_MM),
            PrintConstants::mmToPx(PrintConstants::PRINT_CORNER_RADIUS_MM),
            $pxPerMm
        );
        
        Log::info('TileSpecification initialized', [
            'clear' => $this->tileSpec->getClearTileWidth() . 'x' . $this->tileSpec->getClearTileHeight(),
            'print' => $this->tileSpec->getPrintTileWidth() . 'x' . $this->tileSpec->getPrintTileHeight(),
            'bleed' => $this->tileSpec->getBleedPixels() . 'px',
        ]);
    }
}









