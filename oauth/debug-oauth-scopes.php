<?php
/**
 * Debug OAuth Scopes
 * Check actual authorized scopes vs API access
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug OAuth Scopes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Debug OAuth Scopes</h1>
    
    <div class="section">
        <h2>1. Raw OAuth Token Check Response</h2>
        <?php
        $tokenResponse = $api->checkTokenStatus();
        echo "<div class='code'>";
        echo "<strong>HTTP Code:</strong> " . $tokenResponse['http_code'] . "<br>";
        echo "<strong>Success:</strong> " . ($tokenResponse['success'] ? 'TRUE' : 'FALSE') . "<br>";
        echo "<strong>Raw Response:</strong><br>";
        echo "<pre>" . json_encode($tokenResponse['data'], JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";
        ?>
    </div>
    
    <div class="section">
        <h2>2. Authorized Scopes from OAuth</h2>
        <?php
        $authorizedScopes = [];
        if ($tokenResponse['success'] && isset($tokenResponse['data']['scope'])) {
            $authorizedScopes = explode(' ', $tokenResponse['data']['scope']);
            echo "<p><strong>Scopes from OAuth Response:</strong></p>";
            echo "<ul>";
            foreach ($authorizedScopes as $scope) {
                echo "<li class='success'>{$scope}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>No scopes found in OAuth response</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>3. API Access Test vs OAuth Authorization</h2>
        <?php
        $testScopes = [
            'item_view' => 'testItemView',
            'branch_view' => 'testBranchView',
            'vendor_view' => 'testVendorView',
            'customer_view' => 'testCustomerView',
            'warehouse_view' => 'testWarehouseView',
            'sales_invoice_view' => 'testSalesInvoiceView',
            'purchase_invoice_view' => 'testPurchaseInvoiceView',
            'employee_view' => 'testEmployeeView',
            'item_category_view' => 'testItemCategoryView',
            'coa_view' => 'testCoaView',
            'department_view' => 'testDepartmentView',
            'unit_view' => 'testUnitView',
            'currency_view' => 'testCurrencyView',
            'tax_view' => 'testTaxView',
            'journal_view' => 'testJournalView',
            'report_view' => 'testReportView'
        ];
        
        echo "<table>";
        echo "<tr><th>Scope</th><th>OAuth Authorized</th><th>API Access</th><th>Status</th><th>Issue</th></tr>";
        
        foreach ($testScopes as $scope => $method) {
            $isAuthorized = in_array($scope, $authorizedScopes);
            $hasApiAccess = false;
            $apiError = '';
            
            try {
                if (method_exists($api, $method)) {
                    $result = $api->$method();
                    $hasApiAccess = $result['success'];
                    if (!$hasApiAccess && isset($result['error'])) {
                        $apiError = $result['error'];
                    }
                } else {
                    $apiError = 'Method not exists';
                }
            } catch (Exception $e) {
                $apiError = $e->getMessage();
            }
            
            // Determine status
            $status = '';
            $issue = '';
            $statusClass = '';
            
            if ($isAuthorized && $hasApiAccess) {
                $status = 'VALID';
                $statusClass = 'success';
            } elseif (!$isAuthorized && !$hasApiAccess) {
                $status = 'NOT AUTHORIZED';
                $statusClass = 'info';
            } elseif (!$isAuthorized && $hasApiAccess) {
                $status = 'UNAUTHORIZED ACCESS';
                $statusClass = 'warning';
                $issue = 'API accessible without OAuth authorization!';
            } elseif ($isAuthorized && !$hasApiAccess) {
                $status = 'AUTHORIZED BUT NO ACCESS';
                $statusClass = 'error';
                $issue = 'OAuth authorized but API call failed: ' . $apiError;
            }
            
            echo "<tr>";
            echo "<td>{$scope}</td>";
            echo "<td class='" . ($isAuthorized ? 'success' : 'error') . "'>" . ($isAuthorized ? 'YES' : 'NO') . "</td>";
            echo "<td class='" . ($hasApiAccess ? 'success' : 'error') . "'>" . ($hasApiAccess ? 'YES' : 'NO') . "</td>";
            echo "<td class='{$statusClass}'>{$status}</td>";
            echo "<td class='" . ($issue ? 'warning' : '') . "'>{$issue}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>
    
    <div class="section">
        <h2>4. Recommendations</h2>
        <div class="info">
            <p><strong>Masalah yang ditemukan:</strong></p>
            <ul>
                <li>Sistem saat ini menggunakan API call test untuk menentukan scope availability</li>
                <li>Ini tidak akurat karena beberapa API mungkin bisa diakses tanpa scope yang tepat</li>
                <li>Seharusnya validasi berdasarkan OAuth scope response yang sebenarnya</li>
            </ul>
            
            <p><strong>Solusi yang disarankan:</strong></p>
            <ul>
                <li>Gunakan scope dari OAuth response sebagai sumber kebenaran</li>
                <li>Hanya tampilkan scope yang benar-benar diotorisasi</li>
                <li>Jangan mengandalkan API call success untuk validasi scope</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>5. Current Token Info</h2>
        <?php
        if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
            $tokenData = $tokenResponse['data'];
            echo "<table>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            
            foreach ($tokenData as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                echo "<tr><td>{$key}</td><td>{$value}</td></tr>";
            }
            
            echo "</table>";
        }
        ?>
    </div>
    
</body>
</html>