<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$purchaseInvoiceId = $_GET['id'] ?? null;

if (!$purchaseInvoiceId) {
    header('Location: index.php');
    exit;
}

// Gunakan purchase invoice detail API
$result = $api->getPurchaseInvoiceDetail($purchaseInvoiceId);
$purchaseInvoice = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data'])) {
    // Cek apakah ada struktur 'd' seperti di sales invoice
    if (isset($result['data']['d'])) {
        $purchaseInvoice = $result['data']['d'];
    } else {
        $purchaseInvoice = $result['data'];
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
    <title>Detail Purchase Invoice - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice-dollar text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Purchase Invoice</h1>
                </div>
                <div class="flex gap-4">
                    <a href="print_pi_pdf.php?id=<?php echo urlencode($purchaseInvoiceId); ?>" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-print mr-2"></i>Print PDF
                    </a>
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
        <?php if ($purchaseInvoice): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-file-invoice-dollar text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Purchase Invoice Details</h2>
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
                                            $vendorName = $purchaseInvoice['vendor']['name'] ?? 'N/A';
                                            $vendorNo = $purchaseInvoice['vendor']['vendorNo'] ?? '';
                                            echo htmlspecialchars($vendorName . ($vendorNo ? ' ' . $vendorNo : ''));
                                        ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rekening</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-university text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $bankAccount = $purchaseInvoice['vendorBankAccount']['bankAccount'] ?? '';
                                            $bankName = $purchaseInvoice['vendorBankAccount']['bankName'] ?? '';
                                            $accountName = $purchaseInvoice['vendorBankAccount']['bankAccountName'] ?? '';
                                            $rekening = [];
                                            if ($bankAccount) $rekening[] = $bankAccount;
                                            if ($bankName) $rekening[] = $bankName;
                                            if ($accountName) $rekening[] = $accountName;
                                            echo !empty($rekening) ? htmlspecialchars(implode(' ', $rekening)) : 'N/A';
                                        ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['transDate'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $statusName = $purchaseInvoice['statusName'] ?? 'N/A';
                                            $status = $purchaseInvoice['status'] ?? '';
                                            echo htmlspecialchars($statusName . ($status ? ' ' . $status : ''));
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Form Number</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-alt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['number'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Faktur</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-receipt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['billNumber'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Syarat Pembayaran</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-handshake text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['paymentTerm']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch ID</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-code-branch text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['branchId'] ?? 'N/A'); ?></span>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Kode Barang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Nama Barang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Gudang</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Kts</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Satuan</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Harga</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Diskon</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php if (isset($purchaseInvoice['detailItem']) && is_array($purchaseInvoice['detailItem'])): ?>
                                        <?php foreach ($purchaseInvoice['detailItem'] as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['item']['name'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['warehouse']['name'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($item['item']['unit1']['name'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php 
                                                $unitPrice = $item['item']['unitPrice'] ?? 0;
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
                                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No items found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Expense Table Section -->
                <div class="border-t border-gray-200">
                    <div class="px-6 py-4 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Expenses</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Nama Biaya</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Kode</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php if (isset($purchaseInvoice['detailExpense']) && is_array($purchaseInvoice['detailExpense'])): ?>
                                        <?php foreach ($purchaseInvoice['detailExpense'] as $expense): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($expense['expenseName'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($expense['account']['no'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                                <?php 
                                                $expenseAmount = $expense['expenseAmount'] ?? 0;
                                                echo $expenseAmount == floor($expenseAmount) ? number_format($expenseAmount, 0) : number_format($expenseAmount, 2);
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <?php echo htmlspecialchars($expense['expenseNotes'] ?? 'N/A'); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No expenses found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Section -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left Side - Tax and Document Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pajak</label>
                                <div class="flex items-center p-3 bg-white rounded-lg">
                                    <i class="fas fa-receipt text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php 
                                        $taxInfo = '';
                                        if (isset($purchaseInvoice['detailTax']) && is_array($purchaseInvoice['detailTax']) && !empty($purchaseInvoice['detailTax'])) {
                                            $firstTax = $purchaseInvoice['detailTax'][0];
                                            $taxName = $firstTax['tax']['taxInfo'] ?? '';
                                            $taxDate = $purchaseInvoice['taxDate'] ?? '';
                                            $taxInfo = $taxName . ($taxDate ? ' ' . $taxDate : '');
                                        }
                                        echo htmlspecialchars($taxInfo ?: 'N/A');
                                    ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                                <div class="flex items-center p-3 bg-white rounded-lg">
                                    <i class="fas fa-file-text text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php 
                                        $documentCode = $purchaseInvoice['documentCode'] ?? '';
                                        $taxNumber = $purchaseInvoice['taxNumber'] ?? '';
                                        $docInfo = $documentCode . ($taxNumber ? ' ' . $taxNumber : '');
                                        echo htmlspecialchars($docInfo ?: 'N/A');
                                    ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pengiriman</label>
                                <div class="flex items-center p-3 bg-white rounded-lg">
                                    <i class="fas fa-shipping-fast text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($purchaseInvoice['shipDate'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Amount Summary -->
                        <div class="w-full max-w-lg ml-auto">
                            <div class="bg-white rounded-lg p-6 shadow-sm border">
                                <h4 class="font-semibold text-gray-900 mb-4 text-center border-b pb-2">Amount Summary</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600 font-medium">Subtotal:</span>
                                        <span class="text-gray-900 font-semibold"><?php 
                                            $subTotal = $purchaseInvoice['subTotal'] ?? 0;
                                            echo 'Rp ' . ($subTotal == floor($subTotal) ? number_format($subTotal, 0) : number_format($subTotal, 2));
                                        ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600 font-medium">Expense:</span>
                                        <span class="text-gray-900 font-semibold"><?php 
                                            $totalExpense = $purchaseInvoice['totalExpense'] ?? 0;
                                            echo 'Rp ' . ($totalExpense == floor($totalExpense) ? number_format($totalExpense, 0) : number_format($totalExpense, 2));
                                        ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600 font-medium">Discount:</span>
                                        <span class="text-gray-900 font-semibold"><?php 
                                            $cashDiscount = $purchaseInvoice['cashDiscount'] ?? 0;
                                            echo 'Rp ' . ($cashDiscount == floor($cashDiscount) ? number_format($cashDiscount, 0) : number_format($cashDiscount, 2));
                                        ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600 font-medium">PPN:</span>
                                        <span class="text-gray-900 font-semibold"><?php 
                                            $tax1Amount = $purchaseInvoice['tax1Amount'] ?? 0;
                                            echo 'Rp ' . ($tax1Amount == floor($tax1Amount) ? number_format($tax1Amount, 0) : number_format($tax1Amount, 2));
                                        ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 bg-blue-50 rounded-lg px-4 border-2 border-blue-200">
                                        <span class="text-blue-900 font-bold text-lg">Total:</span>
                                        <span class="text-blue-900 font-bold text-lg"><?php 
                                            $totalAmount = $purchaseInvoice['totalAmount'] ?? 0;
                                            echo 'Rp ' . ($totalAmount == floor($totalAmount) ? number_format($totalAmount, 0) : number_format($totalAmount, 2));
                                        ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Purchase Invoice Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Purchase Invoice Found:</strong> <?php echo $purchaseInvoice ? 'Yes' : 'No'; ?></p>
                        <p><strong>Purchase Invoice ID:</strong> <?php echo htmlspecialchars($purchaseInvoiceId); ?></p>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Purchase Invoice Not Found</h3>
                    <p class="text-gray-600 mb-4">Purchase Invoice dengan ID <?php echo htmlspecialchars($purchaseInvoiceId); ?> tidak ditemukan.</p>
                    
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
                    <p><strong>Purchase Invoice ID:</strong> <?php echo htmlspecialchars($purchaseInvoiceId); ?></p>
                    <p><strong>API Response:</strong></p>
                    <pre class="mt-2 text-xs bg-white p-3 rounded border overflow-x-auto"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
