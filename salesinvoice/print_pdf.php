<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$salesInvoiceId = $_GET['id'] ?? null;

if (!$salesInvoiceId) {
    die('Sales Invoice ID is required');
}

// Get sales invoice detail
$result = $api->getSalesInvoiceDetail($salesInvoiceId);
$salesInvoice = null;

if ($result['success'] && isset($result['data']['d'])) {
    $salesInvoice = $result['data']['d'];
} else {
    die('Sales Invoice not found or error occurred');
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
    <title>Sales Invoice - <?php echo htmlspecialchars($salesInvoice['number'] ?? $salesInvoiceId); ?></title>
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
        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-left, .invoice-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-right {
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">NUANSA INVOICE SYSTEM</div>
        <div class="invoice-title">SALES INVOICE</div>
    </div>

    <!-- Invoice Information -->
    <div class="invoice-info">
        <div class="invoice-left">
            <div class="info-row">
                <span class="label">Invoice No:</span>
                <?php echo htmlspecialchars($salesInvoice['number'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <?php echo htmlspecialchars($salesInvoice['shipDate'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Customer:</span>
                <?php echo htmlspecialchars($salesInvoice['customer']['name'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Customer No:</span>
                <?php echo htmlspecialchars($salesInvoice['customer']['customerNo'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">PO Number:</span>
                <?php echo htmlspecialchars($salesInvoice['poNumber'] ?? 'N/A'); ?>
            </div>
        </div>
        <div class="invoice-right">
            <div class="info-row">
                <span class="label">Invoice ID:</span>
                <?php echo htmlspecialchars($salesInvoice['id'] ?? $salesInvoiceId); ?>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <?php echo htmlspecialchars($salesInvoice['statusName'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Branch:</span>
                <?php echo htmlspecialchars($salesInvoice['branchName'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Sales:</span>
                <?php echo htmlspecialchars($salesInvoice['masterSalesmanName'] ?? 'N/A'); ?>
            </div>
            <div class="info-row">
                <span class="label">Payment Term:</span>
                <?php echo htmlspecialchars($salesInvoice['paymentTerm']['name'] ?? 'N/A'); ?>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div style="margin-bottom: 20px;">
        <div class="label">Ship To Address:</div>
        <div style="margin-left: 120px; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
            <?php echo htmlspecialchars($salesInvoice['toAddress'] ?? 'N/A'); ?>
        </div>
    </div>

    <!-- Items Table -->
    <?php if (isset($salesInvoice['detailItem']) && is_array($salesInvoice['detailItem'])): ?>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Product Name</th>
                <th>Code</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($salesInvoice['detailItem'] as $item): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($item['detailName'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['unitPrice'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['itemCashDiscount'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatPDFCurrency($item['totalPrice'] ?? 0); ?></td>
            </tr>
            
            <!-- Serial Numbers -->
            <?php if (isset($item['detailSerialNumber']) && is_array($item['detailSerialNumber']) && !empty($item['detailSerialNumber'])): ?>
            <tr>
                <td></td>
                <td colspan="6" style="font-size: 10px; color: #666; padding-left: 20px;">
                    <strong>Serial Numbers:</strong>
                    <?php 
                    $serials = [];
                    foreach ($item['detailSerialNumber'] as $serialData) {
                        if (isset($serialData['serialNumber'])) {
                            if (is_array($serialData['serialNumber'])) {
                                foreach ($serialData['serialNumber'] as $serial) {
                                    if (isset($serial['Number'])) {
                                        $serialNum = $serial['Number'];
                                        if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialNum)) {
                                            $serials[] = $serialNum;
                                        }
                                    } elseif (is_string($serial)) {
                                        if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serial)) {
                                            $serials[] = $serial;
                                        }
                                    }
                                }
                            } elseif (is_string($serialData['serialNumber'])) {
                                if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialData['serialNumber'])) {
                                    $serials[] = $serialData['serialNumber'];
                                }
                            }
                        } elseif (isset($serialData['Number'])) {
                            $serialNum = $serialData['Number'];
                            if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialNum)) {
                                $serials[] = $serialNum;
                            }
                        } elseif (is_string($serialData)) {
                            if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialData)) {
                                $serials[] = $serialData;
                            }
                        }
                    }
                    $serials = array_unique($serials);
                    echo !empty($serials) ? htmlspecialchars(implode(', ', $serials)) : 'No serial numbers';
                    ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span><?php echo formatPDFCurrency($salesInvoice['subTotal'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span>Tax Total:</span>
            <span><?php 
                $taxTotal = 0;
                if (isset($salesInvoice['detailTax']) && is_array($salesInvoice['detailTax'])) {
                    foreach ($salesInvoice['detailTax'] as $tax) {
                        if (isset($tax['taxAmount'])) {
                            $taxTotal += $tax['taxAmount'];
                        }
                    }
                }
                echo formatPDFCurrency($taxTotal);
            ?></span>
        </div>
        <div class="summary-row">
            <span>Discount:</span>
            <span><?php echo formatPDFCurrency($salesInvoice['cashDiscount'] ?? 0); ?></span>
        </div>
        <div class="summary-row summary-total">
            <span>Total Amount:</span>
            <span><?php echo formatPDFCurrency($salesInvoice['totalAmount'] ?? 0); ?></span>
        </div>
    </div>

    <!-- Tax Information -->
    <div style="clear: both; margin-top: 40px;">
        <h4>Tax Information</h4>
        <div class="info-row">
            <span class="label">Tax Number:</span>
            <?php echo htmlspecialchars($salesInvoice['taxNumber'] ?? 'N/A'); ?>
        </div>
        <div class="info-row">
            <span class="label">Tax Date:</span>
            <?php echo htmlspecialchars($salesInvoice['taxDate'] ?? 'N/A'); ?>
        </div>
        <div class="info-row">
            <span class="label">Taxable:</span>
            <?php echo ($salesInvoice['taxable'] ?? false) ? 'Yes' : 'No'; ?>
        </div>
        <div class="info-row">
            <span class="label">Inclusive Tax:</span>
            <?php echo ($salesInvoice['inclusiveTax'] ?? false) ? 'Yes' : 'No'; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on <?php echo date('Y-m-d H:i:s'); ?> | Nuansa Invoice System</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Set headers untuk PDF download
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="invoice_' . $salesInvoiceId . '.html"');

// Output HTML (bisa diprint ke PDF via browser)
echo $html;
?>
