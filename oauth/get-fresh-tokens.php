<?php
/**
 * OAuth Authorization Helper - Get Fresh Tokens
 */

require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Authorization - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-key text-blue-600 mr-3"></i>
                OAuth Authorization Helper
            </h1>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">Token Refresh Issue Detected</h3>
                        <p class="text-yellow-700 mb-4">
                            The error "Halaman tidak boleh diakses" suggests that your current tokens (access token and/or refresh token) have expired or are invalid.
                        </p>
                        <p class="text-yellow-700">
                            You need to perform a fresh OAuth authorization to get new tokens.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Current Configuration
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>Client ID:</strong> <?php echo OAUTH_CLIENT_ID; ?></div>
                        <div><strong>Redirect URI:</strong> <?php echo OAUTH_REDIRECT_URI; ?></div>
                        <div><strong>Current Access Token:</strong> <?php echo substr(ACCURATE_ACCESS_TOKEN, 0, 20) . '...'; ?></div>
                        <div><strong>Current Refresh Token:</strong> <?php echo substr(ACCURATE_REFRESH_TOKEN, 0, 20) . '...'; ?></div>
                    </div>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Required Scopes
                    </h3>
                    <div class="text-sm">
                        <p class="mb-2">These scopes will be requested:</p>
                        <div class="flex flex-wrap gap-1">
                            <?php 
                            $scopes = ['item_view', 'branch_view', 'item_category_view', 'vendor_view', 'warehouse_view', 'employee_view', 'fixed_asset_view'];
                            foreach ($scopes as $scope): 
                            ?>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded"><?php echo $scope; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Get Fresh OAuth Tokens</h2>
                <p class="text-gray-600 mb-6">Click the button below to start the OAuth authorization process and get fresh tokens.</p>
                
                <?php
                $authUrl = ACCURATE_AUTH_HOST . '/oauth/authorize?' . http_build_query([
                    'client_id' => OAUTH_CLIENT_ID,
                    'response_type' => 'code',
                    'redirect_uri' => OAUTH_REDIRECT_URI,
                    'scope' => 'item_view branch_view item_category_view vendor_view warehouse_view employee_view fixed_asset_view'
                ]);
                ?>
                
                <a href="<?php echo $authUrl; ?>" 
                   class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-lg transition-colors shadow-lg">
                    <i class="fas fa-external-link-alt mr-3"></i>
                    Start OAuth Authorization
                </a>
                
                <div class="mt-6 text-sm text-gray-500">
                    <p>You will be redirected to Accurate's authorization page.</p>
                    <p>After authorization, you'll be redirected back with fresh tokens.</p>
                </div>
            </div>
            
            <div class="mt-12 bg-gray-50 p-6 rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-question-circle mr-2"></i>
                    What happens next?
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                    <li>You'll be redirected to Accurate's OAuth authorization page</li>
                    <li>Login with your Accurate credentials if needed</li>
                    <li>Authorize the application to access your data</li>
                    <li>You'll be redirected back to our callback page</li>
                    <li>Fresh tokens will be automatically saved to config.php</li>
                    <li>The refresh token functionality should work again</li>
                </ol>
            </div>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
