<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>
    <h1 class="h3 mb-3"><?= (isset($role) && isset($role['id'])) ? 'Edit' : 'Tambah' ?> Role</h1>
    <div class="card">
        <div class="card-body">
            <?php if (isset($validation) && is_array($validation) && count($validation) > 0): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($validation as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= (isset($role) && isset($role['id'])) ? base_url('backend/role/edit/'.$role['id']) : base_url('backend/role/create') ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Role</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($role['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description"><?= esc($role['description'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?= base_url('backend/role') ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
