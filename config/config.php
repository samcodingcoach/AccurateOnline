<?php
/**
 * Konfigurasi terpusat untuk aplikasi Accurate API
 * File ini berisi semua konfigurasi yang dibutuhkan aplikasi
 */

// Konfigurasi OAuth
define('OAUTH_CLIENT_ID', 'c0f130ce-1e13-42a8-97a5-714d8e492b08');
define('OAUTH_CLIENT_SECRET', 'cc001a5f04678825fec7a52d553944a6');
define('OAUTH_REDIRECT_URI', 'https://kxywm-36-83-62-101.a.free.pinggy.link/nuansa/callback.php');

// Konfigurasi API Accurate
define('ACCURATE_API_HOST', 'https://zeus.accurate.id');
define('ACCURATE_AUTH_HOST', 'https://account.accurate.id');
define('ACCURATE_ACCESS_TOKEN', 'b2ccac16-852d-4bcc-8e29-0dbc4da99655');
define('ACCURATE_TOKEN_SCOPE', 'branch_view fixed_asset_view item_transfer_view access_privilege_view employee_view sales_order_save purchase_invoice_view item_category_view sales_invoice_view warehouse_view sellingprice_adjustment_view stock_mutation_history_view customer_view sales_order_view sales_receipt_view payment_term_view shipment_view item_transfer_save purchase_order_view sellingprice_adjustment_save item_view vendor_view sales_invoice_save item_save price_category_view');
define('ACCURATE_REFRESH_TOKEN', 'a01f4b02-1881-4801-ac85-43d3e37ad189');
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
