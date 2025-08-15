<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>
    <h1 class="h3 mb-3"><?= isset($permission) ? 'Edit' : 'Tambah' ?> Permission</h1>
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
            <form method="post" action="<?= (isset($permission) && isset($permission['id'])) ? base_url('backend/permission/edit/'.$permission['id']) : base_url('backend/permission/create') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Permission</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($permission['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?= esc($permission['slug'] ?? '') ?>" readonly required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description"><?= esc($permission['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tampilkan di Menu?</label><br>
                    <input type="checkbox" id="menu_on" name="menu_on" value="1" <?= (isset($permission['menu_on']) && $permission['menu_on']) ? 'checked' : '' ?>> <label for="menu_on">Ya, tampilkan di menu sidebar</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipe Permission</label><br>
                    <input type="radio" id="as_parent" name="tipe_permission" value="parent" checked> <label for="as_parent">Sebagai Parent Menu</label>
                    <input type="radio" id="as_sub" name="tipe_permission" value="sub" style="margin-left:20px;"> <label for="as_sub">Sebagai Submenu</label>
                    <input type="radio" id="as_permission" name="tipe_permission" value="permission" style="margin-left:20px;"> <label for="as_permission">Sebagai Permission (biasa)</label>
                </div>
                <div class="mb-3" id="parent-menu-group" style="display:none;">
                    <label class="form-label">Pilih Parent Menu</label>
                    <?php require __DIR__.'/_parent_dropdown.php'; ?>
                </div>
                <div class="mb-3" id="allparent-group" style="display:none;">
                    <label class="form-label">Pilih Parent Permission</label>
                    <?php require __DIR__.'/_allparent_dropdown.php'; ?>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?= base_url('backend/permission') ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    <script>
    // Otomatisasi slug dari nama permission
    document.addEventListener('DOMContentLoaded', function() {
        var nameInput = document.getElementById('name');
        var slugInput = document.getElementById('slug');
        var menuOn = document.getElementById('menu_on');
        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^a-z0-9\-]/g, '')    // Remove all non-alphanumeric except -
                .replace(/\-+/g, '-')           // Replace multiple - with single -
                .replace(/^-+|-+$/g, '');        // Trim - from start/end
        }
        nameInput.addEventListener('input', function() {
            slugInput.value = slugify(this.value);
        });
        // Tampilkan/hidden parent menu group
        var asParent = document.getElementById('as_parent');
        var asSub = document.getElementById('as_sub');
        var asPermission = document.getElementById('as_permission');
        var parentGroup = document.getElementById('parent-menu-group');
        var allParentGroup = document.getElementById('allparent-group');
        asParent.addEventListener('change', function() {
            if(this.checked) {
                parentGroup.style.display = 'none';
                allParentGroup.style.display = 'none';
                menuOn.disabled = false;
            }
        });
        asSub.addEventListener('change', function() {
            if(this.checked) {
                parentGroup.style.display = '';
                allParentGroup.style.display = 'none';
                menuOn.disabled = false;
            }
        });
        asPermission.addEventListener('change', function() {
            if(this.checked) {
                parentGroup.style.display = 'none';
                allParentGroup.style.display = '';
                menuOn.checked = false;
                menuOn.disabled = true;
            }
        });
    });
    </script>
<?= $this->endSection() ?>
