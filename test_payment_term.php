<?php
/**
 * Test script untuk payment term API
 * Untuk memverifikasi bahwa getPaymentTermList method berfungsi dengan baik
 */

require_once __DIR__ . '/bootstrap.php';

echo "<h1>Payment Term API Test</h1>";

try {
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    echo "<h2>Testing getPaymentTermList method...</h2>";
    
    // Test payment term list
    $result = $api->getPaymentTermList(10, 1); // limit 10, page 1
    
    echo "<h3>Result:</h3>";
    echo "<pre>";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "HTTP Code: " . $result['http_code'] . "\n";
    
    if ($result['success'] && isset($result['data'])) {
        echo "Data structure:\n";
        if (isset($result['data']['d'])) {
            echo "Found " . count($result['data']['d']) . " payment terms\n";
            echo "First payment term:\n";
            if (!empty($result['data']['d'])) {
                print_r($result['data']['d'][0]);
            }
        } else {
            echo "No 'd' field found in data\n";
            print_r($result['data']);
        }
    } else {
        echo "Error: " . ($result['error'] ?? 'Unknown error') . "\n";
        echo "Raw response:\n";
        echo $result['raw_response'] ?? 'No raw response';
    }
    echo "</pre>";
    
    echo "<h2>Testing through list_term.php API...</h2>";
    
    // Test the API endpoint directly
    $apiUrl = 'http://localhost/nuansa/payterm/list_term.php?limit=10';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h3>API Endpoint Response:</h3>";
    echo "<pre>";
    echo "HTTP Code: $httpCode\n";
    echo "Response:\n";
    echo $apiResponse;
    echo "</pre>";

} catch (Exception $e) {
    echo "<h3>Exception:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>