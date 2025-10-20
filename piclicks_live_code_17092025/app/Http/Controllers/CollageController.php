<?php

namespace App\Http\Controllers;
// phpinfo();die;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Requests\{SaveUploadPhotosRequest, CollageRequest};
use App\Services\CollageServices;
use App\Services\Admin\FrameServices;
use App\Repository\Eloquent\DesignCollageRepository;
use App\Repository\Admin\{CollectionRepository, TagRepository};
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\DB;

class CollageController extends Controller
{
    protected $CollageServices, $DesignCollageRepository, $FrameServices, $TagRepository, $CollectionRepository;
    public function __construct(TagRepository $TagRepository, CollectionRepository $CollectionRepository, CollageServices $CollageServices, DesignCollageRepository $DesignCollageRepository, FrameServices $frameServices)
    {
        $this->CollageServices = $CollageServices;
        $this->DesignCollageRepository = $DesignCollageRepository;
        $this->FrameServices = $frameServices;
        $this->CollectionRepository = $CollectionRepository;
        $this->TagRepository = $TagRepository;
    }

    public function uploadPhotos()
    {
        try {
            return view('front.upload-photos');
        } catch (\Exception $e) {
            Log::error('Error in CollageController/uploadPhotos :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    // public function saveUploadPhotos(Request $request)
    public function saveUploadPhotos(SaveUploadPhotosRequest $request)
    {
        try {
            return $this->CollageServices->saveImages($request);
        } catch (\Exception $e) {
            Log::error('Error in CollageController/uploadPhotos :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function updateCollage(CollageRequest $request)
    {
        try {
            return $this->CollageServices->updateCollage($request);
        } catch (\Exception $e) {
            Log::error('Error in CollageController/updateCollage :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function designCollage(Request $request)
    {
        try {
            $unique_id = $request->unique_id;

            $images = $this->DesignCollageRepository->getByWhere(['unique_id' => $unique_id, 'is_deleted' => 0], ['seq' => 'asc'])->toArray();
            // dd($images);
            $master = $this->DesignCollageRepository->getOneMaster(['unique_id' => $unique_id, 'status' => 0]);
            if (empty($master) || empty($images)) {
                return redirect()
                    ->route('front.upload-photos')
                    ->with(['images_empty_error' => 'Collage not found, try again.']);
            }

            $user_type = $master['user_type'] ?? 'user';
            if ($master['user_type'] == 'admin' && !auth('admins')->check()) {
                return redirect()
                    ->route('admin.uploadPhotos', ['type' => 'gallery'])
                    ->with(['error_message' => 'Admin authentication required.']);
            }

            if ($master->grid_rows <= 0 || $master->grid_columns <= 0) {
                $grid_images = [];
                $imagesCount = count($images);
                $grid_columns = 0;
                $grid_rows = 0;
                $totalSlots = 0;

                $placeholdersNeeded = 0;
                $placeholder_image = asset('assets/images/grey-back.png');

                // logic to make grid according to images count
                if ($imagesCount <= 4) {
                    $grid_columns = 2;
                    $grid_rows = 2;
                } elseif ($imagesCount <= 9) {
                    $grid_columns = 3;
                    $grid_rows = 3;
                    // $emptyPlaceholders = [1, 3, 7, 9];
                    // $keepIn = [8,12,15,16]; // can place in 13,14
                } else {
                    $grid_columns = ceil(sqrt($imagesCount));
                    $grid_rows = $grid_columns;
                }
                $totalSlots = $grid_columns * $grid_rows;

                // according to 3*3 grid replace: 3:8, 9:12
                // $remove = [($grid_columns - 1), ((($grid_columns + 1) * ($grid_rows - 1)) + 1) - 1];
                // $replace = [(($grid_columns + 1) * 2) - 1, (($grid_columns + 1) * 3) - 1];

                $grid_images[] = [
                    'id' => 0,
                    'empty' => 1,
                    'image' => $placeholder_image,
                ];
                // dd($grid_images);
                foreach ($images as $item) {
                    $grid_images[] = [
                        'id' => $item['id'],
                        'empty' => $item['empty'],
                        'image' => asset('storage/' . $item['image']),
                    ];
                }

                $placeholdersNeeded = $totalSlots - $imagesCount - 1;
                for ($i = 0; $i < $placeholdersNeeded; $i++) {
                    $grid_images[] = [
                        'id' => 0,
                        'empty' => 1,
                        'image' => $placeholder_image,
                    ];
                }

                // logic to add empty lines on right and in bottom
                $new_grid_images = [];
                foreach ($grid_images as $index => $image) {
                    $new_grid_images[] = $image;

                    // Add a new placeholder at the end of each row
                    if (($index + 1) % $grid_columns === 0) {
                        $new_grid_images[] = [
                            'id' => 0,
                            'empty' => 1,
                            'image' => $placeholder_image,
                        ];
                    }
                }
                $grid_columns++;
                for ($i = 1; $i <= $grid_columns; $i++) {
                    $new_grid_images[] = [
                        'id' => 0,
                        'empty' => 1,
                        'image' => $placeholder_image,
                    ];
                }
                $grid_rows++;
                $totalSlots = $grid_columns * $grid_rows;

                //update the grid in database
                // dd($new_grid_images, asset('/'));
                foreach ($new_grid_images as $key => $val) {
                    $userdata = [];
                    $userdata['image'] = str_replace([asset('/'), 'storage/'], '', $val['image']);
                    $userdata['seq'] = $key + 1;
                    $userdata['empty'] = $val['empty'];

                    if ($val['id'] === 0) {
                        $userdata['unique_id'] = $unique_id;
                        $userdata['other_settings'] = json_encode([]);

                        $this->DesignCollageRepository->create($userdata);
                    }
                    $this->DesignCollageRepository->update(['id' => $val['id']], $userdata);
                }
                $masterdata['grid_rows'] = $grid_rows;
                $masterdata['grid_columns'] = $grid_columns;
                $this->DesignCollageRepository->updateMaster(['unique_id' => $unique_id], $masterdata);

                $master = $this->DesignCollageRepository->getOneMaster(['unique_id' => $unique_id]);
                $images = $this->DesignCollageRepository->getByWhere(['unique_id' => $unique_id, 'is_deleted' => 0], ['seq' => 'asc'])->toArray();

                // logic swap images
                // foreach ($remove as $index => $keyToRemove) {
                //     $keyToReplace = $replace[$index];

                //     // Check if both keys exist in the array
                //     if (isset($new_grid_images[$keyToRemove]) && isset($new_grid_images[$keyToReplace])) {
                //         // Swap the data between the keys
                //         $temp = $new_grid_images[$keyToRemove];
                //         $new_grid_images[$keyToRemove] = $new_grid_images[$keyToReplace];
                //         $new_grid_images[$keyToReplace] = $temp;
                //     }
                // }
                // dd($new_grid_images, $remove, $replace);

                // $grid_images = $new_grid_images;
                // unset($grid_images[21]);
                // unset($grid_images[22]);
                // unset($grid_images[23]);
                // unset($grid_images[24]);
                // echo "inside";
            }
            // dd($images);
            // dd($images, $grid_columns, $grid_rows);

            // Calculate the correct number of occupied tiles for display
            $occupiedTilesCount = $this->calculateOccupiedTiles($images);
            
            // Update the total_tiles in the master data to show correct count
            $master['total_tiles'] = $occupiedTilesCount;
            
            // Log for debugging
            Log::info("Design collage page tile count", [
                'unique_id' => $unique_id,
                'occupied_tiles' => $occupiedTilesCount,
                'total_images' => count($images)
            ]);

            $response = $this->FrameServices->getAllService();
            $framService = collect(json_decode($response->getContent(), true)['data']);

            return view('front.design-collage', compact('unique_id', 'master', 'images', 'framService', 'user_type'));
        } catch (\Exception $e) {
            dd($e->getMessage() . 'in line' . $e->getLine());
            Log::error('Error in CollageController/designCollage :' . $e->getMessage() . 'in line' . $e->getLine());
            return redirect()
                ->route('front.upload-photos')
                ->with(['message_error' => __('message.statusZero')]);
        }
    }

    // public function designCollage_2($unique_id)
    // {
    //     try {
    //         $images = $this->DesignCollageRepository->getByWhere(['unique_id' => $unique_id])->toArray();
    //         if (empty($images)) {
    //             return redirect()->route('front.upload-photos')->with(['images_empty_error' => 'Minimum 4 images needed to continue']);
    //         }
    //         // dd($images);
    //         $imagesCount = count($images);
    //         $grid_columns = 0;
    //         $grid_rows = 0;
    //         $totalSlots = 0;
    //         $placeholdersNeeded = 0;
    //         $placeholder_image = asset('assets/images/grey-back.png');

    //         // logic to make grid according to images count
    //         if ($imagesCount <= 4) {
    //             $grid_columns = 2;
    //             $grid_rows = 2;
    //         } elseif ($imagesCount <= 9) {
    //             $grid_columns = 3;
    //             $grid_rows = 3;
    //             // $emptyPlaceholders = [1, 3, 7, 9];
    //             // $keepIn = [8,12,15,16]; // can place in 13,14
    //         } else {
    //             $grid_columns = ceil(sqrt($imagesCount));
    //             $grid_rows = $grid_columns;
    //         }
    //         $totalSlots = $grid_columns * $grid_rows;

    //         // according to 3*3 grid replace: 3:8, 9:12
    //         // $remove = [($grid_columns - 1), ((($grid_columns + 1) * ($grid_rows - 1)) + 1) - 1];
    //         // $replace = [(($grid_columns + 1) * 2) - 1, (($grid_columns + 1) * 3) - 1];

    //         $grid_images = [];
    //         $grid_images[] = [
    //             'empty' => 1,
    //             'image' => $placeholder_image,
    //         ];
    //         foreach ($images as $item) {
    //             $grid_images[] = [
    //                 'empty' => 0,
    //                 'image' => asset('storage/' . $item['image']),
    //             ];
    //         }

    //         $placeholdersNeeded = $totalSlots - $imagesCount - 1;
    //         for ($i = 0; $i < $placeholdersNeeded; $i++) {
    //             $grid_images[] = [
    //                 'empty' => 1,
    //                 'image' => $placeholder_image,
    //             ];
    //         }

    //         // logic to add empty lines on right and in bottom
    //         $new_grid_images = [];
    //         foreach ($grid_images as $index => $image) {
    //             $new_grid_images[] = $image;

    //             // Add a new placeholder at the end of each row
    //             if (($index + 1) % ($grid_columns) === 0) {
    //                 $new_grid_images[] = [
    //                     'empty' => 1,
    //                     'image' => $placeholder_image,
    //                 ];
    //             }
    //         }
    //         $grid_columns++;
    //         for ($i = 1; $i <= $grid_columns; $i++) {
    //             $new_grid_images[] = [
    //                 'empty' => 1,
    //                 'image' => $placeholder_image,
    //             ];
    //         }
    //         $grid_rows++;

    //         // logic swap images
    //         // foreach ($remove as $index => $keyToRemove) {
    //         //     $keyToReplace = $replace[$index];

    //         //     // Check if both keys exist in the array
    //         //     if (isset($new_grid_images[$keyToRemove]) && isset($new_grid_images[$keyToReplace])) {
    //         //         // Swap the data between the keys
    //         //         $temp = $new_grid_images[$keyToRemove];
    //         //         $new_grid_images[$keyToRemove] = $new_grid_images[$keyToReplace];
    //         //         $new_grid_images[$keyToReplace] = $temp;
    //         //     }
    //         // }
    //         // dd($new_grid_images, $remove, $replace);

    //         $grid_images = $new_grid_images;

    //         return view('front.design-collage_2', compact(
    //             'unique_id',
    //             'grid_images',
    //             'grid_columns',
    //             'grid_rows'
    //         ));
    //     } catch (\Exception $e) {
    //         dd($e->getMessage() . 'in line' . $e->getLine());
    //         Log::error('Error in CollageController/designCollage :' . $e->getMessage() . 'in line' . $e->getLine());
    //         return redirect()->route('front.upload-photos')->with(['message_error' => __('message.statusZero')]);
    //     }
    // }

    function saveCollage(Request $request)
    {
        try {
            if (isset($request->user_type) && $request->user_type == 'user') {
                if (!auth()->check()) {
                    if ($request->type == 'auto') {
                        return response()->json(['status' => 1, 'message' => 'Kindly sign-up or sign-in.', 'data' => ['login' => 1]]);
                    } elseif ($request->type == 'manual') {
                        return response()->json(['status' => 0, 'message' => 'Kindly sign-up or sign-in.', 'data' => ['login' => 1]]);
                    } elseif ($request->type == 'preview') {
                        return response()->json(['status' => 0, 'message' => 'Kindly sign-up or sign-in !', 'data' => ['login' => 1]]);
                    } else {
                        return response()->json(['status' => 0, 'message' => '', 'data' => ['login' => 1]]);
                    }
                }
            } else {
                if (!auth('admins')->check()) {
                    if ($request->type == 'auto') {
                        return response()->json(['status' => 1, 'message' => 'Admin authentication required.']);
                    } else {
                        return response()->json(['status' => 0, 'message' => 'Admin authentication required.']);
                    }
                }
            }

            return $this->CollageServices->saveCollage($request);
        } catch (\Exception $e) {
            Log::error('Error in CollageController/saveCollage :' . $e->getMessage() . 'in line' . $e->getLine());
        }
    }

    // preview design collage

    function previewDesignCollage(Request $request)
    {
        try {
            // dd(getUserCartItems());
            $unique_id = $request->unique_id;
            $designCollagePreviewData = $this->DesignCollageRepository->getOneMaster(['unique_id' => $unique_id, 'status' => 0]);
            $images = $this->DesignCollageRepository->getByWhere(['unique_id' => $unique_id, 'is_deleted' => 0], ['seq' => 'asc'])->toArray();
            if (empty($designCollagePreviewData) || empty($images)) {
                return redirect()
                    ->route('front.upload-photos')
                    ->with(['images_empty_error' => 'Collage not found, try again.']);
            }

            $mergedImage1 = asset('assets/images/preview_livingroom.png');
            $mergedImage2 = asset('assets/images/preview_kitchen.png');
            $preview_1 = public_path('/assets/images/preview_livingroom.png'); // background image
            $preview_2 = public_path('/assets/images/preview_kitchen.png'); // background image
            $img2Path = storage_path('app/public/') . $designCollagePreviewData['image_path'];

            // $prevPath = asset('storage') . '/' . $designCollagePreviewData['image_path'];
            // echo '<img src="' . $prevPath . '" alt="">';
            // die;

            // dd($preview_1, $preview_2, $img2Path);

            // Load images
            $preview_1 = @imagecreatefromjpeg($preview_1) ?: @imagecreatefrompng($preview_1);
            $preview_2 = @imagecreatefromjpeg($preview_2) ?: @imagecreatefrompng($preview_2);
            $img2 = @imagecreatefromjpeg($img2Path) ?: @imagecreatefrompng($img2Path);

            // Get raw grid info
            $tile_width = 91;
            $tile_height = 80;
            $cols = $designCollagePreviewData['grid_columns'] ?? 5;
            $rows = $designCollagePreviewData['grid_rows'] ?? 7;
            $rawWidth = $tile_width * $cols;
            $rawHeight = $tile_height * $rows;

            $varr = $cols > $rows ? $cols : $rows;


            $scaleFactor = $varr >= 1 && $varr <= 5 ? 0.63 : ($varr >= 6 && $varr <= 8 ? 0.45 : 0);
            // if ($scaleFactor == 0) $scaleFactor = 1.0;
            // dd($scaleFactor);

            // dd($designCollagePreviewData['image_path']);

            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                throw new \Exception('GD extension is not installed. GD is required for image processing.');
            }

            // Ensure temp directory exists
            $tempDir = storage_path('app/public/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Verify image files exist
            $backgroundPath = public_path('/assets/images/preview_livingroom.png');
            $collagePath = storage_path('app/public/') . $designCollagePreviewData['image_path'];
            
            if (!file_exists($backgroundPath)) {
                throw new \Exception('Background preview image not found at: ' . $backgroundPath);
            }
            
            if (!file_exists($collagePath)) {
                throw new \Exception('Collage image not found at: ' . $collagePath);
            }

            // Load background and collage images with GD
            $preview_1 = @imagecreatefrompng($backgroundPath);
            if (!$preview_1) {
                $preview_1 = @imagecreatefromjpeg($backgroundPath);
            }
            
            $img2 = @imagecreatefromjpeg($collagePath);
            if (!$img2) {
                $img2 = @imagecreatefrompng($collagePath);
            }

            if (!$preview_1 || !$img2) {
                throw new \Exception('Failed to load images. Check if files are valid JPEG/PNG images.');
            }

            // Calculate scaling and position (same logic as before)
            $bg_w = 1611;
            $bg_h = 1074;
            $margin_left = intval($bg_w * 0.15);
            $margin_top = intval($bg_h * 0.08);
            $avail_w = $bg_w - $margin_left;
            $avail_h = $bg_h - $margin_top;

            $img2_w = imagesx($img2);
            $img2_h = imagesy($img2);

            if ($scaleFactor !== 0) {
                $scale = $scaleFactor * 1.4; // Increase by 40% (1.0 + 0.4 = 1.4)
                $final_w = intval($img2_w * $scale);
                $final_h = intval($img2_h * $scale);
            } else {
                $max_width = intval($bg_w * 0.55 * 1.4); // Increase by 40%
                $max_height = intval($bg_h * 0.6 * 1.4); // Increase by 40%
                if ($cols > $rows) {
                    $target_w = min($avail_w, $max_width);
                    $scale = min($target_w / $img2_w, 1);
                    $final_w = intval($img2_w * $scale);
                    $final_h = intval($img2_h * $scale);
                } else {
                    $target_h = min($avail_h, $max_height);
                    $scale = min($target_h / $img2_h, 1);
                    $final_w = intval($img2_w * $scale);
                    $final_h = intval($img2_h * $scale);
                }
            }

            // Position collage 12% higher in the scene
            $vertical_adjustment = intval($bg_h * 0.12);
            $dst_x = $margin_left + intval(($avail_w - $final_w) / 2);
            $dst_y = $margin_top + intval(($avail_h - $final_h) / 2) - $vertical_adjustment;

            // Apply style filter to collage before scaling (if filter is set)
            if (!empty($designCollagePreviewData['filter']) && $designCollagePreviewData['filter'] !== 'filter-original') {
                $this->applyFilterToImage($img2, $designCollagePreviewData['filter']);
            }

            // Create scaled collage image
            $img2_scaled = imagecreatetruecolor($final_w, $final_h);
            imagealphablending($img2_scaled, false);
            imagesavealpha($img2_scaled, true);
            imagecopyresampled($img2_scaled, $img2, 0, 0, 0, 0, $final_w, $final_h, $img2_w, $img2_h);

            // Merge collage onto background
            imagecopy($preview_1, $img2_scaled, $dst_x, $dst_y, 0, 0, $final_w, $final_h);

            // Save as PNG for best quality and transparency
            $mergedName = 'temp/collage_merged_' . $unique_id . '-1.png';
            $mergedPath = storage_path('app/public/') . $mergedName;
            imagepng($preview_1, $mergedPath, 9);

            $mergedImage1 = asset('storage/' . $mergedName);

            // Clean up
            imagedestroy($preview_1);
            imagedestroy($img2);
            imagedestroy($img2_scaled);


            // if ($preview_1 && $img2) {
            //     // Scale second image
            //     $img2_w = imagesx($img2);
            //     $img2_h = imagesy($img2);
            //     // $scaled_w = intval($img2_w * $scaleFactor);
            //     // $scaled_h = intval($img2_h * $scaleFactor);

            //     // $img2_scaled = imagecreatetruecolor($scaled_w, $scaled_h);
            //     // imagealphablending($img2_scaled, false);
            //     // imagesavealpha($img2_scaled, true);
            //     // imagecopyresampled($img2_scaled, $img2, 0, 0, 0, 0, $scaled_w, $scaled_h, $img2_w, $img2_h);

            //     // Background size
            //     $bg_w = 1611;
            //     $bg_h = 1074;

            //     // margin
            //     $margin_left = intval($bg_w * 0.15);
            //     $margin_top = intval($bg_h * 0.08);

            //     // Available area for collage (after margin)
            //     $avail_w = $bg_w - $margin_left;
            //     $avail_h = $bg_h - $margin_top;

            //     if ($scaleFactor !== 0) {
            //         // // Collage image original size
            //         // $img2_w = imagesx($img2_scaled);
            //         // $img2_h = imagesy($img2_scaled);

            //         // Scale img2 to fit inside available area (preserve aspect ratio)
            //         // $scale_w = $avail_w / $img2_w;
            //         // $scale_h = $avail_h / $img2_h;
            //         // $scale = min($scale_w, $scale_h, 1);
            //         $scale = $scaleFactor;

            //         $final_w = intval($img2_w * $scale);
            //         $final_h = intval($img2_h * $scale);

            //         // // If scaling is needed, create a new scaled image
            //         // if ($scale < 1) {
            //         //     $img2_final = imagecreatetruecolor($final_w, $final_h);
            //         //     imagealphablending($img2_final, false);
            //         //     imagesavealpha($img2_final, true);
            //         //     imagecopyresampled($img2_final, $img2_scaled, 0, 0, 0, 0, $final_w, $final_h, $img2_w, $img2_h);
            //         //     imagedestroy($img2_scaled);
            //         // } else {
            //         //     $img2_final = $img2_scaled;
            //         // }
            //     } else {
            //         // CSS-based limits
            //         // $max_width = intval($bg_w * 0.25);   // 25dvw
            //         // $max_height = intval($bg_h * 0.40);  // 40dvh
            //         $max_width = intval($bg_w * 0.55); // 25dvw
            //         $max_height = intval($bg_h * 0.6); // 40dvh

            //         if ($cols > $rows) {
            //             // Limit by width
            //             $target_w = min($avail_w, $max_width);
            //             $scale = min($target_w / $img2_w, 1);
            //             $final_w = intval($img2_w * $scale);
            //             $final_h = intval($img2_h * $scale);
            //         } else {
            //             // Limit by height
            //             $target_h = min($avail_h, $max_height);
            //             $scale = min($target_h / $img2_h, 1);
            //             $final_w = intval($img2_w * $scale);
            //             $final_h = intval($img2_h * $scale);
            //         }
            //     }

            //     // Center img2 in the available area (after margin)
            //     $dst_x = $margin_left + intval(($avail_w - $final_w) / 2);
            //     $dst_y = $margin_top + intval(($avail_h - $final_h) / 2);

            //     // Create scaled collage image
            //     $img2_scaled = imagecreatetruecolor($final_w, $final_h);
            //     imagealphablending($img2_scaled, false);
            //     imagesavealpha($img2_scaled, true);
            //     imagecopyresampled($img2_scaled, $img2, 0, 0, 0, 0, $final_w, $final_h, $img2_w, $img2_h);

            //     // Merge
            //     imagecopy($preview_1, $img2_scaled, $dst_x, $dst_y, 0, 0, $final_w, $final_h);

            //     // Save merged image
            //     $mergedName = 'temp/collage_merged_' . $unique_id . '-1.png';
            //     $mergedPath = storage_path('app/public/') . $mergedName;
            //     imagepng($preview_1, $mergedPath, 0);
            //     // imagejpeg($preview_1, $mergedPath, 99);

            //     // Free memory
            //     imagedestroy($preview_1);
            //     imagedestroy($img2);
            //     imagedestroy($img2_scaled);

            //     // Pass merged image path to view
            //     $mergedImage1 = asset('storage/' . $mergedName);
            //     // } else {
            //     //     $mergedImage1 = null;
            // }

            if ($preview_2 && $img2) {
                // Scale second image
                $img2_w = imagesx($img2);
                $img2_h = imagesy($img2);
                // $scaled_w = intval($img2_w * $scaleFactor);
                // $scaled_h = intval($img2_h * $scaleFactor);

                // $img2_scaled = imagecreatetruecolor($scaled_w, $scaled_h);
                // imagealphablending($img2_scaled, false);
                // imagesavealpha($img2_scaled, true);
                // imagecopyresampled($img2_scaled, $img2, 0, 0, 0, 0, $scaled_w, $scaled_h, $img2_w, $img2_h);

                // Background size
                $bg_w = 1611;
                $bg_h = 1074;

                // margin
                $margin_left = intval($bg_w * 0.08);
                $margin_bottom = intval($bg_h * 0.35);

                // Available area for collage (after margin)
                $avail_w = $bg_w - $margin_left;
                $avail_h = $bg_h - $margin_bottom;

                if ($scaleFactor !== 0) {
                    // // Collage image original size
                    // $img2_w = imagesx($img2_scaled);
                    // $img2_h = imagesy($img2_scaled);

                    // Scale img2 to fit inside available area (preserve aspect ratio)
                    // $scale_w = $avail_w / $img2_w;
                    // $scale_h = $avail_h / $img2_h;
                    // $scale = min($scale_w, $scale_h, 1);
                    $scale = $scaleFactor * 1.4; // Increase by 40%

                    $final_w = intval($img2_w * $scale);
                    $final_h = intval($img2_h * $scale);

                    // // If scaling is needed, create a new scaled image
                    // if ($scale < 1) {
                    //     $img2_final = imagecreatetruecolor($final_w, $final_h);
                    //     imagealphablending($img2_final, false);
                    //     imagesavealpha($img2_final, true);
                    //     imagecopyresampled($img2_final, $img2_scaled, 0, 0, 0, 0, $final_w, $final_h, $img2_w, $img2_h);
                    //     imagedestroy($img2_scaled);
                    // } else {
                    //     $img2_final = $img2_scaled;
                    // }
                } else {
                    // CSS-based limits
                    // $max_width = intval($bg_w * 0.25);   // 25dvw
                    // $max_height = intval($bg_h * 0.40);  // 40dvh
                    $max_width = intval($bg_w * 0.55 * 1.4); // Increase by 40%
                    $max_height = intval($bg_h * 0.6 * 1.4); // Increase by 40%

                    if ($cols > $rows) {
                        // Limit by width
                        $target_w = min($avail_w, $max_width);
                        $scale = min($target_w / $img2_w, 1);
                        $final_w = intval($img2_w * $scale);
                        $final_h = intval($img2_h * $scale);
                    } else {
                        // Limit by height
                        $target_h = min($avail_h, $max_height);
                        $scale = min($target_h / $img2_h, 1);
                        $final_w = intval($img2_w * $scale);
                        $final_h = intval($img2_h * $scale);
                    }
                }

                // Position collage 12% higher in the scene
                $vertical_adjustment = intval($bg_h * 0.12);
                // Center img2 in the available area (after margin)
                $dst_x = $margin_left + intval(($avail_w - $final_w) / 2);
                $dst_y = $bg_h - $margin_bottom - $final_h - $vertical_adjustment;

                // Apply style filter to collage before scaling (if filter is set) 
                // Note: img2 was already filtered for preview_1, so we need a fresh copy or reuse
                // Since we're using the same $img2, the filter is already applied from preview_1
                
                // Create scaled collage image
                $img2_scaled = imagecreatetruecolor($final_w, $final_h);
                imagealphablending($img2_scaled, false);
                imagesavealpha($img2_scaled, true);
                imagecopyresampled($img2_scaled, $img2, 0, 0, 0, 0, $final_w, $final_h, $img2_w, $img2_h);

                // Merge
                imagecopy($preview_2, $img2_scaled, $dst_x, $dst_y, 0, 0, $final_w, $final_h);

                // Save merged image
                $mergedName = 'temp/collage_merged_' . $unique_id . '-2.jpg';
                $mergedPath = storage_path('app/public/') . $mergedName;
                // imagepng($preview_2, $mergedPath);
                imagejpeg($preview_2, $mergedPath, 80); // 80% quality

                // Free memory
                imagedestroy($preview_2);
                imagedestroy($img2);
                imagedestroy($img2_scaled);

                // Pass merged image path to view
                $mergedImage2 = asset('storage/' . $mergedName);
                // } else {
                //     $mergedImage2 = null;
            }

            // echo "<img src='" . $mergedImageUrl . "' alt='Collage Preview' style='width:100%; height:auto;'>";
            // die;

            // Calculate the correct number of occupied tiles for display
            $occupiedTilesCount = $this->calculateOccupiedTiles($images);
            
            // Update the total_tiles in the preview data to show correct count
            $designCollagePreviewData['total_tiles'] = $occupiedTilesCount;
            
            // Log for debugging
            Log::info("Preview page tile count", [
                'unique_id' => $unique_id,
                'occupied_tiles' => $occupiedTilesCount,
                'total_images' => count($images)
            ]);

            return view('front.design-collage-preview', compact(['designCollagePreviewData', 'images', 'mergedImage1', 'mergedImage2']));
        } catch (\Exception $e) {
            Log::error('Error in CollageController/previewDesignCollage: ' . $e->getMessage() . ' in line ' . $e->getLine() . ' | Stack: ' . $e->getTraceAsString());
            
            // Return user-friendly error page or redirect
            return redirect()
                ->route('front.upload-photos')
                ->with(['error' => 'Unable to generate preview. Please try again or contact support. Error: ' . $e->getMessage()]);
        }
    }
    function viewDesignCollage(Request $request)
    {
        try {
            // dd(getUserCartItems());
            $collections = $this->CollectionRepository->getAll();
            $tags = $this->TagRepository->getAll();
            // dd($tags);
            $unique_id = $request->unique_id;
            $designCollagePreviewData = $this->DesignCollageRepository->getOneMaster(['unique_id' => $unique_id]);
            // dd($designCollagePreviewData);
            $images = $this->DesignCollageRepository->getByWhere(['unique_id' => $unique_id, 'is_deleted' => 0], ['seq' => 'asc'])->toArray();
            if (empty($images)) {
                return redirect()
                    ->route('admin.galleryList')
                    ->with(['images_empty_error' => 'Collage not found, try again.']);
            }
            $collageAdmin = $this->DesignCollageRepository->getOneCollageAdmin(['unique_id' => $unique_id]);
            $selectedTagIds = json_decode($collageAdmin->tag_id ?? '[]', true); // ← decode here
            // dd($selectedTagIds);
            return view('admin.collage-edit', compact(['designCollagePreviewData', 'images', 'collageAdmin', 'collections', 'tags', 'selectedTagIds']));
        } catch (\Exception $e) {
            Log::error('Error in CollageController/previewDesignCollage :' . $e->getMessage() . 'in line' . $e->getLine());
        }
    }

    public function deleteCollage(Request $request)
    {
        try {
            $masterData = $this->DesignCollageRepository->getOneMaster([
                'unique_id' => $request->unique_id,
                'user_type' => 'admin',
            ]);

            $images = $this->DesignCollageRepository
                ->getByWhere([
                    'unique_id' => $request->unique_id,
                    'empty' => 0,
                ])
                ->toArray();

            $userPath = storage_path('app/public/');

            // Delete main image if exists
            if (!empty($masterData) && !empty($masterData->image_path)) {
                $mainImagePath = $userPath . $masterData->image_path;
                if (File::exists($mainImagePath)) {
                    File::delete($mainImagePath);
                }
            }

            // Delete individual image files if any
            if (!empty($images)) {
                foreach ($images as $img) {
                    if (!empty($img['image'])) {
                        $imagePath = $userPath . $img['image'];
                        if (File::exists($imagePath)) {
                            File::delete($imagePath);
                        }
                    }
                    if (!empty($img['image_edited'])) {
                        $editedPath = $userPath . $img['image_edited'];
                        if (File::exists($editedPath)) {
                            File::delete($editedPath);
                        }
                    }
                }
            }

            // Delete collage records
            $deletedMaster = $this->DesignCollageRepository->deleteMaster([
                'unique_id' => $request->unique_id,
                'user_type' => 'admin',
            ]);

            $deletedImages = $this->DesignCollageRepository->deleteMaster([
                'unique_id' => $request->unique_id,
                'empty' => 0,
            ]);

            if ($deletedMaster || $deletedImages) {
                return response()->json(['status' => 1, 'message' => 'Collage deleted successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Collage not found.']);
            }
        } catch (\Exception $e) {
            Log::error('Error in CollageController/deleteCollage :' . $e->getMessage() . 'in line' . $e->getLine());
        }
    }

    // refresh product item

    function refreshProductItem(Request $request)
    {
        try {
            return $this->CollageServices->refreshProductItem($request);
        } catch (\Exception $e) {
            Log::error('Error in CollageController/refreshProductItem :' . $e->getMessage() . 'in line' . $e->getLine());
        }
    }

    function artGalleryEdit($unique_id)
    {
        try {
            $existingDraft = DB::table('design_collage_master')
                ->where('user_id', auth()->user()->id)
                ->where('artgallery_unique_id', $unique_id)
                ->where('status', 0) // status = 0 => draft
                ->where('user_type', 'user')
                ->first();

            if ($existingDraft) {
                return redirect()->route('front.design-collage', ['unique_id' => $existingDraft->unique_id]);
            }

            $master = DB::table('design_collage_master')->where('unique_id', $unique_id)->first();
            if (!$master) {
                return redirect()->back()->with('gallery_edit_error', 'Original collage not found.');
            }

            [$returnstatus, $message, $new_unique_id] = $this->CollageServices->artGalleryEdit($unique_id, $master, 1);

            if ($returnstatus === 1) {
                return redirect()->route('front.design-collage', ['unique_id' => $new_unique_id]);
            } else {
                return redirect()->back()->with('gallery_edit_error', $message);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('gallery_edit_error', 'Error duplicating collage: ' . $e->getMessage());
        }
    }

    /**
     * Calculate the number of occupied tiles (counting tile spans for multi-tile images)
     * @param array $images - Array of image data
     * @return int - Number of occupied tiles
     */
    private function calculateOccupiedTiles($images)
    {
        $occupiedTilesCount = 0;
        foreach ($images as $image) {
            if (isset($image['empty']) && $image['empty'] == 0) {
                // Parse other_settings to get imageDivDataMargin for tile span calculation
                $otherSettings = isset($image['other_settings']) ? json_decode($image['other_settings'], true) : [];
                $imageDivDataMargin = $otherSettings['imageDivDataMargin'] ?? '';
                
                if (empty($imageDivDataMargin) || $imageDivDataMargin == 'null' || $imageDivDataMargin == null) {
                    // Single tile image
                    $occupiedTilesCount++;
                } else {
                    // Multi-tile image - calculate span
                    $marginArr = explode('|', $imageDivDataMargin);
                    if (is_array($marginArr) && isset($marginArr[1])) {
                        $marginW = floatval($marginArr[0]);
                        $marginH = floatval($marginArr[1]);
                        $tile_w = ($marginW > 0) ? ($marginW / 2) + 1 : 1;
                        $tile_h = ($marginH > 0) ? ($marginH / 2) + 1 : 1;
                        $tiles_wh = intval($tile_w * $tile_h);
                        $occupiedTilesCount += $tiles_wh;
                    } else {
                        // Fallback to single tile
                        $occupiedTilesCount++;
                    }
                }
            }
        }
        return $occupiedTilesCount;
    }

    /**
     * Apply style filter to an image resource
     * Matches the filters available in PrintFileService and frontend CSS
     * @param resource $canvas - GD image resource
     * @param string $filter - Filter name (e.g., 'filter-noir', 'filter-stark')
     */
    private function applyFilterToImage($canvas, string $filter): void
    {
        Log::info("Applying filter to preview image", ['filter' => $filter]);
        
        switch ($filter) {
            case 'filter-noir':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_CONTRAST, -10);
                break;
            case 'filter-stark':
                imagefilter($canvas, IMG_FILTER_CONTRAST, -15);
                imagefilter($canvas, IMG_FILTER_BRIGHTNESS, 5);
                break;
            case 'filter-scandi':
                imagefilter($canvas, IMG_FILTER_COLORIZE, 20, 10, 0, 0);
                break;
            case 'filter-capri':
                imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 10, 25, 0);
                break;
            case 'filter-nordic':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 25, 20, 15, 0);
                break;
            case 'filter-belveder':
                imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                imagefilter($canvas, IMG_FILTER_COLORIZE, 90, 55, 30, 0);
                break;
            default:
                Log::warning("Unknown or no filter", ['filter' => $filter]);
                break;
        }
        
        Log::info("Filter applied to preview image", ['filter' => $filter]);
    }
}
