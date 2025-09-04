<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();

// Ambil parameter filter dari URL GET
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';
$searchName = $_GET['search'] ?? '';
$limit = $_GET['limit'] ?? 100;

// Jika ada filter tanggal atau pencarian nama, gunakan method filter
if ((!empty($startDate) && !empty($endDate)) || !empty($searchName)) {
    $filterParams = [];
    
    // Filter tanggal
    if (!empty($startDate) && !empty($endDate)) {
        // Validasi format tanggal
        $startFormatted = DateTime::createFromFormat('d/m/Y', $startDate);
        $endFormatted = DateTime::createFromFormat('d/m/Y', $endDate);
        
        if ($startFormatted && $endFormatted) {
            // Format tanggal untuk API dengan waktu
            $startWithTime = $startDate . ' 00:00:00';
            $endWithTime = $endDate . ' 23:59:59';
            
            $filterParams['filter.lastUpdate.op'] = 'BETWEEN';
            $filterParams['filter.lastUpdate.val[0]'] = $startWithTime;
            $filterParams['filter.lastUpdate.val[1]'] = $endWithTime;
        }
    }
    
    // Filter pencarian nama
    if (!empty($searchName)) {
        $filterParams['filter.name.op'] = 'LIKE';
        $filterParams['filter.name.val'] = $searchName;
    }
    
    $result = $api->getItemListWithFilter($limit, 1, $filterParams);
} else {
    // Tidak ada filter, gunakan list biasa
    $result = $api->getItemList($limit);
}

$items = [];
if ($result['success'] && isset($result['data']['d'])) {
    $items = $result['data']['d'];
}

// Status filter
$isFiltered = (!empty($startDate) && !empty($endDate)) || !empty($searchName);
$filterStatus = '';
if (!empty($startDate) && !empty($endDate)) {
    $filterStatus = "Filter: {$startDate} - {$endDate}";
}
if (!empty($searchName)) {
    if (!empty($filterStatus)) {
        $filterStatus .= " & Nama: {$searchName}";
    } else {
        $filterStatus = "Nama: {$searchName}";
    }
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
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3">
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
                        
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Nama:</label>
                            <input type="text" 
                                   name="search" 
                                   id="search"
                                   value="<?php echo htmlspecialchars($searchName); ?>"
                                   placeholder="Cari nama barang..."
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <!-- Card Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $noUrut = 1;
                    foreach ($items as $item): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 truncate"><?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['no'] ?? 'N/A'); ?></p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                        if (isset($item['availableToSell'])) {
                                            $stok = $item['availableToSell'];
                                            if ($stok > 0) {
                                                echo 'bg-green-100 text-green-800';
                                            } elseif ($stok == 0) {
                                                echo 'bg-red-100 text-red-800';
                                            } else {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            }
                                        } else {
                                            echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php 
                                        if (isset($item['availableToSell'])) {
                                            $stok = $item['availableToSell'];
                                            if ($stok > 0) {
                                                echo number_format($stok, 0, ',', '.');
                                            } elseif ($stok == 0) {
                                                echo 'Habis';
                                            } else {
                                                echo number_format($stok, 0, ',', '.');
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Jenis:</span>
                                        <span class="text-sm font-medium"><?php echo htmlspecialchars($item['itemTypeName'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Harga Beli:</span>
                                        <span class="text-sm font-medium"><?php echo formatCurrency($item['vendorPrice'] ?? 0); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Harga Jual:</span>
                                        <span class="text-sm font-medium"><?php echo formatCurrency($item['unitPrice'] ?? 0); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Terakhir Diperbarui:</span>
                                        <span class="text-sm font-medium">
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
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between gap-2">
                                    <button onclick="showPriceLevels(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>')" 
                                        class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-800 py-2 px-3 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                                        <i class="fas fa-tags mr-1"></i>Levels
                                    </button>
                                    <a href="itemv3.php?id=<?php echo $item['id']; ?>" 
                                        class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 py-2 px-3 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-screen overflow-hidden">
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
                        <!-- Dropdown Navigation -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Cabang:</label>
                            <div class="relative">
                                <select id="branchSelect" onchange="switchBranch()" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none">
                                    <!-- Options akan diisi via JavaScript -->
                                </select>
                                <!-- Chevron icon -->
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="overflow-y-auto max-h-96" id="branchContent">
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
            const branchSelect = document.getElementById('branchSelect');
            const branchContent = document.getElementById('branchContent');
            
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
                
                // Store data for later use
                window.priceLevelData = data;
                
                // Build dropdown interface
                if (!data.groupedPrices || data.groupedPrices.length === 0) {
                    branchContent.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <p>No price levels found for this item</p>
                        </div>
                    `;
                    branchSelect.innerHTML = '<option value="">No branches available</option>';
                } else {
                    // Build dropdown options
                    let optionsHtml = '';
                    data.groupedPrices.forEach((group, index) => {
                        const branchId = `branch-${group.branchId || index}`;
                        optionsHtml += `<option value="${branchId}">${group.branchName}</option>`;
                    });
                    branchSelect.innerHTML = optionsHtml;
                    
                    // Display content for first branch
                    displayBranchContent(0);
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

        // Function to display content for a specific branch
        function displayBranchContent(branchIndex) {
            const branchContent = document.getElementById('branchContent');
            const data = window.priceLevelData;
            
            if (!data || !data.groupedPrices || data.groupedPrices.length === 0) {
                branchContent.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>No price levels found for this item</p>
                    </div>
                `;
                return;
            }
            
            const group = data.groupedPrices[branchIndex];
            if (!group) {
                branchContent.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-2xl mb-2"></i>
                        <p>No data available for selected branch</p>
                    </div>
                `;
                return;
            }
            
            let contentHtml = `
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
            `;
            
            group.prices.forEach(price => {
                contentHtml += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">${price.categoryName}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${price.effectiveDate}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">${formatCurrency(price.price)}</td>
                    </tr>
                `;
            });
            
            contentHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            branchContent.innerHTML = contentHtml;
        }

        // Function untuk switch branch
        function switchBranch() {
            const branchSelect = document.getElementById('branchSelect');
            const selectedBranchId = branchSelect.value;
            
            // Show loading indicator
            const branchContent = document.getElementById('branchContent');
            branchContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl mb-2"></i>
                    <p class="text-gray-600">Memuat data untuk cabang yang dipilih...</p>
                </div>
            `;
            
            // Add a small delay to simulate loading
            setTimeout(() => {
                // Find the index of the selected branch
                const data = window.priceLevelData;
                if (!data || !data.groupedPrices) return;
                
                const selectedIndex = data.groupedPrices.findIndex((group, index) => {
                    const branchId = `branch-${group.branchId || index}`;
                    return branchId === selectedBranchId;
                });
                
                if (selectedIndex !== -1) {
                    displayBranchContent(selectedIndex);
                }
            }, 300); // 300ms delay to simulate loading
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
