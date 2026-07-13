<?php

namespace App\Controllers;

use App\Models\DeliveryReceiptModel;

class DeliveryReceiptController extends BaseController
{
    protected $deliveryReceiptModel;
    public function __construct()
    {
        $this->deliveryReceiptModel = new DeliveryReceiptModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '3']; // Define allowed roles here
        $session = session();
        $role = $session->get('role');
        return in_array($role, $allowedRoles);
    }

    public function index()
    {
        $session = session();
        $login = $session->get('login');
        if ($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        return view('delivery_receipts/delivery_receipts');
    }

    public function get_products_clients_dr()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $start = $data['start'];
        $end = $data['end'];

        $result = $this->deliveryReceiptModel->get_products_clients_dr($start, $end);

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode($result);
    }

    public function print_delivery()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        // Validate data
        $missingFields = [];
        if (empty($data['customer'])) {
            $missingFields[] = 'Customer';
        }

        if (empty($data['items'])) {
            $missingFields[] = 'items';
        }

        if (!empty($missingFields)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid data. Please fill in the following fields: ' . implode(', ', $missingFields)]);
        }

        // Extract customer data (defensive: support both 'id' or 'client_id')
        $customerDetail = $data['customer'];
        $customerId = $customerDetail['id'] ?? $customerDetail['client_id'] ?? null;
        $customerTerms = $customerDetail['terms'] ?? null;
        $customerDate = $customerDetail['date'] ?? null;

        // Extract items data
        $items = $data['items'];

        $freightCost = $data['freight_cost'] ?? 0;

        $params = [
            $customerId,
            $customerTerms,
            $freightCost,
            'printed',
            $user_id,
            $user_id,
            $customerDate
        ];

        $allItemParams = [];
        $allDiscountParams = [];
        foreach ($items as $item) {
            $itemParams = [
                $item['price'],
                $item['qty'],
                $item['unique_id'],
                $user_id,
                $user_id,
                $item['product_id'],
            ];

            $allItemParams[] = $itemParams;
            
            // Only process discounts that have a non-empty value
            if (!empty($item['discounts'])) {
                $validDiscounts = array_filter(
                    $item['discounts'],
                    function($discount) {
                        return isset($discount['value']) && $discount['value'] !== '';
                    }
                );
                foreach ($validDiscounts as $discount) {
                    $discountParams = [
                        $discount['label'],
                        $discount['value'],
                        $item['unique_id'],
                        $user_id,
                        $user_id
                    ];
                    $allDiscountParams[] = $discountParams;
                }
            }
        }

        $result = $this->deliveryReceiptModel->insert_delivery_receipt($params, $allItemParams, $allDiscountParams);

        echo json_encode(['result' => $result]);
    }
    
    public function save_draft()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        // Validate data
        $missingFields = [];
        if (empty($data['customer'])) {
            $missingFields[] = 'Customer';
        }

        if (empty($data['items'])) {
            $missingFields[] = 'items';
        }

        if (!empty($missingFields)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid data. Please fill in the following fields: ' . implode(', ', $missingFields)]);
        }

        // Extract customer data (defensive)
        $customerDetail = $data['customer'];
        $customerId = $customerDetail['client_id'] ?? $customerDetail['id'] ?? null;
        $customerTerms = $customerDetail['terms'] ?? null;
        $customerDate = $customerDetail['date'] ?? null;

        // Extract items data
        $items = $data['items'];

        $freightCost = $data['freight_cost'] ?? 0;

        $params = [
            $customerId,
            $customerTerms,
            $freightCost,
            'draft',
            $user_id,
            $user_id,
            $customerDate
        ];

        $allItemParams = [];
        $allDiscountParams = [];
        foreach ($items as $item) {
            $itemParams = [
                $item['price'],
                $item['qty'],
                $item['unique_id'],
                $user_id,
                $user_id,
                $item['unique_product_id'],
            ];

            $allItemParams[] = $itemParams;
            
            // Only process discounts that have a non-empty value
            if (!empty($item['discounts'])) {
                $validDiscounts = array_filter(
                    $item['discounts'],
                    function($discount) {
                        return isset($discount['value']) && $discount['value'] !== '';
                    }
                );
                foreach ($validDiscounts as $discount) {
                    $discountParams = [
                        $discount['label'],
                        $discount['value'],
                        $item['unique_id'],
                        $user_id,
                        $user_id
                    ];
                    $allDiscountParams[] = $discountParams;
                }
            }
        }

        $result = $this->deliveryReceiptModel->insert_delivery_receipt($params, $allItemParams, $allDiscountParams);

        echo json_encode(['result' => $result]);
    }

    public function update_draft()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        // Validate data
        $missingFields = [];
        if (empty($data['customer'])) {
            $missingFields[] = 'Customer';
        }

        if (empty($data['items'])) {
            $missingFields[] = 'items';
        }

        if (!empty($missingFields)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid data. Please fill in the following fields: ' . implode(', ', $missingFields)]);
        }

        // Extract customer data (defensive)
        $customerDetail = $data['customer'];
        $dr_id = $customerDetail['dr_id'] ?? $customerDetail['id'] ?? null;
        $customerTerms = $customerDetail['terms'] ?? null;
        $customerDate = $customerDetail['date'] ?? null;

        // Extract items data
        $items = $data['items'];

        $freightCost = $data['freight_cost'] ?? 0;

        $params = [
            $customerTerms,
            $freightCost,
            $customerDate,
            $dr_id
        ];

        $allItemParams = [];
        $allDiscountParams = [];
        foreach ($items as $item) {
            $itemParams = [
                $item['price'],
                $item['qty'],
                $item['unique_id'],
                $user_id,
                $user_id,
                $item['unique_product_id'], 
            ];

            $allItemParams[] = $itemParams;
            
            // Only process discounts that have a non-empty value
            if (!empty($item['discounts'])) {
                $validDiscounts = array_filter(
                    $item['discounts'],
                    function($discount) {
                        return isset($discount['value']) && $discount['value'] !== '';
                    }
                );
                foreach ($validDiscounts as $discount) {
                    $discountParams = [
                        $discount['label'],
                        $discount['value'],
                        $item['unique_id'],
                        $user_id,
                        $user_id
                    ];
                    $allDiscountParams[] = $discountParams;
                }
            }
        }

        $result = $this->deliveryReceiptModel->update_delivery_receipt($dr_id, $params, $allItemParams, $allDiscountParams);

        echo json_encode(['result' => $result]);
    }

    public function get_delivery_receipt_by_id()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $input = $this->request->getJSON(true);

        // Support passing either raw id or object with dr_id/id
        if (is_array($input)) {
            $id = $input['dr_id'] ?? $input['id'] ?? null;
        } else {
            $id = $input;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Delivery Receipt ID is required.']);
        }

        $result = $this->deliveryReceiptModel->get_delivery_receipt_by_id($id);

        return json_encode($result);
    }

    public function get_dr_receipt_by_id()
    {

        $session = session();
        $role = $session->get('role');
        // Allow access if hasAccess() or if user role is 6
        if (!$this->hasAccess() && $role !== '6') {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $dr_id = $data['dr_id'] ?? null;
        $status = $data['status'] ?? null;

        // Validate data
        if (empty($dr_id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Delivery Receipt ID is required.']);
        }

        // Fetch delivery receipt by ID
        $result = $this->deliveryReceiptModel->get_dr_receipt_by_id($dr_id, $status);

        return json_encode($result);
    }

    public function authenticate_user()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $result = $this->deliveryReceiptModel->user_login($username);

        if ($result && password_verify($password, $result[0]->password)) {
            return json_encode(['status' => 'success']);
        } else {
            return json_encode(['status' => 'failed']);
        }

        // return json_encode(['status' => $result]);
    }

    function print_dr_receipt()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $result = $this->deliveryReceiptModel->print_dr($data['dr_id'], $data['client_id']);

        return json_encode(['status' => $result]);
    }

    function draft_dr_receipt()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $result = $this->deliveryReceiptModel->draft_dr_receipt($data['dr_id'], $data['client_id']);

        return json_encode(['status' => $result]);
    }

    function cancel_dr_receipt()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $result = $this->deliveryReceiptModel->cancel_dr_receipt($data['dr_id']);

        return json_encode(['status' => $result]);
    }
}
