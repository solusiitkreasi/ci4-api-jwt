<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-3">Transaksi Berhasil Dibuat</h1>
<div class="card">
    <div class="card-body">
        <h5>Data Transaksi</h5>
        <pre><?= print_r($transaksi, true) ?></pre>
        <h5>Pembayaran</h5>
        <?php if ($payment['provider'] === 'midtrans'): ?>
            <button id="pay-button" class="btn btn-success">Bayar via Midtrans</button>
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= getenv('MIDTRANS_CLIENT_KEY') ?>"></script>
            <script>
                document.getElementById('pay-button').onclick = function() {
                    snap.pay('<?= $payment['snap_token'] ?>');
                };
            </script>
        <?php elseif ($payment['provider'] === 'xendit' && $payment['invoice_url']): ?>
            <a href="<?= esc($payment['invoice_url']) ?>" class="btn btn-warning" target="_blank">Bayar via Xendit</a>
        <?php elseif ($payment['provider'] === 'hitpay' && !empty($payment['payment_url'])): ?>
            <a href="<?= esc($payment['payment_url']) ?>" class="btn btn-primary" target="_blank">Bayar via HitPay</a>
        <?php else: ?>
            <div class="alert alert-info">Pembayaran manual atau tidak ada payment gateway.</div>
        <?php endif; ?>
    </div>
</div>
<a href="<?= base_url('backend/transaksi') ?>" class="btn btn-secondary mt-3">Kembali ke Daftar Transaksi</a>
<?= $this->endSection() ?>
