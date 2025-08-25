<?php
/**
 * API endpoint untuk mendapatkan detail fixed asset dari Accurate
 * Endpoint: /accurate/api/fixed-asset/detail.do (HTTP Method: GET, Scope: fixed_asset_view)
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

$id = (int)$params['id'];

if ($id <= 0) {
    echo jsonResponse(null, false, 'Format ID tidak valid');
    exit;
}

// Get fixed asset detail
$result = $api->getFixedAssetDetail($id);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data detail fixed asset berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data detail fixed asset: ' . ($result['error'] ?? 'Unknown error'));
}
?>
