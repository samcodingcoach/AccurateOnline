<?php
/**
 * Test OAuth Validation Fix
 * Verify that scope validation now works correctly based on OAuth authorization
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test OAuth Validation Fix</title>
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
    </style>
</head>
<body>
    <h1>Test OAuth Validation Fix</h1>
    
    <div class="section">
        <h2>1. OAuth Token Response</h2>
        <?php
        $tokenResponse = $api->checkTokenStatus();
        
        if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
            $tokenData = $tokenResponse['data'];
            
            echo "<p><strong>Token Active:</strong> <span class='" . (isset($tokenData['active']) && $tokenData['active'] ? 'success' : 'error') . "'>" . (isset($tokenData['active']) && $tokenData['active'] ? 'YES' : 'NO') . "</span></p>";
            
            if (isset($tokenData['scope'])) {
                $authorizedScopes = explode(' ', $tokenData['scope']);
                echo "<p><strong>Authorized Scopes:</strong></p>";
                echo "<ul>";
                foreach ($authorizedScopes as $scope) {
                    echo "<li class='success'>{$scope}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'>No scope information in token response</p>";
            }
        } else {
            echo "<p class='error'>Failed to get token status</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>2. Scope Validation Test (New Method)</h2>
        <?php
        // Simulate the new validation logic from token-status.php
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
            'purchase_invoice_view' => 'Purchase Invoice View'
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
        echo "<tr><th>Required Scope</th><th>Description</th><th>Authorized</th><th>Status</th></tr>";
        
        foreach ($requiredScopes as $scope => $description) {
            $isAuthorized = $tokenStatus['scopes'][$scope];
            $statusText = $isAuthorized ? 'AVAILABLE' : 'NOT AUTHORIZED';
            $statusClass = $isAuthorized ? 'success' : 'error';
            
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td>{$description}</td>";
            echo "<td class='" . ($isAuthorized ? 'success' : 'error') . "'>" . ($isAuthorized ? 'YES' : 'NO') . "</td>";
            echo "<td class='{$statusClass}'>{$statusText}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>
    
    <div class="section">
        <h2>3. Validation Summary</h2>
        <div class="info">
            <p><strong>Perbaikan yang telah dilakukan:</strong></p>
            <ul>
                <li>✅ Scope validation sekarang berdasarkan OAuth authorization response</li>
                <li>✅ Tidak lagi menggunakan API call test untuk menentukan scope availability</li>
                <li>✅ Hanya scope yang benar-benar diotorisasi yang ditampilkan sebagai available</li>
                <li>✅ Scope baru yang belum diotorisasi akan ditampilkan sebagai "NOT AUTHORIZED"</li>
            </ul>
            
            <p><strong>Cara kerja baru:</strong></p>
            <ul>
                <li>1. Ambil token status dari OAuth endpoint</li>
                <li>2. Parse scope yang diotorisasi dari response OAuth</li>
                <li>3. Bandingkan required scope dengan authorized scope</li>
                <li>4. Tampilkan status berdasarkan OAuth authorization, bukan API access</li>
            </ul>
        </div>
    </div>
    
</body>
</html>