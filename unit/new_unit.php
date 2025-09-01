<?php
require_once __DIR__ . '/../bootstrap.php';

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Tambah Satuan Baru";
$message = '';
$message_type = ''; // 'success' or 'error'

// Cek jika form disubmit (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;

    if (empty($name)) {
        $message = 'Nama satuan tidak boleh kosong.';
        $message_type = 'error';
    } else {
        try {
            $api = new AccurateAPI();
            $unitData = ['name' => $name];
            $result = $api->saveUnit($unitData);

            if ($result['success']) {
                $newUnitName = $result['data']['d']['name'] ?? $name;
                // Simpan pesan sukses di session dan redirect
                $_SESSION['flash_message'] = "Satuan '" . htmlspecialchars($newUnitName) . "' berhasil disimpan.";
                $_SESSION['flash_message_type'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $error_message = $result['error'] ?? 'Gagal menyimpan satuan.';
                $message = "Error: " . htmlspecialchars($error_message);
                $message_type = 'error';
            }
        } catch (Exception $e) {
            logError("Exception in new_unit.php: " . $e->getMessage(), __FILE__, $e->getLine());
            $message = 'Terjadi kesalahan internal server.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Nuansa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-plus-circle text-blue-600 mr-3 text-2xl"></i>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow p-8">
            <h2 class="text-2xl font-semibold text-center mb-6">Formulir Satuan Baru</h2>

            <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="new_unit.php" method="POST">
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Satuan</label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Pcs, Box, Lusin" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                        <i class="fas fa-save mr-2"></i>Simpan Satuan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>