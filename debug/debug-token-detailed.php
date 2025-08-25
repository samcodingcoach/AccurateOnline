<?php
require_once '../bootstrap.php';
require_once '../classes/AccurateAPI.php';

// Buat instance API
$api = new AccurateAPI();

echo "<h2>Debug Token Status</h2>";

// Test checkTokenStatus dengan berbagai parameter
echo "<h3>1. Check dengan required scopes: item_view, branch_view, vendor_view</h3>";
$status1 = $api->checkTokenStatus(['item_view', 'branch_view', 'vendor_view']);
echo "<pre>";
var_dump($status1);
echo "</pre>";

echo "<hr>";

echo "<h3>2. Check tanpa required scopes (default)</h3>";
$status2 = $api->checkTokenStatus();
echo "<pre>";
var_dump($status2);
echo "</pre>";

echo "<hr>";

echo "<h3>3. Test getApprovedScopes langsung</h3>";
$scopes = $api->getApprovedScopes();
echo "<pre>";
var_dump($scopes);
echo "</pre>";

echo "<hr>";

echo "<h3>4. Test individual scope tests</h3>";

echo "<h4>Item View Test:</h4>";
try {
    $itemTest = $api->testItemView();
    echo "Result: " . ($itemTest ? 'PASS' : 'FAIL');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h4>Branch View Test:</h4>";
try {
    $branchTest = $api->testBranchView();
    echo "Result: " . ($branchTest ? 'PASS' : 'FAIL');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h4>Vendor View Test:</h4>";
try {
    $vendorTest = $api->testVendorView();
    echo "Result: " . ($vendorTest ? 'PASS' : 'FAIL');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h4>Warehouse View Test:</h4>";
try {
    $warehouseTest = $api->testWarehouseView();
    echo "Result: " . ($warehouseTest ? 'PASS' : 'FAIL');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
