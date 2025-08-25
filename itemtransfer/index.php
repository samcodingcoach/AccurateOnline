<?php
require_once __DIR__ . '/../bootstrap.php';

// Get filter values from GET request
$numberFilter = $_GET['number'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo = $_GET['dateTo'] ?? '';

$filters = [];
if (!empty($numberFilter)) {
    $filters['filter.number.op'] = 'LIKE';
    $filters['filter.number.val'] = $numberFilter;
}
if (!empty($dateFrom) && !empty($dateTo)) {
    // Pastikan format tanggal DD/MM/YYYY
    $formattedDateFrom = date('d/m/Y', strtotime($dateFrom));
    $formattedDateTo = date('d/m/Y', strtotime($dateTo));
    $filters['filter.transDate.op'] = 'BETWEEN';
    $filters['filter.transDate.val[0]'] = $formattedDateFrom;
    $filters['filter.transDate.val[1]'] = $formattedDateTo;
}

$api = new AccurateAPI();
$result = $api->getItemTransferList(100, 1, $filters); // Fetch up to 100 records with filters
$transfers = [];

if ($result['success'] && isset($result['data']['d'])) {
    $transfers = $result['data']['d'];
    
    // Sort transfers by ID ascending
    usort($transfers, function($a, $b) {
        return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
    });
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Item Transfer - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exchange-alt text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Item Transfer</h1>
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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            
            <!-- Filter Form -->
            <div class="mb-6 pb-4 border-b">
                <h2 class="text-xl font-semibold mb-4">Filter Data</h2>
                <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="number" class="block text-sm font-medium text-gray-700">Cari Nomor</label>
                        <input type="text" name="number" id="number" value="<?php echo htmlspecialchars($numberFilter); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Masukkan nomor...">
                    </div>
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="date" name="dateFrom" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" name="dateTo" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="index.php" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <h2 class="text-xl font-semibold mb-4">Hasil Pencarian</h2>
            
            <?php if (!empty($transfers)):
 ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($transfers); ?> transfer.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($transfers as $transfer):
 ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($transfer['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($transfer['number'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($transfer['transDate'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail.php?id=<?php echo $transfer['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Tidak ada data item transfer yang cocok dengan filter Anda.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
