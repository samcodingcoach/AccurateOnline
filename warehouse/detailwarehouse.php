<?php
/**
 * API untuk mendapatkan detail warehouse dalam format JSON
 * Endpoint: /detail.do
 * HTTP Method: GET
 * Scope: warehouse_view
 * Required Header: X-Session-ID
 * Required Parameter: id
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameter ID dari query string
$warehouseId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validasi parameter ID
if (empty($warehouseId)) {
    echo jsonResponse(null, false, 'Parameter ID warehouse wajib diisi');
    exit();
}

// Get warehouse detail
$result = $api->getWarehouseDetail($warehouseId);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Detail warehouse berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil detail warehouse: ' . ($result['error'] ?? 'Unknown error'));
}
?>
