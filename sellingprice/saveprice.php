<?php
/**
 * Selling Price Adjustment Save API
 * File: /sellingprice/saveprice.php
 * HTTP Method: POST
 * Scope: sellingprice_save
 * Endpoint: /api/sellingprice-adjustment/save.do
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
    // Debug: Log semua info request
    error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
    error_log('Content Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    error_log('HTTP Headers: ' . print_r(getallheaders(), true));
    
    $requestData = null;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    // Get raw input once
    $rawData = file_get_contents('php://input');
    error_log('Raw input length: ' . strlen($rawData));
    error_log('Raw input data: ' . $rawData);
    
    // Also log $_POST for comparison
    error_log('$_POST data: ' . print_r($_POST, true));
    
    // Handle berbagai content type
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON data
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
            error_log('JSON decode error: ' . print_r($errorDetails, true));
            echo jsonResponse($errorDetails, false, 'Invalid JSON format: ' . $jsonError);
            exit;
        }
        
    } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
        // Handle form-encoded data
        if (empty($rawData)) {
            echo jsonResponse(null, false, 'No form data received in request body.');
            exit;
        }
        
        // Custom parsing for dot notation (detailItem[0].itemNo)
        $requestData = [];
        $pairs = explode('&', $rawData);
        
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) == 2) {
                $key = urldecode($parts[0]);
                $value = urldecode($parts[1]);
                
                // Handle dot notation for detailItem
                if (preg_match('/detailItem\[(\d+)\]\.(.+)/', $key, $matches)) {
                    $index = intval($matches[1]);
                    $field = $matches[2];
                    
                    if (!isset($requestData['detailItem'])) {
                        $requestData['detailItem'] = [];
                    }
                    if (!isset($requestData['detailItem'][$index])) {
                        $requestData['detailItem'][$index] = [];
                    }
                    
                    $requestData['detailItem'][$index][$field] = $value;
                } else {
                    // Regular field
                    $requestData[$key] = $value;
                }
            }
        }
        
        error_log('Custom parsed form data: ' . print_r($requestData, true));
        
    } else {
        // Try $_POST as fallback
        if (!empty($_POST)) {
            $requestData = $_POST;
            error_log('Using $_POST data: ' . print_r($requestData, true));
        } elseif (!empty($rawData)) {
            // Try to auto-detect and parse
            if (strpos($rawData, '=') !== false && strpos($rawData, '&') !== false) {
                // Looks like form data
                parse_str($rawData, $requestData);
                error_log('Auto-detected form data: ' . print_r($requestData, true));
            } else {
                // Try JSON
                $jsonDecoded = json_decode($rawData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $requestData = $jsonDecoded;
                    error_log('Auto-detected JSON data: ' . print_r($requestData, true));
                } else {
                    echo jsonResponse(['raw_data' => $rawData], false, 'Could not parse request data. Please use application/json or application/x-www-form-urlencoded content type.');
                    exit;
                }
            }
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
    $requiredFields = ['detailItem', 'salesAdjustmentType'];
    $validation = validateRequired($requestData, $requiredFields);
    
    if ($validation !== true) {
        echo jsonResponse(null, false, 'Validation failed: ' . implode(', ', $validation));
        exit;
    }
    
    // Validasi detailItem array structure
    if (!isset($requestData['detailItem']) || !is_array($requestData['detailItem']) || empty($requestData['detailItem'])) {
        echo jsonResponse([
            'received_data' => $requestData,
            'detailItem_exists' => isset($requestData['detailItem']),
            'detailItem_type' => gettype($requestData['detailItem'] ?? null),
            'detailItem_value' => $requestData['detailItem'] ?? null
        ], false, 'detailItem must be a non-empty array. Use format: detailItem[0].itemNo and detailItem[0].price');
        exit;
    }
    
    // Validasi detailItem[0] fields
    if (!isset($requestData['detailItem'][0]) || !is_array($requestData['detailItem'][0])) {
        echo jsonResponse([
            'received_data' => $requestData,
            'detailItem_0_exists' => isset($requestData['detailItem'][0]),
            'detailItem_0_type' => gettype($requestData['detailItem'][0] ?? null)
        ], false, 'detailItem[0] is missing or invalid');
        exit;
    }
    
    $firstDetail = $requestData['detailItem'][0];
    if (!isset($firstDetail['itemNo']) || !isset($firstDetail['price'])) {
        echo jsonResponse([
            'received_data' => $requestData,
            'first_detail' => $firstDetail,
            'has_itemNo' => isset($firstDetail['itemNo']),
            'has_price' => isset($firstDetail['price'])
        ], false, 'detailItem[0] must contain itemNo and price fields. Use format: detailItem[0].itemNo and detailItem[0].price');
        exit;
    }
    
    // Prepare data dengan current date
    $currentDate = date('d/m/Y'); // Format: 09/08/2025
    
    // Build selling price data
    $sellingPriceData = [
        'detailItem' => [],
        'salesAdjustmentType' => sanitizeInput($requestData['salesAdjustmentType']),
        'transDate' => $currentDate
    ];
    
    // Add id if provided
    if (isset($requestData['id']) && !empty($requestData['id'])) {
        $sellingPriceData['id'] = sanitizeInput($requestData['id']);
        error_log('Price Level ID provided: ' . $sellingPriceData['id']);
        
        // Special debug for ID 100
        if ($sellingPriceData['id'] == '200') {
            error_log('*** DEBUGGING PRICE LEVEL 2 (ID=200) ***');
            error_log('Full request data: ' . print_r($requestData, true));
            error_log('Selling price data to be sent: ' . print_r($sellingPriceData, true));
        }
    }
    
    // Process detail items
    foreach ($requestData['detailItem'] as $index => $detail) {
        if (!isset($detail['itemNo']) || !isset($detail['price'])) {
            echo jsonResponse(null, false, "detailItem[$index] must contain itemNo and price fields");
            exit;
        }
        
        $sellingPriceData['detailItem'][] = [
            'itemNo' => sanitizeInput($detail['itemNo']),
            'price' => floatval($detail['price'])
        ];
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
    
    // Save selling price via API
    $result = $api->saveSellingPrice($sellingPriceData);
    
    // Special debug for ID 100
    if (isset($sellingPriceData['id']) && $sellingPriceData['id'] == '200') {
        error_log('*** PRICE LEVEL 2 (ID=200) API RESULT ***');
        error_log('Success: ' . ($result['success'] ? 'YES' : 'NO'));
        error_log('Full result: ' . print_r($result, true));
    }
    
    if ($result['success']) {
        // Format response data
        $responseData = [
            'sellingPrice' => $result['data'],
            'meta' => [
                'scope_required' => 'sellingprice_save',
                'http_code' => $result['http_code'],
                'endpoint' => '/accurate/api/sellingprice-adjustment/save.do',
                'trans_date' => $currentDate
            ]
        ];
        
        echo jsonResponse($responseData, true, 'Selling price adjustment berhasil disimpan');
    } else {
        // Log error untuk debugging
        $errorMessage = $result['error'] ?? 'Unknown error';
        logError("Failed to save selling price: " . $errorMessage, __FILE__, __LINE__);
        
        echo jsonResponse(null, false, 'Gagal menyimpan selling price adjustment: ' . $errorMessage);
    }

} catch (Exception $e) {
    // Handle unexpected errors
    logError("Exception in selling price save API: " . $e->getMessage(), __FILE__, $e->getLine());
    echo jsonResponse(null, false, 'Terjadi kesalahan internal server: ' . $e->getMessage());
}
?>
