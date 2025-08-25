<?php
/**
 * API untuk mengambil serial numbers yang sudah tersimpan
 * File: /salesinvoice/get_serial.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan GET.']);
    exit;
}

// Validasi parameter item_id
if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter item_id diperlukan']);
    exit;
}

$item_id = $_GET['item_id'];

try {
    // Inisialisasi session jika belum ada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Cek apakah ada data serial untuk item ini
    if (isset($_SESSION['item_serials'][$item_id])) {
        $serialData = $_SESSION['item_serials'][$item_id];
        
        echo json_encode([
            'success' => true,
            'message' => 'Data serial ditemukan',
            'data' => [
                'item_id' => $item_id,
                'item_name' => $serialData['item_name'],
                'item_code' => $serialData['item_code'],
                'so_id' => $serialData['so_id'],
                'serial_numbers' => $serialData['serial_numbers'],
                'total_serials' => count($serialData['serial_numbers']),
                'updated_at' => $serialData['updated_at']
            ]
        ]);
    } else {
        // Tidak ada data, kembalikan data kosong
        echo json_encode([
            'success' => true,
            'message' => 'Belum ada data serial untuk item ini',
            'data' => [
                'item_id' => $item_id,
                'serial_numbers' => [],
                'total_serials' => 0
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
