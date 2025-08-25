<?php
// Check available price categories
require_once __DIR__ . '/../bootstrap.php';

echo "<h2>Available Price Categories</h2>";

$api = new AccurateAPI();

try {
    $result = $api->getPriceCategoryList();
    
    if ($result['success']) {
        $categories = $result['data']['d'] ?? [];
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
        
        foreach ($categories as $category) {
            $highlight = ($category['id'] == 100) ? 'style="background-color: yellow;"' : '';
            echo "<tr $highlight>";
            echo "<td>" . $category['id'] . "</td>";
            echo "<td>" . htmlspecialchars($category['name']) . "</td>";
            echo "<td>" . ($category['suspended'] ? 'Suspended' : 'Active') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check specifically for ID 100
        $category100 = null;
        foreach ($categories as $category) {
            if ($category['id'] == 100) {
                $category100 = $category;
                break;
            }
        }
        
        if ($category100) {
            echo "<h3>Price Category ID 100 Details:</h3>";
            echo "<pre>" . print_r($category100, true) . "</pre>";
            
            if ($category100['suspended']) {
                echo "<p style='color: red; font-weight: bold;'>⚠️ WARNING: Price Category ID 100 is SUSPENDED!</p>";
            } else {
                echo "<p style='color: green; font-weight: bold;'>✅ Price Category ID 100 is ACTIVE</p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ ERROR: Price Category ID 100 NOT FOUND!</p>";
        }
        
    } else {
        echo "<p>Error: " . ($result['error'] ?? 'Unknown error') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Exception: " . $e->getMessage() . "</p>";
}
?>
