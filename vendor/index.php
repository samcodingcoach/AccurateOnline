<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getVendorList();
$vendors = [];

if ($result['success'] && isset($result['data']['d'])) {
    $vendors = $result['data']['d'];
    
    // getVendorList() already returns category data, no need for additional API calls
    // This fixes the duplication issue and improves performance
}

// Helper function untuk mendapatkan kategori dari data vendor
function getVendorCategory($vendor) {
    // Since getVendorList() already includes category data, use it directly
    if (isset($vendor['category']['name'])) {
        return $vendor['category']['name'];
    }
    
    // Fallback untuk field kategori lainnya jika tidak ada
    if (isset($vendor['vendorGroup'])) {
        return $vendor['vendorGroup'];
    }
    
    if (isset($vendor['type'])) {
        return $vendor['type'];
    }
    
    // Default kategori
    return 'General';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Vendor - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-truck text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Vendor</h1>
                </div>
                <div class="flex gap-4">
                    <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Data Vendor</h2>
            
            <?php if (!empty($vendors)): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($vendors); ?> vendor.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Vendor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($vendors as $vendor): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($vendor['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($vendor['name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            <?php echo htmlspecialchars(getVendorCategory($vendor)); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($vendor['mobilePhone'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($vendor['email'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail.php?id=<?php echo $vendor['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Tidak ada data vendor.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
