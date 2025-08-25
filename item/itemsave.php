<?php
/**
 * Item Save API
 * File: /item/itemsave.php
 * HTTP Method: POST
 * Scope: item_save
 * Endpoint: /api/item/save.do
 * X-Session-ID header required
 */

require_once __DIR__ . '/../bootstrap.php';

// Enable error reporting for debugging but don't display errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set header untuk JSON response
header('Content-Type: application/json; charset=UTF-8');

// Add CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Session-ID');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle hanya untuk method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo jsonResponse(null, false, 'Method tidak diizinkan. Gunakan POST.');
    exit;
}

try {
    // Debug: Log semua info request (disabled for production)
    // error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
    // error_log('Content Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    // error_log('HTTP Headers: ' . print_r(getallheaders(), true));
    
    $requestData = null;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    // Handle berbagai content type
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON data
        $rawData = file_get_contents('php://input');
        // error_log('Raw JSON data: ' . $rawData);
        
        if (empty($rawData)) {
            echo jsonResponse(null, false, 'No JSON data received in request body.');
            exit;
        }
        
        // Clean BOM if exists
        $rawData = str_replace("\xEF\xBB\xBF", '', $rawData);
        $rawData = trim($rawData);
        
        $requestData = json_decode($rawData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            $errorDetails = [
                'error' => $jsonError,
                'error_code' => json_last_error(),
                'raw_data_preview' => substr($rawData, 0, 200),
                'raw_data_length' => strlen($rawData)
            ];
            // error_log('JSON decode error: ' . print_r($errorDetails, true));
            echo jsonResponse($errorDetails, false, 'Invalid JSON format: ' . $jsonError);
            exit;
        }
        
    } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
        // Handle form-encoded data
        $rawData = file_get_contents('php://input');
        // error_log('Raw form data: ' . $rawData);
        
        if (empty($rawData)) {
            echo jsonResponse(null, false, 'No form data received in request body.');
            exit;
        }
        
        parse_str($rawData, $requestData);
        // error_log('Parsed form data: ' . print_r($requestData, true));
        
    } else {
        // Try $_POST as fallback
        if (!empty($_POST)) {
            $requestData = $_POST;
            // error_log('Using $_POST data: ' . print_r($requestData, true));
        } else {
            echo jsonResponse(null, false, 'No data received. Please send data as JSON (application/json) or form-encoded (application/x-www-form-urlencoded).');
            exit;
        }
    }
    
    // Cek apakah result adalah array
    if (!is_array($requestData)) {
        echo jsonResponse(['decoded_type' => gettype($requestData), 'decoded_value' => $requestData], false, 'Request data must be an array/object');
        exit;
    }
    
    // Validasi parameter required
    $requiredFields = ['itemCategoryName', 'name', 'unit1Name', 'manageSN', 'no'];
    $validation = validateRequired($requestData, $requiredFields);
    
    if ($validation !== true) {
        echo jsonResponse(null, false, 'Validation failed: ' . implode(', ', $validation));
        exit;
    }
    
    // Sanitize input data dan handle boolean conversion
    $itemData = [
        'no' => sanitizeInput($requestData['no']),
        'itemCategoryName' => sanitizeInput($requestData['itemCategoryName']),
        'itemType' => 'INVENTORY', // static value
        'name' => sanitizeInput($requestData['name']),
        'unit1Name' => sanitizeInput($requestData['unit1Name']),
        'manageSN' => convertToBoolean($requestData['manageSN']),
        'serialNumberType' => 'UNIQUE' // static value
    ];
    
    // Validasi manageSN harus boolean
    if ($itemData['manageSN'] === null) {
        echo jsonResponse([
            'received_value' => $requestData['manageSN'],
            'received_type' => gettype($requestData['manageSN']),
            'expected' => 'boolean true/false or string "true"/"false"/"True"/"False"/1/0'
        ], false, 'Parameter manageSN harus berupa boolean atau string boolean yang valid');
        exit;
    }
    
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    // Validasi session ID dari header
    $sessionId = $_SERVER['HTTP_X_SESSION_ID'] ?? null;
    if (!$sessionId) {
        echo jsonResponse(null, false, 'X-Session-ID header is required');
        exit;
    }
    
    // Set session ID jika berbeda dari config
    if ($sessionId !== ACCURATE_SESSION_ID) {
        $api->setSessionId($sessionId);
    }
    
    // Save item via API
    $result = $api->saveItem($itemData);
    
    if ($result['success']) {
        // Format response data
        $responseData = [
            'item' => $result['data'],
            'meta' => [
                'scope_required' => 'item_save',
                'http_code' => $result['http_code'],
                'endpoint' => '/accurate/api/item/save.do'
            ]
        ];
        
        echo jsonResponse($responseData, true, 'Item berhasil disimpan');
    } else {
        // Log error untuk debugging
        $errorMessage = $result['error'] ?? 'Unknown error';
        logError("Failed to save item: " . $errorMessage, __FILE__, __LINE__);
        
        echo jsonResponse(null, false, 'Gagal menyimpan item: ' . $errorMessage);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    logError("Exception in item save API: " . $e->getMessage(), __FILE__, $e->getLine());
    echo jsonResponse(null, false, 'Terjadi kesalahan internal server: ' . $e->getMessage());
}
?>
