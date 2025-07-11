<?php
helper(['model_helper', 'lensa_helper']);
?>
<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-3">Create Transaksi</h1>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= esc($error) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">NOTA PESAN</label>
                    <input type="text" class="form-control" name="data_lensa[no_po]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">TANGGAL NOTA</label>
                    <input type="date" class="form-control" name="data_lensa[tgl_nota]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">TANGGAL SELESAI TOL</label>
                    <input type="date" class="form-control" name="data_lensa[tgl_selesai]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">RO</label>
                    <input type="text" class="form-control" name="data_lensa[ro]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PELANGGAN</label>
                    <input type="text" class="form-control" name="data_lensa[nama_customer]" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">INSTRUKSI KHUSUS</label>
                    <input type="text" class="form-control" name="data_lensa[spesial_instruksi]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">TANGGAL LAHIR</label>
                    <input type="date" class="form-control" name="data_lensa[tgl_lahir]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">USIA</label>
                    <input type="number" class="form-control" name="data_lensa[usia]">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Hanya Jasa?</label>
                <select class="form-select" id="hanya_jasa" name="data_lensa[hanya_jasa]" onchange="toggleLensaFields()">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">MODEL FRAME</label>
                <div id="model-frame-list" class="d-flex flex-wrap gap-2">
                    <?php
                    $modelList = get_model_frame_backend();
                    if (!empty($modelList)) {
                        foreach ($modelList as $idx => $model) {
                            $id = 'model_'."$idx";
                            $val = $model->nomor ?? $idx;
                            ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="data_lensa[model_frame_radio]" id="<?= $id ?>" value="<?= esc($val) ?>" onclick="document.getElementById('model_frame_value').value=this.value;">
                                <label class="form-check-label me-3" for="<?= $id ?>" style="cursor:pointer;">
                                    <?php if ($model->gambar_m): ?>
                                        <img src="<?= $model->gambar_m ?>" alt="<?= esc($model->nama_model) ?>" style="width:60px;height:40px;display:block;">
                                    <?php else: ?>
                                        <span class="text-danger">No Image</span>
                                    <?php endif; ?>
                                    <div style="text-align:center"><?= esc($model->nama_model ?? $val) ?></div>
                                </label>
                            </div>
                        <?php }
                    } else {
                        echo '<span class="text-danger">Data model frame kosong/tidak ditemukan.</span>';
                    }
                    ?>
                </div>
                <input type="hidden" name="data_lensa[model]" id="model_frame_value">
            </div>

            
            
            <br><hr>
            <div class="mb-3">
                <label class="form-label">Lensa</label>
                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="table table-bordered align-middle mb-0" style="min-width:1200px;" id="lensa-table">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Kode Lensa</th>
                                <th>Nama Lensa</th>
                                <th>Sph</th>
                                <th>Cyl</th>
                                <th>Axs</th>
                                <th>Add</th>
                                <th>PDF</th>
                                <th>PDN</th>
                                <th>Prism1</th>
                                <th>Base1</th>
                                <th>Prism2</th>
                                <th>Base2</th>
                                <th>Base</th>
                                <th>Curve</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>R</td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap" style="width:220px;">
                                        <button type="button" class="btn btn-outline-primary" style="width:48px;padding:2px 6px;" onclick="openLensaModal('r_lensa','r_nama_lensa')">Pilih</button>
                                        <input type="text" class="form-control" style="width:60px;padding-right:2px;padding-left:2px;" name="data_lensa[r_lensa]" id="r_lensa" readonly onchange="updateNamaLensa('r_lensa','r_nama_lensa')" placeholder="Kode Lensa">
                                        <button type="button" class="btn btn-outline-danger" style="width:50px;padding:2px 6px;" onclick="clearLensa('r_lensa','r_nama_lensa')">&times; Batal</button>
                                    </div>
                                </td>
                                <td><input type="text" class="form-control" style="min-width:200px;max-width:350px;" name="data_lensa[r_nama_lensa]" id="r_nama_lensa" readonly placeholder="Nama Lensa"></td>
                                <td><input type="text" class="form-control" id="r_spheris" style="min-width:90px;max-width:140px;" name="data_lensa[r_spheris]"></td>
                                <td><input type="text" class="form-control" id="r_cylinder" style="min-width:90px;max-width:140px;" name="data_lensa[r_cylinder]"></td>
                                <td><input type="text" class="form-control" id="r_axis" style="min-width:90px;max-width:140px;" name="data_lensa[r_axis]"></td>
                                <td><input type="text" class="form-control" id="r_additional" style="min-width:90px;max-width:140px;" name="data_lensa[r_additional]"></td>
                                <td><input type="text" class="form-control" id="r_pdf" style="min-width:90px;max-width:140px;" name="data_lensa[r_pdf]"></td>
                                <td><input type="text" class="form-control" id="r_pdn" style="min-width:90px;max-width:140px;" name="data_lensa[r_pdn]"></td>
                                <td><input type="text" class="form-control" id="r_prisma" style="min-width:90px;max-width:140px;" name="data_lensa[r_prisma]"></td>
                                <td><input type="text" class="form-control" id="r_base" style="min-width:90px;max-width:140px;" name="data_lensa[r_base]"></td>
                                <td><input type="text" class="form-control" id="r_prisma2" style="min-width:90px;max-width:140px;" name="data_lensa[r_prisma2]"></td>
                                <td><input type="text" class="form-control" id="r_base2" style="min-width:90px;max-width:140px;" name="data_lensa[r_base2]"></td>
                                <td><input type="text" class="form-control" id="r_base_curve" style="min-width:90px;max-width:140px;" name="data_lensa[r_base_curve]"></td>
                                <td><input type="text" class="form-control" id="r_edge_thickness" style="min-width:90px;max-width:140px;" name="data_lensa[r_edge_thickness]"></td>
                            </tr>
                            <tr>
                                <td>L</td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap" style="width:220px;">                                        
                                        <button type="button" class="btn btn-outline-primary" style="width:48px;padding:2px 6px;" onclick="openLensaModal('l_lensa','l_nama_lensa')">Pilih</button>
                                        <input type="text" class="form-control" style="width:60px;padding-right:2px;padding-left:2px;" name="data_lensa[l_lensa]" id="l_lensa" readonly onchange="updateNamaLensa('l_lensa','l_nama_lensa')" placeholder="Kode Lensa">
                                        <button type="button" class="btn btn-outline-danger" style="width:50px;padding:2px 6px;" onclick="clearLensa('l_lensa','l_nama_lensa')">&times; Batal</button>
                                    </div>
                                </td>
                                <td><input type="text" class="form-control" style="min-width:200px;max-width:350px;" name="data_lensa[l_nama_lensa]" id="l_nama_lensa" readonly placeholder="Nama Lensa"></td>
                                <td><input type="text" class="form-control" id="l_spheris" style="min-width:90px;max-width:140px;" name="data_lensa[l_spheris]"></td>
                                <td><input type="text" class="form-control" id="l_cylinder" style="min-width:90px;max-width:140px;" name="data_lensa[l_cylinder]"></td>
                                <td><input type="text" class="form-control" id="l_axis" style="min-width:90px;max-width:140px;" name="data_lensa[l_axis]"></td>
                                <td><input type="text" class="form-control" id="l_additional" style="min-width:90px;max-width:140px;" name="data_lensa[l_additional]"></td>
                                <td><input type="text" class="form-control" id="l_pdf" style="min-width:90px;max-width:140px;" name="data_lensa[l_pdf]"></td>
                                <td><input type="text" class="form-control" id="l_pdn" style="min-width:90px;max-width:140px;" name="data_lensa[l_pdn]"></td>
                                <td><input type="text" class="form-control" id="l_prisma" style="min-width:90px;max-width:140px;" name="data_lensa[l_prisma]"></td>
                                <td><input type="text" class="form-control" id="l_base" style="min-width:90px;max-width:140px;" name="data_lensa[l_base]"></td>
                                <td><input type="text" class="form-control" id="l_prisma2" style="min-width:90px;max-width:140px;" name="data_lensa[l_prisma2]"></td>
                                <td><input type="text" class="form-control" id="l_base2" style="min-width:90px;max-width:140px;" name="data_lensa[l_base2]"></td>
                                <td><input type="text" class="form-control" id="l_base_curve" style="min-width:90px;max-width:140px;" name="data_lensa[l_base_curve]"></td>
                                <td><input type="text" class="form-control" id="l_edge_thickness" style="min-width:90px;max-width:140px;" name="data_lensa[l_edge_thickness]"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Total</th>
                                <th id="total_pdf">0</th>
                                <th id="total_pdn">0</th>
                                <th colspan="6"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Frame - Nama</label>
                    <input type="text" class="form-control" name="data_lensa[frame_nama]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kode</label>
                    <input type="text" class="form-control" name="data_lensa[frame_kode]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kondisi</label>
                    <input type="text" class="form-control" name="data_lensa[frame_kondisi]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nomor Case</label>
                    <input type="text" class="form-control" name="data_lensa[frame_nomor_case]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="data_lensa[frame_keterangan]">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[vert]" placeholder="VERT"></div>
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[ed]" placeholder="ED"></div>
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[ls]" placeholder="LS"></div>
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[bs]" placeholder="BS"></div>
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[sh]" placeholder="SH"></div>
                <div class="col-md-2"><input type="text" class="form-control" name="data_lensa[mbs]" placeholder="MBS"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Jenis Frame</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="FULL METAL">
                        <label class="form-check-label">FULL METAL</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="FULL PLASTIC">
                        <label class="form-check-label">FULL PLASTIC</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="NYLOR">
                        <label class="form-check-label">NYLOR</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="NYLOR METAL">
                        <label class="form-check-label">NYLOR METAL</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="BOR">
                        <label class="form-check-label">BOR</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[jenis_frame]" value="BOR BEVEL">
                        <label class="form-check-label">BOR BEVEL</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status Frame</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[status_frame]" value="COMPLETE">
                        <label class="form-check-label">COMPLETE</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[status_frame]" value="ENCLOSE">
                        <label class="form-check-label">ENCLOSE</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_lensa[status_frame]" value="TO COME">
                        <label class="form-check-label">TO COME</label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">WA</label>
                    <select class="form-select" name="data_lensa[wa]">
                        <option value="">-</option>
                        <option value="Wrap Angle">Wrap Angle</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">PT</label>
                    <select class="form-select" name="data_lensa[pt]">
                        <option value="">-</option>
                        <option value="Pantoscopic Tilt">Pantoscopic Tilt</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">BVD</label>
                    <select class="form-select" name="data_lensa[bvd]">
                        <option value="">-</option>
                        <option value="Back Vertex Distance">Back Vertex Distance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">FFV</label>
                    <select class="form-select" name="data_lensa[ffv]">
                        <option value="">-</option>
                        <option value="Frame Fit Value">Frame Fit Value</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">NWD</label>
                    <select class="form-select" name="data_lensa[nwd]">
                        <option value="">-</option>
                        <option value="Near Working Distance">Near Working Distance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">MID</label>
                    <select class="form-select" name="data_lensa[mid]">
                        <option value="">-</option>
                        <option value="Maximum Intermediate Distance">Maximum Intermediate Distance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">PE</label>
                    <select class="form-select" name="data_lensa[pe]">
                        <option value="">-</option>
                        <option value="Personal Engraving">Personal Engraving</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">V CODE</label>
                    <input type="text" class="form-control" name="data_lensa[v_code]">
                </div>
            </div>

            

            <br><hr>
            <div class="mb-3">
                <label class="form-label">Jasa (opsional, bisa lebih dari satu)</label>
                <div class="table-responsive" style="max-width:700px;">
                    <table class="table table-bordered align-middle mb-0" id="jasa-input-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:120px;">Kode Jasa</th>
                                <th>Nama Jasa</th>
                                <th style="width:80px;">Qty</th>
                                <th style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="jasa-list">
                            <tr>
                                <td><input type="text" class="form-control" name="data_jasa[0][jasa_id]" id="jasa_id_0" placeholder="Kode Jasa" readonly></td>
                                <td><input type="text" class="form-control" name="data_jasa[0][jasa_nama]" id="jasa_nama_0" placeholder="Nama Jasa" readonly></td>
                                <td><input type="number" class="form-control" name="data_jasa[0][jasa_qty]" placeholder="Qty Jasa" style="max-width:70px;"></td>
                                <td>
                                    <div class="d-flex flex-row gap-1 justify-content-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openJasaModal(0)">Pilih</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addJasa()">Tambah Jasa</button>
            </div>

            <!-- Modal Pilih Jasa -->
            <div class="modal fade" id="jasaModal" tabindex="-1" aria-labelledby="jasaModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="jasaModalLabel">Pilih Jasa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="text" class="form-control mb-2" id="searchJasa" placeholder="Cari kode/nama jasa..." onkeyup="filterJasaTable()">
                    <div class="table-responsive" style="max-height:400px;overflow:auto;">
                      <table class="table table-bordered table-hover" id="jasaTable">
                        <thead><tr><th>Kode Jasa</th><th>Nama Jasa</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach(get_jasa_master_backend() as $jasa): ?>
                          <tr>
                            <td><?= esc($jasa->kode_jasa) ?></td>
                            <td><?= esc($jasa->nama_jasa) ?></td>
                            <td><button type="button" class="btn btn-sm btn-success" onclick="selectJasa('<?= esc($jasa->kode_jasa) ?>','<?= esc(addslashes($jasa->nama_jasa)) ?>')">Pilih</button></td>
                          </tr>
                        <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <br><hr>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="manual">Manual</option>
                    <option value="midtrans">Midtrans</option>
                    <option value="xendit">Xendit</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan & Proses Pembayaran</button>
        </form>

    </div>
