<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php $session = session(); ?>
<?php 
    $userRole = $session->get('role'); 
?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/products/products.css') ?>">

<input type="hidden" id="user_role" value="<?= isset($user_role) ? $user_role : '' ?>">

<div class="loader" id="loader"></div>

<div class="mx-2 mx-md-3 mx-lg-5">
    <div class="row">
        <div class="col-12">
            <div class="product_box">
                <div class="product_title">
                    <p>Products</p>
                </div>
                <hr>
                <div class="">
                    <?php if ($userRole == 1 || $userRole == 2 || $userRole == 3): ?>
                        <div class="row">
                            <div class="col-12 d-flex justify-content-start">
                                <button type="button" id="addProductBtn" class="btn btn-primary mb-2 me-2" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div>
                        <table id="product_table" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>Name</th>
                                    <th>Item&nbsp;Code</th>
                                    <th>Units</th>
                                    <th>kg/ml</th>
                                    <th>Price</th>
                                    <th>Cost</th>
                                    <th>Tag</th>
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
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Add Product</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal_box">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" id="product_name">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Item Code</label>
                    <input type="text" class="form-control" id="product_item">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Units</label>
                    <input type="text" class="form-control" id="product_unit">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">kg/ml</label>
                    <input type="number" class="form-control" id="product_weight">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Price</label>
                    <input type="number" class="form-control" id="product_price">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Tag</label>
                    <select class="select2" id="product_tag" style="width: 100%;">
                        <option value="na">N/A</option>
                        <option value="A">Product&nbsp;A</option>
                        <option value="B">Atlas&nbsp;B</option>
                        <option value="C">Product&nbsp;C</option>
                    </select>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='save_product'>Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Edit Product</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal_box">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" id="edit_product_name">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Item Code</label>
                    <input type="text" class="form-control" id="edit_product_item">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Units</label>
                    <input type="text" class="form-control" id="edit_product_unit">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">kg/ml</label>
                    <input type="number" class="form-control" id="edit_product_weight">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Price</label>
                    <input type="number" class="form-control" id="edit_product_price">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Tag</label>
                    <select class="select2" id="edit_product_tag" style="width: 100%;">
                        <option value="na">N/A</option>
                        <option value="A">Product&nbsp;A</option>
                        <option value="B">Product&nbsp;B</option>
                        <option value="C">Product&nbsp;C</option>
                    </select>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='save_edit_product'>Save changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Product Cost Modal -->
<div class="modal fade" id="productCostModal" tabindex="-1" aria-labelledby="productCostModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="productCostModalLabel">Set Product Cost</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cost_product_id">
        <label class="form-label fw-semibold">Cost</label>
        <input type="number" step="0.01" min="0" class="form-control" id="product_cost_input" placeholder="Enter product cost">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="save_product_cost">Save</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/my_js/products/products.js') ?>"></script>

<?= $this->endSection() ?>