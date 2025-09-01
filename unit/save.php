<?php
/**
 * API untuk menyimpan unit baru
 * File: /unit/save.php
 * HTTP Method: POST
 * Scope: unit_save
 * Endpoint: /save.do
 */

require_once __DIR__ . '/../bootstrap.php';

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Handle hanya untuk method POST
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['s' => false, 'd' => 'Method Not Allowed']);
    exit;
}

// Ambil nama dari body request (form-data atau x-www-form-urlencoded)
$name = $_POST['name'] ?? null;

if (empty($name)) {
    http_response_code(400);
    echo json_encode(['s' => false, 'd' => 'Parameter 'name' is required.']);
    exit;
}

try {
    // Inisialisasi API class
    $api = new AccurateAPI();

    // Siapkan data untuk disimpan
    $unitData = [
        'name' => $name
    ];

    // Panggil method untuk menyimpan unit
    $result = $api->saveUnit($unitData);

    if ($result['success']) {
        // Jika berhasil, kembalikan data dari Accurate dengan status 201 Created
        http_response_code(201);
        echo json_encode($result['data']);
    } else {
        // Jika gagal, berikan response error yang sesuai
        $http_code = $result['http_code'] ?? 500;
        $error_message = $result['error'] ?? 'Failed to save unit.';
        
        // Log error untuk debugging
        logError("Failed to save unit with name: $name - $error_message", __FILE__, __LINE__);

        http_response_code($http_code);
        echo json_encode(['s' => false, 'd' => $error_message]);
    }

} catch (Exception $e) {
    // Handle kesalahan tak terduga
    logError("Exception in unit save API: " . $e->getMessage(), __FILE__, $e->getLine());
    http_response_code(500);
    echo json_encode(['s' => false, 'd' => 'Internal Server Error']);
}
?>
