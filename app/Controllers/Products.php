<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Products extends BaseController
{
    protected $ProductModel;
    public function __construct()
    {
        $this->ProductModel = new ProductModel();
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
        return view('products/products', $data);
    }

    public function get_table_products()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $products = $this->ProductModel->get_products();
        return json_encode($products);
    }

    public function save_product()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $product_id = uniqid('p_', false);

        $data = $this->request->getJSON(true);

        $product_name = $data['product_name'];
        $product_item = $data['product_item'];
        $product_unit = $data['product_unit'];
        $product_weight = $data['product_weight'];
        $product_price = $data['product_price'];
        $product_tag = $data['product_tag'];

        $result = $this->ProductModel->check_product_exists($product_name, $product_item);
        if (is_string($result)) {
            return json_encode(['status' => 'error', 'message' => $result]);
        }
        if ($result[0]->count > 0) {
            return json_encode(['status' => 'exists', 'message' => 'Product already exists']);
        }

        $params = [
            $product_name,
            $product_item,
            $product_unit,
            $product_weight,
            $product_price,
            $user_id,
            $user_id,
            $product_id,
            $product_tag
        ];

        $insert = $this->ProductModel->insert_product($params);
        if ($insert['status'] === 'failed') {
            return json_encode(['status' => 'error', 'message' => 'Failed to save product']);
        }
        return json_encode(['status' => 'success', 'message' => 'Product saved successfully']);
    }

    public function edit_product()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);
        
        $product_name = $data['product_name'];
        $product_name_attr = $data['product_name_attr'];
        $product_id = $data['product_id'];
        $product_unit = $data['product_unit'];
        $product_item = $data['product_item'];
        $product_weight = $data['product_weight'];
        $product_price = $data['product_price'];
        $product_tag = $data['product_tag'];

        // Check if product name or item already exists
        $result = $this->ProductModel->edit_check_product_exists($product_name, $product_item, $product_name_attr);
        if(!empty($result)) {
            if(count($result) > 1) {
                return json_encode(['status' => 'error', 'message' => 'Product or Item code already exists']);
            }
            if($result[0]->id !== $product_name_attr) {
                return json_encode(['status' => 'error', 'message' => 'Product or Item code already exists']);
            }
        }

        $params1 = [
            $user_id,
            $product_name_attr,
        ];

        $params2 = [
            $product_name,
            $product_item,
            $product_unit,
            $product_weight,
            $product_price,
            $user_id,
            $user_id,
            $product_id,
            $product_tag
        ];

        $update = $this->ProductModel->update_product($params1, $params2);
        if ($update['status'] === 'failed') {
            return json_encode(['status' => 'error', 'message' => 'Failed to update product']);
        }
        return json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
    }

    public function active_inactive()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');
        
        $product_id = $this->request->getPost('product_id');
        $is_active = $this->request->getPost('is_active');

        $params = [
            $is_active,
            $user_id,
            $product_id
        ];

        $update = $this->ProductModel->active_inactive_product($params);
        if ($update === 'success') {
            return json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
        } else {
            return json_encode(['status' => 'error', 'message' => $update]);
        }
    }

    public function get_custom_filters()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }

        $filters = $this->ProductModel->get_product_custom_filters();
        return $this->response->setJSON([
            'success' => true,
            'filters' => $filters,
        ]);
    }

    public function save_product_cost()
    {
        if (!$this->hasAccess()) {
            return redirect()->to(base_url('access-forbidden'));
        }
        $session = session();
        $user_id = $session->get('user_id');

        $data = $this->request->getJSON(true);

        $product_id = $data['product_id'];
        $cost = $data['cost'];

        if (empty($product_id) || $cost === '' || $cost === null) {
            return json_encode(['status' => 'error', 'message' => 'Product ID and cost are required']);
        }

        // Check if there's an existing cost for this product
        $existing = $this->ProductModel->get_existing_product_cost($product_id);

        // Insert new cost (model handles archiving old one if it exists)
        $result = $this->ProductModel->insert_product_cost($product_id, $cost, $user_id, $existing);

        return json_encode($result);
        // return json_encode($existing);
    }
}
