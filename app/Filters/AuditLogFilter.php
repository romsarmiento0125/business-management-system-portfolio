<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\AuditLogModel;

class AuditLogFilter implements FilterInterface
{
    /**
     * Endpoint-to-description mapping.
     * Describes what the user did in plain language.
     */
    private array $endpointDescriptions = [
        // Login / Logout
        'POST /login/authenticate'                    => 'User logged in',
        'POST /login/logout'                          => 'User logged out',

        // Dashboard
        'GET /dashboard'                              => 'Viewed dashboard',
        'POST /dashboard/get_dashboard_data'          => 'Loaded dashboard data',
        'POST /dashboard/export_accounting_total'     => 'Exported accounting total from dashboard',

        // Accounting
        'GET /accounting'                             => 'Viewed accounting page',
        'POST /accounting/get_filters'                => 'Loaded accounting filters',
        'POST /accounting/get_si_data_items_accounting' => 'Loaded SI data in accounting',
        'POST /accounting/get_dr_data_items_accounting' => 'Loaded DR data in accounting',
        'POST /accounting/dynamic_change_client_show' => 'Changed client visibility in accounting',
        'POST /accounting/dynamic_change_product_show'=> 'Changed product visibility in accounting',
        'POST /accounting/get_si_volume'              => 'Loaded SI volume in accounting',
        'POST /accounting/get_dr_volume'              => 'Loaded DR volume in accounting',
        'POST /accounting/get_si_dr_volume'           => 'Loaded SI & DR volume in accounting',

        // Sales Invoice
        'GET /sales_invoice'                          => 'Viewed sales invoice page',
        'POST /sales_invoice/get_products_clients_si' => 'Loaded products & clients for SI',
        'POST /sales_invoice/save_draft'              => 'Saved sales invoice draft',
        'POST /sales_invoice/print_invoice'           => 'Printed sales invoice',
        'POST /sales_invoice/get_sales_invoice_by_id' => 'Viewed sales invoice details',
        'POST /sales_invoice/update_draft'            => 'Updated sales invoice draft',
        'POST /sales_invoice/get_si_receipt_by_id'    => 'Viewed SI receipt details',
        'POST /sales_invoice/print_si_receipt'        => 'Printed SI receipt',
        'POST /sales_invoice/draft_si_receipt'        => 'Drafted SI receipt',
        'POST /sales_invoice/cancel_si_receipt'       => 'Cancelled SI receipt',
        'POST /sales_invoice/authenticate_user'       => 'Authenticated user for SI action',

        // Delivery Receipt
        'GET /delivery_receipt'                          => 'Viewed delivery receipt page',
        'POST /delivery_receipt/get_products_clients_dr' => 'Loaded products & clients for DR',
        'POST /delivery_receipt/save_draft'              => 'Saved delivery receipt draft',
        'POST /delivery_receipt/print_delivery'          => 'Printed delivery receipt',
        'POST /delivery_receipt/get_delivery_receipt_by_id' => 'Viewed delivery receipt details',
        'POST /delivery_receipt/update_draft'            => 'Updated delivery receipt draft',
        'POST /delivery_receipt/get_dr_receipt_by_id'    => 'Viewed DR receipt details',
        'POST /delivery_receipt/print_dr_receipt'        => 'Printed DR receipt',
        'POST /delivery_receipt/draft_dr_receipt'        => 'Drafted DR receipt',
        'POST /delivery_receipt/cancel_dr_receipt'       => 'Cancelled DR receipt',
        'POST /delivery_receipt/authenticate_user'       => 'Authenticated user for DR action',

        // Products
        'GET /products'                               => 'Viewed products page',
        'POST /products/save_product'                  => 'Created a new product',
        'POST /products/get_table_products'            => 'Loaded products table',
        'POST /products/edit_product'                  => 'Edited a product',
        'POST /products/active_inactive'               => 'Changed product active/inactive status',
        'POST /products/get_custom_filters'            => 'Loaded product custom filters',
        'POST /products/save_product_cost'             => 'Saved product cost',

        // Clients
        'GET /clients'                                => 'Viewed clients page',
        'POST /clients/save_client'                   => 'Created a new client',
        'POST /clients/get_table_clients'             => 'Loaded clients table',
        'POST /clients/edit_client'                   => 'Edited a client',
        'POST /clients/active_inactive'               => 'Changed client active/inactive status',
        'POST /clients/get_custom_filters'            => 'Loaded client custom filters',

        // Users
        'GET /user'                                   => 'Viewed user management page',
        'POST /user/get_user_role'                    => 'Loaded user roles',
        'POST /user/get_table_user'                   => 'Loaded users table',
        'POST /user/save_user'                        => 'Created a new user',
        'POST /user/edit_user'                        => 'Edited a user',
        'POST /user/archive_user'                     => 'Archived a user',
        'POST /user/activate_user'                    => 'Activated a user',

        // SI & DR Dashboard
        'GET /sidrdashboard'                          => 'Viewed SI & DR dashboard',
        'POST /sidrdashboard/get_si_dr'               => 'Loaded SI & DR data',
        'POST /sidrdashboard/update_si_dr_payment'    => 'Updated SI/DR payment status',
        'POST /sidrdashboard/si_get_paid_unpaid'      => 'Loaded SI paid/unpaid data',
        'POST /sidrdashboard/dr_get_paid_unpaid'      => 'Loaded DR paid/unpaid data',

        // Dynamic Filter - Client
        'GET /dynamic_filter_client'                        => 'Viewed client filter page',
        'GET /dynamic_filter_client/get_clients'            => 'Loaded clients for filter',
        'POST /dynamic_filter_client/save_filter'           => 'Saved a client filter',
        'POST /dynamic_filter_client/get_client_filters'    => 'Loaded client filters',
        'POST /dynamic_filter_client/view_client_filter'    => 'Viewed a client filter',
        'POST /dynamic_filter_client/edit_client_filter'    => 'Edited a client filter',
        'POST /dynamic_filter_client/delete_client_filter'  => 'Deleted a client filter',

        // Dynamic Filter - Product
        'GET /dynamic_filter_product'                        => 'Viewed product filter page',
        'GET /dynamic_filter_product/get_products'           => 'Loaded products for filter',
        'POST /dynamic_filter_product/save_product_filter'   => 'Saved a product filter',
        'POST /dynamic_filter_product/get_product_filters'   => 'Loaded product filters',
        'POST /dynamic_filter_product/view_product_filter'   => 'Viewed a product filter',
        'POST /dynamic_filter_product/edit_product_filter'   => 'Edited a product filter',
        'POST /dynamic_filter_product/delete_product_filter' => 'Deleted a product filter',

        // Profile
        'GET /profile'                                => 'Viewed profile page',
        'POST /profile/update'                        => 'Updated profile',

        // Home / Receipts
        'GET /'                                       => 'Viewed home page',
    ];

