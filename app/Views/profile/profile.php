<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container py-4" style="max-width:560px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <!-- Avatar + name header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold"
                     style="width:56px;height:56px;font-size:1.4rem;flex-shrink:0;">
                    <?= strtoupper(substr($user->username ?? '?', 0, 1)) ?>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold"><?= esc($user->first_name . ' ' . $user->last_name) ?></h5>
                    <small class="text-muted">@<?= esc($user->username) ?> &middot; <?= esc($user->user_role) ?></small>
                </div>
            </div>

            <hr>

            <!-- Edit form -->
            <form id="profileForm">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control bg-light" value="<?= esc($user->username) ?>" disabled>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= esc($user->first_name) ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= esc($user->last_name) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Leave blank to keep current password" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               placeholder="Confirm new password" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        const password        = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password && password !== confirmPassword) {
            showUniversalModal('Error', 'Passwords do not match.', function() {});
            return;
        }

        const payload = {
            first_name: $('#first_name').val().trim(),
            last_name:  $('#last_name').val().trim(),
            password:   password || null,
        };

        $.ajax({
            url: '<?= base_url('/profile/update') ?>',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: JSON.stringify(payload),
            success: function(data) {
                if (data.status === 'success') {
                    showUniversalModal('Success', 'Profile updated successfully.', function() {
                        location.reload();
                    });
                } else {
                    showUniversalModal('Error', data.message, function() {});
                }
            },
            error: function() {
                showUniversalModal('Error', 'Something went wrong. Please try again.', function() {});
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
