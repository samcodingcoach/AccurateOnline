<?php
/**
 * Branch Detail Page
 * Menampilkan detail informasi branch berdasarkan ID
 */

require_once __DIR__ . '/../bootstrap.php';

// Ambil ID branch dari parameter
$branchId = $_GET['id'] ?? null;

if (!$branchId) {
    header('Location: index.php');
    exit;
}

// Inisialisasi API class
$api = new AccurateAPI();

// Ambil detail branch
$branchResponse = $api->getBranchDetail($branchId);
$branch = null;

if ($branchResponse['success'] && isset($branchResponse['data'])) {
    // Cek apakah response berhasil berdasarkan field 's' dalam data
    if (isset($branchResponse['data']['s']) && $branchResponse['data']['s'] === true) {
        // Jika berhasil, data ada di 'd'
        if (isset($branchResponse['data']['d'])) {
            $branch = $branchResponse['data']['d'];
        }
    }
    // Jika 's' false, berarti ada error (seperti invalid field value)
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Branch Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-info-circle"></i> Branch Information
                    </h1>
                    <p class="text-gray-600 mt-2">Detail informasi cabang ID: <?php echo htmlspecialchars($branchId); ?></p>
                </div>
                <div class="space-x-2">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if (!$branch): ?>
            <!-- Error State -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-4"></i>
                    <div>
                        <h2 class="text-lg font-semibold text-red-800">Branch Tidak Ditemukan</h2>
                        <p class="text-red-600 mt-1">
                            <?php
                            if (isset($branchResponse['data']['d']) && is_array($branchResponse['data']['d'])) {
                                echo "Error: " . implode(', ', $branchResponse['data']['d']);
                            } else {
                                echo "Branch dengan ID \"" . htmlspecialchars($branchId) . "\" tidak ditemukan atau tidak dapat diakses.";
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-red-700 font-medium">Silakan gunakan ID branch yang valid:</p>
                    <p class="text-red-600 text-sm mt-1">
                        <a href="../get-valid-branch-ids.php" class="underline" target="_blank">Lihat daftar ID branch yang valid</a>
                    </p>
                </div>
                
                <div class="mt-4 bg-red-100 p-4 rounded">
                    <h3 class="font-semibold text-red-800 mb-2">Error Details:</h3>
                    <pre class="text-sm text-red-700"><?php echo json_encode($branchResponse, JSON_PRETTY_PRINT); ?></pre>
                </div>
            </div>
        <?php else: ?>
            <!-- Branch Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-building mr-2"></i>Branch Details
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <i class="fas fa-building text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($branch['name'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch ID</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <i class="fas fa-hashtag text-gray-400 mr-2"></i>
                                    <code><?php echo htmlspecialchars($branch['id'] ?? 'N/A'); ?></code>
                                </div>
                            </div>
                            
                            <?php if (isset($branch['code'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <i class="fas fa-code text-gray-400 mr-2"></i>
                                    <code><?php echo htmlspecialchars($branch['code']); ?></code>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <?php 
                                    // suspended: false = aktif, suspended: true = tidak aktif
                                    $isActive = !($branch['suspended'] ?? true); // Default false jika tidak ada field suspended
                                    ?>
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactive (Suspended)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Branch</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <?php if (isset($branch['isDefault']) && $branch['isDefault']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-star mr-1"></i>
                                            Default Branch
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500">
                                            <i class="fas fa-minus mr-1"></i>
                                            Not Default
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (isset($branch['description'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <div class="bg-gray-50 p-3 rounded border">
                                    <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($branch['description']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Raw Data -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-code mr-2"></i>Raw API Response
                </h3>
            </div>
            <div class="p-6">
                <div class="bg-gray-100 p-4 rounded">
                    <pre class="text-sm overflow-x-auto"><?php echo json_encode($branchResponse, JSON_PRETTY_PRINT); ?></pre>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
