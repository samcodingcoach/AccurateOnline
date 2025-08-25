<?php
/**
 * API untuk mendapatkan detail purchase invoice dalam format JSON
 * File: /purchaseinvoice/detail_purchaseinvoice.php
 * HTTP Method: GET
 * Scope: purchase_invoice_view
 * Endpoint: /detail.do
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'Parameter ID purchase invoice diperlukan.'
    ]);
    exit;
}

$purchaseInvoiceId = (int) $_GET['id'];

try {
    // Inisialisasi AccurateAPI
    $api = new AccurateAPI();
    
    // Dapatkan detail purchase invoice dari API
    $result = $api->getPurchaseInvoiceDetail($purchaseInvoiceId);
    
    if ($result['success']) {
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'data' => null,
            'message' => $result['message'] ?? 'Failed to fetch purchase invoice detail'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;
?>
