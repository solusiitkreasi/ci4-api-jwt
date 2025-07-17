<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>


<h2>Manajemen User</h2>

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
<div class="card">
    <div class="card-body">
        <div class="container mt-4">
            <?php
            $isStorePic = false;
            $isAdmin = false;
            $isSuperAdmin = false;
            if (session()->has('user_id')) {
                $userModel = new \App\Models\UserModel();
                $currentUserId = session('user_id');
                $currentUserRoles = $userModel->getRoles($currentUserId);
                foreach ($currentUserRoles as $role) {
                    $roleName = strtolower(trim($role['name']));
                    if ($roleName === 'store pic') $isStorePic = true;
                    if ($roleName === 'admin') $isAdmin = true;
                    if ($roleName === 'super admin') $isSuperAdmin = true;
                }
            }
            ?>
            <?php if ($isAdmin || $isSuperAdmin): ?>
                <a href="<?= base_url('backend/user/create') ?>" class="btn btn-primary mb-3">Tambah User</a>
            <?php endif; ?>
            <div class="row mb-3">
                <?php
                $isStorePic = false;
                $currentGroup = null;
                if (session()->has('user_id')) {
                    $userModel = new \App\Models\UserModel();
                    $currentUserId = session('user_id');
                    $currentUserRoles = $userModel->getRoles($currentUserId);
                    foreach ($currentUserRoles as $role) {
                        if (strtolower(trim($role['name'])) === 'store pic') {
                            $isStorePic = true;
                        }
                    }
                    if ($isStorePic) {
                        $currentUser = $userModel->find($currentUserId);
                        $currentGroup = $currentUser['kode_group'] ?? null;
                    }
                }
                ?>
                <div class="col-md-4" id="groupFilterContainer" <?= $isStorePic ? 'style="display:none;"' : '' ?> >
                    <label for="filterGroup" class="form-label">Filter Group Store</label>
                    <select id="filterGroup" class="form-select">
                        <option value="">Semua Group</option>
                        <?php foreach ($group_customer as $group): ?>
                            <option value="<?= $group->kode_group ?>" <?= ($isStorePic && $currentGroup == $group->kode_group) ? 'selected' : '' ?>><?= $group->nama_group ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" id="storePicGroup" value="<?= $isStorePic ? $currentGroup : '' ?>">
                <div class="col-md-4">
                    <label for="filterStore" class="form-label">Filter Store</label>
                    <select id="filterStore" class="form-select">
                        <option value="">Semua Store</option>
                    </select>
                </div>
            </div>
            <table class="table table-striped" id="userTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh DataTables -->
                </tbody>
            </table>
            <!-- Delete Modal -->
            <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Konfirmasi Hapus User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Yakin ingin menghapus user <span id="deleteUserName"></span>?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUser">Hapus</button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    var deleteUserId = null;
    $(document).on('click', '.btn-delete-user', function() {
        deleteUserId = $(this).data('id');
        var userName = $(this).data('name');
        $('#deleteUserName').text(userName);
        $('#deleteUserModal').modal('show');
    });

    $('#confirmDeleteUser').on('click', function() {
        if (deleteUserId) {
            $.ajax({
                url: '<?= base_url('backend/user/delete/') ?>' + deleteUserId,
                type: 'POST',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    $('#deleteUserModal').modal('hide');
                    userTable.ajax.reload();
                },
                error: function() {
                    alert('Gagal menghapus user.');
                }
            });
        }
    });
$(document).ready(function() {
    // Mengubah cara DataTables melaporkan error, dari alert() menjadi throw error
    $.fn.dataTable.ext.errMode = 'throw';

    var isStorePic = $('#storePicGroup').val() !== '';
    if (isStorePic) {
        $('#filterGroup').val($('#storePicGroup').val());
        $('#groupFilterContainer').hide();
    }

    var userTable = $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('backend/user/datatables') ?>",
            "type": "GET",
            "data": function(d) {
                if (isStorePic) {
                    d.filter_group = $('#storePicGroup').val();
                } else {
                    d.filter_group = $('#filterGroup').val();
                }
                d.filter_store = $('#filterStore').val();
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Terjadi kesalahan saat memuat data.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    let errorText = $(jqXHR.responseText).find('h1').text();
                    if(errorText) errorMessage = errorText;
                }
                $('#errorModalBody').html(errorMessage);
                $('#errorModal').modal('show');
                $('#userTable_processing').hide();
                $('#userTable > tbody').html(
                    '<tr><td colspan="6" class="text-center">' + errorMessage + '</td></tr>'
                );
            }
        },
        "columns": [
            { "data": "no", "name": "no", "orderable": false },
            { "data": "name", "name": "name" },
            { "data": "email", "name": "email" },
            { "data": "roles", "name": "roles" },
            { "data": "is_active", "name": "is_active" },
            { "data": "action", "name": "action", "orderable": false, "searchable": false }
        ],
        "order": [[ 1, 'asc' ]]
    });

    // Sub-select store berdasarkan group
    function loadStores(group) {
        $('#filterStore').empty().append('<option value="">Loading...</option>');
        $.ajax({
            url: "<?= base_url('backend/user/get_stores_group') ?>",
            type: 'POST',
            data: { group: group },
            dataType: 'json',
            success: function(response) {
                var stores = response.stores || [];
                var storeSelect = $('#filterStore');
                storeSelect.empty();
                storeSelect.append('<option value="">Semua Store</option>');
                $.each(stores, function(i, store) {
                    storeSelect.append('<option value="'+store.kode_customer+'">'+store.nama_customer+'</option>');
                });
            },
            error: function() {
                $('#filterStore').empty().append('<option value="">Gagal memuat store</option>');
            }
        });
    }

    if (isStorePic) {
        loadStores($('#storePicGroup').val());
    }

    $('#filterGroup').on('change', function() {
        var group = $(this).val();
        loadStores(group);
        userTable.ajax.reload();
    });
    $('#filterStore').on('change', function() {
        userTable.ajax.reload();
    });
});
</script>
<?= $this->endSection() ?>
