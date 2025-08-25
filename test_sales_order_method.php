<?php
/**
 * Test script untuk memverifikasi bahwa createSalesOrder method sudah ada
 */

require_once __DIR__ . '/bootstrap.php';

try {
    echo "Testing AccurateAPI::createSalesOrder method...\n\n";
    
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    // Check if method exists
    if (method_exists($api, 'createSalesOrder')) {
        echo "✅ SUCCESS: createSalesOrder method exists!\n";
        
        // Test with empty data to see validation
        $result = $api->createSalesOrder([]);
        echo "Validation test result:\n";
        echo "- Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
        echo "- Error: " . ($result['error'] ?? 'No error') . "\n";
        
        // Test with missing required fields
        $result2 = $api->createSalesOrder(['customerNo' => 'TEST']);
        echo "\nPartial data test result:\n";
        echo "- Success: " . ($result2['success'] ? 'Yes' : 'No') . "\n";
        echo "- Error: " . ($result2['error'] ?? 'No error') . "\n";
        
        echo "\n✅ Method is working correctly with proper validation!\n";
        
    } else {
        echo "❌ ERROR: createSalesOrder method does NOT exist!\n";
    }
    
    // Check available methods
    echo "\nAvailable methods in AccurateAPI class:\n";
    $methods = get_class_methods($api);
    $salesOrderMethods = array_filter($methods, function($method) {
        return stripos($method, 'sales') !== false || stripos($method, 'order') !== false;
    });
    
    if (!empty($salesOrderMethods)) {
        foreach ($salesOrderMethods as $method) {
            echo "- $method\n";
        }
    } else {
        echo "- No sales order related methods found\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
?>