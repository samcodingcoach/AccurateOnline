<?php
/**
 * Test Scope Fix
 * Verify that scope detection is working correctly after the fix
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Scope Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Test Scope Fix</h1>
    
    <?php
    // Test getApprovedScopes method
    echo "<h2>Test getApprovedScopes Method</h2>";
    
    try {
        $approvedScopes = $api->getApprovedScopes();
        
        echo "<p><strong>Method Success:</strong> <span class='" . ($approvedScopes['success'] ? 'success' : 'error') . "'>" . ($approvedScopes['success'] ? 'TRUE' : 'FALSE') . "</span></p>";
        echo "<p><strong>HTTP Code:</strong> " . $approvedScopes['http_code'] . "</p>";
        
        if ($approvedScopes['success']) {
            echo "<p><strong>Approved Scopes Count:</strong> " . count($approvedScopes['data']) . "</p>";
            echo "<p><strong>Approved Scopes:</strong></p>";
            echo "<ul>";
            foreach ($approvedScopes['data'] as $scope) {
                echo "<li class='success'>{$scope}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><strong>Error:</strong> <span class='error'>" . ($approvedScopes['error'] ?? 'Unknown error') . "</span></p>";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>Exception:</strong> <span class='error'>" . $e->getMessage() . "</span></p>";
    }
    
    // Test individual scope methods
    echo "<h2>Test Individual Scope Methods</h2>";
    
    $scopes = [
        'item_view' => 'testItemView',
        'branch_view' => 'testBranchView',
        'vendor_view' => 'testVendorView',
        'customer_view' => 'testCustomerView',
        'warehouse_view' => 'testWarehouseView',
        'sales_invoice_view' => 'testSalesInvoiceView',
        'purchase_invoice_view' => 'testPurchaseInvoiceView'
    ];
    
    echo "<table>";
    echo "<tr><th>Scope</th><th>Method</th><th>Success</th><th>HTTP Code</th><th>Status</th></tr>";
    
    foreach ($scopes as $scope => $method) {
        try {
            $result = $api->$method();
            $success = $result['success'];
            $httpCode = $result['http_code'];
            $status = $success ? 'AVAILABLE' : 'NOT AVAILABLE';
            $statusClass = $success ? 'success' : 'error';
            
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td>{$method}</td>";
            echo "<td class='" . ($success ? 'success' : 'error') . "'>" . ($success ? 'YES' : 'NO') . "</td>";
            echo "<td>{$httpCode}</td>";
            echo "<td class='{$statusClass}'>{$status}</td>";
            echo "</tr>";
            
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td>{$method}</td>";
            echo "<td class='error'>EXCEPTION</td>";
            echo "<td>-</td>";
            echo "<td class='error'>ERROR</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Test token-status.php logic simulation
    echo "<h2>Simulate token-status.php Logic</h2>";
    
    // Check token status
    $tokenResponse = $api->checkTokenStatus();
    
    // Process token response to create compatible structure
    $tokenStatus = [
        'valid' => false,
        'scopes' => []
    ];
    
    // Define required scopes to test
    $requiredScopes = [
        'item_view' => 'Item View',
        'branch_view' => 'Branch View', 
        'vendor_view' => 'Vendor View',
        'customer_view' => 'Customer View',
        'warehouse_view' => 'Warehouse View',
        'sales_invoice_view' => 'Sales Invoice View',
        'purchase_invoice_view' => 'Purchase Invoice View'
    ];
    
    if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
        $tokenData = $tokenResponse['data'];
        
        // Check if token is valid based on response structure
        if (isset($tokenData['active']) && $tokenData['active'] === true) {
            // Test each required scope by making actual API calls
            $allScopesValid = true;
            
            foreach ($requiredScopes as $scope => $description) {
                $testResult = false;
                
                // Test scope by calling corresponding API method
                switch ($scope) {
                    case 'item_view':
                        $testResult = $api->testItemView()['success'];
                        break;
                    case 'branch_view':
                        $testResult = $api->testBranchView()['success'];
                        break;
                    case 'vendor_view':
                        $testResult = $api->testVendorView()['success'];
                        break;
                    case 'customer_view':
                        $testResult = $api->testCustomerView()['success'];
                        break;
                    case 'warehouse_view':
                        $testResult = $api->testWarehouseView()['success'];
                        break;
                    case 'sales_invoice_view':
                        $testResult = $api->testSalesInvoiceView()['success'];
                        break;
                    case 'purchase_invoice_view':
                        $testResult = $api->testPurchaseInvoiceView()['success'];
                        break;
                }
                
                $tokenStatus['scopes'][$scope] = $testResult;
                
                if (!$testResult) {
                    $allScopesValid = false;
                }
            }
            
            $tokenStatus['valid'] = $allScopesValid;
        } else {
            // Token is not active, mark all scopes as invalid
            foreach ($requiredScopes as $scope => $description) {
                $tokenStatus['scopes'][$scope] = false;
            }
        }
    } else {
        // API call failed, mark all scopes as invalid
        foreach ($requiredScopes as $scope => $description) {
            $tokenStatus['scopes'][$scope] = false;
        }
    }
    
    echo "<p><strong>Token Valid:</strong> <span class='" . ($tokenStatus['valid'] ? 'success' : 'error') . "'>" . ($tokenStatus['valid'] ? 'TRUE' : 'FALSE') . "</span></p>";
    
    echo "<h3>Scope Status Results:</h3>";
    echo "<table>";
    echo "<tr><th>Scope</th><th>Status</th><th>Description</th></tr>";
    
    foreach ($tokenStatus['scopes'] as $scope => $status) {
        $statusText = $status ? 'AVAILABLE' : 'NOT AVAILABLE';
        $statusClass = $status ? 'success' : 'error';
        $description = $requiredScopes[$scope] ?? $scope;
        
        echo "<tr>";
        echo "<td>{$scope}</td>";
        echo "<td class='{$statusClass}'>{$statusText}</td>";
        echo "<td>{$description}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    ?>
    
</body>
</html>