<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$salesOrderId = $_GET['id'] ?? null;

if (!$salesOrderId) {
    header('Location: index.php');
    exit;
}

// Gunakan sales order detail API
$result = $api->getSalesOrderDetail($salesOrderId);
$salesOrder = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $salesOrder = $result['data']['d'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sales Order - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Sales Order</h1>
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
        <?php if ($salesOrder): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Sales Order Details</h2>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order By</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-user text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['customer']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-400 mr-3">#</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['customer']['customerNo'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['shipDate'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-400 mr-3">ID</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['id'] ?? $salesOrderId); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2 - Background Abu-abu -->
                            <div class="bg-gray-100 p-4 rounded-lg space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-handshake text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['paymentTerm']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['status'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Printed</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-print text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['printUserName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-alt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['number'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Po No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-clipboard text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['poNumber'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-percent text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo ($salesOrder['taxable'] ?? false) ? 'Yes' : 'No'; ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Inclusive Tax</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-plus-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo ($salesOrder['inclusiveTax'] ?? false) ? 'Yes' : 'No'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2 - Background Abu-abu -->
                            <div class="bg-gray-100 p-4 rounded-lg space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-building text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['branchId'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ship Date</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-shipping-fast text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['shipDateView'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipment</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-truck text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesOrder['shipment']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detail Items Table -->
                <?php if (isset($salesOrder['detailItem']) && is_array($salesOrder['detailItem'])): ?>
                <div class="border-t border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list text-blue-600 mr-2"></i>
                            Detail Items
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($salesOrder['detailItem'] as $item): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['id'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['detailName'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($item['unitPrice'] ?? 0, 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($item['itemCashDiscount'] ?? 0, 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($item['totalPrice'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Summary -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="flex justify-end">
                        <div class="w-64 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                <span class="text-sm text-gray-900"><?php echo number_format($salesOrder['subTotal'] ?? 0, 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Discount:</span>
                                <span class="text-sm text-gray-900"><?php echo number_format($salesOrder['cashDiscount'] ?? 0, 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">PPN 12%:</span>
                                <span class="text-sm text-gray-900"><?php echo number_format($salesOrder['tax1Amount'] ?? 0, 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2">
                                <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                                <span class="text-base font-semibold text-gray-900"><?php echo number_format($salesOrder['totalAmount'] ?? 0, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Sales Order Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Order Found:</strong> <?php echo $salesOrder ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Order ID:</strong> <?php echo htmlspecialchars($salesOrderId); ?></p>
                        <?php if (isset($result['error'])): ?>
                            <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                        <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Sales Order Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Sales Order dengan ID <?php echo htmlspecialchars($salesOrderId); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar Sales Order
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
