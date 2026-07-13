<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'authcheck']);
$routes->get('/sales_invoice_view/(:num)/(:any)', 'Home::si_receipt/$1/$2', ['filter' => 'authcheck']);
$routes->get('/delivery_receipt_view/(:num)/(:any)', 'Home::dr_receipt/$1/$2', ['filter' => 'authcheck']);

$routes->get('/login', 'Login::index');
$routes->post('/login/authenticate', 'Login::authenticate');
$routes->post('/login/logout', 'Login::logout');

$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'authcheck']);
$routes->post('/dashboard/get_dashboard_data', 'DashboardController::get_dashboard_data', ['filter' => 'authcheck']);
$routes->post('/dashboard/export_accounting_total', 'DashboardController::export_accounting_total', ['filter' => 'authcheck']);

$routes->get('/accounting', 'AccountingController::index', ['filter' => 'authcheck']);
$routes->post('/accounting/get_filters', 'AccountingController::get_filters', ['filter' => 'authcheck']);
$routes->post('/accounting/get_si_data_items_accounting', 'AccountingController::get_si_data_items_accounting', ['filter' => 'authcheck']);
$routes->post('/accounting/get_dr_data_items_accounting', 'AccountingController::get_dr_data_items_accounting', ['filter' => 'authcheck']);
$routes->post('/accounting/dynamic_change_client_show', 'AccountingController::dynamic_change_client_show', ['filter' => 'authcheck']);
$routes->post('/accounting/dynamic_change_product_show', 'AccountingController::dynamic_change_product_show', ['filter' => 'authcheck']);
$routes->post('/accounting/get_si_volume', 'AccountingController::get_si_volume', ['filter' => 'authcheck']);
$routes->post('/accounting/get_dr_volume', 'AccountingController::get_dr_volume', ['filter' => 'authcheck']);
$routes->post('/accounting/get_si_dr_volume', 'AccountingController::get_si_dr_volume', ['filter' => 'authcheck']);

