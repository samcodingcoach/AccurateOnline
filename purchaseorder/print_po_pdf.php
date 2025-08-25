<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$purchaseOrderId = $_GET['id'] ?? null;

if (!$purchaseOrderId) {
    die('Purchase Order ID is required');
}

// Get purchase order detail
$result = $api->getPurchaseOrderDetail($purchaseOrderId);
$purchaseOrder = null;

if ($result['success'] && isset($result['data'])) {
    // Cek apakah ada struktur 'd' seperti di sales invoice
    if (isset($result['data']['d'])) {
        $purchaseOrder = $result['data']['d'];
    } else {
        $purchaseOrder = $result['data'];
    }
} else {
    die('Purchase Order not found or error occurred');
}

// Helper function untuk format currency
function formatPDFCurrency($amount) {
    return $amount == floor($amount) ? number_format($amount, 0) : number_format($amount, 2);
}

// Generate PDF Content
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - <?php echo htmlspecialchars($purchaseOrder['number'] ?? $purchaseOrderId); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .document-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-right {
            text-align: right;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .table .text-right {
            text-align: right;
        }
        .table .text-center {
            text-align: center;
        }
        .summary {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 5px 0;
        }
        .summary-total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            clear: both;
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .history-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">NUANSA PURCHASE ORDER SYSTEM</div>
        <div class="document-title">PURCHASE ORDER</div>
    </div>

    <!-- Purchase Order Information -->
    <div class="document-info">
        <div class="info-left">
            <div class="info-row">
                <span class="label">PO Number:</span>
                <?php echo htmlspecialchars($purchaseOrder['number'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <?php echo htmlspecialchars($purchaseOrder['transDate'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Supplier:</span>
                <?php 
                    $vendorName = $purchaseOrder['vendor']['name'] ?? 'N/A';
                    $vendorNo = $purchaseOrder['vendor']['vendorNo'] ?? '';
                    echo htmlspecialchars($vendorName . ($vendorNo ? ' - ' . $vendorNo : ''));
                ?>
            </div>
            <div class="info-row">
                <span class="label">Payment Term:</span>
                <?php echo htmlspecialchars($purchaseOrder['paymentTerm']['name'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <?php echo htmlspecialchars($purchaseOrder['statusName'] ?? 'N/A'); ?>
            </div>
        </div>
        <div class="info-right">
            <div class="info-row">
                <span class="label">PO ID:</span>
                <?php echo htmlspecialchars($purchaseOrder['id'] ?? $purchaseOrderId); ?>
            </div>
            <div class="info-row">
                <span class="label">Exp Date:</span>
                <?php echo htmlspecialchars($purchaseOrder['dateField1'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Printed By:</span>
                <?php echo htmlspecialchars($purchaseOrder['printUserName'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">DP Amount:</span>
                <?php 
                $dpAmount = $purchaseOrder['totalDownPayment'] ?? 0;
                echo formatPDFCurrency($dpAmount);
                ?>
            </div>
        </div>
    </div>

    <!-- History Section -->
    <?php if (isset($purchaseOrder['processHistory']) && is_array($purchaseOrder['processHistory']) && !empty($purchaseOrder['processHistory'])): ?>
    <div class="history-section">
        <div class="label">Process History:</div>
        <div style="margin-left: 120px;">
            <?php 
            $historyNumbers = [];
            foreach ($purchaseOrder['processHistory'] as $history) {
                if (isset($history['historyNumber'])) {
                    $historyNumbers[] = $history['historyNumber'];
                }
            }
            echo htmlspecialchars(implode(', ', $historyNumbers));
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Items Table -->
    <?php if (isset($purchaseOrder['detailItem']) && is_array($purchaseOrder['detailItem'])): ?>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Item Name</th>
                <th>Code</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Unit</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($purchaseOrder['detailItem'] as $item): ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($item['item']['name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['availableItemUnit']['name'] ?? 'N/A'); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['unitPrice'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['itemCashDiscount'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['totalPrice'] ?? 0); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align: center; padding: 20px; color: #666;">
        No items found in this purchase order.
    </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span><?php echo formatPDFCurrency($purchaseOrder['subTotal'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>Expense:</span>
            <span><?php echo formatPDFCurrency($purchaseOrder['totalExpense'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>Discount:</span>
            <span><?php echo formatPDFCurrency($purchaseOrder['cashDiscount'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>PPN:</span>
            <span><?php echo formatPDFCurrency($purchaseOrder['tax1Amount'] ?? 0); ?></span>
        </div>
        <div class="summary-row summary-total">
            <span>Total Amount:</span>
            <span><?php echo formatPDFCurrency($purchaseOrder['totalAmount'] ?? 0); ?></span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Purchase Order generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>© <?php echo date('Y'); ?> Nuansa Purchase Order System</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>
