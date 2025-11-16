<?php
/**
 * Text Rendering Diagnostic Test
 * 
 * This page tests text rendering on a canvas to diagnose:
 * - Is text being rendered at all?
 * - Is text too small to see?
 * - Is text positioned outside the canvas?
 * - Is text on the correct layer?
 */

// Simulate the PrintFileService text rendering
$canvasW = 1744; // Example tile size with bleed
$canvasH = 1535;

// Create canvas
$canvas = imagecreatetruecolor($canvasW, $canvasH);
imagealphablending($canvas, false);
imagesavealpha($canvas, true);

// White background
$white = imagecolorallocate($canvas, 255, 255, 255);
imagefilledrectangle($canvas, 0, 0, $canvasW, $canvasH, $white);

// Enable alpha blending for drawing
imagealphablending($canvas, true);

// Test data
$testCases = [
    [
        'label' => 'Normal position',
        'text' => 'NORMAL',
        'x' => 300,
        'y' => 300,
        'fontSize' => 100,
        'color' => [255, 0, 0], // Red
        'rotation' => 0
    ],
    [
        'label' => 'Top-left corner',
        'text' => 'TOP-LEFT',
        'x' => 50,
        'y' => 100,
        'fontSize' => 80,
        'color' => [0, 255, 0], // Green
        'rotation' => 0
    ],
    [
        'label' => 'Negative Y (above canvas)',
        'text' => 'ABOVE',
        'x' => 500,
        'y' => -50,
        'fontSize' => 60,
        'color' => [0, 0, 255], // Blue
        'rotation' => 0
    ],
    [
        'label' => 'Very small font',
        'text' => 'TINY',
        'x' => 700,
        'y' => 300,
        'fontSize' => 10,
        'color' => [255, 0, 255], // Magenta
        'rotation' => 0
    ],
    [
        'label' => 'Huge font',
        'text' => 'BIG',
        'x' => 100,
        'y' => 800,
        'fontSize' => 300,
        'color' => [255, 165, 0], // Orange
        'rotation' => 0
    ],
    [
        'label' => 'Rotated 45deg',
        'text' => 'ROTATED',
        'x' => 1200,
        'y' => 700,
        'fontSize' => 80,
        'color' => [128, 0, 128], // Purple
        'rotation' => -45
    ],
    [
        'label' => 'Off right edge',
        'text' => 'RIGHT-EDGE',
        'x' => $canvasW - 50,
        'y' => 500,
        'fontSize' => 80,
        'color' => [0, 128, 128], // Teal
        'rotation' => 0
    ],
    [
        'label' => 'With translate(-50%, -50%)',
        'text' => 'CENTERED',
        'x' => 872, // Center of canvas
        'y' => 767,
        'fontSize' => 90,
        'color' => [255, 69, 0], // Red-Orange
        'rotation' => -16.251
    ]
];

// Draw grid lines for reference
$gray = imagecolorallocate($canvas, 200, 200, 200);
for ($i = 0; $i < $canvasW; $i += 200) {
    imageline($canvas, $i, 0, $i, $canvasH, $gray);
}
for ($i = 0; $i < $canvasH; $i += 200) {
    imageline($canvas, 0, $i, $canvasW, $i, $gray);
}

// Draw center crosshair
$black = imagecolorallocate($canvas, 0, 0, 0);
$centerX = intval($canvasW / 2);
$centerY = intval($canvasH / 2);
imageline($canvas, $centerX - 50, $centerY, $centerX + 50, $centerY, $black);
imageline($canvas, $centerX, $centerY - 50, $centerX, $centerY + 50, $black);

// Test font path
$fontPath = 'C:/Windows/Fonts/arial.ttf';
$diagnosticInfo = [];

// Render each test case
foreach ($testCases as $test) {
    $textColor = imagecolorallocate($canvas, $test['color'][0], $test['color'][1], $test['color'][2]);
    
    $diagnosticInfo[] = [
        'label' => $test['label'],
        'text' => $test['text'],
        'position' => "({$test['x']}, {$test['y']})",
        'fontSize' => $test['fontSize'],
        'rotation' => $test['rotation'],
        'color' => "rgb({$test['color'][0]}, {$test['color'][1]}, {$test['color'][2]})"
    ];
    
    if ($test['rotation'] != 0) {
        // Rotated text - use temporary canvas
        $bbox = imagettfbbox($test['fontSize'], 0, $fontPath, $test['text']);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        
        $padding = max($textWidth, $textHeight) * 0.5;
        $tempWidth = intval($textWidth + $padding * 2);
        $tempHeight = intval($textHeight + $padding * 2);
        
        if ($tempWidth > 0 && $tempHeight > 0 && $tempWidth < 10000 && $tempHeight < 10000) {
            $tempCanvas = imagecreatetruecolor($tempWidth, $tempHeight);
            imagealphablending($tempCanvas, false);
            imagesavealpha($tempCanvas, true);
            $transparent = imagecolorallocatealpha($tempCanvas, 0, 0, 0, 127);
            imagefilledrectangle($tempCanvas, 0, 0, $tempWidth, $tempHeight, $transparent);
            
            imagealphablending($tempCanvas, true);
            imagettftext($tempCanvas, $test['fontSize'], 0, intval($padding), intval($textHeight + $padding), $textColor, $fontPath, $test['text']);
            imagealphablending($tempCanvas, false);
            
            $rotatedCanvas = imagerotate($tempCanvas, -$test['rotation'], $transparent);
            $rotatedWidth = imagesx($rotatedCanvas);
            $rotatedHeight = imagesy($rotatedCanvas);
            
            $centerX = $test['x'] - intval($rotatedWidth / 2);
            $centerY = $test['y'] - intval($rotatedHeight / 2);
            
            imagecopy($canvas, $rotatedCanvas, $centerX, $centerY, 0, 0, $rotatedWidth, $rotatedHeight);
            imagealphablending($canvas, false);
            
            imagedestroy($tempCanvas);
            imagedestroy($rotatedCanvas);
        }
    } else {
        // Non-rotated text
        $bbox = imagettfbbox($test['fontSize'], 0, $fontPath, $test['text']);
        $ascent = abs($bbox[7]);
        $adjustedY = $test['y'] + $ascent;
        
        imagettftext($canvas, $test['fontSize'], 0, $test['x'], $adjustedY, $textColor, $fontPath, $test['text']);
    }
    
    // Draw marker dot at position
    imagefilledellipse($canvas, $test['x'], $test['y'], 10, 10, $black);
}

// Output the image
header('Content-Type: image/png');
imagepng($canvas);
imagedestroy($canvas);

// Save diagnostic info to a separate file
file_put_contents(__DIR__ . '/test-text-render-info.txt', print_r($diagnosticInfo, true));






