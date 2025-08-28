<?php
/**
 * Debug script untuk memeriksa scope yang tersedia
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

echo "<h1>Debug: Approved Scopes</h1>";

// Test getApprovedScopes method
echo "<h2>1. getApprovedScopes() Method Test</h2>";
$approvedScopes = $api->getApprovedScopes();

echo "<pre>";
echo "Response: ";
print_r($approvedScopes);
echo "</pre>";

// Test checkTokenStatus method
echo "<h2>2. checkTokenStatus() Method Test</h2>";
$tokenStatus = $api->checkTokenStatus();

echo "<pre>";
echo "Response: ";
print_r($tokenStatus);
echo "</pre>";

// Test getTokenScopes method
echo "<h2>3. getTokenScopes() Method Test</h2>";
$tokenScopes = $api->getTokenScopes();

echo "<pre>";
echo "Response: ";
print_r($tokenScopes);
echo "</pre>";

// Config scopes
echo "<h2>4. Config Scopes</h2>";
if (defined('ACCURATE_TOKEN_SCOPE') && !empty(ACCURATE_TOKEN_SCOPE)) {
    $configScopes = explode(' ', trim(ACCURATE_TOKEN_SCOPE));
    echo "<pre>";
    echo "Count: " . count($configScopes) . "\n";
    echo "Scopes: ";
    print_r($configScopes);
    echo "</pre>";
} else {
    echo "<p>No ACCURATE_TOKEN_SCOPE defined in config</p>";
}

// Test a few individual API endpoints to check scope functionality
echo "<h2>5. Individual API Endpoint Tests</h2>";

$testEndpoints = [
    'item_view' => function() use ($api) {
        return $api->testItemView();
    },
    'branch_view' => function() use ($api) {
        return $api->testBranchView();
    },
    'vendor_view' => function() use ($api) {
        return $api->testVendorView();
    },
    'warehouse_view' => function() use ($api) {
        return $api->testWarehouseView();
    },
    'customer_view' => function() use ($api) {
        return $api->testCustomerView();
    }
];

foreach ($testEndpoints as $scope => $testFunction) {
    echo "<h3>Testing: $scope</h3>";
    try {
        $result = $testFunction();
        echo "<pre>";
        echo "Result: " . ($result ? "✅ Working" : "❌ Not Working") . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "<pre>";
        echo "Error: " . $e->getMessage() . "\n";
        echo "</pre>";
    }
}
?>