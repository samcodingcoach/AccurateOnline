<?php
/**
 * Debug version of database opening
 * Use this to troubleshoot database opening issues
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== Debug Database Open ===\n\n";

try {
    require_once __DIR__ . '/bootstrap.php';
    
    echo "1. Bootstrap loaded successfully\n";
    
    // Create API instance
    $api = new AccurateAPI();
    echo "2. AccurateAPI instance created\n";
    
    // Get database ID from parameter
    $databaseId = $_GET['id'] ?? $_POST['id'] ?? null;
    echo "3. Database ID from request: " . ($databaseId ?: 'null') . "\n";
    
    // If no ID provided, try to get latest
    if (!$databaseId) {
        echo "4. No ID provided, getting database list...\n";
        
        $dbListResult = $api->getDatabaseList();
        echo "5. Database list result success: " . ($dbListResult['success'] ? 'true' : 'false') . "\n";
        
        if ($dbListResult['success'] && isset($dbListResult['data']['d'])) {
            $databases = $dbListResult['data']['d'];
            echo "6. Found " . count($databases) . " databases\n";
            
            // Sort databases
            usort($databases, function($a, $b) {
                if ($a['expired'] !== $b['expired']) {
                    return $a['expired'] ? 1 : -1;
                }
                return $b['id'] - $a['id'];
            });
            
            // Find latest non-expired
            foreach ($databases as $db) {
                echo "   - DB ID: {$db['id']}, Alias: {$db['alias']}, Expired: " . ($db['expired'] ? 'true' : 'false') . "\n";
                if (!$db['expired'] && !$databaseId) {
                    $databaseId = $db['id'];
                    echo "   -> Selected as latest: {$databaseId}\n";
                }
            }
        } else {
            echo "6. Failed to get database list: " . ($dbListResult['error'] ?? 'Unknown error') . "\n";
        }
    }
    
    if (!$databaseId) {
        echo "7. ERROR: No database ID available\n";
        exit;
    }
    
    echo "7. Using database ID: $databaseId\n";
    echo "8. Attempting to open database...\n";
    
    // Open database
    $result = $api->openDatabase($databaseId);
    
    echo "9. Open database result:\n";
    echo "   - Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "   - HTTP Code: " . ($result['http_code'] ?? 'unknown') . "\n";
    echo "   - Error: " . ($result['error'] ?? 'none') . "\n";
    
    if ($result['success']) {
        echo "10. SUCCESS! Database opened successfully\n";
        echo "11. Response data:\n";
        print_r($result['data']);
        
        // Save session
        if (isset($result['data'])) {
            file_put_contents(__DIR__ . '/session.txt', json_encode($result['data'], JSON_PRETTY_PRINT));
            echo "12. Session saved to session.txt\n";
        }
    } else {
        echo "10. FAILED to open database\n";
        echo "11. Raw response:\n";
        echo $result['raw_response'] ?? 'No raw response';
    }
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== End Debug ===\n";
?>