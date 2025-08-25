<?php
header('Content-Type: application/json');
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit();
}

try {
    require_once '../bootstrap.php';
    require_once '../classes/AccurateAPI.php';
    
    // Get session ID
    $sessionId = $_SERVER['HTTP_X_SESSION_ID'] ?? '';
    if (empty($sessionId)) {
        throw new Exception('X-Session-ID required');
    }
    
    // Parse input data - MANUAL untuk handle detailItem[0].itemNo
    $rawInput = file_get_contents('php://input');
    $inputData = [];
    
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
    
    // Validate required fields
    if (empty($inputData['customerNo'])) {
        throw new Exception('customerNo required');
    }
    if (empty($inputData['branchId'])) {
        throw new Exception('branchId required');
    }
    
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
    $response = $api->createSalesOrder($inputData);
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
