<?php
/**
 * API untuk mendapatkan detail journal voucher dalam format JSON
 * File: /journal/detail_journal.php
 * HTTP Method: GET
 * Scope: journal_view
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
    $journalId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    // Validasi parameter ID
    if (empty($journalId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parameter ID journal voucher wajib diisi']);
        exit;
    }

    // Get journal voucher detail
    $result = $api->getJournalVoucherDetail($journalId);

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
            'message' => $result['error'] ?? 'Gagal mengambil detail journal voucher',
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