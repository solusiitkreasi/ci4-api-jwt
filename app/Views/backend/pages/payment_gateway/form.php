<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-3"><?= $gateway ? 'Edit' : 'Tambah' ?> Payment Gateway</h1>
<form method="post" action="<?= $gateway ? base_url('backend/payment-gateway/update/'.$gateway['id']) : base_url('backend/payment-gateway/store') ?>">
    <div class="mb-3">
        <label class="form-label">Provider</label>
        <select name="provider" class="form-select" required <?= $gateway ? 'readonly disabled' : '' ?>>
            <option value="midtrans" <?= $gateway && $gateway['provider']==='midtrans'?'selected':'' ?>>Midtrans</option>
            <option value="xendit" <?= $gateway && $gateway['provider']==='xendit'?'selected':'' ?>>Xendit</option>
            <option value="hitpay" <?= $gateway && $gateway['provider']==='hitpay'?'selected':'' ?>>HitPay</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Mode</label>
        <select name="mode" class="form-select" required <?= $gateway ? 'readonly disabled' : '' ?>>
            <option value="production" <?= $gateway && $gateway['mode']==='production'?'selected':'' ?>>Production</option>
            <option value="sandbox" <?= $gateway && $gateway['mode']==='sandbox'?'selected':'' ?>>Sandbox</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">API Key</label>
        <input type="text" name="api_key" class="form-control" value="<?= $gateway['api_key'] ?? '' ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">API URL</label>
        <input type="text" name="api_url" class="form-control" value="<?= $gateway['api_url'] ?? '' ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Client Key</label>
        <input type="text" name="client_key" class="form-control" value="<?= $gateway['client_key'] ?? '' ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Status Aktif</label>
        <select name="is_active" class="form-select">
            <option value="1" <?= !$gateway || !isset($gateway['is_active']) || $gateway['is_active'] ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= $gateway && isset($gateway['is_active']) && !$gateway['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('backend/payment-gateway') ?>" class="btn btn-secondary">Batal</a>
</form>
<?= $this->endSection() ?>
