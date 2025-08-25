<?php
/**
 * Get Access Token dari Accurate OAuth
 * File ini sudah direfactor untuk menggunakan struktur baru
 * Auto update config dan redirect ke dashboard
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/classes/AutoOAuthHandler.php';

// Authorization Code yang diterima dari URL callback
$code = $_GET['code'] ?? null;

if (empty($code)) {
    // Redirect to authorization if no code
    header('Location: oauth/authorize.php?error=no_code');
    exit;
}

// Inisialisasi API class dan Auto OAuth Handler
$api = new AccurateAPI();
$autoHandler = new AutoOAuthHandler();

// Get access token menggunakan authorization code
$result = $api->getAccessToken($code);

if ($result['success']) {
    // Simpan token ke file untuk penggunaan selanjutnya
    $tokenData = $result['data'];
    file_put_contents(__DIR__ . '/apitoken.txt', json_encode($tokenData, JSON_PRETTY_PRINT));
    
    // Auto update konfigurasi dengan token baru
    $autoResult = $autoHandler->updateTokenAndConfig($tokenData);
    
    if ($autoResult['success']) {
        // Test scopes yang tersedia dengan konfigurasi baru
        $scopeTest = $autoHandler->testAllScopes($tokenData['access_token']);
        
        // Get actual authorized scopes from token data or config
        $authorizedScopes = [];
        if (isset($tokenData['scope'])) {
            $authorizedScopes = explode(' ', trim($tokenData['scope']));
        } elseif (defined('ACCURATE_TOKEN_SCOPE')) {
            $authorizedScopes = explode(' ', trim(ACCURATE_TOKEN_SCOPE));
        }
        
        $workingScopes = count(array_filter($scopeTest['scopes']));
        $totalAuthorized = count($authorizedScopes);
        
        // Set session untuk notifikasi sukses
        session_start();
        $_SESSION['oauth_success'] = [
            'message' => 'Access token dan konfigurasi berhasil diperbarui!',
            'token_updated' => true,
            'config_updated' => true,
            'scopes_available' => $workingScopes,
            'scopes_total' => $totalAuthorized,
            'scopes_authorized' => $authorizedScopes,
            'scopes_working' => array_keys(array_filter($scopeTest['scopes'])),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Redirect ke dashboard dengan notifikasi sukses
        header('Location: index.php?token_updated=1');
        exit;
        
    } else {
        // Jika auto config gagal, set warning dan redirect
        session_start();
        $_SESSION['oauth_warning'] = [
            'message' => 'Token berhasil didapat tapi auto config gagal: ' . $autoResult['message'],
            'token_updated' => true,
            'config_updated' => false
        ];
        
        header('Location: index.php?token_updated=1&config_error=1');
        exit;
    }
} else {
    // Error getting token, redirect dengan error
    session_start();
    $_SESSION['oauth_error'] = [
        'message' => 'Gagal mendapatkan access token: ' . ($result['error'] ?? 'Unknown error'),
        'code' => $code,
        'error_details' => $result['error'] ?? null
    ];
    
    header('Location: oauth/authorize.php?error=token_failed');
    exit;
}
?>
