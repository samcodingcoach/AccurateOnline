<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$purchaseOrderId = $_GET['id'] ?? null;

if (!$purchaseOrderId) {
    header('Location: index.php');
    exit;
}

// Gunakan purchase order detail API
$result = $api->getPurchaseOrderDetail($purchaseOrderId);
$purchaseOrder = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data'])) {
    // Cek apakah ada struktur 'd' seperti di sales invoice
    if (isset($result['data']['d'])) {
        $purchaseOrder = $result['data']['d'];
    } else {
        $purchaseOrder = $result['data'];
    }
}

// Debug: Tampilkan struktur data untuk development
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<pre>";
    echo "=== DEBUG MODE ===\n";
    echo "Result success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "Result keys: " . implode(', ', array_keys($result)) . "\n";
    if (isset($result['data'])) {
        echo "Data type: " . gettype($result['data']) . "\n";
        if (is_array($result['data'])) {
            echo "Data keys: " . implode(', ', array_keys($result['data'])) . "\n";
        }
    }
    echo "\nFull response:\n";
    print_r($result);
    echo "</pre>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Purchase Order - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Purchase Order</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="print_po_pdf.php?id=<?php echo urlencode($purchaseOrderId); ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg" 
                       target="_blank">
                        <i class="fas fa-print mr-2"></i>Print PDF
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
        <?php if ($purchaseOrder): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Purchase Order Details</h2>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-building text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $vendorName = $purchaseOrder['vendor']['name'] ?? 'N/A';
                                            $vendorNo = $purchaseOrder['vendor']['vendorNo'] ?? '';
                                            echo htmlspecialchars($vendorName . ($vendorNo ? ' - ' . $vendorNo : ''));
                                        ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['transDate'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['statusName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">History</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-history text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $historyNumbers = [];
                                            if (isset($purchaseOrder['processHistory']) && is_array($purchaseOrder['processHistory'])) {
                                                foreach ($purchaseOrder['processHistory'] as $history) {
                                                    if (isset($history['historyNumber'])) {
                                                        $historyNumbers[] = $history['historyNumber'];
                                                    }
                                                }
                                            }
                                            echo !empty($historyNumbers) ? htmlspecialchars(implode(', ', $historyNumbers)) : 'N/A';
                                        ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">DP Amount</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-money-bill text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $dpAmount = $purchaseOrder['totalDownPayment'] ?? 0;
                                            echo $dpAmount == floor($dpAmount) ? number_format($dpAmount, 0) : number_format($dpAmount, 2);
                                        ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">PO Number</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-alt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['number'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-handshake text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['paymentTerm']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Exp Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar-times text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['dateField1'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Printed</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-print text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseOrder['printUserName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Items Table Section -->
                <div class="border-t border-gray-200">
                    <div class="px-6 py-4 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Item Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Code</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Unit</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Price Unit</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Discount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php if (isset($purchaseOrder['detailItem']) && is_array($purchaseOrder['detailItem'])): ?>
                                        <?php foreach ($purchaseOrder['detailItem'] as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['item']['name'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['availableItemUnit']['name'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php 
                                                $unitPrice = $item['unitPrice'] ?? 0;
                                                echo $unitPrice == floor($unitPrice) ? number_format($unitPrice, 0) : number_format($unitPrice, 2);
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php 
                                                $discount = $item['itemCashDiscount'] ?? 0;
                                                echo $discount == floor($discount) ? number_format($discount, 0) : number_format($discount, 2);
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right border-b">
                                                <?php 
                                                $totalPrice = $item['totalPrice'] ?? 0;
                                                echo $totalPrice == floor($totalPrice) ? number_format($totalPrice, 0) : number_format($totalPrice, 2);
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No items found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Section -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="max-w-md ml-auto">
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-900">Amount Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="text-gray-900"><?php 
                                        $subTotal = $purchaseOrder['subTotal'] ?? 0;
                                        echo $subTotal == floor($subTotal) ? number_format($subTotal, 0) : number_format($subTotal, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Expense:</span>
                                    <span class="text-gray-900"><?php 
                                        $totalExpense = $purchaseOrder['totalExpense'] ?? 0;
                                        echo $totalExpense == floor($totalExpense) ? number_format($totalExpense, 0) : number_format($totalExpense, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="text-gray-900"><?php 
                                        $cashDiscount = $purchaseOrder['cashDiscount'] ?? 0;
                                        echo $cashDiscount == floor($cashDiscount) ? number_format($cashDiscount, 0) : number_format($cashDiscount, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">PPN:</span>
                                    <span class="text-gray-900"><?php 
                                        $tax1Amount = $purchaseOrder['tax1Amount'] ?? 0;
                                        echo $tax1Amount == floor($tax1Amount) ? number_format($tax1Amount, 0) : number_format($tax1Amount, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between border-t pt-2 font-semibold">
                                    <span class="text-gray-900">Total:</span>
                                    <span class="text-gray-900"><?php 
                                        $totalAmount = $purchaseOrder['totalAmount'] ?? 0;
                                        echo $totalAmount == floor($totalAmount) ? number_format($totalAmount, 0) : number_format($totalAmount, 2);
                                    ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Purchase Order Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Purchase Order Found:</strong> <?php echo $purchaseOrder ? 'Yes' : 'No'; ?></p>
                        <p><strong>Purchase Order ID:</strong> <?php echo htmlspecialchars($purchaseOrderId); ?></p>
                        <?php if (isset($result['message']) && !$result['success']): ?>
                            <p><strong>Error:</strong> <?php echo htmlspecialchars($result['message']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Show Raw Response for debugging -->
                    <details class="mt-4">
                        <summary class="cursor-pointer font-medium">Raw API Response</summary>
                        <pre class="mt-2 text-xs bg-white p-3 rounded border overflow-x-auto"><?php echo htmlspecialchars(json_encode($rawResponse, JSON_PRETTY_PRINT)); ?></pre>
                    </details>
                </div>
            </div>
            
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Purchase Order Not Found</h3>
                    <p class="text-gray-600 mb-4">Purchase Order dengan ID <?php echo htmlspecialchars($purchaseOrderId); ?> tidak ditemukan.</p>
                    
                    <?php if (isset($result['message'])): ?>
                        <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                            <p class="text-red-700 text-sm"><?php echo htmlspecialchars($result['message']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-center gap-4">
                        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke List
                        </a>
                        <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Debug Info for error case -->
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                <div class="text-sm">
                    <p><strong>Purchase Order ID:</strong> <?php echo htmlspecialchars($purchaseOrderId); ?></p>
                    <p><strong>API Response:</strong></p>
                    <pre class="mt-2 text-xs bg-white p-3 rounded border overflow-x-auto"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
