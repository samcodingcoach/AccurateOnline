<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Valid Branch IDs</h1>";

$api = new AccurateAPI();
$result = $api->getBranchList();

if ($result['success'] && isset($result['data']['d'])) {
    echo "<h2>Available Branches:</h2>";
    foreach ($result['data']['d'] as $branch) {
        $id = $branch['id'] ?? 'N/A';
        $name = $branch['name'] ?? 'N/A';
        $detailUrl = "branch/detail.php?id=" . urlencode($id);
        echo "<p>ID: <strong>$id</strong> - Name: $name - <a href='$detailUrl' target='_blank'>View Detail</a></p>";
    }
} else {
    echo "<p>Error getting branch list: " . ($result['error'] ?? 'Unknown error') . "</p>";
}
?>
