<?php
/**
 * Konfigurasi terpusat untuk aplikasi Accurate API
 * File ini berisi semua konfigurasi yang dibutuhkan aplikasi
 */

// Konfigurasi OAuth
define('OAUTH_CLIENT_ID', 'c0f130ce-1e13-42a8-97a5-714d8e492b08');
define('OAUTH_CLIENT_SECRET', 'cc001a5f04678825fec7a52d553944a6');
define('OAUTH_REDIRECT_URI', 'https://rbjjv-36-83-62-101.a.free.pinggy.link/nuansa/callback.php');

// Konfigurasi API Accurate
define('ACCURATE_API_HOST', 'https://zeus.accurate.id');
define('ACCURATE_AUTH_HOST', 'https://account.accurate.id');
define('ACCURATE_ACCESS_TOKEN', '0e95218c-b98f-45fd-894d-e2038d705a9e');
define('ACCURATE_TOKEN_SCOPE', 'unit_save branch_view unit_delete tax_view fixed_asset_view item_transfer_view access_privilege_view sales_order_save employee_view purchase_invoice_view item_category_view sales_invoice_view warehouse_view sellingprice_adjustment_view unit_view stock_mutation_history_view customer_view vendor_category_view sales_order_view sales_receipt_view shipment_view payment_term_view purchase_order_view item_transfer_save sellingprice_adjustment_save item_view vendor_view sales_invoice_save item_save price_category_view');
define('ACCURATE_REFRESH_TOKEN', 'f311a524-0000-4613-83d7-1c174f17e370');
define('ACCURATE_SESSION_ID', '6d5cd7c5-092b-497c-b29a-d9105b1f7173');
define('ACCURATE_DATABASE_ID', '1963082');

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
