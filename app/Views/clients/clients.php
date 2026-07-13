<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/clients/clients.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<input type="hidden" id="user_role" value="<?= isset($user_role) ? $user_role : '' ?>">

<div class="loader" id="loader"></div>

<div class="mx-2 mx-md-3 mx-lg-5">
    <div class="row">
        <div class="col-12">
            <div class="client_box">
                <div class="client_title">
                    <p>Clients</p>
                </div>
                <hr>
                <div class="">
                    <?php if ($userRole != 4 && $userRole != 6): ?>
                    <button type="button" id="addClientBtn" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addClientModal">Add Client</button>
                    <?php endif; ?>
                    <div class="">
                        <table id="client_table" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="d-none">ID</th>
                                    <th>Name</th>
                                    <th>TIN&nbsp;Code</th>
                                    <th>Address</th>
                                    <th>Company</th>
                                    <th>Term</th>
                                    <th>Status</th>
                                    <th class='text-center'>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModal" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Add Client</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal_box">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" class="form-control" id="client_name">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">TIN</label>
                                <input type="text" class="form-control" id="client_tin">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control" id="client_business_name">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Term</label>
                                <select class="select2" id="client_term" style="width: 100%;">
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
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" class="form-control" id="client_address">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='save_client'>Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Edit Client</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal_box">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" class="form-control" id="edit_client_name">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">TIN</label>
                            <input type="text" class="form-control" id="edit_client_tin">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Business Name</label>
                            <input type="text" class="form-control" id="edit_client_business_name">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Term</label>
                            <select class="select2" id="edit_client_term" style="width: 100%;">
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
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" class="form-control" id="edit_client_address">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id='save_edit_client'>Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="volumeModal" tabindex="-1" aria-labelledby="volumeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content volume-modal-content">
            <div class="modal-header volume-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="volume-modal-icon"><i class="fas fa-chart-bar"></i></div>
                    <div>
                        <h1 class="modal-title fs-5 mb-0" id="volumeModalLabel">Client Volume</h1>
                        <small class="text-white-50" id="volume_modal_client_name"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="volume_modal_client_id">

                <!-- Date Range & Product Filter -->
                <div class="volume-filter-card mb-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="volume_daterange" autocomplete="off" placeholder="Select date range">
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-1">Product Filter</label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-grow-1">
                                <select class="select2 w-100" id="product_filter"></select>
                            </div>
                            <button id="add_custom_product_filter" class="btn btn-outline-primary btn-sm" title="Manage product filters">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Volume Type Buttons -->
                <div class="volume-type-group mb-4">
                    <button type="button" class="btn btn-info text-white fw-semibold flex-fill" id="volume_si_btn">
                        <i class="fas fa-file-invoice me-1"></i> SI
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold flex-fill" id="volume_sidr_btn">
                        <i class="fas fa-layer-group me-1"></i> SI &amp; DR
                    </button>
                    <button type="button" class="btn btn-warning text-white fw-semibold flex-fill" id="volume_dr_btn">
                        <i class="fas fa-truck me-1"></i> DR
                    </button>
                </div>

                <!-- Result Display -->
                <div class="volume-result-card" id="volume_result_card" style="display:none;">
                    <div class="volume-result-label" id="volume_result_label"></div>
                    <div class="volume-result-value" id="volume_result"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/my_js/clients/clients.js') ?>"></script>
<script src="<?= base_url('assets/my_js/global.js') ?>"></script>

<?= $this->endSection() ?>