<?php
require_once __DIR__ . '/../bootstrap.php';

$api = new AccurateAPI();
$transferId = $_GET['id'] ?? null;

if (!$transferId) {
    header('Location: index.php');
    exit;
}

$result = $api->getItemTransferDetail($transferId);
$transfer = null;

if ($result['success'] && isset($result['data']['d'])) {
    $transfer = $result['data']['d'];
}

// Helper untuk mengambil nilai dengan aman dari array
function get_value($array, $key, $default = 'N/A') {
    if (is_array($array) && isset($array[$key])) {
        return htmlspecialchars($array[$key]);
    }
    return htmlspecialchars($default);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Item Transfer - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exchange-alt text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Item Transfer</h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($transfer): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-file-invoice text-blue-600 mr-3 text-lg"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Informasi Umum</h2>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-hashtag text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'number'); ?></span>
                                </div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'transDateView'); ?></span>
                                </div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-store text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'branchId'); ?></span>
                                </div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-info-circle text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'statusName'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gudang Asal</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-warehouse text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'warehouseName'); ?></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gudang Tujuan</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-warehouse text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'referenceWarehouseName'); ?></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transfer</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-tags text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'itemTransferType'); ?></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Penerimaan</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <i class="fas fa-check-circle text-gray-400 mr-3"></i>
                                    <span class="text-gray-900"><?php echo get_value($transfer, 'itemTransferOutStatus'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-box-open text-blue-600 mr-3 text-lg"></i>
                            <h2 class="text-xl font-semibold text-gray-900">Rincian Barang</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Diterima</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">No. Seri</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($transfer['detailItem'])): ?>
                                    <?php foreach ($transfer['detailItem'] as $index => $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo get_value($item['item'], 'no'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo get_value($item['item'], 'name'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo get_value($item, 'itemCategory'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo get_value($item['item']['unit1'], 'name'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"><?php echo get_value($item, 'quantity'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"><?php echo get_value($item, 'receivedQuantity'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                                <?php if (!empty($item['detailSerialNumber'])): ?>
                                                    <button data-modal-target="serial-modal-<?php echo $index; ?>" class="text-blue-600 hover:text-blue-900">
                                                        Detail
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-gray-500">Tidak ada rincian barang.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modals for Serial Numbers -->
            <?php if (!empty($transfer['detailItem'])): ?>
                <?php foreach ($transfer['detailItem'] as $index => $item): ?>
                    <?php if (!empty($item['detailSerialNumber'])): ?>
                        <div id="serial-modal-<?php echo $index; ?>" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
                            <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
                                <div class="px-6 py-4 border-b flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Nomor Seri untuk: <?php echo get_value($item['item'], 'name'); ?></h3>
                                    <button data-modal-close class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
                                </div>
                                <div class="p-6 max-h-80 overflow-y-auto">
                                    <table class="min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No.</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nomor Seri</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            <?php foreach ($item['detailSerialNumber'] as $serialIndex => $serial): ?>
                                                <tr>
                                                    <td class="px-4 py-2"><?php echo $serialIndex + 1; ?></td>
                                                    <td class="px-4 py-2 font-mono"><?php echo get_value($serial['serialNumber'], 'number'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Item Transfer Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-4">Data dengan ID <?php echo htmlspecialchars($transferId); ?> tidak ditemukan.</p>
                    <?php if (isset($result['error'])): ?>
                        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openModalButtons = document.querySelectorAll('[data-modal-target]');
            
            openModalButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const modalId = button.getAttribute('data-modal-target');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden'; // Prevent background scrolling
                    }
                });
            });

            const closeModal = (modal) => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            };

            document.querySelectorAll('.modal-overlay').forEach(modal => {
                // Close with button
                modal.querySelector('[data-modal-close]').addEventListener('click', () => {
                    closeModal(modal);
                });

                // Close by clicking overlay
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            // Close with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === "Escape") {
                    document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(closeModal);
                }
            });
        });
    </script>

</body>
</html>