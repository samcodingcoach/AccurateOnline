<?php
/**
 * API untuk mendapatkan detail employee dalam format JSON
 * Endpoint: /detail.do
 * HTTP Method: GET
 * Scope: employee_view
 * Required Header: X-Session-ID
 * Required Parameter: id
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameter ID dari query string
$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validasi parameter ID
if (empty($employeeId)) {
    echo jsonResponse(null, false, 'Parameter ID employee wajib diisi');
    exit();
}

// Get employee detail
$result = $api->getEmployeeDetail($employeeId);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Detail employee berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil detail employee: ' . ($result['error'] ?? 'Unknown error'));
}
?>