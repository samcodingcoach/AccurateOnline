<?php
/**
 * API endpoint untuk mendapatkan database list dari Accurate
 * Endpoint: https://account.accurate.id/api/db-list.do (HTTP Method: GET)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get database list
$result = $api->getDatabaseList();

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data database list berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data database list: ' . ($result['error'] ?? 'Unknown error'));
}
?>