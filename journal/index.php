<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getJournalVoucherList();
$journalVouchers = [];

if ($result['success'] && isset($result['data']['d'])) {
    $journalVouchers = $result['data']['d'];
    
    // Sort journal vouchers by ID descending
    usort($journalVouchers, function($a, $b) {
        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
    });
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Journal Voucher - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Journal Voucher</h1>
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
            <h2 class="text-xl font-semibold mb-4">Data Journal Voucher</h2>
            
            <?php if (!empty($journalVouchers)): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($journalVouchers); ?> journal voucher.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Transaksi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($journalVouchers as $voucher): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($voucher['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($voucher['number'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($voucher['transDate'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($voucher['transNumber'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($voucher['description'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail.php?id=<?php echo $voucher['id']; ?>" class="text-blue-600 hover:text-blue-900">
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
                    <i class="fas fa-file-invoice text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Tidak ada data journal voucher.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mt-2"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>