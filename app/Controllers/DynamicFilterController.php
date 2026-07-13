<?php

namespace App\Controllers;

use App\Models\DynamicFilterModel;

class DynamicFilterController extends BaseController
{
    protected $dynamicFilterModel;
    public function __construct()
    {
        $this->dynamicFilterModel = new DynamicFilterModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '3', '4', '5', '6']; // Define allowed roles here
        $session = session();
        $role = $session->get('role'); // Assuming 'role' is stored in the session
        return in_array($role, $allowedRoles);
    }

    public function dynamic_filter_client()
    {
        $session = session();
        $login = $session->get('login');
        if($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        return view('dynamic_filter/dynamic_filter_client');
    }

    public function get_clients()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $result = $this->dynamicFilterModel->get_clients();

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode($result);
    }

    public function save_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $session = session();
        $user_id = $session->get('user_id');

        // Get the incoming JSON as an associative array (consistent with SalesInvoice style)
        $data = $this->request->getJSON(true);

        // Simple validation: ensure payload, filter name, and clients are present
        if (empty($data) || empty($data['filter_name']) || empty($data['clients'])) {
            echo json_encode(['result' => 'data empty']);
            return;
        }

        // Save and handle uniqueness or other errors
        $result = $this->dynamicFilterModel->save_client_filter($data, $user_id);

        if (is_string($result)) {
            // Model returned an error message (e.g., duplicate name)
            return $this->response->setStatusCode(409)->setJSON(['error' => $result]);
        }

        return $this->response->setJSON([
            'message' => 'Filter saved successfully.',
            'result' => $result,
        ]);
    }

    public function get_client_filters()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $session = session();
        $user_id = $session->get('user_id');

        // Fetch filters from the model
        $filters = $this->dynamicFilterModel->get_client_filters($user_id);

        if (is_string($filters)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $filters]);
        }

        return json_encode(['filters' => $filters]);
    }

    public function view_client_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['client_filter_id'] ?? null;

        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        $result = $this->dynamicFilterModel->get_client_filter_items($filterId);

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode(['clients' => $result]);
    }

    public function edit_client_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['client_filter_id'] ?? null;

        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        // If payload includes fields for update, perform update; otherwise, return items (view/edit prefill)
        if (isset($data['filter_name']) || isset($data['clients'])) {
            $session = session();
            $user_id = $session->get('user_id');

            $filterName = $data['filter_name'] ?? '';
            $clients = $data['clients'] ?? [];

            $update = $this->dynamicFilterModel->update_client_filter($filterId, $user_id, $filterName, $clients);
            if (is_string($update)) {
                // Model returned an error message (e.g., duplicate name)
                return $this->response->setStatusCode(409)->setJSON(['error' => $update]);
            }

            return $this->response->setJSON([
                'message' => 'Filter updated successfully.',
                'result' => $update,
            ]);
        } else {
            $result = $this->dynamicFilterModel->get_client_filter_items($filterId);

            if (is_string($result)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
            }

            return json_encode(['clients' => $result]);
        }
    }

    public function delete_client_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['client_filter_id'] ?? null;
        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        $session = session();

        $result = $this->dynamicFilterModel->delete_client_filter($filterId);
        if (is_string($result)) {
            // Model returned an error string
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return $this->response->setJSON(['message' => 'Filter deleted successfully.', 'result' => $result]);
    }

    // ---------------------------------- Products -------------------------------------------------

    public function dynamic_filter_product()
    {
        $session = session();
        $login = $session->get('login');
        if($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        return view('dynamic_filter/dynamic_filter_product');
    }

    public function get_products()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $result = $this->dynamicFilterModel->get_products();

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode($result);
    }

    public function save_product_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $session = session();
        $user_id = $session->get('user_id');

        // Get the incoming JSON as an associative array (consistent with SalesInvoice style)
        $data = $this->request->getJSON(true);

        // Simple validation: ensure payload, filter name, and products are present
        if (empty($data) || empty($data['filter_name_product']) || empty($data['products'])) {
            echo json_encode(['result' => 'data empty']);
            return;
        }

        // Save and handle uniqueness or other errors
        $result = $this->dynamicFilterModel->save_product_filter($data, $user_id);

        if (is_string($result)) {
            // Model returned an error message (e.g., duplicate name)
            return $this->response->setStatusCode(409)->setJSON(['error' => $result]);
        }

        return $this->response->setJSON([
            'message' => 'Filter saved successfully.',
            'result' => $result,
        ]);
    }

    public function get_product_filters()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $session = session();
        $user_id = $session->get('user_id');

        // Fetch filters from the model
        $filters = $this->dynamicFilterModel->get_product_filters($user_id);

        if (is_string($filters)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $filters]);
        }

        return json_encode(['filters' => $filters]);
    }

    public function view_product_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['product_filter_id'] ?? null;

        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        $result = $this->dynamicFilterModel->get_product_filter_items($filterId);

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode(['products' => $result]);
    }

    public function edit_product_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['product_filter_id'] ?? null;

        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        // If payload includes fields for update, perform update; otherwise, return items (view/edit prefill)
        if (isset($data['filter_name']) || isset($data['products'])) {
            $session = session();
            $user_id = $session->get('user_id');

            $filterName = $data['filter_name'] ?? '';
            $products = $data['products'] ?? [];

            $update = $this->dynamicFilterModel->update_product_filter($filterId, $user_id, $filterName, $products);
            if (is_string($update)) {
                // Model returned an error message (e.g., duplicate name)
                return $this->response->setStatusCode(409)->setJSON(['error' => $update]);
            }

            return $this->response->setJSON([
                'message' => 'Filter updated successfully.',
                'result' => $update,
            ]);
        } else {
            $result = $this->dynamicFilterModel->get_client_filter_items($filterId);

            if (is_string($result)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
            }

            return json_encode(['clients' => $result]);
        }
    }

    public function delete_product_filter()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);
        $filterId = $data['product_filter_id'] ?? null;
        if (empty($filterId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Filter ID is required']);
        }

        $session = session();

        $result = $this->dynamicFilterModel->delete_product_filter($filterId);
        if (is_string($result)) {
            // Model returned an error string
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return $this->response->setJSON(['message' => 'Filter deleted successfully.', 'result' => $result]);
    }
}

