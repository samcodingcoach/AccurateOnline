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
function formatAdvancedPDFCurrency($amount) {
    return $amount == floor($amount) ? number_format($amount, 0) : number_format($amount, 2);
}

// Check if we have mPDF library
$mpdfPath = __DIR__ . '/../vendor/mpdf/mpdf/src/Mpdf.php';
$useMpdf = file_exists($mpdfPath);

if ($useMpdf) {
    require_once $mpdfPath;
    
    try {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);
        
        // PDF metadata
        $mpdf->SetTitle('Sales Invoice - ' . ($salesInvoice['number'] ?? $salesInvoiceId));
        $mpdf->SetAuthor('Nuansa Invoice System');
        $mpdf->SetSubject('Sales Invoice');
        
    } catch (Exception $e) {
        $useMpdf = false;
    }
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
        @page {
            margin: 1cm;
            @top-center {
                content: "Sales Invoice - <?php echo htmlspecialchars($salesInvoice['number'] ?? $salesInvoiceId); ?>";
                font-size: 10px;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1f2937;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .invoice-subtitle {
            font-size: 12px;
            color: #6b7280;
        }
        
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .invoice-left, .invoice-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
        }
        
        .invoice-left {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        
        .invoice-right {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            margin-left: 10px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-row {
            margin-bottom: 6px;
            display: flex;
        }
        
        .label {
            font-weight: 600;
            display: inline-block;
            width: 100px;
            color: #6b7280;
            font-size: 10px;
        }
        
        .value {
            flex: 1;
            color: #1f2937;
            font-size: 11px;
        }
        
        .address-section {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .address-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #475569;
        }
        
        .address-content {
            background-color: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #2563eb;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .table tbody tr:hover {
            background-color: #e0f2fe;
        }
        
        .table .text-right {
            text-align: right;
        }
        
        .table .text-center {
            text-align: center;
        }
        
        .serial-row {
            background-color: #ecfdf5 !important;
            font-size: 9px;
            color: #059669;
        }
        
        .serial-row td {
            padding: 6px 8px;
            border-bottom: 1px solid #d1fae5;
        }
        
        .summary-section {
            float: right;
            width: 350px;
            margin-top: 20px;
        }
        
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #374151;
            text-align: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 6px 0;
            font-size: 11px;
        }
        
        .summary-row.total {
            border-top: 2px solid #2563eb;
            margin-top: 15px;
            padding-top: 12px;
            font-weight: bold;
            font-size: 13px;
            color: #1f2937;
        }
        
        .tax-info {
            clear: both;
            margin-top: 40px;
            background-color: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
        }
        
        .tax-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #92400e;
        }
        
        .tax-grid {
            display: table;
            width: 100%;
        }
        
        .tax-left, .tax-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .footer {
            clear: both;
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
        }
        
        .footer-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-logo">N</div>
        <div class="company-name">NUANSA INVOICE SYSTEM</div>
        <div class="invoice-title">SALES INVOICE</div>
        <div class="invoice-subtitle">Professional Invoice Document</div>
    </div>

    <!-- Invoice Information -->
    <div class="invoice-info">
        <div class="invoice-left">
            <div class="info-section">
                <div class="info-title">Invoice Information</div>
                <div class="info-row">
                    <span class="label">Invoice No:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['number'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['shipDate'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">PO Number:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['poNumber'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Invoice ID:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['id'] ?? $salesInvoiceId); ?></span>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Customer Information</div>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['customer']['name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Customer No:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['customer']['customerNo'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="invoice-right">
            <div class="info-section">
                <div class="info-title">Status & Details</div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['statusName'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Branch:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['branchName'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Sales Person:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['masterSalesmanName'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Term:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['paymentTerm']['name'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ship To Address -->
    <div class="address-section">
        <div class="address-title">Ship To Address</div>
        <div class="address-content">
            <?php echo htmlspecialchars($salesInvoice['toAddress'] ?? 'N/A'); ?>
        </div>
    </div>

    <!-- Items Table -->
    <?php if (isset($salesInvoice['detailItem']) && is_array($salesInvoice['detailItem'])): ?>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 200px;">Product Name</th>
                <th style="width: 80px;">Code</th>
                <th style="width: 60px;" class="text-center">Qty</th>
                <th style="width: 80px;" class="text-right">Unit Price</th>
                <th style="width: 80px;" class="text-right">Discount</th>
                <th style="width: 100px;" class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($salesInvoice['detailItem'] as $item): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><strong><?php echo htmlspecialchars($item['detailName'] ?? 'N/A'); ?></strong></td>
                <td><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></td>
                <td class="text-right"><?php echo formatAdvancedPDFCurrency($item['unitPrice'] ?? 0); ?></td>
                <td class="text-right"><?php echo formatAdvancedPDFCurrency($item['itemCashDiscount'] ?? 0); ?></td>
                <td class="text-right"><strong><?php echo formatAdvancedPDFCurrency($item['totalPrice'] ?? 0); ?></strong></td>
            </tr>
            
            <!-- Serial Numbers -->
            <?php if (isset($item['detailSerialNumber']) && is_array($item['detailSerialNumber']) && !empty($item['detailSerialNumber'])): ?>
            <tr class="serial-row">
                <td></td>
                <td colspan="6">
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
                    echo !empty($serials) ? htmlspecialchars(implode(', ', $serials)) : 'No valid serial numbers';
                    ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Summary -->
    <div class="summary-section">
        <div class="summary-box">
            <div class="summary-title">Amount Summary</div>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?php echo formatAdvancedPDFCurrency($salesInvoice['subTotal'] ?? 0); ?></span>
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
                    echo formatAdvancedPDFCurrency($taxTotal);
                ?></span>
            </div>
            <div class="summary-row">
                <span>Discount:</span>
                <span><?php echo formatAdvancedPDFCurrency($salesInvoice['cashDiscount'] ?? 0); ?></span>
            </div>
            <div class="summary-row total">
                <span>TOTAL AMOUNT:</span>
                <span><?php echo formatAdvancedPDFCurrency($salesInvoice['totalAmount'] ?? 0); ?></span>
            </div>
        </div>
    </div>

    <!-- Tax Information -->
    <div class="tax-info">
        <div class="tax-title">Tax Information</div>
        <div class="tax-grid">
            <div class="tax-left">
                <div class="info-row">
                    <span class="label">Tax Number:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['taxNumber'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Tax Date:</span>
                    <span class="value"><?php echo htmlspecialchars($salesInvoice['taxDate'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="tax-right">
                <div class="info-row">
                    <span class="label">Taxable:</span>
                    <span class="value"><?php echo ($salesInvoice['taxable'] ?? false) ? 'Yes' : 'No'; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Inclusive Tax:</span>
                    <span class="value"><?php echo ($salesInvoice['inclusiveTax'] ?? false) ? 'Yes' : 'No'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-title">Nuansa Invoice System</div>
        <p>Generated on <?php echo date('d M Y H:i:s'); ?> | This is a computer-generated document.</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

if ($useMpdf) {
    // Use mPDF to generate PDF
    try {
        $mpdf->WriteHTML($html);
        $filename = 'invoice_' . $salesInvoiceId . '_' . date('Ymd_His') . '.pdf';
        $mpdf->Output($filename, 'D'); // 'D' for download, 'I' for inline view
        exit;
    } catch (Exception $e) {
        // Fallback to HTML if mPDF fails
        $useMpdf = false;
    }
}

// Fallback: Output as HTML (can be printed to PDF via browser)
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="invoice_' . $salesInvoiceId . '.html"');

echo $html;
?>
