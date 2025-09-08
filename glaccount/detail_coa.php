<?php
/**
 * API untuk endpoint /glaccount/detail.do
 * HTTP Method: GET
 * Scope: glaccount_view
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get ID from query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo jsonResponse(null, false, 'ID Akun GL tidak valid.');
    exit;
}

// Get GL Account detail
$result = $api->getGlAccountDetail($id);

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'Data Akun GL berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data Akun GL: ' . ($result['error'] ?? 'Unknown error'));
}
?>
