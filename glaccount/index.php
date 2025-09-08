<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
// Mengambil parameter halaman dari URL, default ke halaman 1 jika tidak ada
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = 50; // Jumlah item per halaman

// Menyiapkan parameter untuk API call
$params = [
    'sp.page' => $page,
    'sp.pageSize' => $pageSize
];

$result = $api->getGlAccountList($params);
$accounts = [];
$pagination = null;

if ($result['success'] && isset($result['data']['d'])) {
    $accounts = $result['data']['d'];
    $pagination = $result['data']['sp'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun GL - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-book text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Akun Perkiraan</h1>
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
            <h2 class="text-xl font-semibold mb-4">Data Akun Perkiraan</h2>
            
            <?php if (!empty($accounts)): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($accounts); ?> dari total <?php echo $pagination['rowCount']; ?> akun. Halaman <?php echo $pagination['page']; ?> dari <?php echo $pagination['pageCount']; ?>.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Akun</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Akun</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Akun</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($accounts as $account): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($account['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($account['no'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($account['name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($account['accountTypeName'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <?php echo number_format($account['balance'] ?? 0, 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail_coa.php?id=<?php echo $account['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex justify-between items-center">
                    <div>
                        <?php if ($pagination && $pagination['page'] > 1): ?>
                            <a href="?page=<?php echo $pagination['page'] - 1; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                                <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($pagination && $pagination['page'] < $pagination['pageCount']): ?>
                            <a href="?page=<?php echo $pagination['page'] + 1; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <p class="text-gray-600">Tidak ada data akun perkiraan.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
