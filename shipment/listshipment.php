<?php
/**
 * API untuk endpoint /list.do
 * HTTP Method: GET
 * Scope: shipment_view
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['page', 'pageSize'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Parameter untuk pagination
$page = isset($params['page']) ? (int)$params['page'] : 1;
$pageSize = isset($params['pageSize']) ? (int)$params['pageSize'] : 100;

// Validasi pagination parameters
if ($page < 1) $page = 1;
if ($pageSize < 1 || $pageSize > 1000) $pageSize = 100;

// Get shipment list
$result = $api->getShipmentList($page, $pageSize);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data shipment berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data shipment: ' . ($result['error'] ?? 'Unknown error'));
}
?>