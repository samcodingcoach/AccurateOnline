<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getPurchaseOrderList();
$purchaseOrders = [];

if ($result['success'] && isset($result['data']['d'])) {
    $purchaseOrders = $result['data']['d'];
    
    // Sort purchase orders by ID descending (newest first)
    usort($purchaseOrders, function($a, $b) {
        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
    });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Purchase Order - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Purchase Order</h1>
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
            <h2 class="text-xl font-semibold mb-4">Data Purchase Order</h2>
            
            <?php if (!empty($purchaseOrders)): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($purchaseOrders); ?> purchase order.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shipment (%)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($purchaseOrders as $purchaseOrder): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($purchaseOrder['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($purchaseOrder['number'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($purchaseOrder['statusName'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <?php echo number_format($purchaseOrder['totalAmount'] ?? 0, 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?php 
                                            $percentShipped = $purchaseOrder['percentShipped'] ?? 0;
                                            if ($percentShipped == 100) {
                                                echo 'bg-green-100 text-green-800';
                                            } elseif ($percentShipped >= 50) {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                echo 'bg-red-100 text-red-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($percentShipped); ?>%
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail.php?id=<?php echo $purchaseOrder['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-600">Tidak ada data purchase order.</p>
                    <?php if (isset($result['message']) && !$result['success']): ?>
                        <div class="mt-4 bg-red-50 border border-red-200 rounded p-3">
                            <p class="text-red-700 text-sm"><?php echo htmlspecialchars($result['message']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Debug Info -->
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                <div class="text-sm">
                    <p><strong>Data Source:</strong> Purchase Order List API (/list.do)</p>
                    <p><strong>List API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Purchase Orders Found:</strong> <?php echo count($purchaseOrders); ?></p>
                    <?php if (isset($result['message']) && !$result['success']): ?>
                        <p><strong>Error:</strong> <?php echo htmlspecialchars($result['message']); ?></p>
                    <?php endif; ?>
                </div>
                
                <details class="mt-4">
                    <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                    <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                        if ($result) {
                            echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
                        } else {
                            echo "No raw response available";
                        }
                    ?></pre>
                </details>
            </div>
        </div>
    </main>
</body>
</html>
