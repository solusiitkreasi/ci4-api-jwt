<?php $this->extend('backend/layouts/main'); ?>
<?php $this->section('content'); ?>
<h1>Log Activity</h1>
<?php if (isset($logs['data']) && count($logs['data'])): ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Resource</th>
            <th>IP Address</th>
            <th>User Agent</th>
            <th>Waktu</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs['data'] as $log): ?>
        <tr>
            <td><?= esc($log['user_name'] ?? '-') ?> (<?= esc($log['user_email'] ?? '-') ?>)</td>
            <td><?= esc($log['action']) ?></td>
            <td><?= esc($log['resource']) ?></td>
            <td><?= esc($log['ip_address']) ?></td>
            <td><?= esc($log['user_agent']) ?></td>
            <td><?= esc($log['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>Tidak ada data log aktivitas.</p>
<?php endif; ?>
<?php $this->endSection(); ?>
