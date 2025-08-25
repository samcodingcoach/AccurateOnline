<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$salesReceiptId = $_GET['id'] ?? null;

if (!$salesReceiptId) {
    header('Location: index.php');
    exit;
}

// Gunakan sales receipt detail API
$result = $api->getSalesReceiptDetail($salesReceiptId);
$salesReceipt = null;

if ($result['success'] && isset($result['data']['d'])) {
    $salesReceipt = $result['data']['d'];
}

if (!$salesReceipt) {
    echo "Sales Receipt not found";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Sales Receipt - <?php echo htmlspecialchars($salesReceipt['number'] ?? 'N/A'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Print Controls (Hidden when printing) -->
    <div class="no-print bg-gray-100 p-4 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">Print Sales Receipt</h1>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>

    <!-- Print Content -->
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">SALES RECEIPT</h1>
            <div class="text-lg text-gray-700">
                No. <?php echo htmlspecialchars($salesReceipt['number'] ?? 'N/A'); ?>
            </div>
        </div>

        <!-- Company Info and Receipt Info -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Company Info -->
            <div>
                <h3 class="font-bold text-gray-900 mb-3">Company Information</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <div><strong>PT NUANSA</strong></div>
                    <div>Jl. Contoh No. 123</div>
                    <div>Jakarta, Indonesia</div>
                    <div>Telp: (021) 123-4567</div>
                </div>
            </div>

            <!-- Receipt Info -->
            <div>
                <h3 class="font-bold text-gray-900 mb-3">Receipt Information</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <div><strong>Receipt No:</strong> <?php echo htmlspecialchars($salesReceipt['number'] ?? 'N/A'); ?></div>
                    <div><strong>Date:</strong> <?php echo htmlspecialchars($salesReceipt['transDate'] ?? 'N/A'); ?></div>
                    <div><strong>Payment Method:</strong> <?php echo htmlspecialchars($salesReceipt['paymentMethod'] ?? 'N/A'); ?></div>
                    <div><strong>Bank:</strong> <?php echo htmlspecialchars($salesReceipt['bank']['name'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="mb-8">
            <h3 class="font-bold text-gray-900 mb-3">Receive From</h3>
            <div class="bg-gray-50 p-4 rounded">
                <div class="text-sm text-gray-700">
                    <?php 
                    $customerInfo = '';
                    if (isset($salesReceipt['customer']['customerNo'])) {
                        $customerInfo .= htmlspecialchars($salesReceipt['customer']['customerNo']);
                    }
                    if (isset($salesReceipt['customer']['name'])) {
                        if ($customerInfo) $customerInfo .= ' - ';
                        $customerInfo .= htmlspecialchars($salesReceipt['customer']['name']);
                    }
                    echo $customerInfo ?: 'N/A';
                    ?>
                </div>
            </div>
        </div>

        <!-- Invoice Details Table -->
        <?php if (isset($salesReceipt['detailInvoice']) && is_array($salesReceipt['detailInvoice'])): ?>
        <div class="mb-8">
            <h3 class="font-bold text-gray-900 mb-3">Invoice Details</h3>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-left text-xs font-medium text-gray-700">Invoice No</th>
                        <th class="border border-gray-300 px-3 py-2 text-left text-xs font-medium text-gray-700">Invoice Date</th>
                        <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-700">Total Invoice</th>
                        <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-700">Owing</th>
                        <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-700">Pay</th>
                        <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-700">Discount</th>
                        <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-700">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesReceipt['detailInvoice'] as $detail): ?>
                    <tr>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900">
                            <?php echo htmlspecialchars($detail['invoice']['number'] ?? 'N/A'); ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900">
                            <?php echo htmlspecialchars($detail['invoice']['transDate'] ?? 'N/A'); ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900 text-right">
                            <?php 
                            $totalInvoice = $detail['paymentAmount'] ?? 0;
                            echo 'Rp ' . number_format($totalInvoice, 0, ',', '.');
                            ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900 text-right">
                            <?php 
                            $owing = $detail['invoice']['owing']['orPayment'] ?? 0;
                            echo 'Rp ' . number_format($owing, 0, ',', '.');
                            ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900 text-right">
                            <?php 
                            $payAmount = $detail['paymentAmount'] ?? 0;
                            echo 'Rp ' . number_format($payAmount, 0, ',', '.');
                            ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm text-gray-900 text-right">
                            <?php 
                            $discount = 0;
                            if (isset($detail['detailDiscount']) && is_array($detail['detailDiscount'])) {
                                foreach ($detail['detailDiscount'] as $discDetail) {
                                    $discount += $discDetail['amount'] ?? 0;
                                }
                            }
                            echo 'Rp ' . number_format($discount, 0, ',', '.');
                            ?>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-sm font-medium text-gray-900 text-right">
                            <?php 
                            $payment = $detail['invoicePayment'] ?? 0;
                            echo 'Rp ' . number_format($payment, 0, ',', '.');
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Summary -->
        <div class="grid grid-cols-2 gap-8">
            <div></div> <!-- Empty space -->
            <div>
                <div class="border border-gray-300">
                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-300">
                        <h4 class="font-bold text-gray-900">Payment Summary</h4>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Payment Amount:</span>
                            <span class="text-gray-900 font-medium">
                                <?php 
                                $paymentAmount = $salesReceipt['equivalentAmount'] ?? 0;
                                echo 'Rp ' . number_format($paymentAmount, 0, ',', '.');
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Invoice Paid:</span>
                            <span class="text-gray-900">
                                <?php 
                                $totalPayment = $salesReceipt['totalPayment'] ?? 0;
                                echo 'Rp ' . number_format($totalPayment, 0, ',', '.');
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Discount:</span>
                            <span class="text-gray-900">
                                <?php 
                                $totalDiscount = $salesReceipt['totalDiscount'] ?? 0;
                                echo 'Rp ' . number_format($totalDiscount, 0, ',', '.');
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between border-t pt-2 font-bold">
                            <span class="text-gray-900">Over Pay:</span>
                            <span class="text-gray-900">
                                <?php 
                                $overPay = $salesReceipt['overPay'] ?? 0;
                                echo 'Rp ' . number_format($overPay, 0, ',', '.');
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-xs text-gray-500">
            <p>Printed on <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>Sales Receipt ID: <?php echo htmlspecialchars($salesReceiptId); ?></p>
        </div>
    </div>

    <script>
        // Auto-print when opened with print parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>
