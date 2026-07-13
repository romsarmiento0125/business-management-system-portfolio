<?php
namespace App\Models;

use CodeIgniter\Model;

class DynamicFilterModel extends Model
{
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    public function get_clients()
    {
        try {
            $clientsQuery = "SELECT * FROM clients WHERE is_old = 0 AND is_active = 0";

            $clients = $this->db->query($clientsQuery)->getResult();

            return ['clients' => $clients];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // Fetch list of client filters for a given user
    public function get_client_filters($userId)
    {
        try {
            $sql = "SELECT id, filter_name
                    FROM client_filters
                    ORDER BY id DESC";
            return $this->db->query($sql, [$userId])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function save_client_filter(array $data, $userId = null)
    {
        try {
            $filterName = trim($data['filter_name'] ?? '');
            $clients = $data['clients'] ?? [];

            // Uniqueness check (per user, case-insensitive)
            if ($this->client_filter_name_exists($userId, $filterName)) {
                return 'Filter name already exists.';
            }

            $this->db->transStart();

            // Insert parent filter record (DB handles timestamps)
            $this->db->table('client_filters')->insert([
                'filter_name' => $filterName,
                'user_id' => $userId,
            ]);

            $filterId = (int) $this->db->insertID();

            // Prepare batch items (only fields defined in the final schema)
            $batch = [];
            foreach ($clients as $c) {
                $batch[] = [
                    'filter_name_id' => $filterId,
                    'client_id' => isset($c['id']) ? (string) $c['id'] : null,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('client_filter_items')->insertBatch($batch);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to save client filter.');
            }

            return ['filter_id' => $filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    function get_client_filter_items($filterId)
    {
        try {
            $sql = "SELECT 
                        cf.id AS filter_id,
                        cf.filter_name,
                        c.client_id,
                        c.client_name,
                        c.client_address
                    FROM client_filters cf
                    INNER JOIN client_filter_items cfi ON cfi.filter_name_id = cf.id
                    INNER JOIN clients c ON c.client_id = cfi.client_id
                    WHERE cf.id = ? AND c.is_old = 0";
            return $this->db->query($sql, [$filterId])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    // Check if a filter name already exists for the user (case-insensitive)
    private function client_filter_name_exists($userId, $filterName, $excludeFilterId = null)
    {
        try {
            $params = [$userId, $filterName];
            $sql = "SELECT COUNT(*) AS cnt
                    FROM client_filters
                    WHERE user_id = ? AND LOWER(filter_name) = LOWER(?)";
            if (!empty($excludeFilterId)) {
                $sql .= " AND id <> ?";
                $params[] = $excludeFilterId;
            }
            $row = $this->db->query($sql, $params)->getRow();
            return ($row && (int)$row->cnt > 0);
        } catch (\Exception $e) {
            // On query error, be conservative and assume it exists to prevent duplicates
            return true;
        }
    }

    // Update existing client filter (name and items)
    public function update_client_filter($filterId, $userId, $filterName, array $clients)
    {
        try {
            $filterName = trim($filterName ?? '');
            if ($filterName === '') {
                return 'Filter name is required.';
            }
            if (empty($clients)) {
                return 'At least one client is required.';
            }

            // Uniqueness check excluding current filter id
            if ($this->client_filter_name_exists($userId, $filterName, $filterId)) {
                return 'Filter name already exists.';
            }

            $this->db->transStart();

            // Update parent record (scoped by user_id for safety)
            $sqlUpdate = "UPDATE client_filters SET filter_name = ? WHERE id = ? AND user_id = ?";
            $this->db->query($sqlUpdate, [$filterName, $filterId, $userId]);

            // Replace items
            $sqlDelete = "DELETE FROM client_filter_items WHERE filter_name_id = ?";
            $this->db->query($sqlDelete, [$filterId]);

            $sqlInsert = "INSERT INTO client_filter_items (filter_name_id, client_id) VALUES (?, ?)";
            foreach ($clients as $c) {
                $clientId = isset($c['id']) ? (string)$c['id'] : (isset($c['client_id']) ? (string)$c['client_id'] : null);
                if ($clientId === null || $clientId === '') continue;
                $this->db->query($sqlInsert, [$filterId, $clientId]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to update client filter.');
            }

            return ['filter_id' => (int)$filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    // Delete a client filter (and its items) for the given user
    public function delete_client_filter($filterId)
    {
        try {
            $this->db->transStart();

            // With ON DELETE CASCADE on client_filter_items.filter_name_id -> client_filters.id,
            // deleting the parent will automatically delete the children.
            $sqlDeleteParent = "DELETE FROM client_filters WHERE id = ?";
            $this->db->query($sqlDeleteParent, [$filterId]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to delete client filter.');
            }

            return ['filter_id' => (int)$filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    // product

    public function get_products()
    {
        try {
            $productsQuery = "SELECT * FROM products WHERE is_old = 0 AND is_active = 0";

            $products = $this->db->query($productsQuery)->getResult();

            return ['products' => $products];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // Fetch list of product filters for a given user
    public function get_product_filters($userId)
    {
        try {
            $sql = "SELECT id, filter_name
                    FROM product_filter
                    ORDER BY id DESC";
            return $this->db->query($sql, [$userId])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function save_product_filter(array $data, $userId = null)
    {
        try {
            $filterName = trim($data['filter_name_product'] ?? '');
            $products = $data['products'] ?? [];

            // Uniqueness check (per user, case-insensitive)
            if ($this->product_filter_name_exists($userId, $filterName)) {
                return 'Filter name already exists.';
            }

            $this->db->transStart();

            // Insert parent filter record (DB handles timestamps)
            $this->db->table('product_filter')->insert([
                'filter_name' => $filterName,
                'user_id' => $userId,
            ]);

            $filterId = (int) $this->db->insertID();

            // Prepare batch items (only fields defined in the final schema)
            $batch = [];
            foreach ($products as $p) {
                $batch[] = [
                    'filter_name_id' => $filterId,
                    'product_id' => isset($p['id']) ? (string) $p['id'] : null,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('product_filter_items')->insertBatch($batch);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to save product filter.');
            }

            return ['filter_id' => $filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    function get_product_filter_items($filterId)
    {
        try {
            $sql = "SELECT 
                        pf.id AS filter_id,
                        pf.filter_name,
                        p.product_id,
                        p.product_name,
                        p.product_item
                    FROM product_filter pf
                    INNER JOIN product_filter_items pfi ON pfi.filter_name_id = pf.id
                    INNER JOIN products p ON p.product_id = pfi.product_id
                    WHERE pf.id = ? AND p.is_old = 0";
            return $this->db->query($sql, [$filterId])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // Check if a filter name already exists for the user (case-insensitive)
    private function product_filter_name_exists($userId, $filterName, $excludeFilterId = null)
    {
        try {
            $params = [$userId, $filterName];
            $sql = "SELECT COUNT(*) AS pnt
                    FROM product_filter
                    WHERE user_id = ? AND LOWER(filter_name) = LOWER(?)";
            if (!empty($excludeFilterId)) {
                $sql .= " AND id <> ?";
                $params[] = $excludeFilterId;
            }
            $row = $this->db->query($sql, $params)->getRow();
            return ($row && (int)$row->pnt > 0);
        } catch (\Exception $e) {
            // On query error, be conservative and assume it exists to prevent duplicates
            return true;
        }
    }

    // Update existing client filter (name and items)
    public function update_product_filter($filterId, $userId, $filterName, array $products)
    {
        try {
            $filterName = trim($filterName ?? '');
            if ($filterName === '') {
                return 'Filter name is required.';
            }
            if (empty($products)) {
                return 'At least one product is required.';
            }

            // Uniqueness check excluding current filter id
            if ($this->product_filter_name_exists($userId, $filterName, $filterId)) {
                return 'Filter name already exists.';
            }

            $this->db->transStart();

            // Update parent record (scoped by user_id for safety)
            $sqlUpdate = "UPDATE product_filter SET filter_name = ? WHERE id = ? AND user_id = ?";
            $this->db->query($sqlUpdate, [$filterName, $filterId, $userId]);

            // Replace items
            $sqlDelete = "DELETE FROM product_filter_items WHERE filter_name_id = ?";
            $this->db->query($sqlDelete, [$filterId]);

            $sqlInsert = "INSERT INTO product_filter_items (filter_name_id, product_id) VALUES (?, ?)";
            foreach ($products as $p) {
                $productId = isset($p['id']) ? (string)$p['id'] : (isset($p['product_id']) ? (string)$p['product_id'] : null);
                if ($productId === null || $productId === '') continue;
                $this->db->query($sqlInsert, [$filterId, $productId]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to update product filter.');
            }

            return ['filter_id' => (int)$filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    // Delete a client filter (and its items) for the given user
    public function delete_product_filter($filterId)
    {
        try {
            $this->db->transStart();

            // With ON DELETE CASCADE on product_filter_items.filter_name_id -> product_filter.id,
            // deleting the parent will automatically delete the children.
            $sqlDeleteParent = "DELETE FROM product_filter WHERE id = ?";
            $this->db->query($sqlDeleteParent, [$filterId]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to delete client filter.');
            }

            return ['filter_id' => (int)$filterId];
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}