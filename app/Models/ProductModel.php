<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    public function get_products()
    {
        try {
            $query = "SELECT p.*, pc.cost as product_cost_value
                      FROM products p
                      LEFT JOIN product_cost pc ON p.product_id = pc.product_id AND pc.is_old = 0
                      WHERE p.is_old = 0";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function check_product_exists($product_name, $product_item)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM products WHERE (product_name = ? OR product_item = ?) AND is_old = 0";
            return $this->db->query($query, [$product_name, $product_item])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function edit_check_product_exists($product_name, $product_item, $product_id)
    {
        try {
            $query = "SELECT id FROM products WHERE (product_name = ? OR product_item = ?) AND is_old = 0 AND id != ?";
            return $this->db->query($query, [$product_name, $product_item, $product_id])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function insert_product($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "INSERT INTO products (
                product_name,
                product_item,
                product_unit,
                product_weight,
                product_price,
                creator_id,
                updater_id,
                is_old,
                is_active,
                product_id,
                product_tag
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)";

            $this->db->query($query, $params);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'Product added successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update_product($params1, $params2)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query_1 = "UPDATE products SET 
            is_old = 1,
            updater_id = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

            $this->db->query($query_1, $params1);

            $query2 = "INSERT INTO products (
                product_name,
                product_item,
                product_unit,
                product_weight,
                product_price,
                creator_id,
                updater_id,
                is_old,
                is_active,
                product_id,
                product_tag
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)";

            $this->db->query($query2, $params2);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'Product updated successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function active_inactive_product($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "UPDATE products SET 
                is_active = ?,
                updater_id = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE product_id = ?";

            $this->db->query($query, $params);


            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return 'failed';
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return 'success';
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return $e->getMessage();
        }
    }

    public function get_product_custom_filters() {
        try {
            $query = "SELECT 
                        id,
                        filter_name
                    FROM
                        product_filter";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_existing_product_cost($product_id)
    {
        try {
            $query = "SELECT * FROM product_cost WHERE product_id = ? AND is_old = 0";
            return $this->db->query($query, [$product_id])->getRow();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function insert_product_cost($product_id, $cost, $creator_id, $existing)
    {
        try {
            $this->db->transStart();

            // If there's an existing cost, mark it as old and set archive_date (date only)
            if ($existing) {
                $archive_query = "UPDATE product_cost SET 
                    is_old = 1,
                    archive_date = CURDATE(),
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
                $this->db->query($archive_query, [$existing->id]);
            }

            // Insert the new cost (date only)
            $insert_query = "INSERT INTO product_cost (
                product_id,
                cost,
                created_date,
                archive_date,
                is_old,
                creator_id
            ) VALUES (?, ?, CURDATE(), NULL, 0, ?)";
            $this->db->query($insert_query, [$product_id, $cost, $creator_id]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'Product cost saved successfully'];
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}