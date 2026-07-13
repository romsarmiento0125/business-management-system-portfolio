<?php

namespace App\Controllers;

use App\Models\CoreModel;

class UserController extends BaseController
{
    protected $coreModel;
    public function __construct()
    {
        $this->coreModel = new CoreModel();
    }

    private function hasAccess()
    {
        $allowedRoles = ['1']; // Define allowed roles here
        $session = session();
        $role = $session->get('role'); // Assuming 'role' is stored in the session
        return in_array($role, $allowedRoles);
    }

    public function index()
    {
        $session = session();
        $login = $session->get('login');
        if ($login != 1) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        return view('user_management/user_management');
    }

    public function get_user_role()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $products = $this->coreModel->get_role();
        return json_encode($products);
    }

    public function get_table_user()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $products = $this->coreModel->get_user();
        return json_encode($products);
    }
    
    public function save_user()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        $username = $data['username'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT); // Encrypt the password
        $role = $data['role'];

        $result = $this->coreModel->check_user_exists($username);
        if (is_string($result)) {
            return json_encode(['status' => 'error', 'message' => $result]);
        }
        if ($result[0]->count > 0) {
            return json_encode(['status' => 'exists', 'message' => 'Username already exists']);
        }

        $params = [
            $username,
            $password,
            $firstname,
            $lastname,
            $user_id,
            $user_id,
            $role,
        ];

        $insert = $this->coreModel->insert_user($params);
        if ($insert['status'] !== 'success') { // Adjust condition to check the returned status
            return json_encode(['status' => 'error', 'message' => $insert['message']]);
        }
        return json_encode(['status' => 'success', 'message' => $insert['message']]);
    }

    public function edit_user()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        $params = [
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'role_id' => $data['role'],
            'updater_id' => $user_id,
            'password' => isset($data['password']) && !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null,
            'id' => $data['id']
        ];

        $update = $this->coreModel->update_user($params);
        if ($update['status'] !== 'success') { // Adjust condition to check the returned status
            return json_encode(['status' => 'error', 'message' => $update['message']]);
        }
        return json_encode(['status' => 'success', 'message' => $update['message']]);
    }

    public function archive_user()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        $params = [
            'id' => $data['id'],
            'updater_id' => $user_id
        ];

        $archive = $this->coreModel->archive_user($params);
        if ($archive['status'] !== 'success') { // Adjust condition to check the returned status
            return json_encode(['status' => 'error', 'message' => $archive['message']]);
        }
        return json_encode(['status' => 'success', 'message' => $archive['message']]);
    }

    public function activate_user()
    {
        if (!$this->hasAccess()) { // Check access using the simplified function
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        $params = [
            'id' => $data['id'],
            'updater_id' => $user_id
        ];

        $activate = $this->coreModel->activate_user($params);
        if ($activate['status'] !== 'success') { // Adjust condition to check the returned status
            return json_encode(['status' => 'error', 'message' => $activate['message']]);
        }
        return json_encode(['status' => 'success', 'message' => $activate['message']]);
    }

    public function profile()
    {
        $session = session();
        if ($session->get('login') != 1) {
            return redirect()->to(base_url('login'));
        }
        $user = $this->coreModel->get_user_by_id($session->get('user_id'));
        return view('profile/profile', ['user' => $user]);
    }

    public function update_profile()
    {
        $session = session();
        if ($session->get('login') != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        $data = $this->request->getJSON(true);
        $params = [
            'id'         => $session->get('user_id'),
            'first_name' => trim($data['first_name'] ?? ''),
            'last_name'  => trim($data['last_name'] ?? ''),
            'password'   => !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null,
        ];
        $result = $this->coreModel->update_profile($params);
        return $this->response->setJSON($result);
    }
}
