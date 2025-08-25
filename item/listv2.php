<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

// Ambil parameter filter dari URL GET
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';
$limit = $_GET['limit'] ?? 100;

// Jika ada filter tanggal, gunakan method filter
if (!empty($startDate) && !empty($endDate)) {
    // Validasi format tanggal
    $startFormatted = DateTime::createFromFormat('d/m/Y', $startDate);
    $endFormatted = DateTime::createFromFormat('d/m/Y', $endDate);
    
    if ($startFormatted && $endFormatted) {
        // Format tanggal untuk API dengan waktu
        $startWithTime = $startDate . ' 00:00:00';
        $endWithTime = $endDate . ' 23:59:59';
        
        $filterParams = [
            'filter.lastUpdate.op' => 'BETWEEN',
            'filter.lastUpdate.val[0]' => $startWithTime,
            'filter.lastUpdate.val[1]' => $endWithTime
        ];
        
        $result = $api->getItemListWithFilter($limit, 1, $filterParams);
    } else {
        // Format tanggal tidak valid, gunakan list biasa
        $result = $api->getItemList($limit);
    }
} else {
    // Tidak ada filter, gunakan list biasa
    $result = $api->getItemList($limit);
}

$items = [];
if ($result['success'] && isset($result['data']['d'])) {
    $items = $result['data']['d'];
}

