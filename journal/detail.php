<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$journalId = $_GET['id'] ?? null;

if (!$journalId) {
    header('Location: index.php');
    exit;
}

// Gunakan journal voucher detail API
$result = $api->getJournalVoucherDetail($journalId);
$journal = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $journal = $result['data']['d'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Journal Voucher - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Journal Voucher</h1>
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
        <?php if ($journal): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-file-invoice text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Journal Voucher Details</h2>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-hashtag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($journal['number'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-400 mr-3">ID</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($journal['id'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($journal['transDate'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Transaksi</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-receipt text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($journal['transNumber'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-building text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php 
                                        // Get branch name using branchId
                                        $branchName = 'N/A';
                                        if (isset($journal['branchId'])) {
                                            // Get branch detail using API
                                            $branchResult = $api->getBranchDetail($journal['branchId']);
                                            if ($branchResult['success'] && isset($branchResult['data']['d']['name'])) {
                                                $branchName = $branchResult['data']['d']['name'];
                                            } else {
                                                $branchName = 'Branch ID: ' . htmlspecialchars($journal['branchId']);
                                            }
                                        }
                                        echo htmlspecialchars($branchName);
                                    ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Description -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Deskripsi</h3>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900"><?php echo htmlspecialchars($journal['description'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Section Detail Items -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Item Jurnal</h3>
                        
                        <?php if (isset($journal['detailJournalVoucher']) && is_array($journal['detailJournalVoucher']) && !empty($journal['detailJournalVoucher'])): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akun</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        $totalDebit = 0;
                                        $totalCredit = 0;
                                        foreach ($journal['detailJournalVoucher'] as $index => $detail): 
                                            $debitAmount = $detail['debitAmount'] ?? 0;
                                            $creditAmount = $detail['creditAmount'] ?? 0;
                                            $totalDebit += $debitAmount;
                                            $totalCredit += $creditAmount;
                                        ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $index + 1; ?>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-900">
                                                    <div class="font-medium"><?php echo htmlspecialchars($detail['accountNameRef'] ?? 'N/A'); ?></div>
                                                    <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($detail['accountNoRef'] ?? 'N/A'); ?></div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo number_format($debitAmount, 2, ',', '.'); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo number_format($creditAmount, 2, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <!-- Total Row -->
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">
                                                Total
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo number_format($totalDebit, 2, ',', '.'); ?>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo number_format($totalCredit, 2, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-file-invoice text-4xl text-gray-400"></i>
                                <p class="mt-4 text-gray-600">Tidak ada detail item jurnal yang ditemukan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Journal Voucher Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Journal Found:</strong> <?php echo $journal ? 'Yes' : 'No'; ?></p>
                        <p><strong>Journal ID:</strong> <?php echo htmlspecialchars($journalId); ?></p>
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Journal Voucher Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Journal Voucher dengan ID <?php echo htmlspecialchars($journalId); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar Journal Voucher
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>