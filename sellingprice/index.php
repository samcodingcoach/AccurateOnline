<?php
require_once __DIR__ . '/../bootstrap.php';

// Pastikan session sudah dimulai untuk mengambil flash message
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil flash message jika ada, lalu hapus dari session
$flash_message = $_SESSION['flash_message'] ?? null;
$flash_message_type = $_SESSION['flash_message_type'] ?? 'success';
if ($flash_message) {
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}

$api = new AccurateAPI();
// Memanggil fungsi untuk mendapatkan daftar selling price adjustment
$result = $api->getSellingPriceAdjustmentList(); 
$sellingPrices = [];

if ($result['success'] && isset($result['data']['d'])) {
    $sellingPrices = $result['data']['d'];
    
    // Urutkan berdasarkan ID
    usort($sellingPrices, function($a, $b) {
        return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
    });
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Selling Price Adjustment - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Selling Price Adjustment</h1>
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

        <?php if ($flash_message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flash_message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flash_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                <?php echo $flash_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Data Selling Price Adjustment</h2>
            
            <?php if (!empty($sellingPrices)):
            ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($sellingPrices); ?> data.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($sellingPrices as $price): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($price['number'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($price['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($price['transDate'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($price['salesAdjustmentType'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="detail.php?number=<?php echo htmlspecialchars($price['number'] ?? ''); ?>" class="text-blue-600 hover:text-blue-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else:
            ?>
                <div class="text-center py-8">
                    <i class="fas fa-box-open text-4xl text-gray-400"></i>
                    <p class="mt-4 text-gray-600">Tidak ada data selling price adjustment yang ditemukan.</p>
                    <p class="text-sm text-gray-500">Coba periksa koneksi API atau tambahkan data baru.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>