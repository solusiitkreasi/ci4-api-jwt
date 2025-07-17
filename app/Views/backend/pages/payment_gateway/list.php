<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-3">Payment Gateway Management</h1>
<a href="<?= base_url('backend/payment/create') ?>" class="btn btn-primary mb-3">Tambah Gateway</a>
<?php if (session('success')): ?><div class="alert alert-success"><?= session('success') ?></div><?php endif; ?>
<?php if (session('error')): ?><div class="alert alert-danger"><?= session('error') ?></div><?php endif; ?>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Provider</th>
            <th>Mode</th>
            <th>Status</th>
            <th>API Key</th>
            <th>API URL</th>
            <th>Client Key</th>
            <th>Updated</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gateways as $g): ?>
        <tr>
            <td><?= esc($g['id']) ?></td>
            <td><?= esc($g['provider']) ?></td>
            <td><?= esc($g['mode']) ?></td>
            <td><?= $g['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
            <td><?= esc($g['api_key']) ?></td>
            <td><?= esc($g['api_url']) ?></td>
            <td><?= esc($g['client_key']) ?></td>
            <td><?= esc($g['updated_at']) ?></td>
            <td>
                <a href="<?= base_url('backend/payment-gateway/edit/'.$g['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                <form action="<?= base_url('backend/payment-gateway/delete/'.$g['id']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Hapus gateway ini?')">
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <form action="<?= base_url('backend/payment-gateway/toggle/'.$g['id']) ?>" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-sm <?= $g['is_active'] ? 'btn-secondary' : 'btn-success' ?>"><?= $g['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->endSection() ?>
