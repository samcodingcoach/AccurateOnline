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

   Perintah 5:

Perintah 6:
tambahkan menu setelah Price Category Management Kelola Kategori Harga ada tombol untuk menuju sellingprice/index.php dan API JSON dapat membuka modal yang memunculkan list_spa.php

perintah 7:
pada file item/listv2.php gantilah tampilan data yang awalnya table menjadi list berbentuk card

perintah 8:

1. buatkan filter lain search berdasarkan nama barang
2. perbaiki modal price level dengan desain yang lebih baik
   - tab diganti dengan combobox, dropdownlist. memilih data cabang pakai dropdownlist

perintah 9:

1. pastikan modal Price Levels ukurannya fit, karna di punya saya height modalnya tidak proper ada data yang tidak terlihat dan tertutup
2. pada modal price level bagian dropdown ada dua simbol v. hapus salah satu yang buruk

perintah 10

1. chevron yang anda berikan kurang menarik
   berikut contoh dari dokumentasi tailwind flowbite

<button id="dropdownDefaultButton" data-dropdown-toggle="dropdown" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">Dropdown button <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
</svg>
</button>

<!-- Dropdown menu -->
<div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
      <li>
        <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Dashboard</a>
      </li>
      
    </ul>
</div>

2. saat merubah pilih ada loading / effect reload data minimal 3 detik walaupun datanya kosong/ada

perintah 11:
ubah urutan kolom modal price level
Price Category Effective Date Price

perintah 12:

nuansa/item/listv2.php pada halaman ini, filter tanggal itu berbeda dengan pencarian nama barang bukan satu perintah

akhirnya error
Fatal error: Uncaught Error: Call to undefined method AccurateAPI::getItemListWithFilter() in C:\xampp\htdocs\nuansa\item\listv2.php:39 Stack trace: #0 {main} thrown in C:\xampp\htdocs\nuansa\item\listv2.php on line 39

Perintah 13:
ketika saya masukan inputan ke misal 'Poco M5 Pro' ke input search klik filter nama, url yang jadi adalah
http://localhost/nuansa/item/listv2.php?start_picker=&end_picker=&search=Poco+M5+Pro&action=filter_name
Filter Nama: Poco M5 Pro
(9 items ditemukan)
padahal datanya cuma 1 menurut table

Perintah 14:
-pada list card untuk name sebaiknya pakai teknik string Food Chopper Motto CN 2505 menjadi Food Chopper Motto... jika terlalu panjang apalagi pada saat mobile preview

perintah 15
-perbaiki responsive filter tanggal
-perbaiki responsive pada bagian Daftar Barang dan dua tombol Add new item, dashboard

perintah 16
-redesign nuansa/item/new_item.php agar lebih baik dan proper dari sisi layout, responsive dll

perintah 17 di new_item.php

-saat simpan item dan harga ada info yang ada di 699-799, tolong redesign dengan menempatkannya di tengah-tengah
rapi dan readable informatif, serta blur/hitamkan halaman selama proses

perintah 18
// Tampilkan hasil di itemStatus seperti sebelumnya line 811
ini seharusnya di tampilkan pada modal ditengah layar itu juga. buat yang bagus rapi dan responsive, karna jika lama itu berantakan.

perintah 19

1. cek pada new item untuk switch Aktif S/N apakah mengirim body dengan value true/false
2. setelah selesai tau nomor 1 adalah true/false pastikan tersimpan pada AccurateAPI.php public function saveItem($itemData) line 1561 membaca body aktif s/n tersebut. karna sebelumnya tidak tersimpan
   saya pilih switch true tapi tidak tersimpan.

perintah 20
-pada AccurateAPI.php baris 1577
$postData = [
'no' => $itemData['no'],
'itemCategoryName' => $itemData['itemCategoryName'],
'itemType' => $itemData['itemType'] ?? 'INVENTORY',
'name' => $itemData['name'],
'unit1Name' => $itemData['unit1Name'],
'manageSN' => $itemData['manageSN'],
'serialNumberType' => $itemData['serialNumberType'] ?? 'UNIQUE'
];

        saya merasa serialNumberType ?? 'UNIQUE' tidak ada asal inputnya dari new_item.php,
        jadikan UNIQUE sebagai nilai pasti saja.