    /**
     * We don't need to do anything before the request.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before the request
    }

    /**
     * After the request is handled, log the action.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session();

        // Only log for authenticated users
        if (! $session->get('login')) {
            return;
        }

        $method   = $request->getMethod();
        $endpoint = '/' . ltrim(uri_string(), '/');
        $userId   = $session->get('user_id');

        // Sanitize data — strip sensitive fields
        $sensitiveKeys = ['password', 'pass', 'password_confirm', 'current_password', 'new_password', 'token', 'csrf_token'];

        // Capture payload for non-GET requests
        $payload = null;

        if ($method !== 'GET') {
            $rawBody  = $request->getBody();
            $postData = $request->getPost();

            // Priority 1: Try parsing raw body as JSON (AJAX with contentType: application/json)
            if (! empty($rawBody)) {
                $decoded = json_decode($rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload = $this->redactSensitive($decoded, $sensitiveKeys);
                }
            }

            // Priority 2: Use parsed POST data (standard form submissions)
            if ($payload === null && ! empty($postData)) {
                // Detect JSON-as-form-key artifact: {'{"start":"2026-01-01"}' => ''}
                if (count($postData) === 1) {
                    $key   = array_key_first($postData);
                    $value = $postData[$key];
                    if (($value === '' || $value === null) && str_starts_with($key, '{')) {
                        $decoded = json_decode($key, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $payload = $this->redactSensitive($decoded, $sensitiveKeys);
                        }
                    }
                }

                // Normal form-encoded POST data
                if ($payload === null) {
                    $payload = $this->redactSensitive($postData, $sensitiveKeys);
                }
            }

            // Priority 3: Parse URL-encoded raw body (e.g. username=x&password=y)
            if ($payload === null && ! empty($rawBody)) {
                parse_str($rawBody, $parsed);
                if (! empty($parsed)) {
                    $payload = $this->redactSensitive($parsed, $sensitiveKeys);
                }
            }

            // Convert to JSON string for storage
            $payload = ($payload !== null) ? json_encode($payload) : null;
        }

        // Generate human-readable description
        $description = $this->resolveDescription($method, $endpoint);

        $auditLog = new AuditLogModel();
        $auditLog->insert([
            'user_id'       => $userId,
            'method'        => $method,
            'endpoint'      => mb_substr($endpoint, 0, 255),
            'payload'       => $payload,
            'description'   => $description,
            'response_code' => $response->getStatusCode(),
            'ip_address'    => $request->getIPAddress(),
        ]);
    }

    /**
     * Redact sensitive keys from an array recursively.
     */
    private function redactSensitive(array $data, array $keys): array
    {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Resolve a human-readable description from the endpoint map.
     * Falls back to auto-generating one from the URL.
     */
    private function resolveDescription(string $method, string $endpoint): string
    {
        $key = $method . ' ' . $endpoint;

        if (isset($this->endpointDescriptions[$key])) {
            return $this->endpointDescriptions[$key];
        }

        // For dynamic-segment routes like /sales_invoice_view/5/abc, match the prefix
        foreach ($this->endpointDescriptions as $pattern => $desc) {
            // Convert the static key to a prefix check (strip trailing segments)
            if (str_starts_with($key, $pattern)) {
                return $desc;
            }
        }

        // Auto-generate: "POST /sales_invoice/save_draft" → "Sales invoice - Save draft"
        $parts   = explode('/', trim($endpoint, '/'));
        $module  = ucwords(str_replace('_', ' ', $parts[0] ?? 'Unknown'));
        $action  = isset($parts[1]) ? ucwords(str_replace('_', ' ', $parts[1])) : 'View';

        return "{$module} - {$action}";
    }
}
