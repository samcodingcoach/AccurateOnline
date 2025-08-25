<?php
/**
 * API untuk mendapatkan vendors
 * File ini menggunakan struktur baru yang terorganisir
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get list vendors
$result = $api->getVendors();

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'List vendor berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil list vendor: ' . ($result['error'] ?? 'Unknown error'));
}
?>
