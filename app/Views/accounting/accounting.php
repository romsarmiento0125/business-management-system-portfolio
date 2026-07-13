<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/accounting/accounting.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<div class="loader" id="loader"></div>

<div class="mx-5">
    <div class="row">
        <div class="col-12">
            <div class="accounting_box">
                <div class="clients_filter_box">
                    <h4>Accounting</h4>
                    <hr>

                    <!-- Custom Export Buttons -->
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="form-group">
                                <label>Date range:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="date_range">
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <button id="si_volume_view" class="btn btn-warning mx-2">SI View</button>
                                <hr style="border: none; border-left: 1px solid #000; height: 50px; width: 1px;">
                                <button id="total_volume_view" class="btn btn-primary mx-2">Total View</button>
                                <hr style="border: none; border-left: 1px solid #000; height: 50px; width: 1px;">
                                <button id="dr_volume_view" class="btn btn-success mx-2">DR View</button>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="d-flex justify-content-end align-items-center h-100">
                                <button id="sales_invoice_btn" class="btn btn-secondary me-2">Sales Invoice</button>
                                <button id="delivery_receipt_btn" class="btn btn-secondary">Delivery Receipt</button>
                            </div>
                        </div>
                    </div>

                    <div class="filter_box">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5>Accounting filter</h5>
                                </div>
                                <div>
                                    <button id="hide_filter" class="btn btn-secondary btn-sm"><i class="fas fa-bars"></i></button>
                                </div>
                            </div>

                        <div class="row" id="accounting_filter">
                            <div class="col-5">
                                <div class="filter_box" style="overflow:auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5>Clients</h5>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div>
                                                <select class="select2" id="client_filter" style="width: 13vw;"></select>
                                            </div>
                                            <div>
                                                <button id="add_custom_client_filter" class="btn btn-primary btn-sm ms-2"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
    
                                    <table id="filter_clients" class="table display nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="d-none">ID</th>
                                                <th>Clients</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
    
                            <div class="col-7">
                                <div class="filter_box" style="overflow:auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5>Products</h5>
                                        </div>
                                        <!-- <div class="">
                                            <button id="export_product_qty" class="btn btn-primary btn-sm">Sales Product QTY</button>
                                        </div> -->
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div>
                                                <select class="select2" id="product_filter" style="width: 13vw;"></select>
                                            </div>
                                            <div>
                                                <button id="add_custom_product_filter" class="btn btn-primary btn-sm ms-2"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
    
                                    <table id="filter_products" class="table display nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="d-none">ID</th>
                                                <th>Products</th>
                                                <th>SRP</th>
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

    <div class="row mt-4 d-none" id="accounting_data">
        <div class="col-12">
            <div class="accounting_box">
                <h4 id="accounting_title"></h4>
                <hr>
                <div class="si_dr_table" id="si_dr_table">
                   
                </div>
            </div>
        </div>
    </div>
</div>

<div class="data_export_table"></div>

<div class="modal fade" id="si_volume_modal" tabindex="-1" aria-labelledby="siViewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Sales Invoice Volume</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter_box" style="overflow:auto;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5>Products</h5>
                        </div>
                        <div class="">
                            <button id="si_volume_export" class="btn btn-primary btn-sm">Export</button>
                        </div>
                    </div>
                    <div id="si_volume_tag_legend" class="mb-2"></div>
                    <table id="si_volume_table" class="table display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Product Name & Code</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th>Total Cost</th>
                                <th>Selling Price</th>
                                <th>Gross Sales</th>
                                <th>Net Profit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div class="">
                        <button type="button" class="btn btn-primary" id='close_modal' data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dr_volume_modal" tabindex="-1" aria-labelledby="drViewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Delivery Receipt Volume</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter_box" style="overflow:auto;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5>Products</h5>
                        </div>
                        <div class="">
                            <button id="dr_volume_export" class="btn btn-primary btn-sm">Export</button>
                        </div>
                    </div>
                    <div id="dr_volume_tag_legend" class="mb-2"></div>
                    <table id="dr_volume_table" class="table display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Product Name & Code</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th>Total Cost</th>
                                <th>Selling Price</th>
                                <th>Gross Sales</th>
                                <th>Net Profit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div class="">
                        <button type="button" class="btn btn-primary" id='close_modal' data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="si_dr_volume_modal" tabindex="-1" aria-labelledby="siDrViewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Total Sales Volume</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter_box" style="overflow:auto;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5>Products</h5>
                        </div>
                        <div class="">
                            <button id="si_dr_volume_export" class="btn btn-primary btn-sm">Export</button>
                        </div>
                    </div>
                    <div id="si_dr_volume_tag_legend" class="mb-2"></div>
                    <table id="si_dr_volume_table" class="table display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Product Name & Code</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th>Total Cost</th>
                                <th>Selling Price</th>
                                <th>Gross Sales</th>
                                <th>Net Profit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div class="">
                        <button type="button" class="btn btn-primary" id='close_modal' data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="<?= base_url('assets/my_js/accounting/accounting.js') ?>"></script>
<script src="<?= base_url('assets/my_js/accounting/accounting_si.js') ?>"></script>
<script src="<?= base_url('assets/my_js/accounting/accounting_dr.js') ?>"></script>
<script src="<?= base_url('assets/my_js/global.js') ?>"></script>

<?= $this->endSection() ?>