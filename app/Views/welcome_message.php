<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1>Welcome to CodeIgniter 4!</h1>
    <p>This is a simple example of using Bootstrap in CodeIgniter 4.</p>
    <button type="button" class="btn btn-primary">Primary</button>
    <button type="button" class="btn btn-secondary">Secondary</button>
    <button type="button" class="btn btn-success">Success</button>
    <button type="button" class="btn btn-danger">Danger</button>
    <button type="button" class="btn btn-warning">Warning</button>
    <button type="button" class="btn btn-info">Info</button>
    <button type="button" class="btn btn-light">Light</button>
    <button type="button" class="btn btn-dark">Dark</button>

    <button type="button" class="btn btn-link">Link</button>
</div>
<?= $this->endSection() ?>