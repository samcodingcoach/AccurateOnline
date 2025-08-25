<?php
/**
 * Form Invoice Baru dari Sales Order
 * File: /salesinvoice/new_invoice.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Ambil parameter dari GET
$so_id = $_GET['so_id'] ?? '';
$so_number = $_GET['so_number'] ?? '';
$trans_date = $_GET['trans_date'] ?? '';
$customer_id = $_GET['customer_id'] ?? '';
$customer_no = $_GET['customer_no'] ?? '';

// Ambil detail sales order jika diminta
$salesOrderDetail = null;
$showDetails = isset($_GET['load_details']) && $_GET['load_details'] == '1';

if ($showDetails && $so_id) {
    $api = new AccurateAPI();
    $result = $api->getSalesOrderDetail($so_id);
    
    if ($result['success'] && isset($result['data']['d'])) {
        $salesOrderDetail = $result['data']['d'];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Invoice dari Sales Order - Nuansa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .back-button {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.15s ease;
        }

        .back-button:hover {
            background: #4b5563;
        }

        .form-content {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="number"],
        select,
        textarea {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            background: white;
            transition: border-color 0.15s ease;
        }

        select {
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        input[readonly] {
            background-color: white;
            color: #374151;
            cursor: not-allowed;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            margin: 24px 0;
        }

        .btn-primary {
            background: #111827;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.75rem;
        }

        /* Table Styles */
        .table-container {
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .qty-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* Summary */
        .summary {
            text-align: right;
            margin-top: 20px;
            padding: 16px 0;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }

        .summary-label {
            width: 120px;
            text-align: right;
            margin-right: 20px;
            font-weight: 500;
            color: #374151;
        }

        .summary-value {
            width: 120px;
            text-align: right;
            font-weight: 600;
            color: #111827;
        }

        .grand-total .summary-label,
        .grand-total .summary-value {
            font-size: 1rem;
            font-weight: 700;
            color: #059669;
        }

        /* Section Headers */
        .section-header {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin: 24px 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .section-sub-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }

        /* Modal Styles - Consistent with new_invoice.php pattern */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
        }
        
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border: none;
            width: 90%;
            max-width: 900px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 8px 8px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .close {
            color: #6b7280;
            background: none;
            border: none;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        
        .close:hover,
        .close:focus {
            color: #374151;
            background: #e5e7eb;
        }
        
        /* Modal Form Styles - consistent with main form */
        .modal-form-group {
            margin-bottom: 20px;
        }
        
        .modal-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .modal-form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: border-color 0.15s ease;
            background: white;
        }
        
        .modal-form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .modal-form-group input:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            border-color: #d1d5db;
            cursor: not-allowed;
        }
        
        .btn-primary:disabled {
            background: #6b7280;
            cursor: not-allowed;
        }
        
        .btn-primary:disabled:hover {
            background: #6b7280;
        }
        
        .modal-input-group {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        .modal-input-group input {
            flex: 1;
        }
        
        /* Modal Item Info - consistent with info-section */
        .modal-item-info {
            background: #f9fafb;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        
        .modal-item-info p {
            margin: 8px 0;
            font-size: 0.875rem;
            display: flex;
            justify-content: space-between;
        }
        
        .modal-item-info strong {
            color: #374151;
            font-weight: 600;
        }
        
        .modal-item-value {
            color: #111827;
            font-weight: 500;
        }
        
        /* Modal Table - consistent with main table */
        .modal-table-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .modal-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modal-table th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }
        
        .modal-table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .modal-loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
            font-size: 0.875rem;
        }
        
        .serial-badge {
            background: #2563eb;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Invoice Form Styles */
        .invoice-form-container {
            background: #f9fafb;
            padding: 24px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            margin-top: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 6px;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
            background: white;
            transition: border-color 0.15s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-group input[readonly] {
            background-color: white;
            color: #374151;
            cursor: not-allowed;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        /* Tabs */
        .tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .tab-list {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
            color: #6b7280;
        }

        .tab-list li {
            margin-right: 8px;
        }

        .me-2 {
            margin-right: 8px;
        }

        .tab-button {
            display: inline-block;
            padding: 16px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            border-radius: 8px 8px 0 0;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .tab-button:hover {
            color: #4b5563;
            background-color: #f9fafb;
        }

        .tab-button.active {
            color: #2563eb;
            background-color: #f3f4f6;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Checkbox Items */
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        /* Textarea */
        textarea {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            background: white;
            transition: border-color 0.15s ease;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
        }

        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            
            .modal-header, .modal-body, .modal-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>Buat Invoice dari Sales Order</h1>
            <a href="../salesorder/index.php" class="back-button">
                ← Kembali ke Sales Order
            </a>
        </div>

        <div class="form-content">
            <?php if (!$showDetails): ?>
                <!-- Sales Order Information -->
                <div class="form-grid">
                    <div class="form-group">
                        <label>Konsumen</label>
                        <input type="text" value="<?php echo htmlspecialchars($customer_no); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>SO Number</label>
                        <input type="text" value="<?php echo htmlspecialchars($so_number); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>SO Tanggal</label>
                        <input type="text" value="<?php echo htmlspecialchars($trans_date); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <input type="text" id="statusPembayaran" value="" readonly>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="action-buttons">
                    <a href="?so_id=<?php echo urlencode($so_id); ?>&so_number=<?php echo urlencode($so_number); ?>&trans_date=<?php echo urlencode($trans_date); ?>&customer_id=<?php echo urlencode($customer_id); ?>&customer_no=<?php echo urlencode($customer_no); ?>&load_details=1" 
                       class="btn-primary">
                        AMBIL DATA
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($showDetails && $salesOrderDetail): ?>
                <!-- Tabs -->
                <div class="tabs">
                    <ul class="tab-list">
                        <li class="me-2">
                            <button type="button" class="tab-button active" onclick="switchTab('salesOrder')">Sales Order</button>
                        </li>
                        <li class="me-2">
                            <button type="button" class="tab-button" onclick="switchTab('otherInfo')">Info Lainnya</button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content: Sales Order -->
                <div id="salesOrderTab" class="tab-content active">
                    <!-- Sales Order Information -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Konsumen</label>
                            <input type="text" value="<?php echo htmlspecialchars($customer_no); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>SO Number</label>
                            <input type="text" value="<?php echo htmlspecialchars($so_number); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>SO Tanggal</label>
                            <input type="text" value="<?php echo htmlspecialchars($trans_date); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Status Pembayaran</label>
                            <input type="text" id="statusPembayaran2" value="" readonly>
                        </div>
                    </div>

                </div> <!-- End Sales Order Tab -->

                <!-- Tab Content: Other Info -->
                <div id="otherInfoTab" class="tab-content">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                        
                        <!-- Tax/Pajak Section -->
                        <div class="form-group">
                            <div class="section-sub-header" style="margin-bottom: 16px;">
                                <label style="font-weight: 600; font-size: 1rem; color: #111827;">Tax / Pajak</label>
                            </div>
                            
                            <div style="margin-bottom: 26px; display: flex; gap: 30px; align-items: flex-start;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="taxableCheckbox" name="taxable" value="true" style="margin: 0;">
                                    <label for="taxableCheckbox" style="margin: 0; font-size: 0.875rem; line-height: 1;">Kena Pajak</label>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="inclusiveTaxCheckbox" name="inclusiveTax" value="true" style="margin: 0;">
                                    <label for="inclusiveTaxCheckbox" style="margin: 0; font-size: 0.875rem; line-height: 1;">Inclusive Pajak</label>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="reverseInvoice" name="reverseInvoice" value="true" style="margin: 0;">
                                    <label for="reverseInvoice" style="margin: 0; font-size: 0.875rem; line-height: 1;">Faktur Dimuka</label>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="paymentTerm" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px; font-weight: 500;">Syarat Pembayaran</label>
                                <input type="text" id="paymentTerm" name="paymentTerm" readonly style="width: 60%;">
                            </div>
                            
                            <div class="form-group" >
                                <label for="branch" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px; font-weight: 500;">Cabang</label>
                                <input type="text" id="branch" name="branch" readonly style="width: 60%;">
                            </div>
                        </div>
                        
                        <!-- Pengiriman Section -->
                        <div class="form-group">
                            <div class="section-sub-header" style="margin-bottom: 16px;">
                                <label style="font-weight: 600; font-size: 1rem; color: #111827;">Pengiriman</label>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="toAddress" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px; font-weight: 500;">Alamat Pengiriman</label>
                                <textarea id="toAddress" 
                                          name="toAddress" readonly
                                          rows="3"
                                          placeholder="Masukkan alamat pengiriman..."
                                          style="resize: vertical; min-height: 70px;"></textarea>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label for="deliveryType" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px; font-weight: 500;">Jenis Pengiriman</label>
                                    <input type="text" 
                                           id="deliveryType" 
                                           name="deliveryType"
                                           readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="deliveryDate" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px; font-weight: 500;">Tanggal Pengiriman</label>
                                    <input type="text" 
                                           id="deliveryDate" 
                                           name="deliveryDate"
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- Detail Items Section -->
                <div class="section-header">Detail Items</div>
                
                <?php if (isset($salesOrderDetail['detailItem']) && is_array($salesOrderDetail['detailItem'])): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">QTY</th>
                                    <th>Satuan</th>
                                    <th>Gudang</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Diskon</th>
                                    <th class="text-right">Total Harga</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesOrderDetail['detailItem'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['id'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($item['item']['no'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['detailName'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <span class="qty-badge"><?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['itemUnit']['name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item['warehouse']['name'] ?? 'N/A'); ?></td>
                                        <td class="text-right">Rp <?php echo number_format($item['unitPrice'] ?? 0, 0, ',', '.'); ?></td>
                                        <td class="text-right">Rp <?php echo number_format($item['itemCashDiscount'] ?? 0, 0, ',', '.'); ?></td>
                                        <td class="text-right"><strong>Rp <?php echo number_format($item['totalPrice'] ?? 0, 0, ',', '.'); ?></strong></td>
                                        <td class="text-center">
                                            <button onclick="openSerialModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['detailName']); ?>', <?php echo $item['quantity']; ?>, '<?php echo htmlspecialchars($item['item']['no']); ?>', '<?php echo htmlspecialchars($item['warehouse']['name'] ?? ''); ?>')" 
                                                    class="btn-primary btn-small">
                                                Input Serial
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #dc2626; padding: 16px; background: #fef2f2; border-radius: 6px; border: 1px solid #fecaca;">
                        Tidak ada detail items yang ditemukan untuk Sales Order ini.
                    </p>
                <?php endif; ?>

                <!-- Summary Section -->
                <div class="summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value">Rp <?php echo number_format($salesOrderDetail['subTotal'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Discount:</span>
                        <span class="summary-value">Rp <?php echo number_format($salesOrderDetail['cashDiscount'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">PPN 12%:</span>
                        <span class="summary-value">Rp <?php echo number_format($salesOrderDetail['tax1Amount'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row grand-total">
                        <span class="summary-label">Total Amount:</span>
                        <span class="summary-value">Rp <?php echo number_format($salesOrderDetail['totalAmount'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="action-buttons" style="justify-content:right; margin-top: 30px;">
                    <button type="button" class="btn-primary" onclick="submitInvoice()">
                        Submit Invoice
                    </button>
                </div>

            <?php elseif ($showDetails && !$salesOrderDetail): ?>
                <p style="color: #dc2626; padding: 16px; background: #fef2f2; border-radius: 6px; border: 1px solid #fecaca;">
                    Gagal mengambil detail Sales Order. Silakan coba lagi.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Serial Input -->
    <div id="serialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Input Serial Number</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="modal-item-info">
                    <p><strong>Nama Barang:</strong> <span class="modal-item-value" id="modalItemName"></span></p>
                    <p><strong>Warehouse:</strong> <span class="modal-item-value" id="modalWarehouse"></span></p>
                    <p><strong>Kuantitas:</strong> <span class="modal-item-value" id="modalQuantity"></span></p>
                    <p><strong>Serial Terinput:</strong> <span class="modal-item-value" id="serialCount">0</span> / <span id="maxQuantity">0</span></p>
                </div>
                
                <div class="modal-form-group">
                    <label for="serialInput">Input Serial Number</label>
                    <div class="modal-input-group">
                        <input type="text" id="serialInput" placeholder="Masukkan serial number">
                        <button class="btn-primary" onclick="addSerial()">Tambah</button>
                    </div>
                </div>
                
                <div class="modal-table-container">
                    <table class="modal-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Serial Number</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="serialTableBody">
                            <!-- Data akan dimuat disini -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn-primary" onclick="saveSerials()">Simpan Serial</button>
                
            </div>
        </div>
    </div>

    <script>
        let currentItemId = null;
        let currentItemName = '';
        let currentQuantity = 0;
        let currentItemCode = '';
        let currentWarehouseName = '';
        let serialCounter = 1;
        let serialData = [];
        
        // Storage untuk menyimpan data serial per item
        let itemSerialData = {};

        // Tab switching function
        function switchTab(tabName) {
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                if (button) button.classList.remove('active');
            });
            
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                if (content) content.classList.remove('active');
            });
            
            // Show selected tab content and activate button
            if (tabName === 'salesOrder') {
                const salesOrderTab = document.getElementById('salesOrderTab');
                const salesOrderButton = document.querySelector('[onclick="switchTab(\'salesOrder\')"]');
                
                if (salesOrderTab) salesOrderTab.classList.add('active');
                if (salesOrderButton) salesOrderButton.classList.add('active');
            } else if (tabName === 'otherInfo') {
                const otherInfoTab = document.getElementById('otherInfoTab');
                const otherInfoButton = document.querySelector('[onclick="switchTab(\'otherInfo\')"]');
                
                if (otherInfoTab) otherInfoTab.classList.add('active');
                if (otherInfoButton) otherInfoButton.classList.add('active');
            }
        }

        function openSerialModal(itemId, itemName, quantity, itemCode, warehouseName) {
            currentItemId = itemId;
            currentItemName = itemName;
            currentQuantity = quantity;
            currentItemCode = itemCode;
            currentWarehouseName = warehouseName || '';
            
            document.getElementById('modalItemName').textContent = itemName;
            document.getElementById('modalWarehouse').textContent = warehouseName || 'Tidak diketahui';
            document.getElementById('modalQuantity').textContent = quantity;
            document.getElementById('maxQuantity').textContent = quantity;
            document.getElementById('serialModal').style.display = 'block';
            
            // Load existing data for this item if available
            if (itemSerialData[itemId]) {
                serialData = [...itemSerialData[itemId]]; // Copy existing data
                // Set counter to continue from last number
                serialCounter = serialData.length > 0 ? Math.max(...serialData.map(item => item.no)) + 1 : 1;
            } else {
                // Reset data for new item
                serialCounter = 1;
                serialData = [];
                
                // Check for existing serials from API
                checkExistingSerials(itemId);
            }
            
            // Update counter display and table
            updateSerialCounter();
            updateSerialTable();
            updateInputState();
        }

        function closeModal() {
            // Save current serial data for this item before closing
            if (currentItemId && serialData.length > 0) {
                itemSerialData[currentItemId] = [...serialData]; // Save copy of current data
            }
            
            document.getElementById('serialModal').style.display = 'none';
            document.getElementById('serialInput').value = '';
        }

        function checkExistingSerials(itemId) {
            // If we already have data for this item, don't reload from API
            if (itemSerialData[itemId]) {
                return;
            }
            
            document.getElementById('serialTableBody').innerHTML = '<tr><td colspan="4" class="modal-loading">Mengecek data serial...</td></tr>';
            
            // Panggil API detail_so.php dengan so_id (sesuai contoh JSON yang diberikan)
            const soId = '<?php echo $so_id; ?>';
            fetch(`../salesorder/detail_so.php?id=${soId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Sales Order Detail API Response:', data);
                    
                    if (data.success && data.data && data.data.d) {
                        const salesOrderData = data.data.d;
                        console.log('Full Sales Order Data:', salesOrderData);
                        
                        // Reset counter dan data
                        serialCounter = 1;
                        serialData = [];
                        
                        // Cari data serial dari detailItem
                        if (salesOrderData.detailItem && Array.isArray(salesOrderData.detailItem)) {
                            console.log('Found detailItem array with', salesOrderData.detailItem.length, 'items');
                            
                            // Cari item yang sesuai dengan currentItemId
                            const matchingItem = salesOrderData.detailItem.find(item => item.id == currentItemId);
                            console.log('Looking for item ID:', currentItemId);
                            console.log('Matching item found:', matchingItem);
                            
                            if (matchingItem) {
                                console.log('Item detailSerialNumber:', matchingItem.detailSerialNumber);
                                
                                if (matchingItem.detailSerialNumber && Array.isArray(matchingItem.detailSerialNumber)) {
                                    console.log('Found detailSerialNumber array with', matchingItem.detailSerialNumber.length, 'entries');
                                    
                                    // Loop melalui detailSerialNumber sesuai struktur JSON yang benar
                                    matchingItem.detailSerialNumber.forEach((detailSerial, index) => {
                                        console.log(`Processing detailSerial ${index}:`, detailSerial);
                                        
                                        // Dalam struktur ini, serialNumber adalah object tunggal, bukan array
                                        if (detailSerial.serialNumber && detailSerial.serialNumber.number) {
                                            serialData.push({
                                                no: serialCounter++,
                                                serial: detailSerial.serialNumber.number,
                                                date: detailSerial.serialNumber.updateStockDate || new Date().toLocaleDateString('id-ID')
                                            });
                                            console.log('Added serial:', detailSerial.serialNumber.number);
                                        }
                                    });
                                } else {
                                    console.log('No detailSerialNumber found or not an array');
                                }
                            } else {
                                console.log('No matching item found for ID:', currentItemId);
                            }
                        } else {
                            console.log('No detailItem found or not an array');
                        }
                        
                        // Save loaded data to storage
                        if (serialData.length > 0) {
                            itemSerialData[currentItemId] = [...serialData];
                        }
                        
                        // Update tabel dengan data yang ditemukan
                        updateSerialTable();
                        updateSerialCounter();
                        updateInputState();
                        
                        if (serialData.length > 0) {
                            console.log(`Found ${serialData.length} existing serials for item ${itemId}`);
                        } else {
                            console.log('No serial data found for item ' + itemId);
                        }
                        
                    } else {
                        // Error dari API
                        console.log('API Error:', data.message || 'Unknown error');
                        console.log('Full error response:', data);
                        document.getElementById('serialTableBody').innerHTML = '<tr><td colspan="4" class="modal-loading">Error loading data: ' + (data.message || 'Unknown error') + '</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching sales order detail:', error);
                    document.getElementById('serialTableBody').innerHTML = '<tr><td colspan="4" class="modal-loading">Error loading data: ' + error.message + '</td></tr>';
                });
        }

        function addSerial() {
            const serialInput = document.getElementById('serialInput');
            const serialValue = serialInput.value.trim();
            
            if (serialValue === '') {
                alert('Masukkan serial number terlebih dahulu!');
                return;
            }
            
            // Cek duplikasi
            if (serialData.some(item => item.serial === serialValue)) {
                alert('Serial number sudah ada!');
                return;
            }
            
            // Cek apakah sudah mencapai batas quantity
            if (serialData.length >= currentQuantity) {
                alert('Sudah mencapai batas kuantitas (' + currentQuantity + ')! Tidak bisa menambah serial lagi.');
                return;
            }
            
            // Validasi serial number dengan API
            validateSerialNumber(serialValue);
        }
        
        function validateSerialNumber(serialValue) {
            const serialInput = document.getElementById('serialInput');
            const addButton = serialInput.parentElement.querySelector('button');
            
            // Show loading state
            addButton.disabled = true;
            addButton.textContent = 'Validating...';
            serialInput.disabled = true;
            
            // Get session ID
            let sessionId = localStorage.getItem('accurate_session_id') || 
                           localStorage.getItem('session_id') ||
                           sessionStorage.getItem('accurate_session_id') ||
                           sessionStorage.getItem('session_id');
            
            if (!sessionId) {
                console.error('No session ID found for validation');
                alert('Session ID tidak ditemukan. Tidak dapat memvalidasi serial number.');
                resetAddButton();
                return;
            }
            
            // Call validation API
            const apiUrl = `../mutasi/list_serial.php?itemNo=${encodeURIComponent(currentItemCode)}&sessionId=${encodeURIComponent(sessionId)}`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    console.log('Serial validation response:', data);
                    
                    if (data.success && data.data && data.data.s && data.data.d) {
                        // Check if serial number exists and matches warehouse
                        const serialExists = data.data.d.find(item => 
                            item.serialNumber && 
                            item.serialNumber.number === serialValue &&
                            item.warehouse &&
                            item.warehouse.name === currentWarehouseName &&
                            item.quantity > 0
                        );
                        
                        if (serialExists) {
                            // Serial valid, add to list
                            addValidatedSerial(serialValue);
                            console.log('Serial number validated successfully:', serialValue);
                        } else {
                            // Check if serial exists in different warehouse
                            const serialInOtherWarehouse = data.data.d.find(item => 
                                item.serialNumber && 
                                item.serialNumber.number === serialValue
                            );
                            
                            if (serialInOtherWarehouse) {
                                alert(`Serial number "${serialValue}" ditemukan di warehouse "${serialInOtherWarehouse.warehouse.name}", bukan di "${currentWarehouseName}".`);
                            } else {
                                alert(`Serial number "${serialValue}" tidak ditemukan untuk item "${currentItemCode}" atau sudah habis.`);
                            }
                        }
                    } else {
                        alert('Gagal memvalidasi serial number: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error validating serial number:', error);
                    alert('Error saat memvalidasi serial number: ' + error.message);
                })
                .finally(() => {
                    resetAddButton();
                });
        }
        
        function addValidatedSerial(serialValue) {
            // Tambah ke array setelah validasi berhasil
            const currentDate = new Date().toLocaleDateString('id-ID');
            serialData.push({
                no: serialCounter,
                serial: serialValue,
                date: currentDate
            });
            
            // Update stored data
            if (currentItemId) {
                itemSerialData[currentItemId] = [...serialData];
            }
            
            // Update tabel dan counter
            updateSerialTable();
            updateSerialCounter();
            updateInputState();
            
            // Reset input
            document.getElementById('serialInput').value = '';
            serialCounter++;
            
            // Focus kembali ke input jika masih bisa input
            if (serialData.length < currentQuantity) {
                document.getElementById('serialInput').focus();
            }
        }
        
        function resetAddButton() {
            const serialInput = document.getElementById('serialInput');
            const addButton = serialInput.parentElement.querySelector('button');
            
            // Reset button state
            serialInput.disabled = false;
            addButton.disabled = false;
            addButton.textContent = 'Tambah';
            addButton.style.background = '#2563eb';
        }

        function updateSerialTable() {
            const tbody = document.getElementById('serialTableBody');
            
            if (serialData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="modal-loading">Belum ada data serial</td></tr>';
                return;
            }
            
            let html = '';
            serialData.forEach((item, index) => {
                html += `
                    <tr>
                        <td style="text-align: center; font-weight: 500;">${item.no}</td>
                        <td><span class="serial-badge">${item.serial}</span></td>
                        <td style="text-align: center;">${item.date}</td>
                        <td style="text-align: center;">
                            <button class="btn-secondary btn-small" onclick="deleteSerial(${index})">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function updateSerialCounter() {
            document.getElementById('serialCount').textContent = serialData.length;
            
            // Update warna berdasarkan status
            const serialCountEl = document.getElementById('serialCount');
            const maxQuantityEl = document.getElementById('maxQuantity');
            
            if (serialData.length >= currentQuantity) {
                serialCountEl.style.color = '#059669';
                serialCountEl.style.fontWeight = 'bold';
                maxQuantityEl.style.color = '#059669';
            } else {
                serialCountEl.style.color = '#2563eb';
                serialCountEl.style.fontWeight = '500';
                maxQuantityEl.style.color = '#374151';
            }
        }

        function updateInputState() {
            const serialInput = document.getElementById('serialInput');
            const addButton = serialInput.parentElement.querySelector('button');
            
            if (serialData.length >= currentQuantity) {
                serialInput.disabled = true;
                serialInput.placeholder = 'Kuantitas sudah terpenuhi';
                addButton.disabled = true;
                addButton.textContent = 'Selesai';
                addButton.style.background = '#059669';
            } else {
                serialInput.disabled = false;
                serialInput.placeholder = 'Masukkan serial number';
                addButton.disabled = false;
                addButton.textContent = 'Tambah';
                addButton.style.background = '#2563eb';
            }
        }

        function deleteSerial(index) {
            if (confirm('Hapus serial number ini?')) {
                serialData.splice(index, 1);
                updateSerialTable();
                updateSerialCounter();
                updateInputState();
                
                // Update stored data
                if (currentItemId) {
                    itemSerialData[currentItemId] = [...serialData];
                }
            }
        }

        function saveSerials() {
            if (serialData.length === 0) {
                alert('Belum ada serial number yang diinput!');
                return;
            }
            
            // Save current serial data for this item
            if (currentItemId) {
                itemSerialData[currentItemId] = [...serialData];
            }
            
            // Close modal without showing demo message
            closeModal();
        }

        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('serialModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Enter key untuk tambah serial
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('serialInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    addSerial();
                }
            });
            
            // Initialize session ID if not available
            initializeSessionId();
            
            // Load data from API if so_id is available
            const soId = '<?php echo $so_id; ?>';
            if (soId) {
                loadSalesOrderData(soId);
            }
        });
        
        function initializeSessionId() {
            let sessionId = localStorage.getItem('accurate_session_id') || 
                           localStorage.getItem('session_id') ||
                           sessionStorage.getItem('accurate_session_id') ||
                           sessionStorage.getItem('session_id');
            
            if (!sessionId) {
                // Try to get session ID from server
                fetch('../get_session_id.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.sessionId) {
                            localStorage.setItem('accurate_session_id', data.sessionId);
                            console.log('Session ID initialized:', data.sessionId);
                        }
                    })
                    .catch(error => {
                        console.log('Could not initialize session ID:', error.message);
                    });
            } else {
                console.log('Session ID already available:', sessionId);
            }
        }
        
        function loadSalesOrderData(soId) {
            fetch(`../salesorder/detail_so.php?id=${soId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Loading SO Data:', data);
                    
                    if (data.success && data.data && data.data.d) {
                        const soData = data.data.d;
                        
                        // Set Status Pembayaran dari statusName
                        const statusPembayaran = document.getElementById('statusPembayaran');
                        const statusPembayaran2 = document.getElementById('statusPembayaran2');
                        if (statusPembayaran && soData.statusName) {
                            statusPembayaran.value = soData.statusName;
                        }
                        if (statusPembayaran2 && soData.statusName) {
                            statusPembayaran2.value = soData.statusName;
                        }
                        
                        // Populate Other Info fields
                        // Kena Pajak checkbox
                        const taxableCheckbox = document.getElementById('taxableCheckbox');
                        if (taxableCheckbox && typeof soData.taxable !== 'undefined') {
                            taxableCheckbox.checked = soData.taxable;
                        }
                        
                        // Inclusive Pajak checkbox
                        const inclusiveTaxCheckbox = document.getElementById('inclusiveTaxCheckbox');
                        if (inclusiveTaxCheckbox && typeof soData.inclusiveTax !== 'undefined') {
                            inclusiveTaxCheckbox.checked = soData.inclusiveTax;
                        }
                        
                        // Syarat Pembayaran from paymentTerm.name
                        const paymentTermField = document.getElementById('paymentTerm');
                        if (paymentTermField && soData.paymentTerm && soData.paymentTerm.name) {
                            paymentTermField.value = soData.paymentTerm.name;
                        }
                        
                        // Cabang from branchId - fetch branch name from API
                        const branchField = document.getElementById('branch');
                        if (branchField && soData.branchId) {
                            // Store branchId for submission
                            branchField.dataset.branchId = soData.branchId;
                            
                            // First show the ID as loading indicator
                            branchField.value = `Loading... (${soData.branchId})`;
                            
                            // Try to fetch branch details from dedicated API endpoint
                            fetch(`../branch/api_detail.php?id=${soData.branchId}`)
                                .then(response => response.json())
                                .then(branchData => {
                                    console.log('Branch Detail API Response:', branchData);
                                    
                                    if (branchData.success && branchData.data && branchData.data.d) {
                                        const branch = branchData.data.d;
                                        // Show branch name instead of just ID
                                        branchField.value = branch.name || `Branch ${soData.branchId}`;
                                        console.log('Loaded branch name:', branch.name);
                                    } else {
                                        // Fallback to ID if API fails
                                        branchField.value = `Branch ${soData.branchId}`;
                                        console.log('Failed to load branch details, using ID:', soData.branchId);
                                    }
                                })
                                .catch(error => {
                                    console.log('Branch API error:', error.message);
                                    // Fallback to ID if request fails
                                    branchField.value = `Branch ${soData.branchId}`;
                                });
                        }
                        
                        // Alamat Pengiriman from toAddress
                        const toAddressField = document.getElementById('toAddress');
                        if (toAddressField && soData.toAddress) {
                            toAddressField.value = soData.toAddress;
                        }
                        
                        // Jenis Pengiriman from shipment.name
                        const deliveryTypeField = document.getElementById('deliveryType');
                        if (deliveryTypeField && soData.shipment && soData.shipment.name) {
                            deliveryTypeField.value = soData.shipment.name;
                        }
                        
                        // Tanggal Pengiriman from shipDate
                        const deliveryDateField = document.getElementById('deliveryDate');
                        if (deliveryDateField && soData.shipDate) {
                            deliveryDateField.value = soData.shipDate;
                        }
                        
                        console.log('Loaded statusName:', soData.statusName);
                        console.log('Loaded Other Info fields:', {
                            taxable: soData.taxable,
                            inclusiveTax: soData.inclusiveTax,
                            paymentTerm: soData.paymentTerm?.name,
                            branchId: soData.branchId,
                            toAddress: soData.toAddress,
                            shipmentName: soData.shipment?.name,
                            shipDate: soData.shipDate
                        });
                        console.log('Full API Response for debugging:', soData);
                    } else {
                        console.log('Failed to load SO data:', data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error loading SO data:', error);
                });
        }
        
        // Submit Invoice Function
        function submitInvoice() {
            // Get session ID - try multiple sources
            let sessionId = localStorage.getItem('accurate_session_id') || 
                           localStorage.getItem('session_id') ||
                           sessionStorage.getItem('accurate_session_id') ||
                           sessionStorage.getItem('session_id');
            
            console.log('Found session ID:', sessionId);
            
            if (!sessionId) {
                // Try to get from current page or make a quick call to get it
                fetch('../get_session_id.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Session API response:', data);
                        
                        if (data.success && data.sessionId) {
                            sessionId = data.sessionId;
                            console.log('Session ID from API:', sessionId);
                            
                            // Save to localStorage for future use
                            localStorage.setItem('accurate_session_id', sessionId);
                            // Continue with submission
                            performSubmission(sessionId);
                        } else {
                            alert('Session ID tidak ditemukan: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error getting session ID:', error);
                        alert('Error getting session ID: ' + error.message);
                    });
                return;
            }
            
            // Continue with submission if session ID found
            performSubmission(sessionId);
        }
        
        function performSubmission(sessionId) {
            
            // Collect form data
            const formData = new URLSearchParams();
            
            // Basic info from URL parameters
            formData.append('customerNo', '<?php echo $customer_no; ?>');
            formData.append('branchId', document.getElementById('branch').dataset.branchId || '<?php echo $salesOrderDetail['branchId'] ?? ''; ?>');
            
            // Format tanggal ke DD/MM/YYYY untuk Accurate API
            const today = new Date();
            const formattedDate = String(today.getDate()).padStart(2, '0') + '/' + 
                                String(today.getMonth() + 1).padStart(2, '0') + '/' + 
                                today.getFullYear();
            formData.append('transDate', formattedDate);
            
            // Other Info fields
            const toAddress = document.getElementById('toAddress').value;
            const deliveryDate = document.getElementById('deliveryDate').value;
            const deliveryType = document.getElementById('deliveryType').value;
            const taxableCheckbox = document.getElementById('taxableCheckbox');
            const inclusiveTaxCheckbox = document.getElementById('inclusiveTaxCheckbox');
            const reverseInvoiceCheckbox = document.getElementById('reverseInvoice');
            const paymentTerm = document.getElementById('paymentTerm').value;
            
            if (toAddress) formData.append('toAddress', toAddress);
            if (deliveryDate) {
                // Convert delivery date to DD/MM/YYYY format if needed
                let formattedDeliveryDate = deliveryDate;
                if (deliveryDate.includes('-')) {
                    // If it's in YYYY-MM-DD format, convert it
                    const parts = deliveryDate.split('-');
                    if (parts.length === 3) {
                        formattedDeliveryDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                    }
                }
                formData.append('shipDate', formattedDeliveryDate);
                console.log('Delivery date converted:', deliveryDate, '->', formattedDeliveryDate);
            }
            if (deliveryType) formData.append('shipmentName', deliveryType);
            if (taxableCheckbox) formData.append('taxable', taxableCheckbox.checked ? 'true' : 'false');
            if (inclusiveTaxCheckbox) formData.append('inclusiveTax', inclusiveTaxCheckbox.checked ? 'true' : 'false');
            if (reverseInvoiceCheckbox) formData.append('reverseInvoice', reverseInvoiceCheckbox.checked ? 'true' : 'false');
            if (paymentTerm) formData.append('paymentTermName', paymentTerm);
            
            // Detail Items from sales order data
            const soData = <?php echo json_encode($salesOrderDetail ?? []); ?>;
            if (soData && soData.detailItem) {
                soData.detailItem.forEach((item, itemIndex) => {
                    // Basic item info
                    formData.append(`detailItem[${itemIndex}].itemNo`, item.item.no);
                    formData.append(`detailItem[${itemIndex}].quantity`, item.quantity);
                    formData.append(`detailItem[${itemIndex}].unitPrice`, item.unitPrice);
                    formData.append(`detailItem[${itemIndex}].warehouseName`, item.warehouse.name);
                    
                    // Use saved serial data if available, otherwise use existing data
                    const savedSerialData = itemSerialData[item.id];
                    
                    if (savedSerialData && savedSerialData.length > 0) {
                        // Use manually entered serial data
                        savedSerialData.forEach((serialEntry, serialIndex) => {
                            formData.append(`detailItem[${itemIndex}].detailSerialNumber[${serialIndex}].serialNumberNo`, serialEntry.serial);
                            formData.append(`detailItem[${itemIndex}].detailSerialNumber[${serialIndex}].quantity`, 1);
                        });
                    } else if (item.detailSerialNumber && Array.isArray(item.detailSerialNumber)) {
                        // Use existing serial numbers from API
                        item.detailSerialNumber.forEach((serialDetail, serialIndex) => {
                            if (serialDetail.serialNumber && serialDetail.serialNumber.number) {
                                formData.append(`detailItem[${itemIndex}].detailSerialNumber[${serialIndex}].serialNumberNo`, serialDetail.serialNumber.number);
                                formData.append(`detailItem[${itemIndex}].detailSerialNumber[${serialIndex}].quantity`, 1);
                            }
                        });
                    } else {
                        // If no serial numbers, check if we have manual serial data
                        if (item.quantity > 1) {
                            // Multiple quantity without serials - create entries for each qty
                            for (let qtyIndex = 0; qtyIndex < item.quantity; qtyIndex++) {
                                formData.append(`detailItem[${itemIndex}].detailSerialNumber[${qtyIndex}].quantity`, 1);
                                // Serial number would be empty if not provided
                                formData.append(`detailItem[${itemIndex}].detailSerialNumber[${qtyIndex}].serialNumberNo`, '');
                            }
                        }
                    }
                });
            }
            
            // Show loading state
            const submitButton = event.target;
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Submitting...';
            submitButton.disabled = true;
            
            console.log('Submitting with session ID:', sessionId);
            console.log('Transaction date format:', formattedDate);
            console.log('Form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Submit to API
            fetch('save_final.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Session-ID': sessionId
                },
                body: formData.toString()
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Get response as text first to check content
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                
                // Try to parse as JSON
                try {
                    const data = JSON.parse(text);
                    console.log('Invoice submission response:', data);
                    
                    if (data.success) {
                        alert('Invoice berhasil dibuat!');
                        // Redirect to invoice list or show success page
                        window.location.href = 'index.php';
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', text);
                    
                    // If it's HTML, likely an error page
                    if (text.includes('<br />') || text.includes('<html>')) {
                        alert('Server error occurred. Check console for details.');
                    } else {
                        alert('Invalid response format: ' + text.substring(0, 100));
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting invoice:', error);
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }
    </script>
</body>
</html>
