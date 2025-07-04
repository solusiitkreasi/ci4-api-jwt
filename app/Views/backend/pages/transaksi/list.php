<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

    <h1 class="h3 mb-3"><?= esc($title) ?></h1>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label for="filter-customer" class="form-label">Filter Customer</label>
                    <select id="filter-customer" class="form-select">
                        <option value="">Semua Customer</option>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= esc($c['kode_customer']) ?>">
                                    <?= esc($c['kode_customer']) ?> - <?= esc($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="filter-status" class="form-label">Filter Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="0">Proses</option>
                        <option value="1">Sudah</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="filter-date-from" class="form-label">Tanggal Dari</label>
                    <input type="text" id="filter-date-from" class="form-control" placeholder="dd-mm-yyyy" autocomplete="off">
                </div>
                <div class="col-md-3 mb-2">
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
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi datepicker
            $('#filter-date-from, #filter-date-to').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true
            });

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
                    data: function(d) {
                        d.filter_customer = $('#filter-customer').val();
                        d.filter_status = $('#filter-status').val();
                        d.filter_date_from = $('#filter-date-from').val();
                        d.filter_date_to = $('#filter-date-to').val();
                    }
                },
                columns: [
                    { data: 0 }, // No
                    { data: 1 }, // No PO
                    { data: 2 }, // Kode Customer
                    { data: 3 }, // Nama Klien
                    { data: 4, render: function(data, type, row) {
                        // Format tanggal di JS agar tetap bisa sorting
                        if (!data) return '';
                        var d = new Date(data.replace(' ', 'T'));
                        if (isNaN(d)) return data;
                        let pad = n => n.toString().padStart(2, '0');
                        return pad(d.getDate())+'-'+pad(d.getMonth()+1)+'-'+d.getFullYear()+' | '+pad(d.getHours())+':'+pad(d.getMinutes());
                    } }, // Tanggal tampil & sort
                    { data: 5 }, // Status
                    { data: 6, orderable: false, searchable: false } // Aksi
                ],
                order: [[4, 'desc']], // Urutkan berdasarkan tanggal mentah
            });

            $('#filter-customer, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
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