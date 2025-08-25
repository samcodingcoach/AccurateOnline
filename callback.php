<?php
/**
 * OAuth Callback Handler
 * Menangani callback dari Accurate OAuth dengan lebih baik
 */

require_once __DIR__ . '/bootstrap.php';

$code = $_GET['code'] ?? null;
$error = $_GET['error'] ?? null;
$errorDescription = $_GET['error_description'] ?? null;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - OAuth Callback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-sign-in-alt"></i> OAuth Callback
            </h1>
            <a href="index.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>OAuth Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php if ($errorDescription): ?>
                    <div class="mt-2 text-sm">
                        <strong>Description:</strong> <?php echo htmlspecialchars($errorDescription); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($code): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Success:</strong> Authorization code berhasil diterima
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-key"></i> Authorization Code
                </h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="mb-2">
                        <strong>Code:</strong>
                    </div>
                    <code class="block bg-white p-3 rounded border text-sm break-all"><?php echo htmlspecialchars($code); ?></code>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-arrow-right"></i> Langkah Selanjutnya
                </h2>
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-2">1. Get Access Token</h3>
                        <p class="text-blue-700 mb-3">
                            Gunakan authorization code ini untuk mendapatkan access token
                        </p>
                        <a href="get_access_token.php?code=<?php echo urlencode($code); ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-arrow-right mr-2"></i> Get Access Token
                        </a>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-yellow-800 mb-2">2. Manual Process</h3>
                        <p class="text-yellow-700 mb-3">
                            Atau copy authorization code dan update di get_access_token.php
                        </p>
                        <button onclick="copyCode()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            <i class="fas fa-copy mr-2"></i> Copy Code
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-100 border border-gray-300 text-gray-600 px-4 py-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    No authorization code or error received.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle"></i> Informasi
            </h3>
            <div class="text-sm text-blue-700 space-y-1">
                <p><strong>Redirect URI:</strong> <?php echo htmlspecialchars(OAUTH_REDIRECT_URI); ?></p>
                <p><strong>Client ID:</strong> <?php echo htmlspecialchars(OAUTH_CLIENT_ID); ?></p>
                <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
    </div>
    
    <script>
        function copyCode() {
            const code = "<?php echo htmlspecialchars($code); ?>";
            navigator.clipboard.writeText(code).then(function() {
                alert('Authorization code copied to clipboard!');
            });
        }
    </script>
</body>
</html>
