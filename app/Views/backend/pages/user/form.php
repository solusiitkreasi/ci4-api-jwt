<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>


<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= esc($errors) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
        <div class="alert alert-errors alert-dismissible fade show" role="alert">
            <?= $errors ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<div class="card">
    <div class="card-body">

        <h2><?= isset($user) ? 'Edit User' : 'Tambah User' ?></h2>
        <form method="post" action="<?= isset($user) ? base_url('backend/user/edit/'.$user['id']) : base_url('backend/user/create') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="group_store" class="form-label">Group Store</label>
                <select id="group_store" name="group_store" required class="form-control select2 <?= isset($errors['group_store']) ? 'is-invalid' : '' ?>">
                    <option value="">Pilih Group Store...</option>
                    <?php foreach ($group_customer as $group): ?>
                        <option value="<?= $group->kode_group ?>" <?= (old('group_store', $user['kode_group'] ?? '') == $group->kode_group) ? 'selected' : '' ?>><?= $group->nama_group ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($errors['group_store'])): ?>
                    <div class="invalid-feedback"><?= $errors['group_store'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="store" class="form-label">Store</label>
                <select id="store" name="store" required class="form-control select2 <?= isset($errors['store']) ? 'is-invalid' : '' ?>">
                    <option value="">Pilih Group Store terlebih dahulu...</option>
                    <?php if(isset($stores) && !empty($stores)): ?>
                        <?php foreach($stores as $s): ?>
                            <option value="<?= $s->kode_customer ?>" <?= (old('store', $user['kode_customer'] ?? '') == $s->kode_customer) ? 'selected' : '' ?>><?= $s->nama_customer ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if(isset($errors['store'])): ?>
                    <div class="invalid-feedback"><?= $errors['store'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $user['name'] ?? '') ?>" required>
                <?php if(isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required <?= isset($user) ? 'readonly' : '' ?> >
                <?php if(isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Multiple Start -->
            <div class="mb-3 w-100">
                <label class="form-label">Data Role</label>
                <select multiple="multiple" id="select2Multiple" name="roles[]" class="form-control select2-multiple <?= isset($errors['roles']) ? 'is-invalid' : '' ?>" required style="width:100%">
                    <?php
                    $isStorePic = false;
                    $isAdmin = false;
                    $isSuperAdmin = false;
                    if (session()->has('user_id')) {
                        $userModel = new \App\Models\UserModel();
                        $currentUserId = session('user_id');
                        $currentUserRoles = $userModel->getRoles($currentUserId);
                        foreach ($currentUserRoles as $roleCheck) {
                            $roleName = strtolower(trim($roleCheck['name']));
                            if ($roleName === 'store pic') $isStorePic = true;
                            if ($roleName === 'admin') $isAdmin = true;
                            if ($roleName === 'super admin') $isSuperAdmin = true;
                        }
                    }
                    foreach($allRoles as $role):
                        $roleName = strtolower(trim($role['name']));
                        $optionStyle = 'style="color:black"';
                        if ($isSuperAdmin) {
                            echo '<option value="'.$role['id'].'" '.$optionStyle.(isset($selectedRoles) && in_array($role['id'], $selectedRoles) ? ' selected' : '').'>'.$role['name'].'</option>';
                        } elseif ($isAdmin) {
                            if ($roleName === 'store pic' || $roleName === 'client' ) {
                                echo '<option value="'.$role['id'].'" '.$optionStyle.(isset($selectedRoles) && in_array($role['id'], $selectedRoles) ? ' selected' : '').'>'.$role['name'].'</option>';
                            }
                        } elseif ($isStorePic) {
                            if ($roleName === 'client') {
                                echo '<option value="'.$role['id'].'" '.$optionStyle.(isset($selectedRoles) && in_array($role['id'], $selectedRoles) ? ' selected' : '').'>'.$role['name'].'</option>';
                            }
                        }
                    endforeach;
                    ?>
                <?= isset($isStorePic) ? 'disabled' : '' ?>
                </select>
                <?php if(isset($errors['roles'])): ?>
                    <div class="invalid-feedback d-block"><?= $errors['roles'] ?></div>
                <?php endif; ?>
            </div>
            <!-- Multiple End -->

            
            <div class="mb-3">
                <label for="is_active" class="form-label">Status</label>
                <select class="form-control" id="is_active" name="is_active">
                    <option value="1" <?= (old('is_active', $user['is_active'] ?? 1) == 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= (old('is_active', $user['is_active'] ?? 1) == 0) ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password <?= isset($user) ? '(Kosongkan jika tidak diubah)' : '' ?></label>
                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" <?= isset($user) ? '' : 'required' ?> >
                <?php if(isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('backend/user') ?>" class="btn btn-warning">Batal</a>
        </form>

    </div>
</div>


<style>
/* Select2 dropdown highlighted/selected option font color black */
.select2-container--bootstrap-5 .select2-results__option.select2-results__option--highlighted {
    color: #000 !important;
}
.select2-container--bootstrap4 .select2-results__option--highlighted, .select2-container--bootstrap4 .select2-results__option--highlighted.select2-results__option[aria-selected=true] {
    color: #00362b !important;
}
</style>
<script src="<?= base_url('assets/admin/') ?>js/vendor/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    // Pastikan text select2-multiple tetap hitam
    $('#select2Multiple option').css('color', 'black');
    $('#select2Multiple').on('change', function() {
        $('#select2Multiple option').css('color', 'black');
    });
    // Jika pakai Select2, pastikan dropdown dan selected item hitam
    if ($.fn.select2) {
        $('#select2Multiple').select2({
            theme: 'bootstrap-5'
        });
        $('#select2Multiple').on('select2:open', function() {
            $('.select2-results__option').css('color', 'black');
        });
        $('#select2Multiple').on('select2:select', function() {
            $('.select2-selection__choice').css('color', 'black');
        });
    }
});
$(document).ready(function() {
    // Inisialisasi Select2 pada elemen yang relevan
    // $('.select2').select2({ theme: 'bootstrap-5' });
    // $('.select2-multiple').select2({ theme: 'bootstrap-5' });

    $('#group_store').on('change', function() {
        let group = $(this).val();
        let storeSelect = $('#store');

        // Kosongkan dan beri status loading
        storeSelect.empty().append(new Option('Loading...', '')).trigger('change');

        if (!group) {
            storeSelect.empty().append(new Option('Pilih Group Store terlebih dahulu...', '')).trigger('change');
            return;
        }

        $.ajax({
            url: "/register/get-customer-by-group", ////base_url('backend/user/get-stores-by-group') ?>
            type: 'POST',
            data: { group: group }, // CSRF tidak perlu karena dinonaktifkan secara global
            dataType: 'json',
            success: function(response) {
                // Dapatkan array of stores dari response (sesuaikan jika struktur response berbeda)
                let stores = response.stores || response;

                storeSelect.empty(); // Hapus semua opsi yang ada

                if (stores && stores.length > 0) {
                    storeSelect.append(new Option('Pilih Store...', ''));
                    $.each(stores, function(i, store) {
                        storeSelect.append(new Option(store.nama_customer, store.kode_customer));
                    });
                } else {
                    storeSelect.append(new Option('Tidak ada store di grup ini', ''));
                }

                // PENTING: Beritahu Select2 untuk memperbarui UI setelah semua opsi baru ditambahkan
                storeSelect.trigger('change');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                storeSelect.empty().append(new Option('Gagal memuat data store', '')).trigger('change');
            }
        });
    });


    $('#store').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        if(selectedText && selectedText !== '' && selectedText !== 'Select Store') {
            $('#name').val(selectedText);
        } else {
            $('#name').val('');
        }
    });
});
</script>
<?php $this->endSection(); ?>
