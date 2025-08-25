<?php
require_once '../bootstrap.php';

header('Content-Type: application/json');

try {
    // Cek method harus GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed. Use GET method.', 405);
    }
    
    // Validasi parameter wajib
    $no = $_GET['no'] ?? '';
    
    if (empty($no)) {
        throw new Exception('Parameter "no" (item number) is required', 400);
    }
    
    // Parameter optional
    $branchName = $_GET['branchName'] ?? '';
    $priceCategoryName = $_GET['priceCategoryName'] ?? '';
    
    // Initialize AccurateAPI
    $api = new AccurateAPI();
    
    // Use the new getItemSellingPrice method
    $result = $api->getItemSellingPrice($no, $branchName, $priceCategoryName);
    
    if ($result['success']) {
        // Log successful request for debugging
        error_log("Selling Price API Success - Item: {$no}, Branch: " . ($branchName ?: 'default') . ", PriceCategory: " . ($priceCategoryName ?: 'default'));
        
        // Return successful response in consistent format
        echo json_encode([
            'success' => true,
            'message' => 'Selling price retrieved successfully',
            'data' => $result['data'],
            'request_info' => [
                'item_no' => $no,
                'branch_name' => $branchName ?: 'default',
                'price_category_name' => $priceCategoryName ?: 'default',
                'scope_required' => 'item_view',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Failed to get selling price', $result['http_code'] ?? 500);
    }
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    
    // Log error for debugging
    error_log("Selling Price API Error - Code: {$statusCode}, Message: " . $e->getMessage());
    
    // Set appropriate HTTP status code
    http_response_code($statusCode);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $statusCode,
        'request_info' => [
            'item_no' => $_GET['no'] ?? 'not provided',
            'branch_name' => $_GET['branchName'] ?? 'not provided',
            'price_category_name' => $_GET['priceCategoryName'] ?? 'not provided',
            'method' => 'GET',
            'scope_required' => 'item_view',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}
?>
