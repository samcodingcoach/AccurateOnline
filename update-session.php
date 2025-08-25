<?php
require_once __DIR__ . '/bootstrap.php';

$api = new AccurateAPI();

echo "<h1>Update Session dengan Token Baru</h1>";

// 1. Get database list
echo "<h2>1. Database List:</h2>";
$dbList = $api->getDatabaseList();
echo "<pre>";
print_r($dbList);
echo "</pre>";

if ($dbList['success'] && isset($dbList['data']['d'])) {
    $databases = $dbList['data']['d'];
    echo "<h3>Available Databases:</h3>";
    foreach ($databases as $db) {
        echo "<p>ID: " . $db['id'] . " - " . $db['alias'] . "</p>";
    }
    
    // 2. Open database dengan ID yang tersedia
    if (!empty($databases)) {
        $dbId = $databases[0]['id']; // Ambil database pertama
        echo "<h2>2. Opening Database ID: $dbId</h2>";
        
        $openDb = $api->openDatabase($dbId);
        echo "<pre>";
        print_r($openDb);
        echo "</pre>";
        
        if ($openDb['success'] && isset($openDb['data']['session']) && isset($openDb['data']['host'])) {
            $session = $openDb['data']['session'];
            $host = $openDb['data']['host'];
            
            echo "<h3>New Configuration:</h3>";
            echo "<p><strong>Host:</strong> $host</p>";
            echo "<p><strong>Session:</strong> $session</p>";
            echo "<p><strong>Database ID:</strong> $dbId</p>";
            
            echo "<h3>Update config.php dengan:</h3>";
            echo "<pre>";
            echo "define('ACCURATE_API_HOST', '$host');\n";
            echo "define('ACCURATE_SESSION_ID', '$session');\n";
            echo "define('ACCURATE_DATABASE_ID', '$dbId');\n";
            echo "</pre>";
        }
    }
} else {
    echo "<p>Error getting database list: " . ($dbList['error'] ?? 'Unknown error') . "</p>";
}
?>
