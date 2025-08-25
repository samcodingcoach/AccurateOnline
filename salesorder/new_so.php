﻿﻿﻿<?php
/**
 * Halaman Input Sales Order Baru
 * File: /salesorder/new_so.php
 * 
 * IMPORTANT: Ketika melakukan save/submit ke API Accurate, 
 * pastikan include header X-Session-ID untuk otentikasi
 */

require_once __DIR__ . '/../bootstrap.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Order - Nuansa</title>
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

        /* Search dropdown */
        #customerDropdown,
        #itemDropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        #customerDropdown div,
        #itemDropdown div {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        #customerDropdown div:hover,
        #itemDropdown div:hover {
            background-color: #f3f4f6;
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

        /* Table */
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
        }

        .summary-value {
            width: 120px;
            text-align: right;
            font-weight: 600;
        }

        .grand-total {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
        }

        /* Buttons */
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        button {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-primary {
            background: #111827;
            color: white;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        /* Checkbox and form controls */
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        /* Utility classes */
        .hidden {
            display: none !important;
        }

        .search-container {
            position: relative;
        }

        /* Status indicators */
        .status-indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-error {
            background-color: #fecaca;
            color: #991b1b;
        }

        /* Input styling in table */
        .quantity-input,
        .discount-input {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .container {
                margin: 10px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>Sales Order Form</h1>
            <a href="index.php" class="back-button">
                ← Kembali ke Daftar
            </a>
        </div>
        
        <form id="formSalesOrder" method="POST">
            <div class="form-content">
                
                <!-- Top Form Grid -->
                <div class="form-grid">
                    <!-- Row 1: Konsumen dan Payment Term -->
                    <div class="form-group">
                        <label for="customerSearch">Konsumen</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" 
                                   id="customerSearch" 
                                   name="customerSearch" 
                                   placeholder="Cari konsumen..."
                                   autocomplete="off"
                                   style="flex: 1;">
                            <select id="customerSelect" name="customerNo" style="flex: 1;">
                                <option value="">-- Pilih Konsumen --</option>
                            </select>
                        </div>
                        <div id="customerDropdown"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="paymentTermSelect">Payment Term</label>
                        <select id="paymentTermSelect" name="paymentTermName" required>
                            <option value="">-- Pilih Syarat Pembayaran --</option>
                        </select>
                    </div>
                    
                    <!-- Row 2: Cabang dan Tanggal Transaksi -->
                    <div class="form-group">
                        <label for="branchSelect">Cabang</label>
                        <select id="branchSelect" name="branchId" required>
                            <option value="">-- Pilih Cabang --</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transactionDate">Tanggal Transaksi</label>
                        <input type="date" 
                               id="transactionDate" 
                               name="transactionDate"
                               required>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="tabs">
                    <ul class="tab-list">
                        <li class="me-2">
                            <button type="button" class="tab-button active" onclick="switchTab('item')">Item</button>
                        </li>
                        <li class="me-2">
                            <button type="button" class="tab-button" onclick="switchTab('otherInfo')">Other Info</button>
                        </li>
                    </ul>
                </div>
                
                <!-- Tab Content: Item -->
                <div id="itemTab" class="tab-content active">
                    <!-- Item Search -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <div class="search-container">
                            <input type="text" 
                                   id="itemSearch" 
                                   name="itemSearch" 
                                   placeholder="Cari item..."
                                   autocomplete="off">
                            <div id="itemDropdown"></div>
                        </div>
                    </div>
                    
                    <!-- Item Table -->
                    <div class="table-container">
                        <table id="itemTable">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>Stok</th>
                                    <th>Harga Satuan</th>
                                    <th>Diskon</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemTableBody">
                                <tr id="noItemRow">
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
                                        Belum ada item yang dipilih
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary -->
                    <div class="summary">
                        <div class="summary-row grand-total">
                            <div class="summary-label">Grand Total:</div>
                            <div class="summary-value">Rp <span id="grandTotalAmount">0</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Content: Other Info -->
                <div id="otherInfoTab" class="tab-content">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                        <!-- Tax/Pajak -->
                        <div class="form-group">
                            <label style="margin-bottom: 16px; font-weight: 600;">Tax / Pajak</label>
                            
                            <div class="form-group" style="margin-bottom: 12px;">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="taxableCheckbox" name="taxable" value="true">
                                    <label for="taxableCheckbox" style="margin-bottom: 0; margin-left: 8px;">Pajak (11%)</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="inclusiveTaxCheckbox" name="inclusiveTax" value="true">
                                    <label for="inclusiveTaxCheckbox" style="margin-bottom: 0; margin-left: 8px;">Inclusive Pajak</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pengiriman -->
                        <div class="form-group">
                            <label style="margin-bottom: 16px; font-weight: 600;">Pengiriman</label>
                            
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="toAddress" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px;">Alamat Pengiriman</label>
                                <textarea id="toAddress" 
                                          name="toAddress" 
                                          rows="3"
                                          placeholder="Masukkan alamat pengiriman..."
                                          style="resize: vertical;"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="deliveryDate" style="font-size: 0.875rem; color: #374151; margin-bottom: 6px;">Tanggal Pengiriman</label>
                                <input type="date" 
                                       id="deliveryDate" 
                                       name="deliveryDate">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Hidden fields untuk menyimpan data -->
            <input type="hidden" id="selectedCustomerNo" name="selectedCustomerNo" value="">
            <input type="hidden" id="selectedBranchId" name="selectedBranchId" value="">
            <input type="hidden" id="selectedPaymentTerm" name="selectedPaymentTermName" value="">
            <input type="hidden" id="selectedItems" name="selectedItems" value="">
            <input type="hidden" id="selectedTaxable" name="selectedTaxable" value="false">
            <input type="hidden" id="selectedInclusiveTax" name="selectedInclusiveTax" value="false">
            <input type="hidden" id="selectedToAddress" name="selectedToAddress" value="">
            
            <!-- Action Buttons -->
            <div class="button-group">
                <button type="button" class="btn-primary" id="btnSubmit" onclick="submitOrder()">
                    Submit Order
                </button>
            </div>
            
        </form>
    </div>

    <script>
        // Variable untuk menyimpan data customer, branch, payment terms, tax settings, address, description, dan items
        let customersData = [];
        let branchesData = [];
        let paymentTermsData = [];
        let itemsData = [];
        let selectedCustomer = null;
        let selectedBranch = null;
        let selectedPaymentTerm = null;
        let selectedTaxable = false;
        let selectedInclusiveTax = false;
        let selectedToAddress = '';
        let selectedDescription = '';
        let selectedItems = [];
        let itemCounter = 0;
        let selectedCustomerPriceCategory = null; // Untuk menyimpan priceCategory dari customer yang dipilih
        
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
            if (tabName === 'item') {
                const itemTab = document.getElementById('itemTab');
                const itemButton = document.querySelector('[onclick="switchTab(\'item\')"]');
                
                if (itemTab) itemTab.classList.add('active');
                if (itemButton) itemButton.classList.add('active');
            } else if (tabName === 'otherInfo') {
                const otherInfoTab = document.getElementById('otherInfoTab');
                const otherInfoButton = document.querySelector('[onclick="switchTab(\'otherInfo\')"]');
                
                if (otherInfoTab) otherInfoTab.classList.add('active');
                if (otherInfoButton) otherInfoButton.classList.add('active');
            }
        }
        
        // Update totals display
        function updateTotals(subtotal) {
            console.log('Calculating totals - Subtotal from items:', subtotal);
            
            // Calculate tax if applicable
            let grandTotal = subtotal;
            if (selectedTaxable) {
                const tax = subtotal * 0.11; // 11% tax
                grandTotal = selectedInclusiveTax ? subtotal : subtotal + tax;
                console.log('Tax applied - Tax amount:', tax, 'Inclusive:', selectedInclusiveTax, 'Grand Total:', grandTotal);
            } else {
                console.log('No tax applied - Grand Total equals Subtotal:', grandTotal);
            }
            
            // Format currency using our existing function
            const formattedAmount = formatCurrency(grandTotal).replace('Rp', '').trim();
            document.getElementById('grandTotalAmount').textContent = formattedAmount;
            console.log('Grand total updated to:', formattedAmount);
        }
        
        // Update item quantity
        function updateItemQuantity(index, newQuantity) {
            const quantity = parseInt(newQuantity);
            if (quantity > 0 && quantity <= selectedItems[index].availableStock) {
                selectedItems[index].quantity = quantity;
                updateItemTable();
            } else {
                // Reset to previous value if invalid
                updateItemTable();
            }
        }
        
        // Update item discount
        function updateItemDiscount(index, newDiscount) {
            const discount = parseFloat(newDiscount);
            if (discount >= 0 && discount <= 100) {
                selectedItems[index].discount = discount;
                updateItemTable();
            } else {
                // Reset to previous value if invalid
                updateItemTable();
            }
        }
        
        // Remove item from list
        function removeItem(index) {
            selectedItems.splice(index, 1);
            updateItemTable();
        }
        
        // Load customer data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, starting initialization...'); // Debug log
            
            // Load data with delay to ensure page is fully ready
            setTimeout(function() {
                loadCustomers();
                loadBranches(); 
                loadPaymentTerms();
                loadItems();
                setDefaultDate();
                setupTaxCheckboxes();
                setupAddressAndDescription();
                setupItemSearch();
                setupCustomerEventListeners();
                setupDateValidation(); // Tambahkan setup validasi tanggal
                
                // Initialize totals to 0 when page loads
                updateTotals(0);
                
                console.log('All initialization functions called'); // Debug log
            }, 100);
        });
        
        // Set tanggal default ke hari ini
        function setDefaultDate() {
            const today = new Date();
            const dateString = today.toISOString().split('T')[0];
            
            // Set default value dan minimum date untuk transaction date
            document.getElementById('transactionDate').value = dateString;
            document.getElementById('transactionDate').min = dateString;
            
            // Set minimum date untuk delivery date (akan diupdate saat transaction date berubah)
            document.getElementById('deliveryDate').min = dateString;
        }
        
        // Setup tax checkboxes event listeners
        function setupTaxCheckboxes() {
            // Handle taxable checkbox
            document.getElementById('taxableCheckbox').addEventListener('change', function() {
                selectedTaxable = this.checked;
                document.getElementById('selectedTaxable').value = selectedTaxable;
                updateItemTable(); // Update totals when tax setting changes
                console.log('Taxable changed to:', selectedTaxable);
            });
            
            // Handle inclusive tax checkbox
            document.getElementById('inclusiveTaxCheckbox').addEventListener('change', function() {
                selectedInclusiveTax = this.checked;
                document.getElementById('selectedInclusiveTax').value = selectedInclusiveTax;
                updateItemTable(); // Update totals when tax setting changes
                console.log('Inclusive Tax changed to:', selectedInclusiveTax);
            });
        }
        
        // Setup address and description event listeners
        function setupAddressAndDescription() {
            // Handle address textarea
            const toAddressElement = document.getElementById('toAddress');
            if (toAddressElement) {
                toAddressElement.addEventListener('input', function() {
                    selectedToAddress = this.value.trim();
                    document.getElementById('selectedToAddress').value = selectedToAddress;
                    console.log('Address changed to:', selectedToAddress);
                });
            }
            
            // Handle description textarea
            const descriptionElement = document.getElementById('description');
            if (descriptionElement) {
                descriptionElement.addEventListener('input', function() {
                    selectedDescription = this.value.trim();
                    document.getElementById('selectedDescription').value = selectedDescription;
                    console.log('Description changed to:', selectedDescription);
                });
            }
        }
        
        // Setup date validation event listeners
        function setupDateValidation() {
            // Handle transaction date change
            document.getElementById('transactionDate').addEventListener('change', function() {
                const transactionDate = this.value;
                const deliveryDateInput = document.getElementById('deliveryDate');
                
                // Update minimum date untuk delivery date berdasarkan transaction date
                if (transactionDate) {
                    deliveryDateInput.min = transactionDate;
                    
                    // Jika delivery date sudah diset dan lebih kecil dari transaction date, reset
                    if (deliveryDateInput.value && deliveryDateInput.value < transactionDate) {
                        deliveryDateInput.value = '';
                        console.log('Tanggal pengiriman telah direset karena kurang dari tanggal transaksi yang baru');
                    }
                }
                
                console.log('Transaction date changed to:', transactionDate);
                console.log('Delivery date minimum set to:', transactionDate);
            });
            
            // Handle delivery date change validation
            document.getElementById('deliveryDate').addEventListener('change', function() {
                const deliveryDate = this.value;
                const transactionDate = document.getElementById('transactionDate').value;
                
                if (deliveryDate && transactionDate && deliveryDate < transactionDate) {
                    console.log('Tanggal pengiriman tidak boleh kurang dari tanggal transaksi');
                    this.value = ''; // Reset delivery date
                    this.focus();
                }
                
                console.log('Delivery date changed to:', deliveryDate);
            });
            
            // Validasi real-time saat user mengetik tanggal
            document.getElementById('transactionDate').addEventListener('input', function() {
                const today = new Date().toISOString().split('T')[0];
                if (this.value && this.value < today) {
                    this.setCustomValidity('Tanggal transaksi tidak boleh kurang dari hari ini');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            document.getElementById('deliveryDate').addEventListener('input', function() {
                const transactionDate = document.getElementById('transactionDate').value;
                if (this.value && transactionDate && this.value < transactionDate) {
                    this.setCustomValidity('Tanggal pengiriman tidak boleh kurang dari tanggal transaksi');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        // Load semua items untuk autocomplete
        function loadItems() {
            fetch('../item/api_listbarang.php?limit=1000')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Items API Response:', data); // Debug log
                    
                    if (data.success && data.data) {
                        // Handle different possible response structures
                        let items = [];
                        
                        // Check if data.data.items exists
                        if (data.data.items && data.data.items.d && Array.isArray(data.data.items.d)) {
                            items = data.data.items.d;
                        }
                        // Check if data.data.d exists (direct Accurate API format)
                        else if (data.data.d && Array.isArray(data.data.d)) {
                            items = data.data.d;
                        }
                        // Check if data.data is array directly
                        else if (Array.isArray(data.data)) {
                            items = data.data;
                        }
                        
                        // Filter items yang availableToSell > 0
                        const availableItems = items.filter(item => item.availableToSell > 0);
                        
                        if (availableItems.length > 0) {
                            itemsData = availableItems;
                            console.log('Loaded', availableItems.length, 'available items');
                        } else {
                            console.warn('No available items found');
                            // Load fallback for testing
                            loadItemsFallback();
                        }
                    } else {
                        console.error('Items API error:', data);
                        // Load fallback for testing
                        loadItemsFallback();
                    }
                })
                .catch(error => {
                    console.error('Items fetch error:', error);
                    // Load fallback for testing  
                    loadItemsFallback();
                });
        }
        
        // Fallback untuk load items jika API gagal
        function loadItemsFallback() {
            console.log('Using items fallback');
            
            // Fallback dengan items dummy untuk testing
            itemsData = [
                { 
                    no: 'ITEM001', 
                    name: 'Test Item 1 - Laptop Gaming', 
                    availableToSell: 10, 
                    unitPrice: 15000000 
                },
                { 
                    no: 'ITEM002', 
                    name: 'Test Item 2 - Mouse Wireless', 
                    availableToSell: 25, 
                    unitPrice: 250000 
                },
                { 
                    no: 'ITEM003', 
                    name: 'Test Item 3 - Keyboard Mechanical', 
                    availableToSell: 15, 
                    unitPrice: 750000 
                },
                { 
                    no: 'BARANG001', 
                    name: 'Monitor LED 24 inch', 
                    availableToSell: 8, 
                    unitPrice: 2500000 
                },
                { 
                    no: 'PROD005', 
                    name: 'Printer Inkjet Canon', 
                    availableToSell: 12, 
                    unitPrice: 1200000 
                }
            ];
            
            console.log('Items fallback loaded:', itemsData.length, 'items');
            console.log('Fallback items:', itemsData);
        }
        
        // Fungsi untuk mengambil harga item berdasarkan priceCategory customer
        async function getItemPriceByCategory(itemNo) {
            try {
                let url = `../sellingprice/listprice.php?no=${itemNo}`;
                
                // Tambahkan priceCategoryName jika customer memiliki priceCategory
                if (selectedCustomerPriceCategory) {
                    url += `&priceCategoryName=${encodeURIComponent(selectedCustomerPriceCategory)}`;
                }
                
                console.log('Fetching item price from:', url); // Debug log
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success && data.data && data.data.d) {
                    return data.data.d.unitPrice || 0;
                } else {
                    console.warn('Failed to get item price:', data.message);
                    return 0;
                }
            } catch (error) {
                console.error('Error fetching item price:', error);
                return 0;
            }
        }
        
        // Setup item search event listeners
        function setupItemSearch() {
            console.log('setupItemSearch called, itemsData:', itemsData.length, 'items'); // DEBUG
            
            // Handle item search
            document.getElementById('itemSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const dropdown = document.getElementById('itemDropdown');
                
                console.log('Search term:', searchTerm, 'itemsData length:', itemsData.length); // DEBUG
                
                if (searchTerm.length < 2) {
                    dropdown.style.display = 'none';
                    return;
                }
                
                // Filter items based on search term (no atau name)
                const filteredItems = itemsData.filter(item => 
                    item.no.toLowerCase().includes(searchTerm) ||
                    item.name.toLowerCase().includes(searchTerm)
                );
                
                console.log('Filtered items:', filteredItems.length); // DEBUG
                
                // Show dropdown results
                if (filteredItems.length > 0) {
                    dropdown.innerHTML = '';
                    filteredItems.slice(0, 10).forEach(item => { // Limit to 10 results
                        const div = document.createElement('div');
                        div.style.padding = '8px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        
                        // Warna berdasarkan stok
                        let stockColor = '#28a745'; // Green for good stock
                        let stockWarning = '';
                        
                        if (item.availableToSell < 5) {
                            stockColor = '#dc3545'; // Red for low stock
                            stockWarning = '  Stok Terbatas';
                        } else if (item.availableToSell < 10) {
                            stockColor = '#ffc107'; // Yellow for medium stock
                            stockWarning = '  Stok Rendah';
                        }
                        
                        div.innerHTML = `
                            <strong>${item.name}</strong><br>
                            <small>Kode: ${item.no} | <span style="color: ${stockColor}">Stok: ${item.availableToSell}${stockWarning}</span> | Harga: ${formatCurrency(item.unitPrice)}</small>
                        `;
                        
                        div.addEventListener('click', async function() {
                            await addItemToOrder(item);
                            dropdown.style.display = 'none';
                            document.getElementById('itemSearch').value = '';
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = '#f0f0f0';
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = 'white';
                        });
                        
                        dropdown.appendChild(div);
                    });
                    dropdown.style.display = 'block';
                    console.log('Dropdown shown with', filteredItems.length, 'items'); // DEBUG
                } else {
                    dropdown.innerHTML = '<div style="padding: 8px;">Item tidak ditemukan atau stok tidak tersedia</div>';
                    dropdown.style.display = 'block';
                    console.log('No items found message shown'); // DEBUG
                }
            });
        }
        
        // Add item to order
        async function addItemToOrder(item) {
            // Check if item already exists in order
            const existingItemIndex = selectedItems.findIndex(orderItem => orderItem.itemNo === item.no);
            
            if (existingItemIndex > -1) {
                // Jika item sudah ada, cek apakah menambah quantity akan melebihi stok
                const currentQuantity = selectedItems[existingItemIndex].quantity;
                const newQuantity = currentQuantity + 1;
                
                // Gunakan stok dari item asli, bukan dari selectedItems
                const availableStock = item.availableToSell;
                
                if (newQuantity > availableStock) {
                    console.log(`Quantity tidak dapat ditambah. Stok tersedia: ${availableStock}, quantity saat ini: ${currentQuantity}`);
                    return;
                }
                
                selectedItems[existingItemIndex].quantity = newQuantity;
                selectedItems[existingItemIndex].totalPrice = selectedItems[existingItemIndex].quantity * selectedItems[existingItemIndex].unitPrice;
                // Update stok info juga jika berubah
                selectedItems[existingItemIndex].availableToSell = availableStock;
            } else {
                // Jika item baru, cek stok tersedia
                if (item.availableToSell < 1) {
                    console.log(`Item "${item.name}" tidak dapat ditambahkan. Stok tidak tersedia.`);
                    return;
                }
                
                // Ambil harga berdasarkan priceCategory customer
                console.log('Getting price for item:', item.no, 'with priceCategory:', selectedCustomerPriceCategory);
                const categoryPrice = await getItemPriceByCategory(item.no);
                
                // Gunakan harga dari kategori jika tersedia, jika tidak gunakan harga default
                const finalPrice = categoryPrice > 0 ? categoryPrice : (item.unitPrice || 0);
                
                console.log('Item price - Default:', item.unitPrice, 'Category:', categoryPrice, 'Final:', finalPrice);
                
                // Tambahkan item dengan format yang konsisten dengan backup
                const newItem = {
                    index: itemCounter++,
                    itemNo: item.no,
                    itemName: item.name,
                    quantity: 1,
                    unitPrice: finalPrice,
                    discount: 0,
                    totalPrice: finalPrice,
                    availableToSell: item.availableToSell
                };
                selectedItems.push(newItem);
            }
            
            updateItemTable();
            updateSelectedItemsInput();
            console.log('Current selected items:', selectedItems);
        }
        
        // Update item table display
        function updateItemTable() {
            const tbody = document.getElementById('itemTableBody');
            const noItemRow = document.getElementById('noItemRow');
            
            if (selectedItems.length === 0) {
                noItemRow.style.display = 'table-row';
                // Remove all other rows
                const existingRows = tbody.querySelectorAll('tr:not(#noItemRow)');
                existingRows.forEach(row => row.remove());
                updateTotals(0); // Add this to update totals when no items
                return;
            }
            
            noItemRow.style.display = 'none';
            
            // Clear existing rows except no item row
            const existingRows = tbody.querySelectorAll('tr:not(#noItemRow)');
            existingRows.forEach(row => row.remove());
            
            let subtotal = 0; // Add subtotal calculation
            
            // Add rows for each selected item
            selectedItems.forEach((item, index) => {
                const row = document.createElement('tr');
                
                // Calculate total for this item
                const total = (item.unitPrice - item.discount) * item.quantity;
                subtotal += total;
                
                // Update item.totalPrice to match calculation
                selectedItems[index].totalPrice = total;
                
                console.log(`Item ${index + 1}: ${item.itemName} - Unit: ${item.unitPrice}, Discount: ${item.discount}, Qty: ${item.quantity}, Total: ${total}`);
                console.log(`Running subtotal after item ${index + 1}: ${subtotal}`);
                
                // Add highlighting for recently updated prices
                const isRecentlyUpdated = item.priceUpdated && (Date.now() - item.priceUpdated) < 10000; // 10 seconds
                if (isRecentlyUpdated) {
                    row.style.backgroundColor = '#fff3cd';
                    row.style.borderLeft = '3px solid #ffc107';
                }
                
                row.innerHTML = `
                    <td>${item.itemNo}</td>
                    <td>${item.itemName}</td>
                    <td>
                        <input type="number" 
                               value="${item.quantity}" 
                               min="1" 
                               max="${item.availableToSell}"
                               onchange="updateItemQuantity(${index}, this.value)"
                               oninput="validateQuantityInput(this, ${item.availableToSell})"
                               title="Maksimal: ${item.availableToSell}">
                    </td>
                    <td>
                        ${item.availableToSell}
                    </td>
                    <td>${formatCurrency(item.unitPrice)}${isRecentlyUpdated ? ' ' : ''}</td>
                    <td>
                        <input type="number" 
                               value="${item.discount}" 
                               min="0" 
                               max="${item.unitPrice}"
                               step="1000"
                               placeholder="0"
                               title="Discount dalam nominal (Rp). Maksimal: ${formatCurrency(item.unitPrice)}"
                               onchange="updateItemDiscount(${index}, this.value)"
                               oninput="validateDiscountInput(this, ${item.unitPrice})">
                    </td>
                    <td><strong style="color: #007bff;">${formatCurrency(total)}</strong></td>
                    <td>
                        <button type="button" class="btn-danger"
                                onclick="removeItem(${index})" title="Hapus item ini">
                            Hapus
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // Update totals with calculated subtotal
            updateTotals(subtotal);
            
            // Update hidden field
            document.getElementById('selectedItems').value = JSON.stringify(selectedItems);
        }
        
        // Validate quantity input in real-time
        function validateQuantityInput(input, maxStock) {
            const value = parseInt(input.value) || 0;
            
            // Reset border color
            input.style.borderColor = '';
            input.style.backgroundColor = '';
            
            if (value > maxStock) {
                // Show red border and background if exceeds stock
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#ffe6e6';
                input.title = `Melebihi stok! Maksimal: ${maxStock}`;
            } else if (value < 1) {
                // Show orange border if less than 1
                input.style.borderColor = '#ffc107';
                input.style.backgroundColor = '#fff8e1';
                input.title = 'Minimal quantity: 1';
            } else {
                // Valid quantity - green border
                input.style.borderColor = '#28a745';
                input.style.backgroundColor = '#e8f5e8';
                input.title = `Valid. Maksimal: ${maxStock}`;
            }
        }
        
        // Validate discount input in real-time
        function validateDiscountInput(input, maxDiscount) {
            const value = parseFloat(input.value) || 0;
            
            // Reset border color
            input.style.borderColor = '';
            input.style.backgroundColor = '';
            
            if (value > maxDiscount) {
                // Show red border and background if exceeds unit price
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#ffe6e6';
                input.title = `Discount melebihi harga unit! Maksimal: ${formatCurrency(maxDiscount)}`;
            } else if (value < 0) {
                // Show orange border if negative
                input.style.borderColor = '#ffc107';
                input.style.backgroundColor = '#fff8e1';
                input.title = 'Discount tidak boleh negatif';
            } else {
                // Valid discount - green border
                input.style.borderColor = '#28a745';
                input.style.backgroundColor = '#e8f5e8';
                input.title = `Valid. Maksimal: ${formatCurrency(maxDiscount)}`;
            }
        }
        
        // Update item quantity
        function updateItemQuantity(index, newQuantity) {
            const quantity = parseInt(newQuantity) || 1;
            if (quantity < 1) {
                console.log('Quantity tidak boleh kurang dari 1');
                updateItemTable(); // Refresh table to revert change
                return;
            }
            
            const item = selectedItems[index];
            
            // Validasi quantity tidak boleh melebihi stok tersedia
            if (quantity > item.availableToSell) {
                console.log(`Quantity tidak boleh melebihi stok tersedia. Stok tersedia: ${item.availableToSell}`);
                updateItemTable(); // Refresh table to revert change
                return;
            }
            
            selectedItems[index].quantity = quantity;
            selectedItems[index].totalPrice = (selectedItems[index].unitPrice - selectedItems[index].discount) * quantity;
            
            updateItemTable();
            updateSelectedItemsInput();
        }
        
        // Update item discount
        function updateItemDiscount(index, newDiscount) {
            const discount = parseFloat(newDiscount) || 0;
            
            // Validasi discount tidak boleh negatif
            if (discount < 0) {
                console.log('Discount tidak boleh negatif');
                updateItemTable(); // Refresh table to revert change
                return;
            }
            
            // Validasi discount tidak boleh melebihi harga unit
            const item = selectedItems[index];
            if (discount > item.unitPrice) {
                console.log(`Discount tidak boleh melebihi harga unit. Harga unit: ${formatCurrency(item.unitPrice)}`);
                updateItemTable(); // Refresh table to revert change
                return;
            }
            
            selectedItems[index].discount = discount;
            selectedItems[index].totalPrice = (selectedItems[index].unitPrice - discount) * selectedItems[index].quantity;
            
            updateItemTable();
            updateSelectedItemsInput();
        }
        
        // Remove item from order
        function removeItem(index) {
            if (confirm('Yakin ingin menghapus item ini?')) {
                selectedItems.splice(index, 1);
                updateItemTable();
                updateSelectedItemsInput();
            }
        }
        
        // Update hidden input for selected items
        function updateSelectedItemsInput() {
            // Format data untuk API sesuai struktur detailItem[n]
            const formattedItems = selectedItems.map((item, index) => ({
                [`detailItem[${index}].itemNo`]: item.itemNo,
                [`detailItem[${index}].unitPrice`]: item.unitPrice,
                [`detailItem[${index}].quantity`]: item.quantity,
                [`detailItem[${index}].itemCashDiscount`]: item.discount
            }));
            
            document.getElementById('selectedItems').value = JSON.stringify(formattedItems);
        }
        
        // Generate data yang akan di-POST ke endpoint /save.do
        function generatePostData() {
            console.log('generatePostData called with selectedItems:', selectedItems);
            console.log('selectedItems.length:', selectedItems.length);
            console.log('selectedCustomer object:', selectedCustomer);
            console.log('selectedBranch object:', selectedBranch);
            
            // Debug: Check what fields are available
            if (selectedCustomer) {
                console.log('selectedCustomer keys:', Object.keys(selectedCustomer));
                console.log('selectedCustomer.customerNo:', selectedCustomer.customerNo);
                console.log('selectedCustomer.id:', selectedCustomer.id);
            }
            
            if (selectedBranch) {
                console.log('selectedBranch keys:', Object.keys(selectedBranch));
                console.log('selectedBranch.id:', selectedBranch.id);
                console.log('selectedBranch.code:', selectedBranch.code);
                console.log('selectedBranch.branchName:', selectedBranch.branchName);
            }
            
            const postData = {
                // Data Customer - gunakan customerNo atau id yang tersedia
                customerNo: selectedCustomer ? (selectedCustomer.customerNo || selectedCustomer.id || '') : '',
                
                // Data Branch - gunakan id (harus integer untuk Accurate API)
                branchId: selectedBranch ? selectedBranch.id : '',
                
                // Data Payment Terms
                paymentTerm: selectedPaymentTerm ? selectedPaymentTerm.name : '',
                
                // Data Tax Settings
                taxable: selectedTaxable,
                inclusiveTax: selectedInclusiveTax,
                
                // Data Address & Description
                toAddress: selectedToAddress || '',
                description: selectedDescription || '',
                
                // Data Tanggal
                transDate: document.getElementById('transactionDate').value ? 
                           formatDateForStore(document.getElementById('transactionDate').value) : '',
                shipDate: document.getElementById('deliveryDate').value ? 
                          formatDateForStore(document.getElementById('deliveryDate').value) : '',
            };
            
            // Debug: Check if required fields are set
            console.log('postData.customerNo:', postData.customerNo);
            console.log('postData.branchId:', postData.branchId);
            
            if (!postData.customerNo) {
                console.error('VALIDATION ERROR: customerNo is empty!');
                console.log('selectedCustomer:', selectedCustomer);
            }
            
            if (!postData.branchId) {
                console.error('VALIDATION ERROR: branchId is empty!');
                console.log('selectedBranch:', selectedBranch);
            }
            
            // Tambahkan detail items sesuai format dokumentasi Accurate API: detailItem[n].field
            // Index dimulai dari 0 sesuai contoh Postman yang berhasil
            console.log('Adding detail items to postData...');
            selectedItems.forEach((item, index) => {
                console.log(`Adding item ${index}:`, item);
                postData[`detailItem[${index}].itemNo`] = item.itemNo;
                postData[`detailItem[${index}].unitPrice`] = item.unitPrice;
                postData[`detailItem[${index}].quantity`] = item.quantity;
                postData[`detailItem[${index}].itemCashDiscount`] = item.discount;
            });
            
            console.log('Final postData:', postData);
            
            return postData;
        }
        
        // Format currency display
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount || 0);
        }
        
        // Load semua customer untuk combo box
        function loadCustomers() {
            console.log('Loading customers...'); // Debug log
            
            fetch('../customer/listcustomer.php?limit=100')
                .then(response => {
                    console.log('Customer API response status:', response.status); // Debug log
                    return response.json();
                })
                .then(data => {
                    console.log('Customer API Response:', data); // Debug log
                    
                    if (data.success && data.data && data.data.customers) {
                        customersData = data.data.customers.d || [];
                        console.log('Loaded customers count:', customersData.length); // Debug log
                        
                        if (customersData.length > 0) {
                            populateCustomerSelect();
                            console.log('Customer select populated successfully'); // Debug log
                        } else {
                            console.warn('No customers found in response');
                            // Try fallback
                            loadCustomerFallback();
                        }
                    } else {
                        console.error('Gagal load customer:', data.message);
                        console.log('Gagal memuat data konsumen: ' + (data.message || 'Unknown error'));
                        loadCustomerFallback();
                    }
                })
                .catch(error => {
                    console.error('Customer fetch error:', error);
                    console.log('Terjadi kesalahan saat memuat data konsumen: ' + error.message);
                    loadCustomerFallback();
                });
        }
        
        // Fallback untuk load customer jika API gagal
        function loadCustomerFallback() {
            console.log('Using customer fallback');
            
            // Fallback dengan customer dummy untuk testing
            customersData = [
                { id: 1, customerNo: 'CUST001', name: 'Customer Test 1', email: 'test1@example.com' },
                { id: 2, customerNo: 'CUST002', name: 'Customer Test 2', email: 'test2@example.com' },
                { id: 3, customerNo: 'CUST003', name: 'Customer Test 3', email: 'test3@example.com' }
            ];
            
            populateCustomerSelect();
            
            // Berikan peringatan kepada user
            const select = document.getElementById('customerSelect');
            const warningOption = document.createElement('option');
            warningOption.value = '';
            warningOption.textContent = '-- Data konsumen tidak dapat dimuat, gunakan fallback --';
            warningOption.style.color = 'red';
            select.insertBefore(warningOption, select.firstChild.nextSibling);
        }
        
        // Load semua branch untuk combo box
        function loadBranches() {
            console.log('Loading branches...'); // Debug log
            
            fetch('../branch/listbranch.php')
                .then(response => {
                    console.log('Branch API response status:', response.status); // Debug log
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Branch API Response:', data); // Debug log
                    
                    if (data.success && data.data) {
                        // Handle different possible response structures
                        let branches = [];
                        
                        // Check if data.data is array directly
                        if (Array.isArray(data.data)) {
                            branches = data.data;
                        }
                        // Check if data.data.data exists (nested structure)
                        else if (data.data.data && Array.isArray(data.data.data)) {
                            branches = data.data.data;
                        }
                        // Check if data.data.d exists (Accurate API format)
                        else if (data.data.d && Array.isArray(data.data.d)) {
                            branches = data.data.d;
                        }
                        // Check if data.data has branches property
                        else if (data.data.branches && Array.isArray(data.data.branches)) {
                            branches = data.data.branches;
                        }
                        
                        console.log('Processed branches:', branches.length); // Debug log
                        
                        if (branches.length > 0) {
                            branchesData = branches;
                            populateBranchSelect();
                        } else {
                            console.warn('No branches found in response');
                            loadBranchFallback();
                        }
                    } else {
                        console.error('Branch API error:', data);
                        loadBranchFallback();
                    }
                })
                .catch(error => {
                    console.error('Branch fetch error:', error);
                    loadBranchFallback();
                });
        }
        
        // Load semua payment terms untuk combo box
        function loadPaymentTerms() {
            console.log('Loading payment terms...'); // Debug log
            
            fetch('../payterm/list_term.php?limit=100')
                .then(response => {
                    console.log('Payment Terms API response status:', response.status); // Debug log
                    console.log('Payment Terms API response headers:', [...response.headers.entries()]); // Debug log
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.text(); // Get as text first to debug
                })
                .then(responseText => {
                    console.log('Payment Terms API raw response:', responseText); // Debug log
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + e.message);
                    }
                    
                    console.log('Payment Terms API Response:', data); // Debug log
                    
                    if (data.success && data.data) {
                        // Handle different possible response structures
                        let paymentTerms = [];
                        
                        // Check if data.data.paymentTerms exists
                        if (data.data.paymentTerms && data.data.paymentTerms.d && Array.isArray(data.data.paymentTerms.d)) {
                            paymentTerms = data.data.paymentTerms.d;
                            console.log('Found payment terms in paymentTerms.d structure');
                        }
                        // Check if data.data.d exists (direct Accurate API format)
                        else if (data.data.d && Array.isArray(data.data.d)) {
                            paymentTerms = data.data.d;
                            console.log('Found payment terms in d structure');
                        }
                        // Check if data.data is array directly
                        else if (Array.isArray(data.data)) {
                            paymentTerms = data.data;
                            console.log('Found payment terms as direct array');
                        }
                        else {
                            console.warn('No payment terms found in expected structures');
                            console.log('Available data keys:', Object.keys(data.data));
                        }
                        
                        console.log('Processed payment terms:', paymentTerms.length); // Debug log
                        
                        // Filter payment terms yang tidak suspended
                        const activePaymentTerms = paymentTerms.filter(term => !term.suspended);
                        
                        if (activePaymentTerms.length > 0) {
                            paymentTermsData = activePaymentTerms;
                            populatePaymentTermSelect();
                        } else {
                            console.warn('No active payment terms found in response');
                            loadPaymentTermFallback();
                        }
                    } else {
                        console.error('Payment Terms API error:', data);
                        console.error('API returned success=false or no data field');
                        loadPaymentTermFallback();
                    }
                })
                .catch(error => {
                    console.error('Payment Terms fetch error:', error);
                    console.error('Error details:', error.message);
                    loadPaymentTermFallback();
                });
        }
        
        // Fallback untuk load branch jika API gagal
        function loadBranchFallback() {
            console.log('Using branch fallback');
            
            // Fallback dengan branch dummy untuk testing
            branchesData = [
                { id: 1, name: 'Cabang Pusat', code: 'HQ' },
                { id: 2, name: 'Cabang Jakarta', code: 'JKT' },
                { id: 3, name: 'Cabang Surabaya', code: 'SBY' }
            ];
            
            populateBranchSelect();
            
            // Berikan peringatan kepada user
            const select = document.getElementById('branchSelect');
            const warningOption = document.createElement('option');
            warningOption.value = '';
            warningOption.textContent = '-- Data cabang tidak dapat dimuat, gunakan fallback --';
            warningOption.style.color = 'red';
            select.insertBefore(warningOption, select.firstChild.nextSibling);
        }
        
        // Fallback untuk load payment terms jika API gagal
        function loadPaymentTermFallback() {
            console.log('Using payment terms fallback');
            
            // Fallback dengan payment terms dummy untuk testing
            paymentTermsData = [
                { id: 1, name: 'Cash', dueDays: 0, suspended: false, defaultTerm: true },
                { id: 2, name: 'NET 30', dueDays: 30, suspended: false, defaultTerm: false },
                { id: 3, name: 'C.O.D', dueDays: 0, suspended: false, cashOnDelivery: true }
            ];
            
            populatePaymentTermSelect();
            
            // Berikan peringatan kepada user
            const select = document.getElementById('paymentTermSelect');
            const warningOption = document.createElement('option');
            warningOption.value = '';
            warningOption.textContent = '-- Data syarat pembayaran tidak dapat dimuat, gunakan fallback --';
            warningOption.style.color = 'red';
            select.insertBefore(warningOption, select.firstChild.nextSibling);
        }
        
        // Populate customer select options
        function populateCustomerSelect() {
            const select = document.getElementById('customerSelect');
            console.log('Populating customer select, data length:', customersData.length); // Debug log
            
            select.innerHTML = '<option value="">-- Pilih Konsumen --</option>';
            
            if (!customersData || customersData.length === 0) {
                console.warn('No customers data to populate');
                select.innerHTML = '<option value="">-- Tidak ada data konsumen --</option>';
                return;
            }
            
            customersData.forEach((customer, index) => {
                console.log('Adding customer:', index, customer.name, customer.customerNo, 'priceCategory:', customer.priceCategory); // Debug log
                
                const option = document.createElement('option');
                option.value = customer.customerNo || customer.id;
                option.textContent = `${customer.name || 'N/A'} (${customer.customerNo || customer.id || 'N/A'})`;
                select.appendChild(option);
            });
            
            console.log('Customer select options added:', select.options.length - 1); // Debug log
        }
        
        // Populate branch select options
        function populateBranchSelect() {
            const select = document.getElementById('branchSelect');
            select.innerHTML = '<option value="">-- Pilih Cabang --</option>';
            
            if (!branchesData || branchesData.length === 0) {
                console.warn('No branches data to populate');
                return;
            }
            
            branchesData.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                
                // Handle different naming conventions
                let branchName = branch.name || branch.branchName || branch.description || 'Unknown';
                let branchCode = branch.code || branch.branchCode || branch.id;
                
                option.textContent = `${branchName} (${branchCode})`;
                select.appendChild(option);
                
                console.log('Added branch:', branchName, branchCode); // Debug log
            });
            
            console.log('Populated', branchesData.length, 'branches'); // Debug log
        }
        
        // Populate payment terms select options
        function populatePaymentTermSelect() {
            const select = document.getElementById('paymentTermSelect');
            select.innerHTML = '<option value="">-- Pilih Syarat Pembayaran --</option>';
            
            if (!paymentTermsData || paymentTermsData.length === 0) {
                console.warn('No payment terms data to populate');
                return;
            }
            
            paymentTermsData.forEach(term => {
                const option = document.createElement('option');
                option.value = term.name; // Menggunakan name sebagai value
                
                // Create display text with additional info
                let displayText = term.name;
                // Use dueDays from Accurate API (not netDays)
                if (term.dueDays && term.dueDays > 0) {
                    displayText += ` (${term.dueDays} hari)`;
                }
                // Handle old format for backward compatibility
                else if (term.netDays && term.netDays > 0) {
                    displayText += ` (${term.netDays} hari)`;
                }
                if (term.cashOnDelivery) {
                    displayText += ' - COD';
                }
                if (term.defaultTerm) {
                    displayText += ' - Default';
                }
                
                option.textContent = displayText;
                select.appendChild(option);
                
                console.log('Added payment term:', term.name, 'dueDays:', term.dueDays, 'netDays:', term.netDays); // Debug log
            });
            
            console.log('Populated', paymentTermsData.length, 'payment terms'); // Debug log
        }
        
        // Setup customer search event listener
        function setupCustomerSearch() {
            // Handle customer search
            document.getElementById('customerSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const dropdown = document.getElementById('customerDropdown');
                
                console.log('Search term:', searchTerm, 'Customers data length:', customersData.length); // Debug log
                
                if (searchTerm.length < 2) {
                    dropdown.style.display = 'none';
                    return;
                }
                
                // Check if customers data is available
                if (!customersData || customersData.length === 0) {
                    dropdown.innerHTML = '<div>Data konsumen belum dimuat atau kosong</div>';
                    dropdown.style.display = 'block';
                    return;
                }
                
                // Filter customers based on search term
                const filteredCustomers = customersData.filter(customer => 
                    customer.name && customer.name.toLowerCase().includes(searchTerm) ||
                    (customer.customerNo && customer.customerNo.toLowerCase().includes(searchTerm)) ||
                    (customer.email && customer.email.toLowerCase().includes(searchTerm))
                );
                
                console.log('Filtered customers:', filteredCustomers.length); // Debug log
                
                // Show dropdown results
                if (filteredCustomers.length > 0) {
                    dropdown.innerHTML = '';
                    filteredCustomers.slice(0, 10).forEach(customer => { // Limit to 10 results
                        const div = document.createElement('div');
                        div.style.padding = '8px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        
                        // Format price category info
                        let priceCategoryInfo = '';
                        if (customer.priceCategory && customer.priceCategory.name) {
                            priceCategoryInfo = ` | <span>Category: ${customer.priceCategory.name}</span>`;
                        } else {
                            priceCategoryInfo = ' | <span>Default Pricing</span>';
                        }
                        
                        div.innerHTML = `
                            <strong>${customer.name || 'N/A'}</strong><br>
                            <small>No: ${customer.customerNo || 'N/A'} | ${customer.email || 'No email'}${priceCategoryInfo}</small>
                        `;
                        
                        div.addEventListener('click', function() {
                            selectCustomer(customer);
                            dropdown.style.display = 'none';
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = '#f0f0f0';
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = 'white';
                        });
                        
                        dropdown.appendChild(div);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.innerHTML = '<div>Konsumen tidak ditemukan</div>';
                    dropdown.style.display = 'block';
                }
            });
        }
        
        // Setup customer event listeners
        function setupCustomerEventListeners() {
            setupCustomerSearch();
            
            // Handle customer select change
            document.getElementById('customerSelect').addEventListener('change', function() {
                const customerValue = this.value;
                console.log('Customer select changed:', customerValue); // Debug log
                
                if (customerValue) {
                    const customer = customersData.find(c => (c.customerNo || c.id) == customerValue);
                    if (customer) {
                        selectCustomer(customer);
                        document.getElementById('customerSearch').value = '';
                    }
                } else {
                    clearSelectedCustomer();
                }
            });
            
            // Handle branch select change
            document.getElementById('branchSelect').addEventListener('change', function() {
                const branchId = this.value;
                console.log('Branch select changed:', branchId); // Debug log
                
                if (branchId) {
                    const branch = branchesData.find(b => b.id == branchId);
                    if (branch) {
                        selectBranch(branch);
                    }
                } else {
                    clearSelectedBranch();
                }
            });
            
            // Handle payment term select change
            document.getElementById('paymentTermSelect').addEventListener('change', function() {
                const paymentTermName = this.value;
                console.log('Payment term select changed:', paymentTermName); // Debug log
                
                if (paymentTermName) {
                    const paymentTerm = paymentTermsData.find(t => t.name === paymentTermName);
                    if (paymentTerm) {
                        selectPaymentTerm(paymentTerm);
                    }
                } else {
                    clearSelectedPaymentTerm();
                }
            });
        }
        
        // Select customer function
        function selectCustomer(customer) {
            selectedCustomer = customer;
            
            // Update hidden field dengan customerNo
            document.getElementById('selectedCustomerNo').value = customer.customerNo || customer.id;
            
            // Update form controls
            document.getElementById('customerSelect').value = customer.customerNo || customer.id;
            document.getElementById('customerSearch').value = customer.name;
            
            // Hide dropdown
            document.getElementById('customerDropdown').style.display = 'none';
            
            // Simpan priceCategory dari customer untuk digunakan saat pilih item
            const previousPriceCategory = selectedCustomerPriceCategory;
            if (customer.priceCategory && customer.priceCategory.name) {
                selectedCustomerPriceCategory = customer.priceCategory.name;
                console.log('Customer price category:', selectedCustomerPriceCategory);
            } else {
                selectedCustomerPriceCategory = null;
                console.log('Customer has no price category, will use default pricing');
            }
            
            // Jika ada item di tabel, selalu update harga mereka ketika ganti customer (untuk testing)
            if (selectedItems.length > 0) {
                console.log('Customer changed, will update item prices. Previous category:', previousPriceCategory, 'New category:', selectedCustomerPriceCategory);
                console.log('Will update item prices for', selectedItems.length, 'items');
                updateItemPricesAfterCustomerChange();
            } else {
                console.log('No items to update prices for');
            }
            
            console.log('Customer selected:', customer); // Debug log
            console.log('Hidden field value:', document.getElementById('selectedCustomerNo').value); // Debug log
        }
        
        // Select branch function
        function selectBranch(branch) {
            selectedBranch = branch;
            
            // Update hidden field dengan branchId
            document.getElementById('selectedBranchId').value = branch.id;
            
            // Update form controls
            document.getElementById('branchSelect').value = branch.id;
            
            console.log('Branch selected:', branch); // Debug log
            console.log('Hidden field value:', document.getElementById('selectedBranchId').value); // Debug log
        }
        
        // Select payment term function
        function selectPaymentTerm(paymentTerm) {
            selectedPaymentTerm = paymentTerm;
            
            // Update hidden field dengan payment term name
            document.getElementById('selectedPaymentTerm').value = paymentTerm.name;
            
            // Update form controls
            document.getElementById('paymentTermSelect').value = paymentTerm.name;
            
            console.log('Payment term selected:', paymentTerm); // Debug log
            console.log('Hidden field value:', document.getElementById('selectedPaymentTerm').value); // Debug log
        }
        
        // Function untuk ganti customer
        function changeCustomer() {
            let confirmMessage = 'Yakin ingin ganti konsumen?';
            
            if (selectedItems.length > 0) {
                confirmMessage += `\n\nPerhatian: Ada ${selectedItems.length} item di tabel.`;
                confirmMessage += '\nHarga item akan disesuaikan dengan kategori harga konsumen baru.';
                confirmMessage += '\nData item (nama, quantity, discount) akan tetap tersimpan.';
            } else {
                confirmMessage += ' Data yang sudah diinput akan tetap tersimpan.';
            }
            
            if (confirm(confirmMessage)) {
                clearSelectedCustomer();
                
                // Jika ada item di tabel, update harga mereka setelah customer baru dipilih
                // (akan dipanggil otomatis di selectCustomer function)
            }
        }
        
        // Update harga semua item setelah ganti customer
        async function updateItemPricesAfterCustomerChange() {
            console.log('=== updateItemPricesAfterCustomerChange CALLED ===');
            console.log('Updating item prices for new customer with price category:', selectedCustomerPriceCategory);
            console.log('Number of items to update:', selectedItems.length);
            
            // Show loading indicator
            const loadingMessage = document.createElement('div');
            loadingMessage.id = 'priceUpdateLoading';
            loadingMessage.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 1000;';
            loadingMessage.innerHTML = 'Memperbarui harga item untuk konsumen baru...<br><small>Mohon tunggu...</small>';
            document.body.appendChild(loadingMessage);
            
            let updatedCount = 0;
            let errorCount = 0;
            
            try {
                // Update harga untuk setiap item di selectedItems
                for (let i = 0; i < selectedItems.length; i++) {
                    const item = selectedItems[i];
                    
                    try {
                        console.log(`Updating price for item ${i + 1}/${selectedItems.length}: ${item.itemNo}`);
                        
                        // Ambil harga baru berdasarkan priceCategory customer yang baru
                        const newPrice = await getItemPriceByCategory(item.itemNo);
                        console.log(`Got new price for ${item.itemNo}: ${newPrice}`);
                        
                        if (newPrice > 0) {
                            const oldPrice = item.unitPrice;
                            
                            // Update harga item
                            selectedItems[i].unitPrice = newPrice;
                            selectedItems[i].totalPrice = (newPrice - item.discount) * item.quantity;
                            selectedItems[i].priceUpdated = Date.now(); // Tambahkan timestamp untuk highlighting
                            
                            console.log(`Item ${item.itemNo} price updated: ${oldPrice} -> ${newPrice}`);
                            updatedCount++;
                        } else {
                            console.warn(`Failed to get new price for item ${item.itemNo}, keeping old price`);
                            // Untuk testing, tetap tandai sebagai updated
                            selectedItems[i].priceUpdated = Date.now();
                            errorCount++;
                        }
                    } catch (error) {
                        console.error(`Error updating price for item ${item.itemNo}:`, error);
                        errorCount++;
                    }
                }
                
                // Update table display
                console.log('Updating table display...');
                updateItemTable();
                updateSelectedItemsInput();
                
                // Remove loading indicator
                document.body.removeChild(loadingMessage);
                
                // Show result notification
                let message = `Harga item berhasil diperbarui!\n\n`;
                message += `Items diperbarui: ${updatedCount}\n`;
                if (errorCount > 0) {
                    message += `Items dengan error: ${errorCount}\n`;
                }
                message += `\nTotal items: ${selectedItems.length}`;
                
                if (selectedCustomerPriceCategory) {
                    message += `\nKategori harga baru: ${selectedCustomerPriceCategory}`;
                } else {
                    message += `\nMenggunakan harga default`;
                }
                
                console.log('=== UPDATE PRICES COMPLETED ===');
                console.log(message);
                
            } catch (error) {
                console.error('Error in updateItemPricesAfterCustomerChange:', error);
                
                // Remove loading indicator if still exists
                const loading = document.getElementById('priceUpdateLoading');
                if (loading) {
                    document.body.removeChild(loading);
                }
                
                console.log('Terjadi kesalahan saat memperbarui harga item. Silakan periksa console untuk detail error.');
            }
        }
        
        // Clear selected customer
        function clearSelectedCustomer() {
            selectedCustomer = null;
            selectedCustomerPriceCategory = null; // Clear price category juga
            document.getElementById('selectedCustomerNo').value = '';
            document.getElementById('customerSelect').value = '';
            document.getElementById('customerSearch').value = '';
            
            // Show selection area dan hide customer info (with null checks)
            const selectedCustomerInfo = document.getElementById('selectedCustomerInfo');
            const customerSelectionArea = document.getElementById('customerSelectionArea');
            
            if (selectedCustomerInfo) {
                selectedCustomerInfo.classList.add('hidden');
            }
            if (customerSelectionArea) {
                customerSelectionArea.classList.remove('hidden');
            }
            
            document.getElementById('customerDropdown').style.display = 'none';
        }
        
        // Clear selected branch
        function clearSelectedBranch() {
            selectedBranch = null;
            document.getElementById('selectedBranchId').value = '';
            document.getElementById('branchSelect').value = '';
        }
        
        // Clear selected payment term
        function clearSelectedPaymentTerm() {
            selectedPaymentTerm = null;
            document.getElementById('selectedPaymentTerm').value = '';
            document.getElementById('paymentTermSelect').value = '';
        }
        
        // Clear tax settings
        function clearTaxSettings() {
            selectedTaxable = false;
            selectedInclusiveTax = false;
            document.getElementById('selectedTaxable').value = 'false';
            document.getElementById('selectedInclusiveTax').value = 'false';
            document.getElementById('taxableCheckbox').checked = false;
            document.getElementById('inclusiveTaxCheckbox').checked = false;
        }
        
        // Clear address and description
        function clearAddressAndDescription() {
            selectedToAddress = '';
            selectedDescription = '';
            document.getElementById('selectedToAddress').value = '';
            document.getElementById('selectedDescription').value = '';
            document.getElementById('toAddress').value = '';
            document.getElementById('description').value = '';
        }
        
        // Clear selected items
        function clearSelectedItems() {
            selectedItems = [];
            itemCounter = 0;
            document.getElementById('selectedItems').value = '';
            document.getElementById('itemSearch').value = '';
            updateItemTable();
            document.getElementById('itemDropdown').style.display = 'none';
        }
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('customerDropdown');
            const searchInput = document.getElementById('customerSearch');
            const itemDropdown = document.getElementById('itemDropdown');
            const itemSearchInput = document.getElementById('itemSearch');
            
            if (!dropdown.contains(event.target) && event.target !== searchInput) {
                dropdown.style.display = 'none';
            }
            
            if (!itemDropdown.contains(event.target) && event.target !== itemSearchInput) {
                itemDropdown.style.display = 'none';
            }
        });
        
        
        // Validate and proceed to next step
        function validateAndNext() {
            console.log('validateAndNext called'); // Debug log
            
            // Validasi customer
            if (!selectedCustomer || !document.getElementById('selectedCustomerNo').value) {
                console.log('Silakan pilih konsumen terlebih dahulu');
                return;
            }
            console.log('Customer validation passed:', selectedCustomer); // Debug log
            
            // Validasi branch
            if (!selectedBranch || !document.getElementById('selectedBranchId').value) {
                alert('Silakan pilih cabang terlebih dahulu');
                return;
            }
            console.log('Branch validation passed:', selectedBranch); // Debug log
            
            // Validasi payment term
            if (!selectedPaymentTerm || !document.getElementById('selectedPaymentTerm').value) {
                alert('Silakan pilih syarat pembayaran terlebih dahulu');
                return;
            }
            console.log('Payment term validation passed:', selectedPaymentTerm); // Debug log
            
            // Validasi tanggal transaksi
            const transactionDate = document.getElementById('transactionDate').value;
            if (!transactionDate) {
                alert('Tanggal transaksi harus diisi');
                return;
            }
            console.log('Transaction date validation passed:', transactionDate); // Debug log
            
            // Validasi tanggal pengiriman tidak boleh sebelum tanggal transaksi (optional)
            const deliveryDate = document.getElementById('deliveryDate').value;
            if (deliveryDate && deliveryDate < transactionDate) {
                alert('Tanggal pengiriman tidak boleh sebelum tanggal transaksi');
                return;
            }
            console.log('Delivery date validation passed:', deliveryDate); // Debug log
            
            console.log('All validations passed, proceeding to item section'); // Debug log
            
            alert('Validasi berhasil! Siap untuk lanjut ke input item.\n\n' +
                  'Customer: ' + selectedCustomer.name + '\n' +
                  'Customer No: ' + (selectedCustomer.customerNo || selectedCustomer.id) + '\n' +
                  'Cabang: ' + (selectedBranch.name || selectedBranch.branchName || 'Unknown') + '\n' +
                  'Branch ID: ' + selectedBranch.id + '\n' +
                  'Syarat Pembayaran: ' + selectedPaymentTerm.name + '\n' +
                  'Payment Term Name: ' + selectedPaymentTerm.name + '\n' +
                  'Pajak (taxable): ' + selectedTaxable + '\n' +
                  'Inclusive Tax (inclusiveTax): ' + selectedInclusiveTax + '\n' +
                  'Alamat (toAddress): ' + (selectedToAddress || 'Tidak diisi') + '\n' +
                  'Keterangan (description): ' + (selectedDescription || 'Tidak diisi') + '\n' +
                  'Tanggal Transaksi (transDate): ' + formatDateForStore(transactionDate) + '\n' +
                  'Tanggal Pengiriman (shipDate): ' + (deliveryDate ? formatDateForStore(deliveryDate) : 'Tidak diset'));
            
            // Show item section 
            console.log('Showing item section...'); // Debug log
            document.getElementById('itemSection').style.display = 'block';
            
            console.log('Item section displayed, scrolling...'); // Debug log
            // Scroll ke item section
            document.getElementById('itemSection').scrollIntoView({ behavior: 'smooth' });
            
            console.log('validateAndNext completed successfully'); // Debug log
        }
        
        // Submit order (final submission)
        function submitOrder() {
            console.log('submitOrder() started');
            
            // Validasi customer
            if (!selectedCustomer) {
                alert(' Customer belum dipilih!\n\nSilakan pilih customer terlebih dahulu.');
                return;
            }
            
            // Validasi branch
            if (!selectedBranch) {
                alert(' Branch belum dipilih!\n\nSilakan pilih branch terlebih dahulu.');
                return;
            }
            
            // Validasi items
            if (selectedItems.length === 0) {
                alert(' Belum ada item!\n\nSilakan tambahkan minimal satu item ke Sales Order');
                return;
            }
            
            console.log('Validation passed, generating post data...');
            
            // Generate data yang akan di-POST
            const postData = generatePostData();
            
            // Final validation untuk required fields
            if (!postData.customerNo) {
                alert('Customer No kosong!\n\nAda masalah dengan data customer yang dipilih.');
                console.error('Selected customer:', selectedCustomer);
                return;
            }
            
            if (!postData.branchId) {
                alert('Branch ID kosong!\n\nAda masalah dengan data branch yang dipilih.');
                console.error('Selected branch:', selectedBranch);
                return;
            }
            
            console.log('Final validation passed, preparing to submit...');
            
            // Calculate total untuk display
            const totalAmount = selectedItems.reduce((sum, item) => sum + item.totalPrice, 0);
            
            // Log data yang akan dikirim ke console untuk debugging
            console.log('=== HEADER DATA ===');
            console.log(`customerNo: ${postData.customerNo}`);
            console.log(`branchId: ${postData.branchId}`);
            console.log(`paymentTerm: ${postData.paymentTerm}`);
            console.log(`taxable: ${postData.taxable}`);
            console.log(`inclusiveTax: ${postData.inclusiveTax}`);
            console.log(`toAddress: ${postData.toAddress}`);
            console.log(`description: ${postData.description}`);
            console.log(`transDate: ${postData.transDate}`);
            console.log(`shipDate: ${postData.shipDate}`);
            
            console.log('=== DETAIL ITEMS ===');
            selectedItems.forEach((item, index) => {
                console.log(`detailItem[${index}].itemNo: ${postData[`detailItem[${index}].itemNo`]}`);
                console.log(`detailItem[${index}].unitPrice: ${postData[`detailItem[${index}].unitPrice`]}`);
                console.log(`detailItem[${index}].quantity: ${postData[`detailItem[${index}].quantity`]}`);
                console.log(`detailItem[${index}].itemCashDiscount: ${postData[`detailItem[${index}].itemCashDiscount`]}`);
            });
            
            console.log(`TOTAL ITEMS: ${selectedItems.length}`);
            console.log(`ESTIMATED TOTAL: ${formatCurrency(totalAmount)}`);
            
            // Langsung kirim ke API
            saveSalesOrderToAccurate(postData);
        }
        
        // Function untuk mengirim data ke Accurate API
        async function saveSalesOrderToAccurate(postData) {
            // Show loading indicator
            const loadingMessage = document.createElement('div');
            loadingMessage.id = 'saveOrderLoading';
            loadingMessage.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); color: white; padding: 30px; border-radius: 10px; z-index: 1000; text-align: center;';
            loadingMessage.innerHTML = `
                <div>Menyimpan Sales Order...</div>
                <div>Mengirim data ke Accurate API</div>
                <div>
                    <div style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 10px auto;"></div>
                </div>
            `;
            
            // Add CSS animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            document.body.appendChild(loadingMessage);
            
            try {
                console.log('Sending data to Accurate API:', postData);
                console.log('Selected items:', selectedItems);
                console.log('Detail items in postData:');
                Object.keys(postData).forEach(key => {
                    if (key.includes('detailItem')) {
                        console.log(`${key}: ${postData[key]}`);
                    }
                });
                
                // Prepare x-www-form-urlencoded data (MANUAL string building untuk format yang benar!)
                // JANGAN gunakan URLSearchParams karena bisa salah format untuk detailItem[0].itemNo
                
                let urlEncodedString = '';
                const pairs = [];
                
                Object.keys(postData).forEach(key => {
                    const encodedKey = encodeURIComponent(key);
                    const encodedValue = encodeURIComponent(postData[key]);
                    pairs.push(`${encodedKey}=${encodedValue}`);
                    console.log(`Adding to manual string: ${key} = ${postData[key]}`);
                });
                
                urlEncodedString = pairs.join('&');
                console.log('Manual URL encoded string:', urlEncodedString);
                
                // Make API call to save_final.php yang sudah terbukti berhasil
                const response = await fetch('../salesorder/save_final.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded', // Header yang BENAR
                        'X-Session-ID': await getSessionId()
                    },
                    body: urlEncodedString // x-www-form-urlencoded body manual
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);
                
                // Get response text first untuk debug
                const responseText = await response.text();
                console.log('Raw response text:', responseText);
                
                // Remove loading indicator before processing response
                try {
                    if (loadingMessage && loadingMessage.parentNode) {
                        loadingMessage.parentNode.removeChild(loadingMessage);
                    }
                    if (style && style.parentNode) {
                        style.parentNode.removeChild(style);
                    }
                } catch (e) {
                    console.warn('Could not remove loading elements:', e);
                }
                
                // Check if response is HTML (error page) instead of JSON
                if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                    console.error('Server returned HTML instead of JSON');
                    throw new Error(`Server Error!\n\nServer mengembalikan halaman error HTML alih-alih JSON.\n\nKemungkinan penyebab:\n1. PHP Error di save_final.php\n2. Session tidak valid\n3. Masalah database\n\nSilakan cek log server atau hubungi administrator.\n\nResponse preview: ${responseText.substring(0, 200)}...`);
                }
                
                let responseData;
                try {
                    responseData = JSON.parse(responseText);
                    console.log('Parsed response data:', responseData);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text yang gagal di-parse:', responseText);
                    
                    throw new Error(`Invalid JSON Response!\n\nServer mengembalikan response yang bukan JSON valid.\n\nResponse: ${responseText.substring(0, 200)}${responseText.length > 200 ? '...' : ''}\n\nParse Error: ${parseError.message}`);
                }
                
                if (response.ok && responseData.success) {
                    // Success - log to console and redirect
                    console.log('âœ… Sales Order berhasil disimpan!');
                    console.log(`Sales Order ID: ${responseData.data.id || 'N/A'}`);
                    console.log(`Number: ${responseData.data.number || 'N/A'}`);
                    console.log(`Total Amount: ${formatCurrency(selectedItems.reduce((sum, item) => sum + item.totalPrice, 0))}`);
                    
                    // Redirect ke index.php tanpa alert
                    window.location.href = 'index.php';
                } else {
                    // Error dari API
                    let errorMessage = 'Gagal menyimpan Sales Order\n\n';
                    errorMessage += `Error: ${responseData.message || 'Unknown error'}\n`;
                    errorMessage += `Status: ${response.status}\n\n`;
                    
                    if (responseData.errors && Array.isArray(responseData.errors)) {
                        errorMessage += 'Detail errors:\n';
                        responseData.errors.forEach((error, index) => {
                            errorMessage += `${index + 1}. ${error}\n`;
                        });
                    }
                    
                    console.error(errorMessage);
                    alert(errorMessage);
                }
                
            } catch (error) {
                console.error('Error saving to Accurate:', error);
                
                // Remove loading indicator if still exists
                const loading = document.getElementById('saveOrderLoading');
                if (loading) {
                    try {
                        if (loading.parentNode) {
                            loading.parentNode.removeChild(loading);
                        }
                    } catch (e) {
                        console.warn('Could not remove loading indicator:', e);
                    }
                }
                
                // Remove style if still exists
                const styles = document.querySelectorAll('style');
                styles.forEach(styleEl => {
                    if (styleEl && styleEl.textContent && styleEl.textContent.includes('@keyframes spin')) {
                        try {
                            if (styleEl.parentNode) {
                                styleEl.parentNode.removeChild(styleEl);
                            }
                        } catch (e) {
                            console.warn('Could not remove style element:', e);
                        }
                    }
                });
                
                alert(`Terjadi kesalahan saat menyimpan:\n\n${error.message}\n\nSilakan coba lagi atau hubungi administrator.`);
            }
        }
        
        // Function untuk mendapatkan Session ID
        async function getSessionId() {
            try {
                // Get session ID from the new endpoint
                const response = await fetch('../get_session_id.php');
                const data = await response.json();
                
                if (data.success && data.sessionId) {
                    return data.sessionId;
                } else {
                    throw new Error(data.message || 'Session ID not available');
                }
            } catch (error) {
                console.error('Error getting session ID:', error);
                throw new Error('Cannot get session ID for API call: ' + error.message);
            }
        }
        
        // Format date untuk disimpan ke API (dd/mm/yyyy)
        function formatDateForStore(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
        
        // Reset form
        function resetForm() {
            document.getElementById('formSalesOrder').reset();
            clearSelectedCustomer();
            clearSelectedBranch();
            clearSelectedPaymentTerm();
            clearTaxSettings();
            clearAddressAndDescription();
            clearSelectedItems();
            setDefaultDate();
            document.getElementById('customerDropdown').style.display = 'none';
            
            // Reset tampilan section
            document.getElementById('itemSection').style.display = 'none';
        }
    </script>
</body>
</html>
