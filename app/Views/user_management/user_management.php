<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/user_management/user_management.css') ?>">

<div class="loader" id="loader"></div>

<div class="mx-5">
    <div class="row">
        <div class="col-12">
            <div class="user_box">
                <div class="user_title">
                    <p>User</p>
                </div>
                <hr>
                <div class="">
                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                    <div class="">
                        <table id="user_table" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="d-none">ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
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

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Add user</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal_box">
            <form id="user_form">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Username:&nbsp;</p>
                            <input type="text" class="form-control" id="user_username">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>First&nbsp;name:&nbsp;</p>
                            <input type="text" class="form-control" id="user_firstname">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Last&nbsp;name:&nbsp;</p>
                            <input type="text" class="form-control" id="user_lastname">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Password:&nbsp;</p>
                            <input type="password" class="form-control" id="user_password">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Role:&nbsp;</p>
                            <select class="select2" id="user_role" style="width: 100%;">
                                <!-- Options will be dynamically populated -->
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='save_user'>Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editUsertModal" tabindex="-1" aria-labelledby="editUsertModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Edit User</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal_box">
            <form id="user_edit_form">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Username:&nbsp;</p>
                            <p class="" id="edit_user_username"></p>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>First&nbsp;name:&nbsp;</p>
                            <input type="text" class="form-control" id="edit_user_firstname">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Last&nbsp;name:&nbsp;</p>
                            <input type="text" class="form-control" id="edit_user_lastname">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Password:&nbsp;</p>
                            <input type="password" class="form-control" id="edit_user_password">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <p>Role:&nbsp;</p>
                            <select class="select2" id="edit_user_role" style="width: 100%;">
                                <!-- Options will be dynamically populated -->
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='save_edit_user'>Save changes</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/my_js/user_management/user_management.js') ?>"></script>

<?= $this->endSection() ?>