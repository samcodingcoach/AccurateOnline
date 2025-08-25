<?php
/**
 * Test script untuk Payment Term API
 */

require_once __DIR__ . '/../bootstrap.php';

echo "<h1>Test Payment Term API</h1>";

// Test 1: Direct API call
echo "<h2>1. Direct API Call</h2>";
try {
    $api = new AccurateAPI();
    $result = $api->getPaymentTermList(10, 1);
    
    echo "<p><strong>Success:</strong> " . ($result['success'] ? 'true' : 'false') . "</p>";
    echo "<p><strong>HTTP Code:</strong> " . ($result['http_code'] ?? 'N/A') . "</p>";
    
    if (!$result['success']) {
        echo "<p><strong>Error:</strong> " . ($result['error'] ?? 'Unknown') . "</p>";
    }
    
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($result['raw_response'] ?? 'No raw response') . "</pre>";
    
    echo "<h3>Parsed Data:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT)) . "</pre>";
    
} catch (Exception $e) {
    echo "<p><strong>Exception:</strong> " . $e->getMessage() . "</p>";
}

// Test 2: Via endpoint
echo "<h2>2. Via Endpoint (/payterm/list_term.php)</h2>";
echo "<p><a href='../payterm/list_term.php' target='_blank'>Test Endpoint</a></p>";

// Test 3: Show endpoint URL
echo "<h2>3. Endpoint Information</h2>";
echo "<p><strong>File:</strong> /payterm/list_term.php</p>";
echo "<p><strong>HTTP Method:</strong> GET</p>";
echo "<p><strong>Scope Required:</strong> payment_term_view</p>";
echo "<p><strong>Accurate Endpoint:</strong> /accurate/api/payment-term/list.do</p>";

echo "<h3>Parameters:</h3>";
echo "<ul>";
echo "<li><strong>limit</strong> - Number of records per page (default: 25, max: 100)</li>";
echo "<li><strong>page</strong> - Page number (default: 1)</li>";
echo "</ul>";

echo "<h3>Example Calls:</h3>";
echo "<ul>";
echo "<li><a href='../payterm/list_term.php'>Default (25 records, page 1)</a></li>";
echo "<li><a href='../payterm/list_term.php?limit=10'>Limit 10 records</a></li>";
echo "<li><a href='../payterm/list_term.php?limit=10&page=2'>Limit 10 records, page 2</a></li>";
echo "</ul>";
?>
