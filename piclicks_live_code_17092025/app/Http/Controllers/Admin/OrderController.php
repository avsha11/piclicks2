<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Eloquent\{OrderRepository, DesignCollageRepository};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Services\FrontEnd\OrderService;
use App\Services\PrintFileService;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class OrderController extends Controller
{
    protected $orderRepository, $OrderService, $DesignCollageRepository;

    public function __construct(OrderRepository $orderRepository, OrderService $OrderService, DesignCollageRepository $DesignCollageRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->OrderService = $OrderService;
        $this->DesignCollageRepository = $DesignCollageRepository;
    }

    public function index(Request $request)
    {
        return view('admin.order-list');
    }

    public function getOrderList(Request $request)
    {
        if ($request->ajax()) {
            // $data = $this->orderRepository->getAll();
            $data = Order::with(['orderdetail_data', 'shipping_country_data'])->get();

            return DataTables::of($data)
                ->addColumn('internal_order_id', function ($row) {
                    return '#' . $row->internal_order_id;
                })
                // ->editColumn('created_at', function ($row) {
                //     return $row->created_at->format('d-m-Y'); // formatted for display
                // })
                // ->editColumn('delivery_date', function ($row) {
                //     return $row->delivery_date ? Carbon::parse($row->delivery_date)->format('d-m-Y') : 'N/A';
                // })
                // ->editColumn('created_at', function ($row) {
                //     return [
                //         'display' => Carbon::parse($row->created_at)->format('d-m-Y'),
                //         'timestamp' => Carbon::parse($row->created_at)->timestamp,
                //     ];
                // })
                // ->editColumn('delivery_date', function ($row) {
                //     return [
                //         'display' => Carbon::parse($row->delivery_date)->format('d-m-Y'),
                //         'timestamp' => Carbon::parse($row->delivery_date)->timestamp,
                //     ];
                // })
                ->addColumn('created_at_display', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->addColumn('created_at_raw', function ($row) {
                    return Carbon::parse($row->created_at)->timestamp;
                })
                ->addColumn('delivery_date_display', function ($row) {
                    return $row->delivery_date ? Carbon::parse($row->delivery_date)->format('d-m-Y') : 'N/A';
                })
                ->addColumn('delivery_date_raw', function ($row) {
                    return $row->delivery_date ? Carbon::parse($row->delivery_date)->timestamp : 0;
                })

                ->addColumn('shipping_country_name', function ($row) {
                    return $row->shipping_country_data->name ?? 'N/A';
                })
                ->editColumn('shipping_address', function ($row) {
                    return Str::limit($row->shipping_address, 30);
                })
                ->addColumn('total_amount', function ($row) {
                    return $row->total_amount;
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('admin.order-details', ['order_id' => $row->id]) . '" class="btn btn-warning btn-sm m-1">View</a> ' .
                        '<a onclick="return updateStatus(' . $row->id . ', \'' . $row->order_status . '\', \'' . $row->delivery_date . '\');" type="button" class="btn btn-primary btn-sm m-1">Update Status</a>';
                })
                ->rawColumns(['action', 'created_at', 'delivery_date'])
                ->make(true);
        }
        return redirect()->back()->withErrors(['error' => 'Invalid request.']);
    }



    function updateOrderStatus(Request $request)
    {
        try {

            return $this->OrderService->updateOrderStatus($request);
        } catch (Exception $e) {
            Log::error('Error in OrderController/updateOrderStatus :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero') . "in catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }

    public function orderDetails(Request $request)
    {
        try {

            $order = $this->orderRepository->getOne(['id' => $request->order_id]);
            // dd($order);
            if (!$order) {
                return response()->json(['status' => 0, 'message' => __('message.statusFour', ['parameter' => 'Order'])]);
            }

            return view('admin.order-details', compact('order'));
        } catch (Exception $e) {
            Log::error('Error in OrderController/orderDetails :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero') . "in catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }

    public function giftCardList()
    {
        try {

            return view('admin.gift-card');
        } catch (Exception $e) {
            Log::error('Error in OrderController/giftCardList :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero') . "in catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }

    public function getGiftcardList(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->orderRepository->getAllGiftcard();
            // dd($data);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('internal_order_id', function ($row) {
                    return '#' . $row->order_data->internal_order_id;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y');
                })
                ->addColumn('ref_id', function ($row) {
                    return "#REF" . $row->id;
                })
                ->editColumn('image', function ($row) {
                    return $row->giftcard_data->image;
                })
                ->editColumn('redeem_by_name', function ($row) {
                    return $row->redeem_by_data->name ?? '';
                })
                ->addColumn('delivery_date', function ($row) {
                    return Carbon::parse($row->delivery_date)->format('d M Y');
                })
                ->addColumn('total_amount', function ($row) {
                    return $row->quantity * $row->price;
                })->addColumn('used_on_order_id', function ($row) {
                    return optional($row->order_giftcard_used)->internal_order_id;
                })
                ->make(true); // ✅ This returns the final result to DataTables
        }
        return redirect()->back()->withErrors(['error' => 'Invalid request.']);
    }


    public function getDesignCollageImages(Request $request)
    {
        try {
            Log::info("=== getDesignCollageImages started ===", ['order_id' => $request->order_id]);
            
            // Get all tiles for this order
            $tiles_res = $this->DesignCollageRepository->getByWhere([
                'unique_id' => $request->order_id, 
                'empty' => 0, 
                'is_deleted' => 0
            ], ['seq' => 'asc']);
            
            $masterdata = $this->DesignCollageRepository->getOneMaster(['unique_id' => $request->order_id]);
            
            if ($tiles_res->isEmpty()) {
                Log::warning("No tiles found for order", ['order_id' => $request->order_id]);
                return response()->json(['status' => 1, 'message' => 'No tiles found', 'images' => []]);
            }
            
            // Parse text overlays (global for all tiles)
            $textOverlays = $this->parseTextOverlays($masterdata->text_editor ?? null);
            
            // Initialize PrintFileService
            $printFileService = new PrintFileService();
            
            // Process each tile/block
            $processedCount = 0;
            foreach ($tiles_res as $tile) {
                // Delete old print files if they exist
                $this->deleteOldPrintFiles($tile->image_with_bleed);
                
                // Skip if no image
                if (empty($tile->image_edited) || !File::exists(storage_path('app/public/' . $tile->image_edited))) {
                    Log::warning("Skipping tile - image not found", [
                        'tile_id' => $tile->id,
                        'image' => $tile->image_edited ?? 'N/A'
                    ]);
                    $this->DesignCollageRepository->update(['id' => $tile->id], ['image_with_bleed' => null]);
                    continue;
                }
                
                // Parse tile settings
                $other_settings = json_decode($tile->other_settings, true);
                $imageDivDataMargin = $other_settings['imageDivDataMargin'] ?? null;
                
                // Determine block size (cols x rows)
                [$cols, $rows] = $this->parseBlockSize($imageDivDataMargin);
                
                // Parse position from style to determine start row/col
                [$startCol, $startRow] = $this->parsePositionFromStyle($other_settings['imageDivStyle'] ?? '');
                
                Log::info("Processing tile/block", [
                    'tile_id' => $tile->id,
                    'image' => $tile->image_edited,
                    'span' => "{$cols}x{$rows}",
                    'position' => "row:{$startRow}, col:{$startCol}"
                ]);
                
                // Build block configuration for PrintFileService
                $blockConfig = [
                    'image_path' => $tile->image_edited,
                    'cols' => $cols,
                    'rows' => $rows,
                    'start_col' => $startCol,
                    'start_row' => $startRow,
                    'zoom' => $other_settings['zoom'] ?? '0',
                    'rotate' => $other_settings['rotate'] ?? '1',
                    'frame' => [
                        'exists' => !empty($masterdata->frame) && $masterdata->frame !== '0',
                        'color_hex' => stripos($masterdata->frame ?? '', 'black') !== false ? '#000000' : '#ffffff',
                    ],
                    'filter' => $masterdata->filter ?? null,
                    'text_overlays' => $textOverlays,
                ];
                
                // Generate print files using the new service
                [$success, $printFilePaths] = $printFileService->generatePrintFiles($blockConfig);
                
                if ($success) {
                    $printFilesJson = json_encode($printFilePaths, JSON_UNESCAPED_SLASHES);
                    $this->DesignCollageRepository->update(['id' => $tile->id], ['image_with_bleed' => $printFilesJson]);
                    Log::info("Print files generated successfully", [
                        'tile_id' => $tile->id,
                        'files_count' => count($printFilePaths)
                    ]);
                    $processedCount++;
                } else {
                    Log::error("Failed to generate print files", ['tile_id' => $tile->id]);
                    $this->DesignCollageRepository->update(['id' => $tile->id], ['image_with_bleed' => null]);
                }
            }
            
            // Collect all generated print files for response
            $images = [];
            $tiles_res_final = $this->DesignCollageRepository->getByWhere([
                'unique_id' => $request->order_id, 
                'empty' => 0, 
                'is_deleted' => 0
            ], ['seq' => 'asc']);
            
            foreach ($tiles_res_final as $tile) {
                $image_bleed = json_decode($tile->image_with_bleed, true);
                if (is_array($image_bleed) && !empty($image_bleed)) {
                    foreach ($image_bleed as $image) {
                        $images[] = ['image_edited' => $image];
                    }
                }
            }
            
            Log::info("=== getDesignCollageImages completed ===", [
                'processed' => $processedCount,
                'total_print_files' => count($images)
            ]);
            
            return response()->json(['status' => 1, 'message' => '', 'images' => $images]);
            
        } catch (Exception $e) {
            Log::error('Error in OrderController/getDesignCollageImages', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 0, 
                'message' => __('message.statusZero') . " in catch: " . $e->getMessage() . ' in line ' . $e->getLine()
            ]);
        }
    }
    
    /**
     * Parse text overlays from master data
     */
    private function parseTextOverlays(?string $textEditorJson): array
    {
        if (empty($textEditorJson)) {
            return [];
        }
        
        $text_editors = json_decode($textEditorJson);
        if (!is_array($text_editors)) {
            return [];
        }
        
        $textOverlays = [];
        foreach ($text_editors as $textOverlay) {
            $textOverlayArray = is_object($textOverlay) ? (array) $textOverlay : $textOverlay;
            
            $styles = $textOverlayArray['styles'] ?? '';
            $text = $textOverlayArray['text'] ?? '';
            
            if (empty($text)) continue;
            
            $position = $this->parseCssPosition($styles);
            $fontProperties = $this->parseCssFont($styles);
            
            $textOverlays[] = [
                'text' => $text,
                'x' => $position['x'] ?? 0,
                'y' => $position['y'] ?? 0,
                'font_size' => $fontProperties['font_size'] ?? 40,
                'color' => $fontProperties['color'] ?? '#000000',
                'font_family' => $fontProperties['font_family'] ?? 'Arial',
            ];
        }
        
        return $textOverlays;
    }
    
    /**
     * Parse block size from imageDivDataMargin
     * Returns [cols, rows]
     */
    private function parseBlockSize(?string $imageDivDataMargin): array
    {
        // Remove quotes if present
        $imageDivDataMargin = trim($imageDivDataMargin ?? '', '"\'');
        
        if (empty($imageDivDataMargin) || $imageDivDataMargin === 'null') {
            return [1, 1]; // Single tile
        }
        
        $parts = explode('|', $imageDivDataMargin);
        if (count($parts) !== 2) {
            return [1, 1];
        }
        
        $cols = $this->totalCount(intval($parts[0]));
        $rows = $this->totalCount(intval($parts[1]));
        
        return [$cols, $rows];
    }
    
    /**
     * Parse position from imageDivStyle
     * Returns [col, row] (0-based grid position)
     */
    private function parsePositionFromStyle(string $style): array
    {
        // Extract left and top positions
        $left = 0;
        $top = 0;
        
        if (preg_match('/left:\s*(\d+)px/', $style, $matches)) {
            $left = intval($matches[1]);
        }
        
        if (preg_match('/top:\s*(\d+)px/', $style, $matches)) {
            $top = intval($matches[1]);
        }
        
        // Convert pixel position to grid position (approximately)
        // This is a rough estimate - adjust based on your grid tile size
        // Assuming editor tiles are roughly 93px x 82px
        $col = intval(round($left / 93));
        $row = intval(round($top / 82));
        
        return [$col, $row];
    }
    
    /**
     * Delete old print files
     */
    private function deleteOldPrintFiles(?string $imageWithBleedJson): void
    {
        if (empty($imageWithBleedJson)) {
            return;
        }
        
        $image_bleed = json_decode($imageWithBleedJson, true);
        if (!is_array($image_bleed)) {
            return;
        }
        
        $basePath = storage_path('app/public/');
        foreach ($image_bleed as $image) {
            if (is_string($image)) {
                $oldImagePath = $basePath . $image;
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                    Log::info("Deleted old print file", ['path' => $image]);
                }
            }
        }
    }

    
    public function totalCount($count)
    {
        switch ($count) {
            case 0:
                return 1;
            case 1:
                return 1;
            case 2:
                return 2;
            case 3:
                return 3;
            case 4:
                return 3;
            case 6:
                return 4;
            case 8:
                return 5;
            case 10:
                return 6;
            case 12:
                return 7;
            case 14:
                return 8;
            case 16:
                return 9;
            case 18:
                return 10;
            case 20:
                return 11;
            default:
                return 1;
        }
    }


    /**
     * Parse CSS position properties from a style string
     * @param string $styles CSS style string
     * @return array Position data ['x' => int, 'y' => int]
     */
    private function parseCssPosition(string $styles): array
    {
        $position = ['x' => 0, 'y' => 0];

        // Extract top position
        if (preg_match('/top:\s*(\d+)px/', $styles, $matches)) {
            $position['y'] = intval($matches[1]);
        }

        // Extract left position
        if (preg_match('/left:\s*(\d+)px/', $styles, $matches)) {
            $position['x'] = intval($matches[1]);
        }

        return $position;
    }

    /**
     * Parse CSS font properties from a style string
     * @param string $styles CSS style string
     * @return array Font properties
     */
    private function parseCssFont(string $styles): array
    {
        $fontProperties = [
            'font_size' => 16,
            'color' => '#000000',
            'font_family' => 'Arial'
        ];

        // Extract font size
        if (preg_match('/font-size:\s*(\d+)px/', $styles, $matches)) {
            $fontProperties['font_size'] = intval($matches[1]);
        }

        // Extract color
        if (preg_match('/color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $styles, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
            $fontProperties['color'] = sprintf('#%02x%02x%02x', $r, $g, $b);
        } elseif (preg_match('/color:\s*(#[0-9a-fA-F]{6})/', $styles, $matches)) {
            $fontProperties['color'] = $matches[1];
        }

        // Extract font family
        if (preg_match('/font-family:\s*([^;]+)/', $styles, $matches)) {
            $fontFamily = trim($matches[1]);
            // Take the first font family (before the first comma)
            $fontFamily = explode(',', $fontFamily)[0];
            $fontFamily = trim($fontFamily, " '\"");
            $fontProperties['font_family'] = $fontFamily;
        }

        return $fontProperties;
    }

    /**
     * Get font file path for a given font family
     * @param string $fontFamily Font family name
     * @return string|null Font file path or null if not found
     */
    private function getFontPath(string $fontFamily): ?string
    {
        // Common font directories
        $fontDirectories = [
            '/usr/share/fonts/',
            '/usr/local/share/fonts/',
            '/System/Library/Fonts/', // macOS
            'C:/Windows/Fonts/', // Windows
            storage_path('fonts/'), // Custom fonts directory
        ];

        // Font file extensions
        $extensions = ['ttf', 'otf', 'woff', 'woff2'];

        // Common font mappings
        $fontMappings = [
            'Arial' => ['arial.ttf', 'Arial.ttf', 'arial.ttc'],
            'Helvetica' => ['Helvetica.ttf', 'helvetica.ttf'],
            'Times New Roman' => ['times.ttf', 'Times.ttf', 'times.ttc'],
            'Georgia' => ['Georgia.ttf', 'georgia.ttf'],
            'Verdana' => ['verdana.ttf', 'Verdana.ttf'],
            'Courier New' => ['cour.ttf', 'Courier.ttf'],
        ];

        // Check if we have a mapping for this font family
        if (isset($fontMappings[$fontFamily])) {
            $fontFiles = $fontMappings[$fontFamily];
        } else {
            // Try to find font files with the family name
            $fontFiles = [
                strtolower($fontFamily) . '.ttf',
                $fontFamily . '.ttf',
                strtolower($fontFamily) . '.otf',
                $fontFamily . '.otf',
            ];
        }

        // Search for font files
        foreach ($fontDirectories as $directory) {
            if (is_dir($directory)) {
                foreach ($fontFiles as $fontFile) {
                    $fontPath = $directory . $fontFile;
                    if (file_exists($fontPath)) {
                        return $fontPath;
                    }
                }
            }
        }

        return null;
    }


}
