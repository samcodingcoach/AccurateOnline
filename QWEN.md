Perintah 1:
buatkan di sellingprice/index.php sebuah halaman yang menampilkan
data dari endpoint /sellingprice/list_spa.php
secara tampilan anda bisa mencontoh ui pattern di unit/index.html
dan table yang di tampilkan

Table head: Number, ID, Tanggal, Tipe, Action
Table Body: SPA.2025.08.00005,101,03/09/2025, ITEM_PRICE_TYPE, Detail

Perintah 2:
pada sellingprice/detail.php , buatkan sebuah halaman untuk menampilkan data dari endpoint
/sellingprice/detail_spa.php?number=?
data yang tampil mencontoh pattern warehouse/detail.php?id=50

beberapa data yang ditunjukan untuk header

- d.number,d.priceCategory.name,d.spaBranchName,d.salesAdjustmentType,d.transDate
  dan dibawahnya dalam bentuk table
  header: No,Kode,Nama Barang,Satuan, Harga
  table body: increment,d.detailItem[n].item.no,d.detailItem[n].item.name,d.detailItem[n].item.unit1.name,d.detailItem[n].price

table body menggunakan perulangan tentunya n dimulai 0

pada sellingprice/index.php telah dibuatkan link untuk mengarahkan kehalaman ini(detail.php)

Kesimpulan Perintah 2:
Telah dibuat halaman sellingprice/detail.php yang menampilkan data dari endpoint /sellingprice/detail_spa.php dengan parameter number. Halaman ini menampilkan informasi detail selling price adjustment sesuai dengan pola yang digunakan di warehouse/detail.php.

Data yang ditampilkan:

1. Bagian header:

   - Nomor Dokumen (d.number)
   - Kategori Harga (d.priceCategory.name)
   - Cabang (d.spaBranchName)
   - Tipe Penyesuaian (d.salesAdjustmentType)
   - Tanggal Transaksi (d.transDate)

2. Bagian tabel detail item:
   - No (increment dari 1)
   - Kode (d.detailItem[n].item.no)
   - Nama Barang (d.detailItem[n].item.name)
   - Satuan (d.detailItem[n].item.unit1.name)
   - Harga (d.detailItem[n].price)

Kode yang digunakan:

```php
<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$number = $_GET['number'] ?? null;

if (!$number) {
    header('Location: index.php');
    exit;
}

// Gunakan selling price adjustment detail API berdasarkan nomor
$result = $api->getSellingPriceAdjustmentDetailByNumber($number);
$spa = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $spa = $result['data']['d'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Selling Price Adjustment - Nuansa</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Detail Selling Price Adjustment</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
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
        <?php if ($spa): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-tags text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Selling Price Adjustment Details</h2>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Dokumen</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-hashtag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['number'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Harga</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-tag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['priceCategory']['name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-building text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['spaBranchName'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Penyesuaian</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-sliders-h text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['salesAdjustmentType'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['transDate'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-400 mr-3">#</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($spa['id'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Detail Items -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Item</h3>

                        <?php if (isset($spa['detailItem']) && is_array($spa['detailItem']) && !empty($spa['detailItem'])): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($spa['detailItem'] as $index => $detail): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $index + 1; ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($detail['item']['no'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($detail['item']['name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($detail['item']['unit1']['name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo number_format($detail['price'] ?? 0, 2, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-box-open text-4xl text-gray-400"></i>
                                <p class="mt-4 text-gray-600">Tidak ada detail item yang ditemukan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Selling Price Adjustment Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>SPA Found:</strong> <?php echo $spa ? 'Yes' : 'No'; ?></p>
                        <p><strong>SPA Number:</strong> <?php echo htmlspecialchars($number); ?></p>
                        <?php if (isset($result['error'])): ?>
                            <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
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
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Selling Price Adjustment Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Selling Price Adjustment dengan nomor <?php echo htmlspecialchars($number); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar Selling Price Adjustment
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
```

Penjelasan kode:

1. Bagian PHP di awal berfungsi untuk:

   - Mengimpor file bootstrap.php untuk inisialisasi sistem
   - Membuat instance dari class AccurateAPI
   - Mengambil parameter number dari URL
   - Memanggil metode getSellingPriceAdjustmentDetailByNumber() untuk mengambil data detail
   - Menangani kasus ketika parameter number tidak diberikan dengan mengarahkan ke index.php

2. Bagian HTML menggunakan Tailwind CSS untuk tampilan:

   - Header dengan judul dan tombol kembali ke index.php serta dashboard
   - Area untuk menampilkan informasi header selling price adjustment:
     - Nomor Dokumen
     - Kategori Harga
     - Cabang
     - Tipe Penyesuaian
     - Tanggal Transaksi
     - ID
   - Tabel untuk menampilkan detail item dengan kolom:
     - No (increment dari 1)
     - Kode
     - Nama Barang
     - Satuan
     - Harga
   - Penanganan kasus ketika tidak ada data untuk ditampilkan
   - Debug info untuk membantu dalam pengembangan dan troubleshooting

3. Fitur tambahan:

   - Proteksi XSS dengan htmlspecialchars pada semua data yang ditampilkan
   - Format harga dengan number_format untuk tampilan yang lebih baik
   - Tampilan responsif yang bekerja di berbagai ukuran layar
   - Ikon dari Font Awesome untuk mempercantik tampilan
   - Penanganan error jika data tidak ditemukan

   Perintah 3:
   pada sellingprice/index.php, urutkan number by DESC

   Perintah 4:
   pada sellingprice/detail.php bagian Detail Item, buatkan search agar bisa mencari berdasarkan no / kode atau berdasarkan nama barang dan data pada detail item buatkan paging dengan pembagian 20 item per page
