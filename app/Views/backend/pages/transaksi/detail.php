<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3"><b>Data Transaksi</b></h5>
        <table class="table table-bordered table-sm">
            <tbody>
            <?php foreach ($transaksi as $key => $val):
                if ($key === 'id' || $key === 'pic_input' || strpos($key, 'r_') === 0 || strpos($key, 'l_') === 0) continue; ?>
                <tr>
                    <th><?= esc(ucwords(str_replace('_',' ', $key))) ?></th>
                    <td><?= esc($val) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (empty($transaksi['hanya_jasa']) || $transaksi['hanya_jasa'] == '0' || $transaksi['hanya_jasa'] == 0): ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3"><b>Data Lensa R</b></h5>
        <table class="table table-bordered table-sm">
            <tbody>
            <?php foreach ($transaksi as $key => $val):
                if (strpos($key, 'r_') === 0): ?>
                <tr>
                    <th><?= esc(ucwords(str_replace(['r_','_'],['',' '], $key))) ?></th>
                    <td><?= esc($val) ?></td>
                </tr>
            <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3"><b>Data Lensa L</b></h5>
        <table class="table table-bordered table-sm">
            <tbody>
            <?php foreach ($transaksi as $key => $val):
                if (strpos($key, 'l_') === 0): ?>
                <tr>
                    <th><?= esc(ucwords(str_replace(['l_','_'],['',' '], $key))) ?></th>
                    <td><?= esc($val) ?></td>
                </tr>
            <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3"><b>Detail Jasa</b></h5>
        <?php if (!empty($jasa)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama Jasa</th>
                    <th>Qty</th>
                    <th>Operator</th>
                    <th>Waktu Input</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jasa as $row): ?>
                <tr>
                    <td><?= esc($row['nama_jasa'] ?? '-') ?></td>
                    <td><?= esc($row['qty'] ?? '-') ?></td>
                    <td><?= esc($row['operator'] ?? '-') ?></td>
                    <td><?= esc($row['wkt_input'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-muted">Tidak ada data jasa.</div>
        <?php endif; ?>
    </div>
</div>

<a href="<?= base_url('backend/transaksi') ?>" class="btn btn-secondary">Kembali</a>
<?= $this->endSection() ?>
