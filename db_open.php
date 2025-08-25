<?php
/**
 * API untuk membuka database
 * File ini sudah direfactor untuk menggunakan struktur baru
 */

require_once __DIR__ . '/bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get database ID dari parameter atau gunakan default
$databaseId = $_GET['id'] ?? $_POST['id'] ?? null;

// Open database
$result = $api->openDatabase($databaseId);

if ($result['success']) {
    // Simpan session info untuk penggunaan selanjutnya
    $sessionData = $result['data'];
    file_put_contents(__DIR__ . '/session.txt', json_encode($sessionData, JSON_PRETTY_PRINT));
    
    echo jsonResponse($sessionData, true, 'Database berhasil dibuka');
} else {
    echo jsonResponse(null, false, 'Gagal membuka database: ' . ($result['error'] ?? 'Unknown error'));
}
?>
