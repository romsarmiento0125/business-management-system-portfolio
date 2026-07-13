<?php

namespace App\Controllers;

use App\Models\ClientModel;

class Clients extends BaseController
{
    protected $ClientModel;
    public function __construct()
    {
        $this->ClientModel = new ClientModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '3', '4', '6']; // Define allowed roles here
        $session = session();
        $role = $session->get('role'); // Assuming 'role' is stored in the session
        return in_array($role, $allowedRoles);
    }

    public function index()
    {
        $session = session();
        $login = $session->get('login');
        if($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        
        $data['user_role'] = $session->get('role');
        return view('clients/clients', $data);
    }

    public function get_table_clients()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $clients = $this->ClientModel->get_clients();
        return $this->response->setJSON($clients);
    }

    public function save_client()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $client_id = uniqid('cl_', false); // Generate a shorter unique ID

        $client_name = $this->request->getPost('client_name');
        $client_tin = $this->request->getPost('client_tin');
        $client_business_name = $this->request->getPost('client_business_name');
        $client_term = $this->request->getPost('client_term');
        $client_address = $this->request->getPost('client_address');

        $result = $this->ClientModel->check_client_exists($client_name);
        if (is_string($result)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $result]);
        }
        if ($result[0]->count > 0) {
            return $this->response->setJSON(['status' => 'exists', 'message' => 'Client already exists']);
        }

        $params = [
            $client_name,
            $client_tin,
            $client_address,
            $client_business_name,
            $client_term,
            $user_id,
            $user_id,
            $client_id // Add the shorter unique ID to the parameters
        ];

        $insert = $this->ClientModel->insert_client($params);
        if ($insert === 'success') {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Client added successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => $params]);
        }
    }

    public function edit_client()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');
        
        $client_name = $this->request->getPost('client_name');
        $client_name_attr = $this->request->getPost('client_name_attr');
        $client_id = $this->request->getPost('client_id');
        $client_tin = $this->request->getPost('client_tin');
        $client_business_name = $this->request->getPost('client_business_name');
        $client_term = $this->request->getPost('client_term');
        $client_address = $this->request->getPost('client_address');

        $result = $this->ClientModel->check_edit_client_exists($client_name, $client_name_attr);
        if (is_string($result)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $result]);
        }
        if ($result[0]->count > 0) {
            return $this->response->setJSON(['status' => 'exists', 'message' => 'Client already exists']);
        }

        $params1 = [
            $user_id,
            $client_name_attr,
        ];

        $params2 = [
            $client_name,
            $client_tin,
            $client_address,
            $client_business_name,
            $client_term,
            $user_id,
            $user_id,
            $client_id
        ];


        $update = $this->ClientModel->update_client($params1, $params2);
        if ($update === 'success') {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Client updated successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => $update]);
        }
    }
    
    public function active_inactive()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');
        
        $client_id = $this->request->getPost('client_id');
        $is_active = $this->request->getPost('is_active');

        $params = [
            $is_active,
            $user_id,
            $client_id
        ];

        $update = $this->ClientModel->active_inactive_client($params);
        if ($update === 'success') {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Client updated successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => $update]);
        }
    }

    public function get_custom_filters()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $filters = $this->ClientModel->get_client_custom_filters();
        return $this->response->setJSON([
            'success' => true,
            'filters' => $filters,
        ]);
    }

    public function get_client_volume()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $client_id        = $this->request->getPost('client_id');
        $date_from        = $this->request->getPost('date_from');
        $date_to          = $this->request->getPost('date_to');
        $type             = $this->request->getPost('type'); // 'si', 'dr', 'sidr'
        $product_filter_id = $this->request->getPost('product_filter_id');

        if (!$client_id || !$date_from || !$date_to || !in_array($type, ['si', 'dr', 'sidr'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parameters']);
        }

        $result = $this->ClientModel->get_client_volume($client_id, $date_from, $date_to, $type, $product_filter_id);

        if (is_string($result) && $result !== '0' && !is_numeric($result)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $result]);
        }

        return $this->response->setJSON(['status' => 'success', 'total_qty' => $result]);
    }
}
