<?php
/**
 * API untuk membuka database
 * File ini sudah direfactor untuk menggunakan struktur baru
 * Enhanced untuk mendukung auto-selection dan better error handling
 */

// Start output buffering to prevent any unwanted output
ob_start();

// Ensure clean JSON output
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once __DIR__ . '/bootstrap.php';
    
    // Clear any output that might have been generated
    ob_clean();
    
    // Inisialisasi API class
    $api = new AccurateAPI();
    
    // Get database ID dari parameter atau gunakan default
    $databaseId = $_GET['id'] ?? $_POST['id'] ?? null;
    
    // Jika tidak ada ID yang diberikan, coba ambil database terbaru yang tidak expired
    if (!$databaseId) {
        try {
            $dbListResult = $api->getDatabaseList();
            if ($dbListResult['success'] && isset($dbListResult['data']['d'])) {
                $databases = $dbListResult['data']['d'];
                
                // Sort databases: non-expired first, then by ID (newest first)
                usort($databases, function($a, $b) {
                    if ($a['expired'] !== $b['expired']) {
                        return $a['expired'] ? 1 : -1; // Non-expired first
                    }
                    return $b['id'] - $a['id']; // Newest ID first
                });
                
                // Find latest non-expired database
                $latestDb = null;
                foreach ($databases as $db) {
                    if (!$db['expired']) {
                        $latestDb = $db;
                        break;
                    }
                }
                
                if ($latestDb) {
                    $databaseId = $latestDb['id'];
                }
            }
        } catch (Exception $e) {
            // Log error but continue with null databaseId
            error_log('Error getting database list: ' . $e->getMessage());
        }
    }
    
    // Validate database ID
    if (!$databaseId) {
        ob_end_clean();
        echo jsonResponse(null, false, 'No database ID provided and failed to auto-select latest database');
        exit;
    }
    
    // Open database
    $result = $api->openDatabase($databaseId);
    
    if ($result['success']) {
        // Simpan session info untuk penggunaan selanjutnya
        $sessionData = $result['data'];
        
        try {
            file_put_contents(__DIR__ . '/session.txt', json_encode($sessionData, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            // Log error but continue
            error_log('Error saving session: ' . $e->getMessage());
        }
        
        // Auto-update config.php dengan session dan database info yang baru
        try {
            $configPath = __DIR__ . '/config/config.php';
            $configContent = file_get_contents($configPath);
            
            if ($configContent !== false) {
                // Update ACCURATE_SESSION_ID
                if (isset($sessionData['session'])) {
                    $newSessionId = $sessionData['session'];
                    $configContent = preg_replace(
                        "/define\('ACCURATE_SESSION_ID', '[^']*'\);/",
                        "define('ACCURATE_SESSION_ID', '{$newSessionId}');",
                        $configContent
                    );
                }
                
                // Update ACCURATE_DATABASE_ID
                if ($databaseId) {
                    $configContent = preg_replace(
                        "/define\('ACCURATE_DATABASE_ID', '[^']*'\);/",
                        "define('ACCURATE_DATABASE_ID', '{$databaseId}');",
                        $configContent
                    );
                }
                
                // Update ACCURATE_API_HOST if provided
                if (isset($sessionData['host'])) {
                    $newHost = $sessionData['host'];
                    $configContent = preg_replace(
                        "/define\('ACCURATE_API_HOST', '[^']*'\);/",
                        "define('ACCURATE_API_HOST', '{$newHost}');",
                        $configContent
                    );
                }
                
                // Save updated config
                $configSaved = file_put_contents($configPath, $configContent);
                
                if ($configSaved !== false) {
                    // Add config update info to response
                    $responseData = $sessionData;
                    $responseData['database_id'] = $databaseId;
                    $responseData['auto_selected'] = !($_GET['id'] ?? $_POST['id']);
                    $responseData['config_updated'] = true;
                    $responseData['updated_fields'] = [];
                    
                    if (isset($sessionData['session'])) {
                        $responseData['updated_fields'][] = 'ACCURATE_SESSION_ID';
                    }
                    if ($databaseId) {
                        $responseData['updated_fields'][] = 'ACCURATE_DATABASE_ID';
                    }
                    if (isset($sessionData['host'])) {
                        $responseData['updated_fields'][] = 'ACCURATE_API_HOST';
                    }
                    
                    ob_end_clean();
                    echo jsonResponse($responseData, true, 'Database berhasil dibuka dan config diupdate');
                } else {
                    // Config update failed, but database opened successfully
                    $responseData = $sessionData;
                    $responseData['database_id'] = $databaseId;
                    $responseData['auto_selected'] = !($_GET['id'] ?? $_POST['id']);
                    $responseData['config_updated'] = false;
                    $responseData['config_error'] = 'Failed to save config file';
                    
                    ob_end_clean();
                    echo jsonResponse($responseData, true, 'Database berhasil dibuka, tapi gagal update config');
                }
            } else {
                // Config file read failed
                $responseData = $sessionData;
                $responseData['database_id'] = $databaseId;
                $responseData['auto_selected'] = !($_GET['id'] ?? $_POST['id']);
                $responseData['config_updated'] = false;
                $responseData['config_error'] = 'Failed to read config file';
                
                ob_end_clean();
                echo jsonResponse($responseData, true, 'Database berhasil dibuka, tapi gagal baca config');
            }
            
        } catch (Exception $e) {
            // Config update failed, but database opened successfully
            error_log('Error updating config: ' . $e->getMessage());
            
            $responseData = $sessionData;
            $responseData['database_id'] = $databaseId;
            $responseData['auto_selected'] = !($_GET['id'] ?? $_POST['id']);
            $responseData['config_updated'] = false;
            $responseData['config_error'] = $e->getMessage();
            
            ob_end_clean();
            echo jsonResponse($responseData, true, 'Database berhasil dibuka, tapi error saat update config');
        }
    } else {
        $errorMessage = 'Gagal membuka database: ' . ($result['error'] ?? 'Unknown error');
        ob_end_clean();
        echo jsonResponse(null, false, $errorMessage);
    }
    
} catch (Exception $e) {
    // Clean any output
    ob_end_clean();
    
    // Log the full error
    error_log('Database open error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Return clean JSON error
    echo jsonResponse(null, false, 'Internal server error: ' . $e->getMessage());
} catch (Error $e) {
    // Handle fatal errors
    ob_end_clean();
    
    // Log the full error
    error_log('Database open fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Return clean JSON error
    echo jsonResponse(null, false, 'Fatal error occurred');
}
?>
