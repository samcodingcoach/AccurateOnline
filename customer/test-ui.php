<?php
/**
 * Quick test untuk Customer Detail UI
 * File: /customer/test-ui.php
 */

require_once __DIR__ . '/../bootstrap.php';

echo "<!DOCTYPE html>\n<html><head><title>Customer UI Test</title></head><body>";
echo "<h1>Customer UI Test</h1>";

try {
    // Test customer list UI
    echo "<h2>Test Customer UI Pages</h2>";
    echo "<p><a href='/nuansa/customer/index.php' target='_blank'>Test Customer List (hanya ID dan Action)</a></p>";
    echo "<p><a href='/nuansa/customer/detail.php?id=200' target='_blank'>Test Customer Detail (ID=200)</a></p>";
    
    // Test customer detail API structure
    echo "<h2>Test Customer Detail API Structure</h2>";
    $api = new AccurateAPI();
    $result = $api->getCustomerDetail(200);
    
    echo "<h3>API Response Structure:</h3>";
    echo "<pre>";
    echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    if ($result['success'] && isset($result['data'])) {
        echo "Has 'd' key in data: " . (isset($result['data']['d']) ? 'true' : 'false') . "\n";
        
        if (isset($result['data']['d'])) {
            $customer = $result['data']['d'];
            echo "\nCustomer fields available:\n";
            echo "- name: " . ($customer['name'] ?? 'N/A') . "\n";
            echo "- customerNo: " . ($customer['customerNo'] ?? 'N/A') . "\n";
            echo "- id: " . ($customer['id'] ?? 'N/A') . "\n";
            echo "- suspended: " . ($customer['suspended'] ? 'true' : 'false') . "\n";
            echo "- mobilePhone: " . ($customer['mobilePhone'] ?? 'N/A') . "\n";
            echo "- customerBranchName: " . ($customer['customerBranchName'] ?? 'N/A') . "\n";
            echo "- priceCategory.name: " . ($customer['priceCategory']['name'] ?? 'N/A') . "\n";
            echo "- discountCategory.name: " . ($customer['discountCategory']['name'] ?? 'N/A') . "\n";
            echo "- currency.name: " . ($customer['currency']['name'] ?? 'N/A') . "\n";
            echo "- term.name: " . ($customer['term']['name'] ?? 'N/A') . "\n";
            echo "- wpName: " . ($customer['wpName'] ?? 'N/A') . "\n";
        }
    }
    
    if ($result['error']) {
        echo "Error: " . $result['error'] . "\n";
    }
    echo "</pre>";
    
    // Test formatted response
    echo "<h2>Test Formatted API Response</h2>";
    echo "<p><a href='/nuansa/api/customer-detail.php?id=200' target='_blank'>Test API Response (ID=200)</a></p>";
    
} catch (Exception $e) {
    echo "<h3>Exception occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "</body></html>";
?>
