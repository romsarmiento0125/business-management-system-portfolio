<?php
namespace App\Models;

use CodeIgniter\Model;

class SiDrDashboardModel extends Model
{
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    public function get_si_dr($date_start, $date_end)
    {
        try {
            $sales_invoice_query = "SELECT 
                si.id AS si_id,
                c.client_name,
                si.client_term,
                si.si_date,
                si.updated_at,
                c.id AS client_id,
                (
                    SUM(
                        (siil.si_item_price * siil.si_item_qty) - COALESCE(siil.si_item_qty * si_disc_agg.total_item_discount, 0)
                    ) + si.freight_cost
                ) AS total_amount,
                si.si_paid,
                si.si_status
            FROM
                sales_invoice si
                    LEFT JOIN
                sales_invoice_items_list siil ON siil.si_id = si.id
                    LEFT JOIN
                (SELECT 
                    si_item_id, SUM(discount) AS total_item_discount
                FROM
                    sales_invoice_items_list_discount
                GROUP BY si_item_id) si_disc_agg ON si_disc_agg.si_item_id = siil.id
                    LEFT JOIN
                clients c ON c.id = si.client_id
            WHERE
                (si.si_status = 'printed' OR si.si_status = 'cancelled')
                AND si.si_date BETWEEN ? AND ?
            GROUP BY si.id , c.client_name , si.si_date , si.updated_at , c.id , si.si_paid , si.si_status";
            
            $delivery_receipts_query = "SELECT 
                    dr.id AS dr_id,
                    c.client_name,
                    dr.client_term,
                    dr.dr_date,
                    dr.updated_at,
                    c.id AS client_id,
                    (
                        SUM(
                            (dril.dr_item_price * dril.dr_item_qty) - COALESCE(dril.dr_item_qty * dr_disc_agg.total_item_discount, 0)
                        ) + dr.freight_cost
                    ) AS total_amount,
                    dr.dr_paid,
                    dr.dr_status
                FROM
                    delivery_receipt dr
                        LEFT JOIN
                    delivery_receipt_items_list dril ON dril.dr_id = dr.id
                        LEFT JOIN
                    (SELECT 
                        dr_item_id, SUM(discount) AS total_item_discount
                    FROM
                        delivery_receipt_items_list_discount
                    GROUP BY dr_item_id) dr_disc_agg ON dr_disc_agg.dr_item_id = dril.id
                        LEFT JOIN
                    clients c ON c.id = dr.client_id
                WHERE
                    (dr.dr_status = 'printed' OR dr.dr_status = 'cancelled')
                        AND dr.dr_date BETWEEN ? AND ?
                GROUP BY dr.id , c.client_name , dr.dr_date , dr.updated_at , c.id , dr.dr_paid";

            $sales_invoice = $this->db->query($sales_invoice_query, [$date_start, $date_end])->getResult();
            $delivery_receipts = $this->db->query($delivery_receipts_query, [$date_start, $date_end])->getResult();

            return ['sales_invoice' => $sales_invoice, 'delivery_receipts' => $delivery_receipts];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function update_si_dr_payment($data, $sessionUsername)
    {
        try {
            $type = $data->type;
            $id = $data->id;
            $username = $data->username;
            $password = $data->password;

            // Only the currently logged-in user's own credentials are accepted
            if (mb_strtolower($username) !== mb_strtolower($sessionUsername)) {
                return ['status' => 'error', 'message' => 'You may only use your own account credentials'];
            }

            // Validate user with role 1, 2, or 5
            $user = $this->db->query(
                "SELECT * FROM users WHERE username = ? AND role_id IN (1,2,5) LIMIT 1",
                [$username]
            )->getRow();

            if (!$user || !password_verify($password, $user->password)) {
                return ['status' => 'error', 'message' => 'Invalid credentials or insufficient permissions'];
            }

            if ($type === 'si') {
                $this->db->query("UPDATE sales_invoice SET si_paid = IF(si_paid = 1, 0, 1) WHERE id = ?", [$id]);
            } elseif ($type === 'dr') {
                $this->db->query("UPDATE delivery_receipt SET dr_paid = IF(dr_paid = 1, 0, 1) WHERE id = ?", [$id]);
            }

            return ['status' => 'success', 'message' => 'Payment updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    public function get_paid_si($date_start, $date_end)
    {
        try {
            $sql = "SELECT 
                        SUM(receipt_total) AS total_amount_sum
                    FROM
                        (SELECT 
                            si.id AS si_id,
                                SUM(
                                    (siil.si_item_price * siil.si_item_qty) - COALESCE(siil.si_item_qty * (
                                        SELECT 
                                            SUM(discount)
                                        FROM
                                            sales_invoice_items_list_discount disc
                                        WHERE
                                            disc.si_item_id = siil.id), 0)) + si.freight_cost AS receipt_total
                        FROM
                            sales_invoice si
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                        WHERE
                            si.si_paid = 1
                            AND si_date BETWEEN ? AND ?
                                AND si.si_status = 'printed'
                        GROUP BY si.id) AS invoice_totals";
            $query = $this->db->query($sql, [$date_start, $date_end]);
            $row = $query->getRow();
            return $row->total_amount_sum;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_unpaid_si($date_start, $date_end)
    {
        try {
            $sql = "SELECT 
                        SUM(receipt_total) AS total_amount_sum
                    FROM
                        (SELECT 
                            si.id AS si_id,
                                SUM(
                                    (siil.si_item_price * siil.si_item_qty) - COALESCE(siil.si_item_qty * (
                                        SELECT 
                                            SUM(discount)
                                        FROM
                                            sales_invoice_items_list_discount disc
                                        WHERE
                                            disc.si_item_id = siil.id), 0)) + si.freight_cost AS receipt_total
                        FROM
                            sales_invoice si
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                        WHERE
                            si.si_paid = 0
                            AND si_date BETWEEN ? AND ?
                                AND si.si_status = 'printed'
                        GROUP BY si.id) AS invoice_totals";
            $query = $this->db->query($sql, [$date_start, $date_end]);
            $row = $query->getRow();
            return $row->total_amount_sum;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_paid_dr($date_start, $date_end)
    {
        try {
            $sql = "SELECT 
                        SUM(receipt_total) AS total_amount_sum
                    FROM
                        (SELECT 
                            dr.id AS dr_id,
                                SUM(
                                    (dril.dr_item_price * dril.dr_item_qty) - COALESCE(dril.dr_item_qty * (
                                            SELECT 
                                                SUM(discount)
                                            FROM 
                                                delivery_receipt_items_list_discount disc
                                            WHERE 
                                                disc.dr_item_id = dril.id), 0)) + dr.freight_cost AS receipt_total
                            FROM
                                delivery_receipt dr
                            LEFT JOIN delivery_receipt_items_list dril ON dril.dr_id = dr.id
                            WHERE
                                dr.dr_paid = 1
                                AND dr_date BETWEEN ? AND ? 
                                AND dr.dr_status = 'printed'
                            GROUP BY dr.id
                        ) AS receipt_totals";
            $query = $this->db->query($sql, [$date_start, $date_end]);
            $row = $query->getRow();
            return $row->total_amount_sum;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_unpaid_dr($date_start, $date_end)
    {
        try {
            $sql = "SELECT 
                        SUM(receipt_total) AS total_amount_sum
                    FROM
                        (SELECT 
                            dr.id AS dr_id,
                                SUM(
                                    (dril.dr_item_price * dril.dr_item_qty) - COALESCE(dril.dr_item_qty * (
                                            SELECT 
                                                SUM(discount)
                                            FROM 
                                                delivery_receipt_items_list_discount disc
                                            WHERE 
                                                disc.dr_item_id = dril.id), 0)) + dr.freight_cost AS receipt_total
                            FROM
                                delivery_receipt dr
                            LEFT JOIN delivery_receipt_items_list dril ON dril.dr_id = dr.id
                            WHERE
                                dr.dr_paid = 0
                                AND dr_date BETWEEN ? AND ? 
                                AND dr.dr_status = 'printed'
                            GROUP BY dr.id
                        ) AS receipt_totals";
            $query = $this->db->query($sql, [$date_start, $date_end]);
            $row = $query->getRow();
            return $row->total_amount_sum;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}