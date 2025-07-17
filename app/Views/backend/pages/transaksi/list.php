<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

    <h1 class="h3 mb-3"><?= esc($title) ?></h1>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <?php
                $isStorePic = false;
                $isAdmin = false;
                $isSuperAdmin = false;
                $isClient = false;
                $currentGroup = null;
                if (session()->has('user_id')) {
                    $userModel = new \App\Models\UserModel();
                    $currentUserId = session('user_id');
                    $currentUserRoles = $userModel->getRoles($currentUserId);
                    foreach ($currentUserRoles as $role) {
                        $roleName = strtolower(trim($role['name']));
                        if ($roleName === 'store pic') $isStorePic = true;
                        if ($roleName === 'admin') $isAdmin = true;
                        if ($roleName === 'super admin') $isSuperAdmin = true;
                        if ($roleName === 'client') $isClient = true;
                    }
                    if ($isStorePic) {
                        $currentUser = $userModel->find($currentUserId);
                        $currentGroup = $currentUser['kode_group'] ?? null;
                    }
                }
                ?>

                <?php if (!$isClient): ?>
                <div class="col-md-6 mb-2" id="groupFilterContainer" <?= $isStorePic ? 'style="display:none;"' : '' ?> >
                    <label for="filterGroup" class="form-label">Filter Group Store</label>
                    <select id="filterGroup" class="form-select">
                        <option value="">Semua Group</option>
                        <?php if (isset($group_customer)): ?>
                            <?php foreach ($group_customer as $group): ?>
                                <option value="<?= $group->kode_group ?>" <?= ($isStorePic && $currentGroup == $group->kode_group) ? 'selected' : '' ?>><?= $group->nama_group ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <input type="hidden" id="storePicGroup" value="<?= $isStorePic ? $currentGroup : '' ?>">
                <div class="col-md-6 mb-2">
                    <label for="filterStore" class="form-label">Filter Store</label>
                    <select id="filterStore" class="form-select">
                        <option value="">Semua Store</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-md-4 mb-2">
                    <label for="filter-status" class="form-label">Filter Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="0">Proses</option>
                        <option value="1">Sudah</option>
                    </select>
                </div>

                <div class="col-md-4 mb-2">
                    <label for="filter-date-from" class="form-label">Tanggal Dari</label>
                    <input type="text" id="filter-date-from" class="form-control" placeholder="dd-mm-yyyy" autocomplete="off">
                </div>
                <div class="col-md-4 mb-2">
                    <label for="filter-date-to" class="form-label">Tanggal Sampai</label>
                    <input type="text" id="filter-date-to" class="form-control" placeholder="dd-mm-yyyy" autocomplete="off">
                </div>
                
            </div>

            <div class="row mb-3">
                <div class="col-md-12 mb-2">
                    <button id="btn-export-csv" class="btn btn-success">Export CSV</button>
                </div>
            </div>

            <table id="transaksi-table" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No PO</th>
                        <th>Kode Customer</th>
                        <th>Nama Klien</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi datepicker
            $('#filter-date-from, #filter-date-to').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true
            });


            var isStorePic = $('#storePicGroup').val() !== '';
            var isClient = <?= json_encode($isClient) ?>;
            if (isStorePic) {
                $('#filterGroup').val($('#storePicGroup').val());
                $('#groupFilterContainer').hide();
            }

            var table = $('#transaksi-table').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    searchPlaceholder: 'Search No PO',
                    search: ''
                },
                ajax: {
                    url: '<?= base_url('backend/transaksi/datatables') ?>',
                    type: 'GET',
                    error: function (jqXHR, textStatus, errorThrown) {
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
                    },
                    data: function(d) {
                        if (!isClient) {
                            if (isStorePic) {
                                d.filter_group = $('#storePicGroup').val();
                            } else {
                                d.filter_group = $('#filterGroup').val();
                            }
                            d.filter_store = $('#filterStore').val();
                        }
                        d.filter_status = $('#filter-status').val();
                        d.filter_date_from = $('#filter-date-from').val();
                        d.filter_date_to = $('#filter-date-to').val();
                    }
                },
                columns: [
                    { data: 0, orderable: false }, // No
                    { data: 1 }, // No PO
                    { data: 2 }, // Kode Customer
                    { data: 3 }, // Nama Klien
                    { 
                        data: 4, 
                        type: 'date',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'type') {
                                if (!data) return '';
                                var d = new Date(data.replace(' ', 'T'));
                                if (isNaN(d)) return data;
                                let pad = n => n.toString().padStart(2, '0');
                                return pad(d.getDate())+'-'+pad(d.getMonth()+1)+'-'+d.getFullYear()+' | '+pad(d.getHours())+':'+pad(d.getMinutes());
                            }
                            return data;
                        }
                    },
                    { data: 5 },
                    { data: 6, orderable: false, searchable: false }
                ],
                order: [[4, 'desc']],
            });

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
                table.ajax.reload();
            });
            $('#filterStore, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
                table.ajax.reload();
            });

            // Tombol export CSV custom
            $('#btn-export-csv').on('click', function() {
                var params = [];
                if ($('#filter-customer').val()) params.push('filter_customer=' + encodeURIComponent($('#filter-customer').val()));
                if ($('#filter-status').val()) params.push('filter_status=' + encodeURIComponent($('#filter-status').val()));
                if ($('#filter-date-from').val()) params.push('filter_date_from=' + encodeURIComponent($('#filter-date-from').val()));
                if ($('#filter-date-to').val()) params.push('filter_date_to=' + encodeURIComponent($('#filter-date-to').val()));
                var searchVal = $('.dataTables_filter input').val();
                if (searchVal) params.push('search=' + encodeURIComponent(searchVal));
                var url = '<?= base_url('backend/transaksi/export_csv') ?>';
                if (params.length > 0) url += '?' + params.join('&');
                window.open(url, '_blank');
            });
        });
    </script>
<?= $this->endSection() ?>