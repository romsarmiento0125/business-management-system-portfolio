<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/dashboard/dashboard.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>

<div class="loader" id="loader"></div>

<div class="mx-2 mx-md-3 mx-lg-5">
    <div class="row">
        <div class="col-12">
            <div class="dashboard_box">
                <div class="dashboard_title">
                    <p>Dashboard</p>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12 col-lg-4 d-flex align-items-end flex-wrap">
                        <div class="form-group">
                            <label>Date range:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control float-right" id="reservation">
                            </div>
                        </div>
                        <div class="ms-2 mb-3">
                            <button id="export_accounting_total" class="btn btn-primary btn-sm">Detailed List</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fa fa-truck" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">DR&nbsp;Total&nbsp;Amount</span>
                                <span class="info-box-number" id="dr_total_amount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary elevation-1"><i class="fa fa-ship" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">DR&nbsp;Total&nbsp;Freight</span>
                                <span class="info-box-number" id="dr_total_freight">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-barcode" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Total&nbsp;Amount</span>
                                <span class="info-box-number" id="si_total_amount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary elevation-1"><i class="fa fa-ship" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Total&nbsp;Freight</span>
                                <span class="info-box-number" id="si_total_freight">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-sticky-note" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Total&nbsp;Vatable&nbsp;Sales</span>
                                <span class="info-box-number" id="si_total_vatable_sales">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success elevation-1"><i class="fa fa-file" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Total&nbsp;Vat&nbsp;Exempt&nbsp;Sales</span>
                                <span class="info-box-number" id="si_total_vat_exempt_sales">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-adjust" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Total&nbsp;Vat&nbsp;Amount</span>
                                <span class="info-box-number" id="si_total_vat_amount">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($userRole == 1 || $userRole == 2): ?>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success elevation-1"><i class="fa fa-truck" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">DR&nbsp;Net&nbsp;Profit <i class="fa fa-eye toggle-mask" data-target="dr_gains" role="button"></i></span>
                                <span class="info-box-number" id="dr_gains">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-barcode" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SI&nbsp;Net&nbsp;Profit <i class="fa fa-eye toggle-mask" data-target="si_gains" role="button"></i></span>
                                <span class="info-box-number" id="si_gains">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-chart-line" aria-hidden="true"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total&nbsp;Net&nbsp;Profit <i class="fa fa-eye toggle-mask" data-target="total_gains" role="button"></i></span>
                                <span class="info-box-number" id="total_gains">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hidden container for export table -->
<div class="data_export_table" style="display:none;"></div>

<script src="<?= base_url('assets/my_js/dashboard/dashboard.js') ?>"></script>

<?= $this->endSection() ?>