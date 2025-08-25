<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Barang - Modal View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Modal backdrop */
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        
        /* Loading animation */
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-boxes text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">List Barang</h1>
                </div>
                <div class="flex gap-4">
                    <button id="openModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>Lihat Daftar Barang
                    </button>
                    <a href="../index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Data Barang dari API</h2>
            <p class="text-gray-600 mb-4">Klik tombol "Lihat Daftar Barang" untuk melihat data barang dalam modal.</p>
            
            <!-- API Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">API Endpoint:</span>
                </div>
                <p class="text-blue-700 mt-1 font-mono text-sm">
                    https://227ca3e8f358.ngrok-free.app/nuansa/item/api_listbarang.php
                </p>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-boxes text-blue-600 mr-3 text-lg"></i>
                        <h3 class="text-xl font-semibold text-gray-900">Daftar Barang</h3>
                    </div>
                    <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-y-auto scrollbar-thin" style="max-height: 70vh;">
                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-8">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-600">Memuat data barang...</p>
                    </div>
                    
                    <!-- Error State -->
                    <div id="errorState" class="hidden text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h4>
                        <p id="errorMessage" class="text-gray-600 mb-4"></p>
                        <button id="retryBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-retry mr-2"></i>Coba Lagi
                        </button>
                    </div>
                    
                    <!-- Data Table -->
                    <div id="dataTable" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="itemTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6 flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500">
                                <span>Menampilkan <span id="itemCount">0</span> barang</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button id="prevBtn" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="pageInfo" class="text-sm text-gray-600">Halaman 1</span>
                                <button id="nextBtn" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors" disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data dari API Accurate
                    </div>
                    <button id="closeModalBtn2" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal elements
        const modal = document.getElementById('itemModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalBtn2 = document.getElementById('closeModalBtn2');
        const retryBtn = document.getElementById('retryBtn');
        
        // State elements
        const loadingState = document.getElementById('loadingState');
        const errorState = document.getElementById('errorState');
        const dataTable = document.getElementById('dataTable');
        const errorMessage = document.getElementById('errorMessage');
        const itemTableBody = document.getElementById('itemTableBody');
        const itemCount = document.getElementById('itemCount');
        const pageInfo = document.getElementById('pageInfo');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        // Current page state
        let currentPage = 1;
        let totalItems = 0;
        let itemsPerPage = 20;
        
        // Open modal
        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            loadItems();
        });
        
        // Close modal
        closeModalBtn.addEventListener('click', closeModal);
        closeModalBtn2.addEventListener('click', closeModal);
        
        // Close modal when clicking backdrop
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Retry loading
        retryBtn.addEventListener('click', loadItems);
        
        // Pagination
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadItems();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            currentPage++;
            loadItems();
        });
        
        function closeModal() {
            modal.classList.add('hidden');
            resetModal();
        }
        
        function resetModal() {
            loadingState.classList.add('hidden');
            errorState.classList.add('hidden');
            dataTable.classList.add('hidden');
            currentPage = 1;
        }
        
        function showLoading() {
            loadingState.classList.remove('hidden');
            errorState.classList.add('hidden');
            dataTable.classList.add('hidden');
        }
        
        function showError(message) {
            loadingState.classList.add('hidden');
            errorState.classList.remove('hidden');
            dataTable.classList.add('hidden');
            errorMessage.textContent = message;
        }
        
        function showData() {
            loadingState.classList.add('hidden');
            errorState.classList.add('hidden');
            dataTable.classList.remove('hidden');
        }
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }
        
        function loadItems() {
            showLoading();
            
            const url = `api_listbarang.php?limit=${itemsPerPage}&page=${currentPage}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayItems(data.data);
                        showData();
                    } else {
                        showError(data.message || 'Gagal memuat data barang');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Terjadi kesalahan saat memuat data');
                });
        }
        
        function displayItems(data) {
            // Clear existing data
            itemTableBody.innerHTML = '';
            
            // Check if data has items
            const items = data.d || data.items || data || [];
            
            if (items.length === 0) {
                itemTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data barang
                        </td>
                    </tr>
                `;
                return;
            }
            
            // Populate table
            items.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${item.id || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <i class="fas fa-box text-gray-400 mr-2"></i>
                            <span class="text-sm font-medium text-gray-900">${item.name || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${item.no || item.code || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${item.unitName || item.unit || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${item.unitPrice ? formatCurrency(item.unitPrice) : 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${item.suspended === false ? 
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Aktif</span>' : 
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Suspended</span>'
                        }
                    </td>
                `;
                
                itemTableBody.appendChild(row);
            });
            
            // Update counters
            totalItems = items.length;
            itemCount.textContent = totalItems;
            pageInfo.textContent = `Halaman ${currentPage}`;
            
            // Update pagination buttons
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = items.length < itemsPerPage;
            
            // Update button styles
            prevBtn.className = currentPage <= 1 ? 
                'px-3 py-1 bg-gray-200 text-gray-400 rounded text-sm cursor-not-allowed' : 
                'px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors';
                
            nextBtn.className = items.length < itemsPerPage ? 
                'px-3 py-1 bg-gray-200 text-gray-400 rounded text-sm cursor-not-allowed' : 
                'px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors';
        }
        
        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    </script>
</body>
</html>
