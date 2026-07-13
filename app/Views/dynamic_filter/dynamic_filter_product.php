<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/dynamic_filter/dynamic_filter_product.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<div class="loader" id="loader"></div>

<div class="mx-5">
    <div class="row">
        <div class="col-12">
            <div class="dy_product_box">
                <div class="dy_clients_filter_title">
                    <h4>Dynamic Filter - Product</h4>
                </div>
                <hr>
                <div class="">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-7 mb-4">
                                    <div class="dy_add_filter_box_product" id="dy_add_filter_box_product">
                                        <div class="">
                                            <p>Product details</p>
                                        </div>
                                        <hr>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Filter&nbsp;name:&nbsp;</p>
                                            <input type="text" class="form-control" id="filter_name_product" style="width: 100%;">
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Product:&nbsp;</p>
                                            <select class="select2" id="product_name" style="width: 100%;"></select>
                                        </div>
                                        <div class="">
                                            <div class="table-responsive">
                                                <table id="product_filter_list_table" class="table" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="d-none">ID</th>
                                                            <th>Name</th>
                                                            <th>Item&nbsp;code</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-primary me-2" id="save_product_filter">Save&nbsp;filter</button>
                                        </div>
                                    </div>

                                    <div class="dy_add_filter_box_product" id="dy_edit_filter_box_product" style="display: none;">
                                        <div class="">
                                            <p>Product details</p>
                                        </div>
                                        <hr>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Filter&nbsp;name:&nbsp;</p>
                                            <input type="text" class="form-control" id="edit_filter_name_product" style="width: 100%;">
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <p>Product&nbsp;name:&nbsp;</p>
                                            <select class="select2" id="edit_products_details" style="width: 100%;"></select>
                                        </div>
                                        <div class="">
                                            <div class="table-responsive">
                                                <table id="edit_product_filter_list_table" class="table" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="d-none">ID</th>
                                                            <th>Name</th>
                                                            <th>Item&nbsp;code</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-warning me-2" id="edit_filter_product">Edit&nbsp;filter</button>
                                            <button class="btn btn-secondary me-2" id="cancel_edit_product">Cancel&nbsp;edit</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="dy_add_filter_box_product">
                                        <div class="">
                                            <p>Product filter name</p>
                                        </div>
                                        <hr>
                                        <div class="">
                                            <div class="table-responsive">
                                                <table id="filter_name_table_product" class="table" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="d-none">ID</th>
                                                            <th>Filter&nbsp;name</th>
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
        </div>
    </div>
</div>

<!-- Modal 1 start -->

<div class="modal fade" id="dynamic_filter_view_modal" tabindex="-1" aria-labelledby="dynamic_filter_view_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">View Product Filter</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal_box_product">
                    <p id="product_filter_name"></p>
                    <div class="row mb-3">
                        <div class="col-12 ps-4" id="product_filter_items_container">
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1 end -->

<script src="<?= base_url('assets/my_js/dynamic_filter/dynamic_filter_product.js') ?>"></script>
<script src="<?= base_url('assets/my_js/global.js') ?>"></script>

<?= $this->endSection() ?>