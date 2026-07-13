<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>

<style>
    .fluid-container img {
        width: 100%;
        height: 40vh;
        object-fit: cover;
    }
    .nav-box {
        display: block;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        aspect-ratio: 4 / 3;
        background-color: #e0e0e0;
    }
    .nav-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .nav-box .nav-label {
        position: absolute;
        bottom: 0;
        width: 100%;
        background: linear-gradient(transparent, rgba(0,0,0,0.72));
        color: #fff;
        text-align: center;
        padding: 28px 8px 10px;
        font-size: clamp(0.75rem, 2vw, 1rem);
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .nav-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.18);
    }
    .nav-box:active {
        transform: translateY(-1px);
    }
</style>

<div class="main-div">
    <div class="fluid-container d-none d-lg-block">
        <img src="<?php echo base_url('assets/banner.jpg'); ?>" alt="">
    </div>

    <div class="p-3 p-md-4">
        <div class="row g-3">
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 5): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/dashboard" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/dashboard_module.png') ?>" alt="Dashboard">
                    <div class="nav-label">Dashboard</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/sales_invoice" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/sales_invoice_module.png') ?>" alt="Sales Invoice">
                    <div class="nav-label">Sales Invoice</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/delivery_receipt" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/delivery_receipt_module.png') ?>" alt="Delivery Receipt">
                    <div class="nav-label">Delivery Receipt</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 6): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/clients" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/clients_module.png') ?>" alt="Clients">
                    <div class="nav-label">Clients</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 6): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/products" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/products_module.png') ?>" alt="Products">
                    <div class="nav-label">Products</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/user" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/user_module.png') ?>" alt="User Management">
                    <div class="nav-label">User Management</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 4): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/accounting" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/accounting_module.png') ?>" alt="Accounting">
                    <div class="nav-label">Accounting</div>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($userRole == 1 || $userRole == 2 || $userRole == 4 || $userRole == 5 || $userRole == 6): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="/sidrdashboard" class="nav-box">
                    <img src="<?= base_url('assets/home_photo/sidrdashboard_module.png') ?>" alt="SI & DR Dashboard">
                    <div class="nav-label">SI &amp; DR Dashboard</div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>