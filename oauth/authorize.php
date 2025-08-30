<?php
/**
 * Get OAuth Authorization URL dengan scope yang lengkap
 * Gunakan file ini untuk mendapatkan authorization URL dengan scope yang diperlukan
 */

require_once __DIR__ . '/../bootstrap.php';

// Handle POST actions for scope management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    if ($action === 'add_scope') {
        $newScope = trim($_POST['new_scope'] ?? '');
        if (!empty($newScope)) {
            // Get current scopes from config
            $currentScopeString = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
            $currentScopes = !empty($currentScopeString) ? explode(' ', $currentScopeString) : [];
            
            // Add new scope if not already exists
            if (!in_array($newScope, $currentScopes)) {
                $currentScopes[] = $newScope;
                
                // Update config file with new scope
                $configPath = __DIR__ . '/../config/config.php';
                $configContent = file_get_contents($configPath);
                $newScopeString = implode(' ', $currentScopes);
                
                if (strpos($configContent, 'ACCURATE_TOKEN_SCOPE') !== false) {
                    // Update existing
                    $configContent = preg_replace(
                        "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/",
                        "define('ACCURATE_TOKEN_SCOPE', '{$newScopeString}');",
                        $configContent
                    );
                } else {
                    // Add new line after ACCURATE_REFRESH_TOKEN
                    $newLine = "define('ACCURATE_TOKEN_SCOPE', '{$newScopeString}');\n";
                    $configContent = str_replace(
                        "define('ACCURATE_REFRESH_TOKEN',", 
                        $newLine . "define('ACCURATE_REFRESH_TOKEN',", 
                        $configContent
                    );
                }
                
                file_put_contents($configPath, $configContent);
                
                // Redirect to refresh the page with updated config
                header('Location: authorize.php?scope_added=' . urlencode($newScope));
                exit;
            } else {
                // Scope already exists
                header('Location: authorize.php?error=scope_exists&scope=' . urlencode($newScope));
                exit;
            }
        }
    }
    
    if ($action === 'remove_scope') {
        $scopeToRemove = trim($_POST['scope_to_remove'] ?? '');
        if (!empty($scopeToRemove)) {
            // Get current scopes from config
            $currentScopeString = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
            $currentScopes = !empty($currentScopeString) ? explode(' ', $currentScopeString) : [];
            
            // Remove scope
            $currentScopes = array_filter($currentScopes, function($scope) use ($scopeToRemove) {
                return $scope !== $scopeToRemove;
            });
            
            // Update config file
            $configPath = __DIR__ . '/../config/config.php';
            $configContent = file_get_contents($configPath);
            $newScopeString = implode(' ', $currentScopes);
            
            $configContent = preg_replace(
                "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/",
                "define('ACCURATE_TOKEN_SCOPE', '{$newScopeString}');",
                $configContent
            );
            
            file_put_contents($configPath, $configContent);
            
            // Redirect to refresh the page
            header('Location: authorize.php?scope_removed=' . urlencode($scopeToRemove));
            exit;
        }
    }
}

// Scope yang officially supported di Accurate Online API (berdasarkan dokumentasi resmi)
$availableScopes = [
    // Master Data Views
    'item_view' => 'Akses data barang/produk',
    'branch_view' => 'Akses data cabang/lokasi', 
    'vendor_view' => 'Akses data vendor/supplier',
    'warehouse_view' => 'Akses data gudang',
    'customer_view' => 'Akses data customer/pelanggan',
    'coa_view' => 'Akses chart of accounts',
    'department_view' => 'Akses data departemen',
    'project_view' => 'Akses data project',
    'employee_view' => 'Akses data karyawan',
    'unit_view' => 'Akses data satuan',
    'currency_view' => 'Akses data mata uang',
    'tax_view' => 'Akses data pajak',
    
    // Sales Transaction Views
    'sales_invoice_view' => 'Akses faktur penjualan',
    'sales_order_view' => 'Akses sales order',
    'sales_quotation_view' => 'Akses quotation penjualan',
    'delivery_order_view' => 'Akses surat jalan',
    
    // Purchase Transaction Views
    'purchase_invoice_view' => 'Akses faktur pembelian',
    'purchase_order_view' => 'Akses purchase order',
    'purchase_receive_view' => 'Akses penerimaan barang',
    
    // Inventory Management
    'item_adjustment_view' => 'Akses adjustment barang',
    'item_transfer_view' => 'Akses transfer barang',
    'item_opname_view' => 'Akses stock opname',
    
    // Financial Views
    'journal_view' => 'Akses jurnal umum',
    'cash_bank_view' => 'Akses kas & bank',
    'fixed_asset_view' => 'Akses aset tetap',
    
    // Report Views
    'report_view' => 'Akses semua laporan',
    'dashboard_view' => 'Akses dashboard'
];

