<?php
/**
 * API untuk mendapatkan detail item transfer dalam format JSON
 * File: /itemtransfer/detail_itemtransfer.php
 * HTTP Method: GET
 * Scope: item_transfer_view
 * Required Parameter: id
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan GET.']);
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Get parameter ID dari query string
    $transferId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    // Validasi parameter ID
    if (empty($transferId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parameter ID item transfer wajib diisi']);
        exit;
    }

    // Get item transfer detail
    $result = $api->getItemTransferDetail($transferId);

    if (isset($result['success']) && $result['success']) {
        // Hilangkan raw_response dari output
        unset($result['raw_response']);
        
        http_response_code(200);
        echo json_encode($result, JSON_PRETTY_PRINT);

    } else {
        $httpCode = $result['http_code'] ?? 500;
        http_response_code($httpCode);
        
        // Hilangkan raw_response dari output error juga
        unset($result['raw_response']);

        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Gagal mengambil detail item transfer',
            'data' => $result['data'] ?? null
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan internal server: ' . $e->getMessage()
    ]);
}
?>
