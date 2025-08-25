<?php
/**
 * Form Input Serial Number untuk Item
 * File: /salesinvoice/input_serial.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Ambil parameter dari GET
$item_id = $_GET['item_id'] ?? '';
$item_name = $_GET['item_name'] ?? '';
$item_code = $_GET['item_code'] ?? '';
$quantity = $_GET['quantity'] ?? 1;
$so_id = $_GET['so_id'] ?? '';

// Handle POST request untuk save serial numbers
if ($_POST) {
    $serial_numbers = $_POST['serial_numbers'] ?? [];
    
    // Di sini Anda bisa save ke database atau session
    // Untuk sementara, kita simpan ke session
    if (!isset($_SESSION['item_serials'])) {
        $_SESSION['item_serials'] = [];
    }
    
    $_SESSION['item_serials'][$item_id] = $serial_numbers;
    
    // Redirect kembali ke new_invoice dengan success message
    header("Location: new_invoice.php?so_id=$so_id&success=1&item_saved=$item_id");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Serial Number - Nuansa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .serial-input {
            margin-bottom: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .item-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Input Serial Number</h1>
        
        <div class="item-info">
            <h3>Informasi Item</h3>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                <tr>
                    <td><strong>Item ID:</strong></td>
                    <td><?php echo htmlspecialchars($item_id); ?></td>
                </tr>
                <tr>
                    <td><strong>Kode Barang:</strong></td>
                    <td><?php echo htmlspecialchars($item_code); ?></td>
                </tr>
                <tr>
                    <td><strong>Nama Barang:</strong></td>
                    <td><?php echo htmlspecialchars($item_name); ?></td>
                </tr>
                <tr>
                    <td><strong>Quantity:</strong></td>
                    <td><?php echo htmlspecialchars($quantity); ?> unit</td>
                </tr>
            </table>
        </div>
        
        <form method="POST">
            <h3>Input Serial Numbers</h3>
            <p>Masukkan serial number untuk setiap unit item (<?php echo $quantity; ?> unit):</p>
            
            <div id="serial-container">
                <?php for ($i = 1; $i <= $quantity; $i++): ?>
                    <div class="serial-input">
                        <label for="serial_<?php echo $i; ?>">Serial Number <?php echo $i; ?>:</label>
                        <input type="text" 
                               id="serial_<?php echo $i; ?>" 
                               name="serial_numbers[]" 
                               placeholder="Masukkan serial number ke-<?php echo $i; ?>" 
                               required>
                    </div>
                <?php endfor; ?>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">Simpan Serial Numbers</button>
                <a href="new_invoice.php?so_id=<?php echo urlencode($so_id); ?>&load_details=1" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 4px;">
            <h4>Petunjuk:</h4>
            <ul>
                <li>Masukkan serial number yang unik untuk setiap unit</li>
                <li>Serial number tidak boleh kosong</li>
                <li>Pastikan serial number sesuai dengan fisik barang</li>
                <li>Setelah disimpan, data akan kembali ke halaman invoice</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto focus ke input pertama
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input[name="serial_numbers[]"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Validasi untuk memastikan tidak ada serial number yang sama
        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('input[name="serial_numbers[]"]');
            const values = [];
            let hasDuplicate = false;
            
            inputs.forEach(function(input) {
                if (input.value && values.includes(input.value)) {
                    hasDuplicate = true;
                }
                if (input.value) {
                    values.push(input.value);
                }
            });
            
            if (hasDuplicate) {
                alert('Error: Ditemukan serial number yang sama! Setiap serial number harus unik.');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
