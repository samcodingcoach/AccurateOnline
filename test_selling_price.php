<?php
/**
 * Test script untuk sellingprice API
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: text/plain');

echo "Testing Selling Price API...\n\n";

try {
    // Test AccurateAPI helper methods
    $api = new AccurateAPI();
    
    echo "1. Testing AccurateAPI helper methods:\n";
    echo "   - getSessionId(): " . ($api->getSessionId() ?: 'NULL') . "\n";
    echo "   - getCurrentAccessToken(): " . substr($api->getCurrentAccessToken(), 0, 20) . "...\n";
    echo "   - getBaseUrl(): " . $api->getBaseUrl() . "\n\n";
    
    echo "2. Testing selling price endpoint:\n";
    
    // Test with a simple GET request
    $testUrl = 'http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/sellingprice/listprice.php?no=TEST001';
    echo "   - Test URL: $testUrl\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($testUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if ($data) {
            echo "   - Response success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
            echo "   - Message: " . ($data['message'] ?? 'No message') . "\n";
            
            if (!$data['success']) {
                echo "   - Error: " . ($data['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "   - Invalid JSON response\n";
            echo "   - Raw response (first 200 chars): " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "   - Failed to fetch from endpoint\n";
    }
    
    echo "\n3. Alternative test - call selling price API directly:\n";
    
    // Test direct API call (if session is available)
    if ($api->getSessionId()) {
        $result = $api->testItemView(); // Test if item scope works
        echo "   - Item scope test: " . ($result['success'] ? 'OK' : 'FAIL') . "\n";
        
        if ($result['success']) {
            echo "   - API connection is working\n";
        } else {
            echo "   - API connection error: " . ($result['error'] ?? 'Unknown') . "\n";
        }
    } else {
        echo "   - No session ID available for direct API test\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>