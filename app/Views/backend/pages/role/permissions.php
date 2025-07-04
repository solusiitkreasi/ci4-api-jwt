<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>
    <h1 class="h3 mb-3">Permission untuk Role: <?= esc($role['name']) ?></h1>
    <div class="card">
        <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= base_url('backend/role/permissions/'.$role['id']) ?>">
                <div class="mb-2">
                    <input type="checkbox" id="checkAllMaster" class="form-check-input" style="margin-right:8px;">
                    <label for="checkAllMaster" style="font-weight:bold;cursor:pointer;">Check/Uncheck Semua Permission</label>
                </div>
                <div class="mb-3">
                    <?php 
                    require __DIR__ . '/_permission_tree.php';
                    renderPermissionTree($permissions, $role_permission_ids);
                    ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Permission</button>
                <a href="<?= base_url('backend/role') ?>" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
    <style>
    .permission-list { list-style: none; padding-left: 0; }
    .permission-item { margin-bottom: 4px; background: #f8f9fa; border-radius: 4px; transition: box-shadow 0.2s; }
    .permission-item.sortable-chosen { box-shadow: 0 0 8px #007bff55; background: #e9f5ff; }
    .permission-item.sortable-ghost { opacity: 0.5; }
    .drag-handle { cursor: grab; margin-right: 8px; color: #888; }
    .form-check-label { cursor: pointer; }
    </style>
    <script src="/assets/admin/js/sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Checklist parent -> sub ikut checklist (nested recursive)
        document.querySelectorAll('.form-check-input').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var li = this.closest('li.permission-item');
                if (!li) return;
                // Check/uncheck semua anak
                checkAllChildren(li, this.checked);
                // Jika uncheck, parent di atasnya juga ikut uncheck
                if (!this.checked) {
                    uncheckAllParents(li);
                }
            });
        });
        function checkAllChildren(li, checked) {
            li.querySelectorAll('ul.permission-list .form-check-input').forEach(function(childCb) {
                childCb.checked = checked;
            });
        }
        function uncheckAllParents(li) {
            var parentUl = li.parentElement.closest('li.permission-item');
            if (parentUl) {
                var parentCb = parentUl.querySelector('> .form-check .form-check-input');
                if (parentCb) {
                    parentCb.checked = false;
                    uncheckAllParents(parentUl);
                }
            }
        }
        // Inisialisasi SortableJS pada semua ul.permission-list
        document.querySelectorAll('ul.permission-list').forEach(function(ul) {
            new Sortable(ul, {
                animation: 200,
                group: 'permission',
                handle: '.drag-handle',
                fallbackOnBody: true,
                swapThreshold: 0.65,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function (evt) {
                    updateSequenceInputs();
                }
            });
        });
        // Update urutan ke hidden input
        function updateSequenceInputs() {
            var order = [];
            document.querySelectorAll('ul.permission-list').forEach(function(ul) {
                var parent = ul.getAttribute('data-parent') || null;
                Array.from(ul.children).forEach(function(li, idx) {
                    var id = li.getAttribute('data-id');
                    order.push({id: id, parent_id: parent, sequence: idx+1});
                });
            });
            // Hapus input lama
            document.querySelectorAll('.perm-sequence-input').forEach(function(e){e.remove();});
            // Tambah input baru
            var form = document.querySelector('form');
            order.forEach(function(item) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'perm_sequence['+item.id+']';
                input.value = item.sequence+'|'+(item.parent_id||'0');
                input.className = 'perm-sequence-input';
                form.appendChild(input);
            });
        }
        updateSequenceInputs();

        // Master Check/Uncheck All
        var masterCb = document.getElementById('checkAllMaster');
        masterCb.addEventListener('change', function() {
            document.querySelectorAll('.form-check-input').forEach(function(cb) {
                if(cb !== masterCb) cb.checked = masterCb.checked;
            });
        });
        // Sync master checkbox state
        function syncMasterCheckbox() {
            var all = Array.from(document.querySelectorAll('.form-check-input')).filter(cb => cb !== masterCb);
            var checked = all.filter(cb => cb.checked).length;
            masterCb.checked = checked === all.length && all.length > 0;
            masterCb.indeterminate = checked > 0 && checked < all.length;
        }
        document.querySelectorAll('.form-check-input').forEach(function(cb) {
            if(cb !== masterCb) cb.addEventListener('change', syncMasterCheckbox);
        });
        syncMasterCheckbox();
    });
    </script>
<?= $this->endSection() ?>