Perintah 21:
saya rasa tidak tersimpan
pastikan pada new_item.php <input type="checkbox" id="aktifSN" name="manageSN"> ini saat dikirim ke accurateAPI.php adalah aktif = true dan tidak aktif = false

karna pada saat saya melakukan hardcode (managesn = true) langsung tersimpan, tetapi jika pakai nilai checkbox tidak tersimpan/terbawa

AccurateAPI.php line 1577
$postData = [
'no' => $itemData['no'],
'itemCategoryName' => $itemData['itemCategoryName'],
'itemType' => $itemData['itemType'] ?? 'INVENTORY',
'name' => $itemData['name'],
'unit1Name' => $itemData['unit1Name'],
'manageSN' => 'true',
'serialNumberType' => 'UNIQUE'
];

pastikan yang dibawa manageSN

Perintah 22:
salesinvoice/new_invoice.php kemudian detail item kemudian input serial, muncul modal
kemudian didalam modal bagian warehouse itu isinya 'Warehouse Utama'?
ganti dengan nilai default yang ada di end point berikut

http://localhost/nuansa/warehouse/listwarehouse.php
{
"success": true,
"message": "Data warehouse berhasil diambil",
"data": {
"s": true,
"d": [
{
"defaultWarehouse": false,
"scrapWarehouse": false,
"address": {
"zipCode": "",
"country": "Indonesia",
"address": "Jl. Untung Suropati No.30\nSamarinda Kalimantan Timur\nIndonesia",
"province": "Kalimantan Timur",
"city": "Samarinda",
"street": "Jl. Untung Suropati No.30",
"name": "Matos Gudang",
"concatFullAddress": "Jl. Untung Suropati No.30 Samarinda Kalimantan Timur Indonesia",
"picMobileNo": null,
"picName": null,
"id": 200
},
"locationId": 200,
"optLock": 1,
"name": "Matos Gudang",
"description": null,
"pic": null,
"id": 100,
"suspended": false
},
{
"defaultWarehouse": true,
"scrapWarehouse": false,
"address": {
"zipCode": "",
"country": "Indonesia",
"address": "Jl. P. Suryanata SCB\nSamarinda Kalimantan Timur\nIndonesia",
"province": "Kalimantan Timur",
"city": "Samarinda",
"street": "Jl. P. Suryanata SCB",
"name": "Pusat - Suryanata",
"concatFullAddress": "Jl. P. Suryanata SCB Samarinda Kalimantan Timur Indonesia",
"picMobileNo": null,
"picName": null,
"id": 52
},
"locationId": 52,
"optLock": 2,
"name": "Pusat - Suryanata",
"description": null,
"pic": null,
"id": 50,
"suspended": false
}
],
"sp": {
"page": 1,
"sort": null,
"pageSize": 25,
"pageCount": 1,
"rowCount": 2,
"start": 0,
"limit": null
}
},
"timestamp": "2025-09-06 09:54:10"
}
ketika tambah serial misal 50001
kemudian jika stok tidak terdapat di default akan muncul alert berikut
Serial number "50001" ditemukan di warehouse "Matos Gudang", bukan di "Warehouse Utama".

maka buat dialog yes/no 'Apakah ingin pakai stok Matos Gudang?'
yes melanjutkan. dan masuk di table dibawahnya.

perintah 23.

sudah benar default warehouse tetapi saat saya tambah data serial muncul
Serial number "50001" ditemukan di warehouse "Matos Gudang", bukan di "Pusat - Suryanata".
harusnya beri pilihan yes no agar mimilih warehouse sesuai dengan serial number

perintah 24
createSalesInvoice() tidak ada mari dibuat

berikut endpoint /api/sales-invoice/save.do
berikut bodynya yang saya ambil dr post man code http

