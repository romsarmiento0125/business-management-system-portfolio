<?php

namespace App\Controllers;

use App\Models\SiDrDashboardModel;

class SiDrDashboardController extends BaseController
{
    protected $siDrDashboardModel;
    public function __construct()
    {
        $this->siDrDashboardModel = new SiDrDashboardModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '4', '5', '6']; // Define allowed roles here
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

        $data['user_role'] = $session->get('role');
        return view('si_and_dr_dashboard/si_and_dr_dashboard', $data);
    }

    public function get_si_dr()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $result = $this->siDrDashboardModel->get_si_dr($date_start, $date_end);

        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode($result);
    }
    
    public function update_si_dr_payment()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON();
        if (!$data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid data']);
        }

        $session = session();
        $sessionUsername = $session->get('username');
        $result = $this->siDrDashboardModel->update_si_dr_payment($data, $sessionUsername);
        
        if (is_string($result)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $result]);
        }

        return json_encode($result);
    }

    public function si_get_paid_unpaid()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $paid_data = $this->siDrDashboardModel->get_paid_si($date_start, $date_end);
        $unpaid_data = $this->siDrDashboardModel->get_unpaid_si($date_start, $date_end);
        

        return $this->response->setJSON([
            'success' => true,
            'paid_data' => $paid_data,
            'unpaid_data' => $unpaid_data,
        ]);
    }

    public function dr_get_paid_unpaid()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $date_start = $this->request->getPost('date_start');
        $date_end = $this->request->getPost('date_end');

        $paid_data = $this->siDrDashboardModel->get_paid_dr($date_start, $date_end);
        $unpaid_data = $this->siDrDashboardModel->get_unpaid_dr($date_start, $date_end);

        return $this->response->setJSON([
            'success' => true,
            'paid_data' => $paid_data,
            'unpaid_data' => $unpaid_data,
        ]);
    }
}