</div>

<!-- Modal Pilih Lensa -->
<div class="modal fade" id="lensaModal" tabindex="-1" aria-labelledby="lensaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="lensaModalLabel">Pilih Lensa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-2" id="searchLensa" placeholder="Cari kode/nama lensa..." onkeyup="filterLensaTable()">
                <div class="table-responsive" style="max-height:400px;overflow:auto;">
                    <table class="table table-bordered table-hover" id="lensaTable">
                        <thead><tr><th>Kode Lensa</th><th>Nama Lensa</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach(get_lensa_master_backend() as $lensa): ?>
                                <tr>
                                    <td><?= esc($lensa->kode_lensa) ?></td>
                                    <td><?= esc($lensa->nama_lensa) ?></td>
                                    <td><button type="button" class="btn btn-sm btn-success" onclick="selectLensa('<?= esc($lensa->kode_lensa) ?>','<?= esc(addslashes($lensa->nama_lensa)) ?>')">Pilih</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>

            
<!-- Modal Notifikasi Qty Jasa -->
<div class="modal fade" id="qtyJasaNotifModal" tabindex="-1" aria-labelledby="qtyJasaNotifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="qtyJasaNotifModalLabel">Peringatan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="qtyJasaNotifMsg">
        Qty jasa hanya boleh 1 atau 2!
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
    </div>
    </div>