POST /accurate/api/sales-invoice/save.do HTTP/1.1
Host: zeus.accurate.id
Content-Type: application/x-www-form-urlencoded
X-Session-ID: 6d5cd7c5-092b-497c-b29a-d9105b1f7173
Authorization: Bearer 10c4c410-200a-4ed7-80ee-cacc865e5054
Content-Length: 382

customerNo=C.00002&detailItem%5B0%5D.itemNo=100001&detailItem%5B0%5D.unitPrice=18150000&detailItem%5B0%5D.detailSerialNumber%5B0%5D.serialNumberNo=8993&detailItem%5B0%5D.warehouseName=Pusat%20-%20Suryanata&branchId=50&detailItem%5B0%5D.quantity=1&detailItem%5B0%5D.detailSerialNumber%5B0%5D.quantity=1&paymentTerm=&taxable=&inclusiveTax=&toAddress=&description=&transDate=&shipDate=

abaikan valuenya. yang penting keyfieldnya , perhatikan juga header parameternya

perintah 25
saya baru menambahkan scope glaccount_view, buatkan sebuah output di glaccount/list_coa.php dari endpoint berikut
-/api/glaccount/list.do
saya udah melakukan test di post man dan berhasil. Berikut kode dari postman
GET /accurate/api/glaccount/list.do?fields=id,accountTypeName,balance,name,no,lvl,isParent&sp.pageSize=50 HTTP/1.1
Host: zeus.accurate.id
X-Session-ID: XXXX
Content-Type: application/json
Authorization: Bearer XXXX

jika anda lupa stepnya anda bisa mencontoh di itemcategory/listcategory.php
anda cek juga classes/AccurateAPI.php, jika menemukan yang mirip tolong dibiarkan dan buat function baru saja.

perintah 26
buatkan sebuah output di glaccount/detail_coa.php dari endpoint berikut
-/api/glaccount/detail.do
saya sudah melakukan test di postman dan berhasil. berikut kode dari postman

GET /accurate/api/glaccount/detail.do?id=66 HTTP/1.1
Host: zeus.accurate.id
X-Session-ID: XXXX
Content-Type: application/json
Authorization: Bearer XXX

perintah 27
pada file glaccount/index.php buatlah ui untuk menampilkan data list_coa.php
ambil contoh di itemcategory/index.php, ikutin patternya

perintah 28
pada file glaccount/detail.php buatlah ui untuk menampilkan data detail_coa.php
ambil contoh di itemcategory/detail.php, ikutin pattern UI nya

yang di tampilkan adalah
nomor = data.d.no
tanggal dibuat = data.d.asOf
akun tipe = data.d.accountTypeName
saldo = data.d.balance
id = data.d.id
level = data.d.lvl
keterangan = data.d.name
saldo awal = data.d.openBalance

kemudian ada table mumunculkan child/ nasted didalamnya ada perulangan
No, No.COA, ,ID , Nama Akun
X, data.d.childList[0].no ,data.d.childList[0].id , data.d.childList[0].name

perintah 29
buatlah menu seperti Branch Management - Kelola Cabang, posisinya sebelum branch management di index.php
untuk General Account - Kelola Akun Perkiraan. ambil dari folder glaccount

perintah 30
buatkan sebuah route di file journal/list_journal.php dari endpoint berikut
GET
api/journal-voucher/list.do

anda bisa pelajari dahulu patternya di file list_itemtransfer.php

dari postman saya berhasil melakukan test berikut kodenya

GET /accurate/api/journal-voucher/list.do?fields=id,number,transDate,transNumber,description HTTP/1.1
Host: zeus.accurate.id
X-Session-ID: XXX
Accept: application/json
Authorization: Bearer XXX

file yang perlu anda simak classes/AccurateAPI.php

perintah 31
lakukan hal yang sama pada perintah 30 untuk journal/detail_journal.php
berikut endpoint nya
api/journal-voucher/detail.do?id=300 adanya parameter id yang diperlukan
