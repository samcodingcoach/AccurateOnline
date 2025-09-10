<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getJournalVoucherDetail(300); // Menggunakan ID 300 sebagai contoh

echo "<pre>";
print_r($result['data']['d']['detailJournal']);
echo "</pre>";
?>