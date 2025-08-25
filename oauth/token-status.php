<?php
/**
 * Token Status Checker
 * Check apakah token memiliki scope yang diperlukan
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

// Check token status
$tokenResponse = $api->checkTokenStatus();

// Process token response to create compatible structure
$tokenStatus = [
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
            
            $tokenStatus['scopes'][$scope] = $isAuthorized;
            
            if (!$isAuthorized) {
                $allScopesValid = false;
            }
        }
        
        $tokenStatus['valid'] = $allScopesValid;
    } else {
        // Token is not active, mark all scopes as invalid
        foreach ($requiredScopes as $scope => $description) {
            $tokenStatus['scopes'][$scope] = false;
        }
    }
} else {
    // API call failed, mark all scopes as invalid
    foreach ($requiredScopes as $scope => $description) {
        $tokenStatus['scopes'][$scope] = false;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Token Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-shield-alt"></i> Token Status Checker
                </h1>
                <a href="../index.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </div>
        </div>
        
        <!-- Overall Status -->
        <div class="mb-6">
            <?php if (isset($tokenStatus['valid']) && $tokenStatus['valid']): ?>
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-green-800">Token Valid</h3>
                            <p class="text-sm text-green-600">Semua scope yang diperlukan tersedia dan dapat digunakan</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-red-800">Token Bermasalah</h3>
                            <p class="text-sm text-red-600">Beberapa scope tidak tersedia atau token perlu diperbarui</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Scope Status -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-list-check"></i> Status Scope
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (isset($tokenStatus['scopes']) && is_array($tokenStatus['scopes'])): ?>
                    <?php foreach ($tokenStatus['scopes'] as $scope => $status): ?>
                    <div class="relative group">
                        <?php if ($status): ?>
                            <!-- Valid Scope -->
                            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <code class="text-green-100 font-semibold"><?php echo $scope; ?></code>
                                        <div class="text-xs text-green-200 mt-1">
                                            <i class="fas fa-check mr-1"></i>Available
                                        </div>
                                    </div>
                                    <div class="text-green-200">
                                        <i class="fas fa-check-circle text-lg"></i>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Invalid Scope -->
                            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-4 rounded-lg shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <code class="text-red-100 font-semibold"><?php echo $scope; ?></code>
                                        <div class="text-xs text-red-200 mt-1">
                                            <i class="fas fa-times mr-1"></i>Not Available
                                        </div>
                                    </div>
                                    <div class="text-red-200">
                                        <i class="fas fa-times-circle text-lg"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center text-gray-500">
                        <p>No scope information available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-tools"></i> Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!isset($tokenStatus['valid']) || !$tokenStatus['valid']): ?>
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-yellow-800 mb-2">Authorization Diperlukan</h3>
                                <p class="text-sm text-yellow-700 mb-3">
                                    Token tidak memiliki scope yang diperlukan. Authorize ulang dengan scope lengkap.
                                </p>
                                <a href="authorize.php" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-key mr-2"></i> Get New Authorization
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-sync-alt text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Refresh Token Status</h3>
                            <p class="text-sm text-blue-700 mb-3">
                                Check ulang status token dan scope yang tersedia saat ini.
                            </p>
                            <div class="flex gap-2">
                                <button id="refreshBtn" onclick="refreshStatus()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i> Auto Refresh
                                </button>
                                <button onclick="location.reload()" 
                                        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-redo mr-2"></i> Page Reload
                                </button>
                            </div>
                            <div id="refreshResult" class="mt-3 hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function refreshStatus() {
            const refreshBtn = document.getElementById('refreshBtn');
            const refreshResult = document.getElementById('refreshResult');
            const icon = refreshBtn.querySelector('i');
            
            // Show loading state
            refreshBtn.disabled = true;
            icon.classList.add('fa-spin');
            refreshResult.className = 'mt-3 p-3 bg-blue-100 text-blue-700 rounded text-sm';
            refreshResult.textContent = 'Refreshing token status...';
            refreshResult.classList.remove('hidden');
            
            try {
                const response = await fetch('../debug/refresh-status.php');
                const data = await response.json();
                
                if (data.success) {
                    // Show success and scope info
                    let scopeText = '';
                    let availableCount = 0;
                    
                    for (const [scope, available] of Object.entries(data.data.scopes)) {
                        if (available) {
                            scopeText += `✅ ${scope} `;
                            availableCount++;
                        } else {
                            scopeText += `❌ ${scope} `;
                        }
                    }
                    
                    refreshResult.className = 'mt-3 p-3 bg-green-100 text-green-700 rounded text-sm';
                    refreshResult.innerHTML = `
                        <strong>✅ Status refreshed successfully!</strong><br>
                        <div class="mt-2">
                            <strong>Available scopes (${availableCount}/${Object.keys(data.data.scopes).length}):</strong><br>
                            ${scopeText}
                        </div>
                        <div class="mt-2 text-xs opacity-75">
                            Last updated: ${data.data.timestamp}
                        </div>
                    `;
                    
                    // Auto reload page after 2 seconds to show updated status
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    refreshResult.className = 'mt-3 p-3 bg-red-100 text-red-700 rounded text-sm';
                    refreshResult.textContent = '❌ Refresh failed: ' + (data.message || data.error);
                }
            } catch (error) {
                refreshResult.className = 'mt-3 p-3 bg-red-100 text-red-700 rounded text-sm';
                refreshResult.textContent = '❌ Network error: ' + error.message;
            } finally {
                // Reset button state
                refreshBtn.disabled = false;
                icon.classList.remove('fa-spin');
            }
        }
    </script>
</body>
</html>
