<?php
header('Content-Type: application/json');
// Turn off error display to prevent HTML output
ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit();
}

try {
    require_once '../bootstrap.php';
    require_once '../classes/AccurateAPI.php';
    
    // Debug: Log request info
    error_log("Invoice submission request received");
    error_log("Headers: " . json_encode(getallheaders()));
    
    // Get session ID
    $sessionId = $_SERVER['HTTP_X_SESSION_ID'] ?? '';
    error_log("Session ID: " . $sessionId);
    
    if (empty($sessionId)) {
        throw new Exception('X-Session-ID required');
    }
    
    // Parse input data - MANUAL untuk handle detailItem[0].itemNo
    $rawInput = file_get_contents('php://input');
    $inputData = [];
    
    error_log("Raw input: " . substr($rawInput, 0, 500)); // Log first 500 chars
    
    if (!empty($rawInput)) {
        // Manual parsing seperti yang berhasil di test awal
        $pairs = explode('&', $rawInput);
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $inputData[urldecode($key)] = urldecode($value);
            }
        }
    } else {
        $inputData = $_POST;
    }
    
    error_log("Parsed input data count: " . count($inputData));
    
    // Validate required fields
    if (empty($inputData['customerNo'])) {
        throw new Exception('customerNo required');
    }
    if (empty($inputData['branchId'])) {
        throw new Exception('branchId required');
    }
    
    error_log("Customer: " . $inputData['customerNo']);
    error_log("Branch: " . $inputData['branchId']);
    
    // Check for detail items
    $hasItems = false;
    foreach ($inputData as $key => $value) {
        if (strpos($key, 'detailItem') === 0 && strpos($key, 'itemNo') !== false) {
            $hasItems = true;
            break;
        }
    }
    
    if (!$hasItems) {
        throw new Exception('At least one item required');
    }
    
    // Call API
    $api = new AccurateAPI();
    $api->setSessionId($sessionId);
    
    error_log("Calling createSalesInvoice API");
    $response = $api->createSalesInvoice($inputData);
    error_log("API response: " . json_encode($response));
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
} catch (Error $e) {
    error_log("Fatal Error: " . $e->getMessage());
    error_log("Fatal Error trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