</div>


    <script>
        let jasaIdx = 1;
        function addJasa() {
            const jasaList = document.getElementById('jasa-list');
            const idx = jasaList.children.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td><input type="text" class="form-control" name="data_jasa[${idx}][jasa_id]" id="jasa_id_${idx}" placeholder="Kode Jasa" readonly></td>
                <td><input type="text" class="form-control" name="data_jasa[${idx}][jasa_nama]" id="jasa_nama_${idx}" placeholder="Nama Jasa" readonly></td>
                <td><input type="number" class="form-control" name="data_jasa[${idx}][jasa_qty]" placeholder="Qty Jasa" style="max-width:70px;"></td>
                <td><div class='d-flex flex-row gap-1 justify-content-center'><button type="button" class="btn btn-outline-primary btn-sm" onclick="openJasaModal(${idx})">Pilih</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();updateJasaActionButtons();">Hapus</button></div></td>`;
            jasaList.appendChild(tr);
            updateJasaQtyFields();
            updateJasaActionButtons(); // <-- update tombol setelah tambah baris
        }
        function openJasaModal(idx) {
            jasaTargetIdx = idx;
            var modal = new bootstrap.Modal(document.getElementById('jasaModal'));
            modal.show();
        }
        function selectJasa(kode, nama) {
            document.getElementById('jasa_id_' + jasaTargetIdx).value = kode;
            document.getElementById('jasa_nama_' + jasaTargetIdx).value = nama;
            bootstrap.Modal.getInstance(document.getElementById('jasaModal')).hide();
        }
        function updateNamaLensa(kodeId, namaId) {
            // Optional: bisa diisi jika ingin update nama otomatis saat input kode manual
        }
        function filterJasaTable() {
            var input = document.getElementById('searchJasa');
            var filter = input.value.toUpperCase();
            var table = document.getElementById('jasaTable');
            var tr = table.getElementsByTagName('tr');
            for (var i = 1; i < tr.length; i++) {
                var tdKode = tr[i].getElementsByTagName('td')[0];
                var tdNama = tr[i].getElementsByTagName('td')[1];
                if (tdKode && tdNama) {
                    var kode = tdKode.textContent || tdKode.innerText;
                    var nama = tdNama.textContent || tdNama.innerText;
                    if (kode.toUpperCase().indexOf(filter) > -1 || nama.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        // --- FIX: Modal Lensa ---
        let lensaTargetKodeId = null;
        let lensaTargetNamaId = null;
        function openLensaModal(kodeId, namaId) {
            lensaTargetKodeId = kodeId;
            lensaTargetNamaId = namaId;
            var modal = new bootstrap.Modal(document.getElementById('lensaModal'));
            modal.show();
        }
        function selectLensa(kode, nama) {
            if (lensaTargetKodeId && lensaTargetNamaId) {
                document.getElementById(lensaTargetKodeId).value = kode;
                document.getElementById(lensaTargetNamaId).value = nama;
                // Trigger event agar update qty jasa otomatis
                document.getElementById(lensaTargetKodeId).dispatchEvent(new Event('input'));
            }
            bootstrap.Modal.getInstance(document.getElementById('lensaModal')).hide();
        }
        // --- END FIX ---

        function updateTotalPdfPdn() {
            let pdfR = parseFloat(document.querySelector('[name="data_lensa[r_pdf]"]').value) || 0;
            let pdfL = parseFloat(document.querySelector('[name="data_lensa[l_pdf]"]').value) || 0;
            let pdnR = parseFloat(document.querySelector('[name="data_lensa[r_pdn]"]').value) || 0;
            let pdnL = parseFloat(document.querySelector('[name="data_lensa[l_pdn]"]').value) || 0;
            document.getElementById('total_pdf').innerText = pdfR + pdfL;
            document.getElementById('total_pdn').innerText = pdnR + pdnL;
        }
        document.querySelector('[name="data_lensa[r_pdf]"]').addEventListener('input', updateTotalPdfPdn);
        document.querySelector('[name="data_lensa[l_pdf]"]').addEventListener('input', updateTotalPdfPdn);
        document.querySelector('[name="data_lensa[r_pdn]"]').addEventListener('input', updateTotalPdfPdn);
        document.querySelector('[name="data_lensa[l_pdn]"]').addEventListener('input', updateTotalPdfPdn);
        // Inisialisasi awal
        updateTotalPdfPdn();

        function clearLensa(kodeId, namaId) {
            // Hapus semua input di baris yang sama (R atau L)
            const row = kodeId.startsWith('r_') ? 'r_' : 'l_';
            const fields = [
                row + 'lensa', row + 'nama_lensa', row + 'spheris', row + 'cylinder', row + 'axis', row + 'additional',
                row + 'pdf', row + 'pdn', row + 'prisma', row + 'base', row + 'prisma2', row + 'base2', row + 'base_curve', row + 'edge_thickness'
            ];
            fields.forEach(function(fid) {
                var el = document.getElementById(fid);
                if (el) el.value = '';
            });
            updateTotalPdfPdn();
            updateJasaQtyFields();
            updateJasaActionButtons(); // <-- update tombol setelah clear lensa
        }

        function toggleLensaFields() {
            var hanyaJasa = document.getElementById('hanya_jasa').value;
            var lensaFields = document.querySelectorAll('#lensa-table input, #lensa-table button');
            lensaFields.forEach(function(el) {
                if (hanyaJasa === '1') {
                    el.setAttribute('disabled', 'disabled');
                } else {
                    el.removeAttribute('disabled');
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleLensaFields();
        });
        function getLensaCount() {
            let count = 0;
            if (document.getElementById('r_lensa') && document.getElementById('r_lensa').value) count++;
            if (document.getElementById('l_lensa') && document.getElementById('l_lensa').value) count++;
            return count;
        }
        // --- Perbaiki logic qty jasa agar disabled jika belum pilih lensa (hanya jasa=0) ---
        function updateJasaQtyFields() {
            var hanyaJasa = document.getElementById('hanya_jasa').value;
            var jasaRows = document.querySelectorAll('#jasa-list tr');
            var lensaCount = getLensaCount();
            jasaRows.forEach(function(row) {
                var qtyInput = row.querySelector('input[name*="[jasa_qty]"]');
                if (!qtyInput) return;
                if (hanyaJasa === '1') {
                    qtyInput.removeAttribute('readonly');
                    qtyInput.removeAttribute('disabled');
                    qtyInput.value = '';
                } else {
                    qtyInput.setAttribute('readonly', 'readonly');
                    if (lensaCount === 0) {
                        qtyInput.value = '';
                        qtyInput.setAttribute('disabled', 'disabled');
                    } else {
                        qtyInput.value = lensaCount;
                        qtyInput.removeAttribute('disabled');
                    }
                }
            });
        }
        // --- END PERBAIKI ---
        document.getElementById('hanya_jasa').addEventListener('change', updateJasaQtyFields);
        document.getElementById('r_lensa').addEventListener('input', updateJasaQtyFields);
        document.getElementById('l_lensa').addEventListener('input', updateJasaQtyFields);
        function showQtyJasaNotif(msg) {
            document.getElementById('qtyJasaNotifMsg').innerText = msg || 'Qty jasa hanya boleh 1 atau 2!';
            var modal = new bootstrap.Modal(document.getElementById('qtyJasaNotifModal'));
            modal.show();
        }
        function validateJasaQty(input) {
            var hanyaJasa = document.getElementById('hanya_jasa').value;
            if (hanyaJasa === '1') {
                var val = parseInt(input.value, 10);
                if (val < 1 || val > 2) {
                    showQtyJasaNotif('Qty jasa hanya boleh 1 atau 2!');
                    input.value = '';
                }
            }
        }
        document.addEventListener('input', function(e) {
            if (e.target && e.target.name && e.target.name.includes('[jasa_qty]')) {
                validateJasaQty(e.target);
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            updateJasaQtyFields();
        });
        function updateJasaActionButtons() {
            var hanyaJasa = document.getElementById('hanya_jasa').value;
            var lensaCount = getLensaCount();
            var jasaRows = document.querySelectorAll('#jasa-list tr');
            // Tombol 'Tambah Jasa'
            var btnTambahJasa = document.querySelector('button[onclick="addJasa()"]');
            if (hanyaJasa === '0' && lensaCount === 0) {
                if (btnTambahJasa) btnTambahJasa.setAttribute('disabled', 'disabled');
            } else {
                if (btnTambahJasa) btnTambahJasa.removeAttribute('disabled');
            }
            // Tombol 'Pilih Jasa' di setiap baris
            jasaRows.forEach(function(row) {
                var btnPilih = row.querySelector('button.btn-outline-primary');
                if (!btnPilih) return;
                if (hanyaJasa === '0' && lensaCount === 0) {
                    btnPilih.setAttribute('disabled', 'disabled');
                } else {
                    btnPilih.removeAttribute('disabled');
                }
            });
        }
        // Panggil updateJasaActionButtons setiap perubahan lensa/hanya jasa
        ['change','input'].forEach(function(ev){
            document.getElementById('hanya_jasa').addEventListener(ev, function(){updateJasaActionButtons();});
            document.getElementById('r_lensa').addEventListener(ev, function(){updateJasaActionButtons();});
            document.getElementById('l_lensa').addEventListener(ev, function(){updateJasaActionButtons();});
        });
        document.addEventListener('DOMContentLoaded', function() { updateJasaActionButtons(); });
    </script>
<?= $this->endSection() ?>