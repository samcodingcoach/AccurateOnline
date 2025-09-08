<?php
/**
 * Konfigurasi terpusat untuk aplikasi Accurate API
 * File ini berisi semua konfigurasi yang dibutuhkan aplikasi
 */

// Konfigurasi OAuth
define('OAUTH_CLIENT_ID', 'c0f130ce-1e13-42a8-97a5-714d8e492b08');
define('OAUTH_CLIENT_SECRET', 'cc001a5f04678825fec7a52d553944a6');
define('OAUTH_REDIRECT_URI', 'https://owbxi-110-139-174-5.a.free.pinggy.link/nuansa/callback.php');

// Konfigurasi API Accurate
define('ACCURATE_API_HOST', 'https://zeus.accurate.id');
define('ACCURATE_AUTH_HOST', 'https://account.accurate.id');
define('ACCURATE_ACCESS_TOKEN', '68d28a21-31f5-4454-9d1b-91473dd7f20a');
define('ACCURATE_TOKEN_SCOPE', 'unit_save branch_view unit_delete fixed_asset_view tax_view item_transfer_view access_privilege_view employee_view sales_order_save glaccount_view purchase_invoice_view item_category_view sales_invoice_view warehouse_view sellingprice_adjustment_view stock_mutation_history_view unit_view customer_view vendor_category_view sales_order_view sales_receipt_view payment_term_view shipment_view item_transfer_save purchase_order_view sellingprice_adjustment_save item_view vendor_view sales_invoice_save item_save price_category_view');
define('ACCURATE_REFRESH_TOKEN', '0c2e719d-1be7-43e1-964f-43d9f47d33ac');
define('ACCURATE_SESSION_ID', '9ec49c80-f542-409b-9160-045b37e270ad');
define('ACCURATE_DATABASE_ID', '2028904');

// Konfigurasi aplikasi
define('APP_NAME', 'Nuansa Accurate API');
define('APP_VERSION', '1.0.0');
define('DEFAULT_TIMEZONE', 'Asia/Jakarta');

// Set timezone default
date_default_timezone_set(DEFAULT_TIMEZONE);

// Konfigurasi error reporting untuk development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
