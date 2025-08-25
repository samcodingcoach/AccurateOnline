<?php
/**
 * API untuk searching barang
 * File ini menggunakan struktur baru yang terorganisir
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get search query
$query = $_GET['q'] ?? $_POST['q'] ?? '';

if (empty($query)) {
    echo jsonResponse(null, false, 'Query parameter "q" is required');
    exit;
}

// Get additional parameters
$params = [];
$allowedParams = ['page', 'limit', 'fields'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params[$param] = sanitizeInput($_GET[$param]);
    }
}

// Search items
$result = $api->searchItems($query, $params);

if ($result['success']) {
    echo jsonResponse($result['data'], true, "Hasil pencarian untuk: \"$query\"");
} else {
    echo jsonResponse(null, false, 'Gagal melakukan pencarian: ' . ($result['error'] ?? 'Unknown error'));
}
?>
