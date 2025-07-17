<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

        <div class="page-title-container mb-4">
            <div class="row">
                <div class="col-auto mb-3 mb-md-0 me-auto">
                    <div class="w-auto sw-md-30">
                        <a href="<?= base_url('backend/transaksi') ?>" class="muted-link pb-1 d-inline-block breadcrumb-back">
                            <i class="bi bi-chevron-left"></i>
                            <span class="text-small align-middle">Kembali</span>
                        </a>
                        <h1 class="mb-0 pb-0 display-6" id="title">Detail Transaksi</h1>
                    </div>
                </div>
            </div>
        </div>


        <h2 class="small-title">Status & Info</h2>
        <div class="row g-2 mb-4">
            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center lh-1-25">No PO</div>
                        <div class="text-primary"><?= esc($transaksi['no_po'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center lh-1-25">Status</div>
                        <div class="text-primary">
                            <?= ($transaksi['is_proses_tol'] == 1) ? '<span class="badge bg-success">Sudah</span>' : '<span class="badge bg-info">Proses</span>' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center lh-1-25">Tanggal Input</div>
                        <div class="text-primary"><?= esc($transaksi['wkt_input'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center lh-1-25">Nama Klien</div>
                        <div class="text-primary"><?= esc($transaksi['customer_name'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="small-title">Data Transaksi</h2>
        <div class="card mb-4">
            <div class="card-body">
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
        <div class="row">
            <div class="col-md-6">
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
            </div>
            <div class="col-md-6">
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
            </div>
        </div>
        <?php endif; ?>
        
        <h2 class="small-title">Detail Jasa</h2>
        <div class="card mb-4">
            <div class="card-body">
                <?php if (!empty($jasa)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Jasa</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jasa as $row): ?>
                        <tr>
                            <td><?= esc($row['jasa_id'] ?? '') ?> - <?= esc($row['nama_jasa'] ?? '-') ?></td>
                            <td><?= esc($row['qty'] ?? '-') ?></td>
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
