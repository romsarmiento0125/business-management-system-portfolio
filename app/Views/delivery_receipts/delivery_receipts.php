<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/delivery_receipts/delivery_receipts.css') ?>">

<div class="loader" id="loader"></div>

<div class="mx-5">
    <div class="row">
        <div class="col-12">
            <div class="delivery_receipt_box">
                <div class="delivery_receipt_title">
                    <p id="dr_id">Delivery Receipt</p>
                </div>
                <hr>
                <div class="">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6 mb-4">
                                    <div class="delivery_receipt_details_box">
                                        <div class="delivery_receipt_details_title">
                                            <p>Customer Details</p>
                                        </div>
                                        <hr>
                                        <div class="clients_details_container">
                                            <div class="d-flex align-items-center mb-2">
                                                <p>Name:&nbsp;</p>
                                                <select class="select2" id="clients_details" style="width: 100%;"></select>
                                            </div>
                                        </div>
                                        <div class="clients_details_name_container" style="display: none;">
                                            <div class="d-flex align-items-center mb-2">
                                                <p>Name:&nbsp;</p>
                                                <p class="fw-bold" id="clients_details_name">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>TIN:&nbsp;</p>
                                            <p class="fw-bold" id="client_tin_details">&nbsp;</p>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Address:&nbsp;</p>
                                            <p class="fw-bold" id="client_address_details">&nbsp;</p>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Company:&nbsp;</p>
                                            <p class="fw-bold" id="client_company_details">&nbsp;</p>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                    <p>Terms:&nbsp;</p>
                                                    <select class="select2" id="client_term_details" style="width: 100%;">
                                                        <option value="cod">COD</option>
                                                        <option value="7">7 Days</option>
                                                        <option value="15">15 Days</option>
                                                        <option value="21">21 Days</option>
                                                        <option value="30">30 Days</option>
                                                        <option value="45">45 Days</option>
                                                        <option value="60">60 Days</option>
                                                        <option value="flex">FLEX</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6 content_center">
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <p>Date:&nbsp;</p>
                                                    <input type="date" class="form-control" id="client_date_details">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-4">
                                    <div class="delivery_receipt_details_box">
                                        <div class="delivery_receipt_details_title">
                                            <p>Items</p>
                                        </div>
                                        <hr>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Product:&nbsp;</p>
                                            <select class="select2" id="products_details" style="width: 100%;"></select>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                    <p>Price:&nbsp;</p>
                                                    <input type="number" class="form-control" id="item_price_details">
                                                </div>
                                            </div>
                                            <div class="col-6 content_center">
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <p>Qty:&nbsp;</p>
                                                    <input type="number" class="form-control" id="item_qty_details">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                    <p>Amount:&nbsp;</p>
                                                    <p class="fw-bold" id="item_amount_details">&nbsp;</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-10">
                                                <div class="row" id="add_input_discount">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-danger" id="item_remove_discount" onclick="remove_discount_input()"><i class="fa fa-trash"></i></button>
                                                <button type="button" class="btn btn-success" id="item_add_discount" onclick="add_discount_input()"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Total Amount:&nbsp;</p>
                                            <p class="fw-bold" id="item_total_details">&nbsp;</p>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-primary" onclick="add_item_details()">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-4">
                            <div class="delivery_receipt_details_box">
                                <div class="delivery_receipt_details_title">
                                    <p>Summary</p>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table id="item_list_table" class="table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="d-none">ID</th>
                                                <th>Item&nbsp;Code</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Amount</th>
                                                <th>Discount</th>
                                                <th>Total&nbsp;Amount</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="offset-2 col-4">
                                        <div class="d-flex">
                                            <div class="">
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>TPA:&nbsp;</p>
                                                    <input type="number" class="form-control" id="item_freight_details" value='0'>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="d-flex">
                                            <div class="">
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>Sub&nbsp;total:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_sub_total"></p>
                                                </div>
                                                <p>Discount:</p>
                                                <div class="" id="discount_summary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="offset-1 col-5">
                                        <div class="d-flex">
                                            <div class="">
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>TOTAL&nbsp;AMOUNT:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_total_amount"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-secondary me-2" id="update_draft_btn" style="display: none;" onclick="update_delivery_receipt()">Update Draft</button>
                                    <button class="btn btn-danger me-2" id="cancel_update_draft_btn" style="display: none;" onclick="cancel_update_delivery_receipt()">Cancel</button>
                                    <button class="btn btn-secondary me-2" id="draft_btn" onclick="save_delivery_receipt('draft')">Draft</button>
                                    <button class="btn btn-success" id="print_btn" onclick="save_delivery_receipt('printed')">Print</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-4">
                            <div class="delivery_receipt_details_box">
                                <div class="delivery_receipt_details_title">
                                    <p>Receipts</p>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Date range:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control float-right" id="dr_date_range">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table id="receipt_list_table" class="table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>DR_ID</th>
                                                <th>Name</th>
                                                <th>Terms</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="" id="view_dr_receipt_modal"></div>

<div class="" id="dr_auth_modal"></div>

<script>
window.addEventListener('load', function(){
    // If an input/select/textarea is focused on load (causing arrow keys to change values),
    // blur it so arrow keys will scroll the page instead.
    var active = document.activeElement;
    if(active && (active.tagName === 'INPUT' || active.tagName === 'SELECT' || active.tagName === 'TEXTAREA')) {
        active.blur();
    }
});
</script>

<script src="<?= base_url('assets/my_js/global.js') ?>"></script>
<script src="<?= base_url('assets/my_js/delivery_receipts/delivery_receipts_data_population.js') ?>"></script>
<script src="<?= base_url('assets/my_js/delivery_receipts/delivery_receipts.js') ?>"></script>
<script src="<?= base_url('assets/my_js/delivery_receipts/delivery_receipts_summary.js') ?>"></script>
<script src="<?= base_url('assets/my_js/view_receipt/delivery_receipt_receipt.js') ?>"></script>

<?= $this->endSection() ?>