// Scope default yang akan digunakan (otomatis dari approved-scope API)
try {
    // Coba ambil dari API approved-scope yang aktual
    require_once __DIR__ . '/../classes/AccurateAPI.php';
    $api = new AccurateAPI();
    $approvedScopesResult = $api->getApprovedScopes();
    
    if (isset($approvedScopesResult['success']) && $approvedScopesResult['success'] && !empty($approvedScopesResult['data'])) {
        // Parse data response dengan benar
        $apiData = $approvedScopesResult['data'];
        
        // Jika data adalah array string langsung
        if (is_array($apiData) && !is_assoc_array($apiData)) {
            $defaultScopes = $apiData;
        }
        // Jika data ada di field 'd' seperti endpoint lainnya
        elseif (is_array($apiData) && isset($apiData['d']) && is_array($apiData['d'])) {
            $defaultScopes = $apiData['d'];
        }
        // Jika data adalah string separated by space
        elseif (is_string($apiData)) {
            $defaultScopes = explode(' ', trim($apiData));
        }
        // Fallback jika format tidak dikenali
        else {
            throw new Exception('Unknown API data format');
        }
        
        // Pastikan $defaultScopes adalah array yang valid
        if (!is_array($defaultScopes) || empty($defaultScopes)) {
            throw new Exception('Invalid scope data from API');
        }
        
    } else {
        // Fallback ke scope yang sudah diketahui bekerja
        $defaultScopes = [
            'item_view',
            'branch_view', 
            'item_category_view',
            'vendor_view',
            'warehouse_view'
        ];
    }
} catch (Exception $e) {
    // Jika ada error, gunakan scope yang sudah diketahui bekerja
    $defaultScopes = [
        'item_view',
        'branch_view', 
        'item_category_view',
        'vendor_view',
        'warehouse_view'
    ];
}

// Helper function untuk detect associative array
function is_assoc_array($array) {
    if (!is_array($array)) return false;
    return array_keys($array) !== range(0, count($array) - 1);
}

// Gunakan scope dari config yang sudah diupdate, bukan default hardcoded
$currentScopeString = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
if (!empty($currentScopeString)) {
    $currentScopes = explode(' ', trim($currentScopeString));
} else {
    // Fallback ke default jika config kosong
    $currentScopes = $defaultScopes;
}

// Pastikan tidak ada elemen yang bukan string
$currentScopes = array_filter($currentScopes, function($scope) {
    return is_string($scope) && !empty(trim($scope));
});

// Reindex array untuk memastikan tidak ada gap
$currentScopes = array_values($currentScopes);

// Auto-detect current base URL untuk ngrok dan tunneling services lainnya
// Check multiple sources for HTTPS detection
$isHttps = false;

// Standard HTTPS detection
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isHttps = true;
}

// Check proxy headers (common with tunneling services)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $isHttps = true;
}

// Check if port is 443 (HTTPS port)
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
    $isHttps = true;
}

$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Force HTTPS untuk tunneling services (ngrok, pinggy, dll)
if (strpos($host, '.ngrok-free.app') !== false || 
    strpos($host, '.ngrok.io') !== false ||
    strpos($host, '.pinggy.link') !== false ||
    strpos($host, '.loca.lt') !== false) {
    $protocol = 'https';
}

$currentBaseUrl = $protocol . '://' . $host;

// Build redirect URI dengan URL yang aktual
$redirectUri = $currentBaseUrl . '/nuansa/callback.php';

// Check jika ada perbedaan antara config dan current URL
$urlMismatch = (OAUTH_REDIRECT_URI !== $redirectUri);

// Build authorization URL dengan redirect URI yang benar
$authUrl = ACCURATE_AUTH_HOST . '/oauth/authorize?' . http_build_query([
    'client_id' => OAUTH_CLIENT_ID,
    'redirect_uri' => $redirectUri, // Gunakan URL yang auto-detected
    'response_type' => 'code',
    'scope' => implode(' ', $currentScopes)
]);

