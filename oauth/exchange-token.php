<?php
/**
 * Exchange Authorization Code untuk Access Token
 * Gunakan file ini untuk menukar authorization code dengan access token
 */

require_once __DIR__ . '/../bootstrap.php';

// Authorization code yang didapat dari callback
$authorizationCode = 'tPERYIeV1AZJnUvjK49a';

// Data untuk request token
$tokenData = [
    'grant_type' => 'authorization_code',
    'client_id' => OAUTH_CLIENT_ID,
    'client_secret' => OAUTH_CLIENT_SECRET,
    'code' => $authorizationCode,
    'redirect_uri' => OAUTH_REDIRECT_URI
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Exchange Token</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-exchange-alt"></i> Exchange Authorization Code
            </h1>
            <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Authorization Code:</strong> <?php echo htmlspecialchars($authorizationCode); ?>
            </div>
        </div>

        <?php
        // Proses exchange token
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => ACCURATE_AUTH_HOST . '/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($tokenData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);
        
        if ($httpCode == 200 && isset($result['access_token'])) {
            // Token berhasil didapat
            ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold text-green-800 mb-4">
                    <i class="fas fa-check-circle"></i> Token Exchange Berhasil!
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-green-700">Access Token:</label>
                        <div class="bg-white p-3 rounded border break-all text-sm font-mono">
                            <?php echo htmlspecialchars($result['access_token']); ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-green-700">Refresh Token:</label>
                        <div class="bg-white p-3 rounded border break-all text-sm font-mono">
                            <?php echo htmlspecialchars($result['refresh_token']); ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-green-700">Token Type:</label>
                            <div class="bg-white p-2 rounded border text-sm">
                                <?php echo htmlspecialchars($result['token_type']); ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-green-700">Expires In:</label>
                            <div class="bg-white p-2 rounded border text-sm">
                                <?php echo number_format($result['expires_in']); ?> seconds
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-green-700">Scope:</label>
                            <div class="bg-white p-2 rounded border text-sm">
                                <?php echo htmlspecialchars($result['scope']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($result['user'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-green-700">User Info:</label>
                        <div class="bg-white p-3 rounded border">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div><strong>Name:</strong> <?php echo htmlspecialchars($result['user']['name']); ?></div>
                                <div><strong>Email:</strong> <?php echo htmlspecialchars($result['user']['email']); ?></div>
                                <div><strong>ID:</strong> <?php echo htmlspecialchars($result['user']['id']); ?></div>
                                <div><strong>Nickname:</strong> <?php echo htmlspecialchars($result['user']['nickname']); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-6 bg-blue-50 p-4 rounded">
                    <h3 class="font-semibold text-blue-800 mb-2">
                        <i class="fas fa-info-circle"></i> Langkah Selanjutnya
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                        <li>Copy access token di atas</li>
                        <li>Update file config/config.php dengan token baru</li>
                        <li>Test API dengan token baru</li>
                        <li>Simpan refresh token untuk refresh di masa depan</li>
                    </ol>
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-2">
                    <i class="fas fa-code"></i> Update Config (copy code ini ke config/config.php)
                </h3>
                <div class="bg-white p-3 rounded border">
                    <pre class="text-sm overflow-x-auto"><?php 
echo "define('ACCURATE_ACCESS_TOKEN', '" . $result['access_token'] . "');";
                    ?></pre>
                </div>
            </div>
            
            <?php
        } else {
            // Token exchange gagal
            ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold text-red-800 mb-4">
                    <i class="fas fa-times-circle"></i> Token Exchange Gagal
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-red-700">HTTP Code:</label>
                        <div class="bg-white p-2 rounded border text-sm">
                            <?php echo $httpCode; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-red-700">Response:</label>
                        <div class="bg-white p-3 rounded border">
                            <pre class="text-sm overflow-x-auto"><?php echo htmlspecialchars($response); ?></pre>
                        </div>
                    </div>
                    
                    <?php if ($result): ?>
                    <div>
                        <label class="block text-sm font-medium text-red-700">Parsed Response:</label>
                        <div class="bg-white p-3 rounded border">
                            <pre class="text-sm overflow-x-auto"><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-6 bg-yellow-50 p-4 rounded">
                    <h3 class="font-semibold text-yellow-800 mb-2">
                        <i class="fas fa-exclamation-triangle"></i> Kemungkinan Penyebab
                    </h3>
                    <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                        <li>Authorization code sudah expired (hanya berlaku beberapa menit)</li>
                        <li>Authorization code sudah pernah digunakan</li>
                        <li>Client ID atau Client Secret tidak valid</li>
                        <li>Redirect URI tidak sesuai</li>
                    </ul>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-gray-700 mb-2">
                <i class="fas fa-info-circle"></i> Request Data
            </h3>
            <div class="bg-white p-3 rounded border">
                <pre class="text-sm overflow-x-auto"><?php echo json_encode($tokenData, JSON_PRETTY_PRINT); ?></pre>
            </div>
        </div>
    </div>
</body>
</html>
