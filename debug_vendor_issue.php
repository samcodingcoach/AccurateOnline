<?php
/**
 * DEBUG: Vendor Duplication Issue
 * Investigating why index.php shows duplicate data
 */

require_once __DIR__ . '/bootstrap.php';

echo "<h1>🐛 DEBUG: Vendor Duplication Issue</h1>";

$api = new AccurateAPI();
$result = $api->getVendorList();

echo "<h2>1️⃣ Raw API Response:</h2>";
if ($result['success'] && isset($result['data']['d'])) {
    $vendors = $result['data']['d'];
    
    echo "<p><strong>Number of vendors from API:</strong> " . count($vendors) . "</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Index</th><th>ID</th><th>Name</th><th>Has Category</th></tr>";
    
    foreach ($vendors as $index => $vendor) {
        $hasCategory = isset($vendor['category']) ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>" . ($vendor['id'] ?? 'N/A') . "</td>";
        echo "<td>" . ($vendor['name'] ?? 'N/A') . "</td>";
        echo "<td>$hasCategory</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2️⃣ Testing Enrichment Process (like index.php):</h2>";
    
    // Test the enrichment process step by step
    $testVendors = $vendors; // Make a copy
    
    echo "<p><strong>Before enrichment:</strong></p>";
    echo "<pre>";
    foreach ($testVendors as $i => $vendor) {
        echo "[$i] ID: " . ($vendor['id'] ?? 'N/A') . " - Name: " . ($vendor['name'] ?? 'N/A') . "\n";
    }
    echo "</pre>";
    
    // Simulate the problematic foreach loop from index.php
    echo "<p><strong>Processing enrichment...</strong></p>";
    foreach ($testVendors as &$vendor) {
        echo "<p>Processing vendor ID: " . ($vendor['id'] ?? 'N/A') . " - " . ($vendor['name'] ?? 'N/A') . "</p>";
        
        // This is what index.php does - but it's unnecessary since category already exists!
        $detailResult = $api->getVendorDetail($vendor['id']);
        if ($detailResult['success'] && isset($detailResult['data']['category'])) {
            $vendor['category'] = $detailResult['data']['category'];
            echo "<p>✅ Category updated for vendor " . ($vendor['id'] ?? 'N/A') . "</p>";
        } else {
            echo "<p>❌ Failed to get category for vendor " . ($vendor['id'] ?? 'N/A') . "</p>";
        }
    }
    
    echo "<p><strong>After enrichment:</strong></p>";
    echo "<pre>";
    foreach ($testVendors as $i => $vendor) {
        echo "[$i] ID: " . ($vendor['id'] ?? 'N/A') . " - Name: " . ($vendor['name'] ?? 'N/A') . "\n";
    }
    echo "</pre>";
    
    echo "<h2>3️⃣ Final Array State:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Index</th><th>ID</th><th>Name</th><th>Category Name</th></tr>";
    
    foreach ($testVendors as $index => $vendor) {
        $categoryName = 'N/A';
        if (isset($vendor['category']['name'])) {
            $categoryName = $vendor['category']['name'];
        }
        
        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>" . ($vendor['id'] ?? 'N/A') . "</td>";
        echo "<td>" . ($vendor['name'] ?? 'N/A') . "</td>";
        echo "<td>$categoryName</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>4️⃣ Analysis:</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>";
    echo "<h4>🔍 Issue Found:</h4>";
    echo "<ul>";
    echo "<li><strong>getVendorList() already returns category data</strong> - no need for additional calls!</li>";
    echo "<li>The enrichment process in index.php is <strong>unnecessary and potentially harmful</strong></li>";
    echo "<li>Each getVendorDetail() call might be modifying the array structure</li>";
    echo "<li>PHP reference (&\$vendor) might be causing array corruption</li>";
    echo "</ul>";
    
    echo "<h4>💡 Solution:</h4>";
    echo "<ul>";
    echo "<li>Remove the unnecessary enrichment loop from index.php</li>";
    echo "<li>Use the category data that's already in the getVendorList() response</li>";
    echo "<li>This will fix the duplication and improve performance</li>";
    echo "</ul>";
    echo "</div>";
    
} else {
    echo "<p>❌ Failed to get vendor data: " . ($result['error'] ?? 'Unknown error') . "</p>";
}
?>