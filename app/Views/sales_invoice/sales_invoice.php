<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/sales_invoice/sales_invoice.css') ?>">

<div class="loader" id="loader"></div>

<div class="mx-5">
    <div class="row">
        <div class="col-12">
            <div class="sales_invoice_box">
                <div class="sales_invoice_title">
                    <p id="si_id">Sales Invoice</p>
                </div>
                <hr>
                <div class="">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6 mb-4">
                                    <div class="sales_invoice_details_box">
                                        <div class="sales_invoice_details_title">
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
                                    <div class="sales_invoice_details_box">
                                        <div class="sales_invoice_details_title">
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
                                            <div class="col-6 content_center">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="item_switch_details">
                                                        <label class="form-check-label" id="item_switch_label_detail">Not Vatable</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-7">
                                                <div class="d-flex align-items-center">
                                                    <p>Vatable&nbsp;Sales:&nbsp;</p>
                                                    <p class="fw-bold" id="item_vatsales_details">&nbsp;</p>
                                                </div>
                                            </div>
                                            <div class="col-5 content_center">
                                                <div class="d-flex align-items-center">
                                                    <p>Vat:&nbsp;</p>
                                                    <p class="fw-bold" id="item_vat_details">&nbsp;</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-10">
                                                <div class="row" id="add_input_discount"></div>
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
                            <div class="sales_invoice_details_box">
                                <div class="sales_invoice_details_title">
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
                                                    <p>Total&nbsp;amount:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_total_amount"></p>
                                                </div>
                                                <p>Discount:</p>
                                                <div class="" id="discount_summary"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="offset-1 col-5">
                                        <div class="d-flex">
                                            <div class="">
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>VATable&nbsp;Sales:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_vatable_sales"></p>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>VAT-Exempt&nbsp;Sales:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_vat_exempt_sales"></p>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>VAT-Zero&nbsp;Rated&nbsp;Sales:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_zero_rated"></p>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>VAT&nbsp;Amount:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_vat_amount"></p>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <p>TOTAL&nbsp;AMOUNT&nbsp;DUE:&nbsp;</p>
                                                    <p class="fw-bold" id="summary_total_amount_due"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-secondary me-2" id="update_draft_btn" style="display: none;" onclick="update_sales_invoice()">Update Draft</button>
                                    <button class="btn btn-danger me-2" id="cancel_update_draft_btn" style="display: none;" onclick="cancel_update_sales_invoice()">Cancel</button>
                                    <button class="btn btn-secondary me-2" id="draft_btn" onclick="save_sales_invoice('draft')">Draft</button>
                                    <button class="btn btn-success" id="print_btn" onclick="save_sales_invoice('printed')">Print</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-4">
                            <div class="sales_invoice_details_box">
                                <div class="sales_invoice_details_title">
                                    <p>Invoices</p>
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
                                                <input type="text" class="form-control float-right" id="si_date_range">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table id="invoice_list_table" class="table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>SI_ID</th>
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

<div class="" id="view_si_receipt_modal"></div>

<div class="" id="si_auth_modal"></div>

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
<script src="<?= base_url('assets/my_js/sales_invoice/sales_invoice_data_population.js') ?>"></script>
<script src="<?= base_url('assets/my_js/sales_invoice/sales_invoice.js') ?>"></script>
<script src="<?= base_url('assets/my_js/sales_invoice/sales_invoice_summary.js') ?>"></script>
<script src="<?= base_url('assets/my_js/view_receipt/sales_invoice_receipt.js') ?>"></script>

<?= $this->endSection() ?>