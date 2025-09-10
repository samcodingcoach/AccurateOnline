<?php
/**
 * API untuk mendapatkan list journal voucher dalam format JSON
 * File: /journal/list_journal.php
 * HTTP Method: GET
 * Scope: journal_view
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['s' => false, 'e' => 'Method tidak diizinkan. Gunakan GET.']);
    exit;
}

try {
    // Inisialisasi AccurateAPI
    $api = new AccurateAPI();
    
    // Dapatkan data journal voucher list dari API
    $result = $api->getJournalVoucherList();
    
    // Hilangkan raw_response dari output, baik sukses maupun gagal
    unset($result['raw_response']);

    // Cek jika 'success' ada dan true
    if (isset($result['success']) && $result['success']) {
        // Kembalikan seluruh response dari API, yang sudah mencakup data dan status sukses
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        // Set HTTP status code jika tersedia dari API response
        $httpCode = $result['http_code'] ?? 500;
        http_response_code($httpCode);

        // Kembalikan response error yang informatif
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to fetch journal voucher data',
            'http_code' => $httpCode,
            'data' => $result['data'] ?? null
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage(),
        'http_code' => 500,
        'data' => null
    ]);
}
?>