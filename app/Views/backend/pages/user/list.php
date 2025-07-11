<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>

<h2>Manajemen User</h2>
<div class="card">
    <div class="card-body">
        <div class="container mt-4">
            <a href="<?= base_url('backend/user/create') ?>" class="btn btn-primary mb-3">Tambah User</a>
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
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Mengubah cara DataTables melaporkan error, dari alert() menjadi throw error
    $.fn.dataTable.ext.errMode = 'throw';

    $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('backend/user/datatables') ?>",
            "type": "GET",
            "error": function (jqXHR, textStatus, errorThrown) {
                // Tangkap error AJAX di sini
                let errorMessage = 'Terjadi kesalahan saat memuat data.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    // Coba parse sebagai HTML jika response bukan JSON
                    let errorText = $(jqXHR.responseText).find('h1').text();
                    if(errorText) errorMessage = errorText;
                }

                // Tampilkan pesan di modal
                $('#errorModalBody').html(errorMessage);
                $('#errorModal').modal('show');

                // Kosongkan tabel untuk menunjukkan ada masalah
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
        "order": [[ 1, 'asc' ]] // Default sorting by name
    });
});
</script>
<?= $this->endSection() ?>
