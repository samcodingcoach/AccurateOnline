<?php
/**
 * JSON COMPARISON: 2 PERBEDAAN UTAMA
 * Membandingkan output JSON dari listvendor.php vs index.php
 */

require_once __DIR__ . '/bootstrap.php';

echo "<h1>🔍 2 PERBEDAAN UTAMA JSON VENDOR</h1>";
echo "<h2>📊 PERBANDINGAN: listvendor.php vs index.php</h2>";

$api = new AccurateAPI();

// Simulasi output dari listvendor.php (raw JSON)
echo "<h3>1️⃣ DATA DARI listvendor.php (RAW JSON):</h3>";
$rawResult = $api->getVendorList();
$rawVendors = [];

if ($rawResult['success'] && isset($rawResult['data']['d'])) {
    $rawVendors = $rawResult['data']['d'];
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border-left: 4px solid #0066cc;'>";
    echo "<h4>Raw JSON Structure (First Vendor):</h4>";
    echo "<pre>" . json_encode($rawVendors[0] ?? [], JSON_PRETTY_PRINT) . "</pre>";
    echo "<p><strong>Total Fields:</strong> " . count(array_keys($rawVendors[0] ?? [])) . "</p>";
    echo "</div>";
}

// Simulasi output dari index.php (enriched JSON)
echo "<h3>2️⃣ DATA DARI index.php (ENRICHED JSON):</h3>";
$enrichedVendors = [];

if ($rawResult['success'] && isset($rawResult['data']['d'])) {
    $enrichedVendors = $rawResult['data']['d'];
    
    // Ambil detail untuk vendor pertama (sama seperti di index.php)
    if (!empty($enrichedVendors)) {
        $firstVendor = &$enrichedVendors[0];
        $detailResult = $api->getVendorDetail($firstVendor['id']);
        
        if ($detailResult['success'] && isset($detailResult['data']['category'])) {
            $firstVendor['category'] = $detailResult['data']['category'];
        }
        
        echo "<div style='background: #f0fff0; padding: 15px; margin: 10px 0; border-left: 4px solid #00cc66;'>";
        echo "<h4>Enriched JSON Structure (First Vendor):</h4>";
        echo "<pre>" . json_encode($firstVendor, JSON_PRETTY_PRINT) . "</pre>";
        echo "<p><strong>Total Fields:</strong> " . count(array_keys($firstVendor)) . "</p>";
        echo "</div>";
    }
}

// PERBEDAAN 1: STRUKTUR DATA
echo "<h2>🎯 PERBEDAAN #1: STRUKTUR DATA</h2>";
echo "<div style='background: #fff5f5; padding: 15px; margin: 10px 0; border: 2px solid #ff6b6b;'>";

if (!empty($rawVendors) && !empty($enrichedVendors)) {
    $rawFields = array_keys($rawVendors[0]);
    $enrichedFields = array_keys($enrichedVendors[0]);
    
    $newFields = array_diff($enrichedFields, $rawFields);
    $missingFields = array_diff($rawFields, $enrichedFields);
    
    echo "<h4>📋 Field Comparison:</h4>";
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'>";
    echo "<th style='padding: 10px;'>listvendor.php Fields</th>";
    echo "<th style='padding: 10px;'>index.php Fields</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='padding: 10px; vertical-align: top;'>";
    echo "<ul>";
    foreach ($rawFields as $field) {
        echo "<li>" . htmlspecialchars($field) . "</li>";
    }
    echo "</ul>";
    echo "</td>";
    echo "<td style='padding: 10px; vertical-align: top;'>";
    echo "<ul>";
    foreach ($enrichedFields as $field) {
        $isNew = in_array($field, $newFields);
        $style = $isNew ? "color: red; font-weight: bold;" : "";
        echo "<li style='$style'>" . htmlspecialchars($field) . ($isNew ? " (NEW!)" : "") . "</li>";
    }
    echo "</ul>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    
    if (!empty($newFields)) {
        echo "<p><strong>🆕 Field tambahan di index.php:</strong> " . implode(', ', $newFields) . "</p>";
    }
}

