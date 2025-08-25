<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$salesReceiptId = $_GET['id'] ?? null;
$searchNumber = $_GET['number'] ?? null;

// Jika ada parameter number, cari berdasarkan number
if ($searchNumber && !$salesReceiptId) {
    // Gunakan list API untuk mencari berdasarkan number
    $listResult = $api->getSalesReceiptList();
    if ($listResult['success'] && isset($listResult['data']['d'])) {
        foreach ($listResult['data']['d'] as $receipt) {
            if (isset($receipt['number']) && $receipt['number'] === $searchNumber) {
                $salesReceiptId = $receipt['id'];
                // Redirect dengan kedua parameter number dan id
                header('Location: detail.php?number=' . urlencode($searchNumber) . '&id=' . urlencode($salesReceiptId));
                exit;
            }
        }
    }
    
    // Jika tidak ditemukan, tetap lanjutkan untuk menampilkan error
    if (!$salesReceiptId) {
        $salesReceiptId = 'not_found_' . $searchNumber;
    }
}

if (!$salesReceiptId && !$searchNumber) {
    // Tidak ada parameter - tidak perlu API call
    $showSearchForm = true;
} else {
    $showSearchForm = false;
    // Gunakan sales receipt detail API
    $result = $api->getSalesReceiptDetail($salesReceiptId);
    $salesReceipt = null;
    $rawResponse = $result; // Simpan raw response dari detail

    if ($result['success'] && isset($result['data']['d'])) {
        $salesReceipt = $result['data']['d'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sales Receipt - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-receipt text-green-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Sales Receipt</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="print_pdf.php?id=<?php echo urlencode($salesReceiptId); ?>" 
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
        <!-- Search Form - Always Show -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-search text-green-600 mr-2"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Cari Sales Receipt</h3>
                </div>
                <form method="GET" class="flex items-center gap-3">
                    <?php if ($salesReceiptId && !str_contains($salesReceiptId, 'not_found')): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($salesReceiptId); ?>">
                    <?php endif; ?>
                    <input type="text" 
                           name="number" 
                           value="<?php echo htmlspecialchars($searchNumber ?? ''); ?>"
                           placeholder="Masukkan nomor receipt..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                           required>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </form>
            </div>
        </div>

        <?php if ($salesReceipt): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-receipt text-green-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Sales Receipt Details</h2>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Receive From</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-user text-gray-400 mr-3"></i>
                                        <span class="text-gray-900">
                                            <?php 
                                            $customerInfo = '';
                                            if (isset($salesReceipt['customer']['customerNo'])) {
                                                $customerInfo .= htmlspecialchars($salesReceipt['customer']['customerNo']);
                                            }
                                            if (isset($salesReceipt['customer']['name'])) {
                                                if ($customerInfo) $customerInfo .= ', ';
                                                $customerInfo .= htmlspecialchars($salesReceipt['customer']['name']);
                                            }
                                            echo $customerInfo ?: 'N/A';
                                            ?>
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesReceipt['transDate'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>

                            </div>
                            
                            <!-- Section 2 - Background Abu-abu -->
                            <div class="bg-gray-100 p-4 rounded-lg space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-university text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesReceipt['bank']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-credit-card text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesReceipt['paymentMethod'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rekonsiliasi</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-check-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900">
                                            <?php echo ($salesReceipt['reconciled'] ?? false) ? 'Yes' : 'No'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 3 - Payment Amount -->
                          
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Voucher No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-alt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesReceipt['number'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Payment</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-money-check text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $totalPayment = $salesReceipt['totalPayment'] ?? 0;
                                            echo 'Rp ' . number_format($totalPayment, 0, ',', '.');
                                        ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-money-bill text-gray-400 mr-3"></i>
                                        <span class="text-gray-900 font-semibold">
                                            <?php 
                                            $amount = $salesReceipt['equivalentAmount'] ?? 0;
                                            echo 'Rp ' . number_format($amount, 0, ',', '.');
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Details Table -->
                <?php if (isset($salesReceipt['detailInvoice']) && is_array($salesReceipt['detailInvoice'])): ?>
                <div class="border-t border-gray-200">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-list mr-2"></i>Invoice Details
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Invoice No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Invoice Date</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total Invoice</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Owing</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Pay</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Discount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Payment</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($salesReceipt['detailInvoice'] as $detail): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <?php echo htmlspecialchars($detail['invoice']['number'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <?php echo htmlspecialchars($detail['invoice']['transDate'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $totalInvoice = $detail['paymentAmount'] ?? 0;
                                            echo 'Rp ' . number_format($totalInvoice, 0, ',', '.');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $owing = $detail['invoice']['owing']['orPayment'] ?? 0;
                                            echo 'Rp ' . number_format($owing, 0, ',', '.');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $payAmount = $detail['paymentAmount'] ?? 0;
                                            echo 'Rp ' . number_format($payAmount, 0, ',', '.');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $discount = 0;
                                            if (isset($detail['detailDiscount']) && is_array($detail['detailDiscount'])) {
                                                foreach ($detail['detailDiscount'] as $discDetail) {
                                                    $discount += $discDetail['amount'] ?? 0;
                                                }
                                            }
                                            echo 'Rp ' . number_format($discount, 0, ',', '.');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right border-b">
                                            <?php 
                                            $payment = $detail['invoicePayment'] ?? 0;
                                            echo 'Rp ' . number_format($payment, 0, ',', '.');
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Summary Section -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Amount:</span>
                            <span class="text-gray-900"><?php 
                                $paymentAmount = $salesReceipt['equivalentAmount'] ?? 0;
                                echo 'Rp ' . number_format($paymentAmount, 0, ',', '.');
                            ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Invoice Paid:</span>
                            <span class="text-gray-900"><?php 
                                $totalPayment = $salesReceipt['totalPayment'] ?? 0;
                                echo 'Rp ' . number_format($totalPayment, 0, ',', '.');
                            ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount:</span>
                            <span class="text-gray-900"><?php 
                                $totalDiscount = $salesReceipt['totalDiscount'] ?? 0;
                                echo 'Rp ' . number_format($totalDiscount, 0, ',', '.');
                            ?></span>
                        </div>
                        <div class="flex justify-between border-t pt-2 font-semibold">
                            <span class="text-gray-900">Over Pay:</span>
                            <span class="text-gray-900"><?php 
                                $overPay = $salesReceipt['overPay'] ?? 0;
                                echo 'Rp ' . number_format($overPay, 0, ',', '.');
                            ?></span>
                        </div>
                    </div>
                </div>

                <!-- Debug Info -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Sales Receipt Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Receipt Found:</strong> <?php echo $salesReceipt ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Receipt ID:</strong> <?php echo htmlspecialchars($salesReceiptId); ?></p>
                        <?php if ($searchNumber): ?>
                            <p><strong>Search Number:</strong> <?php echo htmlspecialchars($searchNumber); ?></p>
                        <?php endif; ?>
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
        <?php elseif (!$salesReceipt && ($salesReceiptId || $searchNumber)): ?>
            <!-- Error State -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Data Tidak Ditemukan</h3>
                    <?php if ($searchNumber): ?>
                        <p class="text-gray-600 mb-4">Sales Receipt dengan nomor "<?php echo htmlspecialchars($searchNumber); ?>" tidak ditemukan.</p>
                    <?php else: ?>
                        <p class="text-gray-600 mb-4">Sales Receipt dengan ID "<?php echo htmlspecialchars($salesReceiptId); ?>" tidak ditemukan atau terjadi error.</p>
                    <?php endif; ?>
                    
                    <?php if (isset($result['error'])): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-red-800"><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                        <?php if (isset($result['http_code'])): ?>
                        <p class="text-red-600 text-sm mt-1">HTTP Code: <?php echo htmlspecialchars($result['http_code']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex gap-4 justify-center">
                        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke List
                        </a>
                        <a href="test_detail_receipt.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-bug mr-2"></i>Debug Data
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- No Parameters - Show Welcome -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-receipt text-green-600 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Sales Receipt Detail</h3>
                    <p class="text-gray-600 mb-4">Gunakan form pencarian di atas untuk mencari receipt berdasarkan nomor.</p>
                    
                    <div class="mt-6">
                        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-list mr-2"></i>Lihat Semua Receipt
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
