<?php
/**
 * Dashboard utama untuk aplikasi Accurate API
 * File ini menggunakan struktur baru yang terorganisir
 */

require_once __DIR__ . '/bootstrap.php';

// Start session untuk notifikasi
session_start();

// Auto-detect current base URL untuk tunneling services
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

// Inisialisasi API class
$api = new AccurateAPI();

// Get session info
$sessionInfo = $api->getSessionInfo();

// Get company info langsung di PHP
$companyInfo = [
    'alias' => 'API PANEL',
    'licenseEnd' => 'Loading...',
    'logoUrl' => null
];

try {
    $dbResult = $api->getDatabaseList();
    if ($dbResult['success'] && isset($dbResult['data']['d'])) {
        $databases = $dbResult['data']['d'];
        $selectedDb = null;
        
        // Cari database yang sesuai dengan config ID
        foreach ($databases as $db) {
            if ($db['id'] == ACCURATE_DATABASE_ID) {
                $selectedDb = $db;
                break;
            }
        }
        
        // Jika tidak ada yang match, cari yang tidak expired
        if (!$selectedDb) {
            foreach ($databases as $db) {
                if (!$db['expired']) {
                    $selectedDb = $db;
                    break;
                }
            }
        }
        
        // Jika masih tidak ada, ambil yang terakhir
        if (!$selectedDb && !empty($databases)) {
            $selectedDb = end($databases);
        }
        
        if ($selectedDb) {
            $companyInfo['alias'] = $selectedDb['alias'] ?? 'API PANEL';
            $companyInfo['licenseEnd'] = $selectedDb['licenseEnd'] ?? 'Unknown';
            $companyInfo['logoUrl'] = $selectedDb['logoUrl'] ?? null;
        }
    }
} catch (Exception $e) {
    // Jika ada error, gunakan default values
    error_log("Error getting company info: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        
        <!-- Notifikasi OAuth Success/Warning -->
        <?php if (isset($_SESSION['oauth_success'])): ?>
            <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full">
                <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg relative">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute top-2 right-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <h4 class="font-semibold">Token Updated!</h4>
                            <p class="text-sm"><?php echo $_SESSION['oauth_success']['message']; ?></p>
                            <p class="text-xs mt-1 opacity-90">
                                <?php 
                                if (isset($_SESSION['oauth_success']['scopes_total'])) {
                                    echo $_SESSION['oauth_success']['scopes_available'] . '/' . $_SESSION['oauth_success']['scopes_total'] . ' scopes working';
                                } else {
                                    echo $_SESSION['oauth_success']['scopes_available'] . ' scopes available';
                                }
                                echo ' | ' . $_SESSION['oauth_success']['timestamp']; 
                                ?>
                            </p>
                            <?php if (isset($_SESSION['oauth_success']['scopes_authorized'])): ?>
                                <p class="text-xs mt-1 opacity-80">
                                    Authorized: <?php echo implode(', ', $_SESSION['oauth_success']['scopes_authorized']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['oauth_success']['scopes_working']) && isset($_SESSION['oauth_success']['scopes_authorized'])): ?>
                                <?php 
                                $notWorking = array_diff($_SESSION['oauth_success']['scopes_authorized'], $_SESSION['oauth_success']['scopes_working']);
                                if (!empty($notWorking)): 
                                ?>
                                    <p class="text-xs mt-1 opacity-80 text-yellow-200">
                                        Not working: <?php echo implode(', ', $notWorking); ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['oauth_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['oauth_warning'])): ?>
            <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full">
                <div class="bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg relative">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute top-2 right-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                        <div>
                            <h4 class="font-semibold">Partial Success</h4>
                            <p class="text-sm"><?php echo $_SESSION['oauth_warning']['message']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['oauth_warning']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['oauth_error'])): ?>
            <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full">
                <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg relative">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute top-2 right-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="flex items-center">
                        <i class="fas fa-times-circle mr-3 text-xl"></i>
                        <div>
                            <h4 class="font-semibold">OAuth Error</h4>
                            <p class="text-sm"><?php echo $_SESSION['oauth_error']['message']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['oauth_error']); ?>
        <?php endif; ?>

        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex items-center mr-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200 mr-3">
                                <?php if ($companyInfo['logoUrl']): ?>
                                <img id="companyLogo" src="<?php echo htmlspecialchars($companyInfo['logoUrl']); ?>" alt="Company Logo" class="w-full h-full object-cover" 
                                     onerror="this.style.display='none'; document.getElementById('defaultLogo').style.display='flex';">
                                <div id="defaultLogo" class="w-full h-full bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center" style="display: none;">
                                <?php else: ?>
                                <div id="defaultLogo" class="w-full h-full bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center">
                                <?php endif; ?>
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <!-- Title and Info -->
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">
                                <span id="companyAlias"><?php echo htmlspecialchars($companyInfo['alias']); ?></span>
                                <span class="text-sm font-normal text-gray-600 ml-1">API PANEL</span>
                            </h1>
                            <p class="text-sm text-gray-600">
                                License end: <span id="licenseEnd"><?php echo htmlspecialchars($companyInfo['licenseEnd']); ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Token Expiration Countdown -->
                        <div class="flex items-center">
                            <i class="fas fa-hourglass-half text-red-500 mr-2"></i>
                            <span class="text-sm text-gray-700 font-medium">Token Date Expiration:</span>
                            <div id="tokenCountdown" class="ml-2 px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-bold">
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Session Info -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-6 border border-gray-100">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        Session Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Host Info -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-server text-green-500 mr-2"></i>
                                <dt class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Host</dt>
                            </div>
                            <dd class="text-sm text-gray-900 font-medium break-all"><?php echo htmlspecialchars($sessionInfo['host']); ?></dd>
                        </div>
                        
                        <!-- Database ID -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-database text-blue-500 mr-2"></i>
                                <dt class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Database ID</dt>
                            </div>
                            <dd class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($sessionInfo['database_id']); ?></dd>
                        </div>
                        
                        <!-- Session ID -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-key text-purple-500 mr-2"></i>
                                <dt class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Session ID</dt>
                            </div>
                            <div class="flex items-center">
                                <input type="password" id="sessionId" value="<?php echo htmlspecialchars($sessionInfo['session_id']); ?>" 
                                       class="flex-1 text-xs text-gray-900 font-mono bg-white px-2 py-1 rounded border mr-2 focus:outline-none" readonly>
                                <button id="toggleSessionId" class="text-gray-500 hover:text-gray-700 mr-1" title="Show/Hide">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button id="copySessionId" class="text-blue-500 hover:text-blue-700" title="Copy">
                                    <i class="fas fa-copy text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Access Token -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-shield-alt text-red-500 mr-2"></i>
                                <dt class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Access Token</dt>
                            </div>
                            <div class="flex items-center">
                                <input type="password" id="accessToken" value="<?php echo htmlspecialchars($sessionInfo['access_token']); ?>" 
                                       class="flex-1 text-xs text-gray-900 font-mono bg-white px-2 py-1 rounded border mr-2 focus:outline-none" readonly>
                                <button id="toggleAccessToken" class="text-gray-500 hover:text-gray-700 mr-1" title="Show/Hide">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button id="copyAccessToken" class="text-blue-500 hover:text-blue-700" title="Copy">
                                    <i class="fas fa-copy text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 1. Branch Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-building text-orange-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Branch Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Cabang
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="branch/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                    <i class="fas fa-building mr-1"></i> List Branch
                                </a>
                                <button id="openBranchApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Employee Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Employee Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Karyawan
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="employee/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-users mr-1"></i> List Employee
                                </a>
                                <button id="openEmployeeApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Shipment Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shipping-fast text-orange-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Shipment Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Pengiriman
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="shipment/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                    <i class="fas fa-shipping-fast mr-1"></i> List Shipment
                                </a>
                                <button id="openShipmentApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Item Category Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-tags text-purple-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Item Category Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Kategori Item
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="itemcategory/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    <i class="fas fa-tags mr-1"></i> List Categories
                                </a>
                                <button id="openCategoryApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Fixed Asset Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-desktop text-teal-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Fixed Asset Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Aset Tetap
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="fixedasset/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700">
                                    <i class="fas fa-desktop mr-1"></i> List Assets
                                </a>
                                <button id="openFixedAssetApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Vendor & Brand -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-truck text-indigo-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Vendor & Brand
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Vendor
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="vendor/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <i class="fas fa-truck mr-1"></i> Vendors
                                </a>
                                <button id="openVendorApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. Warehouse Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-warehouse text-orange-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Warehouse Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Gudang
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="warehouse/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                    <i class="fas fa-warehouse mr-1"></i> List Warehouse
                                </a>
                                <button id="openWarehouseApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. Items Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Items Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Barang
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="item/listv2.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-list mr-1"></i> List Barang
                                </a>
                                <button id="openItemApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 9. Purchase Order Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shopping-basket text-teal-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Purchase Order Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Pembelian
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="purchaseorder/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700">
                                    <i class="fas fa-shopping-basket mr-1"></i> List Purchase Order
                                </a>
                                <button id="openPurchaseOrderApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 10. Invoice PO Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar text-emerald-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Invoice PO Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Faktur PO
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="purchaseinvoice/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> List Purchase Invoice
                                </a>
                                <button id="openPurchaseInvoiceApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 11. Customer Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-purple-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Customer Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Customer
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="customer/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    <i class="fas fa-users mr-1"></i> List Customer
                                </a>
                                <button id="openCustomerApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 12. Price Category Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-tags text-rose-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Price Category Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Kategori Harga
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="price/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700">
                                    <i class="fas fa-tags mr-1"></i> List Price Categories
                                </a>
                                <button id="openPriceCategoryApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 13. Sales Order Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shopping-cart text-cyan-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Sales Order Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Sales Order
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="salesorder/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700">
                                    <i class="fas fa-shopping-cart mr-1"></i> List Sales Order
                                </a>
                                <button id="openSalesOrderApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 14. Sales Invoice Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-invoice text-amber-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Sales Invoice Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Invoice
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="salesinvoice/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700">
                                    <i class="fas fa-file-invoice mr-1"></i> List Sales Invoice
                                </a>
                                <button id="openSalesInvoiceApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 15. Sales Receipt Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-receipt text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Sales Receipt Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Penerimaan Penjualan
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="salesreceipt/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-receipt mr-1"></i> List Sales Receipt
                                </a>
                                <button id="openSalesReceiptApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 16. Item Transfer Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exchange-alt text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Item Transfer Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Item Transfer
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="itemtransfer/index.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-exchange-alt mr-1"></i> List Item Transfer
                                </a>
                                <button id="openItemTransferApiModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-code mr-1"></i> API JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 17. OAuth Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-key text-red-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        OAuth Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Token & Auth
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <a href="get_token_page.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    <i class="fas fa-key mr-1"></i> Get Token
                                </a>
                                <a href="oauth/token-status.php" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-shield-alt mr-1"></i> Token Status
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 17. Database Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Database Management
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Kelola Database
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex flex-wrap gap-2">
                                <button id="openDbListModal" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-list mr-1"></i> List DB
                                </button>
                                <button id="openDbSelectModal" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-mouse-pointer mr-1"></i> Select DB
                                </button>
                                <button id="openLatestDb" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    <i class="fas fa-rocket mr-1"></i> Auto Open Latest
                                </button>
                                <button id="openDbOpenModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-folder-open mr-1"></i> Open DB
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                

                <!-- 18. API Status Check -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-heartbeat text-purple-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        API Status Check
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        Health Check
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex space-x-2">
                                <button id="checkAllEndpoints" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    <i class="fas fa-heartbeat mr-1"></i> Check All
                                </button>
                                <button id="openStatusModal" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-chart-line mr-1"></i> Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal API Status Check -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center">
                        <i class="fas fa-heartbeat text-purple-600 mr-3 text-lg"></i>
                        <h3 class="text-xl font-semibold text-gray-900">API Status Check</h3>
                    </div>
                    <button id="closeStatusModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-y-auto">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600" id="healthyCount">-</div>
                            <div class="text-sm text-green-600">Healthy</div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-600" id="errorCount">-</div>
                            <div class="text-sm text-red-600">Error</div>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-600" id="slowCount">-</div>
                            <div class="text-sm text-yellow-600">Slow (>2s)</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600" id="totalCount">-</div>
                            <div class="text-sm text-blue-600">Total</div>
                        </div>
                    </div>
                    
                    <!-- Endpoints Status Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HTTP Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                </tr>
                            </thead>
                            <tbody id="statusTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Table content will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="statusLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mb-4"></div>
                        <p class="text-gray-600">Checking all endpoints...</p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-3 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Last checked: <span id="lastChecked">Never</span>
                    </div>
                    <button id="refreshStatus" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal JSON API -->
    <div id="jsonModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center">
                        <i class="fas fa-code text-blue-600 mr-3 text-lg"></i>
                        <h3 class="text-xl font-semibold text-gray-900">API Response</h3>
                    </div>
                    <button id="closeJsonModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-y-auto">
                    <!-- API URL -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Endpoint:</label>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3">
                            <i class="fas fa-link text-gray-400 mr-2"></i>
                            <code id="apiUrl" class="text-sm text-gray-900 flex-1"><?php echo $currentBaseUrl; ?>/nuansa/item/api_listbarang.php</code>
                            <button id="copyUrl" class="ml-2 text-blue-600 hover:text-blue-800 transition-colors" title="Copy URL">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="jsonLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                        <p class="text-gray-600">Loading data...</p>
                    </div>
                    
                    <!-- Error State -->
                    <div id="jsonError" class="hidden text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Data</h4>
                        <p id="jsonErrorMessage" class="text-gray-600 mb-4"></p>
                        <button id="retryJson" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-retry mr-2"></i>Retry
                        </button>
                    </div>
                    
                    <!-- JSON Content -->
                    <div id="jsonContent" class="hidden">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Response:</label>
                            <button id="copyJson" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                <i class="fas fa-copy mr-1"></i>
                                <span class="text-sm">Copy Content</span>
                            </button>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-auto border" style="max-height: 400px; min-height: 200px;">
                            <pre id="jsonData" class="text-green-400 text-sm font-mono whitespace-pre-wrap overflow-auto" style="max-height: 350px; overflow-y: auto; overflow-x: auto;"></pre>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-3 border-t border-gray-200 flex justify-start items-center flex-shrink-0">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data from Accurate API
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Database Selection -->
    <div id="dbSelectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center">
                        <i class="fas fa-database text-green-600 mr-3 text-lg"></i>
                        <h3 class="text-xl font-semibold text-gray-900">Select Database</h3>
                    </div>
                    <button id="closeDbSelectModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-y-auto">
                    <!-- Loading State -->
                    <div id="dbSelectLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mb-4"></div>
                        <p class="text-gray-600">Loading databases...</p>
                    </div>
                    
                    <!-- Error State -->
                    <div id="dbSelectError" class="hidden text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Databases</h4>
                        <p id="dbSelectErrorMessage" class="text-gray-600 mb-4"></p>
                        <button id="retryDbSelect" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-retry mr-2"></i>Retry
                        </button>
                    </div>
                    
                    <!-- Database List -->
                    <div id="dbSelectContent" class="hidden">
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-medium text-gray-900">Available Databases</h4>
                                <div class="flex items-center space-x-2">
                                    <button id="openLatestDbFromModal" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                        <i class="fas fa-rocket mr-1"></i>Auto Open Latest
                                    </button>
                                    <button id="refreshDbList" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Database Cards -->
                        <div id="databaseList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Database cards will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-3 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Select a database to open it automatically
                    </div>
                    <div class="flex space-x-2">
                        <button id="closeDbSelectModalFooter" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        console.log('JavaScript is loading...');
        
        // Session info toggle and copy functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded!');
            
            // Toggle Session ID visibility
            const toggleSessionId = document.getElementById('toggleSessionId');
            const sessionIdInput = document.getElementById('sessionId');
            const copySessionId = document.getElementById('copySessionId');
            
            toggleSessionId.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (sessionIdInput.type === 'password') {
                    sessionIdInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    sessionIdInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
            
            copySessionId.addEventListener('click', function() {
                sessionIdInput.select();
                document.execCommand('copy');
                showToast('Session ID copied to clipboard!');
            });
            
            // Toggle Access Token visibility
            const toggleAccessToken = document.getElementById('toggleAccessToken');
            const accessTokenInput = document.getElementById('accessToken');
            const copyAccessToken = document.getElementById('copyAccessToken');
            
            toggleAccessToken.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (accessTokenInput.type === 'password') {
                    accessTokenInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    accessTokenInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
            
            copyAccessToken.addEventListener('click', function() {
                accessTokenInput.select();
                document.execCommand('copy');
                showToast('Access Token copied to clipboard!');
            });
            
            // Start countdown when DOM is ready
            startTokenCountdown();
            
            // Company info sudah di-load langsung dari PHP, tidak perlu AJAX lagi
            console.log('Company info loaded from PHP:', {
                alias: '<?php echo addslashes($companyInfo['alias']); ?>',
                licenseEnd: '<?php echo addslashes($companyInfo['licenseEnd']); ?>',
                logoUrl: '<?php echo addslashes($companyInfo['logoUrl'] ?? ''); ?>'
            });
            
            // Auto hide notifications after 5 seconds
            const notifications = document.querySelectorAll('.fixed.top-4');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.transition = 'opacity 0.5s ease-out';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 500);
                }, 5000);
            });
        });
        
        // Auto-detected base URL from PHP
        const BASE_URL = '<?php echo $currentBaseUrl; ?>';
        
        // API URLs menggunakan base URL dinamis
        const ITEM_API_URL = BASE_URL + '/nuansa/item/api_listbarang.php';
        const VENDOR_API_URL = BASE_URL + '/nuansa/vendor/listvendor.php';
        const BRANCH_API_URL = BASE_URL + '/nuansa/branch/listbranch.php';
        const EMPLOYEE_API_URL = BASE_URL + '/nuansa/employee/listemployee.php';
        const CUSTOMER_API_URL = BASE_URL + '/nuansa/customer/listcustomer.php';
        const SHIPMENT_API_URL = BASE_URL + '/nuansa/shipment/listshipment.php';
        const CATEGORY_API_URL = BASE_URL + '/nuansa/itemcategory/listcategory.php';
        const FIXEDASSET_API_URL = BASE_URL + '/nuansa/fixedasset/listasset.php';
        const WAREHOUSE_API_URL = BASE_URL + '/nuansa/warehouse/listwarehouse.php';
        const PRICECATEGORY_API_URL = BASE_URL + '/nuansa/price/listprice_category.php';
        const SALESORDER_API_URL = BASE_URL + '/nuansa/salesorder/list_so.php';
        const SALESINVOICE_API_URL = BASE_URL + '/nuansa/salesinvoice/list_invoice.php';
        const SALESRECEIPT_API_URL = BASE_URL + '/nuansa/salesreceipt/list_receipt.php';
        const ITEMTRANSFER_API_URL = BASE_URL + '/nuansa/itemtransfer/list_itemtransfer.php';
        const PURCHASEORDER_API_URL = BASE_URL + '/nuansa/purchaseorder/list_po.php';
        const DB_LIST_URL = BASE_URL + '/nuansa/db.php';
        const DB_OPEN_URL = BASE_URL + '/nuansa/db_open.php';
        let currentApiUrl = ITEM_API_URL;
        
        // API endpoints untuk status check
        const API_ENDPOINTS = [
            { name: 'Items API', url: ITEM_API_URL, type: 'json' },
            { name: 'Vendors API', url: VENDOR_API_URL, type: 'json' },
            { name: 'Branches API', url: BRANCH_API_URL, type: 'json' },
            { name: 'Employees API', url: EMPLOYEE_API_URL, type: 'json' },
            { name: 'Customers API', url: CUSTOMER_API_URL, type: 'json' },
            { name: 'Shipments API', url: SHIPMENT_API_URL, type: 'json' },
            { name: 'Categories API', url: CATEGORY_API_URL, type: 'json' },
            { name: 'Fixed Assets API', url: FIXEDASSET_API_URL, type: 'json' },
            { name: 'Warehouses API', url: WAREHOUSE_API_URL, type: 'json' },
            { name: 'Price Categories API', url: PRICECATEGORY_API_URL, type: 'json' },
            { name: 'Sales Orders API', url: SALESORDER_API_URL, type: 'json' },
            { name: 'Sales Invoices API', url: SALESINVOICE_API_URL, type: 'json' },
            { name: 'Sales Receipts API', url: SALESRECEIPT_API_URL, type: 'json' },
            { name: 'Item Transfers API', url: ITEMTRANSFER_API_URL, type: 'json' },
            { name: 'Purchase Orders API', url: PURCHASEORDER_API_URL, type: 'json' },
            { name: 'Database List', url: DB_LIST_URL, type: 'json' },
            { name: 'Database Open', url: DB_OPEN_URL, type: 'json' }
        ];
        
        // Modal elements - moved inside DOMContentLoaded to ensure elements exist
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Setting up modal event listeners...');
            
            // Get all modal elements after DOM is ready
            const jsonModal = document.getElementById('jsonModal');
            const statusModal = document.getElementById('statusModal');
            const dbSelectModal = document.getElementById('dbSelectModal');
            const openItemApiModal = document.getElementById('openItemApiModal');
            const openVendorApiModal = document.getElementById('openVendorApiModal');
            const openBranchApiModal = document.getElementById('openBranchApiModal');
            const openEmployeeApiModal = document.getElementById('openEmployeeApiModal');
            const openCustomerApiModal = document.getElementById('openCustomerApiModal');
            const openShipmentApiModal = document.getElementById('openShipmentApiModal');
            const openCategoryApiModal = document.getElementById('openCategoryApiModal');
            const openFixedAssetApiModal = document.getElementById('openFixedAssetApiModal');
            const openWarehouseApiModal = document.getElementById('openWarehouseApiModal');
            const openPriceCategoryApiModal = document.getElementById('openPriceCategoryApiModal');
            const openSalesOrderApiModal = document.getElementById('openSalesOrderApiModal');
            const openSalesInvoiceApiModal = document.getElementById('openSalesInvoiceApiModal');
            const openSalesReceiptApiModal = document.getElementById('openSalesReceiptApiModal');
            const openItemTransferApiModal = document.getElementById('openItemTransferApiModal');
            const openPurchaseOrderApiModal = document.getElementById('openPurchaseOrderApiModal');
            const openDbListModal = document.getElementById('openDbListModal');
            const openDbSelectModal = document.getElementById('openDbSelectModal');
            const openLatestDb = document.getElementById('openLatestDb');
            const openDbOpenModal = document.getElementById('openDbOpenModal');
            const checkAllEndpoints = document.getElementById('checkAllEndpoints');
            const openStatusModal = document.getElementById('openStatusModal');
            const closeJsonModal = document.getElementById('closeJsonModal');
            const closeStatusModal = document.getElementById('closeStatusModal');
            const closeDbSelectModal = document.getElementById('closeDbSelectModal');
            const closeDbSelectModalFooter = document.getElementById('closeDbSelectModalFooter');
            const refreshStatus = document.getElementById('refreshStatus');
            const retryJson = document.getElementById('retryJson');
            const copyUrl = document.getElementById('copyUrl');
            const copyJson = document.getElementById('copyJson');
            
            // State elements
            const jsonLoading = document.getElementById('jsonLoading');
            const jsonError = document.getElementById('jsonError');
            const jsonContent = document.getElementById('jsonContent');
            const jsonErrorMessage = document.getElementById('jsonErrorMessage');
            const jsonData = document.getElementById('jsonData');
            const apiUrl = document.getElementById('apiUrl');
            
            // Event listeners for JSON modals
            if (openItemApiModal) {
                openItemApiModal.addEventListener('click', () => {
                    currentApiUrl = ITEM_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openVendorApiModal) {
                openVendorApiModal.addEventListener('click', () => {
                    currentApiUrl = VENDOR_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openBranchApiModal) {
                openBranchApiModal.addEventListener('click', () => {
                    currentApiUrl = BRANCH_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openEmployeeApiModal) {
                openEmployeeApiModal.addEventListener('click', () => {
                    currentApiUrl = EMPLOYEE_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openCustomerApiModal) {
                openCustomerApiModal.addEventListener('click', () => {
                    currentApiUrl = CUSTOMER_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openShipmentApiModal) {
                openShipmentApiModal.addEventListener('click', () => {
                    currentApiUrl = SHIPMENT_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openCategoryApiModal) {
                openCategoryApiModal.addEventListener('click', () => {
                    currentApiUrl = CATEGORY_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openFixedAssetApiModal) {
                openFixedAssetApiModal.addEventListener('click', () => {
                    currentApiUrl = FIXEDASSET_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openWarehouseApiModal) {
                openWarehouseApiModal.addEventListener('click', () => {
                    currentApiUrl = WAREHOUSE_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openPriceCategoryApiModal) {
                openPriceCategoryApiModal.addEventListener('click', () => {
                    currentApiUrl = PRICECATEGORY_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openSalesOrderApiModal) {
                openSalesOrderApiModal.addEventListener('click', () => {
                    currentApiUrl = SALESORDER_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openSalesInvoiceApiModal) {
                openSalesInvoiceApiModal.addEventListener('click', () => {
                    currentApiUrl = SALESINVOICE_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openSalesReceiptApiModal) {
                openSalesReceiptApiModal.addEventListener('click', () => {
                    currentApiUrl = SALESRECEIPT_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openItemTransferApiModal) {
                openItemTransferApiModal.addEventListener('click', () => {
                    currentApiUrl = ITEMTRANSFER_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openPurchaseOrderApiModal) {
                openPurchaseOrderApiModal.addEventListener('click', () => {
                    currentApiUrl = PURCHASEORDER_API_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openDbListModal) {
                openDbListModal.addEventListener('click', () => {
                    currentApiUrl = DB_LIST_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            if (openDbOpenModal) {
                openDbOpenModal.addEventListener('click', () => {
                    currentApiUrl = DB_OPEN_URL;
                    apiUrl.textContent = currentApiUrl;
                    jsonModal.classList.remove('hidden');
                    loadJsonData();
                });
            }
            
            // Database Selection Modal Event Listeners
            if (openDbSelectModal) {
                openDbSelectModal.addEventListener('click', () => {
                    console.log('Opening database selection modal...');
                    dbSelectModal.classList.remove('hidden');
                    loadDatabaseList();
                });
            }
            
            if (openLatestDb) {
                openLatestDb.addEventListener('click', () => {
                    console.log('Auto-opening latest database...');
                    openLatestDatabase();
                });
            }
            
            // API Status Check Event Listeners
            if (checkAllEndpoints) {
                checkAllEndpoints.addEventListener('click', () => {
                    console.log('checkAllEndpoints clicked!');
                    statusModal.classList.remove('hidden');
                    checkAllAPIs();
                });
            }
            
            if (openStatusModal) {
                openStatusModal.addEventListener('click', () => {
                    console.log('openStatusModal clicked!');
                    statusModal.classList.remove('hidden');
                });
            }
            
            // Close buttons
            if (closeJsonModal) {
                closeJsonModal.addEventListener('click', closeModal);
            }
            
            if (closeStatusModal) {
                closeStatusModal.addEventListener('click', function() {
                    console.log('Close status modal clicked!');
                    statusModal.classList.add('hidden');
                });
            }
            
            if (closeDbSelectModal) {
                closeDbSelectModal.addEventListener('click', function() {
                    console.log('Close database select modal clicked!');
                    dbSelectModal.classList.add('hidden');
                });
            }
            
            if (closeDbSelectModalFooter) {
                closeDbSelectModalFooter.addEventListener('click', function() {
                    console.log('Close database select modal footer clicked!');
                    dbSelectModal.classList.add('hidden');
                });
            }
            
            if (refreshStatus) {
                refreshStatus.addEventListener('click', checkAllAPIs);
            }
            
            if (retryJson) {
                retryJson.addEventListener('click', loadJsonData);
            }
            
            // Copy buttons
            if (copyUrl) {
                copyUrl.addEventListener('click', () => {
                    copyToClipboard(currentApiUrl);
                    showToast('URL copied to clipboard!');
                });
            }
            
            if (copyJson) {
                copyJson.addEventListener('click', () => {
                    copyToClipboard(jsonData.textContent);
                    showToast('JSON copied to clipboard!');
                });
            }
            
            // Close modal when clicking backdrop
            if (jsonModal) {
                jsonModal.addEventListener('click', (e) => {
                    if (e.target === jsonModal) {
                        closeModal();
                    }
                });
            }
            
            if (statusModal) {
                statusModal.addEventListener('click', (e) => {
                    if (e.target === statusModal) {
                        console.log('Status modal backdrop clicked!');
                        statusModal.classList.add('hidden');
                    }
                });
            }
            
            if (dbSelectModal) {
                dbSelectModal.addEventListener('click', (e) => {
                    if (e.target === dbSelectModal) {
                        console.log('Database select modal backdrop clicked!');
                        dbSelectModal.classList.add('hidden');
                    }
                });
            }
            
            // Close modal with ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (jsonModal && !jsonModal.classList.contains('hidden')) {
                        closeModal();
                    }
                    if (statusModal && !statusModal.classList.contains('hidden')) {
                        console.log('ESC pressed - closing status modal');
                        statusModal.classList.add('hidden');
                    }
                    if (dbSelectModal && !dbSelectModal.classList.contains('hidden')) {
                        console.log('ESC pressed - closing database select modal');
                        dbSelectModal.classList.add('hidden');
                    }
                }
            });
        });
        
        // API Status Check Functions
        async function checkAllAPIs() {
            console.log('Starting API status check...');
            
            // Show loading state
            const statusLoading = document.getElementById('statusLoading');
            const statusTableBody = document.getElementById('statusTableBody');
            
            statusLoading.classList.remove('hidden');
            statusTableBody.innerHTML = '';
            
            // Reset counters
            let healthyCount = 0;
            let errorCount = 0;
            let slowCount = 0;
            
            console.log('Checking', API_ENDPOINTS.length, 'endpoints...');
            
            // Check each endpoint
            for (let i = 0; i < API_ENDPOINTS.length; i++) {
                const endpoint = API_ENDPOINTS[i];
                console.log('Checking endpoint:', endpoint.name);
                
                try {
                    const result = await checkSingleAPI(endpoint);
                    console.log('Result for', endpoint.name, ':', result);
                    
                    if (result.status === 'healthy') healthyCount++;
                    else if (result.status === 'error') errorCount++;
                    
                    if (result.responseTime > 2000) slowCount++;
                    
                    // Add row to table
                    addStatusRow(result);
                } catch (error) {
                    console.error('Error checking endpoint', endpoint.name, ':', error);
                    errorCount++;
                    
                    // Add error row
                    addStatusRow({
                        name: endpoint.name,
                        url: endpoint.url,
                        status: 'error',
                        responseTime: 0,
                        httpCode: 0,
                        message: 'Failed to check: ' + error.message
                    });
                }
            }
            
            // Update summary stats
            document.getElementById('healthyCount').textContent = healthyCount;
            document.getElementById('errorCount').textContent = errorCount;
            document.getElementById('slowCount').textContent = slowCount;
            document.getElementById('totalCount').textContent = API_ENDPOINTS.length;
            document.getElementById('lastChecked').textContent = new Date().toLocaleTimeString();
            
            // Hide loading state
            statusLoading.classList.add('hidden');
            
            console.log('API status check completed');
        }
        
        async function checkSingleAPI(endpoint) {
            const startTime = Date.now();
            
            try {
                console.log('Fetching:', endpoint.url);
                
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
                
                const response = await fetch(endpoint.url, {
                    method: 'GET',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                const endTime = Date.now();
                const responseTime = endTime - startTime;
                
                console.log('Response for', endpoint.name, '- Status:', response.status, 'Time:', responseTime + 'ms');
                
                let status = 'error';
                let message = `HTTP ${response.status}`;
                
                if (response.ok) {
                    if (endpoint.type === 'json') {
                        try {
                            const data = await response.json();
                            console.log('JSON data for', endpoint.name, ':', data);
                            
                            if (data.success !== false) {
                                status = 'healthy';
                                message = 'OK';
                            } else {
                                message = data.message || 'API returned success: false';
                            }
                        } catch (e) {
                            console.error('JSON parse error for', endpoint.name, ':', e);
                            message = 'Invalid JSON response';
                        }
                    } else {
                        status = 'healthy';
                        message = 'OK';
                    }
                } else {
                    console.error('HTTP error for', endpoint.name, ':', response.status);
                }
                
                return {
                    name: endpoint.name,
                    url: endpoint.url,
                    status: status,
                    responseTime: responseTime,
                    httpCode: response.status,
                    message: message
                };
                
            } catch (error) {
                const endTime = Date.now();
                const responseTime = endTime - startTime;
                
                console.error('Network error for', endpoint.name, ':', error);
                
                return {
                    name: endpoint.name,
                    url: endpoint.url,
                    status: 'error',
                    responseTime: responseTime,
                    httpCode: 0,
                    message: error.name === 'AbortError' ? 'Timeout (10s)' : (error.message || 'Network error')
                };
            }
        }
        
        function addStatusRow(result) {
            const tbody = document.getElementById('statusTableBody');
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            // Status badge
            let statusBadge = '';
            if (result.status === 'healthy') {
                statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Healthy</span>';
            } else {
                statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Error</span>';
            }
            
            // Response time badge
            let timeBadge = '';
            if (result.responseTime < 1000) {
                timeBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">' + result.responseTime + 'ms</span>';
            } else if (result.responseTime < 2000) {
                timeBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">' + result.responseTime + 'ms</span>';
            } else {
                timeBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">' + result.responseTime + 'ms</span>';
            }
            
            row.innerHTML = `
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${result.name}
                    <div class="text-xs text-gray-500 mt-1">${result.url}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${statusBadge}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${timeBadge}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${result.httpCode}</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${result.message}
                </td>
            `;
            
            tbody.appendChild(row);
        }
        
        function closeModal() {
            const jsonModal = document.getElementById('jsonModal');
            const jsonLoading = document.getElementById('jsonLoading');
            const jsonError = document.getElementById('jsonError');
            const jsonContent = document.getElementById('jsonContent');
            
            jsonModal.classList.add('hidden');
            resetModal();
        }
        
        function resetModal() {
            const jsonLoading = document.getElementById('jsonLoading');
            const jsonError = document.getElementById('jsonError');
            const jsonContent = document.getElementById('jsonContent');
            
            jsonLoading.classList.add('hidden');
            jsonError.classList.add('hidden');
            jsonContent.classList.add('hidden');
        }
        
        function showLoading() {
            const jsonLoading = document.getElementById('jsonLoading');
            const jsonError = document.getElementById('jsonError');
            const jsonContent = document.getElementById('jsonContent');
            
            jsonLoading.classList.remove('hidden');
            jsonError.classList.add('hidden');
            jsonContent.classList.add('hidden');
        }
        
        function showError(message) {
            const jsonLoading = document.getElementById('jsonLoading');
            const jsonError = document.getElementById('jsonError');
            const jsonErrorMessage = document.getElementById('jsonErrorMessage');
            const jsonContent = document.getElementById('jsonContent');
            
            jsonLoading.classList.add('hidden');
            jsonError.classList.remove('hidden');
            jsonContent.classList.add('hidden');
            jsonErrorMessage.textContent = message;
        }
        
        function showContent() {
            jsonLoading.classList.add('hidden');
            jsonError.classList.add('hidden');
            jsonContent.classList.remove('hidden');
        }
        
        function loadJsonData() {
            showLoading();
            
            fetch(currentApiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    // Check if response is JSON or HTML
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text();
                    }
                })
                .then(data => {
                    if (typeof data === 'string') {
                        // Handle HTML response (for DB pages)
                        jsonData.textContent = data;
                    } else {
                        // Handle JSON response (for API pages)
                        jsonData.textContent = JSON.stringify(data, null, 2);
                    }
                    showContent();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError(`Failed to load data: ${error.message}`);
                });
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Success
            }).catch(err => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            });
        }
        
        function showToast(message) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Token Expiration Countdown
        function startTokenCountdown() {
            const element = document.getElementById('tokenCountdown');
            if (!element) return;
            
            // Show loading
            element.innerHTML = 'Loading...';
            
            // Gunakan API database list untuk mendapatkan accessibleUntil
            fetch('api/db-list.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.d && data.data.d.length > 0) {
                        // Ambil database pertama atau yang sesuai dengan ACCURATE_DATABASE_ID
                        const databases = data.data.d;
                        let selectedDb = databases.find(db => db.id == <?php echo ACCURATE_DATABASE_ID; ?>);
                        
                        // Jika tidak ditemukan, ambil yang pertama
                        if (!selectedDb) {
                            selectedDb = databases[0];
                        }
                        
                        if (selectedDb && selectedDb.accessibleUntil) {
                            // Parse tanggal dari API (format: DD/MM/YYYY)
                            const dateStr = selectedDb.accessibleUntil;
                            const parts = dateStr.split('/');
                            const day = parseInt(parts[0]);
                            const month = parseInt(parts[1]) - 1; // JavaScript month is 0-indexed
                            const year = parseInt(parts[2]);
                            
                            // Set ekspirasi jam 23:59:59 di tanggal tersebut
                            const expirationDate = new Date(year, month, day, 23, 59, 59);
                            
                            // Function untuk update countdown setiap detik
                            const updateCountdown = () => {
                                const now = new Date();
                                const timeLeft = expirationDate.getTime() - now.getTime();
                                
                                if (timeLeft <= 0) {
                                    element.innerHTML = 'EXPIRED';
                                    element.className = 'ml-2 px-3 py-1 bg-red-500 text-white rounded-full text-sm font-bold animate-pulse';
                                    return;
                                }
                                
                                // Hitung sisa waktu
                                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                                
                                // Format tampilan dan warna - FORMAT: "6 Day 07:02:38"
                                let text = '';
                                let bgColor = 'bg-green-100';
                                let textColor = 'text-green-800';
                                
                                // Helper function untuk format 2 digit
                                const pad = (num) => num.toString().padStart(2, '0');
                                
                                if (days > 0) {
                                    // Format: "6 Day 07:02:38"
                                    text = `${days} Day ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                                    
                                    if (days === 1) {
                                        bgColor = 'bg-yellow-100';
                                        textColor = 'text-yellow-800';
                                    }
                                } else if (hours > 0) {
                                    // Format: "07:02:38" (tanpa day jika 0 hari)
                                    text = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                                    bgColor = 'bg-orange-100';
                                    textColor = 'text-orange-800';
                                } else if (minutes > 0) {
                                    // Format: "00:02:38" (tetap format jam untuk konsistensi)
                                    text = `00:${pad(minutes)}:${pad(seconds)}`;
                                    bgColor = 'bg-red-100';
                                    textColor = 'text-red-800';
                                } else {
                                    // Format: "00:00:38" (detik terakhir)
                                    text = `00:00:${pad(seconds)}`;
                                    bgColor = 'bg-red-500';
                                    textColor = 'text-white';
                                }
                                
                                element.innerHTML = text;
                                element.className = `ml-2 px-3 py-1 ${bgColor} ${textColor} rounded-full text-sm font-bold`;
                            };
                            
                            // Update langsung, lalu setiap detik
                            updateCountdown();
                            setInterval(updateCountdown, 1000);
                            
                        } else {
                            element.innerHTML = '<span class="text-red-600">Data tidak valid</span>';
                        }
                    } else {
                        element.innerHTML = '<span class="text-red-600">Data tidak valid</span>';
                    }
                })
                .catch(error => {
                    element.innerHTML = '<span class="text-red-600">Error loading</span>';
                    console.error('Token countdown error:', error);
                });
        }
        
        // Database Management Functions
        async function loadDatabaseList() {
            console.log('Loading database list...');
            
            const dbSelectLoading = document.getElementById('dbSelectLoading');
            const dbSelectError = document.getElementById('dbSelectError');
            const dbSelectContent = document.getElementById('dbSelectContent');
            const dbSelectErrorMessage = document.getElementById('dbSelectErrorMessage');
            const databaseList = document.getElementById('databaseList');
            
            // Show loading state
            dbSelectLoading.classList.remove('hidden');
            dbSelectError.classList.add('hidden');
            dbSelectContent.classList.add('hidden');
            
            try {
                const response = await fetch(DB_LIST_URL);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data && data.data.d) {
                    const databases = data.data.d;
                    
                    // Clear existing content
                    databaseList.innerHTML = '';
                    
                    // Sort databases: non-expired first, then by ID (newest first)
                    databases.sort((a, b) => {
                        if (a.expired !== b.expired) {
                            return a.expired ? 1 : -1; // Non-expired first
                        }
                        return b.id - a.id; // Newest ID first
                    });
                    
                    databases.forEach((db, index) => {
                        const card = createDatabaseCard(db, index === 0 && !db.expired);
                        databaseList.appendChild(card);
                    });
                    
                    // Show content
                    dbSelectLoading.classList.add('hidden');
                    dbSelectError.classList.add('hidden');
                    dbSelectContent.classList.remove('hidden');
                    
                } else {
                    throw new Error('Invalid response format');
                }
                
            } catch (error) {
                console.error('Error loading database list:', error);
                dbSelectLoading.classList.add('hidden');
                dbSelectError.classList.remove('hidden');
                dbSelectContent.classList.add('hidden');
                dbSelectErrorMessage.textContent = error.message;
            }
        }
        
        function createDatabaseCard(db, isLatest = false) {
            const card = document.createElement('div');
            card.className = `relative border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md ${
                db.expired ? 'border-red-200 bg-red-50' : 
                isLatest ? 'border-green-500 bg-green-50 ring-2 ring-green-200' : 
                'border-gray-200 bg-white hover:border-blue-300'
            }`;
            
            const statusBadge = db.expired ? 
                '<span class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full">EXPIRED</span>' :
                isLatest ? '<span class="absolute top-2 right-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full">LATEST</span>' :
                '<span class="absolute top-2 right-2 px-2 py-1 bg-blue-500 text-white text-xs rounded-full">ACTIVE</span>';
            
            card.innerHTML = `
                ${statusBadge}
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        ${db.logoUrl ? 
                            `<img src="${db.logoUrl}" alt="Logo" class="w-12 h-12 rounded-full border-2 border-gray-200">` :
                            '<div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full flex items-center justify-center"><i class="fas fa-database text-white"></i></div>'
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-lg font-semibold text-gray-900 truncate">${db.alias}</h4>
                        <div class="mt-1 space-y-1">
                            <p class="text-sm text-gray-600">ID: ${db.id}</p>
                            <p class="text-sm text-gray-600">License End: ${db.licenseEnd}</p>
                            <p class="text-sm text-gray-600">Access Until: ${db.accessibleUntil}</p>
                            <div class="flex items-center space-x-2 mt-2">
                                ${db.trial ? '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Trial</span>' : ''}
                                ${db.admin ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Admin</span>' : ''}
                                ${db.demo ? '<span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Demo</span>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    ${!db.expired ? 
                        `<button onclick="openDatabase(${db.id})" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                            <i class="fas fa-play mr-1"></i>Open Database
                        </button>` :
                        '<button disabled class="px-3 py-1 bg-gray-300 text-gray-500 text-sm rounded cursor-not-allowed">Expired</button>'
                    }
                </div>
            `;
            
            // Add click handler for the entire card (except buttons)
            card.addEventListener('click', (e) => {
                if (!e.target.closest('button') && !db.expired) {
                    openDatabase(db.id);
                }
            });
            
            return card;
        }
        
        async function openDatabase(databaseId) {
            console.log('Opening database:', databaseId);
            
            try {
                showToast('Opening database...');
                
                const response = await fetch(`${DB_OPEN_URL}?id=${databaseId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(`Database ${databaseId} opened successfully!`);
                    
                    // Close the modal
                    const dbSelectModal = document.getElementById('dbSelectModal');
                    if (dbSelectModal) {
                        dbSelectModal.classList.add('hidden');
                    }
                    
                    // Show success information
                    console.log('Database opened:', data.data);
                    
                    // Optionally refresh the page after a delay to reflect the new session
                    setTimeout(() => {
                        if (confirm('Database opened successfully! Would you like to refresh the page to see the updated session info?')) {
                            window.location.reload();
                        }
                    }, 1000);
                    
                } else {
                    throw new Error(data.message || 'Failed to open database');
                }
                
            } catch (error) {
                console.error('Error opening database:', error);
                showToast(`Error: ${error.message}`);
            }
        }
        
        async function openLatestDatabase() {
            console.log('Auto-opening latest database...');
            
            try {
                showToast('Loading latest database...');
                
                const response = await fetch(DB_LIST_URL);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data && data.data.d) {
                    const databases = data.data.d;
                    
                    // Sort databases: non-expired first, then by ID (newest first)
                    databases.sort((a, b) => {
                        if (a.expired !== b.expired) {
                            return a.expired ? 1 : -1; // Non-expired first
                        }
                        return b.id - a.id; // Newest ID first
                    });
                    
                    // Find the latest non-expired database
                    const latestDb = databases.find(db => !db.expired);
                    
                    if (latestDb) {
                        await openDatabase(latestDb.id);
                    } else {
                        throw new Error('No active (non-expired) databases found');
                    }
                    
                } else {
                    throw new Error('Invalid response format');
                }
                
            } catch (error) {
                console.error('Error opening latest database:', error);
                showToast(`Error: ${error.message}`);
            }
        }
        
        // Add retry and refresh handlers for database modal
        document.addEventListener('DOMContentLoaded', function() {
            const retryDbSelect = document.getElementById('retryDbSelect');
            const refreshDbList = document.getElementById('refreshDbList');
            const openLatestDbFromModal = document.getElementById('openLatestDbFromModal');
            
            if (retryDbSelect) {
                retryDbSelect.addEventListener('click', loadDatabaseList);
            }
            
            if (refreshDbList) {
                refreshDbList.addEventListener('click', loadDatabaseList);
            }
            
            if (openLatestDbFromModal) {
                openLatestDbFromModal.addEventListener('click', openLatestDatabase);
            }
        });
    </script>
</body>
</html>
