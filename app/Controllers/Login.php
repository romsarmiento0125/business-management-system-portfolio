<?php

namespace App\Controllers;

use App\Models\CoreModel;

class Login extends BaseController
{
    protected $coreModel;
    public function __construct()
    {
        $this->coreModel = new CoreModel();
    }

    public function index()
    {
        $data['hide_header'] = true;
        return view('login/login', $data);
    }

    public function authenticate()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $result = $this->coreModel->user_login($username);

        if ($result) {
            if (password_verify($password, $result[0]->password)) {
                $session = session();
                $session->set('user_id', $result[0]->id);
                $session->set('username', $result[0]->username);
                $session->set('role', $result[0]->role_id);
                $session->set('login', 1);
                return json_encode(['status' => 'success', 'message' => 'Login success']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
    }
}
