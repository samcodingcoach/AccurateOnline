<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$result = $api->getSalesOrderList();
$salesOrders = [];

if ($result['success'] && isset($result['data']['d'])) {
    $salesOrders = $result['data']['d'];
    
    // Sort sales orders by ID descending (newest first)
    usort($salesOrders, function($a, $b) {
        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
    });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sales Order - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Sales Order</h1>
                </div>
                <div class="flex gap-4">
                    <a href="new_so.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Tambah Sales Order
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
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Data Sales Order</h2>
            
            <?php if (!empty($salesOrders)): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan <?php echo count($salesOrders); ?> sales order.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trans Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Term</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($salesOrders as $salesOrder): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($salesOrder['id'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($salesOrder['number'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $transDate = $salesOrder['transDate'] ?? null;
                                        if ($transDate && !empty($transDate)) {
                                            echo htmlspecialchars($transDate);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $customer = $salesOrder['customer'] ?? null;
                                        if ($customer) {
                                            // Try to get customer name from different possible locations
                                            $customerName = null;
                                            $customerId = null;
                                            
                                            // Method 1: Check contactInfo first
                                            if (isset($customer['contactInfo']['name'])) {
                                                $customerName = $customer['contactInfo']['name'];
                                                $customerId = $customer['customerId'] ?? $customer['contactInfo']['customerId'] ?? '';
                                            }
                                            // Method 2: Check direct name property
                                            elseif (isset($customer['name'])) {
                                                $customerName = $customer['name'];
                                                $customerId = $customer['customerId'] ?? $customer['id'] ?? '';
                                            }
                                            // Method 3: Check if customer is just a string
                                            elseif (is_string($customer)) {
                                                $customerName = $customer;
                                            }
                                            
                                            if ($customerName) {
                                                if ($customerId) {
                                                    echo htmlspecialchars($customerName . ' (ID:' . $customerId . ')');
                                                } else {
                                                    echo htmlspecialchars($customerName);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $paymentTerm = $salesOrder['paymentTerm'] ?? null;
                                        if ($paymentTerm && isset($paymentTerm['name'])) {
                                            echo htmlspecialchars($paymentTerm['name']);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $totalAmount = $salesOrder['totalAmount'] ?? 0;
                                        echo 'Rp ' . number_format($totalAmount, 0, ',', '.');
                                        ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $statusName = $salesOrder['statusName'] ?? 'N/A';
                                        $statusClass = '';
                                        switch(strtolower($statusName)) {
                                            case 'open':
                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'closed':
                                                $statusClass = 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($statusName); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="detail.php?id=<?php echo $salesOrder['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </a>
                                            <?php 
                                            // Prepare customer data for invoice link
                                            $customerId = '';
                                            $customerNo = '';
                                            
                                            if (isset($salesOrder['customer'])) {
                                                $customer = $salesOrder['customer'];
                                                // Get customer ID
                                                if (isset($customer['id'])) {
                                                    $customerId = $customer['id'];
                                                } elseif (isset($customer['customerId'])) {
                                                    $customerId = $customer['customerId'];
                                                }
                                                
                                                // Get customer number
                                                if (isset($customer['customerNo'])) {
                                                    $customerNo = $customer['customerNo'];
                                                } elseif (isset($customer['contactInfo']['customerNo'])) {
                                                    $customerNo = $customer['contactInfo']['customerNo'];
                                                }
                                            }
                                            
                                            $invoiceUrl = "../salesinvoice/new_invoice.php?" . http_build_query([
                                                'so_id' => $salesOrder['id'] ?? '',
                                                'so_number' => $salesOrder['number'] ?? '',
                                                'trans_date' => $salesOrder['transDate'] ?? '',
                                                'customer_id' => $customerId,
                                                'customer_no' => $customerNo
                                            ]);
                                            ?>
                                            <a href="<?php echo htmlspecialchars($invoiceUrl); ?>" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-file-invoice mr-1"></i>Invoice
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr class="font-semibold">
                                <td colspan="6" class="px-4 py-4 text-right text-sm text-gray-900">
                                    <strong>Grand Total:</strong>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <strong>
                                        <?php 
                                        $grandTotal = 0;
                                        foreach ($salesOrders as $order) {
                                            $grandTotal += $order['totalAmount'] ?? 0;
                                        }
                                        echo 'Rp ' . number_format($grandTotal, 0, ',', '.');
                                        ?>
                                    </strong>
                                </td>
                                <td class="px-4 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Tidak ada data sales order.</p>
            <?php endif; ?>
            
            <!-- Debug Info -->
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-medium mb-2">Debug Info</h3>
                <div class="text-sm">
                    <p><strong>Data Source:</strong> Sales Order List API (/list.do)</p>
                    <p><strong>List API Success:</strong> <?php echo isset($result) && $result['success'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Sales Orders Found:</strong> <?php echo count($salesOrders); ?></p>
                    <?php if (isset($result['error'])): ?>
                        <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                </div>
                
                <details class="mt-4">
                    <summary class="cursor-pointer text-blue-600">Raw Response</summary>
                    <pre class="mt-2 bg-white p-3 rounded border text-xs overflow-auto"><?php 
                        if ($result) {
                            echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
                        } else {
                            echo "No raw response available";
                        }
                    ?></pre>
                </details>
            </div>
        </div>
    </main>
</body>
</html>
