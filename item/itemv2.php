<?php
/**
 * Display detail barang dalam format HTML
 * File ini sudah direfactor untuk menggunakan struktur baru
 */

require_once __DIR__ . '/../bootstrap.php';

// Inisialisasi API class
$api = new AccurateAPI();

$itemId = $_GET["no"] ?? null;
$item = null;
$error = null;

if ($itemId) {
    // Get detail barang
    $result = $api->getItemDetail($itemId);
    
    if ($result['success'] && isset($result['data']['d'])) {
        $item = $result['data']['d'];
    } else {
        $error = $result['error'] ?? 'Data kosong atau error.';
    }
}

// Daftar field yang akan ditampilkan
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
    "lastUpdate" => "Terakhir Diperbarui",
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
    <title><?php echo APP_NAME; ?> - Detail Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-box"></i> Detail Barang Accurate
            </h1>
            <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        <!-- Search Form -->
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6 p-4 bg-gray-50 rounded-lg">
            <input type="text" name="no" placeholder="Masukkan Kode Barang..." value="<?php echo htmlspecialchars($itemId ?? ''); ?>"
                   class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
            <button type="submit"
                    class="bg-blue-500 text-white font-semibold px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-search"></i> Cari Barang
            </button>
        </form>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php elseif ($item): ?>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">
                <i class="fas fa-info-circle"></i> Detail Barang: <?php echo htmlspecialchars($itemId); ?>
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 border border-gray-300">Keterangan</th>
                            <th class="text-left px-3 py-2 border border-gray-300">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fields as $path => $label): ?>
                            <?php $value = getNested($item, $path); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border border-gray-300 font-medium">
                                    <?php echo htmlspecialchars($label); ?><br>
                                    <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($path); ?>)</span>
                                </td>
                                <td class="px-3 py-2 border border-gray-300"><?php echo htmlspecialchars($value); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                        // Baris tambahan Preview Image
                        $imagePath = '';
                        if (isset($item['detailItemImage']) && is_array($item['detailItemImage']) && count($item['detailItemImage']) > 0) {
                            $imagePath = $item['detailItemImage'][0]['fileName'] ?? '';
                        }
                        ?>
                        <?php if ($imagePath): ?>
                            <?php $fullImageUrl = "https://zeus.accurate.id" . $imagePath; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border border-gray-300 font-medium">Preview Image</td>
                                <td class="px-3 py-2 border border-gray-300">
                                    <a href="<?= $fullImageUrl ?>" target="_blank"
                                        class="inline-block bg-blue-100 text-blue-700 text-sm font-medium px-3 py-1 rounded hover:bg-blue-200">
                                        Lihat Gambar
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p class="mt-3 text-xs text-gray-500">
                    API: <?php echo htmlspecialchars($api->getSessionInfo()['host'] . '/accurate/api/item/detail.do'); ?><br>
                    HTTP Method: GET, Scope: item_view
                </p>

            </div>
        <?php elseif ($itemId): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Tidak ada data ditemukan untuk kode barang: <strong><?php echo htmlspecialchars($itemId); ?></strong>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-100 border border-gray-300 text-gray-600 px-4 py-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    Masukkan kode barang pada form di atas untuk mencari detail barang.
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
