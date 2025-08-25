<?php
/**
 * API untuk mendapatkan list barang dalam format JSON
 * File ini sudah direfactor untuk menggunakan struktur baru
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['fields', 'page', 'limit', 'filter', 'sort'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Get list barang dengan parameter yang benar
$limit = isset($params['limit']) ? (int)$params['limit'] : 100;
$page = isset($params['page']) ? (int)$params['page'] : 1;

$result = $api->getItemList($limit, $page);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data barang berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data barang: ' . ($result['error'] ?? 'Unknown error'));
}
?>
