<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$sessionInfo = $api->getSessionInfo();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Item Baru - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .price-input {
            text-align: right;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #3b82f6;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-plus-circle text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Item Baru</h1>
                </div>
                <div class="flex gap-4">
                    <a href="listv2.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Form Item -->
            <div class="form-container p-6">
                <div class="flex items-center mb-6">
                    <i class="fas fa-box text-blue-600 mr-2"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Data Item</h2>
                </div>
                
                <form id="itemForm" class="space-y-4">
                    <div>
                        <label for="kodeBarang" class="block text-sm font-medium text-gray-700 mb-2">Kode Barang</label>
                        <input type="text" id="kodeBarang" name="no" required 
                               placeholder="Masukkan kode barang"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <input type="text" id="kategori" name="itemCategoryName" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="namaBarang" class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                        <input type="text" id="namaBarang" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                        <input type="text" id="satuan" name="unit1Name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label for="aktifSN" class="text-sm font-medium text-gray-700">Aktif S/N</label>
                        <div class="flex items-center gap-3">
                            <span id="snStatus" class="text-sm text-gray-600">False</span>
                            <label class="switch">
                                <input type="checkbox" id="aktifSN" name="manageSN">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" id="saveItemBtn" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>SAVE ITEM & PRICE LEVELS
                    </button>
                </form>
                
                <div id="itemLoading" class="hidden mt-4 text-center">
                    <div class="inline-flex items-center px-4 py-2 text-blue-600">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Saving item...
                    </div>
                </div>
                
                <div id="itemStatus" class="hidden mt-4 p-4 rounded-lg"></div>
            </div>
            
            <!-- Form Price Levels -->
            <div class="form-container p-6">
                <div class="flex items-center mb-6">
                    <i class="fas fa-tags text-orange-600 mr-2"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Price Levels</h2>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Level 1</label>
                        <input type="text" id="price1" value="0" 
                               class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Level 2</label>
                        <input type="text" id="price2" value="0" 
                               class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Level 3</label>
                        <input type="text" id="price3" value="0" 
                               class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                </div>
                
                <div id="priceLoading" class="hidden mt-4 text-center">
                    <div class="inline-flex items-center px-4 py-2 text-orange-600">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Saving price levels...
                    </div>
                </div>
                
                <div id="priceStatus" class="hidden mt-4 p-4 rounded-lg"></div>
            </div>
        </div>
    </main>

    <script>
        const SESSION_ID = '<?php echo htmlspecialchars($sessionInfo['session_id'] ?? ''); ?>';
        let savedItemNo = null;
        
        // Number formatter untuk input price
        function formatNumber(input) {
            // Remove non-digits
            let value = input.value.replace(/[^\d]/g, '');
            
            // Add thousand separators
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            
            input.value = value;
        }
        
        // Parse number dari format dengan separator
        function parseNumber(value) {
            return value.replace(/[^\d]/g, '');
        }
        
        // Setup number formatting untuk price inputs
        document.addEventListener('DOMContentLoaded', function() {
            const priceInputs = ['price1', 'price2', 'price3'];
            
            priceInputs.forEach(id => {
                const input = document.getElementById(id);
                
                input.addEventListener('input', function() {
                    formatNumber(this);
                });
                
                input.addEventListener('blur', function() {
                    formatNumber(this);
                });
            });
        });
        
        // Toggle S/N status display
        document.getElementById('aktifSN').addEventListener('change', function() {
            const status = document.getElementById('snStatus');
            status.textContent = this.checked ? 'True' : 'False';
        });

        // Form submit untuk save item + price levels
        document.getElementById('itemForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const saveBtn = document.getElementById('saveItemBtn');
            const itemLoading = document.getElementById('itemLoading');
            const itemStatus = document.getElementById('itemStatus');
            
            // Disable button dan show loading
            saveBtn.disabled = true;
            itemLoading.classList.remove('hidden');
            itemStatus.classList.add('hidden');
            
            try {
                // STEP 1: Save Item
                itemLoading.innerHTML = '<div class="inline-flex items-center px-4 py-2 text-blue-600"><i class="fas fa-spinner fa-spin mr-2"></i>Saving item...</div>';
                
                const itemData = {
                    no: document.getElementById('kodeBarang').value,
                    itemCategoryName: document.getElementById('kategori').value,
                    name: document.getElementById('namaBarang').value,
                    unit1Name: document.getElementById('satuan').value,
                    manageSN: document.getElementById('aktifSN').checked ? 'True' : 'False'
                };
                
                const itemResponse = await fetch('/nuansa/item/itemsave.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Session-ID': SESSION_ID
                    },
                    body: JSON.stringify(itemData)
                });
                
                const itemResult = await itemResponse.json();
                
                if (!itemResult.success) {
                    throw new Error(`Item save failed: ${itemResult.message}`);
                }
                
                // Item berhasil disimpan
                savedItemNo = document.getElementById('kodeBarang').value;
                console.log(`Item saved successfully with item number: ${savedItemNo}`);
                
                // STEP 2: Save Price Levels
                itemLoading.innerHTML = '<div class="inline-flex items-center px-4 py-2 text-orange-600"><i class="fas fa-spinner fa-spin mr-2"></i>Saving price levels...</div>';
                
                const prices = [
                    { id: '50', price: parseNumber(document.getElementById('price1').value) },
                    { id: '51', price: parseNumber(document.getElementById('price2').value) },
                    { id: '100', price: parseNumber(document.getElementById('price3').value) }
                ];
                
                let successCount = 0;
                const results = [];
                
                // Save setiap price level
                for (const priceLevel of prices) {
                    try {
                        // Skip jika price = 0 atau kosong, tapi catat sebagai skipped
                        if (priceLevel.price === '0' || priceLevel.price === '' || priceLevel.price === 0) {
                            results.push(`Level ${priceLevel.id}: ⏭️ Skipped (price = 0)`);
                            continue;
                        }
                        
                        const params = new URLSearchParams();
                        params.append('detailItem[0].itemNo', savedItemNo);
                        params.append('detailItem[0].price', priceLevel.price);
                        params.append('salesAdjustmentType', 'ITEM_PRICE_TYPE');
                        params.append('id', priceLevel.id);
                        
                        console.log(`Saving price level ${priceLevel.id} for item: ${savedItemNo} with price: ${priceLevel.price}`);
                        
                        const priceResponse = await fetch('/nuansa/sellingprice/saveprice.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Session-ID': SESSION_ID
                            },
                            body: params.toString()
                        });
                        
                        const priceResult = await priceResponse.json();
                        console.log(`Price level ${priceLevel.id} response:`, priceResult);
                        
                        if (priceResult.success) {
                            successCount++;
                            results.push(`Level ${priceLevel.id}: ✅ Success`);
                        } else {
                            results.push(`Level ${priceLevel.id}: ❌ ${priceResult.message}`);
                        }
                        
                    } catch (error) {
                        results.push(`Level ${priceLevel.id}: ❌ ${error.message}`);
                    }
                    
                    // Delay 500ms antar request untuk mencegah konflik
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
                
                // Show final results dengan detail
                const totalAttempted = prices.filter(p => p.price !== '0' && p.price !== '' && p.price !== 0).length;
                const totalSkipped = 3 - totalAttempted;
                
                // Show detailed results
                let detailsHtml = '<div class="mt-3 text-sm"><strong>Details:</strong><br>' + results.join('<br>') + '</div>';
                
                if (totalAttempted === 0) {
                    // Hanya item yang disimpan, tidak ada price level
                    itemStatus.className = 'mt-4 p-4 rounded-lg bg-green-100 border border-green-200 text-green-800';
                    itemStatus.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Item berhasil disimpan! (Semua price levels = 0, tidak disimpan) Mengalihkan ke daftar item...` + detailsHtml;
                } else if (successCount === totalAttempted) {
                    // Semua berhasil
                    itemStatus.className = 'mt-4 p-4 rounded-lg bg-green-100 border border-green-200 text-green-800';
                    itemStatus.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Item & semua price levels berhasil disimpan! (${successCount}/${totalAttempted}) Mengalihkan ke daftar item...` + detailsHtml;
                } else if (successCount > 0) {
                    // Sebagian berhasil
                    itemStatus.className = 'mt-4 p-4 rounded-lg bg-yellow-100 border border-yellow-200 text-yellow-800';
                    itemStatus.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>Item berhasil, sebagian price levels berhasil (${successCount}/${totalAttempted}) Mengalihkan ke daftar item...` + detailsHtml;
                } else {
                    // Item berhasil tapi price level gagal semua
                    itemStatus.className = 'mt-4 p-4 rounded-lg bg-yellow-100 border border-yellow-200 text-yellow-800';
                    itemStatus.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>Item berhasil, price levels gagal (${successCount}/${totalAttempted}) Mengalihkan ke daftar item...` + detailsHtml;
                }
                
                itemStatus.classList.remove('hidden');
                
                // Auto redirect ke listv2.php setelah sukses
                setTimeout(() => {
                    window.location.href = 'listv2.php';
                }, 2000); // Redirect setelah 2 detik
                
            } catch (error) {
                itemStatus.className = 'mt-4 p-4 rounded-lg bg-red-100 border border-red-200 text-red-800';
                itemStatus.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>Error: ${error.message}`;
                itemStatus.classList.remove('hidden');
            }
            
            // Re-enable button dan hide loading
            saveBtn.disabled = false;
            itemLoading.classList.add('hidden');
        });
    </script>
</body>
</html>
