<?php
/**
 * API untuk mendapatkan list serial numbers per warehouse
 * Endpoint: /api/report/serial-number-per-warehouse.do (HTTP Method: GET, Scope: stock_mutation_history_view)
 * File: /mutasi/list_serial.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan GET.'], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Ambil X-Session-ID dari header, atau fallback ke session storage
    $sessionId = $_SERVER['HTTP_X_SESSION_ID'] ?? '';
    
    // Jika tidak ada di header, coba ambil dari localStorage via GET parameter
    if (empty($sessionId)) {
        $sessionId = $_GET['sessionId'] ?? '';
    }
    
    // Jika masih kosong, coba ambil dari session
    if (empty($sessionId)) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $sessionId = $_SESSION['accurate_session_id'] ?? '';
    }
    
    // Jika masih kosong, coba ambil dari constants
    if (empty($sessionId) && defined('ACCURATE_SESSION_ID')) {
        $sessionId = ACCURATE_SESSION_ID;
    }
    
    error_log("Final session ID: " . $sessionId);
    
    if (empty($sessionId)) {
        echo json_encode([
            'success' => false, 
            'message' => 'X-Session-ID header atau sessionId parameter diperlukan',
            'usage' => 'Gunakan header X-Session-ID atau parameter ?sessionId=xxx'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Ambil parameter itemNo
    $itemNo = $_GET['itemNo'] ?? '';
    
    if (empty($itemNo)) {
        echo json_encode(['success' => false, 'message' => 'Parameter itemNo diperlukan'], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Log request untuk debugging
    error_log("List Serial Request - Session ID: $sessionId, Item No: $itemNo");
    
    // Inisialisasi AccurateAPI
    $api = new AccurateAPI();
    
    // Set session ID untuk API call
    $api->setSessionId($sessionId);
    
    // Panggil API untuk mendapatkan serial number report
    $result = $api->getSerialNumberReport($itemNo);
    
    // Log response untuk debugging
    error_log("List Serial API Response: " . json_encode($result));
    
    if ($result['success']) {
        // Return successful response dengan format standar
        echo json_encode([
            'success' => true,
            'message' => 'Data serial berhasil diambil',
            'data' => $result['data']  // Data asli dari Accurate API
        ], JSON_PRETTY_PRINT);
    } else {
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Gagal mengambil data serial dari Accurate API',
            'data' => [
                's' => false,
                'd' => null
            ]
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    // Log error
    error_log("List Serial Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>