<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$itemId = $_GET['id'] ?? null;

$item = null;
$error = null;
$sellingPrices = [];

if ($itemId && is_numeric($itemId)) {
    // Get detail barang seperti di itemv3.php
    $result = $api->getItemDetail($itemId);
    
    if ($result['success'] && isset($result['data']['d'])) {
        $item = $result['data']['d'];
        
        // Extract selling prices jika ada
        if (isset($item['detailSellingPrice']) && is_array($item['detailSellingPrice'])) {
            $sellingPrices = $item['detailSellingPrice'];
        }
    } else {
        $error = $result['error'] ?? 'Data tidak ditemukan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Levels - <?php echo htmlspecialchars($item['name'] ?? 'Unknown Item'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-tags text-orange-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Price Levels</h1>
                    <?php if ($item): ?>
                        <span class="ml-4 text-lg text-gray-600">- <?php echo htmlspecialchars($item['name']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex gap-4">
                    <a href="listv2.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                <?php elseif (empty($sellingPrices)): ?>
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2 text-yellow-500"></i>
                            Tidak ada price level yang ditemukan untuk item ini.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($sellingPrices as $priceDetail): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium"><?php echo htmlspecialchars($priceDetail['priceCategory']['name'] ?? 'Unknown'); ?></span>
                                            <div class="text-xs text-gray-500">
                                                <?php echo htmlspecialchars($priceDetail['priceCategory']['description'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($priceDetail['branch']['name'] ?? 'Unknown'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                            <?php echo formatCurrency($priceDetail['price'] ?? 0); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($priceDetail['effectiveDate'] ?? 'Unknown'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($priceDetail['unit']['name'] ?? 'Unknown'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if ($item): ?>
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-3">Item Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Kode:</span>
                                <span class="text-gray-900"><?php echo htmlspecialchars($item['no'] ?? 'N/A'); ?></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Kategori:</span>
                                <span class="text-gray-900"><?php echo htmlspecialchars($item['itemCategory']['name'] ?? 'N/A'); ?></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Default Price:</span>
                                <span class="text-gray-900"><?php echo formatCurrency($item['unitPrice'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
