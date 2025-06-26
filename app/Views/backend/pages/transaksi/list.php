<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

    <h1 class="h3 mb-3"><?= esc($title) ?></h1>

    <div class="card">

        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Klien</th>
                        <th>Tanggal</th>
                        <!-- <th>Total</th> -->
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transaksi as $tx): ?>
                        <tr>
                            <td><?= esc($tx['no_po']) ?></td>
                            <td><?= esc($tx['customer_name']) ?></td>
                            <td><?= esc(date('d-m-Y | h:i',strtotime($tx['wkt_input']))) ?></td>
                            <!-- <td>Rp <?= number_format(10000, 0) ?></td> -->
                            <td><span class="badge bg-success"><?= esc($tx['is_proses_tol']) ?></span></td>
                            <td>
                                <a href="/backend/transaksi/detail/<?= esc($tx['id']) ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>

        <!-- Pagination -->
        <?php if ( ! empty($pager)) :
            //echo $pager->simpleLinks('group1', 'bs_simple');
            echo $pager->links('transaksi', 'bs_full');
        endif ?>

        <!-- Bootstrap 4.5.2 code to show page 1 of 4 total pages using a button. -->
        <!-- <div class="btn-group pagination justify-content-center mb-4" role="group" aria-label="pager counts">
            &nbsp;&nbsp;&nbsp;
            <button type="button" class="btn btn-light"><?= 'Page '.$currentPage.' of '.$totalPages; ?></button>
        </div> -->
    </div>

<?= $this->endSection() ?>