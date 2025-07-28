<?php

/**
 * Helper function to convert file path to base64 data URL
 */
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

/**
 * Helper function to find barcode file path
 */
function findBarcodeFile($relativePath) {
    // Log for debugging
    if (class_exists('\Illuminate\Support\Facades\Log')) {
        \Illuminate\Support\Facades\Log::info("Finding barcode: " . $relativePath);
    }
    
    // Extract filename from path
    $filename = basename($relativePath);
    
    // Check common paths
    $possiblePaths = [
        storage_path('app/public/' . $relativePath),
        public_path('storage/' . $relativePath),
        storage_path('app/public/barcode/' . $filename),
        public_path('storage/barcode/' . $filename),
        public_path('storage/public/barcode/' . $filename),
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}
