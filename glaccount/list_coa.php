<?php
/**
 * API untuk endpoint /glaccount/list.do
 * HTTP Method: GET
 * Scope: glaccount_view
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Get parameters dari query string
$params = [];
$allowedParams = ['page', 'pageSize'];

foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        $params['sp.' . $param] = sanitizeInput($_GET[$param]);
    }
}

// Get GL Account list
$result = $api->getGlAccountList($params);

if ($result['success']) {
    // Fungsi jsonResponse ada di utils/utils.php
    // Parameter ketiga (message) bersifat opsional
    echo jsonResponse($result['data'], true, 'Data GL Account berhasil diambil');
} else {
    echo jsonResponse(null, false, 'Gagal mengambil data GL Account: ' . ($result['error'] ?? 'Unknown error'));
}
?>
