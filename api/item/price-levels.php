<?php
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');

$api = new AccurateAPI();
$itemId = $_GET['id'] ?? null;

if (!$itemId || !is_numeric($itemId)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid item ID'
    ]);
    exit;
}

try {
    // Get item detail
    $result = $api->getItemDetail($itemId);
    
    if (!$result['success'] || !isset($result['data']['d'])) {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Item not found'
        ]);
        exit;
    }
    
    $item = $result['data']['d'];
    $sellingPrices = $item['detailSellingPrice'] ?? [];
    
    // Group by branch
    $groupedPrices = [];
    foreach ($sellingPrices as $price) {
        $branchName = $price['branch']['name'] ?? 'Unknown Branch';
        $branchId = $price['branch']['id'] ?? 0;
        
        if (!isset($groupedPrices[$branchName])) {
            $groupedPrices[$branchName] = [
                'branchId' => $branchId,
                'branchName' => $branchName,
                'prices' => []
            ];
        }
        
        $groupedPrices[$branchName]['prices'][] = [
            'categoryName' => $price['priceCategory']['name'] ?? 'Unknown Category',
            'categoryId' => $price['priceCategory']['id'] ?? 0,
            'price' => $price['price'] ?? 0,
            'effectiveDate' => $price['effectiveDate'] ?? 'Unknown'
        ];
    }
    
    // Sort branches alphabetically
    ksort($groupedPrices);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'itemName' => $item['name'] ?? 'Unknown Item',
            'itemNo' => $item['no'] ?? 'Unknown',
            'groupedPrices' => array_values($groupedPrices)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
