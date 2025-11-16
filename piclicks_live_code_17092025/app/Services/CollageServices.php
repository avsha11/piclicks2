<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Repository\Eloquent\{DesignCollageRepository};
use App\Repository\Eloquent\AppSettingRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\UploadImageTrait;
use App\Models\User;
use Exception;
use App\Mail\DemoMail;
use App\Repository\FrontEnd\Cart\CartRepository;
use App\Services\PrintFileService;
use Session;
use Carbon\Carbon;
// use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic as Image;


class CollageServices
{
    use UploadImageTrait;
    private $dataObject;
    protected $DesignCollageRepository, $AppSettingRepository, $CartRepository;
    public function __construct(
        DesignCollageRepository $DesignCollageRepository,
        CartRepository $CartRepository,
        // AppSettingRepository $AppSettingRepository
    ) {
        $this->dataObject = new \stdClass();
        $this->DesignCollageRepository = $DesignCollageRepository;
        $this->CartRepository = $CartRepository;
        // $this->AppSettingRepository = $AppSettingRepository;
    }


    public function saveImages($request)
    {
        // dd($request);
        try {
            DB::beginTransaction();

            $upload = 0;
            $unique_id = random_int(100000000, 999999999);

            if ($request->hasFile('files')) {

                if (isset($request->user_type) && $request->user_type === 'admin') {
                    $price_id = 2;
                } else {
                    $price_id = 1;
                }

                $data['unique_id'] = $unique_id;
                $data['price_id'] = $price_id;
                $data['grid_rows'] = 0;
                $data['grid_columns'] = 0;
                $data['width'] = 0;
                $data['height'] = 0;
                $data['total_tiles'] = 0;
                $data['frame'] = 0;
                $data['filter'] = '';
                $data['text_editor'] = json_encode([]);
                $data['user_type'] =  $request->user_type ?? 'user';
                $master = $this->DesignCollageRepository->createMaster($data);

                $uploadedFiles = $request->file('files');

                foreach ($uploadedFiles as $key => $file) {

                    $userdata['unique_id'] = $unique_id;
                    $userdata['image'] = $this->uploadImage($file, 'designCollageImages'); // used UploadImageTrait
                    $userdata['seq'] = $key + 1;
                    $userdata['empty'] = 0;
                    $userdata['is_deleted'] = 0;
                    $userdata['other_settings'] = json_encode([]);

                    $user =  $this->DesignCollageRepository->create($userdata);
                    $upload++;
                }

                $this->DesignCollageRepository->updateMaster(['id' => $master->id], ['total_tiles' => $upload]);
            }

            if ($upload > 0) {

                if (isset($request->user_type) && $request->user_type === 'admin') {
                    $photodata = [
                        'unique_id'         => $unique_id,
                        'collection_id'     => $request->collection_id,
                        'title'             => $request->title,
                        'short_description' => $request->short_description,
                        'designer_name'     => $request->designer_name,
                        'amount'            => $request->amount,
                        'tag_id'            => json_encode($request->input('tag_id')),

                    ];

                    $this->DesignCollageRepository->createdesignCollageAdmin($photodata);
                }

                DB::commit();

                return response()->json([
                    'message' => __('message.images_uploaded'),
                    'data' => [
                        'redirect_url' => $request->user_type == 'admin'
                            ? route('front.design-collage', ['unique_id' => $unique_id, 'user_type' => $request->user_type ?? 'user'])
                            : route('front.design-collage', ['unique_id' => $unique_id]),
                        'unique_id' => $unique_id,
                        'master' => $master
                    ],
                    'status' => 1,
                    'error' => $this->dataObject
                ], 201);
            } else {
                DB::rollBack();
                return response()->json(['message' => __('message.some_thing_went_wrong_tryagain'), 'data' => $this->dataObject, 'status' => 0], 500);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in CollageServices.saveImages(): " . $e->getMessage() . ". line:" . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero')]);
        }
    }
    public function updateCollage($request)
    {
        // dd($request);
        try {

            $photodata = [
                'collection_id'     => $request->collection_id,
                'title'             => $request->title,
                'short_description' => $request->short_description,
                'designer_name'     => $request->designer_name,
                'amount'            => $request->amount,
                'tag_id'            => json_encode($request->input('tag_id')),

            ];
            // dd($photodata);
            $this->DesignCollageRepository->updateCollageAdmin(['id' => $request->id], $photodata);

            return response()->json([
                'message' => __('message.collage'),
                'status' => 1,
                'error' => $this->dataObject
            ], 201);
        } catch (Exception $e) {
            Log::error("Error in CollageServices.updateCollage(): " . $e->getMessage() . ". line:" . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero')]);
        }
    }

    function saveCollage($request)
    {

        try {

            $master_data = $this->DesignCollageRepository->getOneMaster(['unique_id' => $request->unique_id]);

            // dd($request->all());
            $text_editor = [];
            if (!empty($request->text_editors)) {
                $text_editors = json_decode($request->text_editors);
                if (is_array($text_editors)) {
                    $text_editor = $text_editors;
                }
            }

            $imagePath = '';
            if (!empty($request->collage_image)) {
                // $imageFile = $request->file('collage_image');
                // $imageName = 'frame_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                // $imagePath = $imageFile->storeAs('public/designCollageImages', $imageName);
                $imagePath = $this->uploadImage($request->collage_image, 'designCollageImages'); // from UploadImageTrait

                if (!empty($imagePath) && $master_data->image_path) {
                    $userPath = storage_path('app/public/');
                    $oldImagePath = $userPath . ($master_data->image_path);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
            }

            $masterdata['grid_columns'] = $request->grid_columns;
            $masterdata['grid_rows'] = $request->grid_rows;
            $masterdata['width'] = $request->width;
            $masterdata['height'] = $request->height;
            $masterdata['frame'] = $request->frame;
            $masterdata['filter'] = $request->filter;
            $masterdata['text_editor'] = json_encode($text_editor);
            if (isset($request->user_type) && $request->user_type == 'user') {
                $masterdata['user_id'] = auth()->user()->id;
            } else {
                $masterdata['user_id'] = 1;
            }

            $masterdata['image_path'] = $imagePath;

            $this->DesignCollageRepository->updateMaster(['unique_id' => $request->unique_id], $masterdata);


            $updateIds = [];
            $total_tiles = 0;
            if (!empty($request->tiles)) {
                $tiles = json_decode($request->tiles);
                // dd($tiles);
                if (is_array($tiles)) {

                    $this->DesignCollageRepository->update(['unique_id' => $request->unique_id], ['is_deleted' => 1]);
                    foreach ($tiles as $key => $tile) {
                        // if($key === 0 || $key === 1) {
                        //     continue;
                        // }
                        $userdata = [];
                        $total_tiles_individual = 0;

                        $other['imageDivStyle'] = $tile->imageDivStyle;
                        $other['imageDivDataMargin'] = $tile->imageDivDataMargin;
                        $other['zoom'] = $tile->zoom;
                        $other['rotate'] = $tile->rotate;
                        $userdata['other_settings'] = json_encode($other);
                        $userdata['seq'] = $tile->seq;
                        $userdata['is_deleted'] = 0;

                        $img_orig = str_replace([asset('/'), 'storage/'], '', $tile->image_original);
                        
                        // More robust empty tile detection
                        $isEmpty = (
                            str_contains($img_orig, 'grey') || 
                            str_contains($img_orig, 'grey-back') ||
                            empty($img_orig) ||
                            $img_orig === 'assets/images/grey-back.png'
                        );
                        
                        if ($isEmpty) {
                            $userdata['empty'] = 1;
                        } else {
                            $userdata['empty'] = 0;


                            if (!empty($tile->image_edited)) {

                                if ($request->hasFile($tile->image_edited)) {

                                    // If image_edited is a file key (from FormData), get the uploaded file
                                    $file = $request->file($tile->image_edited);
                                    $imageName = 'collage_' . time() . '_' . uniqid();
                                    $image_edited = $this->uploadImageWithName($file, 'designCollageImages', $imageName); // from UploadImageTrait
                                    $userdata['image_edited'] = $image_edited;
                                } elseif (str_contains($tile->image_edited, 'data:image')) {

                                    // If image_edited is still a dataURL (fallback, should not happen if JS is correct)
                                    $base64Image = $tile->image_edited;
                                    $imageName = 'collage_' . time() . '_' . uniqid() . '.png';
                                    $image_edited = $this->uploadImageWithBase64($base64Image, 'designCollageImages', $imageName); // from UploadImageTrait
                                    $userdata['image_edited'] = $image_edited;
                                } else {

                                    // Otherwise, treat as a URL/path
                                    $userdata['image_edited'] = str_replace([asset('/'), 'storage/'], '', $tile->image_edited);
                                }
                            }


                            if (str_contains($img_orig, 'data:image')) {
                                $img_orig = 'designCollageImages/' . $imageName;
                            }

                            // Calculate actual tile span (how many tiles this image occupies)
                            if ($other['imageDivDataMargin'] == '' || $other['imageDivDataMargin'] == 'null' || $other['imageDivDataMargin'] == null) {
                                // Single tile image
                                $total_tiles++;
                                $total_tiles_individual = 1;
                            } else {
                                // Multi-tile image - parse margin data to calculate span
                                $tile_count_arr = explode('|', $other['imageDivDataMargin']);
                                if (is_array($tile_count_arr) && isset($tile_count_arr[1])) {
                                    // Calculate tile width and height from margin (Formula: tileSpan = (margin / 2) + 1)
                                    $marginW = floatval($tile_count_arr[0]);
                                    $marginH = floatval($tile_count_arr[1]);
                                    $tile_w = ($marginW > 0) ? ($marginW / 2) + 1 : 1;
                                    $tile_h = ($marginH > 0) ? ($marginH / 2) + 1 : 1;
                                    $tiles_wh = intval($tile_w * $tile_h);
                                    $total_tiles += $tiles_wh;
                                    $total_tiles_individual = $tiles_wh;
                                } else {
                                    // Fallback to single tile
                                    $total_tiles++;
                                    $total_tiles_individual = 1;
                                }
                            }
                        }
                        $userdata['image'] = $img_orig;
                        // dd($userdata);

                        if (empty($tile->id)) {


                            $userdata['unique_id'] = $request->unique_id;
                            $run = $this->DesignCollageRepository->create($userdata);
                            $upd_id = $run->id;
                        } else {
                            $this->DesignCollageRepository->update(['id' => $tile->id], $userdata);
                            $upd_id = $tile->id;
                        }
                        $updateIds[] = [
                            'image' => (!str_contains($img_orig, 'grey')) ? asset('storage/' . $img_orig) : asset($img_orig),
                            'image_edited' => (isset($userdata['image_edited']) ? asset('storage/' . $userdata['image_edited']) : null),
                            'seq' => $tile->seq,
                            'id' => $upd_id,
                            'total_tiles_individual' => $total_tiles_individual,
                            'image_with_bleed' => $userdata['image_with_bleed'] ?? '',
                        ];
                    }



                }
            }

            $this->DesignCollageRepository->updateMaster(['unique_id' => $request->unique_id], ['total_tiles' => $total_tiles]);

            // DEBUG: Log what type we received
            Log::info("CollageServices.saveCollage - Type check", [
                'unique_id' => $request->unique_id,
                'type_received' => $request->type ?? 'NULL',
                'will_generate_print_files' => in_array($request->type, ['preview', 'manual_admin']) ? 'YES' : 'NO'
            ]);

            // Generate print files if this is a preview/admin save
            if (in_array($request->type, ['preview', 'manual_admin'])) {
                Log::info("Generating print files for collage", ['unique_id' => $request->unique_id, 'type' => $request->type]);
                $this->generatePrintFilesForCollage($request->unique_id, $master_data);
            } else {
                Log::warning("Skipping print file generation - type mismatch", [
                    'received_type' => $request->type ?? 'NULL',
                    'expected' => "['preview', 'manual_admin']"
                ]);
            }

            return response()->json(['status' => 1, 'message' => 'Saved successfully', 'data' => ['updateIds' => $updateIds]]);
        } catch (Exception $e) {
            Log::error("Error in CollageServices.saveCollage(): " . $e->getMessage() . ". line:" . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero')]);
        }
    }

    // public function totalCount($count)
    // {
    //     switch ($count) {
    //         case 0:
    //             return 1;
    //         case 1:
    //             return 1;
    //         case 2:
    //             return 2;
    //         case 3:
    //             return 3;
    //         case 4:
    //             return 3;
    //         case 6:
    //             return 4;
    //         case 8:
    //             return 5;
    //         case 10:
    //             return 6;
    //         case 12:
    //             return 7;
    //         case 14:
    //             return 8;
    //         case 16:
    //             return 9;
    //         case 18:
    //             return 10;
    //         case 20:
    //             return 11;
    //         default:
    //             return 1;
    //     }
    // }

    // /**
    //  * Converts millimeters to pixels based on DPI.
    //  * @param float $mm Measurement in millimeters
    //  * @param int $dpi Dots per inch
    //  * @return int Pixels
    //  */
    // private function mmToPx(float $mm, int $dpi): int
    // {
    //     return (int) round(($mm / 25.4) * $dpi);
    // }

    // /**
    //  * Converts a hex color string to an RGB array.
    //  * @param string $hexColor Hex color string (e.g., "#FFFFFF")
    //  * @return array RGB array [r, g, b]
    //  */
    // private function hexToRgb(string $hexColor): array
    // {
    //     $hexColor = ltrim($hexColor, '#');
    //     if (strlen($hexColor) == 3) {
    //         $hexColor = $hexColor[0] . $hexColor[0] . $hexColor[1] . $hexColor[1] . $hexColor[2] . $hexColor[2];
    //     }
    //     return [
    //         hexdec($hexColor[0] . $hexColor[1]),
    //         hexdec($hexColor[2] . $hexColor[3]),
    //         hexdec($hexColor[4] . $hexColor[5]),
    //     ];
    // }

    // /**
    //  * Parse CSS position properties from a style string
    //  * @param string $styles CSS style string
    //  * @return array Position data ['x' => int, 'y' => int]
    //  */
    // private function parseCssPosition(string $styles): array
    // {
    //     $position = ['x' => 0, 'y' => 0];

    //     // Extract top position
    //     if (preg_match('/top:\s*(\d+)px/', $styles, $matches)) {
    //         $position['y'] = intval($matches[1]);
    //     }

    //     // Extract left position
    //     if (preg_match('/left:\s*(\d+)px/', $styles, $matches)) {
    //         $position['x'] = intval($matches[1]);
    //     }

    //     return $position;
    // }

    // /**
    //  * Parse CSS font properties from a style string
    //  * @param string $styles CSS style string
    //  * @return array Font properties
    //  */
    // private function parseCssFont(string $styles): array
    // {
    //     $fontProperties = [
    //         'font_size' => 16,
    //         'color' => '#000000',
    //         'font_family' => 'Arial'
    //     ];

    //     // Extract font size
    //     if (preg_match('/font-size:\s*(\d+)px/', $styles, $matches)) {
    //         $fontProperties['font_size'] = intval($matches[1]);
    //     }

    //     // Extract color
    //     if (preg_match('/color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $styles, $matches)) {
    //         $r = intval($matches[1]);
    //         $g = intval($matches[2]);
    //         $b = intval($matches[3]);
    //         $fontProperties['color'] = sprintf('#%02x%02x%02x', $r, $g, $b);
    //     } elseif (preg_match('/color:\s*(#[0-9a-fA-F]{6})/', $styles, $matches)) {
    //         $fontProperties['color'] = $matches[1];
    //     }

    //     // Extract font family
    //     if (preg_match('/font-family:\s*([^;]+)/', $styles, $matches)) {
    //         $fontFamily = trim($matches[1]);
    //         // Take the first font family (before the first comma)
    //         $fontFamily = explode(',', $fontFamily)[0];
    //         $fontFamily = trim($fontFamily, " '\"");
    //         $fontProperties['font_family'] = $fontFamily;
    //     }

    //     return $fontProperties;
    // }

    // /**
    //  * Get font file path for a given font family
    //  * @param string $fontFamily Font family name
    //  * @return string|null Font file path or null if not found
    //  */
    // private function getFontPath(string $fontFamily): ?string
    // {
    //     // Common font directories
    //     $fontDirectories = [
    //         '/usr/share/fonts/',
    //         '/usr/local/share/fonts/',
    //         '/System/Library/Fonts/', // macOS
    //         'C:/Windows/Fonts/', // Windows
    //         storage_path('fonts/'), // Custom fonts directory
    //     ];

    //     // Font file extensions
    //     $extensions = ['ttf', 'otf', 'woff', 'woff2'];

    //     // Common font mappings
    //     $fontMappings = [
    //         'Arial' => ['arial.ttf', 'Arial.ttf', 'arial.ttc'],
    //         'Helvetica' => ['Helvetica.ttf', 'helvetica.ttf'],
    //         'Times New Roman' => ['times.ttf', 'Times.ttf', 'times.ttc'],
    //         'Georgia' => ['Georgia.ttf', 'georgia.ttf'],
    //         'Verdana' => ['verdana.ttf', 'Verdana.ttf'],
    //         'Courier New' => ['cour.ttf', 'Courier.ttf'],
    //     ];

    //     // Check if we have a mapping for this font family
    //     if (isset($fontMappings[$fontFamily])) {
    //         $fontFiles = $fontMappings[$fontFamily];
    //     } else {
    //         // Try to find font files with the family name
    //         $fontFiles = [
    //             strtolower($fontFamily) . '.ttf',
    //             $fontFamily . '.ttf',
    //             strtolower($fontFamily) . '.otf',
    //             $fontFamily . '.otf',
    //         ];
    //     }

    //     // Search for font files
    //     foreach ($fontDirectories as $directory) {
    //         if (is_dir($directory)) {
    //             foreach ($fontFiles as $fontFile) {
    //                 $fontPath = $directory . $fontFile;
    //                 if (file_exists($fontPath)) {
    //                     return $fontPath;
    //                 }
    //             }
    //         }
    //     }

    //     return null;
    // }



    // // STEP BY STEP FUNCTION
    // private function smartModifyImage($relativeImagePath, $tiles_x, $tiles_y, $tileConfig)
    // {
    //     Log::info("smartModifyImage started with tiles_x: $tiles_x, tiles_y: $tiles_y");

    //     // Constants for print specifications
    //     $targetDpi = 300;
    //     $finalPhotoWidthMm = 143;
    //     $finalPhotoHeightMm = 126;
    //     $externalRadiusMm = 8;
    //     $frameThicknessMm = 8;
    //     $internalRadiusMm = 2;
    //     $bleedMm = 2;

    //     // Convert all measurements to pixels at 300 DPI
    //     $finalPhotoWidthPx = $this->mmToPx($finalPhotoWidthMm, $targetDpi);
    //     $finalPhotoHeightPx = $this->mmToPx($finalPhotoHeightMm, $targetDpi);
    //     $externalRadiusPx = $this->mmToPx($externalRadiusMm, $targetDpi);
    //     $frameThicknessPx = $this->mmToPx($frameThicknessMm, $targetDpi);
    //     $internalRadiusPx = $this->mmToPx($internalRadiusMm, $targetDpi);
    //     $bleedPx = $this->mmToPx($bleedMm, $targetDpi);

    //     Log::info("Print dimensions - Photo: {$finalPhotoWidthPx}x{$finalPhotoHeightPx}px, Frame: {$frameThicknessPx}px, Bleed: {$bleedPx}px");

    //     // Step 1: Load and prepare source image
    //     $basePath = public_path('storage/');
    //     $fullImagePath = $basePath . $relativeImagePath;

    //     if (!file_exists($fullImagePath)) {
    //         Log::error("Source image not found: $fullImagePath");
    //         return [false, []];
    //     }

    //     $sourceImage = imagecreatefromstring(file_get_contents($fullImagePath));
    //     if (!$sourceImage) {
    //         Log::error("Failed to load source image: $fullImagePath");
    //         return [false, []];
    //     }

    //     $originalWidth = imagesx($sourceImage);
    //     $originalHeight = imagesy($sourceImage);
    //     Log::info("Source image loaded: {$originalWidth}x{$originalHeight}");

    //     // Step 2: Crop to 1.14:1 aspect ratio (top-left alignment)
    //     $targetAspect = 1.14; // 143/126
    //     $srcAspect = $originalWidth / $originalHeight;

    //     if ($srcAspect > $targetAspect) {
    //         // Image is too wide, crop width
    //         $cropWidth = intval($originalHeight * $targetAspect);
    //         $cropHeight = $originalHeight;
    //         $cropX = 0;
    //         $cropY = 0;
    //     } else {
    //         // Image is too tall, crop height
    //         $cropWidth = $originalWidth;
    //         $cropHeight = intval($originalWidth / $targetAspect);
    //         $cropX = 0;
    //         $cropY = 0;
    //     }

    //     $croppedImg = imagecreatetruecolor($cropWidth, $cropHeight);
    //     imagecopy($croppedImg, $sourceImage, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);
    //     imagedestroy($sourceImage);

    //     // Step 3: Resize to final photo dimensions
    //     $photoImg = imagecreatetruecolor($finalPhotoWidthPx, $finalPhotoHeightPx);
    //     imagecopyresampled($photoImg, $croppedImg, 0, 0, 0, 0, $finalPhotoWidthPx, $finalPhotoHeightPx, $cropWidth, $cropHeight);
    //     imagedestroy($croppedImg);

    //     Log::info("Photo prepared: {$finalPhotoWidthPx}x{$finalPhotoHeightPx}px");

    //     // Step 4: Apply style filters
    //     if (!empty($tileConfig['filter'])) {
    //         $filter = $tileConfig['filter'];
    //         switch ($filter) {
    //             case 'filter-noir':
    //                 imagefilter($photoImg, IMG_FILTER_GRAYSCALE);
    //                 imagefilter($photoImg, IMG_FILTER_CONTRAST, -10);
    //                 break;
    //             case 'filter-stark':
    //                 imagefilter($photoImg, IMG_FILTER_CONTRAST, -15);
    //                 imagefilter($photoImg, IMG_FILTER_BRIGHTNESS, 5);
    //                 break;
    //             case 'filter-scandi':
    //                 imagefilter($photoImg, IMG_FILTER_COLORIZE, 20, 10, 0, 0);
    //                 break;
    //             case 'filter-capri':
    //                 imagefilter($photoImg, IMG_FILTER_COLORIZE, 0, 10, 25, 0);
    //                 break;
    //             case 'filter-nordic':
    //                 imagefilter($photoImg, IMG_FILTER_GRAYSCALE);
    //                 imagefilter($photoImg, IMG_FILTER_COLORIZE, 25, 20, 15, 0);
    //                 break;
    //             case 'filter-belveder':
    //                 imagefilter($photoImg, IMG_FILTER_GRAYSCALE);
    //                 imagefilter($photoImg, IMG_FILTER_COLORIZE, 90, 55, 30, 0);
    //                 break;
    //         }
    //         Log::info("Applied filter: $filter");
    //     }

    //     // Step 5: Create frame if needed
    //     $frameExists = $tileConfig['frame']['exists'] ?? false;
    //     $frameColorHex = $tileConfig['frame']['color_hex'] ?? '#000000';
    //     $frameColor = $this->hexToRgb($frameColorHex);

    //     if ($frameExists) {
    //         // Create framed image with 8mm frame
    //         $framedWidth = $finalPhotoWidthPx + ($frameThicknessPx * 2);
    //         $framedHeight = $finalPhotoHeightPx + ($frameThicknessPx * 2);

    //         $framedImg = imagecreatetruecolor($framedWidth, $framedHeight);
    //         $frameColorAllocated = imagecolorallocate($framedImg, $frameColor[0], $frameColor[1], $frameColor[2]);
    //         imagefilledrectangle($framedImg, 0, 0, $framedWidth, $framedHeight, $frameColorAllocated);

    //         // Place photo in center of frame
    //         imagecopy($framedImg, $photoImg, $frameThicknessPx, $frameThicknessPx, 0, 0, $finalPhotoWidthPx, $finalPhotoHeightPx);
    //         imagedestroy($photoImg);
    //         $photoImg = $framedImg;

    //         Log::info("Frame applied: {$framedWidth}x{$framedHeight}px");
    //     }

    //     // Step 6: Slice image if needed
    //     $tileExportPaths = [];

    //     if ($tiles_x === 1 && $tiles_y === 1) {
    //         // Single tile - no slicing needed
    //         $tiles = [[$photoImg, 0, 0]];
    //         Log::info("Single tile mode");
    //     } else {
    //         // Multiple tiles - slice the image
    //         $totalWidth = imagesx($photoImg);
    //         $totalHeight = imagesy($photoImg);
    //         $tileWidth = intval($totalWidth / $tiles_x);
    //         $tileHeight = intval($totalHeight / $tiles_y);

    //         $tiles = [];
    //         for ($row = 0; $row < $tiles_y; $row++) {
    //             for ($col = 0; $col < $tiles_x; $col++) {
    //                 $cropX = $col * $tileWidth;
    //                 $cropY = $row * $tileHeight;

    //                 $tileImg = imagecreatetruecolor($tileWidth, $tileHeight);
    //                 imagecopy($tileImg, $photoImg, 0, 0, $cropX, $cropY, $tileWidth, $tileHeight);
    //                 $tiles[] = [$tileImg, $row, $col];
    //             }
    //         }
    //         imagedestroy($photoImg);
    //         Log::info("Sliced into " . count($tiles) . " tiles");
    //     }

    //     // Step 7: Process each tile
    //     foreach ($tiles as $index => $tileData) {
    //         [$tileImg, $row, $col] = $tileData;
    //         $tileWidth = imagesx($tileImg);
    //         $tileHeight = imagesy($tileImg);

    //         // Determine tile position for frame and border radius
    //         $isTop = ($row === 0);
    //         $isBottom = ($row === $tiles_y - 1);
    //         $isLeft = ($col === 0);
    //         $isRight = ($col === $tiles_x - 1);
    //         $isCorner = ($isTop && $isLeft) || ($isTop && $isRight) || ($isBottom && $isLeft) || ($isBottom && $isRight);
    //         $isEdge = $isTop || $isBottom || $isLeft || $isRight;

    //         // Create final tile with bleed
    //         $finalWidth = $tileWidth + ($bleedPx * 2);
    //         $finalHeight = $tileHeight + ($bleedPx * 2);

    //         $finalImg = imagecreatetruecolor($finalWidth, $finalHeight);
    //         imagealphablending($finalImg, false);
    //         imagesavealpha($finalImg, true);
    //         $transparent = imagecolorallocatealpha($finalImg, 0, 0, 0, 127);
    //         imagefilledrectangle($finalImg, 0, 0, $finalWidth, $finalHeight, $transparent);

    //         // Copy tile to center of final image (with bleed)
    //         imagecopy($finalImg, $tileImg, $bleedPx, $bleedPx, 0, 0, $tileWidth, $tileHeight);

    //         // Apply frame segments for sliced images
    //         if ($frameExists && ($tiles_x > 1 || $tiles_y > 1)) {
    //             $frameColorAllocated = imagecolorallocate($finalImg, $frameColor[0], $frameColor[1], $frameColor[2]);

    //             // Draw frame segments based on position
    //             if ($isTop) {
    //                 imagefilledrectangle($finalImg, 0, 0, $finalWidth, $frameThicknessPx, $frameColorAllocated);
    //             }
    //             if ($isBottom) {
    //                 imagefilledrectangle($finalImg, 0, $finalHeight - $frameThicknessPx, $finalWidth, $finalHeight, $frameColorAllocated);
    //             }
    //             if ($isLeft) {
    //                 imagefilledrectangle($finalImg, 0, 0, $frameThicknessPx, $finalHeight, $frameColorAllocated);
    //             }
    //             if ($isRight) {
    //                 imagefilledrectangle($finalImg, $finalWidth - $frameThicknessPx, 0, $finalWidth, $finalHeight, $frameColorAllocated);
    //             }
    //         }

    //         // Apply border radius
    //         if ($isCorner || ($tiles_x === 1 && $tiles_y === 1)) {
    //             $this->applyRoundedCorners($finalImg, $externalRadiusPx);
    //         }

    //         // Apply inner radius for single tile with frame
    //         if ($frameExists && $tiles_x === 1 && $tiles_y === 1) {
    //             // Create inner mask for 2mm radius
    //             $innerMask = imagecreatetruecolor($finalWidth, $finalHeight);
    //             imagealphablending($innerMask, false);
    //             imagesavealpha($innerMask, true);
    //             $maskTransparent = imagecolorallocatealpha($innerMask, 0, 0, 0, 127);
    //             imagefilledrectangle($innerMask, 0, 0, $finalWidth, $finalHeight, $maskTransparent);

    //             // Fill inner area
    //             $innerX = $frameThicknessPx + $bleedPx;
    //             $innerY = $frameThicknessPx + $bleedPx;
    //             $innerW = $tileWidth - ($frameThicknessPx * 2);
    //             $innerH = $tileHeight - ($frameThicknessPx * 2);

    //             $maskOpaque = imagecolorallocate($innerMask, 255, 255, 255);
    //             imagefilledrectangle($innerMask, $innerX, $innerY, $innerX + $innerW, $innerY + $innerH, $maskOpaque);

    //             // Apply inner radius to mask
    //             $this->applyRoundedCorners($innerMask, $internalRadiusPx);

    //             // Apply mask to clear frame inner corners
    //             for ($x = 0; $x < $finalWidth; $x++) {
    //                 for ($y = 0; $y < $finalHeight; $y++) {
    //                     $maskColor = imagecolorat($innerMask, $x, $y);
    //                     if (($maskColor & 0xFF) === 255) {
    //                         $pixelColor = imagecolorat($finalImg, $x, $y);
    //                         $r = ($pixelColor >> 16) & 0xFF;
    //                         $g = ($pixelColor >> 8) & 0xFF;
    //                         $b = $pixelColor & 0xFF;

    //                         if ($r === $frameColor[0] && $g === $frameColor[1] && $b === $frameColor[2]) {
    //                             imagesetpixel($finalImg, $x, $y, imagecolorallocatealpha($finalImg, 0, 0, 0, 127));
    //                         }
    //                     }
    //                 }
    //             }
    //             imagedestroy($innerMask);
    //         }

    //         // Add text overlay if present
    //         if (!empty($tileConfig['text_overlays'])) {
    //             foreach ($tileConfig['text_overlays'] as $textOverlay) {
    //                 $text = $textOverlay['text'] ?? '';
    //                 $styles = $textOverlay['styles'] ?? '';

    //                 if (!empty($text) && !empty($styles)) {
    //                     $fontInfo = $this->parseCssFont($styles);
    //                     $positionInfo = $this->parseCssPosition($styles);

    //                     // Convert global position to tile-relative position
    //                     $tileGlobalX = $col * ($finalPhotoWidthPx + ($frameExists ? $frameThicknessPx * 2 : 0));
    //                     $tileGlobalY = $row * ($finalPhotoHeightPx + ($frameExists ? $frameThicknessPx * 2 : 0));

    //                     $textX = ($positionInfo['left'] ?? 0) - $tileGlobalX + $bleedPx;
    //                     $textY = ($positionInfo['top'] ?? 0) - $tileGlobalY + $bleedPx;

    //                     // Check if text is within this tile bounds
    //                     if ($textX >= -50 && $textX <= $finalWidth + 50 && $textY >= -50 && $textY <= $finalHeight + 50) {
    //                         $fontSize = $fontInfo['font-size'] ?? 40;
    //                         $fontColor = $fontInfo['color'] ?? 'rgb(0, 0, 0)';
    //                         $fontFamily = $fontInfo['font-family'] ?? 'Arial';

    //                         // Convert color
    //                         preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $fontColor, $colorMatches);
    //                         $textR = $colorMatches[1] ?? 0;
    //                         $textG = $colorMatches[2] ?? 0;
    //                         $textB = $colorMatches[3] ?? 0;

    //                         $textColor = imagecolorallocate($finalImg, $textR, $textG, $textB);

    //                         // Get font path
    //                         $fontPath = $this->getFontPath($fontFamily);

    //                         if ($fontPath && file_exists($fontPath)) {
    //                             // Scale font size for print
    //                             $printFontSize = $fontSize * 0.8; // Adjust for print
    //                             imagettftext($finalImg, $printFontSize, 0, $textX, $textY, $textColor, $fontPath, $text);
    //                         } else {
    //                             // Fallback to built-in font
    //                             imagestring($finalImg, 5, $textX, $textY, $text, $textColor);
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         // Save tile
    //         $saveFileName = '';
    //         if ($tiles_x === 1 && $tiles_y === 1) {
    //             $saveFileName = 'designCollageImages/tile_' . time() . '_' . uniqid() . '.png';
    //         } else {
    //             $saveFileName = 'designCollageImages/tile_r' . ($row + 1) . '_c' . ($col + 1) . '_' . time() . '_' . uniqid() . '.png';
    //         }

    //         $saveFullPath = $basePath . $saveFileName;
    //         imagepng($finalImg, $saveFullPath);
    //         imagedestroy($finalImg);
    //         imagedestroy($tileImg);

    //         $tileExportPaths[] = $saveFileName;
    //         Log::info("Saved tile $index: $saveFileName");
    //     }

    //     Log::info("smartModifyImage completed - exported " . count($tileExportPaths) . " tiles");
    //     return [true, $tileExportPaths];
    // }

    // private function applyRoundedCorners(&$img, int $radiusPx, $unusedBackgroundColor = null)
    // {
    //     $w = imagesx($img);
    //     $h = imagesy($img);

    //     // Build an opaque mask of the rounded rectangle
    //     $mask = imagecreatetruecolor($w, $h);
    //     imagealphablending($mask, false);
    //     imagesavealpha($mask, true);
    //     $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
    //     imagefilledrectangle($mask, 0, 0, $w, $h, $transparent);

    //     $opaque = imagecolorallocatealpha($mask, 0, 0, 0, 0); // opaque

    //     // Draw rounded rectangle into mask
    //     // Center rectangle
    //     imagefilledrectangle($mask, $radiusPx, 0, $w - $radiusPx, $h, $opaque);
    //     imagefilledrectangle($mask, 0, $radiusPx, $w, $h - $radiusPx, $opaque);
    //     // Four corners as filled circles
    //     imagefilledellipse($mask, $radiusPx, $radiusPx, $radiusPx * 2, $radiusPx * 2, $opaque);
    //     imagefilledellipse($mask, $w - $radiusPx, $radiusPx, $radiusPx * 2, $radiusPx * 2, $opaque);
    //     imagefilledellipse($mask, $radiusPx, $h - $radiusPx, $radiusPx * 2, $radiusPx * 2, $opaque);
    //     imagefilledellipse($mask, $w - $radiusPx, $h - $radiusPx, $radiusPx * 2, $radiusPx * 2, $opaque);

    //     // Compose: copy only pixels where mask is opaque
    //     $result = imagecreatetruecolor($w, $h);
    //     imagealphablending($result, false);
    //     imagesavealpha($result, true);
    //     $dstTransparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
    //     imagefilledrectangle($result, 0, 0, $w, $h, $dstTransparent);

    //     for ($y = 0; $y < $h; $y++) {
    //         for ($x = 0; $x < $w; $x++) {
    //             $alpha = (imagecolorat($mask, $x, $y) & 0x7F000000) >> 24; // GD stores alpha in high 7 bits
    //             if ($alpha === 0) { // opaque area
    //                 $col = imagecolorat($img, $x, $y);
    //                 imagesetpixel($result, $x, $y, $col);
    //             }
    //         }
    //     }

    //     // Replace
    //     imagecopy($img, $result, 0, 0, 0, 0, $w, $h);
    //     imagedestroy($mask);
    //     imagedestroy($result);
    // }

    // private function drawFilledRoundedRect($img, int $x, int $y, int $w, int $h, int $r, int $color): void
    // {
    //     // Middle rectangles
    //     imagefilledrectangle($img, $x + $r, $y, $x + $w - $r, $y + $h, $color);
    //     imagefilledrectangle($img, $x, $y + $r, $x + $w, $y + $h - $r, $color);
    //     // Four corner circles
    //     imagefilledellipse($img, $x + $r, $y + $r, $r * 2, $r * 2, $color);
    //     imagefilledellipse($img, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
    //     imagefilledellipse($img, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
    //     imagefilledellipse($img, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    // }


    // refresh product item
    function refreshProductItem($request)
    {

        try {


            if ($request->priceId == 1) {
                $newPriceId = 4;
            } elseif ($request->priceId == 4) {
                $newPriceId = 1;
            } elseif ($request->priceId == 2) {
                $newPriceId = 5;
            } elseif ($request->priceId == 5) {
                $newPriceId = 2;
            }

            if ($request->type == 'preview') {
                $run = $this->DesignCollageRepository->updateMaster(['id' => $request->productId], ['price_id' => $newPriceId]);
            } else {
                $run = $this->CartRepository->update(['id' => $request->productId], ['price_id' => $newPriceId]);
            }

            if ($run) {
                return response()->json(['status' => 1, 'message' => 'Collage updated successfully', 'data' => null]);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something Went Wrong Please Try again', 'data' => null]);
            }
        } catch (Exception $e) {
            Log::error("Error in CollageServices.refreshProductItem(): " . $e->getMessage() . ". line:" . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero')]);
        }
    }

    public function artGalleryEdit($unique_id, $master, $is_artgallery = 0)
    {
        // THIS FUNCTION IS USED FROM COLLAGE PREVIEW PAGE, USER PANEL REORDER BUTTON, SUCCESS CHECKOUT CARTSERVICE
        $new_unique_id = rand(1000000000, 9999999999);

        try {


            DB::beginTransaction();

            $adminData = DB::table('design_collage_admins')->where('unique_id', $unique_id)->first();
            // $amount = $adminData->amount ?? 0;

            // Duplicate master
            $new_master = (array) $master;
            unset($new_master['id'], $new_master['created_at'], $new_master['updated_at']);
            $new_master['unique_id'] = $new_unique_id;
            $new_master['price_id'] = 2;
            $new_master['status'] = 0;
            $new_master['user_id'] = auth()->user()->id;
            if ($is_artgallery === 1) {
                $new_master['artgallery_unique_id'] = $unique_id;
            }
            $new_master['user_type'] = 'user';
            $new_master['created_at'] = $new_master['updated_at'] = Carbon::now();
            DB::table('design_collage_master')->insert($new_master);

            // Duplicate images
            $collageImages = DB::table('design_collage')->where('unique_id', $unique_id)->get();

            foreach ($collageImages as $image) {
                $new_image = (array) $image;
                unset($new_image['id'], $new_image['created_at'], $new_image['updated_at']);
                $new_image['unique_id'] = $new_unique_id;

                // Handle image copy if not placeholder
                foreach (['image', 'image_edited'] as $col) {
                    if (!empty($image->$col) && $image->$col !== 'assets/images/grey-back.png') {
                        $oldPath = public_path($image->$col);
                        if (File::exists($oldPath)) {
                            $extension = pathinfo($image->$col, PATHINFO_EXTENSION);
                            $newFilename = time() . rand(99, 1000) . '.' . $extension;
                            $newPath = 'designCollageImages/' . $newFilename;
                            File::copy($oldPath, public_path($newPath));
                            $new_image[$col] = $newPath;
                        }
                    }
                }

                $new_image['created_at'] = $new_image['updated_at'] = Carbon::now();
                DB::table('design_collage')->insert($new_image);
            }

            DB::commit();

            return [1, '', $new_unique_id];
        } catch (Exception $e) {
            Log::error("Error in CollageServices.artGalleryEdit(): " . $e->getMessage() . ". line:" . $e->getLine());
            return [0, __('message.statusZero'), $new_unique_id];
        }
    }

    /**
     * Generate print files for all tiles in a collage
     * This is called when saving before preview to ensure print files are ready
     */
    private function generatePrintFilesForCollage($unique_id, $masterdata)
    {
        try {
            Log::info("=== generatePrintFilesForCollage started ===", ['unique_id' => $unique_id]);
            
            // Get all non-empty, non-deleted tiles for this collage
            $tiles_res = $this->DesignCollageRepository->getByWhere([
                'unique_id' => $unique_id, 
                'empty' => 0, 
                'is_deleted' => 0
            ], ['seq' => 'asc']);
            
            if ($tiles_res->isEmpty()) {
                Log::warning("No tiles found for collage", ['unique_id' => $unique_id]);
                return;
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
                
                // Skip if no image_edited
                if (empty($tile->image_edited) || !File::exists(storage_path('app/public/' . $tile->image_edited))) {
                    Log::warning("Skipping tile - image_edited not found", [
                        'tile_id' => $tile->id,
                        'image_edited' => $tile->image_edited ?? 'N/A'
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
                
                // Filter and adjust text overlays for this specific tile
                $tileTextOverlays = $this->getTextOverlaysForTile(
                    $textOverlays,
                    $startCol,
                    $startRow,
                    $cols,
                    $rows,
                    $masterdata
                );
                
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
                    'text_overlays' => $tileTextOverlays, // Only text relevant to this tile
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
            
            Log::info("=== generatePrintFilesForCollage completed ===", [
                'unique_id' => $unique_id,
                'processed' => $processedCount,
                'total_tiles' => $tiles_res->count()
            ]);
            
        } catch (Exception $e) {
            Log::error('Error in CollageServices/generatePrintFilesForCollage', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function generatePrintFilesForCollageAsync($unique_id, $masterdata)
    {
        return $this->generatePrintFilesForCollage($unique_id, $masterdata);
    }
    
    /**
     * Get text overlays that appear on a specific tile
     * Adjusts global text positions to be relative to the tile
     */
    private function getTextOverlaysForTile(
        array $globalTextOverlays,
        int $tileStartCol,
        int $tileStartRow,
        int $tileCols,
        int $tileRows,
        $masterdata
    ): array {
        if (empty($globalTextOverlays)) {
            return [];
        }
        
        // Get grid dimensions from masterdata
        $gridColumns = intval($masterdata->grid_columns ?? 5);
        $gridRows = intval($masterdata->grid_rows ?? 5);
        
        // Editor tile metrics sourced from tool.js constants
        $editorTileWidth = 91.0;
        $editorTileHeight = 80.0;
        $editorTileMargin = 2.0;
        
        // Calculate this tile's bounding box in global editor coordinates
        $tileLeft = $tileStartCol * ($editorTileWidth + $editorTileMargin);
        $tileTop = $tileStartRow * ($editorTileHeight + $editorTileMargin);
        $tileRight = $tileLeft + ($tileCols * $editorTileWidth) + (max($tileCols - 1, 0) * $editorTileMargin);
        $tileBottom = $tileTop + ($tileRows * $editorTileHeight) + (max($tileRows - 1, 0) * $editorTileMargin);
        
        Log::info("Tile bounding box in editor coordinates", [
            'tile_position' => "col:{$tileStartCol}, row:{$tileStartRow}",
            'tile_size' => "{$tileCols}x{$tileRows}",
            'bounds' => "left:{$tileLeft}, top:{$tileTop}, right:{$tileRight}, bottom:{$tileBottom}"
        ]);
        
        $tileTextOverlays = [];
        
        foreach ($globalTextOverlays as $textOverlay) {
            $textX = floatval($textOverlay['x'] ?? 0);
            $textY = floatval($textOverlay['y'] ?? 0);
            $fontSize = floatval($textOverlay['font_size'] ?? 40);
            $text = $textOverlay['text'] ?? '';
            $translateX = $textOverlay['translate_x'] ?? '-50%';
            $translateY = $textOverlay['translate_y'] ?? '-50%';
            
            if ($text === '') {
                continue;
            }
            
            // Estimate text bounding box in editor space
            $textWidth = strlen($text) * $fontSize * 0.6;
            $textHeight = $fontSize;
            
            $translateXPx = $this->convertTranslateToPixels($translateX, $textWidth, $fontSize);
            $translateYPx = $this->convertTranslateToPixels($translateY, $textHeight, $fontSize);
            
            // Account for translate() shifting the element before deriving the center point
            $centerX = $textX + $translateXPx + ($textWidth / 2);
            $centerY = $textY + $translateYPx + ($textHeight / 2);
            $topLeftX = $centerX - ($textWidth / 2);
            $topLeftY = $centerY - ($textHeight / 2);
            
            $textRight = $topLeftX + $textWidth;
            $textBottom = $topLeftY + $textHeight;
            
            // Check if text bounding box intersects with tile bounding box
            $intersects = !(
                $textRight < $tileLeft ||    // Text is completely to the left
                $topLeftX > $tileRight ||        // Text is completely to the right
                $textBottom < $tileTop ||     // Text is completely above
                $topLeftY > $tileBottom          // Text is completely below
            );
            
            if ($intersects) {
                // Adjust text position to be relative to this tile (not global)
                $adjustedTextOverlay = $textOverlay;
                $adjustedTextOverlay['x'] = $centerX - $tileLeft;
                $adjustedTextOverlay['y'] = $centerY - $tileTop;
                // We've already accounted for translate in the center position
                $adjustedTextOverlay['translate_x'] = 0;
                $adjustedTextOverlay['translate_y'] = 0;
                
                Log::info("Text overlay included on this tile", [
                    'text' => substr($text, 0, 20),
                    'global_pos' => "x:{$centerX}, y:{$centerY}",
                    'translate_px' => "x:{$translateXPx}, y:{$translateYPx}",
                    'tile_relative_pos' => "x:{$adjustedTextOverlay['x']}, y:{$adjustedTextOverlay['y']}",
                    'tile' => "col:{$tileStartCol}, row:{$tileStartRow}"
                ]);
                
                $tileTextOverlays[] = $adjustedTextOverlay;
            } else {
                Log::info("Text overlay excluded from this tile (no intersection)", [
                    'text' => substr($text, 0, 20),
                    'text_bounds' => "x:{$topLeftX}-{$textRight}, y:{$topLeftY}-{$textBottom}",
                    'tile_bounds' => "x:{$tileLeft}-{$tileRight}, y:{$tileTop}-{$tileBottom}",
                    'tile' => "col:{$tileStartCol}, row:{$tileStartRow}"
                ]);
            }
        }
        
        return $tileTextOverlays;
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
                'rotation' => $fontProperties['rotation'] ?? 0,
                'translate_x' => $fontProperties['translate_x'] ?? '-50%',
                'translate_y' => $fontProperties['translate_y'] ?? '-50%',
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
    
    /**
     * Parse CSS position properties from a style string
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
     */
    private function parseCssFont(string $styles): array
    {
        $fontProperties = [
            'font_size' => 16,
            'color' => '#000000',
            'font_family' => 'Arial',
            'rotation' => 0,
            'translate_x' => '-50%',
            'translate_y' => '-50%',
        ];

        if (preg_match('/font-size:\s*(\d+(?:\.\d+)?)px/', $styles, $matches)) {
            $fontProperties['font_size'] = floatval($matches[1]);
        }

        if (preg_match('/transform:[^;]*rotate\(([^)]+)\)/', $styles, $matches)) {
            $rotation = floatval(trim($matches[1], 'deg'));
            $fontProperties['rotation'] = $rotation;
        }

        if (preg_match('/transform:[^;]*translate\(([^)]+)\)/', $styles, $matches)) {
            $parts = array_map('trim', explode(',', $matches[1]));
            if (count($parts) >= 2) {
                $fontProperties['translate_x'] = $parts[0];
                $fontProperties['translate_y'] = $parts[1];
            }
        }

        if (preg_match('/color:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $styles, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
            $fontProperties['color'] = sprintf('#%02x%02x%02x', $r, $g, $b);
        } elseif (preg_match('/color:\s*(#[0-9a-fA-F]{6})/', $styles, $matches)) {
            $fontProperties['color'] = $matches[1];
        }

        if (preg_match('/font-family:\s*([^;]+)/', $styles, $matches)) {
            $fontFamily = trim(explode(',', $matches[1])[0]);
            $fontFamily = trim($fontFamily, " '\"");
            $fontProperties['font_family'] = $fontFamily;
        }

        return $fontProperties;
    }

    /**
     * Convert margin/tile count value to actual tile count
     */
    private function totalCount($count)
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

    private function convertTranslateToPixels($value, float $referenceSize, float $fontSize): float
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return floatval($value);
        }

        $value = trim((string) $value, " \"'");

        if (str_ends_with($value, '%')) {
            $percent = floatval(rtrim($value, '%'));
            $reference = $referenceSize > 0 ? $referenceSize : $fontSize;
            return ($percent / 100.0) * $reference;
        }

        if (str_ends_with($value, 'px')) {
            return floatval(rtrim($value, 'px'));
        }

        return 0.0;
    }
}


