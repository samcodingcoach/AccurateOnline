<?php
/**
 * API untuk mendapatkan informasi vendor/supplier
 * Berdasarkan dokumentasi Accurate API untuk vendor
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Inisialisasi API class
$api = new AccurateAPI();

// Cek apakah ada parameter ID vendor
$vendorId = $_GET['id'] ?? $_POST['id'] ?? null;

if ($vendorId) {
    // Get vendor detail by ID
    $result = $api->getVendorDetail($vendorId);
    
    if ($result['success']) {
        echo json_encode($result['data']);
    } else {
        http_response_code(404);
        echo json_encode([
            'error' => 'Vendor not found',
            'message' => $result['error'] ?? 'Unknown error'
        ]);
    }
} else {
    // Get vendor list (tanpa parameter)
    $result = $api->getVendorList();
    
    if ($result['success']) {
        echo json_encode($result['data']);
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to get vendor list',
            'message' => $result['error'] ?? 'Unknown error'
        ]);
    }
}
?>
