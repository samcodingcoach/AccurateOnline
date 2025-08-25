<?php
/**
 * API untuk mendapatkan informasi branch/cabang
 * Berdasarkan dokumentasi Accurate API untuk branch
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Cek apakah ada parameter ID atau nama branch
$branchId = $_GET['id'] ?? $_POST['id'] ?? null;
$branchName = $_GET['branchName'] ?? $_POST['branchName'] ?? null;

if ($branchId) {
    // Get branch detail by ID
    $result = $api->getBranchDetail($branchId);
    $message = "Detail branch dengan ID: $branchId";
    
    // Jika ID tidak valid, berikan saran ID yang valid
    if (!$result['success'] && 
        isset($result['message']) && 
        strpos($result['message'], 'Invalid field value') !== false) {
        $branchList = $api->getBranchList();
        if ($branchList['success'] && isset($branchList['data']['data'])) {
            $validIds = array_column($branchList['data']['data'], 'id');
            $result['data'] = [
                'error' => 'Invalid branch ID',
                'provided_id' => $branchId,
                'valid_ids' => $validIds,
                'suggestion' => 'Use one of the valid IDs from the valid_ids array'
            ];
            $result['message'] = 'Branch ID tidak valid. Gunakan ID yang valid dari daftar.';
        }
    }
} elseif ($branchName) {
    // Get branch detail by name
    $result = $api->getBranchByName($branchName);
    $message = "Detail branch dengan nama: $branchName";
} else {
    // Get branch list (tanpa parameter)
    $result = $api->getBranchList();
    $message = "Informasi branch";
}

if ($result['success']) {
    echo jsonResponse($result['data'], true, $message);
} else {
    $errorMessage = 'Gagal mengambil data branch: ' . ($result['error'] ?? 'Unknown error');
    
    // Tambahkan informasi debug jika diperlukan
    if (isset($result['http_code'])) {
        $errorMessage .= " (HTTP Code: {$result['http_code']})";
    }
    
    echo jsonResponse(null, false, $errorMessage);
}
?>
