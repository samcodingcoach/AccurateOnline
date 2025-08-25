<?php
/**
 * Debug endpoint untuk payment term API
 * Test langsung method getPaymentTermList
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    echo "=== Payment Term API Debug ===\n\n";
    
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    echo "1. Testing AccurateAPI::getPaymentTermList method...\n";
    
    // Test method langsung
    $result = $api->getPaymentTermList(10, 1);
    
    echo "   - HTTP Code: " . $result['http_code'] . "\n";
    echo "   - Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
    if ($result['success']) {
        echo "   - Data structure check:\n";
        if (isset($result['data']['d'])) {
            $paymentTerms = $result['data']['d'];
            echo "     * Found " . count($paymentTerms) . " payment terms in 'd' field\n";
            
            if (!empty($paymentTerms)) {
                $firstTerm = $paymentTerms[0];
                echo "     * First payment term fields: " . implode(', ', array_keys($firstTerm)) . "\n";
                echo "     * Sample term: " . json_encode($firstTerm, JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "     * No 'd' field found\n";
            echo "     * Available fields: " . implode(', ', array_keys($result['data'])) . "\n";
        }
    } else {
        echo "   - Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n2. Testing via list_term.php endpoint...\n";
    
    // Test via internal request
    $internalUrl = 'http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/payterm/list_term.php?limit=10';
    echo "   - URL: $internalUrl\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 30
        ]
    ]);
    
    $response = @file_get_contents($internalUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data) {
            echo "   - Endpoint Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
            echo "   - Message: " . ($data['message'] ?? 'No message') . "\n";
            
            if ($data['success'] && isset($data['data']['paymentTerms']['d'])) {
                echo "   - Payment terms count: " . count($data['data']['paymentTerms']['d']) . "\n";
            }
        } else {
            echo "   - Invalid JSON response\n";
            echo "   - Raw response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "   - Failed to fetch from endpoint\n";
    }
    
    echo "\n3. Testing OAuth scopes...\n";
    $scopes = $api->getTokenScopes();
    $hasPaymentTermScope = in_array('payment_term_view', $scopes);
    echo "   - Has payment_term_view scope: " . ($hasPaymentTermScope ? 'Yes' : 'No') . "\n";
    echo "   - Available scopes: " . implode(', ', $scopes) . "\n";

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>