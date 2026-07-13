<?php
namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    public function get_dashboard_data($params)
    {
        try {
            
            $get_dr_total_amount = "SELECT 
                    SUM(receipt_total) AS total_amount_sum
                FROM
                    (
                        SELECT 
                            dr.id AS dr_id,
                            SUM(
                                (dril.dr_item_price * dril.dr_item_qty)
                                - COALESCE(
                                    dril.dr_item_qty * (
                                        SELECT SUM(discount)
                                        FROM delivery_receipt_items_list_discount disc
                                        WHERE disc.dr_item_id = dril.id
                                    ), 0
                                )
                            ) AS receipt_total
                        FROM
                            delivery_receipt dr
                        LEFT JOIN delivery_receipt_items_list dril ON dril.dr_id = dr.id
                        WHERE
                            dr_date BETWEEN ? AND ? 
                            AND dr.dr_status = 'printed'
                        GROUP BY dr.id
                    ) AS receipt_totals";

            $get_dr_total_freight = "SELECT 
                    SUM(freight_cost) AS dr_total_freight_sum
                FROM
                    delivery_receipt
                WHERE
                    dr_date BETWEEN ? AND ? 
                    AND dr_status = 'printed'";
        
        // --------------------------------------------------------------

            $get_si_total_amount = "SELECT 
                    SUM(receipt_total) AS total_amount_sum
                FROM (
                    SELECT 
                        si.id AS si_id,
                        SUM(
                            (siil.si_item_price * siil.si_item_qty)
                            - COALESCE(
                                siil.si_item_qty * (
                                    SELECT SUM(discount)
                                    FROM sales_invoice_items_list_discount disc
                                    WHERE disc.si_item_id = siil.id
                                ), 0
                            )
                        ) AS receipt_total
                    FROM
                        sales_invoice si
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                    WHERE
                        si_date BETWEEN ? AND ?
                        AND si.si_status = 'printed'
                    GROUP BY si.id
                ) AS invoice_totals";
            
            $get_si_total_freight = "SELECT 
                    SUM(freight_cost) AS si_total_freight_sum
                FROM
                    sales_invoice
                WHERE
                    si_date BETWEEN ? AND ?
                    AND si_status = 'printed'";

        // --------------------------------------------------------------

            $get_si_total_vatable_sales = "SELECT 
                    SUM(receipt_total / 1.12) AS vatable_sales_sum
                FROM (
                    SELECT 
                        si.id AS si_id,
                        SUM(
                            (siil.si_item_price * siil.si_item_qty)
                            - COALESCE(
                                siil.si_item_qty * (
                                    SELECT SUM(discount)
                                    FROM sales_invoice_items_list_discount disc
                                    WHERE disc.si_item_id = siil.id
                                ), 0
                            )
                        ) AS receipt_total
                    FROM
                        sales_invoice si
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                    WHERE
                        si_date BETWEEN ? AND ?
                        AND si.si_status = 'printed'
                        AND siil.si_item_vat_check = 1
                    GROUP BY si.id
                ) AS invoice_totals";

        // --------------------------------------------------------------

            $get_si_total_vat_exempt_sales = "SELECT 
                    SUM(receipt_total) AS vat_exempt_sales_sum
                FROM (
                    SELECT 
                        si.id AS si_id,
                        SUM(
                            (siil.si_item_price * siil.si_item_qty)
                            - COALESCE(
                                siil.si_item_qty * (
                                    SELECT SUM(discount)
                                    FROM sales_invoice_items_list_discount disc
                                    WHERE disc.si_item_id = siil.id
                                ), 0
                            )
                        ) AS receipt_total
                    FROM
                        sales_invoice si
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                    WHERE
                        si_date BETWEEN ? AND ?
                        AND si.si_status = 'printed'
                        AND siil.si_item_vat_check = 0
                    GROUP BY si.id
                ) AS invoice_totals";
            
        // --------------------------------------------------------------

            $get_si_total_vat_amount = "SELECT 
                    SUM(receipt_total) - SUM(receipt_total / 1.12) AS vat_amount_sum
                FROM
                    (SELECT 
                        si.id AS si_id,
                            SUM((siil.si_item_price * siil.si_item_qty) - COALESCE(siil.si_item_qty * (SELECT 
                                    SUM(discount)
                                FROM
                                    sales_invoice_items_list_discount disc
                                WHERE
                                    disc.si_item_id = siil.id), 0)) AS receipt_total
                    FROM
                        sales_invoice si
                    LEFT JOIN sales_invoice_items_list siil ON siil.si_id = si.id
                    WHERE
                        si_date BETWEEN ? AND ?
                            AND si.si_status = 'printed'
                            AND siil.si_item_vat_check = 1
                    GROUP BY si.id) AS invoice_totals";
            
        // --------------------------------------------------------------

            $get_dr_gains = "SELECT 
                    SUM(
                        (dril.dr_item_qty * (dril.dr_item_price - COALESCE(disc_totals.total_discount, 0))) - 
                        (dril.dr_item_qty * COALESCE(
                            (SELECT pc1.cost
                            FROM product_cost pc1
                            WHERE pc1.product_id = p.product_id
                            AND (
                                (dr.dr_date >= pc1.created_date AND (pc1.archive_date IS NULL OR dr.dr_date <= pc1.archive_date))
                                OR (SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id) = 1
                            )
                            ORDER BY pc1.created_date DESC
                            LIMIT 1), 0)
                        )
                    ) AS grand_total_income
                FROM
                    delivery_receipt_items_list dril
                JOIN 
                    delivery_receipt dr ON dr.id = dril.dr_id
                JOIN 
                    products p ON dril.dr_product_id = p.id
                LEFT JOIN (
                    SELECT 
                        dr_item_id, 
                        SUM(discount) AS total_discount
                    FROM delivery_receipt_items_list_discount
                    GROUP BY dr_item_id
                ) AS disc_totals ON disc_totals.dr_item_id = dril.id
                WHERE
                    dr.dr_status = 'printed'
                    AND dr.dr_date BETWEEN ? AND ?";

            // --------------------------------------------------------------

            $get_si_gains = "SELECT 
                    SUM(
                        (siil.si_item_qty * (siil.si_item_price - COALESCE(disc_totals.total_discount, 0))) - 
                        (siil.si_item_qty * COALESCE(
                            (SELECT pc1.cost
                            FROM product_cost pc1
                            WHERE pc1.product_id = p.product_id
                            AND (
                                (si.si_date >= pc1.created_date AND (pc1.archive_date IS NULL OR si.si_date <= pc1.archive_date))
                                OR (SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id) = 1
                            )
                            ORDER BY pc1.created_date DESC
                            LIMIT 1), 0)
                        )
                    ) AS grand_total_income
                FROM
                    sales_invoice_items_list siil
                JOIN 
                    sales_invoice si ON si.id = siil.si_id
                JOIN 
                    products p ON siil.si_product_id = p.id
                LEFT JOIN (
                    SELECT 
                        si_item_id, 
                        SUM(discount) AS total_discount
                    FROM sales_invoice_items_list_discount
                    GROUP BY si_item_id
                ) AS disc_totals ON disc_totals.si_item_id = siil.id
                WHERE
                    si.si_status = 'printed'
                    AND si.si_date BETWEEN ? AND ?";

            // Ensure $params contains two separate values (start_date and end_date)
            $dr_total_amount = $this->db->query($get_dr_total_amount, [$params['start_date'], $params['end_date']])->getRow();
            $dr_total_freight = $this->db->query($get_dr_total_freight, [$params['start_date'], $params['end_date']])->getRow();
            $si_total_amount = $this->db->query($get_si_total_amount, [$params['start_date'], $params['end_date']])->getRow();
            $si_total_freight = $this->db->query($get_si_total_freight, [$params['start_date'], $params['end_date']])->getRow();
            $si_total_vatable_sales = $this->db->query($get_si_total_vatable_sales, [$params['start_date'], $params['end_date']])->getRow();
            $si_total_vat_exempt_sales = $this->db->query($get_si_total_vat_exempt_sales, [$params['start_date'], $params['end_date']])->getRow();
            $si_total_vat_amount = $this->db->query($get_si_total_vat_amount, [$params['start_date'], $params['end_date']])->getRow();
            $dr_gains = $this->db->query($get_dr_gains, [$params['start_date'], $params['end_date']])->getRow();
            $si_gains = $this->db->query($get_si_gains, [$params['start_date'], $params['end_date']])->getRow();

            return [
                'dr_total_amount' => $dr_total_amount->total_amount_sum,
                'dr_total_freight' => $dr_total_freight->dr_total_freight_sum,
                'si_total_amount' => $si_total_amount->total_amount_sum,
                'si_total_freight' => $si_total_freight->si_total_freight_sum,
                'si_total_vatable_sales' => $si_total_vatable_sales->vatable_sales_sum,
                'si_total_vat_exempt_sales' => $si_total_vat_exempt_sales->vat_exempt_sales_sum,
                'si_total_vat_amount' => $si_total_vat_amount->vat_amount_sum,
                'dr_gains' => $dr_gains->grand_total_income,
                'si_gains' => $si_gains->grand_total_income,
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function export_accounting_total($params)
    {
        try {
            
            $get_si_export_data = "SELECT 
                                        si.si_date AS si_dr_date,
                                        c.client_name,
                                        c.client_address,
                                        c.client_tin,
                                        si.id AS si_id,
                                        '' AS dr_id,
                                        COALESCE(SUM((siil.si_item_price * siil.si_item_qty) - COALESCE(siil.si_item_qty * si_disc_agg.total_item_discount,
                                                        0)),
                                                0) + COALESCE(si.freight_cost, 0) AS total_amount,
                                        si.si_status AS si_dr_status,
                                        siil.si_item_vat_check AS vat_check
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
                                        si.si_status IN ('printed', 'cancelled')
                                            AND
                                        si.si_date BETWEEN ? AND ?
                                    GROUP BY si.si_date, c.client_name, c.client_address, c.client_tin, si.id, si.si_status, siil.si_item_vat_check";
        
// --------------------------------------------------------------

            $get_dr_export_data = "SELECT 
                                        dr.dr_date AS si_dr_date,
                                        c.client_name,
                                        c.client_address,
                                        c.client_tin,
                                        '' AS si_id,
                                        dr.id AS dr_id,
                                        SUM((dril.dr_item_price * dril.dr_item_qty) - COALESCE(dril.dr_item_qty * dr_disc_agg.total_item_discount,
                                                0)) + COALESCE(dr.freight_cost, 0) AS total_amount,
                                        dr.dr_status AS si_dr_status,
                                        0 AS vat_check
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
                                        dr.dr_status IN ('printed', 'cancelled')
                                            AND 
                                        dr.dr_date BETWEEN ? AND ?
                                    GROUP BY dr.dr_date, c.client_name, c.client_address, c.client_tin, dr.id , dr.dr_status";

            // Ensure $params contains two separate values (start_date and end_date)
            $si_export_data = $this->db->query($get_si_export_data, [$params['start_date'], $params['end_date']])->getResultArray();
            $dr_export_data = $this->db->query($get_dr_export_data, [$params['start_date'], $params['end_date']])->getResultArray();
            
            return [
                'si_export_data' => $si_export_data,
                'dr_export_data' => $dr_export_data,
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}