<?php
/**
 * Test Unauthorized Scopes
 * Verify that item_brand_view and job_order_view show as NOT AUTHORIZED
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Unauthorized Scopes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .highlight { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Test Unauthorized Scopes</h1>
    
    <div class="highlight">
        <p><strong>Test Case:</strong> Memverifikasi bahwa scope <code>item_brand_view</code> dan <code>job_order_view</code> yang belum diotorisasi ditampilkan sebagai "NOT AUTHORIZED"</p>
    </div>
    
    <div class="section">
        <h2>1. OAuth Token Response Analysis</h2>
        <?php
        $tokenResponse = $api->checkTokenStatus();
        
        if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
            $tokenData = $tokenResponse['data'];
            
            echo "<p><strong>Token Active:</strong> <span class='" . (isset($tokenData['active']) && $tokenData['active'] ? 'success' : 'error') . "'>" . (isset($tokenData['active']) && $tokenData['active'] ? 'YES' : 'NO') . "</span></p>";
            
            if (isset($tokenData['scope'])) {
                $authorizedScopes = explode(' ', $tokenData['scope']);
                echo "<p><strong>Total Authorized Scopes:</strong> " . count($authorizedScopes) . "</p>";
                
                // Check specifically for the problematic scopes
                $itemBrandAuthorized = in_array('item_brand_view', $authorizedScopes);
                $jobOrderAuthorized = in_array('job_order_view', $authorizedScopes);
                
                echo "<div class='highlight'>";
                echo "<p><strong>Specific Scope Check:</strong></p>";
                echo "<ul>";
                echo "<li><code>item_brand_view</code> in OAuth response: <span class='" . ($itemBrandAuthorized ? 'success' : 'error') . "'>" . ($itemBrandAuthorized ? 'YES' : 'NO') . "</span></li>";
                echo "<li><code>job_order_view</code> in OAuth response: <span class='" . ($jobOrderAuthorized ? 'success' : 'error') . "'>" . ($jobOrderAuthorized ? 'YES' : 'NO') . "</span></li>";
                echo "</ul>";
                echo "</div>";
                
                echo "<details>";
                echo "<summary>All Authorized Scopes (Click to expand)</summary>";
                echo "<ul>";
                foreach ($authorizedScopes as $scope) {
                    $isProblematic = in_array($scope, ['item_brand_view', 'job_order_view']);
                    echo "<li class='" . ($isProblematic ? 'warning' : 'success') . "'>{$scope}" . ($isProblematic ? ' ⚠️ PROBLEMATIC' : '') . "</li>";
                }
                echo "</ul>";
                echo "</details>";
            } else {
                echo "<p class='error'>No scope information in token response</p>";
            }
        } else {
            echo "<p class='error'>Failed to get token status</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>2. Scope Validation Test</h2>
        <?php
        // Test the exact logic from token-status.php
        $tokenStatus = [
            'valid' => false,
            'scopes' => []
        ];
        
        $requiredScopes = [
            'item_view' => 'Item View',
            'branch_view' => 'Branch View', 
            'vendor_view' => 'Vendor View',
            'customer_view' => 'Customer View',
            'warehouse_view' => 'Warehouse View',
            'sales_invoice_view' => 'Sales Invoice View',
            'purchase_invoice_view' => 'Purchase Invoice View',
            'item_brand_view' => 'Item Brand View',
            'job_order_view' => 'Job Order View'
        ];
        
        if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
            $tokenData = $tokenResponse['data'];
            
            if (isset($tokenData['active']) && $tokenData['active'] === true) {
                // Get authorized scopes from OAuth response
                $authorizedScopes = [];
                if (isset($tokenData['scope'])) {
                    $authorizedScopes = explode(' ', $tokenData['scope']);
                }
                
                // Check each required scope against OAuth authorized scopes
                $allScopesValid = true;
                
                foreach ($requiredScopes as $scope => $description) {
                    // Check if scope is actually authorized in OAuth response
                    $isAuthorized = in_array($scope, $authorizedScopes);
                    
                    $tokenStatus['scopes'][$scope] = $isAuthorized;
                    
                    if (!$isAuthorized) {
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
        
        echo "<p><strong>Overall Token Valid:</strong> <span class='" . ($tokenStatus['valid'] ? 'success' : 'error') . "'>" . ($tokenStatus['valid'] ? 'YES' : 'NO') . "</span></p>";
        
        echo "<table>";
        echo "<tr><th>Required Scope</th><th>Description</th><th>Authorized</th><th>Status</th><th>Expected</th></tr>";
        
        foreach ($requiredScopes as $scope => $description) {
            $isAuthorized = $tokenStatus['scopes'][$scope];
            $statusText = $isAuthorized ? 'AVAILABLE' : 'NOT AUTHORIZED';
            $statusClass = $isAuthorized ? 'success' : 'error';
            
            // Expected result for problematic scopes
            $isProblematic = in_array($scope, ['item_brand_view', 'job_order_view']);
            $expectedText = $isProblematic ? 'Should be NOT AUTHORIZED' : 'Should be AVAILABLE';
            $expectedClass = $isProblematic ? ($isAuthorized ? 'error' : 'success') : ($isAuthorized ? 'success' : 'warning');
            
            echo "<tr" . ($isProblematic ? " style='background-color: #fff3cd;'" : "") . ">";
            echo "<td><strong>{$scope}</strong>" . ($isProblematic ? ' 🔍' : '') . "</td>";
            echo "<td>{$description}</td>";
            echo "<td class='" . ($isAuthorized ? 'success' : 'error') . "'>" . ($isAuthorized ? 'YES' : 'NO') . "</td>";
            echo "<td class='{$statusClass}'>{$statusText}</td>";
            echo "<td class='{$expectedClass}'>{$expectedText}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>
    
    <div class="section">
        <h2>3. Test Results Summary</h2>
        <?php
        $itemBrandStatus = $tokenStatus['scopes']['item_brand_view'] ?? false;
        $jobOrderStatus = $tokenStatus['scopes']['job_order_view'] ?? false;
        
        echo "<div class='highlight'>";
        echo "<p><strong>Test Results:</strong></p>";
        echo "<ul>";
        echo "<li><code>item_brand_view</code>: <span class='" . ($itemBrandStatus ? 'error' : 'success') . "'>" . ($itemBrandStatus ? '❌ INCORRECTLY SHOWING AS AUTHORIZED' : '✅ CORRECTLY SHOWING AS NOT AUTHORIZED') . "</span></li>";
        echo "<li><code>job_order_view</code>: <span class='" . ($jobOrderStatus ? 'error' : 'success') . "'>" . ($jobOrderStatus ? '❌ INCORRECTLY SHOWING AS AUTHORIZED' : '✅ CORRECTLY SHOWING AS NOT AUTHORIZED') . "</span></li>";
        echo "</ul>";
        
        if (!$itemBrandStatus && !$jobOrderStatus) {
            echo "<p class='success'><strong>✅ PASSED:</strong> Kedua scope yang belum diotorisasi ditampilkan dengan benar sebagai 'NOT AUTHORIZED'</p>";
        } else {
            echo "<p class='error'><strong>❌ FAILED:</strong> Ada scope yang belum diotorisasi tapi ditampilkan sebagai 'AUTHORIZED'</p>";
            echo "<p class='warning'><strong>Kemungkinan penyebab:</strong></p>";
            echo "<ul>";
            echo "<li>Scope tersebut sebenarnya sudah diotorisasi di OAuth server</li>";
            echo "<li>Ada masalah dengan parsing OAuth response</li>";
            echo "<li>Token yang digunakan berbeda dari yang diharapkan</li>";
            echo "</ul>";
        }
        echo "</div>";
        ?>
    </div>
    
    <div class="section">
        <h2>4. Debugging Information</h2>
        <details>
            <summary>Raw OAuth Response (Click to expand)</summary>
            <pre><?php echo json_encode($tokenResponse, JSON_PRETTY_PRINT); ?></pre>
        </details>
    </div>
    
</body>
</html>