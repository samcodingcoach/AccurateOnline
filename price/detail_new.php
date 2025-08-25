<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    header('Location: index.php');
    exit;
}

// Gunakan price category detail API
$result = $api->getPriceCategoryDetail($categoryId);
$category = null;
$rawResponse = $result; // Simpan raw response dari detail

// Cek berbagai struktur data yang mungkin dikembalikan
if ($result['success']) {
    // Jika data ada di structure: data.d (format standard Accurate)
    if (isset($result['data']['d'])) {
        $category = $result['data']['d'];
    }
    // Jika data langsung di data
    elseif (isset($result['data']) && is_array($result['data'])) {
        $category = $result['data'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Price Category - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-tags text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Price Category</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($category): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Header Detail -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-tags text-blue-600 mr-2"></i>
                        Price Category Detail - ID: <?php echo htmlspecialchars($categoryId); ?>
                    </h2>
                </div>
                
                <!-- Detail Content -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($category['id'] ?? $categoryId); ?></p>
                        </div>
                        
                        <?php if (isset($category['name'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($category['name']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($category['description'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Raw Response untuk debugging -->
            <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Raw Response (Debug)</h3>
                </div>
                <div class="px-6 py-4">
                    <pre class="bg-gray-100 p-4 rounded overflow-auto text-xs"><?php echo htmlspecialchars(json_encode($rawResponse, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Price Category Tidak Ditemukan</h3>
                <p class="text-gray-600 mb-4">Price Category dengan ID <?php echo htmlspecialchars($categoryId); ?> tidak dapat ditemukan.</p>
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
