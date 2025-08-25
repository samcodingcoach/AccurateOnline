<?php
/**
 * API untuk endpoint /detail.do
 * HTTP Method: GET
 * Scope: item_category_view
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

$categoryId = (int)$params['id'];

// Validasi ID harus berupa angka positif
if ($categoryId <= 0) {
    echo jsonResponse(null, false, 'Parameter id harus berupa angka positif');
    exit;
}

// Get item category detail
$result = $api->getItemCategoryDetail($categoryId);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Detail kategori item berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil detail kategori item: ' . ($result['error'] ?? 'Unknown error'));
}
?>