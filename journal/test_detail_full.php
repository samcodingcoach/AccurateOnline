<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getJournalVoucherDetail(300); // Menggunakan ID 300 sebagai contoh

echo "<h2>Full Response:</h2>";
echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['success'] && isset($result['data']['d'])) {
    $journal = $result['data']['d'];
    
    echo "<h2>Journal Data:</h2>";
    echo "<pre>";
    print_r($journal);
    echo "</pre>";
    
    if (isset($journal['detailJournal'])) {
        echo "<h2>Detail Journal Structure:</h2>";
        echo "<pre>";
        print_r($journal['detailJournal']);
        echo "</pre>";
    } else {
        echo "<h2>Key 'detailJournal' not found in journal data</h2>";
        
        // Check for similar keys
        foreach ($journal as $key => $value) {
            if (strpos(strtolower($key), 'detail') !== false) {
                echo "<h3>Possible detail key found: $key</h3>";
                echo "<pre>";
                print_r($value);
                echo "</pre>";
            }
        }
    }
}
?>