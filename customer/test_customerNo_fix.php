<?php
/**
 * Test script to verify customer field mapping after fixes
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

try {
    echo "=== CUSTOMER FIELD MAPPING VERIFICATION ===\n\n";
    
    // Test listcustomer.php endpoint
    echo "1. Testing listcustomer.php endpoint:\n";
    $url = 'http://localhost/nuansa/customer/listcustomer.php?limit=5';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 30
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            echo "✅ Customer API successful\n";
            
            if (isset($data['data']['customers']['d'])) {
                $customers = $data['data']['customers']['d'];
                echo "📊 Found " . count($customers) . " customers\n\n";
                
                // Check field mapping for first few customers
                echo "2. Field Mapping Analysis:\n";
                foreach (array_slice($customers, 0, 3) as $index => $customer) {
                    echo "Customer " . ($index + 1) . ":\n";
                    echo "  - name: " . ($customer['name'] ?? 'N/A') . "\n";
                    echo "  - no: " . ($customer['no'] ?? 'NOT_SET') . "\n";
                    echo "  - customerNo: " . ($customer['customerNo'] ?? 'NOT_SET') . "\n";
                    echo "  - id: " . ($customer['id'] ?? 'N/A') . "\n";
                    
                    // Verify mapping consistency
                    $hasNo = isset($customer['no']) && !empty($customer['no']);
                    $hasCustomerNo = isset($customer['customerNo']) && !empty($customer['customerNo']);
                    
                    if ($hasNo && $hasCustomerNo) {
                        echo "  ✅ Both 'no' and 'customerNo' fields available\n";
                    } elseif ($hasNo) {
                        echo "  ⚠️  Only 'no' field available\n";
                    } elseif ($hasCustomerNo) {
                        echo "  ⚠️  Only 'customerNo' field available\n";
                    } else {
                        echo "  ❌ Neither 'no' nor 'customerNo' available\n";
                    }
                    echo "\n";
                }
                
                // Look for customer 250
                echo "3. Looking for Customer 250:\n";
                $found = false;
                foreach ($customers as $customer) {
                    if (
                        (isset($customer['no']) && $customer['no'] == '250') ||
                        (isset($customer['customerNo']) && $customer['customerNo'] == '250') ||
                        (isset($customer['id']) && $customer['id'] == '250')
                    ) {
                        echo "✅ Found Customer 250:\n";
                        echo "  - name: " . ($customer['name'] ?? 'N/A') . "\n";
                        echo "  - no: " . ($customer['no'] ?? 'NOT_SET') . "\n";
                        echo "  - customerNo: " . ($customer['customerNo'] ?? 'NOT_SET') . "\n";
                        echo "  - id: " . ($customer['id'] ?? 'N/A') . "\n";
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    echo "❌ Customer 250 NOT FOUND in first " . count($customers) . " customers\n";
                    echo "Available customer numbers:\n";
                    foreach ($customers as $c) {
                        $no = $c['no'] ?? '';
                        $customerNo = $c['customerNo'] ?? '';
                        $id = $c['id'] ?? '';
                        echo "  - " . ($c['name'] ?? 'N/A') . " (no: '$no', customerNo: '$customerNo', id: '$id')\n";
                    }
                }
                
            } else {
                echo "❌ No customer data found in response\n";
            }
            
        } else {
            echo "❌ Customer API failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
        
    } else {
        echo "❌ Failed to fetch customer data\n";
    }
    
    echo "\n=== END VERIFICATION ===\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>