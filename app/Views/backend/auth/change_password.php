<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-3">Ganti Password</h1>
<div class="card">
    <div class="card-body" style="max-width:400px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"> <?= esc($error) ?> </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= esc($success) ?> </div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('backend/change_password') ?>">
            <div class="mb-3">
                <label for="old_password" class="form-label">Password Lama</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
