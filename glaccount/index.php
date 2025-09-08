<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

// Pagination and filter parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = 20; // Smaller page size for better display
$accountType = $_GET['type'] ?? '';

// Prepare parameters for API call
$params = [
    'sp.page' => $page,
    'sp.pageSize' => $pageSize,
];

if (!empty($accountType)) {
    $params['type'] = $accountType;
}

$result = $api->getGlAccountList($params);
$accounts = [];
$pagination = null;

if ($result['success'] && isset($result['data']['d'])) {
    $accounts = $result['data']['d'];
    $pagination = $result['data']['sp'];
}

// Account types for the filter dropdown
$accountTypes = [
    'ACCOUNT_PAYABLE' => 'Account Payable',
    'ACCOUNT_RECEIVABLE' => 'Account Receivable',
    'ACCUMULATED_DEPRECIATION' => 'Accumulated Depreciation',
    'CASH_BANK' => 'Cash/Bank',
    'COGS' => 'Cost of Goods Sold',
    'EQUITY' => 'Equity',
    'EXPENSE' => 'Expense',
    'FIXED_ASSET' => 'Fixed Asset',
    'INVENTORY' => 'Inventory',
    'LONG_TERM_LIABILITY' => 'Long Term Liability',
    'OTHER_ASSET' => 'Other Asset',
    'OTHER_CURRENT_ASSET' => 'Other Current Asset',
    'OTHER_CURRENT_LIABILITY' => 'Other Current Liability',
    'OTHER_EXPENSE' => 'Other Expense',
    'OTHER_INCOME' => 'Other Income',
    'REVENUE' => 'Revenue',
];

function build_query_string($key, $value) {
    $query = $_GET;
    $query[$key] = $value;
    if ($key !== 'page') {
        $query['page'] = 1;
    }
    return http_build_query($query);
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
    <style>
        .custom-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><path d="M6 9l6 6 6-6"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
    </style>
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
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Data Akun Perkiraan</h2>
                <form id="filterForm" action="index.php" method="GET" class="flex items-center gap-2">
                    <select name="type" id="typeFilter" class="custom-select bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Semua Tipe Akun</option>
                        <?php foreach ($accountTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($accountType === $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
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
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($account['id'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($account['no'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($account['name'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($account['accountTypeName'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right"><?php echo number_format($account['balance'] ?? 0, 2, ',', '.'); ?></td>
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
                <nav class="mt-6 flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                    <div class="flex w-0 flex-1">
                        <?php if ($pagination && $pagination['page'] > 1): ?>
                            <a href="?<?php echo build_query_string('page', $pagination['page'] - 1); ?>" class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                                <i class="fas fa-arrow-left mr-3"></i>
                                Sebelumnya
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden md:flex">
                        <?php 
                        if ($pagination) {
                            $startPage = max(1, $pagination['page'] - 2);
                            $endPage = min($pagination['pageCount'], $pagination['page'] + 2);

                            if ($startPage > 1) {
                                echo '<a href="?'.build_query_string('page', 1).'" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700">1</a>';
                                if ($startPage > 2) {
                                    echo '<span class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500">...</span>';
                                }
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i == $pagination['page']) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                                echo '<a href="?'.build_query_string('page', $i).'" class="inline-flex items-center border-t-2 '.$activeClass.' px-4 pt-4 text-sm font-medium">'.$i.'</a>';
                            }

                            if ($endPage < $pagination['pageCount']) {
                                if ($endPage < $pagination['pageCount'] - 1) {
                                    echo '<span class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500">...</span>';
                                }
                                echo '<a href="?'.build_query_string('page', $pagination['pageCount']).'" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700">'.$pagination['pageCount'].'</a>';
                            }
                        }
                        ?>
                    </div>
                    <div class="flex w-0 flex-1 justify-end">
                        <?php if ($pagination && $pagination['page'] < $pagination['pageCount']): ?>
                            <a href="?<?php echo build_query_string('page', $pagination['page'] + 1); ?>" class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                                Selanjutnya
                                <i class="fas fa-arrow-right ml-3"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>

            <?php else: ?>
                <p class="text-gray-600">Tidak ada data akun perkiraan yang cocok dengan filter.</p>
            <?php endif; ?>
        </div>
    </main>
    <script>
        document.getElementById('typeFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
</body>
</html>
