<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$searchQuery = $_GET['search'] ?? '';
$itemId = $_GET['id'] ?? $_GET['no'] ?? null;

$item = null;
$error = null;
$rawResponse = null;

if ($itemId) {
    // Get detail barang
    $result = $api->getItemDetail($itemId);
    $rawResponse = $result; // Simpan raw response untuk debug
    
    if ($result['success'] && isset($result['data']['d'])) {
        $item = $result['data']['d'];
    } else {
        $error = $result['error'] ?? 'Data kosong atau error.';
    }
}

// Daftar field yang akan ditampilkan (lengkap dari original)
$fields = [
    "name" => "Nama Barang",
    "itemCategory>name" => "Kategori",
    "itemTypeName" => "Jenis Barang",
    "no" => "Kode Barang",
    "upcNo" => "UPC Barcode",
    "unit1Name" => "Satuan",
    "itemBrand>name" => "Merk",
    "serialNumberType" => "Nomor Unik",
    "defaultDiscount" => "Default Diskon",
    "unitPrice" => "Default Harga Jual",
    "preferedVendor>name" => "Pemasok Utama",
    "detailSellingPrice>unit>name" => "Satuan Beli",
    "vendorPrice" => "Harga Beli",
    "minimumQuantity" => "Minimum Beli",
    "minimumQuantityReorder" => "Batas Minimum Stok",
    "codeItemTax" => "Ref Kode Pajak",
    "percentTaxable" => "Dasar Pengenaan PPN(%)",
    "availableToSell" => "Qty Tersedia",
    "balanceUnitCost" => "Nilai Satuan",
    "balanceTotalCost" => "Beban Pokok",
    "itemBranchName" => "Cabang",
    "notes" => "Catatan",
    "suspended" => "Non Aktif",
    "dimWidth" => "Panjang (cm)",
    "dimDepth" => "Lebar (cm)",
    "dimHeight" => "Tinggi (cm)",
    "weight" => "Berat (gr)"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Item - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Item</h1>
                </div>
                <div class="flex gap-4">
                    <a href="listv2.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
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
        <div class="bg-white rounded-lg shadow">
            <!-- Header -->
            
            
            <!-- Content -->
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                <?php elseif ($item): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Detail Barang: <?php echo htmlspecialchars($itemId); ?>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Kolom Kiri -->
                            <div class="space-y-6">
                                <?php 
                                $leftFields = [
                                    "name" => "Nama Barang",
                                    "itemCategory>name" => "Kategori", 
                                    "itemTypeName" => "Jenis Barang",
                                    "no" => "Kode Barang",
                                    "upcNo" => "UPC Barcode",
                                    "unit1Name" => "Satuan",
                                    "itemBrand>name" => "Merk",
                                    "serialNumberType" => "Nomor Unik",
                                    "defaultDiscount" => "Default Diskon",
                                    "unitPrice" => "Default Harga Jual",
                                    "preferedVendor>name" => "Pemasok Utama",
                                    "detailSellingPrice>unit>name" => "Satuan Beli",
                                    "vendorPrice" => "Harga Beli",
                                    "minimumQuantity" => "Minimum Beli"
                                ];
                                ?>
                                <?php foreach ($leftFields as $path => $label): ?>
                                    <?php $value = getNested($item, $path); ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <strong><?php echo htmlspecialchars($label); ?></strong> (<?php echo htmlspecialchars($path); ?>)
                                        </label>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                            <span class="text-gray-900"><?php echo $value !== null ? htmlspecialchars($value) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Kolom Kanan -->
                            <div class="space-y-6">
                                <?php 
                                $rightFields = [
                                    "minimumQuantityReorder" => "Batas Minimum Stok",
                                    "codeItemTax" => "Ref Kode Pajak",
                                    "percentTaxable" => "Dasar Pengenaan PPN(%)",
                                    "availableToSell" => "Qty Tersedia",
                                    "balanceUnitCost" => "Nilai Satuan",
                                    "balanceTotalCost" => "Beban Pokok",
                                    "itemBranchName" => "Cabang",
                                    "notes" => "Catatan",
                                    "suspended" => "Non Aktif",
                                    "dimWidth" => "Panjang (cm)",
                                    "dimDepth" => "Lebar (cm)",
                                    "dimHeight" => "Tinggi (cm)",
                                    "weight" => "Berat (gr)"
                                ];
                                ?>
                                <?php foreach ($rightFields as $path => $label): ?>
                                    <?php $value = getNested($item, $path); ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <strong><?php echo htmlspecialchars($label); ?></strong> (<?php echo htmlspecialchars($path); ?>)
                                        </label>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                            <span class="text-gray-900"><?php echo $value !== null ? htmlspecialchars($value) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php
                        // Preview Image Section
                        $imagePath = '';
                        if (isset($item['detailItemImage']) && is_array($item['detailItemImage']) && count($item['detailItemImage']) > 0) {
                            $imagePath = $item['detailItemImage'][0]['fileName'] ?? '';
                        }
                        ?>
                        <?php if ($imagePath): ?>
                            <?php 
                            $fullImageUrl = "https://zeus.accurate.id" . $imagePath;
                            $filename = basename($imagePath);
                            if (empty($filename)) {
                                $filename = 'item_image_' . $itemId . '.jpg';
                            }
                            ?>
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <strong>Preview Image</strong> (detailItemImage[0].fileName)
                                    </label>
                                    <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-image text-gray-400"></i>
                                        
                                        <a href="<?php echo $fullImageUrl; ?>" target="_blank"
                                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-lg hover:bg-green-200 transition-colors">
                                            <i class="fas fa-external-link-alt mr-2"></i>
                                            Buka Tab Baru
                                        </a>
                                    </div>
                                    
                                    <!-- Debug info removed -->
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-xs text-gray-500">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>
                                    API: <?php echo htmlspecialchars($api->getSessionInfo()['host'] . '/accurate/api/item/detail.do'); ?><br>
                                    HTTP Method: GET, Scope: item_view
                                </span>
                            </div>
                        </div>
                    </div>

                <?php elseif ($itemId): ?>
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2 text-yellow-500"></i>
                            Tidak ada data ditemukan untuk Item ID: <strong><?php echo htmlspecialchars($itemId); ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-search mr-2 text-gray-400"></i>
                            Masukkan Item ID atau kode barang pada form di atas untuk mencari detail barang.
                        </div>
                    </div>
                <?php endif; ?>

                 <!-- Debug Section -->
        <?php if ($rawResponse): ?>
            <div class="mt-8 bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                <div class="text-sm">
                    <p><strong>Data Source:</strong> Item Detail API (/detail.do)</p>
                    <p><strong>Detail API Success:</strong> <?php echo isset($rawResponse) && $rawResponse['success'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Item Found:</strong> <?php echo $item ? 'Yes' : 'No'; ?></p>
                    <p><strong>Item ID:</strong> <?php echo htmlspecialchars($itemId); ?></p>
                    <?php if (isset($rawResponse['error'])): ?>
                        <p><strong>Error:</strong> <?php echo htmlspecialchars($rawResponse['error']); ?></p>
                    <?php endif; ?>
                </div>
                
                <details class="mt-4">
                    <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                    <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                        // Tampilkan raw response yang sebenarnya dari API
                        if ($rawResponse) {
                            echo htmlspecialchars(json_encode($rawResponse, JSON_PRETTY_PRINT));
                        } else {
                            echo "No raw response available";
                        }
                    ?></pre>
                </details>
            </div>
        <?php endif; ?>

            </div>

        </div>
        
       
    </main>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Gambar Item</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="mt-4 text-center">
                <!-- Loading Spinner -->
                <div id="imageLoading" class="py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Memuat gambar...</p>
                </div>
                
                <!-- Image Container -->
                <div id="imageContainer" class="hidden">
                    <img id="modalImage" class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg" alt="Item Image">
                </div>
                
                <!-- Error Message -->
                <div id="imageError" class="hidden py-8 text-red-600">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.194-.833-2.464 0L5.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p>Gagal memuat gambar</p>
                    <p class="text-sm text-gray-500 mt-2">Silakan coba lagi atau periksa koneksi internet</p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-between items-center pt-4 border-t mt-4">
                <span id="imageName" class="text-sm text-gray-600"></span>
                <div class="space-x-2">
                    <button id="downloadImage" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                        Download
                    </button>
                    <button id="closeModalBtn" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const imageContainer = document.getElementById('imageContainer');
        const imageLoading = document.getElementById('imageLoading');
        const imageError = document.getElementById('imageError');
        const imageName = document.getElementById('imageName');
        const downloadBtn = document.getElementById('downloadImage');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModal = document.getElementById('closeModal');
        
        let currentImageUrl = '';
        let currentImageName = '';
        
        // Function to open modal
        window.openImageModal = function(imageUrl, fileName) {
            console.log('Opening modal with URL:', imageUrl);
            
            currentImageUrl = imageUrl;
            currentImageName = fileName || 'item_image';
            
            // Show modal
            modal.classList.remove('hidden');
            
            // Reset states
            imageContainer.classList.add('hidden');
            imageError.classList.add('hidden');
            imageLoading.classList.remove('hidden');
            
            // Set image name
            imageName.textContent = currentImageName;
            
            // Create new image element to test loading
            const testImage = new Image();
            
            testImage.onload = function() {
                console.log('Image loaded successfully');
                modalImage.src = currentImageUrl;
                modalImage.alt = currentImageName;
                
                // Hide loading, show image
                imageLoading.classList.add('hidden');
                imageContainer.classList.remove('hidden');
            };
            
            testImage.onerror = function() {
                console.log('Image failed to load');
                // Hide loading, show error
                imageLoading.classList.add('hidden');
                imageError.classList.remove('hidden');
            };
            
            // Start loading
            testImage.src = currentImageUrl;
        };
        
        // Function to close modal
        function closeImageModal() {
            modal.classList.add('hidden');
            modalImage.src = '';
            currentImageUrl = '';
            currentImageName = '';
        }
        
        // Event listeners
        closeModal.addEventListener('click', closeImageModal);
        closeModalBtn.addEventListener('click', closeImageModal);
        
        // Download functionality
        downloadBtn.addEventListener('click', function() {
            if (currentImageUrl) {
                const link = document.createElement('a');
                link.href = currentImageUrl;
                link.download = currentImageName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
        
        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeImageModal();
            }
        });
    });
    </script>
</body>
</html>
