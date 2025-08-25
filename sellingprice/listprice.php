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
    
    // Get session ID
    $sessionId = $api->getSessionId();
    
    if (!$sessionId) {
        throw new Exception('Failed to get session ID. Please check API configuration.', 500);
    }
    
    // Prepare API endpoint
    $endpoint = '/accurate/api/item/get-selling-price.do';
    
    // Prepare query parameters
    $params = [
        'no' => $no
    ];
    
    // Add optional parameters if provided
    if (!empty($branchName)) {
        $params['branchName'] = $branchName;
    }
    
    if (!empty($priceCategoryName)) {
        $params['priceCategoryName'] = $priceCategoryName;
    }
    
    // Build query string
    $queryString = http_build_query($params);
    $fullUrl = $endpoint . '?' . $queryString;
    
    // Prepare headers
    $headers = [
        'Authorization: Bearer ' . $api->getCurrentAccessToken(),
        'X-Session-ID: ' . $sessionId,
        'Content-Type: application/json'
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api->getBaseUrl() . $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curlError) {
        throw new Exception('cURL Error: ' . $curlError, 500);
    }
    
    // Check HTTP status
    if ($httpCode !== 200) {
        $errorResponse = json_decode($response, true);
        $errorMessage = $errorResponse['s'] ?? 'HTTP Error ' . $httpCode;
        throw new Exception($errorMessage, $httpCode);
    }
    
    // Decode response
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API', 500);
    }
    
    // Log successful request for debugging
    error_log("Selling Price API Success - Item: {$no}, Branch: " . ($branchName ?: 'default') . ", PriceCategory: " . ($priceCategoryName ?: 'default') . ", Response: " . substr($response, 0, 200));
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'message' => 'Selling price retrieved successfully',
        'data' => $data,
        'request_info' => [
            'item_no' => $no,
            'branch_name' => $branchName ?: 'default',
            'price_category_name' => $priceCategoryName ?: 'default',
            'endpoint' => $fullUrl,
            'scope_required' => 'item_view',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
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
            'endpoint' => '/accurate/api/item/get-selling-price.do',
            'method' => 'GET',
            'scope_required' => 'item_view',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}
?>
