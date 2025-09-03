<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$number = $_GET['number'] ?? null;

if (!$number) {
    header('Location: index.php');
    exit;
}

// Gunakan selling price adjustment detail API berdasarkan nomor
$result = $api->getSellingPriceAdjustmentDetailByNumber($number);
$spa = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $spa = $result['data']['d'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Selling Price Adjustment - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Selling Price Adjustment</h1>
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
        <?php if ($spa): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-tags text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Selling Price Adjustment Details</h2>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Dokumen</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-hashtag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['number'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Harga</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-tag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['priceCategory']['name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-building text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['spaBranchName'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Penyesuaian</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-sliders-h text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['salesAdjustmentType'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['transDate'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-400 mr-3">#</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['id'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Detail Items -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Item</h3>
                        
                        <?php if (isset($spa['detailItem']) && is_array($spa['detailItem']) && !empty($spa['detailItem'])): ?>
                            <?php
                            // Pagination dan search untuk detail items
                            $allItems = $spa['detailItem'];
                            $itemsPerPage = 20;
                            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $search = isset($_GET['search']) ? $_GET['search'] : '';
                            
                            // Filter berdasarkan search
                            if (!empty($search)) {
                                $allItems = array_filter($allItems, function($item) use ($search) {
                                    $kode = $item['item']['no'] ?? '';
                                    $nama = $item['item']['name'] ?? '';
                                    return stripos($kode, $search) !== false || stripos($nama, $search) !== false;
                                });
                            }
                            
                            // Pagination
                            $totalItems = count($allItems);
                            $totalPages = ceil($totalItems / $itemsPerPage);
                            $currentPage = max(1, min($currentPage, $totalPages));
                            $startIndex = ($currentPage - 1) * $itemsPerPage;
                            $pagedItems = array_slice($allItems, $startIndex, $itemsPerPage);
                            ?>
                            
                            <!-- Search Form -->
                            <div class="mb-4">
                                <form method="GET" class="flex gap-2">
                                    <input type="hidden" name="number" value="<?php echo htmlspecialchars($number); ?>">
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Cari berdasarkan kode atau nama barang..." 
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="?number=<?php echo htmlspecialchars($number); ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            
                            <?php if (!empty($pagedItems)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($pagedItems as $index => $detail): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php echo $startIndex + $index + 1; ?>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($detail['item']['no'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($detail['item']['name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($detail['item']['unit1']['name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php echo number_format($detail['price'] ?? 0, 2, ',', '.'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="text-sm text-gray-700">
                                            Menampilkan <?php echo $startIndex + 1; ?> - <?php echo min($startIndex + $itemsPerPage, $totalItems); ?> dari <?php echo $totalItems; ?> item
                                        </div>
                                        <div class="flex gap-2">
                                            <?php if ($currentPage > 1): ?>
                                                <a href="?number=<?php echo htmlspecialchars($number); ?>&search=<?php echo htmlspecialchars($search); ?>&page=<?php echo $currentPage - 1; ?>" 
                                                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                                <?php if ($i == $currentPage): ?>
                                                    <span class="px-3 py-1 rounded bg-blue-600 text-white"><?php echo $i; ?></span>
                                                <?php else: ?>
                                                    <a href="?number=<?php echo htmlspecialchars($number); ?>&search=<?php echo htmlspecialchars($search); ?>&page=<?php echo $i; ?>" 
                                                       class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100"><?php echo $i; ?></a>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            
                                            <?php if ($currentPage < $totalPages): ?>
                                                <a href="?number=<?php echo htmlspecialchars($number); ?>&search=<?php echo htmlspecialchars($search); ?>&page=<?php echo $currentPage + 1; ?>" 
                                                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-box-open text-4xl text-gray-400"></i>
                                    <p class="mt-4 text-gray-600">Tidak ada detail item yang sesuai dengan pencarian.</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-box-open text-4xl text-gray-400"></i>
                                <p class="mt-4 text-gray-600">Tidak ada detail item yang ditemukan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Selling Price Adjustment Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>SPA Found:</strong> <?php echo $spa ? 'Yes' : 'No'; ?></p>
                        <p><strong>SPA Number:</strong> <?php echo htmlspecialchars($number); ?></p>
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Selling Price Adjustment Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Selling Price Adjustment dengan nomor <?php echo htmlspecialchars($number); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar Selling Price Adjustment
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>