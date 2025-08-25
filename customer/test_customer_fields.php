<?php
/**
 * Test script to check customer API fields
 * This will help us understand what fields are actually returned
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $api = new AccurateAPI();
    
    echo "=== TESTING CUSTOMER API FIELDS ===\n\n";
    
    // Test customer list API
    echo "1. Testing Customer List API:\n";
    $result = $api->getCustomerList(5, 1); // Get only 5 customers for testing
    
    if ($result['success']) {
        echo "✅ Customer list API successful\n";
        
        if (isset($result['data']['d']) && !empty($result['data']['d'])) {
            $customers = $result['data']['d'];
            echo "📊 Found " . count($customers) . " customers\n\n";
            
            // Show structure of first customer
            echo "2. First Customer Structure:\n";
            $firstCustomer = $customers[0];
            echo "Available fields: " . implode(', ', array_keys($firstCustomer)) . "\n\n";
            
            // Show detailed info for first few customers
            echo "3. Customer Field Analysis:\n";
            foreach (array_slice($customers, 0, 3) as $index => $customer) {
                echo "Customer " . ($index + 1) . ":\n";
                echo "  - name: " . ($customer['name'] ?? 'N/A') . "\n";
                echo "  - no: " . ($customer['no'] ?? 'N/A') . "\n";
                echo "  - customerNo: " . ($customer['customerNo'] ?? 'NOT_EXISTS') . "\n";
                echo "  - id: " . ($customer['id'] ?? 'N/A') . "\n";
                echo "  - email: " . ($customer['email'] ?? 'N/A') . "\n";
                echo "\n";
            }
            
            // Check for customer 250 specifically
            echo "4. Looking for Customer 250:\n";
            $customer250 = null;
            foreach ($customers as $customer) {
                if (
                    (isset($customer['no']) && $customer['no'] == '250') ||
                    (isset($customer['customerNo']) && $customer['customerNo'] == '250') ||
                    (isset($customer['id']) && $customer['id'] == '250')
                ) {
                    $customer250 = $customer;
                    break;
                }
            }
            
            if ($customer250) {
                echo "✅ Found Customer 250:\n";
                echo "  - name: " . ($customer250['name'] ?? 'N/A') . "\n";
                echo "  - no: " . ($customer250['no'] ?? 'N/A') . "\n";
                echo "  - customerNo: " . ($customer250['customerNo'] ?? 'NOT_EXISTS') . "\n";
                echo "  - id: " . ($customer250['id'] ?? 'N/A') . "\n";
            } else {
                echo "❌ Customer 250 NOT FOUND in first 5 customers\n";
                echo "Available customer numbers (no field): ";
                $numbers = array_map(function($c) { return $c['no'] ?? 'N/A'; }, $customers);
                echo implode(', ', $numbers) . "\n";
            }
            
        } else {
            echo "❌ No customer data found in response\n";
        }
        
    } else {
        echo "❌ Customer list API failed: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n=== END TEST ===\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>