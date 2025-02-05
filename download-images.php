<?php

$types = [
    'industrial_land',
    'industrial_building',
    'logistics_center',
    'warehouse',
    'production_facility'
];

$assetsPath = __DIR__ . '/assets/images/properties/';
$publicPath = __DIR__ . '/public/uploads/properties/';

// Create directories if they don't exist
if (!file_exists($assetsPath)) {
    mkdir($assetsPath, 0777, true);
}
if (!file_exists($publicPath)) {
    mkdir($publicPath, 0777, true);
}

// Create sample images
foreach ($types as $type) {
    echo "Creating images for {$type}\n";

    for ($i = 1; $i <= 3; $i++) {
        $filename = sprintf('%s-%d.jpg', str_replace('_', '-', $type), $i);
        $filepath = $assetsPath . $filename;

        echo "Creating {$filename}...\n";

        // Create a sample image
        $width = 800;
        $height = 600;
        $image = imagecreatetruecolor($width, $height);
        
        // Fill with a random color
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($image, 0, 0, $color);
        
        // Add some text
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $text = "{$type} #{$i}";
        imagestring($image, 5, ($width - strlen($text) * 8) / 2, ($height - 16) / 2, $text, $textColor);
        
        // Save to assets
        imagejpeg($image, $filepath);
        
        // Copy to public
        copy($filepath, $publicPath . $filename);
        
        imagedestroy($image);
        echo "Done!\n";
    }
}

echo "All images have been created and copied!\n"; 