<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getJournalVoucherList();

echo "<pre>";
print_r($result);
echo "</pre>";
?>