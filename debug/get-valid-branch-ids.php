<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Get Valid Branch IDs</h1>";

$api = new AccurateAPI();
$result = $api->getBranchList();

echo "<h2>Branch List Response:</h2>";
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";

if ($result['success'] && isset($result['data']['d'])) {
    echo "<h2>Available Branch IDs:</h2>";
    foreach ($result['data']['d'] as $branch) {
        $id = $branch['id'] ?? 'N/A';
        $name = $branch['name'] ?? 'N/A';
        echo "<p><strong>ID: $id</strong> - Name: $name</p>";
        echo "<p><a href='branch/detail.php?id=$id' target='_blank'>Test Detail with ID $id</a></p>";
        echo "<hr>";
    }
} else {
    echo "<p>Error: " . ($result['error'] ?? 'Unknown error') . "</p>";
}
?>
