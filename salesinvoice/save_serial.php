<?php
/**
 * API untuk menyimpan serial numbers
 * File: /salesinvoice/save_serial.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan POST.']);
    exit;
}

try {
    // Ambil raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    // Validasi data yang diperlukan
    if (!isset($data['item_id']) || !isset($data['serial_numbers'])) {
        echo json_encode(['success' => false, 'message' => 'item_id dan serial_numbers diperlukan']);
        exit;
    }
    
    $item_id = $data['item_id'];
    $item_name = $data['item_name'] ?? '';
    $item_code = $data['item_code'] ?? '';
    $so_id = $data['so_id'] ?? '';
    $serial_numbers = $data['serial_numbers'];
    
    // Validasi serial_numbers adalah array
    if (!is_array($serial_numbers)) {
        echo json_encode(['success' => false, 'message' => 'serial_numbers harus berupa array']);
        exit;
    }
    
    // Inisialisasi session jika belum ada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simpan ke session untuk sementara
    // Di implementasi production, simpan ke database
    if (!isset($_SESSION['item_serials'])) {
        $_SESSION['item_serials'] = [];
    }
    
    $_SESSION['item_serials'][$item_id] = [
        'item_name' => $item_name,
        'item_code' => $item_code,
        'so_id' => $so_id,
        'serial_numbers' => $serial_numbers,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Log untuk debugging
    error_log("Serial data saved for item $item_id: " . json_encode($serial_numbers));
    
    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Serial numbers berhasil disimpan',
        'data' => [
            'item_id' => $item_id,
            'total_serials' => count($serial_numbers),
            'saved_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
