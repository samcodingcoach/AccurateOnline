<?php
/**
 * API untuk mendapatkan item brands
 * File ini menggunakan struktur baru yang terorganisir
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get list brands
$result = $api->getItemBrands();

if ($result['success']) {
    echo jsonResponse($result['data'], true, 'List brand berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil list brand: ' . ($result['error'] ?? 'Unknown error'));
}
?>
