<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-3">Transaksi Berhasil Dibuat</h1>
<div class="card">
    <div class="card-body">
        <h5>Data Transaksi</h5>
        <pre><?= print_r($transaksi, true) ?></pre>
    </div>
</div>
<a href="<?= base_url('backend/transaksi') ?>" class="btn btn-secondary mt-3">Kembali ke Daftar Transaksi</a>

<script>
// Hapus draft transaksi di localStorage setelah transaksi berhasil disimpan
const SESSION_USER_ID = <?= json_encode(session()->get('user_id')) ?>;
const DRAFT_KEY = SESSION_USER_ID ? `transaksi_draft_backend_${SESSION_USER_ID}` : 'transaksi_draft_backend';
localStorage.removeItem(DRAFT_KEY);
</script>

<?= $this->endSection() ?>