// Auto-update config jika URL berbeda
if ($urlMismatch && isset($_GET['update_config']) && $_GET['update_config'] === '1') {
    $configPath = __DIR__ . '/../config/config.php';
    $configContent = file_get_contents($configPath);
    
    // Update OAUTH_REDIRECT_URI dengan URL yang benar
    $configContent = preg_replace(
        "/define\('OAUTH_REDIRECT_URI', '[^']*'\);/",
        "define('OAUTH_REDIRECT_URI', '{$redirectUri}');",
        $configContent
    );
    
    file_put_contents($configPath, $configContent);
    
    // Redirect tanpa parameter untuk refresh
    header('Location: authorize.php?config_updated=1');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - OAuth Authorization</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        
        <!-- Notifikasi Scope Management -->
        <?php if (isset($_GET['scope_added'])): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">Scope "<?php echo htmlspecialchars($_GET['scope_added']); ?>" berhasil ditambahkan.</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['scope_removed'])): ?>
            <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Info!</strong>
                <span class="block sm:inline">Scope "<?php echo htmlspecialchars($_GET['scope_removed']); ?>" berhasil dihapus.</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-blue-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'scope_exists'): ?>
            <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Peringatan!</strong>
                <span class="block sm:inline">Scope "<?php echo htmlspecialchars($_GET['scope'] ?? ''); ?>" sudah ada dalam daftar.</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-yellow-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>
        
        <!-- URL Mismatch Notification -->
        <?php if ($urlMismatch): ?>
            <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    </div>
                    <div class="flex-1">
                        <strong class="font-bold">URL Mismatch Detected!</strong>
                        <div class="text-sm mt-1">
                            <p><strong>Config URL:</strong> <code class="bg-yellow-200 px-1 rounded"><?php echo OAUTH_REDIRECT_URI; ?></code></p>
                            <p><strong>Current URL:</strong> <code class="bg-green-200 px-1 rounded"><?php echo $redirectUri; ?></code></p>
                            <div class="mt-3 flex gap-2">
                                <a href="?update_config=1" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-sync mr-1"></i>Update Config
                                </a>
                                <span class="text-xs text-yellow-600 self-center">Authorization URL sudah otomatis menggunakan URL yang benar</span>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-yellow-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['config_updated'])): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">Config berhasil diupdate dengan URL yang benar!</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-key"></i> OAuth Authorization
                </h1>
                <a href="../index.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('info')" id="info-tab" class="tab-button py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Scope
                    </button>
                    <button onclick="showTab('troubleshooting')" id="troubleshooting-tab" class="tab-button py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                        <i class="fas fa-question-circle mr-2"></i>Troubleshooting
                    </button>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="mt-4">
                <!-- Info Tab -->
                <div id="info-content" class="tab-content">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-blue-800 mb-1">Informasi Scope</h3>
                                <div class="text-sm text-blue-700 space-y-1">
                                    <p><strong>Scope Wajib:</strong> Scope berwarna biru adalah scope minimal yang diperlukan aplikasi</p>
                                    <p><strong>Scope Custom:</strong> Scope berwarna hijau adalah scope tambahan yang bisa dihapus</p>
                                    <p><strong>Quick Add:</strong> Gunakan tombol quick add untuk menambah scope umum dengan cepat</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Troubleshooting Tab -->
                <div id="troubleshooting-content" class="tab-content hidden">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-red-800 mb-2">Troubleshooting</h3>
                                <div class="text-sm text-red-700 space-y-2">
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-times-circle text-red-500 mt-0.5"></i>
                                        <div>
                                            <strong>Error 403 (insufficient_scope):</strong> Scope tidak cukup, gunakan authorization URL ini
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-clock text-red-500 mt-0.5"></i>
                                        <div>
                                            <strong>Error 401 (unauthorized):</strong> Access token expired, perlu refresh token
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                                        <div>
                                            <strong>Error 400 (invalid_request):</strong> Parameter tidak valid
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-cog"></i> Manajemen Scope OAuth
            </h2>
            
            <!-- Status Summary -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>Scope Aktif
                        </h3>
                        <p class="text-sm text-green-600">
                            Total <?php echo count($currentScopes); ?> scope akan diminta dalam authorization
                        </p>
                        <p class="text-xs text-green-500 mt-1">
                            <?php if (isset($approvedScopesResult) && isset($approvedScopesResult['success']) && $approvedScopesResult['success']): ?>
                                <i class="fas fa-sync mr-1"></i>Diambil otomatis dari approved-scope API
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle mr-1"></i>Menggunakan fallback scope (API tidak tersedia)
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-green-600"><?php echo count($currentScopes); ?></div>
                        <div class="text-xs text-green-500">AKTIF</div>
                    </div>
                </div>
            </div>
            
            <!-- Add New Scope Section -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-plus-circle"></i> Tambah Scope Baru
                </h3>
                
                <!-- Manual Input -->
                <form method="POST" class="flex gap-3 mb-4">
                    <input type="hidden" name="action" value="add_scope">
                    <div class="flex-1">
                        <input type="text" 
                               name="new_scope" 
                               placeholder="Masukkan nama scope (contoh: warehouse_view)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                    </div>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        <i class="fas fa-plus mr-2"></i>Tambah
                    </button>
                </form>
                
                <!-- Quick Add Buttons -->
                <div class="space-y-2">
                    <div class="text-sm text-gray-600 font-medium">
                        <i class="fas fa-bolt mr-1"></i> Quick Add - Scope Umum:
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $quickScopes = ['customer_view', 'coa_view', 'department_view', 'employee_view', 'unit_view', 'currency_view', 'tax_view', 'sales_invoice_view', 'purchase_invoice_view'];
                        foreach ($quickScopes as $quickScope):
                            if (!in_array($quickScope, $currentScopes)):
                        ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="add_scope">
                                <input type="hidden" name="new_scope" value="<?php echo $quickScope; ?>">
                                <button type="submit" 
                                        class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-full hover:bg-gray-300 transition-colors border border-gray-300">
                                    <i class="fas fa-plus text-xs mr-1"></i><?php echo $quickScope; ?>
                                </button>
                            </form>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Active Scopes Display -->
            <div class="mb-4">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-heart"></i> Scope yang Akan Diminta
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php 
                    // Tambahkan mapping label yang user-friendly untuk semua kemungkinan scope
                    $scopeLabels = [
                        'item_view' => 'Item View',
                        'branch_view' => 'Branch View',
                        'vendor_view' => 'Vendor View', 
                        'warehouse_view' => 'Warehouse View',
                        'customer_view' => 'Customer View',
                        'coa_view' => 'Chart of Accounts',
                        'item_category_view' => 'Item Category View',
                        'department_view' => 'Department View',
                        'employee_view' => 'Employee View',
                        'unit_view' => 'Unit View',
                        'currency_view' => 'Currency View',
                        'tax_view' => 'Tax View',
                        'sales_invoice_view' => 'Sales Invoice View',
                        'purchase_invoice_view' => 'Purchase Invoice View',
                        'journal_view' => 'Journal View',
                        'report_view' => 'Report View'
                    ];
                    
                    // Safety check untuk memastikan $currentScopes adalah array yang valid
                    if (is_array($currentScopes) && !empty($currentScopes)):
                        foreach ($currentScopes as $scope): 
                            // Skip jika scope bukan string
                            if (!is_string($scope)) continue;
                            
                            $label = isset($scopeLabels[$scope]) ? $scopeLabels[$scope] : ucwords(str_replace('_', ' ', $scope));
                            $isRequired = in_array($scope, $defaultScopes);
                    ?>
                        <div class="relative p-4 rounded-lg bg-blue-500 text-white">
                            <div class="absolute top-2 right-2">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="font-medium mb-1"><?php echo htmlspecialchars($label); ?></div>
                            <div class="text-xs opacity-75">
                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                Required
                            </div>
                            <?php if (!$isRequired): ?>
                                <form method="POST" class="absolute top-1 left-1">
                                    <input type="hidden" name="action" value="remove_scope">
                                    <input type="hidden" name="scope_to_remove" value="<?php echo htmlspecialchars($scope); ?>">
                                    <button type="submit" 
                                            class="w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full text-xs flex items-center justify-center"
                                            onclick="return confirm('Hapus scope <?php echo htmlspecialchars($scope); ?>?')"
                                            title="Hapus scope ini">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-500 p-8">
                            <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                            <p>No scopes found or scope data is invalid</p>
                            <p class="text-sm mt-2">Please check your API token</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-route"></i> Cara Penggunaan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Step 1 -->
                <div class="relative bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="absolute -top-3 -left-3 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <div class="text-blue-600 text-2xl mb-2">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <h3 class="font-semibold text-blue-800 mb-1">Klik Authorize</h3>
                    <p class="text-sm text-blue-600">Klik tombol biru di bawah untuk memulai</p>
                </div>

                <!-- Step 2 -->
                <div class="relative bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 text-center">
                    <div class="absolute -top-3 -left-3 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <div class="text-green-600 text-2xl mb-2">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3 class="font-semibold text-green-800 mb-1">Login & Izinkan</h3>
                    <p class="text-sm text-green-600">Login ke Accurate dan berikan izin</p>
                </div>

                <!-- Step 3 -->
                <div class="relative bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="absolute -top-3 -left-3 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                    <div class="text-purple-600 text-2xl mb-2">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="font-semibold text-purple-800 mb-1">Copy Code</h3>
                    <p class="text-sm text-purple-600">Salin authorization code dari URL</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center">
                <i class="fas fa-link mr-2"></i>Authorization URL
            </h3>
            <blockquote class="p-4 border-s-4 border-blue-400 bg-blue-50 rounded-r-lg">
                <p class="text-sm font-mono leading-relaxed text-blue-800 break-all">
                    <?php echo htmlspecialchars($authUrl); ?>
                </p>
            </blockquote>
            <div class="mt-2 text-xs text-gray-500 flex items-center">
                <i class="fas fa-info-circle mr-1"></i>
                URL ini akan digunakan untuk proses OAuth authorization
            </div>
        </div>

        <div class="text-center mb-6">
            <a href="<?php echo htmlspecialchars($authUrl); ?>" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Authorize dengan Scope Lengkap
            </a>
        </div>
        
        <!-- Debug Information -->
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-bug"></i> Debug Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">URL Configuration:</h4>
                    <div class="text-sm space-y-1">
                        <p><strong>Config Redirect URI:</strong> 
                            <code class="<?php echo $urlMismatch ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-1 rounded text-xs"><?php echo OAUTH_REDIRECT_URI; ?></code>
                        </p>
                        <p><strong>Current Redirect URI:</strong> 
                            <code class="bg-green-100 text-green-800 px-1 rounded text-xs"><?php echo $redirectUri; ?></code>
                        </p>
                        <p><strong>Current Base URL:</strong> 
                            <code class="bg-blue-100 text-blue-800 px-1 rounded text-xs"><?php echo $currentBaseUrl; ?></code>
                        </p>
                        <?php if ($urlMismatch): ?>
                            <p class="text-xs text-yellow-600 mt-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Authorization URL menggunakan Current URL yang benar
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-green-600 mt-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                URL configuration is correct
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Scope Source:</h4>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Scope Source:</h4>
                    <?php if (isset($approvedScopesResult)): ?>
                        <div class="text-sm">
                            <p><strong>API Call Status:</strong> 
                                <span class="<?php echo (isset($approvedScopesResult['success']) && $approvedScopesResult['success']) ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo (isset($approvedScopesResult['success']) && $approvedScopesResult['success']) ? 'SUCCESS' : 'FAILED'; ?>
                                </span>
                            </p>
                            <p><strong>HTTP Code:</strong> <?php echo $approvedScopesResult['http_code'] ?? 'N/A'; ?></p>
                            <?php if (isset($approvedScopesResult['success']) && !$approvedScopesResult['success'] && isset($approvedScopesResult['error']) && $approvedScopesResult['error']): ?>
                                <p><strong>Error:</strong> <span class="text-red-600"><?php echo htmlspecialchars($approvedScopesResult['error']); ?></span></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600">No API call attempted (fallback mode)</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4">
                <h4 class="font-medium text-gray-700 mb-2">Current Scopes:</h4>
                <div class="text-sm">
                    <code class="bg-gray-100 p-2 rounded text-xs block">
                        <?php echo implode(', ', $currentScopes); ?>
                    </code>
                    <p class="text-gray-600 mt-1">Total: <?php echo count($currentScopes); ?> scopes</p>
                </div>
            </div>
            
            <?php if (isset($approvedScopesResult) && !empty($approvedScopesResult['raw_response'])): ?>
                <div class="mt-4">
                    <h4 class="font-medium text-gray-700 mb-2">API Raw Response:</h4>
                    <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-40">
<?php echo htmlspecialchars($approvedScopesResult['raw_response']); ?>
                    </pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tab buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active state to selected tab button
            const activeButton = document.getElementById(tabName + '-tab');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            activeButton.classList.add('border-blue-500', 'text-blue-600');
        }
        
        // Initialize first tab as active
        document.addEventListener('DOMContentLoaded', function() {
            showTab('info');
        });
    </script>
</body>
</html>
