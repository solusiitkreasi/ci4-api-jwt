<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>
    <h1 class="h3 mb-3">Manajemen Role</h1>
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
            <a href="<?= base_url('backend/role/create') ?>" class="btn btn-primary mb-3">Tambah Role</a>
            <table id="role-table" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Role</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Apakah Anda yakin ingin menghapus role ini?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <form id="deleteForm" method="get">
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#role-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('backend/role/datatables') ?>',
                type: 'GET'
            },
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                {
                    data: 3,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editBtn = $(data).filter('a.btn-warning')[0]?.outerHTML || '';
                        var permissionBtn = $(data).filter('a.btn-info')[0]?.outerHTML || '';
                        var deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-id="'+row[0]+'">Hapus</button>';
                        return editBtn + ' ' + deleteBtn + ' ' + permissionBtn;
                    }
                }
            ]
        });

        // Event klik tombol hapus
        $('#role-table').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var url = '<?= base_url('backend/role/delete/') ?>' + id;
            $('#deleteForm').attr('action', url);
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
    </script>
<?= $this->endSection() ?>
