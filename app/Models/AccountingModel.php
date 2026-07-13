<?php
namespace App\Models;

use CodeIgniter\Model;

class AccountingModel extends Model
{
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    public function get_client_filter() {
        try {
            $query = "SELECT 
                    id,
                    client_id,
                    client_name,
                    client_show
                FROM
                    clients
                WHERE is_old = 0 AND client_show = 1";
            return $this->db->query($query)->getResultArray();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function get_product_filter() {
        try {
            $query = "SELECT 
                    id,
                    product_id,
                    product_name,
                    product_item,
                    product_price,
                    product_show
                FROM
                    products
                WHERE is_old = 0 AND product_show = 1";
            return $this->db->query($query)->getResultArray();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function get_client_filter_selected() {
        try {
            $query = "SELECT id FROM client_filters WHERE selected = 1";
            return $this->db->query($query);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function get_product_filter_selected() {
        try {
            $query = "SELECT id FROM product_filter WHERE selected = 1";
            return $this->db->query($query);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_si_data_items_accounting( $date_start, $date_end ) {
        try {
            $cf = $this->get_client_filter_selected();
            $pf = $this->get_product_filter_selected();

            // Build the client filter condition
            $clientFilterCondition = "";
            if (!empty($cf) && $cf->getNumRows() > 0) {
                $clientFilterCondition = "AND c.client_id IN (SELECT 
                                    cfi.client_id
                                FROM
                                    client_filters cf
                                        LEFT JOIN
                                    client_filter_items cfi ON cfi.filter_name_id = cf.id
                                WHERE
                                    cf.id = " . $cf->getRow()->id . ")
                                ";
            }

            // Build the product filter condition
            $productFilterCondition = "";
            if (!empty($pf) && $pf->getNumRows() > 0) {
                $productFilterCondition = "AND p.product_id IN (SELECT 
                                    pfi.product_id
                                FROM
                                    product_filter pf
                                        LEFT JOIN
                                    product_filter_items pfi ON pfi.filter_name_id = pf.id
                                WHERE
                                    pf.id = " . $pf->getRow()->id . ")
                                ";
            }

            $query1 = " SELECT 
                            si.id AS siid,
                            c.client_name,
                            si.si_date,
                            si.updated_at,
                            c.id AS client_id,
                            c.client_id AS client_client_id,
                            siil.id AS siilid,
                            siil.si_item_price,
                            siil.si_item_qty,
                            si_disc.discount,
                            si.si_paid,
                            si.si_status,
                            si.freight_cost
                        FROM
                            sales_invoice si
                                LEFT JOIN
                            sales_invoice_items_list siil ON siil.si_id = si.id
                                LEFT JOIN
                            sales_invoice_items_list_discount si_disc ON si_disc.si_item_id = siil.id
                                LEFT JOIN
                            clients c ON c.id = si.client_id
                                LEFT JOIN
                            products p ON p.id = siil.si_product_id
                        WHERE
                            si.si_status IN ('printed', 'cancelled')
                                " . $clientFilterCondition . "
                                " . $productFilterCondition . "
                                AND si.si_date BETWEEN ? AND ?";
            
            $query2 = "SELECT 
                            si.id AS si_id,
                            siil.id AS siil_id,
                            siil.si_item_qty AS qty,
                            siil.si_item_price AS price,
                            siil.si_item_vat_check AS vat_switch,
                            siil.si_unique_id AS unique_id,
                            p.id AS product_id,
                            p.product_id AS product_unique_id,
                            p.product_name AS product_name,
                            p.product_item AS product_code,
                            p.product_unit AS product_unit,
                            si_disc.id AS si_disc_id,
                            si_disc.discount AS discount
                        FROM
                            sales_invoice si
                                INNER JOIN
                            sales_invoice_items_list siil ON siil.si_id = si.id
                                INNER JOIN
                            products p ON p.id = siil.si_product_id
                                LEFT JOIN
                            sales_invoice_items_list_discount si_disc ON si_disc.si_item_id = siil.id
                        WHERE
                            si.si_status IN ('printed', 'cancelled')
                            " . $productFilterCondition . "
                            AND si.si_date BETWEEN ? AND ?
                        ORDER BY si.id , siil.id , si_disc.id";

            $summary_data = [];

            $result1 = $this->db->query($query1, [$date_start, $date_end])->getResultArray();
            $result2 = $this->db->query($query2, [$date_start, $date_end])->getResultArray();

            // Process the results as needed to create the desired structure
            // This is a placeholder for the actual processing logic
            $summary_data['summary'] = $result1;
            $summary_data['items'] = $result2;

            return $summary_data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_dr_data_items_accounting( $date_start, $date_end ) {
        try {
            $cf = $this->get_client_filter_selected();
            $pf = $this->get_product_filter_selected();

            // Build the client filter condition
            $clientFilterCondition = "";
            if (!empty($cf) && $cf->getNumRows() > 0) {
                $clientFilterCondition = "AND c.client_id IN (SELECT 
                                    cfi.client_id
                                FROM
                                    client_filters cf
                                        LEFT JOIN
                                    client_filter_items cfi ON cfi.filter_name_id = cf.id
                                WHERE
                                    cf.id = " . $cf->getRow()->id . ")
                                ";
            }

            // Build the product filter condition
            $productFilterCondition = "";
            if (!empty($pf) && $pf->getNumRows() > 0) {
                $productFilterCondition = "AND p.product_id IN (SELECT 
                                    pfi.product_id
                                FROM
                                    product_filter pf
                                        LEFT JOIN
                                    product_filter_items pfi ON pfi.filter_name_id = pf.id
                                WHERE
                                    pf.id = " . $pf->getRow()->id . ")
                                ";
            }

            $query1 = " SELECT 
                            dr.id AS drid,
                            c.client_name,
                            dr.dr_date,
                            dr.updated_at,
                            c.id AS client_id,
                            c.client_id AS client_client_id,
                            dril.id AS drilid,
                            dril.dr_item_price,
                            dril.dr_item_qty,
                            dr_disc.discount,
                            dr.dr_paid,
                            dr.dr_status,
                            dr.dr_status AS dr_status,
                            dr.freight_cost
                        FROM
                            delivery_receipt dr
                                LEFT JOIN
                            delivery_receipt_items_list dril ON dril.dr_id = dr.id
                                LEFT JOIN
                            delivery_receipt_items_list_discount dr_disc ON dr_disc.dr_item_id = dril.id
                                LEFT JOIN
                            clients c ON c.id = dr.client_id
                                LEFT JOIN
                            products p ON p.id = dril.dr_product_id
                        WHERE
                            dr.dr_status IN ('printed', 'cancelled')
                                " . $clientFilterCondition . "
                                " . $productFilterCondition . "
                                AND dr.dr_date BETWEEN ? AND ?";
            
            $query2 = "SELECT 
                            dr.id AS dr_id,
                            dril.dr_item_qty AS qty,
                            dril.dr_item_price AS price,
                            dril.dr_unique_id AS unique_id,
                            p.id AS product_id,
                            p.product_id AS product_unique_id,
                            p.product_name AS product_name,
                            p.product_item AS product_code,
                            p.product_unit AS product_unit,
                            dr_disc.id AS dr_disc_id,
                            dr_disc.discount AS discount,
                            dr_disc.dr_item_id AS dr_item_id
                        FROM
                            delivery_receipt dr
                                INNER JOIN
                            delivery_receipt_items_list dril ON dril.dr_id = dr.id
                                INNER JOIN
                            products p ON p.id = dril.dr_product_id
                                LEFT JOIN
                            delivery_receipt_items_list_discount dr_disc ON dr_disc.dr_item_id = dril.id
                        WHERE
                            dr.dr_status IN ('printed', 'cancelled')
                            " . $productFilterCondition . "
                                AND dr.dr_date BETWEEN ? AND ?
                        ORDER BY dr.id , dril.id , dr_disc.id";

            $summary_data = [];

            $result1 = $this->db->query($query1, [$date_start, $date_end])->getResultArray();
            $result2 = $this->db->query($query2, [$date_start, $date_end])->getResultArray();

            // Process the results as needed to create the desired structure
            // This is a placeholder for the actual processing logic
            $summary_data['summary'] = $result1;
            $summary_data['items'] = $result2;

            return $summary_data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function update_clients_by_filter_id($filter_id)
    {
        try {
            // If filter_id is 0, show all clients (set client_show = 1 for non-old records)
            if ((int)$filter_id === 0) {
                $sqlAll = "UPDATE clients SET client_show = 1 WHERE is_old = 0";
                $sql_clear_filter = "UPDATE client_filters SET selected = 0";
                $this->db->query($sqlAll);
                $this->db->query($sql_clear_filter);
                return true;
            }
            $sql = "UPDATE clients c
                    LEFT JOIN (
                        SELECT cfi.client_id
                        FROM client_filters cf
                        LEFT JOIN client_filter_items cfi ON cfi.filter_name_id = cf.id
                        WHERE cf.id = ?
                    ) s ON c.client_id = s.client_id
                    SET c.client_show = IF(s.client_id IS NOT NULL, 1, 0)
                    WHERE c.client_show <> IF(s.client_id IS NOT NULL, 1, 0)";

            $sql_client_filter = "UPDATE client_filters
                                    SET selected = (id = ?)";
            $this->db->query($sql, [$filter_id]);
            $this->db->query($sql_client_filter, [$filter_id]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function update_products_by_filter_id($filter_id)
    {
        try {
            // If filter_id is 0, show all clients (set client_show = 1 for non-old records)
            if ((int)$filter_id === 0) {
                $sqlAll = "UPDATE products SET product_show = 1 WHERE is_old = 0";
                $sql_clear_filter = "UPDATE product_filter SET selected = 0";
                $this->db->query($sqlAll);
                $this->db->query($sql_clear_filter);
                return true;
            }
            $sql = "UPDATE products p
                    LEFT JOIN (
                        SELECT pfi.product_id
                        FROM product_filter pf
                        LEFT JOIN product_filter_items pfi ON pfi.filter_name_id = pf.id
                        WHERE pf.id = ?
                    ) s ON p.product_id = s.product_id
                    SET p.product_show = IF(s.product_id IS NOT NULL, 1, 0)
                    WHERE p.product_show <> IF(s.product_id IS NOT NULL, 1, 0)";
            $sql_product_filter = "UPDATE product_filter
                                    SET selected = (id = ?)";
            $this->db->query($sql, [$filter_id]);
            $this->db->query($sql_product_filter, [$filter_id]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_si_volume($date_start, $date_end) {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.product_tag,
                        MAX(p.product_name) AS product_name,
                        MAX(p.product_item) AS product_item,
                        SUM(siil.si_item_qty) AS total_item_qty,
                        GROUP_CONCAT(DISTINCT siil.si_item_price ORDER BY siil.si_item_price DESC SEPARATOR ', ') AS selling_price,
                        SUM(siil.si_item_qty * (siil.si_item_price - COALESCE(disc.total_discount, 0))) AS gross_sales,
                        GROUP_CONCAT(DISTINCT pc.cost ORDER BY pc.cost DESC SEPARATOR ', ') AS cost,
                        SUM(siil.si_item_qty * COALESCE(pc.cost, 0)) AS total_cost,
                        SUM(siil.si_item_qty * (siil.si_item_price - COALESCE(disc.total_discount, 0))) - SUM(siil.si_item_qty * COALESCE(pc.cost, 0)) AS income
                    FROM
                        products p
                        LEFT JOIN sales_invoice_items_list siil ON siil.si_product_id = p.id
                        LEFT JOIN sales_invoice si ON si.id = siil.si_id
                        LEFT JOIN (
                            SELECT 
                                si_item_id, 
                                SUM(discount) AS total_discount
                            FROM sales_invoice_items_list_discount
                            GROUP BY si_item_id
                        ) disc ON disc.si_item_id = siil.id
                        LEFT JOIN product_cost pc ON pc.product_id = p.product_id
                            AND pc.id = (
                                SELECT pc1.id
                                FROM product_cost pc1
                                WHERE pc1.product_id = p.product_id
                                    AND (
                                        (si.si_date >= pc1.created_date AND (pc1.archive_date IS NULL OR si.si_date <= pc1.archive_date))
                                        OR (
                                            SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id
                                        ) = 1
                                        OR (
                                            NOT EXISTS (
                                                SELECT 1 FROM product_cost pc_chk
                                                WHERE pc_chk.product_id = p.product_id
                                                    AND si.si_date >= pc_chk.created_date
                                                    AND (pc_chk.archive_date IS NULL OR si.si_date <= pc_chk.archive_date)
                                            )
                                            AND si.si_date < (
                                                SELECT MIN(pc_old.created_date)
                                                FROM product_cost pc_old
                                                WHERE pc_old.product_id = p.product_id
                                            )
                                            AND pc1.id = (
                                                SELECT MIN(pc_min.id)
                                                FROM product_cost pc_min
                                                WHERE pc_min.product_id = p.product_id
                                            )
                                        )
                                        OR (
                                            NOT EXISTS (
                                                SELECT 1 FROM product_cost pc_chk
                                                WHERE pc_chk.product_id = p.product_id
                                                    AND si.si_date >= pc_chk.created_date
                                                    AND (pc_chk.archive_date IS NULL OR si.si_date <= pc_chk.archive_date)
                                            )
                                            AND si.si_date >= (
                                                SELECT MIN(pc_old.created_date)
                                                FROM product_cost pc_old
                                                WHERE pc_old.product_id = p.product_id
                                            )
                                            AND pc1.created_date > si.si_date
                                            AND pc1.id = (
                                                SELECT pc_next.id
                                                FROM product_cost pc_next
                                                WHERE pc_next.product_id = p.product_id
                                                    AND pc_next.created_date > si.si_date
                                                ORDER BY pc_next.created_date ASC, pc_next.id ASC
                                                LIMIT 1
                                            )
                                        )
                                    )
                                ORDER BY pc1.id DESC
                                LIMIT 1
                            )
                    WHERE
                        si.si_status = 'printed'
                        AND si.si_date BETWEEN ? AND ?
                    GROUP BY p.product_id, p.product_tag
                    ORDER BY product_name ASC";
            return $this->db->query($sql, [$date_start, $date_end])->getResultArray();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_dr_volume($date_start, $date_end) {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.product_tag,
                        MAX(p.product_name) AS product_name,
                        MAX(p.product_item) AS product_item,
                        SUM(dril.dr_item_qty) AS total_item_qty,
                        GROUP_CONCAT(DISTINCT dril.dr_item_price ORDER BY dril.dr_item_price DESC SEPARATOR ', ') AS selling_price,
                        SUM(dril.dr_item_qty * (dril.dr_item_price - COALESCE(disc_totals.total_discount, 0))) AS gross_sales,
                        GROUP_CONCAT(DISTINCT pc_resolved.cost ORDER BY pc_resolved.cost DESC SEPARATOR ', ') AS cost,
                        SUM(dril.dr_item_qty * COALESCE(pc_resolved.cost, 0)) AS total_cost,
                        SUM(dril.dr_item_qty * (dril.dr_item_price - COALESCE(disc_totals.total_discount, 0))) - SUM(dril.dr_item_qty * COALESCE(pc_resolved.cost, 0)) AS income
                    FROM
                        products p
                        LEFT JOIN delivery_receipt_items_list dril ON dril.dr_product_id = p.id
                        LEFT JOIN delivery_receipt dr ON dr.id = dril.dr_id
                        LEFT JOIN (
                            SELECT 
                                dr_item_id, 
                                SUM(discount) AS total_discount
                            FROM delivery_receipt_items_list_discount
                            GROUP BY dr_item_id
                        ) AS disc_totals ON disc_totals.dr_item_id = dril.id
                        LEFT JOIN product_cost pc_resolved ON pc_resolved.product_id = p.product_id
                            AND pc_resolved.id = (
                                SELECT pc1.id
                                FROM product_cost pc1
                                WHERE pc1.product_id = p.product_id
                                    AND (
                                        (dr.dr_date >= pc1.created_date AND (pc1.archive_date IS NULL OR dr.dr_date <= pc1.archive_date))
                                        OR (
                                            SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id
                                        ) = 1
                                        OR (
                                            NOT EXISTS (
                                                SELECT 1 FROM product_cost pc_chk
                                                WHERE pc_chk.product_id = p.product_id
                                                    AND dr.dr_date >= pc_chk.created_date
                                                    AND (pc_chk.archive_date IS NULL OR dr.dr_date <= pc_chk.archive_date)
                                            )
                                            AND dr.dr_date < (
                                                SELECT MIN(pc_old.created_date)
                                                FROM product_cost pc_old
                                                WHERE pc_old.product_id = p.product_id
                                            )
                                            AND pc1.id = (
                                                SELECT MIN(pc_min.id)
                                                FROM product_cost pc_min
                                                WHERE pc_min.product_id = p.product_id
                                            )
                                        )
                                        OR (
                                            NOT EXISTS (
                                                SELECT 1 FROM product_cost pc_chk
                                                WHERE pc_chk.product_id = p.product_id
                                                    AND dr.dr_date >= pc_chk.created_date
                                                    AND (pc_chk.archive_date IS NULL OR dr.dr_date <= pc_chk.archive_date)
                                            )
                                            AND dr.dr_date >= (
                                                SELECT MIN(pc_old.created_date)
                                                FROM product_cost pc_old
                                                WHERE pc_old.product_id = p.product_id
                                            )
                                            AND pc1.created_date > dr.dr_date
                                            AND pc1.id = (
                                                SELECT pc_next.id
                                                FROM product_cost pc_next
                                                WHERE pc_next.product_id = p.product_id
                                                    AND pc_next.created_date > dr.dr_date
                                                ORDER BY pc_next.created_date ASC, pc_next.id ASC
                                                LIMIT 1
                                            )
                                        )
                                    )
                                ORDER BY pc1.id DESC
                                LIMIT 1
                            )
                    WHERE
                        dr.dr_status = 'printed'
                        AND dr.dr_date BETWEEN ? AND ?
                    GROUP BY p.product_id, p.product_tag
                    ORDER BY product_name ASC";
            return $this->db->query($sql, [$date_start, $date_end])->getResultArray();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

        public function get_si_dr_volume($date_start, $date_end) {
        try {
            $sql = "SELECT 
                        combined.product_id,
                        MAX(combined.product_name) AS product_name,
                        MAX(combined.product_item) AS product_item,
                        MAX(combined.product_tag) AS product_tag,
                        SUM(combined.total_item_qty) AS total_item_qty,
                        GROUP_CONCAT(DISTINCT combined.selling_price ORDER BY combined.selling_price DESC SEPARATOR ', ') AS selling_price,
                        SUM(combined.gross_sales) AS gross_sales,
                        GROUP_CONCAT(DISTINCT combined.cost ORDER BY combined.cost DESC SEPARATOR ', ') AS cost,
                        SUM(combined.total_cost) AS total_cost,
                        SUM(combined.income) AS income
                    FROM (
                        SELECT 
                            p.product_id,
                            p.product_name,
                            p.product_item,
                            p.product_tag,
                            siil.si_item_qty AS total_item_qty,
                            siil.si_item_price AS selling_price,
                            siil.si_item_qty * (siil.si_item_price - COALESCE(disc.total_discount, 0)) AS gross_sales,
                            pc.cost AS cost,
                            siil.si_item_qty * COALESCE(pc.cost, 0) AS total_cost,
                            siil.si_item_qty * (siil.si_item_price - COALESCE(disc.total_discount, 0)) 
                                - siil.si_item_qty * COALESCE(pc.cost, 0) AS income
                        FROM
                            products p
                            LEFT JOIN sales_invoice_items_list siil ON siil.si_product_id = p.id
                            LEFT JOIN sales_invoice si ON si.id = siil.si_id
                            LEFT JOIN (
                                SELECT 
                                    si_item_id, 
                                    SUM(discount) AS total_discount
                                FROM sales_invoice_items_list_discount
                                GROUP BY si_item_id
                            ) disc ON disc.si_item_id = siil.id
                            LEFT JOIN product_cost pc ON pc.product_id = p.product_id
                                AND pc.id = (
                                    SELECT pc1.id
                                    FROM product_cost pc1
                                    WHERE pc1.product_id = p.product_id
                                        AND (
                                            (si.si_date >= pc1.created_date AND (pc1.archive_date IS NULL OR si.si_date <= pc1.archive_date))
                                            OR (
                                                SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id
                                            ) = 1
                                            OR (
                                                NOT EXISTS (
                                                    SELECT 1 FROM product_cost pc_chk
                                                    WHERE pc_chk.product_id = p.product_id
                                                        AND si.si_date >= pc_chk.created_date
                                                        AND (pc_chk.archive_date IS NULL OR si.si_date <= pc_chk.archive_date)
                                                )
                                                AND si.si_date < (
                                                    SELECT MIN(pc_old.created_date)
                                                    FROM product_cost pc_old
                                                    WHERE pc_old.product_id = p.product_id
                                                )
                                                AND pc1.id = (
                                                    SELECT MIN(pc_min.id)
                                                    FROM product_cost pc_min
                                                    WHERE pc_min.product_id = p.product_id
                                                )
                                            )
                                            OR (
                                                NOT EXISTS (
                                                    SELECT 1 FROM product_cost pc_chk
                                                    WHERE pc_chk.product_id = p.product_id
                                                        AND si.si_date >= pc_chk.created_date
                                                        AND (pc_chk.archive_date IS NULL OR si.si_date <= pc_chk.archive_date)
                                                )
                                                AND si.si_date >= (
                                                    SELECT MIN(pc_old.created_date)
                                                    FROM product_cost pc_old
                                                    WHERE pc_old.product_id = p.product_id
                                                )
                                                AND pc1.created_date > si.si_date
                                                AND pc1.id = (
                                                    SELECT pc_next.id
                                                    FROM product_cost pc_next
                                                    WHERE pc_next.product_id = p.product_id
                                                        AND pc_next.created_date > si.si_date
                                                    ORDER BY pc_next.created_date ASC, pc_next.id ASC
                                                    LIMIT 1
                                                )
                                            )
                                        )
                                    ORDER BY pc1.id DESC
                                    LIMIT 1
                                )
                        WHERE
                            si.si_status = 'printed'
                            AND si.si_date BETWEEN ? AND ?

                        UNION ALL

                        SELECT 
                            p.product_id,
                            p.product_name,
                            p.product_item,
                            p.product_tag,
                            dril.dr_item_qty AS total_item_qty,
                            dril.dr_item_price AS selling_price,
                            dril.dr_item_qty * (dril.dr_item_price - COALESCE(disc_totals.total_discount, 0)) AS gross_sales,
                            pc_resolved.cost AS cost,
                            dril.dr_item_qty * COALESCE(pc_resolved.cost, 0) AS total_cost,
                            dril.dr_item_qty * (dril.dr_item_price - COALESCE(disc_totals.total_discount, 0)) 
                                - dril.dr_item_qty * COALESCE(pc_resolved.cost, 0) AS income
                        FROM
                            products p
                            LEFT JOIN delivery_receipt_items_list dril ON dril.dr_product_id = p.id
                            LEFT JOIN delivery_receipt dr ON dr.id = dril.dr_id
                            LEFT JOIN (
                                SELECT 
                                    dr_item_id, 
                                    SUM(discount) AS total_discount
                                FROM delivery_receipt_items_list_discount
                                GROUP BY dr_item_id
                            ) AS disc_totals ON disc_totals.dr_item_id = dril.id
                            LEFT JOIN product_cost pc_resolved ON pc_resolved.product_id = p.product_id
                                AND pc_resolved.id = (
                                    SELECT pc1.id
                                    FROM product_cost pc1
                                    WHERE pc1.product_id = p.product_id
                                        AND (
                                            (dr.dr_date >= pc1.created_date AND (pc1.archive_date IS NULL OR dr.dr_date <= pc1.archive_date))
                                            OR (
                                                SELECT COUNT(*) FROM product_cost pc_cnt WHERE pc_cnt.product_id = p.product_id
                                            ) = 1
                                            OR (
                                                NOT EXISTS (
                                                    SELECT 1 FROM product_cost pc_chk
                                                    WHERE pc_chk.product_id = p.product_id
                                                        AND dr.dr_date >= pc_chk.created_date
                                                        AND (pc_chk.archive_date IS NULL OR dr.dr_date <= pc_chk.archive_date)
                                                )
                                                AND dr.dr_date < (
                                                    SELECT MIN(pc_old.created_date)
                                                    FROM product_cost pc_old
                                                    WHERE pc_old.product_id = p.product_id
                                                )
                                                AND pc1.id = (
                                                    SELECT MIN(pc_min.id)
                                                    FROM product_cost pc_min
                                                    WHERE pc_min.product_id = p.product_id
                                                )
                                            )
                                            OR (
                                                NOT EXISTS (
                                                    SELECT 1 FROM product_cost pc_chk
                                                    WHERE pc_chk.product_id = p.product_id
                                                        AND dr.dr_date >= pc_chk.created_date
                                                        AND (pc_chk.archive_date IS NULL OR dr.dr_date <= pc_chk.archive_date)
                                                )
                                                AND dr.dr_date >= (
                                                    SELECT MIN(pc_old.created_date)
                                                    FROM product_cost pc_old
                                                    WHERE pc_old.product_id = p.product_id
                                                )
                                                AND pc1.created_date > dr.dr_date
                                                AND pc1.id = (
                                                    SELECT pc_next.id
                                                    FROM product_cost pc_next
                                                    WHERE pc_next.product_id = p.product_id
                                                        AND pc_next.created_date > dr.dr_date
                                                    ORDER BY pc_next.created_date ASC, pc_next.id ASC
                                                    LIMIT 1
                                                )
                                            )
                                        )
                                    ORDER BY pc1.id DESC
                                    LIMIT 1
                                )
                        WHERE
                            dr.dr_status = 'printed'
                            AND dr.dr_date BETWEEN ? AND ?
                    ) AS combined
                    GROUP BY combined.product_id
                    ORDER BY MAX(combined.product_name) ASC";
            return $this->db->query($sql, [$date_start, $date_end, $date_start, $date_end])->getResultArray();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}