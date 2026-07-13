<?php
namespace App\Models;

use CodeIgniter\Model;

class CoreModel extends Model
{
    protected $db;

    public function __construct()
    {   
        parent::__construct();
        $this->db = \Config\Database::connect(); // <-- important
    }

    public function user_login($username)
    {
        try {
            $query = "SELECT * FROM users WHERE username = ? AND archive = 0";
            return $this->db->query($query, [$username])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_si_receipt_data($id)
    {
        try {
            $query = $this->db->query("SELECT si_receipt_query_fn(?) AS receipt", [$id]);
            $row = $query->getRow();
            $receipt = json_decode($row->receipt, true);
            return $receipt;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_dr_receipt_data($id)
    {
        try {
            $query = $this->db->query("SELECT dr_receipt_query_fn(?) AS receipt", [$id]);
            $row = $query->getRow();
            $receipt = json_decode($row->receipt, true);
            return $receipt;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_role()
    {
        try {
            $query = "SELECT 
                        id,
                        `name` AS user_role
                    FROM
                        roles";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_user()
    {
        try {
            $query = "SELECT 
                        u.id,
                        u.username,
                        u.first_name,
                        u.last_name,
                        u.role_id,
                        archive,
                        r.name AS user_role
                    FROM
                        users u 
                        INNER JOIN roles r ON u.role_id = r.id";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_user_by_id($id)
    {
        try {
            $query = "SELECT u.id, u.username, u.first_name, u.last_name, u.role_id, r.name AS user_role
                      FROM users u INNER JOIN roles r ON u.role_id = r.id
                      WHERE u.id = ? AND u.archive = 0";
            $result = $this->db->query($query, [$id])->getResult();
            return $result ? $result[0] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update_profile($params)
    {
        try {
            $sets = ['first_name = ?', 'last_name = ?'];
            $values = [$params['first_name'], $params['last_name']];
            if (!empty($params['password'])) {
                $sets[] = 'password = ?';
                $values[] = $params['password'];
            }
            $values[] = $params['id'];
            $query = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
            $this->db->query($query, $values);
            return ['status' => 'success', 'message' => 'Profile updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function check_user_exists($username)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
            return $this->db->query($query, [$username])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function insert_user($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "INSERT INTO users (
                username,
                `password`,
                first_name,
                last_name,
                archive,
                creator_id,
                updater_id,
                role_id
            ) VALUES (?, ?, ?, ?, 0, ?, ?, ?)";

            $this->db->query($query, $params);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'User added successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update_user($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $sql = "UPDATE users SET first_name = ?, last_name = ?, role_id = ?, updater_id = ?";

            // Check if password is provided (5th parameter in the array)
            if (!empty($params['password'])) {
                $sql .= ", password = ?";
            }

            $sql .= " WHERE id = ?";
            $queryParams = [
                $params['first_name'],
                $params['last_name'],
                $params['role_id'],
                $params['updater_id']
            ];

            if (!empty($params['password'])) {
                $queryParams[] = $params['password'];
            }

            $queryParams[] = $params['id']; // Add the ID as the last parameter

            $this->db->query($sql, $queryParams);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'User updated successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function archive_user($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "UPDATE users SET 
                        archive = 1, 
                        updater_id = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";

            $this->db->query($query, [$params['updater_id'], $params['id']]);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'User archived successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function activate_user($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "UPDATE users SET 
                        archive = 0, 
                        updater_id = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";

            $this->db->query($query, [$params['updater_id'], $params['id']]);

            $this->db->transComplete(); // Complete Transaction

            if ($this->db->transStatus() === false) {
                // Transaction failed, rollback
                $this->db->transRollback();
                return ['status' => 'failed', 'message' => 'Transaction failed'];
            } else {
                // Transaction successful, commit
                $this->db->transCommit();
                return ['status' => 'success', 'message' => 'User activated successfully'];
            }
        } catch (\Exception $e) {
            // Rollback transaction in case of exception
            $this->db->transRollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

}