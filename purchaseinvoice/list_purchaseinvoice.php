<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
    exit;
}

try {
    $api = new AccurateAPI();
    $result = $api->getPurchaseInvoiceList();
    
    if ($result['success']) {
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'data' => null,
            'message' => $result['message'] ?? 'Failed to fetch purchase invoice data'
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
