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
    
    public function setAccessToken($newToken) {
        $this->accessToken = $newToken;
    }
    
    public function setSessionId($newSessionId) {
        $this->sessionId = $newSessionId;
    }
    
    public function setHost($newHost) {
        $this->host = $newHost;
    }
    
    private function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Nuansa Accurate API Client/1.0'
        ]);
        
        $defaultHeaders = [
            "Authorization: Bearer {$this->accessToken}",
            "Accept: application/json"
        ];
        
        if ($this->sessionId) {
            $defaultHeaders[] = "X-Session-ID: {$this->sessionId}";
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    $isFormData = false;
                    foreach ($allHeaders as $header) {
                        if (stripos($header, 'Content-Type: application/x-www-form-urlencoded') !== false) {
                            $isFormData = true;
                            break;
                        }
                    }
                    if ($isFormData) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
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
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            logError("cURL Error: $error", __FILE__, __LINE__);
            return ['success' => false, 'http_code' => 0, 'data' => null, 'error' => $error];
        }
        
        $decodedResponse = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;
        
        if ($success && is_array($decodedResponse)) {
            if (isset($decodedResponse['s']) && $decodedResponse['s'] === false) {
                $success = false;
            }
        }
        
        $errorMessage = null;
        if (!$success) {
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $errorMessage = $decodedResponse['error'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
            } elseif (is_array($decodedResponse) && isset($decodedResponse['d']) && is_array($decodedResponse['d']) && !empty($decodedResponse['d'])) {
                $errorMessage = implode(', ', $decodedResponse['d']);
            } else {
                $errorMessage = "HTTP $httpCode error";
            }
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
    
    public function getSessionInfo() {
        $databaseInfo = null;
        $databaseList = $this->getDatabaseList();
        if ($databaseList['success'] && isset($databaseList['data']['d'])) {
            foreach ($databaseList['data']['d'] as $db) {
                if ($db['id'] == $this->databaseId) {
                    $databaseInfo = $db;
                    break;
                }
                if (!$databaseInfo && !$db['expired']) {
                    $databaseInfo = $db;
                }
            }
            if (!$databaseInfo && !empty($databaseList['data']['d'])) {
                $databaseInfo = end($databaseList['data']['d']);
            }
        }
        return [
            'access_token' => $this->accessToken,
            'session_id' => $this->sessionId,
            'host' => $this->host,
            'database_id' => $this->databaseId,
            'database_info' => $databaseInfo,
            'database_alias' => $databaseInfo['alias'] ?? 'Unknown Database',
            'database_expired' => $databaseInfo['expired'] ?? true,
            'database_trial_end' => $databaseInfo['trialEnd'] ?? 'Unknown'
        ];
    }

    public function getDatabaseList() {
        $url = 'https://account.accurate.id/api/db-list.do';
        return $this->makeRequest($url);
    }

    public function getEmployeeList($limit = 25, $page = 1) {
        $url = $this->host . '/accurate/api/employee/list.do';
        $params = ['sp.pageSize' => $limit, 'sp.page' => $page];
        $url .= '?' . http_build_query($params);
        return $this->makeRequest($url);
    }

    public function getItemList($limit = 100, $page = 1, $filters = []) {
        $url = $this->host . '/accurate/api/item/list.do';
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page,
            'fields' => 'id,name,no,itemType,itemTypeName,unitPrice,vendorPrice,availableToSell,lastUpdate'
        ];
        if (!empty($filters)) {
            $params = array_merge($params, $filters);
        }
        $url .= '?' . http_build_query($params);
        return $this->makeRequest($url);
    }

    public function getItemTransferList($limit = 25, $page = 1, $filters = []) {
        $url = $this->host . '/accurate/api/item-transfer/list.do';
        $params = [
            'sp.pageSize' => $limit,
            'sp.page' => $page,
            'fields' => 'id,number,transDate,itemTransferOutStatus,referenceWarehouseName,warehouseName'
        ];
        if (!empty($filters)) {
            $params = array_merge($params, $filters);
        }
        $url .= '?' . http_build_query($params);
        return $this->makeRequest($url);
    }

    public function getItemTransferDetail($transferId) {
        if (empty($transferId)) {
            return ['success' => false, 'message' => 'Item Transfer ID is required', 'data' => null];
        }
        $url = $this->host . '/accurate/api/item-transfer/detail.do';
        $params = [
            'id' => $transferId,
            'fields' => 'number,transDateView,branchId,warehouseName,statusName,itemTransferType,itemTransferOutStatus,referenceWarehouseName,detailItem,detailItem.item,detailItem.item.unit1,detailItem.detailSerialNumber,detailItem.detailSerialNumber.serialNumber'
        ];
        $url .= '?' . http_build_query($params);
        return $this->makeRequest($url);
    }
}
?>
