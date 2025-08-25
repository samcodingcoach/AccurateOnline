<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$purchaseInvoiceId = $_GET['id'] ?? null;

if (!$purchaseInvoiceId) {
    die('Purchase Invoice ID is required');
}

// Get purchase invoice detail
$result = $api->getPurchaseInvoiceDetail($purchaseInvoiceId);
$purchaseInvoice = null;

if ($result['success'] && isset($result['data'])) {
    // Cek apakah ada struktur 'd' seperti di sales invoice
    if (isset($result['data']['d'])) {
        $purchaseInvoice = $result['data']['d'];
    } else {
        $purchaseInvoice = $result['data'];
    }
} else {
    die('Purchase Invoice not found or error occurred');
}

// Helper function untuk format currency
function formatPDFCurrency($amount) {
    return 'Rp ' . ($amount == floor($amount) ? number_format($amount, 0) : number_format($amount, 2));
}

// Generate PDF Content
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - <?php echo htmlspecialchars($purchaseInvoice['number'] ?? $purchaseInvoiceId); ?></title>
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
        .tax-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .expense-table {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">NUANSA PURCHASE INVOICE SYSTEM</div>
        <div class="document-title">PURCHASE INVOICE</div>
    </div>

    <!-- Purchase Invoice Information -->
    <div class="document-info">
        <div class="info-left">
            <div class="info-row">
                <span class="label">Form Number:</span>
                <?php echo htmlspecialchars($purchaseInvoice['number'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Bill Number:</span>
                <?php echo htmlspecialchars($purchaseInvoice['billNumber'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <?php echo htmlspecialchars($purchaseInvoice['transDate'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Supplier:</span>
                <?php 
                    $vendorName = $purchaseInvoice['vendor']['name'] ?? 'N/A';
                    $vendorNo = $purchaseInvoice['vendor']['vendorNo'] ?? '';
                    echo htmlspecialchars($vendorName . ($vendorNo ? ' - ' . $vendorNo : ''));
                ?>
            </div>
            <div class="info-row">
                <span class="label">Payment Term:</span>
                <?php echo htmlspecialchars($purchaseInvoice['paymentTerm']['name'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <?php 
                    $statusName = $purchaseInvoice['statusName'] ?? 'N/A';
                    $status = $purchaseInvoice['status'] ?? '';
                    echo htmlspecialchars($statusName . ($status ? ' - ' . $status : ''));
                ?>
            </div>
        </div>
        <div class="info-right">
            <div class="info-row">
                <span class="label">Invoice ID:</span>
                <?php echo htmlspecialchars($purchaseInvoice['id'] ?? $purchaseInvoiceId); ?>
            </div>
            <div class="info-row">
                <span class="label">Ship Date:</span>
                <?php echo htmlspecialchars($purchaseInvoice['shipDate'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Branch ID:</span>
                <?php echo htmlspecialchars($purchaseInvoice['branchId'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Bank Account:</span>
                <?php 
                    $bankAccount = $purchaseInvoice['vendorBankAccount']['bankAccount'] ?? '';
                    $bankName = $purchaseInvoice['vendorBankAccount']['bankName'] ?? '';
                    echo htmlspecialchars($bankAccount ? $bankAccount . ' - ' . $bankName : 'N/A');
                ?>
            </div>
        </div>
    </div>

    <!-- Tax & Document Information -->
    <?php 
    $taxInfo = '';
    if (isset($purchaseInvoice['detailTax']) && is_array($purchaseInvoice['detailTax']) && !empty($purchaseInvoice['detailTax'])) {
        $firstTax = $purchaseInvoice['detailTax'][0];
        $taxName = $firstTax['tax']['taxInfo'] ?? '';
        $taxDate = $purchaseInvoice['taxDate'] ?? '';
        $taxInfo = $taxName . ($taxDate ? ' - ' . $taxDate : '');
    }
    $documentCode = $purchaseInvoice['documentCode'] ?? '';
    $taxNumber = $purchaseInvoice['taxNumber'] ?? '';
    $docInfo = $documentCode . ($taxNumber ? ' - ' . $taxNumber : '');
    
    if ($taxInfo || $docInfo): ?>
    <div class="tax-section">
        <?php if ($taxInfo): ?>
        <div class="info-row">
            <span class="label">Tax Info:</span>
            <?php echo htmlspecialchars($taxInfo); ?>
        </div>
        <?php endif; ?>
        <?php if ($docInfo): ?>
        <div class="info-row">
            <span class="label">Document Type:</span>
            <?php echo htmlspecialchars($docInfo); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Items Table -->
    <?php if (isset($purchaseInvoice['detailItem']) && is_array($purchaseInvoice['detailItem'])): ?>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th class="text-center">Kts</th>
                <th class="text-center">Satuan</th>
                <th class="text-center">Gudang</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($purchaseInvoice['detailItem'] as $item): ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($item['item']['name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['item']['unit1']['name'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['warehouse']['name'] ?? 'N/A'); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['item']['unitPrice'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['itemCashDiscount'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['totalPrice'] ?? 0); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align: center; padding: 20px; color: #666;">
        No items found in this purchase invoice.
    </div>
    <?php endif; ?>

    <!-- Expense Table -->
    <?php if (isset($purchaseInvoice['detailExpense']) && is_array($purchaseInvoice['detailExpense']) && !empty($purchaseInvoice['detailExpense'])): ?>
    <div class="expense-table">
        <h3 style="margin-bottom: 10px;">Expenses</h3>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Nama Biaya</th>
                    <th>Kode</th>
                    <th class="text-right">Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($purchaseInvoice['detailExpense'] as $expense): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($expense['expenseName'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($expense['account']['no'] ?? 'N/A'); ?></td>
                    <td class="text-right"><?php echo formatPDFCurrency($expense['expenseAmount'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($expense['expenseNotes'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span><?php echo formatPDFCurrency($purchaseInvoice['subTotal'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>Expense:</span>
            <span><?php echo formatPDFCurrency($purchaseInvoice['totalExpense'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>Discount:</span>
            <span><?php echo formatPDFCurrency($purchaseInvoice['cashDiscount'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>PPN:</span>
            <span><?php echo formatPDFCurrency($purchaseInvoice['tax1Amount'] ?? 0); ?></span>
        </div>
        <div class="summary-row summary-total">
            <span>Total Amount:</span>
            <span><?php echo formatPDFCurrency($purchaseInvoice['totalAmount'] ?? 0); ?></span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Purchase Invoice generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>© <?php echo date('Y'); ?> Nuansa Purchase Invoice System</p>
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
