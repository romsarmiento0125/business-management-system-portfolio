<?php

namespace App\Controllers;

use App\Models\AccountingModel;

class AccountingController extends BaseController
{
    protected $accountingModel;
    public function __construct()
    {
        $this->accountingModel = new AccountingModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '4']; // Define allowed roles here
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
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        return view('accounting/accounting');
    }
    
    public function get_filters()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $clients = $this->accountingModel->get_client_filter();
        $products = $this->accountingModel->get_product_filter();

        return $this->response->setJSON([
            'success' => true,
            'clients' => $clients,
            'products' => $products,
        ]);
    }
    
    public function get_si_data_items_accounting()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $data = $this->accountingModel->get_si_data_items_accounting($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function get_dr_data_items_accounting()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $data = $this->accountingModel->get_dr_data_items_accounting($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function dynamic_change_client_show()
    {
        if (!$this->hasAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $filter_id = $this->request->getPost('filter_id');

        if ($filter_id === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing filter_id'
            ]);
        }

        $clients = $this->accountingModel->update_clients_by_filter_id((int)$filter_id);
        if (is_string($clients)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $clients
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'clients' => $clients
        ]);
    }
    
    public function dynamic_change_product_show()
    {
        if (!$this->hasAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $filter_id = $this->request->getPost('filter_id');

        if ($filter_id === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing filter_id'
            ]);
        }

        $products = $this->accountingModel->update_products_by_filter_id((int)$filter_id);
        if (is_string($products)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $products
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'products' => $products
        ]);
    }

    public function get_si_volume() {
        if (!$this->hasAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $data = $this->accountingModel->get_si_volume($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function get_dr_volume() {
        if (!$this->hasAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $data = $this->accountingModel->get_dr_volume($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function get_si_dr_volume() {
        if (!$this->hasAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $data = $this->accountingModel->get_si_dr_volume($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }
}
