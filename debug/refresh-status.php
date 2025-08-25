<?php
/**
 * Auto Refresh Token Status - API endpoint untuk refresh scope status otomatis
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/AutoOAuthHandler.php';

header('Content-Type: application/json');

try {
    $autoHandler = new AutoOAuthHandler();
    $api = new AccurateAPI();
    
    // Get token status and check authorized scopes
    $tokenResponse = $api->checkTokenStatus();
    
    // Process token response to create compatible structure
    $scopeResult = [
        'valid' => false,
        'scopes' => []
    ];
    
    // Define required scopes to test
     $requiredScopes = [
         'item_view' => 'Item View',
         'branch_view' => 'Branch View', 
         'vendor_view' => 'Vendor View',
         'customer_view' => 'Customer View',
         'warehouse_view' => 'Warehouse View',
         'sales_invoice_view' => 'Sales Invoice View',
         'purchase_invoice_view' => 'Purchase Invoice View',
         'item_brand_view' => 'Item Brand View',
         'job_order_view' => 'Job Order View'
     ];
    
    if ($tokenResponse['success'] && isset($tokenResponse['data'])) {
        $tokenData = $tokenResponse['data'];
        
        // Check if token is valid based on response structure
        if (isset($tokenData['active']) && $tokenData['active'] === true) {
            // Get authorized scopes from OAuth response
            $authorizedScopes = [];
            if (isset($tokenData['scope'])) {
                $authorizedScopes = explode(' ', $tokenData['scope']);
            }
            
            // Check each required scope against OAuth authorized scopes
            $allScopesValid = true;
            
            foreach ($requiredScopes as $scope => $description) {
                // Check if scope is actually authorized in OAuth response
                $isAuthorized = in_array($scope, $authorizedScopes);
                
                $scopeResult['scopes'][$scope] = $isAuthorized;
                
                if (!$isAuthorized) {
                    $allScopesValid = false;
                }
            }
            
            $scopeResult['valid'] = $allScopesValid;
        } else {
            // Token is not active, mark all scopes as invalid
            foreach ($requiredScopes as $scope => $description) {
                $scopeResult['scopes'][$scope] = false;
            }
        }
    } else {
        // API call failed, mark all scopes as invalid
        foreach ($requiredScopes as $scope => $description) {
            $scopeResult['scopes'][$scope] = false;
        }
    }
    
    // Get session info juga
    $sessionInfo = $api->getSessionInfo();
    
    $response = [
        'scopes' => $scopeResult['scopes'],
        'all_scopes_valid' => $scopeResult['valid'],
        'session_info' => [
            'host' => $sessionInfo['host'],
            'database_id' => $sessionInfo['database_id'],
            'has_session' => !empty($sessionInfo['session_id'])
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'refresh_success' => true
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $response,
        'message' => 'Token status refreshed successfully'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to refresh token status'
    ], JSON_PRETTY_PRINT);
}
?>
