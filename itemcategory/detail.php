<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    header('Location: index.php');
    exit;
}

// Gunakan item category detail API
$result = $api->getItemCategoryDetail($categoryId);
$category = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $category = $result['data']['d'];
}

// Helper function untuk membersihkan nama kategori dari backslash
function cleanCategoryName($name) {
    if (empty($name) || $name === null) {
        return 'N/A';
    }
    return str_replace('\\', '', $name);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kategori Item - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Kategori Item</h1>
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
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-tags text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Category Details</h2>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-tag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars(cleanCategoryName($category['name'] ?? null)); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-400 mr-3">#</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($category['id'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-layer-group text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($category['lvl'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama dengan Hierarki</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-sitemap text-gray-400 mr-3"></i>
                                    <span class="text-gray-900 font-mono"><?php 
                                        // Prioritas: nameWithIndentStrip (menggunakan "-") lalu nameWithIndent (menggunakan &nbsp;)
                                        $hierarchyName = $category['nameWithIndentStrip'] ?? $category['nameWithIndent'] ?? $category['name'] ?? 'N/A';
                                        echo htmlspecialchars($hierarchyName);
                                    ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kategori</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <?php 
                                    $isParentNode = $category['parentNode'] ?? false;
                                    if ($isParentNode): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-folder mr-1"></i>
                                            Parent Category
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            Child Category
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default Category</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <?php 
                                    $isDefault = $category['defaultCategory'] ?? false;
                                    if ($isDefault): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Ya
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Tidak
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Deskripsi -->
                    <?php if (!empty($category['description'] ?? null)): ?>
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Deskripsi</h3>
                        
                        <div class="mb-6">
                            <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                                <i class="fas fa-info-circle text-gray-400 mr-3 mt-1"></i>
                                <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($category['description'] ?? '')); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Item Category Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Category Found:</strong> <?php echo $category ? 'Yes' : 'No'; ?></p>
                        <p><strong>Category ID:</strong> <?php echo htmlspecialchars($categoryId); ?></p>
                        <?php if (isset($result['error'])): ?>
                            <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                        <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                            // Tampilkan raw response yang sebenarnya dari API
                            if ($rawResponse) {
                                echo htmlspecialchars(json_encode($rawResponse, JSON_PRETTY_PRINT));
                            } else {
                                echo "No raw response available";
                            }
                        ?></pre>
                    </details>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Kategori Item Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Kategori item dengan ID <?php echo htmlspecialchars($categoryId); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar Kategori Item
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
