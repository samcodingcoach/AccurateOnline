<?php
/**
 * Quick test for customer field mapping
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

try {
    $api = new AccurateAPI();
    
    echo "=== CUSTOMER FIELD MAPPING TEST ===\n\n";
    
    // Get customers
    $result = $api->getCustomerList(100, 1);
    
    if ($result['success'] && isset($result['data']['d'])) {
        $customers = $result['data']['d'];
        echo "✅ Loaded " . count($customers) . " customers\n\n";
        
        // Look for customer 250 specifically
        $found = false;
        foreach ($customers as $customer) {
            $no = $customer['no'] ?? '';
            $customerNo = $customer['customerNo'] ?? '';
            $id = $customer['id'] ?? '';
            $name = $customer['name'] ?? '';
            
            if ($no == '250' || $customerNo == '250' || $id == '250') {
                echo "✅ FOUND Customer 250:\n";
                echo "  Name: $name\n";
                echo "  no: '$no'\n";
                echo "  customerNo: '$customerNo'\n";
                echo "  id: '$id'\n";
                echo "  Would use value: '" . ($no ?: $customerNo ?: $id) . "' for dropdown\n\n";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "❌ Customer 250 NOT FOUND\n";
            echo "Available customers (first 10):\n";
            for ($i = 0; $i < min(10, count($customers)); $i++) {
                $c = $customers[$i];
                $no = $c['no'] ?? '';
                $customerNo = $c['customerNo'] ?? '';
                $id = $c['id'] ?? '';
                $name = $c['name'] ?? '';
                
                echo "  " . ($i+1) . ". $name - no: '$no', customerNo: '$customerNo', id: '$id'\n";
            }
        }
        
        // Test what value would be used for dropdown
        echo "\n=== DROPDOWN VALUE TEST ===\n";
        $testCustomer = $customers[0];
        $dropdownValue = $testCustomer['no'] ?? $testCustomer['customerNo'] ?? $testCustomer['id'] ?? '';
        echo "Test customer: " . ($testCustomer['name'] ?? 'N/A') . "\n";
        echo "Dropdown value would be: '$dropdownValue'\n";
        echo "This would be sent as customerNo to sales order API\n";
        
    } else {
        echo "❌ Failed to load customers: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>