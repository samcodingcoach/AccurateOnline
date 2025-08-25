<?php
/**
 * Simple Debug Scope Test
 * Test individual scope methods to identify why they return 'not available'
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Scope Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Debug Scope Test</h1>
    
    <?php
    // Test basic configuration
    echo "<h2>Configuration Check</h2>";
    echo "<p><strong>Access Token:</strong> " . (ACCURATE_ACCESS_TOKEN ? substr(ACCURATE_ACCESS_TOKEN, 0, 20) . "..." : "NOT SET") . "</p>";
    echo "<p><strong>Session ID:</strong> " . (ACCURATE_SESSION_ID ? ACCURATE_SESSION_ID : "NOT SET") . "</p>";
    echo "<p><strong>API Host:</strong> " . ACCURATE_API_HOST . "</p>";
    echo "<p><strong>Database ID:</strong> " . ACCURATE_DATABASE_ID . "</p>";
    
    // Test simple item API call
    echo "<h2>Simple Item API Test</h2>";
    
    try {
        $result = $api->testItemView();
        
        echo "<p><strong>Success:</strong> <span class='" . ($result['success'] ? 'success' : 'error') . "'>" . ($result['success'] ? 'TRUE' : 'FALSE') . "</span></p>";
        echo "<p><strong>HTTP Code:</strong> " . $result['http_code'] . "</p>";
        
        if (!$result['success']) {
            echo "<p><strong>Error:</strong> <span class='error'>" . ($result['error'] ?? 'Unknown error') . "</span></p>";
        }
        
        if (isset($result['raw_response'])) {
            $response = $result['raw_response'];
            echo "<p><strong>Raw Response (first 500 chars):</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            
            // Try to decode JSON
            $decoded = json_decode($response, true);
            if ($decoded) {
                echo "<p><strong>Decoded JSON:</strong></p>";
                echo "<pre>" . htmlspecialchars(print_r($decoded, true)) . "</pre>";
            } else {
                echo "<p><strong>JSON Decode Error:</strong> <span class='error'>" . json_last_error_msg() . "</span></p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p><strong>Exception:</strong> <span class='error'>" . $e->getMessage() . "</span></p>";
    }
    
    // Test all scopes
    echo "<h2>All Scope Tests</h2>";
    
    $scopes = [
        'item_view' => 'testItemView',
        'branch_view' => 'testBranchView',
        'vendor_view' => 'testVendorView',
        'customer_view' => 'testCustomerView',
        'warehouse_view' => 'testWarehouseView',
        'sales_invoice_view' => 'testSalesInvoiceView',
        'purchase_invoice_view' => 'testPurchaseInvoiceView'
    ];
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Scope</th><th>Method</th><th>Success</th><th>HTTP Code</th><th>Error</th></tr>";
    
    foreach ($scopes as $scope => $method) {
        try {
            $result = $api->$method();
            $success = $result['success'] ? 'YES' : 'NO';
            $successClass = $result['success'] ? 'success' : 'error';
            $httpCode = $result['http_code'];
            $error = $result['error'] ?? '-';
            
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td>{$method}</td>";
            echo "<td class='{$successClass}'>{$success}</td>";
            echo "<td>{$httpCode}</td>";
            echo "<td class='error'>" . htmlspecialchars($error) . "</td>";
            echo "</tr>";
            
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td>{$method}</td>";
            echo "<td class='error'>EXCEPTION</td>";
            echo "<td>-</td>";
            echo "<td class='error'>" . htmlspecialchars($e->getMessage()) . "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Test checkTokenStatus method
    echo "<h2>Token Status Check</h2>";
    
    try {
        $tokenResponse = $api->checkTokenStatus();
        
        echo "<p><strong>Success:</strong> <span class='" . ($tokenResponse['success'] ? 'success' : 'error') . "'>" . ($tokenResponse['success'] ? 'TRUE' : 'FALSE') . "</span></p>";
        echo "<p><strong>HTTP Code:</strong> " . $tokenResponse['http_code'] . "</p>";
        
        if (!$tokenResponse['success']) {
            echo "<p><strong>Error:</strong> <span class='error'>" . ($tokenResponse['error'] ?? 'Unknown error') . "</span></p>";
        }
        
        if (isset($tokenResponse['data'])) {
            echo "<p><strong>Token Data:</strong></p>";
            echo "<pre>" . htmlspecialchars(print_r($tokenResponse['data'], true)) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>Exception:</strong> <span class='error'>" . $e->getMessage() . "</span></p>";
    }
    ?>
    
</body>
</html>