$routes->get('/sales_invoice', 'SalesInvoice::index', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/get_products_clients_si', 'SalesInvoice::get_products_clients_si', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/save_draft', 'SalesInvoice::save_draft', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/print_invoice', 'SalesInvoice::print_invoice', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/get_sales_invoice_by_id', 'SalesInvoice::get_sales_invoice_by_id', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/update_draft', 'SalesInvoice::update_draft', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/get_si_receipt_by_id', 'SalesInvoice::get_si_receipt_by_id', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/print_si_receipt', 'SalesInvoice::print_si_receipt', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/draft_si_receipt', 'SalesInvoice::draft_si_receipt', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/cancel_si_receipt', 'SalesInvoice::cancel_si_receipt', ['filter' => 'authcheck']);
$routes->post('/sales_invoice/authenticate_user', 'SalesInvoice::authenticate_user', ['filter' => 'authcheck']);

$routes->get('/delivery_receipt', 'DeliveryReceiptController::index', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/get_products_clients_dr', 'DeliveryReceiptController::get_products_clients_dr', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/save_draft', 'DeliveryReceiptController::save_draft', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/print_delivery', 'DeliveryReceiptController::print_delivery', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/get_delivery_receipt_by_id', 'DeliveryReceiptController::get_delivery_receipt_by_id', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/update_draft', 'DeliveryReceiptController::update_draft', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/get_dr_receipt_by_id', 'DeliveryReceiptController::get_dr_receipt_by_id', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/print_dr_receipt', 'DeliveryReceiptController::print_dr_receipt', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/draft_dr_receipt', 'DeliveryReceiptController::draft_dr_receipt', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/cancel_dr_receipt', 'DeliveryReceiptController::cancel_dr_receipt', ['filter' => 'authcheck']);
$routes->post('/delivery_receipt/authenticate_user', 'DeliveryReceiptController::authenticate_user', ['filter' => 'authcheck']);

$routes->get('/products', 'Products::index', ['filter' => 'authcheck']);
$routes->post('/products/save_product', 'Products::save_product', ['filter' => 'authcheck']);
$routes->post('/products/get_table_products', 'Products::get_table_products', ['filter' => 'authcheck']);
$routes->post('/products/edit_product', 'Products::edit_product', ['filter' => 'authcheck']);
$routes->post('/products/active_inactive', 'Products::active_inactive', ['filter' => 'authcheck']);
$routes->post('/products/get_custom_filters', 'Products::get_custom_filters', ['filter' => 'authcheck']);
$routes->post('/products/save_product_cost', 'Products::save_product_cost', ['filter' => 'authcheck']);

$routes->get('/clients', 'Clients::index', ['filter' => 'authcheck']);
$routes->post('/clients/save_client', 'Clients::save_client', ['filter' => 'authcheck']);
$routes->post('/clients/get_table_clients', 'Clients::get_table_clients', ['filter' => 'authcheck']);
$routes->post('/clients/edit_client', 'Clients::edit_client', ['filter' => 'authcheck']);
$routes->post('/clients/active_inactive', 'Clients::active_inactive', ['filter' => 'authcheck']);
$routes->post('/clients/get_custom_filters', 'Clients::get_custom_filters', ['filter' => 'authcheck']);
$routes->post('/clients/get_client_volume', 'Clients::get_client_volume', ['filter' => 'authcheck']);

$routes->get('/user', 'UserController::index', ['filter' => 'authcheck']);
$routes->post('/user/get_user_role', 'UserController::get_user_role', ['filter' => 'authcheck']);
$routes->post('/user/get_table_user', 'UserController::get_table_user', ['filter' => 'authcheck']);
$routes->post('/user/save_user', 'UserController::save_user', ['filter' => 'authcheck']);
$routes->post('/user/edit_user', 'UserController::edit_user', ['filter' => 'authcheck']);
$routes->post('/user/archive_user', 'UserController::archive_user', ['filter' => 'authcheck']);
$routes->post('/user/activate_user', 'UserController::activate_user', ['filter' => 'authcheck']);

$routes->get('/sidrdashboard', 'SiDrDashboardController::index', ['filter' => 'authcheck']);
$routes->post('/sidrdashboard/get_si_dr', 'SiDrDashboardController::get_si_dr', ['filter' => 'authcheck']);
$routes->post('/sidrdashboard/update_si_dr_payment', 'SiDrDashboardController::update_si_dr_payment', ['filter' => 'authcheck']);
$routes->post('/sidrdashboard/si_get_paid_unpaid', 'SiDrDashboardController::si_get_paid_unpaid', ['filter' => 'authcheck']);
$routes->post('/sidrdashboard/dr_get_paid_unpaid', 'SiDrDashboardController::dr_get_paid_unpaid', ['filter' => 'authcheck']);

$routes->get('/dynamic_filter_client', 'DynamicFilterController::dynamic_filter_client', ['filter' => 'authcheck']);
$routes->get('/dynamic_filter_client/get_clients', 'DynamicFilterController::get_clients', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_client/save_filter', 'DynamicFilterController::save_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_client/get_client_filters', 'DynamicFilterController::get_client_filters', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_client/view_client_filter', 'DynamicFilterController::view_client_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_client/edit_client_filter', 'DynamicFilterController::edit_client_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_client/delete_client_filter', 'DynamicFilterController::delete_client_filter', ['filter' => 'authcheck']);

$routes->get('/dynamic_filter_product', 'DynamicFilterController::dynamic_filter_product', ['filter' => 'authcheck']);
$routes->get('/dynamic_filter_product/get_products', 'DynamicFilterController::get_products', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_product/save_product_filter', 'DynamicFilterController::save_product_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_product/get_product_filters', 'DynamicFilterController::get_product_filters', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_product/view_product_filter', 'DynamicFilterController::view_product_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_product/edit_product_filter', 'DynamicFilterController::edit_product_filter', ['filter' => 'authcheck']);
$routes->post('/dynamic_filter_product/delete_product_filter', 'DynamicFilterController::delete_product_filter', ['filter' => 'authcheck']);

$routes->get('/profile', 'UserController::profile', ['filter' => 'authcheck']);
$routes->post('/profile/update', 'UserController::update_profile', ['filter' => 'authcheck']);




