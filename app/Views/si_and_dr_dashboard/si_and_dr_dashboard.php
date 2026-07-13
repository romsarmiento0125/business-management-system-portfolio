<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>


<link rel="stylesheet" href="<?= base_url('assets/my_css/si_and_dr_dashboard/si_and_dr_dashboard.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<input type="hidden" id="user_role" value="<?= isset($user_role) ? $user_role : '' ?>">

<div class="loader" id="loader"></div>

<div class="mx-2 mx-md-5">
    <div class="row">
        <div class="col-12">
            <div class="si_dr_box">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <div class="si_dr_title">
                        <p>SI and DR</p>
                    </div>
                    <div class="header_button d-flex flex-wrap gap-1">
                        <button class="btn btn-warning btn-sm btn-md-normal" id="btn_show_si">Sales Invoice</button>
                        <button class="btn btn-secondary btn-sm btn-md-normal" id="btn_show_dr">Delivery Receipt</button>
                    </div>
                </div>
                <hr>
                <div class="row mb-3 g-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="form-group">
                            <label>Date range:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control float-right" id="si_dr_date_range">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="form-group">
                            <label for="payment_status_filter">Payment status:</label>
                            <select id="payment_status_filter" name="payment_status_filter" class="form-control">
                                <option value="">All</option>
                                <option value="PAID">PAID</option>
                                <option value="UNPAID">UNPAID</option>
                                <option value="CANCELED">CANCELED</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <?php if ($userRole != 6): ?>
                        <div class="d-flex flex-wrap justify-content-center align-items-center h-100 gap-2">
                            <div class="info-box mx-1 mb-0">
                                <span class="info-box-icon bg-success elevation-1"><i class="fa fa-money" aria-hidden="true"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text"><span id="account_type_paid"></span>&nbsp;Paid</span>
                                    <span class="info-box-number" id="paid_amount">0</span>
                                </div>
                            </div>
                            <div class="info-box mx-1 mb-0">
                                <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-credit-card" aria-hidden="true"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text"><span id="account_type_unpaid"></span>&nbsp;Unpaid</span>
                                    <span class="info-box-number" id="unpaid_amount">0</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row" id="si_table_container">
                    <div class="col-12 mb-4">
                        <div class="sales_invoice_details_box">
                            <div class="sales_invoice_details_title">
                                <p>Sales Invoice</p>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table id="sales_invoice_list_table" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center">SI_ID</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Terms</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Payment&nbsp;Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="dr_table_container" style="display: none;">
                    <div class="col-12 mb-4">
                        <div class="delivery_receipt_details_box">
                            <div class="delivery_receipt_details_title">
                                <p>Delivery Receipts</p>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table id="delivery_receipt_list_table" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center">DR_ID</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Terms</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Payment&nbsp;Status</th>
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

<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="viewModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Authentication Required</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" autocomplete="off" autofill="off" id="auth_form">
                <div class="modal-body mx-auto">
                    <!-- <div class="modal_box"> -->
                    <div class="d-flex align-items-center mb-2">
                        <p>Username:&nbsp;</p>
                        <input type="text" class="form-control" id="auth_username" name="auth_username" autocomplete="username" autofill="on">
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <p>Password:&nbsp;</p>
                        <input type="password" id="auth_password" name="auth_password" class="form-control" autocomplete="current-password" autofill="on">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="auth_submit_btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="" id="view_si_receipt_modal"></div>

<div class="" id="view_dr_receipt_modal"></div>

<div class="" id="si_auth_modal"></div>

<div class="" id="dr_auth_modal"></div>

<script src="<?= base_url('assets/my_js/si_and_dr_dashboard/si_and_dr_dashboard.js') ?>"></script>
<script src="<?= base_url('assets/my_js/view_receipt/sales_invoice_receipt.js') ?>"></script>
<script src="<?= base_url('assets/my_js/view_receipt/delivery_receipt_receipt.js') ?>"></script>
<script src="<?= base_url('assets/my_js/global.js') ?>"></script>

<?= $this->endSection() ?>