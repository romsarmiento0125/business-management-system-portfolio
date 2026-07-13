<?php
namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

        public function get_clients()
    {
        try {
            $query = "SELECT * FROM clients WHERE is_old = 0";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function check_client_exists($client_name)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM clients WHERE client_name = ? AND is_old = 0";
            return $this->db->query($query, [$client_name])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function check_edit_client_exists($client_name, $client_name_attr)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM clients WHERE client_name = ? AND is_old = 0 AND id != ?";
            return $this->db->query($query, [$client_name, $client_name_attr])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function insert_client($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "INSERT INTO clients (
                client_name,
                client_tin,
                client_address,
                client_business_name,
                client_term,
                creator_id,
                updater_id,
                is_old,
                is_active,
                client_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)";

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

    public function update_client($params1, $params2)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query_1 = "UPDATE clients SET 
                is_old = 1,
                updater_id = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

            $this->db->query($query_1, $params1);

            $query_2 = "INSERT INTO clients (
                client_name,
                client_tin,
                client_address,
                client_business_name,
                client_term,
                creator_id,
                updater_id,
                is_old,
                is_active,
                client_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)";

            $this->db->query($query_2, $params2);

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

    public function active_inactive_client($params)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query = "UPDATE clients SET 
                is_active = ?,
                updater_id = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE client_id = ?";

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

    public function get_client_custom_filters() {
        try {
            $query = "SELECT 
                        id,
                        filter_name
                    FROM
                        client_filters";
            return $this->db->query($query)->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_client_volume($client_id, $date_from, $date_to, $type, $product_filter_id = null)
    {
        try {
            $productFilterCondition = '';
            if (!empty($product_filter_id) && (int)$product_filter_id > 0) {
                $pfId = (int)$product_filter_id;
                $productFilterCondition = "AND p.product_id IN (SELECT pfi.product_id FROM product_filter pf LEFT JOIN product_filter_items pfi ON pfi.filter_name_id = pf.id WHERE pf.id = {$pfId})";
            }

            if ($type === 'si') {
                $query = "SELECT 
                            COALESCE(SUM(siil.si_item_qty), 0) AS grand_total_qty
                          FROM clients c
                          INNER JOIN sales_invoice si ON si.client_id = c.id
                          INNER JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                          INNER JOIN products p ON p.id = siil.si_product_id
                          WHERE c.client_id = ?
                            AND (si.si_date BETWEEN ? AND ?)
                            AND si.si_status = 'printed'
                            {$productFilterCondition}";
                $row = $this->db->query($query, [$client_id, $date_from, $date_to])->getRow();
                return $row ? (float) $row->grand_total_qty : 0;

            } elseif ($type === 'dr') {
                $query = "SELECT 
                            COALESCE(SUM(dril.dr_item_qty), 0) AS grand_total_qty
                          FROM clients c
                          INNER JOIN delivery_receipt dr ON dr.client_id = c.id
                          INNER JOIN delivery_receipt_items_list dril ON dril.dr_id = dr.id
                          INNER JOIN products p ON p.id = dril.dr_product_id
                          WHERE c.client_id = ?
                            AND (dr.dr_date BETWEEN ? AND ?)
                            AND dr.dr_status = 'printed'
                            {$productFilterCondition}";
                $row = $this->db->query($query, [$client_id, $date_from, $date_to])->getRow();
                return $row ? (float) $row->grand_total_qty : 0;

            } elseif ($type === 'sidr') {
                $si_query = "SELECT 
                                COALESCE(SUM(siil.si_item_qty), 0) AS grand_total_qty
                             FROM clients c
                             INNER JOIN sales_invoice si ON si.client_id = c.id
                             INNER JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                             INNER JOIN products p ON p.id = siil.si_product_id
                             WHERE c.client_id = ?
                               AND (si.si_date BETWEEN ? AND ?)
                               AND si.si_status = 'printed'
                               {$productFilterCondition}";
                $si_row   = $this->db->query($si_query, [$client_id, $date_from, $date_to])->getRow();
                $si_total = $si_row ? (float) $si_row->grand_total_qty : 0;

                $dr_query = "SELECT 
                                COALESCE(SUM(dril.dr_item_qty), 0) AS grand_total_qty
                             FROM clients c
                             INNER JOIN delivery_receipt dr ON dr.client_id = c.id
                             INNER JOIN delivery_receipt_items_list dril ON dril.dr_id = dr.id
                             INNER JOIN products p ON p.id = dril.dr_product_id
                             WHERE c.client_id = ?
                               AND (dr.dr_date BETWEEN ? AND ?)
                               AND dr.dr_status = 'printed'
                               {$productFilterCondition}";
                $dr_row   = $this->db->query($dr_query, [$client_id, $date_from, $date_to])->getRow();
                $dr_total = $dr_row ? (float) $dr_row->grand_total_qty : 0;

                return $si_total + $dr_total;
            }

            return 'Invalid type';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}