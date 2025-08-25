<?php
/**
 * Class AccurateAPI untuk handle semua API calls ke Accurate
 * Menggabungkan semua fungsi API dalam satu class yang terorganisir
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/utils.php';

class AccurateAPI {
    private $accessToken;
    private $sessionId;
    private $host;
    private $authHost;
    private $databaseId;
    
    public function __construct() {
        $this->accessToken = ACCURATE_ACCESS_TOKEN;
        $this->sessionId = ACCURATE_SESSION_ID;
        $this->host = ACCURATE_API_HOST;
        $this->authHost = ACCURATE_AUTH_HOST;
        $this->databaseId = ACCURATE_DATABASE_ID;
    }
    
    /**
     * Override access token untuk testing dengan token baru
     * @param string $newToken Token baru
     */
    public function setAccessToken($newToken) {
        $this->accessToken = $newToken;
    }
    
    /**
     * Override session ID untuk testing dengan session baru
     * @param string $newSessionId Session ID baru
     */
    public function setSessionId($newSessionId) {
        $this->sessionId = $newSessionId;
    }
    
    /**
     * Override host untuk testing dengan host baru
     * @param string $newHost Host baru
     */
    public function setHost($newHost) {
        $this->host = $newHost;
    }
    
    /**
     * Fungsi untuk melakukan HTTP request dengan cURL
     * @param string $url URL endpoint
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Data untuk POST/PUT
     * @param array $headers Additional headers
     * @return array Response dari API
     */
    private function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        // Set basic cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Nuansa Accurate API Client/1.0'
        ]);
        
        // Set headers
        $defaultHeaders = [
            "Authorization: Bearer {$this->accessToken}",
            "Content-Type: application/json",
            "Accept: application/json"
        ];
        
        if ($this->sessionId) {
            $defaultHeaders[] = "X-Session-ID: {$this->sessionId}";
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        
        // Set method dan data
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            logError("cURL Error: $error", __FILE__, __LINE__);
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
            
            // Log the error
            logError("API Error: $errorMessage (HTTP $httpCode) - URL: $url", __FILE__, __LINE__);
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get session info untuk debugging
     * @return array Session information
     */
    public function getSessionInfo() {
        return [
            'access_token' => $this->accessToken,
            'session_id' => $this->sessionId,
            'host' => $this->host,
            'database_id' => $this->databaseId
        ];
    }
    
    /**
     * Get access token menggunakan OAuth authorization code
     * @param string $code Authorization code
     * @return array Response dari token endpoint
     */
    public function getAccessToken($code) {
        $url = $this->authHost . '/oauth/token';
        
        $data = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => OAUTH_REDIRECT_URI
        ];
        
        $authorization = createBasicAuth(OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET);
        
        $headers = [
            "Authorization: Basic $authorization",
            "Content-Type: application/x-www-form-urlencoded"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode === 200,
            'data' => $result,
            'http_code' => $httpCode,
            'raw_response' => $response,
            'error' => $httpCode !== 200 ? ($result['error'] ?? 'HTTP ' . $httpCode . ': ' . $response) : null
        ];
    }
    
    /**
     * Refresh access token menggunakan refresh token dengan automation config update
     * @param string $refreshToken Refresh token (optional, akan menggunakan dari config jika tidak disediakan)
     * @return array Response dari token endpoint dengan status config update
     */
    public function refreshToken($refreshToken = null) {
        // Gunakan refresh token dari config jika tidak disediakan
        if ($refreshToken === null) {
            $refreshToken = ACCURATE_REFRESH_TOKEN;
        }
        
        if (empty($refreshToken)) {
            return [
                'success' => false,
                'error' => 'Refresh token not found in configuration',
                'http_code' => null,
                'data' => null
            ];
        }
        
        $url = $this->authHost . '/oauth/token';
        
        $data = [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ];
        
        // Create Basic Authorization header for client credentials
        $authorization = createBasicAuth(OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET);
        
        $headers = [
            "Authorization: Basic $authorization",
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'http_code' => null,
                'data' => null,
                'debug_info' => [
                    'url' => $url,
                    'headers_sent' => $headers,
                    'data_sent' => $data
                ]
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['access_token'])) {
            // AUTO UPDATE CONFIG.PHP dengan token baru
            $updateResult = $this->updateConfigWithNewToken($result);
            
            return [
                'success' => true,
                'data' => $result,
                'config_updated' => $updateResult,
                'http_code' => $httpCode,
                'raw_response' => $response,
                'error' => null
            ];
        } else {
            // Enhanced error response for debugging
            $errorMessage = 'Unknown error';
            
            if (isset($result['error'])) {
                $errorMessage = $result['error'];
            } elseif (isset($result['error_description'])) {
                $errorMessage = $result['error_description'];
            } elseif ($httpCode !== 200) {
                $errorMessage = "HTTP $httpCode error";
            }
            
            return [
                'success' => false,
                'data' => $result,
                'http_code' => $httpCode,
                'raw_response' => $response,
                'error' => $errorMessage,
                'debug_info' => [
                    'url' => $url,
                    'headers_sent' => $headers,
                    'data_sent' => $data,
                    'response_received' => $response,
                    'parsed_response' => $result
                ]
            ];
        }
    }
    
    /**
     * Update config.php with new token data (ACCESS_TOKEN dan REFRESH_TOKEN)
     * @param array $tokenData Token data from refresh response
     * @return array Update result
     */
    private function updateConfigWithNewToken($tokenData) {
        $configPath = __DIR__ . '/../config/config.php';
        
        if (!file_exists($configPath)) {
            return [
                'success' => false,
                'error' => 'Config file not found: ' . $configPath
            ];
        }
        
        $configContent = file_get_contents($configPath);
        
        if ($configContent === false) {
            return [
                'success' => false,
                'error' => 'Unable to read config file'
            ];
        }
        
        $updatedFields = [];
        
        // Update ACCESS_TOKEN
        if (isset($tokenData['access_token'])) {
            $configContent = preg_replace(
                "/define\('ACCURATE_ACCESS_TOKEN', '[^']*'\);/",
                "define('ACCURATE_ACCESS_TOKEN', '{$tokenData['access_token']}');",
                $configContent
            );
            $updatedFields['access_token'] = '✅ Updated';
        }
        
        // Update REFRESH_TOKEN (yang penting!)
        if (isset($tokenData['refresh_token'])) {
            $configContent = preg_replace(
                "/define\('ACCURATE_REFRESH_TOKEN', '[^']*'\);/",
                "define('ACCURATE_REFRESH_TOKEN', '{$tokenData['refresh_token']}');",
                $configContent
            );
            $updatedFields['refresh_token'] = '✅ Updated';
        }
        
        // Update SCOPE jika ada
        if (isset($tokenData['scope'])) {
            $configContent = preg_replace(
                "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/",
                "define('ACCURATE_TOKEN_SCOPE', '{$tokenData['scope']}');",
                $configContent
            );
            $updatedFields['scope'] = '✅ Updated';
        }
        
        // Simpan file config yang sudah diupdate
        $writeResult = file_put_contents($configPath, $configContent);
        
        if ($writeResult !== false) {
            return [
                'success' => true,
                'message' => 'Config updated successfully',
                'updated_fields' => $updatedFields,
                'file_path' => $configPath,
                'bytes_written' => $writeResult
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to write config file'
            ];
        }
    }
    
    /**
     * Get item list dari Accurate API
     * @param int $limit Jumlah item per halaman
     * @param int $page Halaman yang diinginkan
     * @return array Response dari API
     */
    public function getItemList($limit = 100, $page = 1) {
        $url = $this->host . '/accurate/api/item/list.do';
        
        // Build query parameters dengan fields yang lengkap
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page,
            'fields' => 'id,name,no,itemType,itemTypeName,unitPrice,vendorPrice,availableToSell'
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di manual test yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get item detail by ID
     * @param int $itemId Item ID
     * @return array Item detail
     */
    public function getItemDetail($itemId) {
        // Validasi ID item
        if (empty($itemId)) {
            return [
                'success' => false,
                'message' => 'Item ID is required',
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar dari dokumentasi
        $url = $this->host . '/accurate/api/item/detail.do';
        $params = ['id' => $itemId];
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di getItemList yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get branch list
     * @return array Branch list
     */
    public function getBranchList() {
        // Gunakan endpoint yang sudah terbukti berhasil
        $url = $this->host . '/accurate/api/branch/list.do';
        
        // Direct cURL call seperti di getItemList yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response,
            'endpoint_used' => '/accurate/api/branch/list.do'
        ];
    }
    
    /**
     * Get branch detail by ID
     * @param int $branchId Branch ID
     * @return array Branch detail
     */
    public function getBranchDetail($branchId) {
        // Validasi ID branch
        if (empty($branchId)) {
            return [
                'success' => false,
                'message' => 'Branch ID is required',
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar: /accurate/api/branch/detail.do
        $url = $this->host . '/accurate/api/branch/detail.do';
        $params = ['id' => $branchId];
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get database list
     * @return array Database list
     */
    public function getDatabaseList() {
        // Gunakan endpoint yang benar dari dokumentasi
        $url = 'https://account.accurate.id/api/db-list.do';
        
        $headers = [
            "Authorization: Bearer {$this->accessToken}",
            "Accept: application/json"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
                'data' => null
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;
        
        return [
            'success' => $success,
            'data' => $decodedResponse,
            'http_code' => $httpCode,
            'error' => $success ? null : 'HTTP ' . $httpCode . ' error',
            'raw_response' => $response
        ];
    }
    
    /**
     * Open session dengan database
     * @param int $databaseId Database ID
     * @return array Session response
     */
    public function openSession($databaseId) {
        $url = $this->host . '/accurate/api/session/open.do';
        $data = ['databaseId' => $databaseId];
        
        return $this->makeRequest($url, 'POST', $data);
    }
    
    /**
     * Close session
     * @return array Session response
     */
    public function closeSession() {
        $url = $this->host . '/accurate/api/session/close.do';
        return $this->makeRequest($url, 'POST');
    }
    
    /**
     * Open database
     * @param int $databaseId Database ID
     * @return array Database open response
     */
    public function openDatabase($databaseId = null) {
        // Gunakan database ID dari parameter atau default
        if (empty($databaseId)) {
            $databaseId = $this->databaseId;
        }
        
        if (empty($databaseId)) {
            return [
                'success' => false,
                'error' => 'Database ID is required',
                'http_code' => 400,
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar dari dokumentasi
        $url = 'https://account.accurate.id/api/open-db.do?id=' . $databaseId;
        
        $headers = [
            "Authorization: Bearer {$this->accessToken}",
            "Accept: application/json"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
                'data' => null
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;
        
        return [
            'success' => $success,
            'data' => $decodedResponse,
            'http_code' => $httpCode,
            'error' => $success ? null : 'HTTP ' . $httpCode . ' error',
            'raw_response' => $response
        ];
    }
    
    /**
     * Test API endpoint (public method untuk testing)
     * @param string $url Full URL
     * @param string $method HTTP method
     * @return array Response
     */
    public function testEndpoint($url, $method = 'GET') {
        return $this->makeRequest($url, $method);
    }
    
    /**
     * Get all available API endpoints for testing
     * @return array Available endpoints
     */
    public function getAvailableEndpoints() {
        $baseEndpoints = [
            // Branch endpoints
            '/accurate/api/branch/list.do',
            '/accurate/api/branch/branch/list.do',
            '/accurate/api/branch.do',
            '/accurate/api/branch/get.do',
            '/accurate/api/branch/all.do',
            '/accurate/api/branch/open.do',
            
            // Item endpoints for comparison
            '/accurate/api/item/list.do',
            '/accurate/api/item/open.do',
            
            // General endpoints
            '/accurate/api/db/list.do',
            '/accurate/api/session/open.do'
        ];
        
        $results = [];
        
        foreach ($baseEndpoints as $endpoint) {
            $url = $this->host . $endpoint;
            $response = $this->makeRequest($url, 'GET');
            
            $results[] = [
                'endpoint' => $endpoint,
                'url' => $url,
                'success' => $response['success'],
                'http_code' => $response['http_code'],
                'has_data' => isset($response['data']) && !empty($response['data']),
                'error' => $response['error'] ?? null
            ];
        }
        
        return $results;
    }
    
    /**
     * Check token status and scopes dynamically (berdasarkan approved scope API)
     * @param array $requiredScopes Array of required scopes
     * @return array Token status information
     */
    public function checkTokenStatus($requiredScopes = []) {
        // Dapatkan scope aktual dari approved-scope API
        $actualScopes = $this->getTokenScopes();
        
        // Scope yang documented dan bisa ditest dengan endpoint yang tersedia
        $testableScopes = [
            'item_view' => [$this, 'testItemView'],
            'branch_view' => [$this, 'testBranchView'],
            'vendor_view' => [$this, 'testVendorView'], 
            'warehouse_view' => [$this, 'testWarehouseView'],
            'customer_view' => [$this, 'testCustomerView'],
            'coa_view' => [$this, 'testCoaView'],
            'department_view' => [$this, 'testDepartmentView'],
            'employee_view' => [$this, 'testEmployeeView'],
            'unit_view' => [$this, 'testUnitView'],
            'currency_view' => [$this, 'testCurrencyView'],
            'tax_view' => [$this, 'testTaxView'],
            'sales_invoice_view' => [$this, 'testSalesInvoiceView'],
            'purchase_invoice_view' => [$this, 'testPurchaseInvoiceView'],
            'journal_view' => [$this, 'testJournalView'],
            'report_view' => [$this, 'testReportView'],
            'item_category_view' => [$this, 'testItemCategoryView']
        ];
        
        $result = [
            'valid' => false,
            'scopes' => [],
            'available_scopes' => [],
            'token_scopes' => $actualScopes,
            'approved_scope_api' => $this->getApprovedScopes()
        ];
        
        // Pastikan $actualScopes adalah array yang valid dan hanya berisi string
        if (!is_array($actualScopes)) {
            $actualScopes = [];
        }
        
        // Filter hanya element yang berupa string
        $actualScopes = array_filter($actualScopes, 'is_string');
        
        // Test scope yang utama dulu (minimal scope)
        $primaryScopes = ['item_view', 'branch_view', 'vendor_view', 'warehouse_view', 'item_category_view'];
        $workingScopes = 0;
        
        // Test all authorized scopes
        $scopesToTest = !empty($actualScopes) ? $actualScopes : $primaryScopes;
        
        foreach ($scopesToTest as $scope) {
            $isWorking = false;
            
            // Test jika scope ini testable
            if (isset($testableScopes[$scope])) {
                try {
                    $isWorking = call_user_func($testableScopes[$scope]);
                } catch (Exception $e) {
                    $isWorking = false;
                }
            } else {
                // Jika tidak ada test method, assume working jika ada di actual scopes
                $isWorking = in_array($scope, $actualScopes);
            }
            
            $result['scopes'][$scope] = $isWorking;
            $result['available_scopes'][$scope] = $isWorking;
            
            if ($isWorking) {
                $workingScopes++;
            }
        }
        
        // Set valid jika scope yang diperlukan tersedia
        if (!empty($requiredScopes) && is_array($requiredScopes)) {
            $validScopes = 0;
            foreach ($requiredScopes as $scope) {
                // Pastikan $scope adalah string
                if (is_string($scope) && isset($result['scopes'][$scope]) && $result['scopes'][$scope]) {
                    $validScopes++;
                }
            }
            $result['valid'] = $validScopes === count($requiredScopes);
        } else {
            // Jika tidak ada required scope, valid jika minimal 2 primary scope working
            $result['valid'] = $workingScopes >= 2;
        }
        
        return $result;
    }
    
    /**
     * Dapatkan scope aktual dari token dengan memanggil API approved-scope
     * @return array Array of scopes dari token
     */
    private function getTokenScopes() {
        // Coba ambil dari API approved-scope yang resmi
        try {
            $approvedScopes = $this->getApprovedScopes();
            if ($approvedScopes['success'] && !empty($approvedScopes['data'])) {
                return $approvedScopes['data'];
            }
        } catch (Exception $e) {
            // Fallback jika API error
        }
        
        // Fallback: Ambil dari konstanta config jika ada (seharusnya sudah disimpan saat token exchange)
        if (defined('ACCURATE_TOKEN_SCOPE') && !empty(ACCURATE_TOKEN_SCOPE)) {
            return explode(' ', trim(ACCURATE_TOKEN_SCOPE));
        }
        
        // Default scope minimal jika tidak ada info
        return ['item_view', 'branch_view', 'item_category_view', 'vendor_view', 'warehouse_view'];
    }
    
    /**
     * Get approved scopes dari token saat ini
     * Menggunakan endpoint resmi: https://account.accurate.id/api/approved-scope.do
     * @return array Response dengan daftar scope yang disetujui
     */
    public function getApprovedScopes() {
        $url = 'https://account.accurate.id/api/approved-scope.do';
        
        $headers = [
            "Authorization: Bearer {$this->accessToken}",
            "Accept: application/json"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
                'data' => []
            ];
        }
        
        $success = $httpCode >= 200 && $httpCode < 300;
        $decodedResponse = json_decode($response, true);
        
        if (!$success) {
            return [
                'success' => false,
                'error' => 'HTTP ' . $httpCode . ' error',
                'http_code' => $httpCode,
                'data' => [],
                'raw_response' => $response
            ];
        }
        
        // Parse scope data dari response API
        $scopeData = [];
        
        if ($decodedResponse) {
            // Handle different response formats
            if (is_array($decodedResponse)) {
                // Check jika ada field 'd' (format Accurate API biasanya)
                if (isset($decodedResponse['d']) && is_array($decodedResponse['d'])) {
                    $scopeData = $decodedResponse['d'];
                } elseif (is_array($decodedResponse) && !empty($decodedResponse)) {
                    // Atau response langsung array of scopes
                    $scopeData = $decodedResponse;
                }
            } elseif (is_string($decodedResponse)) {
                // Jika response berupa string space-separated
                $scopeData = explode(' ', trim($decodedResponse));
            }
        }
        
        // Filter hanya string scope yang valid
        if (is_array($scopeData)) {
            $scopeData = array_filter($scopeData, function($item) {
                return is_string($item) && !empty(trim($item));
            });
            $scopeData = array_values($scopeData); // Reindex array
        } else {
            $scopeData = [];
        }
        
        return [
            'success' => true,
            'data' => $scopeData,
            'http_code' => $httpCode,
            'error' => null,
            'raw_response' => $response
        ];
    }
    
    /**
     * Helper method untuk GET request dengan query parameters
     * @param string $url Base URL
     * @param array $params Query parameters
     * @return array Response dari API
     */
    private function makeGetRequest($url, $params = []) {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->makeRequest($url, 'GET');
    }
    
    // Test methods untuk setiap scope (berdasarkan dokumentasi resmi Accurate Online)
    private function testItemView() {
        try {
            $response = $this->getItemList(1, 1);
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testBranchView() {
        try {
            $response = $this->getBranchList();
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testVendorView() {
        try {
            $response = $this->getVendorList(1, 1);
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testWarehouseView() {
        try {
            $response = $this->getWarehouseList(1, 1);
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testCustomerView() {
        $url = $this->host . '/accurate/api/customer/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testCoaView() {
        $url = $this->host . '/accurate/api/coa/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testDepartmentView() {
        $url = $this->host . '/accurate/api/department/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testEmployeeView() {
        $url = $this->host . '/accurate/api/employee/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testUnitView() {
        $url = $this->host . '/accurate/api/unit/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testCurrencyView() {
        $url = $this->host . '/accurate/api/currency/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testTaxView() {
        $url = $this->host . '/accurate/api/tax/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testSalesInvoiceView() {
        $url = $this->host . '/accurate/api/sales-invoice/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testPurchaseInvoiceView() {
        $url = $this->host . '/accurate/api/purchase-invoice/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testJournalView() {
        $url = $this->host . '/accurate/api/journal/list.do';
        $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
        return $response['success'];
    }
    
    private function testReportView() {
        $url = $this->host . '/accurate/api/report/balance-sheet.do';
        $response = $this->makeGetRequest($url, ['detailType' => 'DETAIL']);
        return $response['success'];
    }
    
    private function testItemCategoryView() {
        try {
            // Try the standard endpoint first
            $url = $this->host . '/accurate/api/item-category/list.do';
            $response = $this->makeGetRequest($url, ['sp.page' => 1, 'sp.pageSize' => 1]);
            
            if ($response['success']) {
                return true;
            }
            
            // Try alternative endpoint
            $url2 = $this->host . '/accurate/api/item/category-list.do';
            $response2 = $this->makeGetRequest($url2, ['sp.page' => 1, 'sp.pageSize' => 1]);
            
            return isset($response2['success']) && $response2['success'];
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get vendor list
     * @param int $page Page number (default: 1)
     * @param int $pageSize Items per page (default: 100)
     * @return array Response data
     */
    public function getVendorList($page = 1, $pageSize = 100) {
        $url = $this->host . '/accurate/api/vendor/list.do';
        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,no,email,mobilePhone,phone,balanceList,category'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'X-Session-ID: ' . $this->sessionId,
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'http_code' => $httpCode,
                'endpoint_used' => $url
            ];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'data' => $data,
                    'http_code' => $httpCode,
                    'endpoint_used' => $url
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode,
            'response' => $response,
            'http_code' => $httpCode,
            'endpoint_used' => $url
        ];
    }
    
    /**
     * Get vendor detail by ID
     * @param int $vendorId Vendor ID
     * @return array Response data
     */
    public function getVendorDetail($vendorId) {
        // Validasi ID vendor
        if (empty($vendorId)) {
            return [
                'success' => false,
                'message' => 'Vendor ID is required',
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar dari dokumentasi
        $url = $this->host . '/accurate/api/vendor/detail.do';
        $params = ['id' => $vendorId];
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get warehouse list dari Accurate API
     * @param int $limit Jumlah warehouse per halaman
     * @param int $page Halaman yang diinginkan
     * @return array Response dari API
     */
    public function getWarehouseList($limit = 25, $page = 1) {
        $url = $this->host . '/accurate/api/warehouse/list.do';
        
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get warehouse detail berdasarkan ID
     * @param int $warehouseId ID warehouse
     * @return array Response dari API
     */
    public function getWarehouseDetail($warehouseId) {
        // Validasi ID warehouse
        if (empty($warehouseId)) {
            return [
                'success' => false,
                'message' => 'Warehouse ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/warehouse/detail.do';
        
        $params = [
            'id' => $warehouseId
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get employee list dari Accurate API
     * @param int $limit Jumlah employee per halaman
     * @param int $page Halaman yang diinginkan
     * @return array Response dari API
     */
    public function getEmployeeList($limit = 25, $page = 1) {
        $url = $this->host . '/accurate/api/employee/list.do';
        
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get customer list with pagination
     * @param int $limit Number of records per page (default: 25, max: 100)
     * @param int $page Page number (default: 1)
     * @return array Response array with success status, data, and error information
     */
    public function getCustomerList($limit = 25, $page = 1) {
        $url = $this->host . '/accurate/api/customer/list.do';
        
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get employee detail berdasarkan ID
     * @param int $employeeId ID employee
     * @return array Response dari API
     */
    public function getCustomerDetail($customerId) {
        // Validasi ID customer
        if (empty($customerId)) {
            return [
                'success' => false,
                'message' => 'Customer ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/customer/detail.do';
        
        $params = [
            'id' => $customerId
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get employee detail berdasarkan ID
     * @param int $employeeId ID employee
     * @return array Response dari API
     */
    public function getEmployeeDetail($employeeId) {
        // Validasi ID employee
        if (empty($employeeId)) {
            return [
                'success' => false,
                'message' => 'Employee ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/employee/detail.do';
        
        $params = [
            'id' => $employeeId
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage,
            'raw_response' => $response
        ];
    }
    
    /**
     * Auto refresh token jika diperlukan berdasarkan status token saat ini
     * @return array Refresh result
     */
    public function autoRefreshIfNeeded() {
        // Cek apakah token masih valid dengan test sederhana
        $tokenStatus = $this->checkTokenStatus();
        
        if (!$tokenStatus['valid']) {
            // Token tidak valid, coba refresh
            $refreshResult = $this->refreshToken();
            
            if ($refreshResult['success']) {
                // Refresh berhasil dan config sudah otomatis terupdate
                return [
                    'success' => true,
                    'action' => 'token_refreshed',
                    'message' => 'Token refreshed automatically and config updated',
                    'data' => $refreshResult['data'],
                    'config_updated' => $refreshResult['config_updated']
                ];
            } else {
                // Refresh gagal, perlu re-authorization
                return [
                    'success' => false,
                    'action' => 'need_reauthorization',
                    'message' => 'Token refresh failed, need new authorization',
                    'error' => $refreshResult['error']
                ];
            }
        }
        
        // Token masih valid
        return [
            'success' => true,
            'action' => 'token_valid',
            'message' => 'Token is still valid, no refresh needed'
        ];
    }
    
    /**
     * Get shipment list
     * @param int $page Page number (default: 1)
     * @param int $pageSize Items per page (default: 100)
     * @return array Response data
     */
    public function getShipmentList($page = 1, $pageSize = 100) {
        $url = $this->host . '/accurate/api/shipment/list.do';
        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,number,transDate,description,customerName,customerNo,totalAmount,shipmentStatus'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'X-Session-ID: ' . $this->sessionId,
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'http_code' => $httpCode,
                'endpoint_used' => $url
            ];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'data' => $data,
                    'http_code' => $httpCode,
                    'endpoint_used' => $url
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode,
            'response' => $response,
            'http_code' => $httpCode,
            'endpoint_used' => $url
        ];
    }
    
    /**
     * Get shipment detail by ID
     * @param int $shipmentId Shipment ID
     * @return array Response data
     */
    public function getShipmentDetail($shipmentId) {
        // Validasi ID shipment
        if (empty($shipmentId)) {
            return [
                'success' => false,
                'message' => 'Shipment ID is required',
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar dari dokumentasi
        $url = $this->host . '/accurate/api/shipment/detail.do';
        $params = ['id' => $shipmentId];
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse)) {
                $errorMessage = $decodedResponse['message'] ?? $decodedResponse['error'] ?? 'Unknown error';
            } else {
                $errorMessage = 'API request failed';
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage
        ];
    }
    
    /**
     * Get item category list
     * @param int $page Page number (default: 1)
     * @param int $pageSize Items per page (default: 100)
     * @return array Response data
     */
    public function getItemCategoryList($page = 1, $pageSize = 100) {
        $url = $this->host . '/accurate/api/item-category/list.do';
        $params = [
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,nameWithIndent,nameWithIndentStrip,lvl,parentNode,defaultCategory'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'X-Session-ID: ' . $this->sessionId,
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'http_code' => $httpCode,
                'endpoint_used' => $url
            ];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'data' => $data,
                    'http_code' => $httpCode,
                    'endpoint_used' => $url
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode,
            'response' => $response,
            'http_code' => $httpCode,
            'endpoint_used' => $url
        ];
    }
    
    /**
     * Get item category detail by ID
     * @param int $categoryId Category ID
     * @return array Response data
     */
    public function getItemCategoryDetail($categoryId) {
        // Validasi ID category
        if (empty($categoryId)) {
            return [
                'success' => false,
                'message' => 'Category ID is required',
                'data' => null
            ];
        }
        
        // Gunakan endpoint yang benar dari dokumentasi
        $url = $this->host . '/accurate/api/item-category/detail.do';
        $params = ['id' => $categoryId];
        $url .= '?' . http_build_query($params);
        
        // Direct cURL call seperti di method lain yang berhasil
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Session-ID: {$this->sessionId}",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'data' => null,
                'error' => $error
            ];
        }
        
        // Parse response
        $decodedResponse = json_decode($response, true);
        
        // Determine success based on HTTP code
        $success = $httpCode >= 200 && $httpCode < 300;
        
        // Handle specific error cases
        $errorMessage = null;
        
        if (!$success) {
            // Try to get error message from response
            if (is_array($decodedResponse)) {
                $errorMessage = $decodedResponse['message'] ?? $decodedResponse['error'] ?? 'Unknown error';
            } else {
                $errorMessage = 'API request failed';
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'error' => $errorMessage
        ];
    }

    /**
     * Get list of fixed assets
     * @param array $params Parameters untuk filter (page, per_page, dll)
     * @return array Response dari API
     */
    public function getFixedAssetList($params = []) {
        $url = $this->host . '/accurate/api/fixed-asset/list.do';
        
        // Parameter default
        $defaultParams = [
            'sp.pageSize' => 25,
            'sp.page' => 1
        ];
        
        // Merge dengan parameter yang diberikan
        $queryParams = array_merge($defaultParams, $params);
        
        // Build URL dengan query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Get detail fixed asset berdasarkan ID
     * @param int $id ID dari fixed asset
     * @return array Response dari API
     */
    public function getFixedAssetDetail($id) {
        $url = $this->host . '/accurate/api/fixed-asset/detail.do';
        
        $params = [
            'id' => $id
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get price category list dengan pagination
     * @param array $params Parameters untuk filter
     * @return array Response dari API
     */
    public function getPriceCategoryList($params = []) {
        $url = $this->host . '/accurate/api/price-category/list.do';
        
        // Default parameters untuk pagination
        $defaultParams = [
            'sp.page' => 1,
            'sp.pageSize' => 100
        ];
        
        // Merge dengan params yang diberikan
        $params = array_merge($defaultParams, $params);
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get price category detail berdasarkan ID
     * @param int $priceCategoryId ID price category
     * @return array Response dari API
     */
    public function getPriceCategoryDetail($priceCategoryId) {
        // Validasi ID price category
        if (empty($priceCategoryId)) {
            return [
                'success' => false,
                'message' => 'Price category ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/price-category/detail.do';
        
        $params = [
            'id' => $priceCategoryId
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get sales order list
     * @param array $params Parameter tambahan untuk query
     * @return array Response dari API
     */
    public function getSalesOrderList($params = []) {
        $url = $this->host . '/accurate/api/sales-order/list.do';
        
        // Default parameters
        $defaultParams = [
            'sp.page' => 1,
            'sp.pageSize' => 100
        ];
        
        // Merge dengan params yang diberikan
        $params = array_merge($defaultParams, $params);
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get sales order detail berdasarkan ID
     * @param int $salesOrderId ID sales order
     * @return array Response dari API
     */
    public function getSalesOrderDetail($salesOrderId) {
        // Validasi ID sales order
        if (empty($salesOrderId)) {
            return [
                'success' => false,
                'message' => 'Sales order ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/sales-order/detail.do';
        
        $params = [
            'id' => $salesOrderId
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get sales invoice list
     * @param array $params Parameter tambahan untuk query
     * @return array Response dari API
     */
    public function getSalesInvoiceList($params = []) {
        $url = $this->host . '/accurate/api/sales-invoice/list.do';
        
        // Default parameters
        $defaultParams = [
            'sp.page' => 1,
            'sp.pageSize' => 100
        ];
        
        // Merge dengan params yang diberikan
        $params = array_merge($defaultParams, $params);
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get sales invoice detail berdasarkan ID
     * @param int $salesInvoiceId ID sales invoice
     * @return array Response dari API
     */
    public function getSalesInvoiceDetail($salesInvoiceId) {
        // Validasi ID sales invoice
        if (empty($salesInvoiceId)) {
            return [
                'success' => false,
                'message' => 'Sales invoice ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/sales-invoice/detail.do';
        
        $params = [
            'id' => $salesInvoiceId
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Get sales receipt list dengan parameter
     * @param array $params Parameter untuk filtering
     * @return array Response dari API
     */
    public function getSalesReceiptList($params = []) {
        $url = $this->host . '/accurate/api/sales-receipt/list.do';
        
        // Default parameters
        $defaultParams = [
            'sp.page' => 1,
            'sp.pageSize' => 100
        ];
        
        // Merge dengan params yang diberikan
        $params = array_merge($defaultParams, $params);
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Get sales receipt detail berdasarkan ID
     * @param int $salesReceiptId ID sales receipt
     * @return array Response dari API
     */
    public function getSalesReceiptDetail($salesReceiptId) {
        // Validasi ID sales receipt
        if (empty($salesReceiptId)) {
            return [
                'success' => false,
                'message' => 'Sales receipt ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/sales-receipt/detail.do';
        
        $params = [
            'id' => $salesReceiptId
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get purchase order list dari Accurate API
     * @param array $params Parameter tambahan untuk query
     * @return array Response dari API
     */
    public function getPurchaseOrderList($params = []) {
        $url = $this->host . '/accurate/api/purchase-order/list.do';
        
        // Default parameters
        $defaultParams = [
            'sp.page' => 1,
            'sp.pageSize' => 100
        ];
        
        // Merge dengan params yang diberikan
        $params = array_merge($defaultParams, $params);
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
    
    /**
     * Get purchase order detail berdasarkan ID
     * @param int $purchaseOrderId ID purchase order
     * @return array Response dari API
     */
    public function getPurchaseOrderDetail($purchaseOrderId) {
        // Validasi ID purchase order
        if (empty($purchaseOrderId)) {
            return [
                'success' => false,
                'message' => 'Purchase order ID is required',
                'data' => null
            ];
        }
        
        $url = $this->host . '/accurate/api/purchase-order/detail.do';
        
        $params = [
            'id' => $purchaseOrderId
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->makeRequest($url, 'GET');
    }
}
?>
