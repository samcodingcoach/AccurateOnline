<?php
/**
 * API untuk mendapatkan list warehouse dalam format JSON
 * File ini menggunakan struktur yang sama dengan api_listbarang
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['page', 'limit'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Get list warehouse dengan parameter yang benar
$limit = isset($params['limit']) ? (int)$params['limit'] : 25;
$page = isset($params['page']) ? (int)$params['page'] : 1;

$result = $api->getWarehouseList($limit, $page);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data warehouse berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data warehouse: ' . ($result['error'] ?? 'Unknown error'));
}
?>
