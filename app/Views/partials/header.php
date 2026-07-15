<?php
    $current_page = basename($_SERVER['REQUEST_URI']);
?>

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>

<style>
    /* Only what Bootstrap 5 utilities can't do */
    .nav-pill-box { border-radius: 50px; }
    .nav-pill-box .nav-link { border-radius: 50px; white-space: nowrap; }
    .nav-pill-box .nav-link.active::before {
        content: '';
        display: inline-block;
        background: url('<?= base_url('assets/logo.png') ?>') no-repeat center / contain;
        width: 0.85rem;
        height: 0.85rem;
        margin-right: 6px;
        vertical-align: middle;
    }
    /* lg (992–1199px): shrink text and padding so items still fit */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .nav-pill-box .nav-link { font-size: 0.78rem; padding: 0.3rem 0.55rem; }
        .navbar-brand { font-size: 0.95rem; }
        .navbar-brand img { height: 28px; }
    }
    @media (max-width: 1199.98px) {
        .nav-pill-box { border-radius: 0; width: 100%; }
        .nav-pill-box .nav-link { border-radius: 8px; white-space: normal; }
    }
</style>

<nav class="navbar navbar-expand-xl navbar-light bg-white shadow-sm sticky-top px-3">
    <!-- Brand / logo -->
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="<?= base_url('/') ?>">
        <img src="<?= base_url('assets/logo.png') ?>" alt="Logo" height="36">
        RPS Digital
    </a>

    <input type="hidden" id="base_url" value="<?= base_url() ?>">

    <!-- Mobile toggle -->
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
            style="margin-left:auto">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav items -->
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <div class="nav-pill-box bg-white shadow-sm px-2 py-1">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == '' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/') ?>">Home</a>
                </li>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 5): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'dashboard' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/dashboard') ?>">Dashboard</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'sales_invoice' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/sales_invoice') ?>">Sales Invoice</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'delivery_receipt' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/delivery_receipt') ?>">Delivery Receipt</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 6): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'clients' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/clients') ?>">Clients</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3 || $userRole == 4 || $userRole == 6): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'products' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/products') ?>">Products</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'user' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/user') ?>">User</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 4): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'accounting' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/accounting') ?>">Accounting</a>
                </li>
                <?php endif; ?>
                <?php if ($userRole == 1 || $userRole == 2 || $userRole == 4 || $userRole == 5 || $userRole == 6): ?>
                <li class="nav-item">
                    <a class="nav-link text-secondary <?= $current_page == 'sidrdashboard' ? 'active fw-semibold text-dark bg-black bg-opacity-10' : '' ?>" href="<?= base_url('/sidrdashboard') ?>">SI & DR Dashboard</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Profile dropdown (right side) -->
        <ul class="navbar-nav ms-auto ms-xl-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 rounded-pill border"
                   href="#" id="profileDropdown" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                          style="width:30px;height:30px;font-size:0.8rem;flex-shrink:0;">
                        <?= strtoupper(substr($session->get('username') ?? '?', 0, 1)) ?>
                    </span>
                    <span class="text-dark fw-semibold" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= esc($session->get('username') ?? '') ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileDropdown">
                    <li>
                        <a class="dropdown-item <?= $current_page == 'profile' ? 'active' : '' ?>" href="<?= base_url('/profile') ?>">
                            <i class="fa fa-user me-2"></i> My Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                            <i class="fa fa-sign-out me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    fetch('<?= base_url('/login/logout') ?>', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(() => { window.location.href = '<?= base_url('/login') ?>'; });
});
</script>
