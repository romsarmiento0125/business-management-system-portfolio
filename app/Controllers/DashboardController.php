<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class DashboardController extends BaseController
{
    protected $dashboardModel;
    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '3', '4', '5']; // Define allowed roles here
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
        return view('dashboard/dashboard');
    }

    public function get_dashboard_data() {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $params = [
            'start_date' => $data['start'],
            'end_date' => $data['end'],
        ];

        $result = $this->dashboardModel->get_dashboard_data($params);

        return json_encode(['result' => $result]);
    }
    
    public function export_accounting_total() {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }

        $data = $this->request->getJSON(true);

        $params = [
            'start_date' => $data['start'],
            'end_date' => $data['end'],
        ];

        $data = $this->dashboardModel->export_accounting_total($params);

        return json_encode(['data' => $data]);
    }
}
