<?php
/**
 * SOLUSI UNTUK MASALAH PERBEDAAN DATA VENDOR
 * 
 * Masalah: listvendor.php dan index.php menampilkan data yang berbeda
 * Penyebab: index.php melakukan enrichment data dengan memanggil getVendorDetail()
 */

require_once __DIR__ . '/bootstrap.php';

echo "<h1>🔍 Analisis Perbedaan Data Vendor</h1>";

$api = new AccurateAPI();

echo "<h2>📊 MASALAH YANG DITEMUKAN:</h2>";
echo "<ul>";
echo "<li><strong>listvendor.php:</strong> Mengembalikan data mentah dari API</li>";
echo "<li><strong>index.php:</strong> Melakukan enrichment dengan memanggil getVendorDetail() untuk setiap vendor</li>";
echo "<li><strong>Dampak:</strong> Data yang ditampilkan berbeda karena index.php memiliki informasi tambahan (kategori)</li>";
echo "<li><strong>Performance:</strong> index.php melakukan N+1 API calls (lambat)</li>";
echo "</ul>";

echo "<h2>💡 SOLUSI YANG DISARANKAN:</h2>";
echo "<ol>";
echo "<li><strong>Optimasi index.php:</strong> Hanya ambil detail jika diperlukan</li>";
echo "<li><strong>Standarisasi data:</strong> Pastikan kedua endpoint mengembalikan struktur data yang sama</li>";
echo "<li><strong>Caching:</strong> Implementasi cache untuk detail vendor</li>";
echo "<li><strong>API improvement:</strong> Modifikasi getVendorList() untuk include kategori</li>";
echo "</ol>";

echo "<h2>🛠️ IMPLEMENTASI PERBAIKAN:</h2>";

// Solusi 1: Optimasi dengan batasi detail calls
$result = $api->getVendorList();
$vendors = [];

if ($result['success'] && isset($result['data']['d'])) {
    $vendors = $result['data']['d'];
    
    echo "<p><strong>Data asli vendor (tanpa enrichment):</strong></p>";
    echo "<pre>";
    foreach (array_slice($vendors, 0, 2) as $i => $vendor) {
        echo "Vendor " . ($i+1) . ": " . ($vendor['name'] ?? 'N/A') . "\n";
        echo "  - ID: " . ($vendor['id'] ?? 'N/A') . "\n";
        echo "  - Email: " . ($vendor['email'] ?? 'N/A') . "\n";
        echo "  - Phone: " . ($vendor['mobilePhone'] ?? 'N/A') . "\n";
        echo "  - Fields: " . implode(', ', array_keys($vendor)) . "\n\n";
    }
    echo "</pre>";
    
    echo "<p><strong>Data setelah enrichment (seperti di index.php):</strong></p>";
    echo "<pre>";
    foreach (array_slice($vendors, 0, 2) as $i => $vendor) {
        // Simulate enrichment
        $detailResult = $api->getVendorDetail($vendor['id']);
        if ($detailResult['success'] && isset($detailResult['data']['category'])) {
            $vendor['category'] = $detailResult['data']['category'];
        }
        
        echo "Vendor " . ($i+1) . " (enriched): " . ($vendor['name'] ?? 'N/A') . "\n";
        echo "  - ID: " . ($vendor['id'] ?? 'N/A') . "\n";
        echo "  - Email: " . ($vendor['email'] ?? 'N/A') . "\n";
        echo "  - Phone: " . ($vendor['mobilePhone'] ?? 'N/A') . "\n";
        echo "  - Category: " . (isset($vendor['category']['name']) ? $vendor['category']['name'] : 'General') . "\n";
        echo "  - Fields: " . implode(', ', array_keys($vendor)) . "\n\n";
    }
    echo "</pre>";
}

echo "<h2>✅ KESIMPULAN:</h2>";
echo "<p>Perbedaan data terjadi karena <strong>index.php melakukan enrichment data</strong> dengan memanggil API detail untuk setiap vendor, sedangkan <strong>listvendor.php mengembalikan data mentah</strong>.</p>";
echo "<p>Untuk menyelaraskan keduanya, Anda bisa:</p>";
echo "<ul>";
echo "<li>Modifikasi listvendor.php untuk juga melakukan enrichment</li>";
echo "<li>Atau modifikasi index.php untuk tidak melakukan enrichment</li>";
echo "<li>Atau buat endpoint baru yang sudah include kategori dari awal</li>";
echo "</ul>";
?>