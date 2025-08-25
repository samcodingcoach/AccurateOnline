<?php
/**
 * Branch Detail API Endpoint
 * Returns branch details as JSON
 */

require_once __DIR__ . '/../bootstrap.php';

// Set JSON header
header('Content-Type: application/json');

// Ambil ID branch dari parameter
$branchId = $_GET['id'] ?? null;

if (!$branchId) {
    echo json_encode([
        'success' => false,
        'message' => 'Branch ID is required'
    ]);
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Ambil detail branch
    $branchResponse = $api->getBranchDetail($branchId);

    if ($branchResponse['success'] && isset($branchResponse['data'])) {
        // Cek apakah response berhasil berdasarkan field 's' dalam data
        if (isset($branchResponse['data']['s']) && $branchResponse['data']['s'] === true) {
            // Jika berhasil, data ada di 'd'
            if (isset($branchResponse['data']['d'])) {
                echo json_encode([
                    'success' => true,
                    'data' => $branchResponse['data']
                ]);
                exit;
            }
        }
    }

    // Jika gagal
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get branch details',
        'data' => $branchResponse
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
