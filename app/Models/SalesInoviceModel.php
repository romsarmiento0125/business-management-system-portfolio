<?php
namespace App\Models;

use CodeIgniter\Model;

class SalesInoviceModel extends Model
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Fetch active products, active clients, and sales invoices (printed/cancelled + draft) within date range.
     * Draft invoices join on generated client_id while printed/cancelled join on stable numeric id.
     *
     * @param string $start  YYYY-MM-DD
     * @param string $end    YYYY-MM-DD
     * @return array|string  Array with keys products, clients, sales_invoice or error message string
     */
    public function get_products_clients_si($start, $end)
    {
        try {
            $productsQuery = "SELECT * FROM products WHERE is_old = 0 AND is_active = 0";
            $clientsQuery = "SELECT * FROM clients WHERE is_old = 0 AND is_active = 0";

            // NOTE about two-id design:
            // - `clients.id` / `products.id` are the stable auto-increment primary keys (permanent record IDs).
            // - `clients.client_id` / `products.product_id` are generated identifiers (based on time/other logic)
            //   used to reference the current mutable client/product record while an invoice is still in DRAFT.
            //
            // Rationale:
            // When a Sales Invoice (SI) is in DRAFT we want the SI to reference the current client/product
            // state (so edits to client/product are reflected while drafting). However, once an SI is PRINTED
            // or CANCELLED we want the SI to remain immutable and point to the stable auto-increment IDs.
            //
            // Implementation detail below: the UNION returns two groups:
            // 1) Printed/Cancelled rows joined on stable `clients.id` (snapshot behavior)
            // 2) Draft rows joined on generated `clients.client_id` (reflects latest edits)
            // This preserves historical integrity for printed SIs while allowing drafts to follow current data.

            $sales_invoice_query = "SELECT 
                si.id,
                c.client_name,
                si.client_term,
                si.si_status,
                si.si_date,
                si.updated_at,
                c.id AS client_id
            FROM
                sales_invoice si
                    LEFT JOIN
                clients c ON c.id = si.client_id
            WHERE
                (si.si_date BETWEEN ? AND ?) AND
                (si.si_status = 'printed' OR si.si_status = 'cancelled') 
            UNION ALL SELECT 
                si.id,
                c.client_name,
                si.client_term,
                si.si_status,
                si.si_date,
                si.updated_at,
                c.id AS client_id
            FROM
                sales_invoice si
                    LEFT JOIN
                clients c ON c.client_id = si.client_id
            WHERE
                si.si_status = 'draft' AND c.is_old = 0";


            $products = $this->db->query($productsQuery)->getResult();
            $clients = $this->db->query($clientsQuery)->getResult();
            $sales_invoice = $this->db->query($sales_invoice_query, [$start, $end])->getResult();

            return ['products' => $products, 'clients' => $clients, 'sales_invoice' => $sales_invoice];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Insert a new sales invoice with items & discounts.
     * @param array $params base invoice params
     * @param array $allItemParams list of item param arrays
     * @param array $allDiscountParams list of discount param arrays
     * @return array|string
     */
    public function insert_sales_invoice($params, $allItemParams, $allDiscountParams)
    {
        try {
            $this->db->transStart();
            $this->db->query("INSERT INTO sales_invoice (
                client_id, client_term, freight_cost, si_status, creator_id, updater_id, si_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?)", $params);

            $invoiceId = $this->db->insertID();
            $this->insertItemsAndDiscounts($invoiceId, $allItemParams, $allDiscountParams);
            return $this->finalizeInvoiceTransaction(true, $invoiceId);
        } catch (\Exception $e) {
            $this->safeRollback();
            return $e->getMessage();
        }
    }

    /**
     * Update existing invoice (replace items & discounts wholly).
     */
    public function update_sales_invoice($si_id, $params, $allItemParams, $allDiscountParams)
    {
        try {
            $this->db->transStart();
            $this->db->query("UPDATE sales_invoice SET client_term = ?, freight_cost = ?, si_date = ? WHERE id = ?", $params);
            $this->db->query("DELETE FROM sales_invoice_items_list WHERE si_id = ?", [$si_id]);
            $this->insertItemsAndDiscounts($si_id, $allItemParams, $allDiscountParams);
            return $this->finalizeInvoiceTransaction(false, $si_id);
        } catch (\Exception $e) {
            $this->safeRollback();
            return $e->getMessage();
        }
    }

    /**
     * Shared logic inserting items & their discounts.
     * @param int $invoiceId
     * @param array $allItemParams
     * @param array $allDiscountParams
     */
    private function insertItemsAndDiscounts(int $invoiceId, array $allItemParams, array $allDiscountParams): void
    {
        $itemSql = "INSERT INTO sales_invoice_items_list (
            si_id, si_item_price, si_item_qty, si_item_vat_check, si_unique_id, creator_id, updater_id, si_product_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $discountSql = "INSERT INTO sales_invoice_items_list_discount (
            si_item_id, discount_label, discount, creator_id, updater_id
        ) VALUES (?, ?, ?, ?, ?)";

        foreach ($allItemParams as $itemParams) {
            $this->db->query($itemSql, array_merge([$invoiceId], $itemParams));
            $itemId = $this->db->insertID();
            // itemParams[3] is si_unique_id; discounts[2] is matching unique ref
            foreach ($allDiscountParams as $discountParams) {
                if (isset($itemParams[3], $discountParams[2]) && $itemParams[3] == $discountParams[2]) {
                    $this->db->query($discountSql, array_merge([$itemId], [$discountParams[0], $discountParams[1], $discountParams[3], $discountParams[4]]));
                }
            }
        }
    }

    /**
     * Commit or rollback based on transaction status and build response array.
     */
    private function finalizeInvoiceTransaction(bool $isInsert, int $id): array
    {
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return ['status' => 'failed', 'invoice_id' => null];
        }
        $this->db->transCommit();
        return ['status' => 'success', 'invoice_id' => $id];
    }

    /**
     * Defensive rollback helper for exceptions.
     */
    private function safeRollback(): void
    {
        if ($this->db->transStatus() !== null) { // inside a transaction
            $this->db->transRollback();
        }
    }

    public function get_sales_invoice_by_id($id)
    {
        try {
            $query = $this->db->query("SELECT si_draft_receipt_query_fn(?) AS receipt", [$id]);
            $row = $query->getRow();
            $receipt = json_decode($row->receipt, true);
            return $receipt;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function user_login($username)
    {
        try {
            $query = "SELECT * FROM users WHERE username = ? AND archive = 0 AND role_id = 2";
            return $this->db->query($query, [$username])->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_si_receipt_by_id($id, $status)
    {
        try {
            $query = $this->db->query("SELECT si_receipt_query_fn_combined(?, ?) AS receipt", [$id, $status]);
            $row = $query->getRow();
            $receipt = json_decode($row->receipt, true);
            return $receipt;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function print_si($id, $client_id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE sales_invoice 
                SET 
                    si_status = 'printed',
                    client_id = ?
                WHERE
                   id = ?";

            $query2 = "UPDATE sales_invoice_items_list siil
                JOIN
                    products p ON p.product_id = siil.si_product_id AND p.is_old = 0 
                SET 
                    siil.si_product_id = p.id
                WHERE
                    siil.si_id = ?";

            $this->db->query($query1, [$client_id, $id]);
            $this->db->query($query2, [$id]);

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
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function draft_si_receipt($id, $client_id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE sales_invoice 
                SET 
                    si_status = 'draft',
                    client_id = ?
                WHERE
                   id = ?";

            $query2 = "UPDATE sales_invoice_items_list siil
                JOIN
                    products p ON p.id = siil.si_product_id
                SET 
                    siil.si_product_id = p.product_id
                WHERE
                    siil.si_id = ?";

            $this->db->query($query1, [$client_id, $id]);
            $this->db->query($query2, [$id]);

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
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel_si_receipt($id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE sales_invoice 
                SET 
                    si_status = 'cancelled'
                WHERE
                   id = ?";

            $this->db->query($query1, [$id]);

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
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}