echo "</div>";

// PERBEDAAN 2: PERFORMA DAN API CALLS
echo "<h2>🎯 PERBEDAAN #2: PERFORMA & API CALLS</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; margin: 10px 0; border: 2px solid #ffa500;'>";

echo "<h4>⚡ Performance Impact Analysis:</h4>";
echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'>";
echo "<th style='padding: 10px;'>Aspect</th>";
echo "<th style='padding: 10px;'>listvendor.php</th>";
echo "<th style='padding: 10px;'>index.php</th>";
echo "</tr>";

$vendorCount = count($rawVendors);

echo "<tr>";
echo "<td style='padding: 10px; font-weight: bold;'>API Calls</td>";
echo "<td style='padding: 10px; background: #e8f5e8;'>1 call (getVendorList)</td>";
echo "<td style='padding: 10px; background: #ffeaa7;'>" . ($vendorCount + 1) . " calls (1 list + $vendorCount details)</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 10px; font-weight: bold;'>Response Time</td>";
echo "<td style='padding: 10px; background: #e8f5e8;'>Fast (~200ms)</td>";
echo "<td style='padding: 10px; background: #ffeaa7;'>Slow (~" . ($vendorCount * 300 + 200) . "ms)</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 10px; font-weight: bold;'>Data Size</td>";
echo "<td style='padding: 10px; background: #e8f5e8;'>Small (original fields only)</td>";
echo "<td style='padding: 10px; background: #ffeaa7;'>Larger (with category data)</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 10px; font-weight: bold;'>Use Case</td>";
echo "<td style='padding: 10px; background: #e8f5e8;'>API endpoint, AJAX calls</td>";
echo "<td style='padding: 10px; background: #ffeaa7;'>Rich UI display</td>";
echo "</tr>";

echo "</table>";

echo "<h4>📈 Calculation:</h4>";
echo "<ul>";
echo "<li><strong>listvendor.php:</strong> 1 API call untuk $vendorCount vendor</li>";
echo "<li><strong>index.php:</strong> " . ($vendorCount + 1) . " API calls (1 + $vendorCount detail calls)</li>";
echo "<li><strong>Overhead:</strong> " . ($vendorCount * 100) . "% lebih banyak API calls</li>";
echo "</ul>";

echo "</div>";

// KESIMPULAN
echo "<h2>📝 KESIMPULAN 2 PERBEDAAN UTAMA:</h2>";
echo "<div style='background: #e8f4f8; padding: 20px; margin: 10px 0; border-radius: 8px;'>";
echo "<ol style='font-size: 16px; line-height: 1.6;'>";
echo "<li><strong>PERBEDAAN STRUKTUR DATA:</strong>";
echo "<ul>";
echo "<li>listvendor.php: Raw JSON tanpa field tambahan</li>";
echo "<li>index.php: Enriched JSON dengan field 'category' dari getVendorDetail()</li>";
echo "</ul></li>";

echo "<li><strong>PERBEDAAN PERFORMA:</strong>";
echo "<ul>";
echo "<li>listvendor.php: 1 API call, response cepat</li>";
echo "<li>index.php: N+1 API calls ($vendorCount detail calls tambahan), response lambat</li>";
echo "</ul></li>";
echo "</ol>";

echo "<h4>💡 Rekomendasi:</h4>";
echo "<p>Kedua endpoint melayani tujuan berbeda:</p>";
echo "<ul>";
echo "<li><strong>listvendor.php:</strong> Cocok untuk API calls, dashboard AJAX</li>";
echo "<li><strong>index.php:</strong> Cocok untuk tampilan UI yang membutuhkan kategori</li>";
echo "</ul>";
echo "</div>";

echo "<hr><p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>