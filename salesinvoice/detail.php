<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$salesInvoiceId = $_GET['id'] ?? null;

if (!$salesInvoiceId) {
    header('Location: index.php');
    exit;
}

// Gunakan sales invoice detail API
$result = $api->getSalesInvoiceDetail($salesInvoiceId);
$salesInvoice = null;
$rawResponse = $result; // Simpan raw response dari detail

if ($result['success'] && isset($result['data']['d'])) {
    $salesInvoice = $result['data']['d'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sales Invoice - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Sales Invoice</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="print_pdf.php?id=<?php echo urlencode($salesInvoiceId); ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg" 
                       target="_blank">
                        <i class="fas fa-print mr-2"></i>Print PDF
                    </a>
                    <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($salesInvoice): ?>
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-file-invoice text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Sales Invoice Details</h2>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order By</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-user text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['customer']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-400 mr-3">#</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['customer']['customerNo'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-calendar text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['shipDate'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-400 mr-3">ID</span>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['id'] ?? $salesInvoiceId); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2 - Background Abu-abu -->
                            <div class="bg-gray-100 p-4 rounded-lg space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-handshake text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['paymentTerm']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['statusName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Printed</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-print text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['printUserName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 3 - Sales Name -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sales Name</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-user-tie text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['masterSalesmanName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Section 1 - Background Putih -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-alt text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['number'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Po No</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-clipboard text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['poNumber'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Age</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-clock text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['age'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">PPN 12%</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-percent text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php 
                                            $ppnAmount = 'N/A';
                                            if (isset($salesInvoice['detailTax']) && is_array($salesInvoice['detailTax'])) {
                                                foreach ($salesInvoice['detailTax'] as $tax) {
                                                    if (isset($tax['taxAmount'])) {
                                                        $taxAmount = $tax['taxAmount'];
                                                        $ppnAmount = $taxAmount == floor($taxAmount) ? number_format($taxAmount, 0) : number_format($taxAmount, 2);
                                                        break;
                                                    }
                                                }
                                            }
                                            echo htmlspecialchars($ppnAmount);
                                        ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2 - Background Abu-abu -->
                            <div class="bg-gray-100 p-4 rounded-lg space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-building text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['branchName'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ship Date</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-shipping-fast text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['shipDateView'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipment</label>
                                    <div class="flex items-center p-3 bg-white rounded-lg">
                                        <i class="fas fa-truck text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['shipment']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 3 - FOB -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fob</label>
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <i class="fas fa-shipping-fast text-gray-400 mr-3"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['fob']['name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detail Items Table -->
                <?php if (isset($salesInvoice['detailItem']) && is_array($salesInvoice['detailItem'])): ?>
                <div class="border-t border-gray-200">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-list mr-2"></i>Detail Items
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Product Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Code</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Price</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Discount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($salesInvoice['detailItem'] as $item): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <?php echo htmlspecialchars($item['id'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <?php echo htmlspecialchars($item['detailName'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $unitPrice = $item['unitPrice'] ?? 0;
                                            echo $unitPrice == floor($unitPrice) ? number_format($unitPrice, 0) : number_format($unitPrice, 2);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right border-b">
                                            <?php 
                                            $discount = $item['itemCashDiscount'] ?? 0;
                                            echo $discount == floor($discount) ? number_format($discount, 0) : number_format($discount, 2);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right border-b">
                                            <?php 
                                            $totalPrice = $item['totalPrice'] ?? 0;
                                            echo $totalPrice == floor($totalPrice) ? number_format($totalPrice, 0) : number_format($totalPrice, 2);
                                            ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- Serial Numbers (if available) -->
                                    <?php if (isset($item['detailSerialNumber']) && is_array($item['detailSerialNumber']) && !empty($item['detailSerialNumber'])): ?>
                                    <tr class="bg-blue-50">
                                        <td colspan="7" class="px-6 py-2 text-sm text-gray-600 border-b">
                                            <strong>Serial Numbers:</strong>
                                            <?php 
                                            $serials = [];
                                            foreach ($item['detailSerialNumber'] as $serialData) {
                                                if (isset($serialData['serialNumber'])) {
                                                    if (is_array($serialData['serialNumber'])) {
                                                        // Handle nested array structure
                                                        foreach ($serialData['serialNumber'] as $serial) {
                                                            if (isset($serial['Number'])) {
                                                                $serialNum = $serial['Number'];
                                                                // Filter out dates - hanya ambil yang bukan format tanggal
                                                                if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialNum)) {
                                                                    $serials[] = $serialNum;
                                                                }
                                                            } elseif (is_string($serial)) {
                                                                // Filter out dates
                                                                if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serial)) {
                                                                    $serials[] = $serial;
                                                                }
                                                            }
                                                        }
                                                    } elseif (is_string($serialData['serialNumber'])) {
                                                        // Filter out dates
                                                        if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialData['serialNumber'])) {
                                                            $serials[] = $serialData['serialNumber'];
                                                        }
                                                    }
                                                } elseif (isset($serialData['Number'])) {
                                                    $serialNum = $serialData['Number'];
                                                    // Filter out dates
                                                    if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialNum)) {
                                                        $serials[] = $serialNum;
                                                    }
                                                } elseif (is_string($serialData)) {
                                                    // Filter out dates
                                                    if (!preg_match('/\d{2}\/\d{2}\/\d{4}|\d{2} \w{3} \d{4}/', $serialData)) {
                                                        $serials[] = $serialData;
                                                    }
                                                }
                                            }
                                            // Remove duplicates and show only unique serial numbers
                                            $serials = array_unique($serials);
                                            echo !empty($serials) ? htmlspecialchars(implode(', ', $serials)) : 'No serial numbers';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Address Section -->
                <div class="border-t border-gray-200 px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-3 mt-1"></i>
                                <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['toAddress'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Section -->
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tax Information -->
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-900">Tax Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">No Pajak:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['taxNumber'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">NIK:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['retailIdCard'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax Date:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($salesInvoice['taxDate'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax:</span>
                                    <span class="text-gray-900"><?php echo ($salesInvoice['taxable'] ?? false) ? 'Yes' : 'No'; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Inclusive Tax:</span>
                                    <span class="text-gray-900"><?php echo ($salesInvoice['inclusiveTax'] ?? false) ? 'Yes' : 'No'; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Amount Summary -->
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-900">Amount Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="text-gray-900"><?php 
                                        $subTotal = $salesInvoice['subTotal'] ?? 0;
                                        echo $subTotal == floor($subTotal) ? number_format($subTotal, 0) : number_format($subTotal, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax Total:</span>
                                    <span class="text-gray-900"><?php 
                                        $taxTotal = 0;
                                        if (isset($salesInvoice['detailTax']) && is_array($salesInvoice['detailTax'])) {
                                            foreach ($salesInvoice['detailTax'] as $tax) {
                                                if (isset($tax['taxAmount'])) {
                                                    $taxTotal += $tax['taxAmount'];
                                                }
                                            }
                                        }
                                        echo $taxTotal == floor($taxTotal) ? number_format($taxTotal, 0) : number_format($taxTotal, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="text-gray-900"><?php 
                                        $cashDiscount = $salesInvoice['cashDiscount'] ?? 0;
                                        echo $cashDiscount == floor($cashDiscount) ? number_format($cashDiscount, 0) : number_format($cashDiscount, 2);
                                    ?></span>
                                </div>
                                <div class="flex justify-between border-t pt-2 font-semibold">
                                    <span class="text-gray-900">Total Amount:</span>
                                    <span class="text-gray-900"><?php 
                                        $totalAmount = $salesInvoice['totalAmount'] ?? 0;
                                        echo $totalAmount == floor($totalAmount) ? number_format($totalAmount, 0) : number_format($totalAmount, 2);
                                    ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Info -->
                <div class="mx-6 mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                    <div class="text-sm">
                        <p><strong>Data Source:</strong> Sales Invoice Detail API (/detail.do)</p>
                        <p><strong>Detail API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Invoice Found:</strong> <?php echo $salesInvoice ? 'Yes' : 'No'; ?></p>
                        <p><strong>Sales Invoice ID:</strong> <?php echo htmlspecialchars($salesInvoiceId); ?></p>
                        <?php if (isset($result['error'])): ?>
                            <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                        <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                            if ($rawResponse) {
                                echo htmlspecialchars(json_encode($rawResponse, JSON_PRETTY_PRINT));
                            } else {
                                echo "No raw response available";
                            }
                        ?></pre>
                    </details>
                </div>
            </div>
        <?php else: ?>
            <!-- Error State -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Data Tidak Ditemukan</h3>
                    <p class="text-gray-600 mb-4">Sales Invoice dengan ID "<?php echo htmlspecialchars($salesInvoiceId); ?>" tidak ditemukan atau terjadi error.</p>
                    
                    <?php if (isset($result['error'])): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-red-800"><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                        <?php if (isset($result['http_code'])): ?>
                        <p class="text-red-600 text-sm mt-1">HTTP Code: <?php echo htmlspecialchars($result['http_code']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex gap-4 justify-center">
                        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke List
                        </a>
                        <a href="debug_detail.php?id=<?php echo urlencode($salesInvoiceId); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-bug mr-2"></i>Debug Data
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
