<?php
/**
 * API untuk mendapatkan detail customer dalam format JSON
 * Endpoint: /detail.do
 * HTTP Method: GET
 * Scope: customer_view
 * Required Header: X-Session-ID
 * Required Parameter: id
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo jsonResponse(null, false, 'Method tidak diizinkan. Gunakan GET.');
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Get parameter ID dari query string
    $customerId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    // Validasi parameter ID
    if (empty($customerId)) {
        echo jsonResponse(null, false, 'Parameter ID customer wajib diisi');
        exit;
    }

    // Get customer detail
    $result = $api->getCustomerDetail($customerId);

    if ($result['success']) {
        // Format response data dengan metadata
        $responseData = [
            'customer' => $result['data'],
            'meta' => [
                'scope_required' => 'customer_view',
                'http_code' => $result['http_code'],
                'endpoint' => '/accurate/api/customer/detail.do',
                'customer_id' => $customerId
            ]
        ];
        
        echo jsonResponse($responseData, true, 'Detail customer berhasil diambil');
    } else {
        // Log error untuk debugging
        $errorMessage = $result['error'] ?? 'Unknown error';
        logError("Failed to get customer detail for ID $customerId: " . $errorMessage, __FILE__, __LINE__);
        
        echo jsonResponse(null, false, 'Gagal mengambil detail customer: ' . $errorMessage);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    logError("Exception in customer detail API: " . $e->getMessage(), __FILE__, $e->getLine());
    echo jsonResponse(null, false, 'Terjadi kesalahan internal server');
}
?>
