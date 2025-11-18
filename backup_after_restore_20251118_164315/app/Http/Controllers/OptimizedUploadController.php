<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;

class OptimizedUploadController extends Controller
{
    private $db;
    
    public function __construct()
    {
        // Initialize database connection
        try {
            $this->db = new PDO(
                'mysql:host=127.0.0.1;dbname=sanshaco_piclicks;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle optimized file upload
     */
    public function uploadFiles(\App\Http\Requests\SaveUploadPhotosRequest $request)
    {
        try {
            // Debug logging
            Log::info('Upload request received', [
                'has_files' => $request->hasFile('files'),
                'files_count' => count($request->file('files', [])),
                'user_type' => $request->input('user_type', 'unknown')
            ]);
            
            // Use Laravel's existing upload system for compatibility
            $collageServices = app(\App\Services\CollageServices::class);
            $result = $collageServices->saveImages($request);
            
            // The result should be a JSON response
            return $result;
            
        } catch (Exception $e) {
            Log::error('Upload controller error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Upload failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Process individual file upload with optimized handling
     */
    private function processUpload($file, $userId, $sessionId)
    {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        // Get image info
        $imageInfo = getimagesize($file->getPathname());
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $originalDpi = $this->getImageDpi($file->getPathname());
        
        // Calculate quality metrics
        $quality = $this->assessImageQuality($originalWidth, $originalHeight, $originalDpi);
        
        // Generate unique filename
        $filename = $this->generateFilename($file->getClientOriginalName(), $userId);
        
        // Process image for print quality
        $printResult = $this->processForPrint($file->getPathname(), $filename, $quality);
        
        // Process image for preview (smaller, faster loading)
        $previewResult = $this->processForPreview($file->getPathname(), $filename);
        
        // Store in database
        $this->storeUploadData($userId, $sessionId, $filename, $quality, $printResult, $previewResult);
        
        return [
            'success' => true,
            'filename' => $filename,
            'quality' => $quality,
            'print_file' => $printResult['filename'],
            'preview_file' => $previewResult['filename'],
            'warnings' => $quality['warnings']
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
        }
        
        if ($file->getSize() > $maxSize) {
            return ['valid' => false, 'error' => 'File too large. Maximum size is 10MB.'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get image DPI from EXIF data
     */
    private function getImageDpi($filePath)
    {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filePath);
            if ($exif && isset($exif['XResolution'])) {
                return (int)$exif['XResolution'];
            }
        }
        return 72; // Default assumption
    }
    
    /**
     * Assess image quality for print requirements
     */
    private function assessImageQuality($width, $height, $dpi)
    {
        $warnings = [];
        $qualityScore = 100;
        
        // Print specifications
        $SAFE_AREA_WIDTH_PX = 1697;  // 143.7mm * 300 DPI / 25.4
        $SAFE_AREA_HEIGHT_PX = 1488; // 126.0mm * 300 DPI / 25.4
        
        // Check if image is large enough for print quality
        if ($width < $SAFE_AREA_WIDTH_PX || $height < $SAFE_AREA_HEIGHT_PX) {
            $warnings[] = "Image resolution may be too low for optimal print quality. Recommended: {$SAFE_AREA_WIDTH_PX}x{$SAFE_AREA_HEIGHT_PX}px or higher.";
            $qualityScore -= 30;
        }
        
        // Check DPI
        if ($dpi < 200) {
            $warnings[] = "Image DPI is low ({$dpi} DPI). For best print quality, use images with 300 DPI or higher.";
            $qualityScore -= 20;
        }
        
        // Check aspect ratio
        $aspectRatio = $width / $height;
        $requiredRatio = 143.7 / 126.0;
        $ratioDifference = abs($aspectRatio - $requiredRatio);
        
        if ($ratioDifference > 0.1) {
            $warnings[] = "Image aspect ratio doesn't match photo tile dimensions. Cropping may be required.";
            $qualityScore -= 10;
        }
        
        return [
            'score' => max(0, $qualityScore),
            'warnings' => $warnings,
            'width' => $width,
            'height' => $height,
            'dpi' => $dpi,
            'aspect_ratio' => $aspectRatio,
            'print_ready' => $qualityScore >= 80
        ];
    }
    
    /**
     * Process image for print quality (full bleed dimensions)
     */
    private function processForPrint($sourcePath, $filename, $quality)
    {
        // Ensure print directory exists
        $this->ensurePrintDirectory();
        
        $image = $this->createImageResource($sourcePath);
        if (!$image) {
            throw new Exception('Failed to create image resource');
        }
        
        // Create print canvas with full bleed dimensions
        $printCanvas = imagecreatetruecolor(1744, 1535); // Full bleed dimensions
        $white = imagecolorallocate($printCanvas, 255, 255, 255);
        imagefill($printCanvas, 0, 0, $white);
        
        // Calculate scaling to fit image in safe area while maintaining aspect ratio
        $scale = min(1697 / imagesx($image), 1488 / imagesy($image));
        
        $scaledWidth = (int)(imagesx($image) * $scale);
        $scaledHeight = (int)(imagesy($image) * $scale);
        
        // Center the image in the safe area
        $x = (1697 - $scaledWidth) / 2;
        $y = (1488 - $scaledHeight) / 2;
        
        // Copy and resize image to print canvas
        imagecopyresampled(
            $printCanvas, $image,
            $x, $y, 0, 0,
            $scaledWidth, $scaledHeight,
            imagesx($image), imagesy($image)
        );
        
        // Save print file
        $printFilename = 'print_' . $filename;
        $printPath = public_path('uploads/print/' . $printFilename);
        
        if (!imagejpeg($printCanvas, $printPath, 95)) {
            throw new Exception('Failed to save print file');
        }
        
        imagedestroy($image);
        imagedestroy($printCanvas);
        
        return [
            'filename' => $printFilename,
            'path' => $printPath,
            'width' => 1744,
            'height' => 1535,
            'dpi' => 300
        ];
    }
    
    /**
     * Process image for preview (smaller, faster loading)
     */
    private function processForPreview($sourcePath, $filename)
    {
        // Ensure preview directory exists
        $this->ensurePreviewDirectory();
        
        $image = $this->createImageResource($sourcePath);
        if (!$image) {
            throw new Exception('Failed to create image resource');
        }
        
        // Create preview canvas (smaller size for web display)
        $previewWidth = 400;
        $previewHeight = 350;
        
        $previewCanvas = imagecreatetruecolor($previewWidth, $previewHeight);
        $white = imagecolorallocate($previewCanvas, 255, 255, 255);
        imagefill($previewCanvas, 0, 0, $white);
        
        // Calculate scaling to fit image in preview canvas
        $scale = min($previewWidth / imagesx($image), $previewHeight / imagesy($image));
        
        $scaledWidth = (int)(imagesx($image) * $scale);
        $scaledHeight = (int)(imagesy($image) * $scale);
        
        // Center the image
        $x = ($previewWidth - $scaledWidth) / 2;
        $y = ($previewHeight - $scaledHeight) / 2;
        
        // Copy and resize image to preview canvas
        imagecopyresampled(
            $previewCanvas, $image,
            $x, $y, 0, 0,
            $scaledWidth, $scaledHeight,
            imagesx($image), imagesy($image)
        );
        
        // Save preview file
        $previewFilename = 'preview_' . $filename;
        $previewPath = public_path('uploads/preview/' . $previewFilename);
        
        if (!imagejpeg($previewCanvas, $previewPath, 85)) {
            throw new Exception('Failed to save preview file');
        }
        
        imagedestroy($image);
        imagedestroy($previewCanvas);
        
        return [
            'filename' => $previewFilename,
            'path' => $previewPath,
            'width' => $previewWidth,
            'height' => $previewHeight
        ];
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource($filePath)
    {
        $imageInfo = getimagesize($filePath);
        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename($originalName, $userId)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = date('Y-m-d_H-i-s');
        $random = substr(md5(uniqid()), 0, 8);
        return "user_{$userId}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Store upload data in database
     */
    private function storeUploadData($userId, $sessionId, $filename, $quality, $printResult, $previewResult)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO uploaded_images 
                (user_id, session_id, filename, original_width, original_height, original_dpi, 
                 quality_score, print_filename, preview_filename, print_width, print_height, 
                 preview_width, preview_height, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId, $sessionId, $filename,
                $quality['width'], $quality['height'], $quality['dpi'],
                $quality['score'], $printResult['filename'], $previewResult['filename'],
                $printResult['width'], $printResult['height'],
                $previewResult['width'], $previewResult['height']
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Database storage failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique ID for collage
     */
    private function generateUniqueId()
    {
        return 'collage_' . time() . '_' . substr(md5(uniqid()), 0, 8);
    }
    
    /**
     * Store collage master record
     */
    private function storeCollageMaster($uniqueId, $userId, $imageCount)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO design_collage_master 
                (unique_id, user_id, user_type, grid_rows, grid_columns, total_images, status, created_at) 
                VALUES (?, ?, 'user', ?, ?, ?, 0, NOW())
            ");
            
            // Calculate grid based on image count
            if ($imageCount <= 4) {
                $gridRows = 2;
                $gridColumns = 2;
            } elseif ($imageCount <= 9) {
                $gridRows = 3;
                $gridColumns = 3;
            } else {
                $gridColumns = ceil(sqrt($imageCount));
                $gridRows = ceil($imageCount / $gridColumns);
            }
            
            $stmt->execute([$uniqueId, $userId, $gridRows, $gridColumns, $imageCount]);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to store collage master: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Store collage image record
     */
    private function storeCollageImage($uniqueId, $fileData, $sequence)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO design_collage 
                (unique_id, seq, image_path, print_path, preview_path, quality_score, 
                 original_filename, is_deleted, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $imagePath = 'uploads/preview/' . $fileData['preview_file'];
            $printPath = 'uploads/print/' . $fileData['print_file'];
            $previewPath = 'uploads/preview/' . $fileData['preview_file'];
            
            $stmt->execute([
                $uniqueId, $sequence, $imagePath, $printPath, $previewPath,
                $fileData['quality']['score'], $fileData['filename']
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to store collage image: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure print directory exists
     */
    private function ensurePrintDirectory()
    {
        $printDir = public_path('uploads/print');
        if (!is_dir($printDir)) {
            if (!mkdir($printDir, 0755, true) && !is_dir($printDir)) {
                throw new Exception('Failed to create print directory: ' . $printDir);
            }
        }
    }
    
    /**
     * Ensure preview directory exists
     */
    private function ensurePreviewDirectory()
    {
        $previewDir = public_path('uploads/preview');
        if (!is_dir($previewDir)) {
            if (!mkdir($previewDir, 0755, true) && !is_dir($previewDir)) {
                throw new Exception('Failed to create preview directory: ' . $previewDir);
            }
        }
    }
}
