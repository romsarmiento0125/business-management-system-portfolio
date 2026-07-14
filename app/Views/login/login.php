<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<style>
    body { background: linear-gradient(135deg, #e8f5e9 0%, #f4f7f2 60%, #e0f0e3 100%) !important; }
    .form-control:focus { box-shadow: 0 0 0 3px rgba(76,175,80,0.18); border-color: #81c784; }
</style>

<div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
    <div class="card border-0 rounded-4 shadow-sm w-100" style="max-width:420px;">
        <div class="card-body p-4 p-sm-5">

            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="<?= base_url('assets/logo.png') ?>" alt="Logo" width="48" height="48" class="object-fit-contain">
                <div>
                    <div class="fw-bold fs-6 text-dark">RPS Digital</div>
                    <div class="text-muted" style="font-size:0.82rem;">Rom Paulo Sarmiento</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold small">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       placeholder="Enter your username" autocomplete="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold small">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter your password" autocomplete="current-password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                        <i class="fa fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <button type="button" class="btn btn-success w-100 fw-semibold" onclick="userLogin()">
                Sign In
            </button>

        </div>
    </div>
</div>

<script>
    const BASE_URL = <?= json_encode(base_url()) ?>;

    $('#togglePassword').on('click', function() {
        const input = $('#password');
        const icon  = $('#toggleIcon');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    function userLogin() {
        var username = $('#username').val();
        var password = $('#password').val();
        var csrfName = '<?= csrf_token() ?>';
        var csrfHash = $('input[name="<?= csrf_token() ?>"]').val();

        if (username === '' || password === '') {
            alert('Username and password cannot be empty.');
            return;
        }

        $.ajax({
            url: '<?= base_url('login/authenticate') ?>',
            type: 'POST',
            data: {
                username: username,
                password: password,
                [csrfName]: csrfHash
            },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        window.location.href = '<?= base_url('/') ?>';
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    alert('An error occurred. Check the console for details.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', { status, error, responseText: xhr.responseText });
                alert('Connection error: ' + error + ' (Status: ' + xhr.status + ')');
            }
        });
    }

    $(document).on('keypress', function(e) {
        if (e.which === 13) { userLogin(); }
    });
</script>
<?= $this->endSection() ?>