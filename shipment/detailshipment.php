<?php
/**
 * API untuk endpoint /detail.do
 * HTTP Method: GET
 * Scope: shipment_view
 * Required Header: X-Session-ID
 * Required Parameter: id
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['id'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Validasi parameter ID (required)
if (!isset($params['id']) || empty($params['id'])) {
    echo jsonResponse(null, false, 'Parameter id wajib diisi');
    exit;
}

$shipmentId = (int)$params['id'];

// Validasi ID harus berupa angka positif
if ($shipmentId <= 0) {
    echo jsonResponse(null, false, 'Parameter id harus berupa angka positif');
    exit;
}

// Get shipment detail
$result = $api->getShipmentDetail($shipmentId);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Detail shipment berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil detail shipment: ' . ($result['error'] ?? 'Unknown error'));
}
?>