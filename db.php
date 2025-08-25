<?php
/**
 * API untuk mendapatkan list database
 * File ini sudah direfactor untuk menggunakan struktur baru
 */

require_once __DIR__ . '/bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get list database
$result = $api->getDatabaseList();

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'List database berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil list database: ' . ($result['error'] ?? 'Unknown error'));
}
?>
