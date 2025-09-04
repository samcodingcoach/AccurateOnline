<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$sessionInfo = $api->getSessionInfo();

// Fetch item categories
$categories = [];
// Construct the URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$url = $protocol . $host . '/nuansa/itemcategory/listcategory.php';

// Use a stream context to pass the session ID in headers if required, or just fetch
// For simplicity, assuming the listcategory.php does not require session for now.
// If it does, a more robust cURL request would be needed.
$categories_json = @file_get_contents($url);
if ($categories_json) {
    $categories_data = json_decode($categories_json, true);
    if (isset($categories_data['data']['d'])) {
        $categories = $categories_data['data']['d'];
        // Sort categories by name alphabetically
        usort($categories, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    }
}

// Fetch units
$units = [];
$units_url = $protocol . $host . '/nuansa/unit/list_unit.php';
$units_json = @file_get_contents($units_url);
if ($units_json) {
    $units_data = json_decode($units_json, true);
    // Adjusted to match the actual structure from list_unit.php
    if (isset($units_data['data']['units']['d'])) {
        $units = $units_data['data']['units']['d'];
        // Sort units by name alphabetically
        usort($units, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Item Baru - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
        }
        
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .form-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }
        
        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.2s ease;
            background-color: #f9fafb;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            background-color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2563eb 100%);
            color: white;
            font-weight: 600;
            padding: 14px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            font-weight: 600;
            padding: 14px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(107, 114, 128, 0.4);
        }
        
        .price-input {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
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
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 30px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        input:checked + .slider {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .price-level-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        
        .price-level-card:hover {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            transform: translateY(-2px);
        }
        
        .status-message {
            border-radius: 12px;
            padding: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .status-success {
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        
        .status-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .status-warning {
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            color: #854d0e;
        }
        
        .status-info {
            background-color: #dbeafe;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        
        /* Style for Select2 */
        .select2-container .select2-selection--single {
            height: 48px; /* Match custom input height */
            border-radius: 12px; /* Match custom input border radius */
            border: 2px solid #e5e7eb; /* Match custom input border */
            background-color: #f9fafb;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
            padding-left: 16px;
            font-size: 16px;
            color: #374151;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
            right: 8px;
        }
        .select2-container--open .select2-dropdown {
            border-radius: 12px;
            border-color: var(--primary-color);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
        }
        
        /* Equal height columns */
        .equal-height-columns {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }
        
        .equal-height-columns > [class*='w-'] {
            display: flex;
            padding: 0 1rem;
            margin-bottom: 2rem;
        }
        
        .equal-height-columns .form-card {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .equal-height-columns {
                flex-direction: column;
            }
            
            .equal-height-columns > [class*='w-'] {
                width: 100%;
                padding: 0 1rem;
            }
        }
        
        @media (max-width: 640px) {
            .header-actions {
                width: 100%;
                justify-content: center;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .form-card {
                padding: 1.25rem;
            }
            
            .equal-height-columns {
                margin: 0 -0.5rem;
            }
            
            .equal-height-columns > [class*='w-'] {
                padding: 0 0.5rem;
                margin-bottom: 1rem;
            }
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Form container adjustments */
        .form-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .form-container:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }
        
        /* Overlay styling */
        #savingOverlay {
            transition: opacity 0.3s ease;
        }
        
        #savingOverlay .bg-white {
            animation: fadeInUp 0.3s ease-out;
        }
        
        /* Result modal styling */
        #resultModal {
            transition: opacity 0.3s ease;
        }
        
        #resultModal .bg-white {
            animation: fadeInScale 0.3s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Responsive adjustments for modals */
        @media (max-width: 640px) {
            #resultModal .max-w-2xl {
                max-width: calc(100% - 2rem);
            }
            
            #resultModal .p-6 {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-xl mr-4">
                        <i class="fas fa-plus-circle text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tambah Item Baru</h1>
                        <p class="text-gray-600 text-sm">Lengkapi informasi item dan harga</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="listv2.php" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="../index.php" class="btn-primary">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="equal-height-columns -mx-4">
            <div class="px-4 mb-8 w-full lg:w-1/2">
                <div class="form-card p-6 h-full flex flex-col">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-box text-blue-600"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Informasi Item</h2>
                    </div>
                    
                    <form id="itemForm" class="space-y-5 flex-grow flex flex-col">
                        <div>
                            <label for="kodeBarang" class="block text-sm font-semibold text-gray-700 mb-2">Kode Barang *</label>
                            <input type="text" id="kodeBarang" name="no" required 
                                   placeholder="Masukkan kode barang"
                                   class="input-field">
                        </div>
                        
                        <div>
                            <label for="kategori" class="block text-sm font-semibold text-gray-700 mb-2">Kategori *</label>
                            <select id="kategori" name="itemCategoryName" required
                                    class="w-full">
                                <option value="">Pilih kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="namaBarang" class="block text-sm font-semibold text-gray-700 mb-2">Nama Barang *</label>
                            <input type="text" id="namaBarang" name="name" required
                                   placeholder="Masukkan nama barang"
                                   class="input-field">
                        </div>
                        
                        <div>
                            <label for="satuan" class="block text-sm font-semibold text-gray-700 mb-2">Satuan *</label>
                            <select id="satuan" name="unit1Name" required
                                    class="w-full">
                                <option value="">Pilih satuan</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo htmlspecialchars($unit['name']); ?>">
                                        <?php echo htmlspecialchars($unit['name']); ?>
                                    </option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <label for="aktifSN" class="block text-sm font-semibold text-gray-700">Aktif S/N</label>
                                <p class="text-gray-600 text-sm mt-1">Aktifkan serial number untuk item ini</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span id="snStatus" class="text-sm font-medium text-gray-600">Tidak Aktif</span>
                                <label class="switch">
                                    <input type="checkbox" id="aktifSN" name="manageSN">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-auto pt-4">
                            <button type="submit" id="saveItemBtn" 
                                    class="btn-primary w-full">
                                <i class="fas fa-save mr-2"></i>SIMPAN ITEM & HARGA
                            </button>
                        </div>
                    </form>
                    
                    <div id="itemLoading" class="hidden mt-6 text-center">
                        <div class="inline-flex items-center px-6 py-3 bg-blue-50 text-blue-700 rounded-xl">
                            <i class="fas fa-spinner fa-spin mr-3"></i>
                            <span>Menyimpan item...</span>
                        </div>
                    </div>
                    
                    <div id="itemStatus" class="hidden mt-6"></div>
                </div>
            </div>
            
            <div class="px-4 mb-8 w-full lg:w-1/2">
                <div class="form-card p-6 h-full flex flex-col">
                    <div class="flex items-center mb-6">
                        <div class="bg-orange-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-tags text-orange-600"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Level Harga</h2>
                    </div>
                    
                    <div class="flex-grow overflow-y-auto pr-2 -mr-2 max-h-96">
                        <div class="space-y-4">
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 1</label>
                                <input type="text" id="price1" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 2</label>
                                <input type="text" id="price2" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 3</label>
                                <input type="text" id="price3" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 4</label>
                                <input type="text" id="price4" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 5</label>
                                <input type="text" id="price5" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 6</label>
                                <input type="text" id="price6" value="0" 
                                       class="price-input input-field">
                            </div>
                            
                            <div class="price-level-card p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Level 7</label>
                                <input type="text" id="price7" value="0" 
                                       class="price-input input-field">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-blue-50 rounded-xl flex-shrink-0">
                        <h3 class="font-semibold text-blue-800 mb-2">Petunjuk</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Masukkan harga tanpa titik atau koma</li>
                            <li>• Harga akan diformat otomatis saat Anda mengetik</li>
                            <li>• Harga 0 tidak akan disimpan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const SESSION_ID = '<?php echo htmlspecialchars($sessionInfo["session_id"] ?? ""); ?>';
        let savedItemNo = null;

        $(document).ready(function() {
            // Initialize Select2 with enhanced styling
            $('#kategori').select2({
                placeholder: "Pilih atau cari kategori...",
                allowClear: true,
                width: '100%',
                theme: "default"
            });

            $('#satuan').select2({
                placeholder: "Pilih atau cari satuan...",
                allowClear: true,
                width: '100%',
                theme: "default"
            });

            // Setup number formatting untuk price inputs
            const priceInputs = ['price1', 'price2', 'price3', 'price4', 'price5', 'price6', 'price7'];
            
            priceInputs.forEach(id => {
                const input = document.getElementById(id);
                
                // Format saat input diketik
                input.addEventListener('input', function() {
                    formatNumber(this);
                });
                
                // Format saat input kehilangan fokus
                input.addEventListener('blur', function() {
                    formatNumber(this);
                });
            });
            
            // Sesuaikan tinggi kolom saat halaman dimuat
            setTimeout(adjustColumnHeights, 100);
            window.addEventListener('resize', function() {
                setTimeout(adjustColumnHeights, 100);
            });
        });
        
        // Fungsi untuk menyesuaikan tinggi kolom
        function adjustColumnHeights() {
            const leftCard = document.querySelector('.equal-height-columns > :first-child .form-card');
            const rightCard = document.querySelector('.equal-height-columns > :last-child .form-card');
            
            if (leftCard && rightCard) {
                // Reset tinggi
                leftCard.style.height = 'auto';
                rightCard.style.height = 'auto';
                
                // Dapatkan tinggi maksimum
                const leftHeight = leftCard.offsetHeight;
                const rightHeight = rightCard.offsetHeight;
                const maxHeight = Math.max(leftHeight, rightHeight);
                
                // Terapkan tinggi yang sama
                leftCard.style.height = maxHeight + 'px';
                rightCard.style.height = maxHeight + 'px';
            }
        }
        
        // Panggil fungsi adjustColumnHeights saat halaman dimuat dan saat ukuran jendela berubah
        window.addEventListener('load', function() {
            setTimeout(adjustColumnHeights, 100);
        });
        
        window.addEventListener('resize', function() {
            setTimeout(adjustColumnHeights, 100);
        });
        
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
        
        // Toggle S/N status display
        document.getElementById('aktifSN').addEventListener('change', function() {
            const status = document.getElementById('snStatus');
            status.textContent = this.checked ? 'Aktif' : 'Tidak Aktif';
            status.className = this.checked ? 'text-sm font-medium text-green-600' : 'text-sm font-medium text-gray-600';
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
                // Tampilkan overlay saat proses dimulai
                showSavingOverlay();
                updateProgress(10, 'Menyimpan item...');
                
                // STEP 1: Save Item
                const itemData = {
                    no: document.getElementById('kodeBarang').value,
                    itemCategoryName: $('#kategori').val(), // Get value from Select2
                    name: document.getElementById('namaBarang').value,
                    unit1Name: $('#satuan').val(), // Get value from Select2
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
                
                updateProgress(30, 'Item berhasil disimpan');
                
                // STEP 2: Save Price Levels
                updateProgress(40, 'Menyimpan harga...');
                
                const prices = [
                    { id: '50', price: parseNumber(document.getElementById('price1').value) },
                    { id: '200', price: parseNumber(document.getElementById('price2').value) },
                    { id: '250', price: parseNumber(document.getElementById('price3').value) },
                    { id: '151', price: parseNumber(document.getElementById('price4').value) },
                    { id: '300', price: parseNumber(document.getElementById('price5').value) },
                    { id: '350', price: parseNumber(document.getElementById('price6').value) },
                    { id: '301', price: parseNumber(document.getElementById('price7').value) }
                ];
                
                let successCount = 0;
                const results = [];
                let processedCount = 0;
                const totalToProcess = prices.filter(p => p.price !== '0' && p.price !== '' && p.price !== 0).length || 1;
                
                // Update progress untuk harga
                updateProgress(50, `Menyimpan 0 dari ${totalToProcess} harga...`);
                
                // Save setiap price level
                for (const priceLevel of prices) {
                    try {
                        // Skip jika price = 0 atau kosong, tapi catat sebagai skipped
                        if (priceLevel.price === '0' || priceLevel.price === '' || priceLevel.price === 0) {
                            results.push(`Level ${priceLevel.id}: ⏭️ Dilewati (harga = 0)`);
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
                        
                        processedCount++;
                        updateProgress(50 + (processedCount / totalToProcess) * 40, `Menyimpan ${processedCount} dari ${totalToProcess} harga...`);
                        
                        if (priceResult.success) {
                            successCount++;
                            results.push(`Level ${priceLevel.id}: ✅ Berhasil`);
                        } else {
                            results.push(`Level ${priceLevel.id}: ❌ ${priceResult.message}`);
                        }
                        
                    } catch (error) {
                        results.push(`Level ${priceLevel.id}: ❌ ${error.message}`);
                        processedCount++;
                        updateProgress(50 + (processedCount / totalToProcess) * 40, `Menyimpan ${processedCount} dari ${totalToProcess} harga...`);
                    }
                    
                    // Delay 300ms antar request untuk mencegah konflik
                    await new Promise(resolve => setTimeout(resolve, 300));
                }
                
                // Show final results dengan detail
                const totalAttempted = prices.filter(p => p.price !== '0' && p.price !== '' && p.price !== 0).length;
                const totalSkipped = 7 - totalAttempted;
                
                updateProgress(95, 'Menampilkan hasil...');
                
                // Tampilkan hasil dalam modal di tengah layar
                setTimeout(() => {
                    updateProgress(100, 'Selesai!');
                    
                    // Sembunyikan overlay setelah delay singkat
                    setTimeout(() => {
                        hideSavingOverlay();
                        
                        // Buat konten hasil untuk modal
                        let detailsHtml = '<div class="mt-4"><h4 class="font-semibold text-gray-900 mb-2">Detail:</h4><ul class="space-y-2">';
                        results.forEach(result => {
                            // Parsing hasil untuk menentukan icon dan warna
                            let icon = 'fa-circle';
                            let color = 'text-gray-500';
                            if (result.includes('✅')) {
                                icon = 'fa-check-circle';
                                color = 'text-green-600';
                            } else if (result.includes('❌')) {
                                icon = 'fa-times-circle';
                                color = 'text-red-600';
                            } else if (result.includes('⏭️')) {
                                icon = 'fa-forward';
                                color = 'text-blue-600';
                            }
                            
                            detailsHtml += `<li class="flex items-start">
                                <i class="fas ${icon} ${color} mt-1 mr-2"></i>
                                <span>${result.replace(/✅|❌|⏭️/, '')}</span>
                            </li>`;
                        });
                        detailsHtml += '</ul></div>';
                        
                        // Tentukan judul dan status
                        let title = '';
                        let isSuccess = true;
                        if (totalAttempted === 0) {
                            title = 'Item Berhasil Disimpan';
                            isSuccess = true;
                        } else if (successCount === totalAttempted) {
                            title = 'Semua Data Berhasil Disimpan';
                            isSuccess = true;
                        } else if (successCount > 0) {
                            title = 'Sebagian Data Berhasil Disimpan';
                            isSuccess = false;
                        } else {
                            title = 'Gagal Menyimpan Data';
                            isSuccess = false;
                        }
                        
                        // Buat konten utama
                        let mainContent = `
                            <div class="text-center mb-6">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full ${isSuccess ? 'bg-green-100' : 'bg-yellow-100'} mb-4">
                                    <i class="fas ${isSuccess ? 'fa-check-circle text-green-600' : 'fa-exclamation-triangle text-yellow-600'} text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">${title}</h3>
                                <p class="text-gray-600">
                                    ${totalAttempted === 0 ? 
                                        'Item berhasil disimpan! (Semua harga = 0, tidak disimpan)' : 
                                        `Item & ${successCount} dari ${totalAttempted} harga berhasil disimpan`}
                                </p>
                            </div>
                            ${detailsHtml}
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <p class="text-center text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Mengalihkan ke daftar item dalam 3 detik...
                                </p>
                            </div>
                        `;
                        
                        // Simpan timeout untuk redirect
                        window.redirectTimeout = setTimeout(() => {
                            window.location.href = 'listv2.php';
                            // Hapus referensi timeout setelah digunakan
                            window.redirectTimeout = null;
                        }, 3000); // Redirect setelah 3 detik
                        
                        // Tampilkan dalam modal
                        showResultModal(title, mainContent, isSuccess);
                        
                    }, 500);
                    
                }, 500);
                
            } catch (error) {
                // Sembunyikan overlay dan tampilkan error dalam modal
                hideSavingOverlay();
                
                // Buat konten error untuk modal
                let errorContent = `
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Terjadi Kesalahan</h3>
                        <p class="text-gray-600">Gagal menyimpan item dan harga</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-2"></i>
                            <div>
                                <h4 class="font-semibold text-red-800">Error Details:</h4>
                                <p class="text-red-700 mt-1">${error.message}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-center text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Silakan periksa kembali data dan coba lagi
                        </p>
                    </div>
                `;
                
                // Hapus timeout redirect jika ada
                if (window.redirectTimeout) {
                    clearTimeout(window.redirectTimeout);
                    window.redirectTimeout = null;
                }
                
                // Tampilkan dalam modal
                showResultModal('Error', errorContent, false);
            }
            
            // Re-enable button dan hide loading
            saveBtn.disabled = false;
            setTimeout(() => {
                itemLoading.classList.add('hidden');
            }, 1000);
        });

        // Overlay untuk proses penyimpanan
        function showSavingOverlay() {
            const overlay = document.getElementById('savingOverlay');
            overlay.classList.remove('hidden');
            // Cegah scrolling pada body
            document.body.style.overflow = 'hidden';
        }

        function hideSavingOverlay() {
            const overlay = document.getElementById('savingOverlay');
            overlay.classList.add('hidden');
            // Aktifkan kembali scrolling pada body
            document.body.style.overflow = '';
        }

        function updateProgress(percent, message) {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            if (progressBar) {
                progressBar.style.width = percent + '%';
            }
            
            if (progressText) {
                progressText.textContent = Math.round(percent) + '% ' + (message || '');
            }
        }
        
        // Fungsi untuk menampilkan hasil dalam modal
        function showResultModal(title, content, isSuccess) {
            const modal = document.getElementById('resultModal');
            const contentDiv = document.getElementById('resultContent');
            
            // Set judul dan konten modal
            document.querySelector('#resultModal h3').innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-yellow-500'} mr-2"></i>${title}`;
            contentDiv.innerHTML = content;
            
            // Tampilkan modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        // Fungsi untuk menutup modal hasil
        function closeResultModal() {
            const modal = document.getElementById('resultModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            
            // Periksa apakah sedang dalam proses redirect
            if (window.redirectTimeout) {
                // Jika ya, lanjutkan redirect dan hapus timeout
                const timeoutId = window.redirectTimeout;
                window.redirectTimeout = null;
                clearTimeout(timeoutId);
                window.location.href = 'listv2.php';
            }
        }
        
        // Tambahkan event listener untuk tombol ESC
        document.addEventListener('keydown', function(event) {
            const resultModal = document.getElementById('resultModal');
            if (event.key === 'Escape' && !resultModal.classList.contains('hidden')) {
                closeResultModal();
            }
        });
    </script>

    <!-- Overlay untuk proses penyimpanan -->
    <div id="savingOverlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Menyimpan Item</h3>
                <p class="text-gray-600 mb-4">Mohon tunggu, sedang menyimpan data item dan harga...</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <div id="progressText" class="text-sm text-gray-500 mt-2">0%</div>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan hasil -->
    <div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" onclick="if (event.target === this) closeResultModal()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-info-circle mr-2"></i>Hasil Penyimpanan
                </h3>
                <button onclick="closeResultModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto flex-grow p-6">
                <div id="resultContent" class="space-y-4">
                    <!-- Hasil akan diisi via JavaScript -->
                </div>
            </div>
            
            <div class="p-6 border-t bg-gray-50">
                <div class="flex justify-end">
                    <button onclick="closeResultModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>