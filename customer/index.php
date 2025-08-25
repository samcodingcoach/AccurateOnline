<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getCustomerList();
$customers = [];

if ($result['success'] && isset($result['data']['d'])) {
    $customers = $result['data']['d'];
    
    // Sort customers by ID ascending
    usort($customers, function($a, $b) {
        return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
    });
}

// Helper function untuk mendapatkan status aktif
function getCustomerStatus($customer) {
    $suspended = $customer['suspended'] ?? false;
    return !$suspended; // false = aktif, true = tidak aktif
}

// Helper function untuk mendapatkan status badge
function getStatusBadge($customer) {
    $isActive = getCustomerStatus($customer);
    if ($isActive) {
        return '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aktif</span>';
    } else {
        return '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Tidak Aktif</span>';
    }
}

// Helper function untuk mendapatkan customer type badge
function getCustomerTypeBadge($customer) {
    $typeName = $customer['customerTypeName'] ?? 'Unknown';
    return '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">' . htmlspecialchars($typeName) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Customer - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-users text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Customer</h1>
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
        <?php if (!empty($customers)): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Header Table -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-users text-blue-600 mr-2"></i>
                            Customer List
                        </h2>
                        <span class="text-sm text-gray-500">Total: <?php echo count($customers); ?> customer</span>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($customers as $customer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium"><?php echo htmlspecialchars($customer['id'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="font-medium"><?php echo htmlspecialchars($customer['name'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($customer['customerNo'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        if (!empty($customer['createDate'])) {
                                            try {
                                                // Parse date dengan berbagai format yang mungkin
                                                $createDate = DateTime::createFromFormat('d/m/Y H:i:s', $customer['createDate']);
                                                
                                                // Coba format lain jika gagal
                                                if (!$createDate) {
                                                    $createDate = DateTime::createFromFormat('Y-m-d H:i:s', $customer['createDate']);
                                                }
                                                
                                                if (!$createDate) {
                                                    $createDate = DateTime::createFromFormat('d/m/Y', $customer['createDate']);
                                                }
                                                
                                                if ($createDate) {
                                                    echo $createDate->format('d/m/Y');
                                                } else {
                                                    echo htmlspecialchars($customer['createDate']);
                                                }
                                            } catch (Exception $e) {
                                                echo htmlspecialchars($customer['createDate']);
                                            }
                                        } else {
                                            echo '<span class="text-gray-400 italic">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="detail.php?id=<?php echo urlencode($customer['id']); ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Customer</h2>
                    <p class="text-gray-600 mb-4">Belum ada data customer yang dapat ditampilkan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Debug Info -->
        <div class="mt-8 bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium mb-2">Debug Info</h3>
            <div class="text-sm">
                <p><strong>Data Source:</strong> Customer List API (/list.do)</p>
                <p><strong>API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                <p><strong>Customers Found:</strong> <?php echo count($customers); ?></p>
                <?php if (isset($result['error'])): ?>
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                <?php endif; ?>
            </div>
            
            <details class="mt-4">
                <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                    echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
                ?></pre>
            </details>
        </div>
    </main>
</body>
</html>
