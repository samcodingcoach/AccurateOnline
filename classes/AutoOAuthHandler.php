<?php
/**
 * Auto OAuth Handler - Otomatisasi proses OAuth dan update konfigurasi
 */

require_once __DIR__ . '/../bootstrap.php';

class AutoOAuthHandler {
    private $api;
    
    public function __construct() {
        $this->api = new AccurateAPI();
    }
    
    /**
     * Update access token dan refresh seluruh konfigurasi otomatis
     */
    public function updateTokenAndConfig($newTokenData) {
        try {
            // 1. Simpan token baru ke apitoken.txt
            file_put_contents(__DIR__ . '/../apitoken.txt', json_encode($newTokenData, JSON_PRETTY_PRINT));
            
            // 2. Update config.php dengan token baru
            $this->updateConfigFile($newTokenData);
            
            // 3. Update session dengan database
            $sessionData = $this->updateSessionWithDatabase($newTokenData['access_token']);
            
            if ($sessionData) {
                // 4. Update config.php dengan session data
                $this->updateConfigFileWithSession($sessionData);
                
                return [
                    'success' => true,
                    'message' => 'Token and configuration updated successfully',
                    'token_data' => $newTokenData,
                    'session_data' => $sessionData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Token updated but failed to establish session',
                    'token_data' => $newTokenData
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update config.php dengan token baru
     */
    private function updateConfigFile($tokenData) {
        $configPath = __DIR__ . '/../config/config.php';
        $configContent = file_get_contents($configPath);
        
        // Update access token
        if (isset($tokenData['access_token'])) {
            $configContent = preg_replace(
                "/define\('ACCURATE_ACCESS_TOKEN', '[^']*'\);/",
                "define('ACCURATE_ACCESS_TOKEN', '{$tokenData['access_token']}');",
                $configContent
            );
        }
        
        // Update refresh token
        if (isset($tokenData['refresh_token'])) {
            $configContent = preg_replace(
                "/define\('ACCURATE_REFRESH_TOKEN', '[^']*'\);/",
                "define('ACCURATE_REFRESH_TOKEN', '{$tokenData['refresh_token']}');",
                $configContent
            );
        }
        
        // Update atau tambahkan token scope
        if (isset($tokenData['scope'])) {
            if (strpos($configContent, 'ACCURATE_TOKEN_SCOPE') !== false) {
                // Update existing
                $configContent = preg_replace(
                    "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/",
                    "define('ACCURATE_TOKEN_SCOPE', '{$tokenData['scope']}');",
                    $configContent
                );
            } else {
                // Add new
                $newLine = "define('ACCURATE_TOKEN_SCOPE', '{$tokenData['scope']}');\n";
                $configContent = str_replace(
                    "define('ACCURATE_REFRESH_TOKEN',", 
                    $newLine . "define('ACCURATE_REFRESH_TOKEN',", 
                    $configContent
                );
            }
        }
        
        file_put_contents($configPath, $configContent);
    }
    
    /**
     * Update session dengan database dan dapatkan host + session baru
     */
    private function updateSessionWithDatabase($accessToken) {
        // 1. Get database list
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://account.accurate.id/api/db-list.do',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $data = json_decode($response, true);
        if (!isset($data['d']) || empty($data['d'])) {
            return false;
        }
        
        // 2. Open first database
        $dbId = $data['d'][0]['id'];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://account.accurate.id/api/open-db.do?id=$dbId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"
            ]
        ]);
        
        $response2 = curl_exec($ch);
        $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode2 !== 200) {
            return false;
        }
        
        $openData = json_decode($response2, true);
        if (!isset($openData['session']) || !isset($openData['host'])) {
            return false;
        }
        
        return [
            'host' => $openData['host'],
            'session_id' => $openData['session'],
            'database_id' => $dbId,
            'database_info' => $data['d'][0]
        ];
    }
    
    /**
     * Update config.php dengan session data
     */
    private function updateConfigFileWithSession($sessionData) {
        $configPath = __DIR__ . '/../config/config.php';
        $configContent = file_get_contents($configPath);
        
        // Update host
        $configContent = preg_replace(
            "/define\('ACCURATE_API_HOST', '[^']*'\);/",
            "define('ACCURATE_API_HOST', '{$sessionData['host']}');",
            $configContent
        );
        
        // Update session ID
        $configContent = preg_replace(
            "/define\('ACCURATE_SESSION_ID', '[^']*'\);/",
            "define('ACCURATE_SESSION_ID', '{$sessionData['session_id']}');",
            $configContent
        );
        
        // Update database ID
        $configContent = preg_replace(
            "/define\('ACCURATE_DATABASE_ID', '[^']*'\);/",
            "define('ACCURATE_DATABASE_ID', '{$sessionData['database_id']}');",
            $configContent
        );
        
        file_put_contents($configPath, $configContent);
    }
    
    /**
     * Test semua scope yang tersedia dengan token yang baru diperoleh
     * @param string $accessToken Token baru untuk testing
     * @return array Hasil testing scope
     */
    public function testAllScopes($accessToken = null) {
        $api = new AccurateAPI();
        
        // Jika ada token baru, gunakan token tersebut untuk testing
        if ($accessToken) {
            $api->setAccessToken($accessToken);
        }
        
        return $api->checkTokenStatus([]);
    }
    
    /**
     * Auto refresh token dengan full automation (termasuk config update)
     * @return array Refresh result dengan automation status
     */
    public function autoRefreshWithAutomation() {
        // Gunakan method baru dari AccurateAPI yang sudah ada automation
        $refreshResult = $this->api->refreshToken();
        
        if ($refreshResult['success'] && isset($refreshResult['data']['access_token'])) {
            // Update session dengan token baru jika perlu
            $sessionData = $this->updateSessionWithDatabase($refreshResult['data']['access_token']);
            
            return [
                'success' => true,
                'action' => 'token_refreshed_with_automation',
                'message' => 'Token refreshed with full automation (config + session updated)',
                'token_data' => $refreshResult['data'],
                'config_updated' => $refreshResult['config_updated'],
                'session_updated' => $sessionData ? 'success' : 'failed',
                'session_data' => $sessionData
            ];
        } else {
            return [
                'success' => false,
                'action' => 'refresh_failed',
                'message' => 'Failed to refresh token',
                'error' => $refreshResult['error']
            ];
        }
    }
    
    /**
     * Smart auto refresh - cek dulu apakah perlu refresh
     * @return array Smart refresh result
     */
    public function smartAutoRefresh() {
        // Cek status token dulu
        $tokenStatus = $this->api->checkTokenStatus();
        
        if ($tokenStatus['valid']) {
            return [
                'success' => true,
                'action' => 'no_refresh_needed',
                'message' => 'Token is still valid, no refresh needed',
                'token_status' => $tokenStatus
            ];
        } else {
            // Token tidak valid, lakukan auto refresh
            return $this->autoRefreshWithAutomation();
        }
    }
}
?>