// Status filter
$isFiltered = !empty($startDate) && !empty($endDate);
$filterStatus = '';
if ($isFiltered) {
    $filterStatus = "Filter: {$startDate} - {$endDate}";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-box text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Barang</h1>
                </div>
                <div class="flex gap-4">
                    <a href="new_item.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add New Item
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
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Data Barang</h2>
                
                <!-- Filter Form -->
                <div class="flex items-center gap-4">
                    <form method="GET" class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Dari:</label>
                            <input type="date" 
                                   name="start_picker" 
                                   id="start_picker"
                                   value="<?php 
                                   if (!empty($startDate)) {
                                       $startFormatted = DateTime::createFromFormat('d/m/Y', $startDate);
                                       echo $startFormatted ? $startFormatted->format('Y-m-d') : '';
                                   }
                                   ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <input type="hidden" name="start" id="start_hidden" value="<?php echo htmlspecialchars($startDate); ?>">
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Sampai:</label>
                            <input type="date" 
                                   name="end_picker" 
                                   id="end_picker"
                                   value="<?php 
                                   if (!empty($endDate)) {
                                       $endFormatted = DateTime::createFromFormat('d/m/Y', $endDate);
                                       echo $endFormatted ? $endFormatted->format('Y-m-d') : '';
                                   }
                                   ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <input type="hidden" name="end" id="end_hidden" value="<?php echo htmlspecialchars($endDate); ?>">
                        </div>
                        
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        
                        <?php if ($isFiltered): ?>
                            <a href="listv2.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                                <i class="fas fa-times mr-1"></i>Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Filter Status -->
            <?php if ($isFiltered): ?>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-filter text-blue-600 mr-2"></i>
                        <span class="text-blue-800 font-medium"><?php echo $filterStatus; ?></span>
                        <span class="ml-2 text-blue-600">
                            (<?php echo count($items); ?> item<?php echo count($items) != 1 ? 's' : ''; ?> ditemukan)
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($items)): ?>
                <!-- Quick Filter Examples -->
                <div class="mb-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 mr-2">Filter cepat:</span>
                    <button type="button" data-days="0" class="quick-filter text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded hover:bg-blue-200 cursor-pointer">
                        Hari ini
                    </button>
                    <button type="button" data-days="7" class="quick-filter text-xs bg-green-100 text-green-800 px-2 py-1 rounded hover:bg-green-200 cursor-pointer">
                        7 hari terakhir
                    </button>
                    <button type="button" data-days="30" class="quick-filter text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded hover:bg-yellow-200 cursor-pointer">
                        30 hari terakhir
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Tersedia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terakhir Diperbarui</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $noUrut = 1;
                            foreach ($items as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $noUrut++; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['no'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['itemTypeName'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <?php 
                                        if (isset($item['availableToSell'])) {
                                            $stok = $item['availableToSell'];
                                            if ($stok > 0) {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">';
                                                echo number_format($stok, 0, ',', '.');
                                                echo '</span>';
                                            } elseif ($stok == 0) {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">';
                                                echo 'Habis';
                                                echo '</span>';
                                            } else {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">';
                                                echo number_format($stok, 0, ',', '.');
                                                echo '</span>';
                                            }
                                        } else {
                                            echo '<span class="text-gray-400 italic">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <?php echo formatCurrency($item['vendorPrice'] ?? 0); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <?php echo formatCurrency($item['unitPrice'] ?? 0); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <button onclick="showPriceLevels(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>')" 
                                           class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-1 rounded-lg text-xs font-medium">
                                            <i class="fas fa-tags mr-1"></i>View Levels
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        if (!empty($item['lastUpdate'])) {
                                            try {
                                                // API mengembalikan format "dd/mm/yyyy HH:mm:ss"
                                                $lastUpdate = DateTime::createFromFormat('d/m/Y H:i:s', $item['lastUpdate']);
                                                
                                                // Jika gagal parsing dengan format lengkap, coba format tanpa detik
                                                if (!$lastUpdate) {
                                                    $lastUpdate = DateTime::createFromFormat('d/m/Y H:i', $item['lastUpdate']);
                                                }
                                                
                                                if ($lastUpdate) {
                                                    echo $lastUpdate->format('d/m/Y H:i');
                                                } else {
                                                    // Jika masih gagal, tampilkan raw value
                                                    echo htmlspecialchars($item['lastUpdate']);
                                                }
                                            } catch (Exception $e) {
                                                // Fallback: tampilkan raw value jika parsing gagal
                                                echo htmlspecialchars($item['lastUpdate']);
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="itemv3.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-900">
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
                    <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600">
                        <?php if ($isFiltered): ?>
                            Tidak ada data barang untuk periode <?php echo $filterStatus; ?>
                        <?php else: ?>
                            Tidak ada data barang.
                        <?php endif; ?>
                    </p>
                    <?php if ($isFiltered): ?>
                        <a href="listv2.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-list mr-2"></i>Tampilkan Semua Data
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Convert date picker values to dd/mm/yyyy format before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const startPicker = document.getElementById('start_picker');
            const endPicker = document.getElementById('end_picker');
            const startHidden = document.getElementById('start_hidden');
            const endHidden = document.getElementById('end_hidden');
            
            // Convert YYYY-MM-DD to DD/MM/YYYY
            if (startPicker.value) {
                const startDate = new Date(startPicker.value);
                const startFormatted = String(startDate.getDate()).padStart(2, '0') + '/' + 
                                     String(startDate.getMonth() + 1).padStart(2, '0') + '/' + 
                                     startDate.getFullYear();
                startHidden.value = startFormatted;
            }
            
            if (endPicker.value) {
                const endDate = new Date(endPicker.value);
                const endFormatted = String(endDate.getDate()).padStart(2, '0') + '/' + 
                                   String(endDate.getMonth() + 1).padStart(2, '0') + '/' + 
                                   endDate.getFullYear();
                endHidden.value = endFormatted;
            }
        });

        // Quick filter functions
        function setQuickFilter(days) {
            const today = new Date();
            const startDate = new Date(today);
            startDate.setDate(today.getDate() - days);
            
            document.getElementById('start_picker').value = startDate.toISOString().split('T')[0];
            document.getElementById('end_picker').value = today.toISOString().split('T')[0];
        }

        // Update quick filter links to use JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Override quick filter links
            const quickFilters = document.querySelectorAll('.quick-filter');
            quickFilters.forEach(filter => {
                filter.addEventListener('click', function(e) {
                    e.preventDefault();
                    const days = parseInt(this.dataset.days);
                    setQuickFilter(days);
                    document.querySelector('form').submit();
                });
            });
        });
    </script>

    <!-- Modal untuk Price Levels -->
    <div id="priceLevelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-96 overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tags text-orange-600 mr-2"></i>
                        Price Levels - <span id="modalItemName">Loading...</span>
                    </h3>
                    <button onclick="closePriceLevelModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div id="priceLevelLoading" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-blue-600 text-2xl mb-2"></i>
                        <p class="text-gray-600">Loading price levels...</p>
                    </div>
                    
                    <div id="priceLevelContent" class="hidden">
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 mb-4">
                            <div class="overflow-x-auto">
                                <nav class="-mb-px flex space-x-2 min-w-max" id="branchTabs">
                                    <!-- Tabs akan diisi via JavaScript -->
                                </nav>
                            </div>
                        </div>
                        
                        <!-- Tab Content -->
                        <div class="overflow-y-auto max-h-64" id="tabContent">
                            <!-- Content akan diisi via JavaScript -->
                        </div>
                    </div>
                    
                    <div id="priceLevelError" class="hidden text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl mb-2"></i>
                        <p class="text-red-600">Failed to load price levels</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Session ID dari PHP
        const SESSION_ID = '<?php echo htmlspecialchars(ACCURATE_SESSION_ID ?? ''); ?>';
        
        // Function untuk format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Function untuk show price levels modal
        async function showPriceLevels(itemId, itemName) {
            const modal = document.getElementById('priceLevelModal');
            const loading = document.getElementById('priceLevelLoading');
            const content = document.getElementById('priceLevelContent');
            const error = document.getElementById('priceLevelError');
            const modalTitle = document.getElementById('modalItemName');
            const tabsContainer = document.getElementById('branchTabs');
            const tabContent = document.getElementById('tabContent');
            
            // Reset modal state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            error.classList.add('hidden');
            modalTitle.textContent = itemName;
            
            try {
                console.log('Fetching price levels for item ID:', itemId);
                
                const response = await fetch(`../api/item/price-levels.php?id=${itemId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (!result.success) {
                    throw new Error('API Error: ' + (result.error || 'Unknown error'));
                }
                
                const data = result.data;
                modalTitle.textContent = `${data.itemName} (#${data.itemNo})`;
                
                // Build tab interface
                if (!data.groupedPrices || data.groupedPrices.length === 0) {
                    tabContent.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <p>No price levels found for this item</p>
                        </div>
                    `;
                    tabsContainer.innerHTML = '';
                } else {
                    // Build tabs
                    let tabsHtml = '';
                    data.groupedPrices.forEach((group, index) => {
                        const isActive = index === 0;
                        const branchId = `branch-${group.branchId || index}`;
                        tabsHtml += `
                            <button onclick="switchTab('${branchId}')" 
                                    id="tab-${branchId}"
                                    class="tab-button ${isActive ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'} 
                                           whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm flex items-center rounded-t-lg transition-all duration-200 min-w-0 flex-shrink-0">
                                <i class="fas fa-building mr-1 text-xs"></i>
                                <span class="truncate max-w-32">${group.branchName}</span>
                            </button>
                        `;
                    });
                    tabsContainer.innerHTML = tabsHtml;
                    
                    // Build tab content
                    let contentHtml = '';
                    data.groupedPrices.forEach((group, index) => {
                        const isActive = index === 0;
                        const branchId = `branch-${group.branchId || index}`;
                        contentHtml += `
                            <div id="content-${branchId}" class="tab-content ${isActive ? '' : 'hidden'}">
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <table class="min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Category</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                        `;
                        
                        group.prices.forEach(price => {
                            contentHtml += `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">${price.categoryName}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">${formatCurrency(price.price)}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${price.effectiveDate}</td>
                                </tr>
                            `;
                        });
                        
                        contentHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    });
                    
                    tabContent.innerHTML = contentHtml;
                }
                
                // Show content
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                
            } catch (err) {
                console.error('Error loading price levels:', err);
                loading.classList.add('hidden');
                error.classList.remove('hidden');
            }
        }

        // Function untuk switch tab
        function switchTab(branchId) {
            // Hide all tab contents
            const allTabContents = document.querySelectorAll('.tab-content');
            allTabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            const allTabs = document.querySelectorAll('.tab-button');
            allTabs.forEach(tab => {
                tab.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            const selectedContent = document.getElementById(`content-${branchId}`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
            
            // Add active state to selected tab
            const selectedTab = document.getElementById(`tab-${branchId}`);
            if (selectedTab) {
                selectedTab.classList.remove('border-transparent', 'text-gray-500');
                selectedTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
            }
        }

        // Function untuk close modal
        function closePriceLevelModal() {
            document.getElementById('priceLevelModal').classList.add('hidden');
        }

        // Close modal ketika klik outside
        document.getElementById('priceLevelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePriceLevelModal();
            }
        });
    </script>
</body>
</html>
