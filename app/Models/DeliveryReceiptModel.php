<?php
namespace App\Models;

use CodeIgniter\Model;

class DeliveryReceiptModel extends Model
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    public function __construct()
    {   
        $this->db = \Config\Database::connect();
    }

    /**
     * Fetch active products, active clients, and delivery receipts (printed/cancelled + draft) within date range.
     * Draft rows join on generated client_id; printed/cancelled on stable numeric id (mirrors SI logic).
     *
     * @param string $start YYYY-MM-DD
     * @param string $end   YYYY-MM-DD
     * @return array|string Array with keys products, clients, delivery_receipt or error message string
     */
    public function get_products_clients_dr($start, $end)
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
            // When a Delivery Receipt (DR) is in DRAFT we want the DR to reference the current client/product
            // state (so edits to client/product are reflected while drafting). However, once an DR is PRINTED
            // or CANCELLED we want the DR to remain immutable and point to the stable auto-increment IDs.
            //
            // Implementation detail below: the UNION returns two groups:
            // 1) Printed/Cancelled rows joined on stable `clients.id` (snapshot behavior)
            // 2) Draft rows joined on generated `clients.client_id` (reflects latest edits)
            // This preserves historical integrity for printed DRs while allowing drafts to follow current data.

            $delivery_receipt_query = "SELECT 
                dr.id, 
                c.client_name,
                dr.client_term,
                dr.dr_status,
                dr.dr_date,
                dr.updated_at,
                c.id AS client_id
            FROM
                delivery_receipt dr
                    LEFT JOIN
                clients c ON c.id = dr.client_id
            WHERE
                (dr.dr_date BETWEEN ? AND ?) AND
                (dr.dr_status = 'printed' OR dr.dr_status = 'cancelled')        
            UNION ALL SELECT 
                dr.id,
                c.client_name,
                dr.client_term,
                dr.dr_status,
                dr.dr_date,
                dr.updated_at,
                c.id AS client_id
            FROM
                delivery_receipt dr
                    LEFT JOIN
                clients c ON c.client_id = dr.client_id
            WHERE
                dr.dr_status = 'draft' AND c.is_old = 0";


            $products = $this->db->query($productsQuery)->getResult();
            $clients = $this->db->query($clientsQuery)->getResult();
            $delivery_receipt = $this->db->query($delivery_receipt_query, [$start, $end])->getResult();

            return ['products' => $products, 'clients' => $clients, 'delivery_receipt' => $delivery_receipt];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Insert a new delivery receipt with items & discounts.
     * @param array $invoiceParams Base header params
     * @param array $allItemParams Item param arrays
     * @param array $allDiscountParams Discount param arrays
     * @return array|string
     */
    public function insert_delivery_receipt($invoiceParams, $allItemParams, $allDiscountParams)
    {
        try {
            $this->db->transStart();
            $this->db->query("INSERT INTO delivery_receipt (
                client_id, client_term, freight_cost, dr_status, creator_id, updater_id, dr_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?)", $invoiceParams);

            $drId = $this->db->insertID();
            $this->insertItemsAndDiscounts($drId, $allItemParams, $allDiscountParams);
            return $this->finalizeTransaction(true, $drId);
        } catch (\Exception $e) {
            $this->safeRollback();
            return $e->getMessage();
        }
    }

    /**
     * Update existing delivery receipt (replace items & discounts).
     */
    public function update_delivery_receipt($dr_id, $params, $allItemParams, $allDiscountParams)
    {
        try {
            $this->db->transStart();
            $this->db->query("UPDATE delivery_receipt SET client_term = ?, freight_cost = ?, dr_date = ? WHERE id = ?", $params);
            $this->db->query("DELETE FROM delivery_receipt_items_list WHERE dr_id = ?", [$dr_id]);
            $this->insertItemsAndDiscounts($dr_id, $allItemParams, $allDiscountParams);
            return $this->finalizeTransaction(false, $dr_id);
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
    private function insertItemsAndDiscounts(int $dr_id, array $allItemParams, array $allDiscountParams): void
    {
        $itemSql = "INSERT INTO delivery_receipt_items_list (
            dr_id, dr_item_price, dr_item_qty, dr_unique_id, creator_id, updater_id, dr_product_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $discountSql = "INSERT INTO delivery_receipt_items_list_discount (
            dr_item_id, discount_label, discount, creator_id, updater_id
        ) VALUES (?, ?, ?, ?, ?)";

        foreach ($allItemParams as $itemParams) {
            $this->db->query($itemSql, array_merge([$dr_id], $itemParams));
            $itemId = $this->db->insertID();
            // itemParams[2] is si_unique_id; discounts[2] is matching unique ref
            foreach ($allDiscountParams as $discountParams) {
                if (isset($itemParams[2], $discountParams[2]) && $itemParams[2] == $discountParams[2]) {
                    $this->db->query($discountSql, array_merge([$itemId], [$discountParams[0], $discountParams[1], $discountParams[3], $discountParams[4]]));
                }
            }
        }
    }

    /**
     * Commit or rollback based on transaction status and build response array.
     */
    private function finalizeTransaction(bool $isInsert, int $id): array
    {
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return ['status' => 'failed', 'receipt_id' => null];
        }
        $this->db->transCommit();
        return ['status' => 'success', 'receipt_id' => $id];
    }

    /**
     * Defensive rollback helper for exceptions.
     */
    private function safeRollback(): void
    {
        if ($this->db->transStatus() !== null) {
            $this->db->transRollback();
        }
    }

    public function get_delivery_receipt_by_id($id)
    {
        try {
            $query = $this->db->query("SELECT dr_draft_receipt_query_fn(?) AS receipt", [$id]);
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

    public function get_dr_receipt_by_id($id, $status)
    {
        try {
            $query = $this->db->query("SELECT dr_receipt_query_fn_combined(?, ?) AS receipt", [$id, $status]);
            $row = $query->getRow();
            $receipt = json_decode($row->receipt, true);
            return $receipt;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function print_dr($id, $client_id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE delivery_receipt 
                SET
                    dr_status = 'printed',
                    client_id = ?
                WHERE
                   id = ?";

            $query2 = "UPDATE delivery_receipt_items_list dril
                JOIN
                    products p ON p.product_id = dril.dr_product_id AND p.is_old = 0
                SET
                    dril.dr_product_id = p.id
                WHERE
                    dril.dr_id = ?";

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

    public function draft_dr_receipt($id, $client_id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE delivery_receipt 
                SET
                    dr_status = 'draft',
                    client_id = ?
                WHERE
                   id = ?";

            $query2 = "UPDATE delivery_receipt_items_list dril
                JOIN
                    products p ON p.id = dril.dr_product_id
                SET
                    dril.dr_product_id = p.product_id
                WHERE
                    dril.dr_id = ?";

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

    public function cancel_dr_receipt($id)
    {
        try {
            $this->db->transStart(); // Start Transaction

            $query1 = "UPDATE delivery_receipt 
                SET 
                    dr_status = 'cancelled'
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