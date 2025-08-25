<?php
/**
 * Dedicated Refresh Token Interface dengan Automation Config Update
 * Interface khusus untuk melakukan refresh token dengan tampilan yang user-friendly
 */

require_once __DIR__ . '/../bootstrap.php';

// Handle JSON API request untuk AJAX calls
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    
    try {
        $api = new AccurateAPI();
        $result = $api->refreshToken();
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Token refreshed successfully and config updated automatically',
                'data' => [
                    'access_token' => $result['data']['access_token'],
                    'refresh_token' => $result['data']['refresh_token'] ?? 'Not provided',
                    'scope' => $result['data']['scope'] ?? 'Not provided',
                    'expires_in' => $result['data']['expires_in'] ?? 'Not provided',
                    'user' => $result['data']['user'] ?? 'Not provided'
                ],
                'config_update' => $result['config_updated'],
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error'],
                'details' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
    }
    exit;
}

// HTML Interface untuk Web Browser
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔄 Refresh Token - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="gradient-bg text-white py-8">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="fas fa-sync-alt mr-3"></i>
                        Refresh Token Manager
                    </h1>
                    <p class="text-blue-100 mt-2">Automated token refresh with config update</p>
                </div>
                <a href="../index.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-lg transition-all">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Current Configuration Status -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-cog text-blue-600 mr-3"></i>
                Current Configuration Status
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Access Token -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-blue-800">Access Token</h3>
                        <i class="fas fa-key text-blue-600"></i>
                    </div>
                    <p class="text-sm text-blue-700 font-mono break-all">
                        <?php echo substr(ACCURATE_ACCESS_TOKEN, 0, 25) . '...'; ?>
                    </p>
                    <p class="text-xs text-blue-600 mt-1">Used for API authentication</p>
                </div>

                <!-- Refresh Token -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-green-800">Refresh Token</h3>
                        <i class="fas fa-refresh text-green-600"></i>
                    </div>
                    <p class="text-sm text-green-700 font-mono break-all">
                        <?php echo substr(ACCURATE_REFRESH_TOKEN, 0, 25) . '...'; ?>
                    </p>
                    <p class="text-xs text-green-600 mt-1">Used to refresh access token</p>
                </div>

                <!-- Token Scope -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-purple-800">Token Scope</h3>
                        <i class="fas fa-shield-alt text-purple-600"></i>
                    </div>
                    <p class="text-sm text-purple-700">
                        <?php 
                        $scopes = explode(' ', ACCURATE_TOKEN_SCOPE);
                        echo count($scopes) . ' scopes active';
                        ?>
                    </p>
                    <p class="text-xs text-purple-600 mt-1">API permissions granted</p>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Active Scopes:</h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (explode(' ', ACCURATE_TOKEN_SCOPE) as $scope): ?>
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"><?php echo $scope; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Refresh Action Panel -->
        <div class="bg-white rounded-xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-rocket text-green-600 mr-3"></i>
                Token Refresh Action
            </h2>
            
            <!-- Automation Features Info -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-magic text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-green-800 mb-2">🤖 Automation Features</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-green-700">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Auto update ACCESS_TOKEN in config.php
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Auto update REFRESH_TOKEN in config.php
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Auto update SCOPE in config.php
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                No manual intervention required
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2 italic">
                            ✨ Semua perubahan akan diterapkan secara otomatis ke file konfigurasi
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Refresh Button -->
            <div class="text-center">
                <button id="refreshBtn" onclick="refreshToken()" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-8 py-4 rounded-lg font-medium text-lg transition-all transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sync-alt mr-3"></i>
                    🔄 Refresh Token Now
                </button>
                <p class="text-gray-600 text-sm mt-3">
                    Click to refresh your access token and automatically update configuration
                </p>
            </div>
        </div>

        <!-- Results Panel -->
        <div id="resultsPanel" class="hidden bg-white rounded-xl card-shadow p-6">
            <h2 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-bar text-purple-600 mr-3"></i>
                Refresh Results
            </h2>
            <div id="resultsContent"></div>
        </div>
    </div>

    <script>
    async function refreshToken() {
        const button = document.getElementById('refreshBtn');
        const resultsPanel = document.getElementById('resultsPanel');
        const resultsContent = document.getElementById('resultsContent');
        const originalText = button.innerHTML;
        
        // Loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>🔄 Refreshing...';
        button.disabled = true;
        button.classList.add('pulse-animation');
        
        try {
            const response = await fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            // Show results panel
            resultsPanel.classList.remove('hidden');
            resultsPanel.scrollIntoView({ behavior: 'smooth' });
            
            if (result.success) {
                // Success result
                resultsContent.innerHTML = `
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-xl font-semibold text-green-800 mb-2">
                                    ✅ Token Refreshed Successfully!
                                </h3>
                                <p class="text-green-700 mb-4">${result.message}</p>
                                <div class="text-sm text-green-600">
                                    <i class="fas fa-clock mr-1"></i>
                                    ${result.timestamp}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- New Token Data -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-key mr-2"></i>New Token Data
                            </h4>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <strong class="text-blue-700">Access Token:</strong>
                                    <p class="font-mono text-xs text-blue-600 bg-white p-2 rounded mt-1 break-all">
                                        ${result.data.access_token.substring(0, 50)}...
                                    </p>
                                </div>
                                <div>
                                    <strong class="text-blue-700">Refresh Token:</strong>
                                    <p class="font-mono text-xs text-blue-600 bg-white p-2 rounded mt-1 break-all">
                                        ${result.data.refresh_token.substring(0, 50)}...
                                    </p>
                                </div>
                                ${result.data.scope !== 'Not provided' ? `
                                <div>
                                    <strong class="text-blue-700">Scope:</strong>
                                    <p class="text-xs text-blue-600 bg-white p-2 rounded mt-1">
                                        ${result.data.scope}
                                    </p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <!-- Config Update Status -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="font-semibold text-purple-800 mb-3 flex items-center">
                                <i class="fas fa-cog mr-2"></i>Config Update Status
                            </h4>
                            ${result.config_update.success ? `
                                <div class="bg-green-100 border border-green-300 rounded-lg p-3 mb-3">
                                    <p class="text-green-800 font-medium flex items-center">
                                        <i class="fas fa-check mr-2"></i>${result.config_update.message}
                                    </p>
                                </div>
                                <div class="space-y-2 text-sm">
                                    ${Object.entries(result.config_update.updated_fields).map(([field, status]) => `
                                        <div class="flex justify-between items-center">
                                            <span class="text-purple-700">${field.replace('_', ' ').toUpperCase()}:</span>
                                            <span class="text-green-600 font-medium">${status}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : `
                                <div class="bg-red-100 border border-red-300 rounded-lg p-3">
                                    <p class="text-red-800 font-medium flex items-center">
                                        <i class="fas fa-times mr-2"></i>Config update failed
                                    </p>
                                    <p class="text-red-700 text-sm mt-1">${result.config_update.error}</p>
                                </div>
                            `}
                        </div>
                    </div>
                    
                    <!-- Next Steps -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-yellow-800 mb-2 flex items-center">
                            <i class="fas fa-lightbulb mr-2"></i>Next Steps
                        </h4>
                        <ul class="text-yellow-700 text-sm space-y-1">
                            <li>• Config.php has been automatically updated with new tokens</li>
                            <li>• You can continue using the API with the new access token</li>
                            <li>• The new refresh token will be used for future refreshes</li>
                            <li>• No manual intervention required</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 justify-center">
                        <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-redo mr-2"></i>Reload Page
                        </button>
                        <a href="../index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-home mr-2"></i>Back to Dashboard
                        </a>
                        <a href="token-status.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-shield-alt mr-2"></i>Check Token Status
                        </a>
                    </div>
                `;
                
            } else {
                // Error result
                resultsContent.innerHTML = `
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-lg p-6 mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-xl font-semibold text-red-800 mb-2">
                                    ❌ Refresh Failed
                                </h3>
                                <p class="text-red-700 mb-2">${result.error}</p>
                                <div class="text-sm text-red-600">
                                    <i class="fas fa-clock mr-1"></i>
                                    ${result.timestamp}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h4 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <i class="fas fa-tools mr-2"></i>💡 Troubleshooting Solutions
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-yellow-700">
                            <div>
                                <h5 class="font-medium mb-2">Common Causes:</h5>
                                <ul class="space-y-1">
                                    <li>• Refresh token has expired</li>
                                    <li>• Invalid OAuth client credentials</li>
                                    <li>• Network connectivity issues</li>
                                    <li>• Accurate API server problems</li>
                                </ul>
                            </div>
                            <div>
                                <h5 class="font-medium mb-2">Recommended Actions:</h5>
                                <ul class="space-y-1">
                                    <li>• <a href="authorize.php" class="text-blue-600 hover:underline">Re-authorize application</a></li>
                                    <li>• Check internet connection</li>
                                    <li>• Verify OAuth credentials</li>
                                    <li>• Contact system administrator</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-3">
                            <a href="authorize.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-key mr-2"></i>Re-authorize
                            </a>
                            <button onclick="refreshToken()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-redo mr-2"></i>Try Again
                            </button>
                        </div>
                    </div>
                `;
            }
            
        } catch (error) {
            console.error('Refresh error:', error);
            resultsPanel.classList.remove('hidden');
            resultsContent.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                        <h3 class="text-lg font-semibold text-red-800">Network Error</h3>
                    </div>
                    <p class="text-red-700 mb-4">Unable to connect to the refresh endpoint.</p>
                    <p class="text-red-600 text-sm">Error: ${error.message}</p>
                    
                    <div class="mt-4">
                        <button onclick="refreshToken()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-redo mr-2"></i>Retry
                        </button>
                    </div>
                </div>
            `;
        } finally {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
            button.classList.remove('pulse-animation');
        }
    }
    </script>
</body>
</html>
