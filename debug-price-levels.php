<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');

$api = new AccurateAPI();

// Get price category list
$result = $api->getPriceCategoryList();

echo "<h2>Debug Price Levels - Checking Valid Price Category IDs</h2>";
echo "<hr>";

echo "<h3>1. Price Categories from API:</h3>";
if ($result['success']) {
    $categories = $result['data']['d'] ?? [];
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Suspended</th></tr>";
    
    foreach ($categories as $category) {
        $id = $category['id'] ?? 'N/A';
        $name = $category['name'] ?? 'N/A';
        $description = $category['description'] ?? 'N/A';
        $suspended = $category['suspended'] ?? false;
        $suspendedText = $suspended ? 'Yes' : 'No';
        
        echo "<tr>";
        echo "<td><strong>{$id}</strong></td>";
        echo "<td>{$name}</td>";
        echo "<td>{$description}</td>";
        echo "<td>{$suspendedText}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error getting price categories: " . ($result['error'] ?? 'Unknown error') . "</p>";
}

echo "<hr>";
echo "<h3>2. Price Level IDs used in new_item.php:</h3>";
$usedIds = ['50', '200', '250', '151', '300', '350', '301'];
echo "<ul>";
foreach ($usedIds as $id) {
    echo "<li>Price Level {$id}</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3>3. Validation Check:</h3>";
if ($result['success']) {
    $validIds = [];
    $categories = $result['data']['d'] ?? [];
    
    foreach ($categories as $category) {
        if (!($category['suspended'] ?? false)) {
            $validIds[] = (string)($category['id'] ?? '');
        }
    }
    
    echo "<p><strong>Valid (non-suspended) Price Category IDs:</strong> " . implode(', ', $validIds) . "</p>";
    
    $invalidIds = [];
    $validUsedIds = [];
    
    foreach ($usedIds as $id) {
        if (in_array($id, $validIds)) {
            $validUsedIds[] = $id;
        } else {
            $invalidIds[] = $id;
        }
    }
    
    if (!empty($validUsedIds)) {
        echo "<p style='color: green;'><strong>✓ Valid IDs being used:</strong> " . implode(', ', $validUsedIds) . "</p>";
    }
    
    if (!empty($invalidIds)) {
        echo "<p style='color: red;'><strong>✗ Invalid IDs being used:</strong> " . implode(', ', $invalidIds) . "</p>";
        echo "<p style='color: red;'><strong>This is likely causing the 'Data tidak ditemukan' error!</strong></p>";
    }
    
    if (empty($invalidIds)) {
        echo "<p style='color: green;'><strong>All price level IDs are valid!</strong></p>";
        echo "<p>The issue might be elsewhere. Check:</p>";
        echo "<ul>";
        echo "<li>Item exists before setting prices</li>";
        echo "<li>Session ID is valid</li>";
        echo "<li>API permissions for sellingprice_save scope</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Cannot validate - failed to get price categories</p>";
}

echo "<hr>";
echo "<h3>4. Recommended Fix:</h3>";
if ($result['success'] && !empty($invalidIds)) {
    $categories = $result['data']['d'] ?? [];
    $recommendations = [];
    
    foreach ($categories as $category) {
        if (!($category['suspended'] ?? false)) {
            $id = $category['id'] ?? '';
            $name = $category['name'] ?? '';
            $recommendations[] = "{ id: '{$id}', price: parseNumber(document.getElementById('price{$id}').value) } // {$name}";
        }
    }
    
    if (!empty($recommendations)) {
        echo "<p><strong>Replace the prices array in new_item.php with:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "const prices = [\n";
        foreach ($recommendations as $i => $rec) {
            echo "    {$rec}";
            if ($i < count($recommendations) - 1) echo ",";
            echo "\n";
        }
        echo "];";
        echo "</pre>";
    }
}

echo "<hr>";
echo "<p><em>Debug completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>