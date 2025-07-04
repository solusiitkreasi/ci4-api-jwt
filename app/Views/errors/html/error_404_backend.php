<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<div class="wrap" style="max-width:600px;margin:5rem auto 2rem auto;">
    <h1 style="font-size:3rem;font-weight:lighter;color:#d9534f;">404</h1>
    <p style="font-size:1.2rem;">
        <?php if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') : ?>
            <?= nl2br(esc($message ?? 'Halaman tidak ditemukan')) ?>
        <?php else : ?>
            Halaman yang Anda cari tidak ditemukan atau Anda tidak memiliki akses.
        <?php endif; ?>
    </p>
    <a href="<?= base_url('backend/dashboard') ?>" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
</div>
<?= $this->endSection() ?>
