<?php
/**
 * API endpoint untuk mendapatkan list fixed asset dari Accurate
 * Endpoint: /accurate/api/fixed-asset/list.do (HTTP Method: GET, Scope: fixed_asset_view)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['page', 'pageSize', 'search'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Parameter untuk pagination
$page = isset($params['page']) ? (int)$params['page'] : 1;
$pageSize = isset($params['pageSize']) ? (int)$params['pageSize'] : 25;
$search = isset($params['search']) ? $params['search'] : '';

// Validasi pagination parameters
if ($page < 1) $page = 1;
if ($pageSize < 1 || $pageSize > 1000) $pageSize = 25;

// Prepare API parameters
$apiParams = [
    'sp.page' => $page,
    'sp.pageSize' => $pageSize
];

// Tambahkan search filter jika ada
if (!empty($search)) {
    $apiParams['sp.search'] = $search;
}

// Get fixed asset list
$result = $api->getFixedAssetList($apiParams);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data fixed asset berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data fixed asset: ' . ($result['error'] ?? 'Unknown error'));
}
?>
