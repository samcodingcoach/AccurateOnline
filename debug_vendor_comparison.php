<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Vendor Data Comparison Debug</h1>";

$api = new AccurateAPI();

// Test 1: Raw vendor list (same as listvendor.php)
echo "<h2>1. Raw Vendor List (like listvendor.php)</h2>";
$result = $api->getVendorList();

if ($result['success']) {
    $rawVendors = $result['data']['d'] ?? [];
    echo "<p><strong>Raw vendor count:</strong> " . count($rawVendors) . "</p>";
    echo "<pre>" . print_r(array_slice($rawVendors, 0, 3), true) . "</pre>";
} else {
    echo "<p>Error: " . ($result['error'] ?? 'Unknown error') . "</p>";
}

// Test 2: Enriched vendor list (same as index.php)
echo "<h2>2. Enriched Vendor List (like index.php)</h2>";
$vendors = [];

if ($result['success'] && isset($result['data']['d'])) {
    $vendors = $result['data']['d'];
    
    // Untuk setiap vendor, ambil detail untuk mendapatkan kategori
    foreach ($vendors as &$vendor) {
        $detailResult = $api->getVendorDetail($vendor['id']);
        if ($detailResult['success'] && isset($detailResult['data']['category'])) {
            $vendor['category'] = $detailResult['data']['category'];
        }
        
        // Only process first 3 for performance
        if (count($vendors) >= 3) break;
    }
}

echo "<p><strong>Enriched vendor count:</strong> " . count($vendors) . "</p>";
echo "<pre>" . print_r(array_slice($vendors, 0, 3), true) . "</pre>";

// Test 3: Compare field differences
echo "<h2>3. Field Comparison</h2>";
if (!empty($rawVendors) && !empty($vendors)) {
    $rawFields = array_keys($rawVendors[0]);
    $enrichedFields = array_keys($vendors[0]);
    
    echo "<p><strong>Raw vendor fields:</strong> " . implode(', ', $rawFields) . "</p>";
    echo "<p><strong>Enriched vendor fields:</strong> " . implode(', ', $enrichedFields) . "</p>";
    
    $newFields = array_diff($enrichedFields, $rawFields);
    if (!empty($newFields)) {
        echo "<p><strong>Additional fields in enriched data:</strong> " . implode(', ', $newFields) . "</p>";
    }
}

echo "<h2>4. Possible Issues</h2>";
echo "<ul>";
echo "<li>index.php makes additional API calls to get vendor details, which adds 'category' field</li>";
echo "<li>This creates different data structure between the two endpoints</li>";
echo "<li>Different data structures could appear as different content to users</li>";
echo "<li>Performance impact: index.php makes N+1 API calls (1 for list + N for each detail)</li>";
echo "</ul>";
?>