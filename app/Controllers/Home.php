<?php

namespace App\Controllers;

use App\Models\CoreModel;

class Home extends BaseController
{
    protected $coreModel;
    public function __construct()
    {
        $this->coreModel = new CoreModel();
    }
    
    private function hasAccess()
    {
        $allowedRoles = ['1', '2', '3', '4', '5', '6']; // Define allowed roles here
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
        return view('home/home');
    }

    public function si_receipt($id, $print_status)
    {
        $session = session();
        $login = $session->get('login');
        if($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) { // Check access
            return redirect()->to(base_url('access-forbidden'));
        }
        $result = $this->coreModel->get_si_receipt_data($id);

        $data = [
            'result' => json_encode($result),
            'print_status' => json_encode($print_status)
        ];

        $data['hide_header'] = true; // Hide the navigation bar for printing
        return view('receipts/sales_invoice_receipts', $data);
    }

    public function dr_receipt($id, $print_status)
    {
        $session = session();
        $login = $session->get('login');
        if($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) { // Check access
            return redirect()->to(base_url('access-forbidden'));
        }
        $result = $this->coreModel->get_dr_receipt_data($id);

        $data = [
            'result' => json_encode($result),
            'print_status' => json_encode($print_status)
        ];

        $data['hide_header'] = true; // Hide the navigation bar for printing
        return view('receipts/delivery_receipt_receipts', $data);
    }
}
