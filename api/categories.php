<?php
/**
 * API untuk mendapatkan item categories
 * File ini menggunakan struktur baru yang terorganisir
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get list categories
$result = $api->getItemCategories();

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'List kategori berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil list kategori: ' . ($result['error'] ?? 'Unknown error'));
}
?>
