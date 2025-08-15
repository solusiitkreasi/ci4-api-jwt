    
<?php
helper(['model_helper', 'lensa_helper']);
// Inject user_id from controller
$user_id = isset($user_id) ? $user_id : null;
?>

<?= $this->extend('backend/layouts/template') ?>
<?= $this->section('content') ?>

<style>
    /* Fix horizontal scroll kosong */
    body { overflow-x: hidden !important; }
    .table-responsive { max-width: 100vw; }
    #lensa-table { max-width: 100%; }

    /* Custom styling for disabled SPH and Cylinder select */
    .select2-container--disabled .select2-selection {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        opacity: 0.6;
    }
    
    .select2-container--disabled .select2-selection__placeholder {
        color: #6c757d !important;
    }
    
    /* Additional styling for disabled state */
    select[disabled] {
        background-color: #e9ecef;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    /* Loading state untuk cylinder select */
    .select2-container.loading-cylinder .select2-selection {
        background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
        position: relative;
    }
    
    .select2-container.loading-cylinder .select2-selection::after {
        content: "⏳";
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        animation: pulse 1s infinite;
    }
    
    /* Loading state untuk SPH select */
    .select2-container.loading-sph .select2-selection {
        background: linear-gradient(90deg, #e3f2fd 25%, #bbdefb 50%, #e3f2fd 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
        position: relative;
    }
    
    .select2-container.loading-sph .select2-selection::after {
        content: "🔍";
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        animation: pulse 1s infinite;
    }
    
    @keyframes loading-shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>


<h1 class="h3 mb-3">Order Transaksi</h1>


<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?= esc($error) ?>
        <?php if (!empty($errors) && is_array($errors)): ?>
            <ul class="mb-0 mt-2">
            <?php foreach ($errors as $key => $err): ?>
                <?php if ($key === 'no_po_exists') continue; ?>
                <?php
                    $label = $key === 'no_po' ? 'Nomor Nota' : $key;
                ?>
                <?php if (is_array($err)): ?>
                    <?php foreach ($err as $suberr): ?>
                        <li><b><?= esc($label) ?></b>: <?= esc($suberr) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><b><?= esc($label) ?></b>: <?= esc($err) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <script>
    // Clear localStorage draft hanya jika ada error validasi
    window.addEventListener('DOMContentLoaded', function() {
        localStorage.removeItem(SESSION_USER_ID ? `transaksi_draft_backend_${SESSION_USER_ID}` : 'transaksi_draft_backend');
    });
    </script>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form id="formTambah" method="POST" action="<?= base_url('backend/transaksi/create') ?>" > 
            <!-- onsubmit="return handleSimpleSubmit(event)" id="mainForm" -->
            <?= csrf_field() ?>
            <br>
            <label><strong>DATA PEMESANAN</strong></label>
            <hr>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">NOTA PESAN</label>
                    <input type="text" class="form-control" name="data_lensa[no_po]" required value="<?= isset($errors) && isset($_POST['data_lensa']['no_po']) ? esc($_POST['data_lensa']['no_po']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SALESMAN</label>
                    <input type="text" class="form-control"  placeholder="<?= $salesman ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">TANGGAL NOTA</label>
                    <input type="date" class="form-control" name="data_lensa[tgl_nota]" id="tgl_nota_input" readonly>

                </div>
                <div class="col-md-3">
                    <label class="form-label">TANGGAL SELESAI TOL</label>
                    <input type="date" class="form-control" name="data_lensa[tgl_selesai]" id="tgl_selesai_input" readonly>
                </div>              
                
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">PELANGGAN</label>
                    <input type="text" class="form-control" name="data_lensa[nama_customer]" required value="<?= isset($errors) && isset($_POST['data_lensa']['nama_customer']) ? esc($_POST['data_lensa']['nama_customer']) : '' ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">TANGGAL LAHIR</label>
                    <input type="text" class="form-control" name="data_lensa[tgl_lahir]" id="tanggal-lahir" placeholder="dd-mm-yyyy" value="<?= isset($errors) && isset($_POST['data_lensa']['tgl_lahir']) ? esc($_POST['data_lensa']['tgl_lahir']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">USIA</label>
                    <input type="number" class="form-control" name="data_lensa[usia]" id="usia_input" value="<?= isset($errors) && isset($_POST['data_lensa']['usia']) ? esc($_POST['data_lensa']['usia']) : '0' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">KETERANGAN</label>
                    <input type="text" class="form-control" name="data_lensa[keterangan]">
                </div>
                
            </div>

            <div class="alert alert-info mt-3">
                <p>
                    <ul>
                        <li><strong>Tanggal Selesai TOL</strong> : Jika input sebelum jam 15:00 maka selesai besok, Jika lewat maka selesai lusa.</li>
                    </ul>
                </p>
            </div>

            <br><hr>
            <div class="mb-3">
                <label class="label"><strong>HANYA JASA?</strong></label>
                <select class="form-select" id="hanya_jasa" name="data_lensa[hanya_jasa]">
                    <option value="0" <?= (isset($errors) && isset($_POST['data_lensa']['hanya_jasa']) && $_POST['data_lensa']['hanya_jasa'] == '0') ? 'selected' : '' ?>>Tidak</option>
                    <option value="1" <?= (isset($errors) && isset($_POST['data_lensa']['hanya_jasa']) && $_POST['data_lensa']['hanya_jasa'] == '1') ? 'selected' : '' ?>>Ya</option>
                </select>
            </div>
            <div class="alert alert-info mt-3">
                <p>
                    <ul>
                        <li><strong>Hanya Jasa</strong> : <br>
                            Jika pilihan hanya jasa = YA (Yang di isi hanya jasa saja dan jasa wajib di isi), <br>
                            Jika pilihan hanya jasa = Tidak ( Pilihan Data Lensa Harus Wajib Dilengkapi)
                        </li>
                        <li class="text-warning mt-2"><strong><i class="fas fa-exclamation-triangle"></i> Peringatan:</strong> <br>
                            Jika merubah pilihan "Hanya Jasa", maka data <strong>Lensa</strong>, <strong>Jasa</strong>, dan <strong>Free Form</strong> akan direset.
                        </li>
                    </ul>
                </p>
            </div>

            <br>
            <div id="lensa-section">
                <label><strong>LENSA</strong></label>
                <hr>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label"><b>Kode Brand</b></label>
                        <select class="form-select" id="kd_brand" name="data_lensa[kd_brand]">
                            <option value="">--Pilih Kode Brand --</option>
                            <option value="CZ">Carl Zeiss</option>
                            <option value="HB">House Brand</option>
                            <option value="SC">Synchroni</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><b>Jenis Lensa</b></label>
                    <select class="form-select" id="jenis_lensa" name="data_lensa[jenis_lensa]">
                        <option value="">-- Pilih Jenis Lensa --</option>
                        <option value="BF">BIFOCAL</option>
                        <option value="OL">OFFICELENS</option>
                        <option value="PG">PROGRESSIVE</option>
                        <option value="PF">PROGRESSIVE FREE FORM</option>
                        <option value="SV">SINGLE VISION</option>
                        <option value="SF">SINGLE VISION FREE FORM</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><b>Data Lensa</b></label>
                <div class="table-responsive" style="overflow-x:auto; max-width:100vw;">
                    <table class="table table-bordered align-middle mb-0" style="min-width:1200px; max-width:100%;" id="lensa-table">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Kode Lensa</th>
                                <th>Nama Lensa</th>
                                <th>Sph</th>
                                <th>Cyl</th>
                                <th>Axs</th>
                                <th>Add</th>
                                <th>Prisma1</th>
                                <th>Base1</th>
                                <th>Prisma2</th>
                                <th>Base2</th>
                                <th>Base Curve</th>
                                <th>PDF</th>
                                <th>PDN</th>
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
                                <td>
                                    <select class="form-select" id="r_spheris" name="data_lensa[r_spheris]" style="min-width:200px;max-width:350px;" >
                                        <option value="">Pilih Lensa R dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="r_cylinder" name="data_lensa[r_cylinder]" style="min-width:200px;max-width:350px;pointer-events:none;background:#eee;"  >
                                        <option value="">Pilih Lensa & Sph dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="r_axis" name="data_lensa[r_axis]" style="min-width:90px;max-width:140px;pointer-events:none;background:#eee;" >
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="r_add" name="data_lensa[r_add]" style="min-width:90px;max-width:140px;pointer-events:none;background:#eee;" >
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>

                                <td>
                                    <select class="form-select" id="r_prisma" name="data_lensa[r_prisma]" style="min-width:90px;max-width:140px;" disabled>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="r_base" name="data_lensa[r_base]" style="min-width:90px;max-width:140px;" disabled>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control" id="r_prisma2" style="min-width:90px;max-width:140px;" name="data_lensa[r_prisma2]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="r_base2" style="min-width:90px;max-width:140px;" name="data_lensa[r_base2]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="r_base_curve" style="min-width:90px;max-width:140px;" name="data_lensa[r_base_curve]" value="0" disabled /></td>

                                <td><input type="number" class="form-control" id="r_pd_far" style="min-width:90px;max-width:140px;" name="data_lensa[r_pd_far]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="r_pd_near" style="min-width:90px;max-width:140px;" name="data_lensa[r_pd_near]" value="0" disabled /></td>
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
                                <td>
                                    <select class="form-select" style="min-width:200px;max-width:350px;pointer-events:none;background:#eee;" id="l_spheris" name="data_lensa[l_spheris]" readonly>
                                        <option value="">Pilih Lensa L dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" style="min-width:200px;max-width:350px;pointer-events:none;background:#eee;" id="l_cylinder" name="data_lensa[l_cylinder]" readonly>
                                        <option value="">Pilih Lensa & Sph dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="l_axis" name="data_lensa[l_axis]" style="min-width:90px;max-width:140px;pointer-events:none;background:#eee;" readonly>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
    
                                <td>
                                    <select class="form-select" id="l_add" name="data_lensa[l_add]" style="min-width:90px;max-width:140px;pointer-events:none;background:#eee;" readonly>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
                                
                                <td>
                                    <select class="form-select" id="l_prisma" name="data_lensa[l_prisma]" style="min-width:90px;max-width:140px;" disabled>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select" id="l_base" name="data_lensa[l_base]" style="min-width:90px;max-width:140px;" disabled>
                                        <option value="">Pilih Cylinder dulu</option>
                                    </select>
                                </td>
    
                                <td><input type="number" class="form-control" id="l_prisma2" style="min-width:90px;max-width:140px;" name="data_lensa[l_prisma2]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="l_base2" style="min-width:90px;max-width:140px;" name="data_lensa[l_base2]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="l_base_curve" style="min-width:90px;max-width:140px;" name="data_lensa[l_base_curve]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="l_pd_far" style="min-width:90px;max-width:140px;" name="data_lensa[l_pd_far]" value="0" disabled /></td>
                                <td><input type="number" class="form-control" id="l_pd_near" style="min-width:90px;max-width:140px;" name="data_lensa[l_pd_near]" value="0" disabled /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="12" class="text-end">Total</th>
                                <th id="total_pd_far">0</th>
                                <th id="total_pd_near">0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </div> <!-- End lensa-section -->

            <div id="free-form-section" style="display: none;">
                <br>
                <label><strong>FREE FORM</strong></label>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-3 mb-1">
                        <label class="form-label">WA <small>(Wrap Angle)</small></label>
                        <select class="form-select" name="data_lensa[wa]" id="wa_select">
                            <?php for ($i = 0; $i <= 40; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?php
                                    if (isset($errors) && isset($_POST['data_lensa']['wa'])) {
                                        echo $_POST['data_lensa']['wa'] == $i ? 'selected' : '';
                                    } else {
                                        echo $i == 5 ? 'selected' : '';
                                    }
                                    ?>
                                ><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">PT <small>(Pantoscopic Tilt)</small></label>
                        <select class="form-select" name="data_lensa[pt]" id="pt_select">
                            <?php for ($i = 8; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?php
                                    if (isset($errors) && isset($_POST['data_lensa']['pt'])) {
                                        echo $_POST['data_lensa']['pt'] == $i ? 'selected' : '';
                                    } else {
                                        echo $i == 9 ? 'selected' : '';
                                    }
                                    ?>
                                ><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">BVD <small>(Back Vertex Distance)</small></label>
                        <select class="form-select" name="data_lensa[bvd]" id="bvd_select">
                            <?php for ($i = 8; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?php
                                    if (isset($errors) && isset($_POST['data_lensa']['bvd'])) {
                                        echo $_POST['data_lensa']['bvd'] == $i ? 'selected' : '';
                                    } else {
                                        echo $i == 12 ? 'selected' : '';
                                    }
                                    ?>
                                ><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">FFV <small>(Frame Fit Value)</small></label>
                        <select class="form-select" name="data_lensa[ffv]" id="ffv_select">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?php
                                    if (isset($errors) && isset($_POST['data_lensa']['ffv'])) {
                                        echo $_POST['data_lensa']['ffv'] == $i ? 'selected' : '';
                                    } else {
                                        echo $i == 0 ? 'selected' : '';
                                    }
                                    ?>
                                ><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">V CODE </label>
                        <select class="form-select" name="data_lensa[v_code]" id="vcode_select">
                            <option value="0"
                                <?php
                                if (isset($errors) && isset($_POST['data_lensa']['v_code'])) {
                                    echo $_POST['data_lensa']['v_code'] == '0' ? 'selected' : '';
                                } else {
                                    echo 'selected';
                                }
                                ?>
                            >0</option>
                            <?php for ($i = 4; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?php
                                    if (isset($errors) && isset($_POST['data_lensa']['v_code'])) {
                                        echo $_POST['data_lensa']['v_code'] == $i ? 'selected' : '';
                                    }
                                    ?>
                                ><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">RD <small>(Range Distance)</small></label>
                        <input type="number" class="form-control" name="data_lensa[rd]" min="0" step="1" value="<?= isset($errors) && isset($_POST['data_lensa']['rd']) ? esc($_POST['data_lensa']['rd']) : '0' ?>">
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label"><strong>MID <small>(Maximum Intermediate Distance)</small></strong> </label>
                        <input type="number" class="form-control" name="data_lensa[mid]" id="mid_input" value="<?= isset($errors) && isset($_POST['data_lensa']['mid']) ? esc($_POST['data_lensa']['mid']) : '' ?>" min="0">
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="form-label">PE <small>(Personal Engraving)</small></label>
                        <input type="text" class="form-control" name="data_lensa[pe]" maxlength="5" value="<?= isset($errors) && isset($_POST['data_lensa']['pe']) ? esc($_POST['data_lensa']['pe']) : '' ?>">
                    </div>
                    <div class="alert alert-info">
                        <p>
                            *Note : 
                            <ul>
                                <li>Pada Inputan <strong>MID (Maximum Intermediate Distance)</strong> 
                                    <br> untuk kode lensa ini ( 68539, 68540, 68541, 68542 ) pada Lensa R dan L harus terpenuhi semuanya,
                                    <br>tidak boleh lebih dari value 999, selain kode lensa ini harus isi dimulai ribuan 1001.
                                </li>
                                <li><strong>PE (Personal Engraving)</strong> Maximal inputan 5 karakter</li>
                            </ul>
                        </p>
                    </div>
                </div>
            </div> <!-- End free-form-section -->

            
            <br>
            <label><strong>MODEL FRAME</strong></label>
            <hr>
            <div class="mb-3">
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
            

            <br>
            <label><strong>FRAME</strong></label>
            <hr>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">INSTRUKSI KHUSUS</label>
                    <input type="text" class="form-control" name="data_lensa[spesial_instruksi]" value="<?= isset($errors) && isset($_POST['data_lensa']['spesial_instruksi']) ? esc($_POST['data_lensa']['spesial_instruksi']) : '' ?>">
                    <label class="form-label"><small>*Example: Gosok Tipis Max  </small></label>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NOTE</label>
                    <input type="text" class="form-control" name="data_lensa[note]" value="<?= isset($errors) && isset($_POST['data_lensa']['note']) ? esc($_POST['data_lensa']['note']) : '' ?>">
                    <label class="form-label"><small>*Example: Top Urgent </small></label>
                </div>
            </div>

            <div class="mt-2 mb-3">
                <label class="form-label"><strong>JENIS FRAME</strong></label>
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
                <label class="form-label"><strong>STATUS FRAME</strong></label>
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
            
            <!-- Frame Measurements Fields -->
            <div class="mb-3 mt-2">
                <label class="label mb-1"><strong>FRAME MEASUREMENTS</strong></label>
                <div class="row mb-4">
                    <!-- RIGHT Side -->
                    <div class="col-md-6">
                        <label class="form-label text-primary"><strong>RIGHT</strong></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">ET (Edge Thickness)</label>
                                <input type="number" class="form-control" name="data_lensa[r_et]" value="<?= isset($errors) && isset($_POST['data_lensa']['r_et']) ? esc($_POST['data_lensa']['r_et']) : '0' ?>" step="0.01">
                            </div>
                            <div class="col-6">
                                <label class="form-label">CT (Center Thickness)</label>
                                <input type="number" class="form-control" name="data_lensa[r_ct]" value="<?= isset($errors) && isset($_POST['data_lensa']['r_ct']) ? esc($_POST['data_lensa']['r_ct']) : '0' ?>" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <!-- LEFT Side -->
                    <div class="col-md-6">
                        <label class="form-label text-success"><strong>LEFT</strong></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">ET (Edge Thickness)</label>
                                <input type="number" class="form-control" name="data_lensa[l_et]" value="<?= isset($errors) && isset($_POST['data_lensa']['l_et']) ? esc($_POST['data_lensa']['l_et']) : '0' ?>" step="0.01">
                            </div>
                            <div class="col-6">
                                <label class="form-label">CT (Center Thickness)</label>
                                <input type="number" class="form-control" name="data_lensa[l_ct]" value="<?= isset($errors) && isset($_POST['data_lensa']['l_ct']) ? esc($_POST['data_lensa']['l_ct']) : '0' ?>" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Other Frame Measurements -->
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label class="form-label">B (Vertical)</label>
                        <input type="number" class="form-control" name="data_lensa[b_measurement]" value="<?= isset($errors) && isset($_POST['data_lensa']['b_measurement']) ? esc($_POST['data_lensa']['b_measurement']) : '0' ?>" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ED (Effectif Diameter)</label>
                        <input type="number" class="form-control" name="data_lensa[ed_measurement]" value="<?= isset($errors) && isset($_POST['data_lensa']['ed_measurement']) ? esc($_POST['data_lensa']['ed_measurement']) : '0' ?>" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">A (Lens Size)</label>
                        <input type="number" class="form-control" name="data_lensa[a_measurement]" value="<?= isset($errors) && isset($_POST['data_lensa']['a_measurement']) ? esc($_POST['data_lensa']['a_measurement']) : '0' ?>" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DBL (Bridge Size)</label>
                        <input type="number" class="form-control" name="data_lensa[dbl_measurement]" value="<?= isset($errors) && isset($_POST['data_lensa']['dbl_measurement']) ? esc($_POST['data_lensa']['dbl_measurement']) : '0' ?>" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SH/PY (Seg Height)</label>
                        <input type="number" class="form-control" name="data_lensa[sh_py_measurement]" id="sh_py_measurement" value="<?= isset($errors) && isset($_POST['data_lensa']['sh_py_measurement']) ? esc($_POST['data_lensa']['sh_py_measurement']) : '0' ?>" step="0.01">
                        <small id="sh_py_status" class="form-text text-muted"></small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">MBS</label>
                        <input type="number" class="form-control" name="data_lensa[mbs_measurement]" id="mbs_field" value="<?= isset($errors) && isset($_POST['data_lensa']['mbs_measurement']) ? esc($_POST['data_lensa']['mbs_measurement']) : '0' ?>" step="0.01" readonly>
                    </div>
                </div>
            </div>
            
            <br><hr>
            <div class="mb-3">
                <label class="form-label"><b>Jasa bisa lebih dari satu (Wajib jika pilihan hanya jasa YA, Opsional jika hanya jasa dipilih TIDAK )</b></label>

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
                                <td><input type="number" class="form-control" name="data_jasa[0][jasa_qty]" id="jasa_qty_0" placeholder="Qty Jasa" style="max-width:70px;"></td>
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

            <!-- <br><hr>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="manual">Manual</option>
                    <option value="midtrans">Midtrans</option>
                    <option value="xendit">Xendit</option>
                </select>
            </div> -->

            <br><hr>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-warning me-2" onclick="clearDraft(); location.reload();">Clear</button>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary"> Proses Transaksi </button>
                    <button type="button" class="btn btn-warning ms-2" onclick="debugFormData()">
                        <i class="fas fa-bug"></i> Debug Form
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Reset Lensa -->
<div class="modal fade" id="confirmResetLensaModal" tabindex="-1" aria-labelledby="confirmResetLensaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmResetLensaLabel"><b>Konfirmasi !!</b></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmResetLensaMsg">
        Tidak bisa ganti <b>Kode Brand atau Jenis Lensa</b> jika sudah pilih lensa R atau L, yakin dilanjutkan maka akan direset pilihan sekarang
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-warning me-2" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnConfirmResetLensa">Lanjutkan & Reset</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Reset Data (Hanya Jasa) -->
<div class="modal fade" id="confirmResetDataModal" tabindex="-1" aria-labelledby="confirmResetDataLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-dark"> <!-- bg-warning  -->
        <h5 class="modal-title" id="confirmResetDataLabel"><i class="fas fa-exclamation-triangle me-2"></i><b>Konfirmasi Reset Data</b></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <h6><i class="fas fa-info-circle me-2"></i>Peringatan!</h6>
          <p class="mb-2">Jika merubah pilihan <strong>"Hanya Jasa"</strong>, maka:</p>
          <ul class="mb-2">
            <li>Data <strong>Lensa</strong> akan direset</li>
            <li>Data <strong>Jasa</strong> akan direset</li>
            <li>Data <strong>Free Form</strong> akan direset</li>
          </ul>
          <p class="text-danger mb-0">Apakah Anda yakin ingin melanjutkan?</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" id="btnConfirmResetData">Ya, Reset Data</button>
      </div>
    </div>
  </div>
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

<!-- Modal Pilih Lensa -->
<div class="modal fade" id="lensaModal" tabindex="-1" aria-labelledby="lensaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lensaModalLabel">Pilih Lensa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-2" id="searchLensa" placeholder="Cari kode/nama lensa...">
                <div class="table-responsive" style="max-height:400px;overflow:auto;">
                    <table class="table table-stripped" id="lensaTable">
                        <thead>
                            <tr>
                                <th>Kode Lensa</th>
                                <th>Nama Lensa</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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

<!-- Modal Error / Notifikasi -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Peringatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                Pesan error akan ditampilkan di sini.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal Konfirmasi Submit Transaksi -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="confirmSubmitLabel"><i class="fas fa-check-circle me-2"></i><b>Konfirmasi Submit Transaksi</b></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <h6><i class="fas fa-info-circle me-2"></i>Yakin Diproses !!</h6>
          <p class="mb-0">Jika Sudah Lengkap Datanya Silahkan Lanjutkan.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success" id="btn-submit" >Ya, Proses Transaksi</button>
      </div>
    </div>
  </div>
</div>


<?php if (!empty($errors['no_po_exists'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('errorModalBody').innerText = 'Nomor Nota "<?= esc($errors['no_po']) ?>" sudah ada untuk store ini!';
        let modalEl = document.getElementById('errorModal');
        if (!window.errorModalInstance) {
            window.errorModalInstance = new bootstrap.Modal(modalEl);
        }
        window.errorModalInstance.show();
    });
</script>
<?php endif; ?>


<!-- jQuery (Wajib sebelum DataTables & Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>

    // Datatables Modal Lensa
    document.addEventListener('DOMContentLoaded', function() {
        let lensaTable;
        function initLensaTable() {
            if (lensaTable) {
                lensaTable.destroy();
                // Reset thead agar header selalu muncul
                var theadHtml = `<thead class="table-light">
                    <tr>
                        <th>Kode Lensa</th>
                        <th>Nama Lensa</th>
                        <th></th>
                    </tr>
                </thead>`;
                $('#lensaTable').html(theadHtml);
            }
            lensaTable = $('#lensaTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                pageLength: 10,
                ajax: {
                    url: '<?= base_url('backend/transaksi/datalensa') ?>',
                    type: 'GET',
                    data: function(d) {
                        d.kd_brand = document.getElementById('kd_brand').value;
                        d.jenis_lensa = document.getElementById('jenis_lensa').value;
                        d.search_custom = document.getElementById('searchLensa').value;
                    }
                },
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 }
                ]
            });
        }
        // Load DataTable setiap kali modal dibuka agar filter fresh
        $('#lensaModal').on('shown.bs.modal', function () {
            initLensaTable();
        });
        // Reload DataTable saat filter berubah
        document.getElementById('searchLensa').addEventListener('input', function() {
            if (lensaTable) lensaTable.ajax.reload();
        });
        document.getElementById('kd_brand').addEventListener('change', function() {
            if (lensaTable) lensaTable.ajax.reload();
        });
        document.getElementById('jenis_lensa').addEventListener('change', function() {
            if (lensaTable) lensaTable.ajax.reload();
        });
    });

    // Inisialisasi Select2 dan event handler independen untuk R dan L
    document.addEventListener('DOMContentLoaded', function() {
        // Select2 inisialisasi
        if (window.jQuery && $('#r_spheris').length) {
            $('#r_spheris').select2({ width: '100%', placeholder: 'Pilih Sph' });
        }
        if (window.jQuery && $('#l_spheris').length) {
            $('#l_spheris').select2({ width: '100%', placeholder: 'Pilih Sph' });
        }
        if (window.jQuery && $('#r_cylinder').length) {
            $('#r_cylinder').select2({ width: '100%', placeholder: 'Pilih Cylinder' });
        }
        if (window.jQuery && $('#l_cylinder').length) {
            $('#l_cylinder').select2({ width: '100%', placeholder: 'Pilih Cylinder' });
        }

        if (window.jQuery && $('#r_axis').length) {
            $('#r_axis').select2({ width: '100%', placeholder: 'Pilih Axis' });
        }
        if (window.jQuery && $('#l_axis').length) {
            $('#l_axis').select2({ width: '100%', placeholder: 'Pilih Axis' });
        }
        // Inisialisasi Select2 untuk select add (additional)
        if (window.jQuery && $('#r_add').length) {
            $('#r_add').select2({ width: '100%', placeholder: 'Pilih Additional' });
        }
        if (window.jQuery && $('#l_add').length) {
            $('#l_add').select2({ width: '100%', placeholder: 'Pilih Additional' });
        }
        // Inisialisasi Select2 untuk prisma 1
        if (window.jQuery && $('#r_prisma').length) {
            $('#r_prisma').select2({ width: '100%', placeholder: 'Pilih Prisma' });
        }
        if (window.jQuery && $('#l_prisma').length) {
            $('#l_prisma').select2({ width: '100%', placeholder: 'Pilih Prisma' });
        }
        // Inisialisasi Select2 untuk base 1
        if (window.jQuery && $('#r_base').length) {
            $('#r_base').select2({ width: '100%', placeholder: 'Pilih Base' });
        }
        if (window.jQuery && $('#l_base').length) {
            $('#l_base').select2({ width: '100%', placeholder: 'Pilih Base' });
        }

        // Inisialisasi disabled state untuk field additional saat halaman dimuat
        const additionalFieldIds = [
            'r_prisma2', 'r_base2', 'r_base_curve', 'r_pd_far', 'r_pd_near',
            'l_prisma2', 'l_base2', 'l_base_curve', 'l_pd_far', 'l_pd_near'
        ];
        additionalFieldIds.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.setAttribute('disabled', 'disabled');
                field.disabled = true;
                field.value = '0';
            }
        });

        // Event listener: update axis setelah pilih cylinder (select2)
        if (window.jQuery && $('#r_cylinder').length) {
            $('#r_cylinder').on('select2:select', function() {
                updateAxisSelectStateSingle('R');
                updatePrismaSelectStateSingle('R');
                updateBaseSelectStateSingle('R');
            });
        }
        if (window.jQuery && $('#l_cylinder').length) {
            $('#l_cylinder').on('select2:select', function() {
                updateAxisSelectStateSingle('L');
                updatePrismaSelectStateSingle('L');
                updateBaseSelectStateSingle('L');
            });
        }


        // Handler R: update sph dan cyl hanya untuk R
        const rLensaInput = document.getElementById('r_lensa');
        const rSphSelect = document.getElementById('r_spheris');
        if (rLensaInput) {
            rLensaInput.addEventListener('change', function() {
                // Jika lensa R dikosongkan, reset sph & cyl R
                if (!rLensaInput.value || rLensaInput.value.trim() === '') {
                    resetSelectSphCylSingle('R');
                }
                updateSphSelectStateSingle('R');
            });
        }
        if (rSphSelect) {
            rSphSelect.addEventListener('change', function() {
                updateCylinderSelectStateSingle('R');
            });
            if (window.jQuery) {
                $('#r_spheris').on('select2:select', function() {
                    updateCylinderSelectStateSingle('R');
                });
            }
        }
        // Handler L: update sph dan cyl hanya untuk L
        const lLensaInput = document.getElementById('l_lensa');
        const lSphSelect = document.getElementById('l_spheris');
        if (lLensaInput) {
            lLensaInput.addEventListener('change', function() {
                // Jika lensa L dikosongkan, reset sph & cyl L
                if (!lLensaInput.value || lLensaInput.value.trim() === '') {
                    resetSelectSphCylSingle('L');
                }
                updateSphSelectStateSingle('L');
            });
        }
        if (lSphSelect) {
            lSphSelect.addEventListener('change', function() {
                updateCylinderSelectStateSingle('L');
            });
            if (window.jQuery) {
                $('#l_spheris').on('select2:select', function() {
                    updateCylinderSelectStateSingle('L');
                });
            }
        }

        // Initial state
        updateSphSelectStateSingle('R');
        updateSphSelectStateSingle('L');
        // Initial state axiz
        updateAxisSelectStateSingle('R');
        updateAxisSelectStateSingle('L');
        // Initial state prisma
        updatePrismaSelectStateSingle('R');
        updatePrismaSelectStateSingle('L');
        // Initial state base
        updateBaseSelectStateSingle('R');
        updateBaseSelectStateSingle('L');
    });

    // Fungsi enable/disable SPH select per sisi
    function updateSphSelectStateSingle(side) {
        const lensaInput = document.getElementById(side === 'R' ? 'r_lensa' : 'l_lensa');
        const sphSelect = document.getElementById(side === 'R' ? 'r_spheris' : 'l_spheris');
        const hanyaJasa = document.getElementById('hanya_jasa').value;
        if (!lensaInput || !sphSelect) return;
        if (hanyaJasa === '1') {
            // Disable SPH untuk hanya jasa
            sphSelect.setAttribute('disabled', 'disabled');
            sphSelect.style.pointerEvents = 'none';
            sphSelect.style.background = '#eee';
            sphSelect.innerHTML = '<option value="">Hanya Jasa dipilih</option>';
            if (window.jQuery && $('#' + sphSelect.id).length) {
                $('#' + sphSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
            updateCylinderSelectStateSingle(side);
            return;
        }
        if (lensaInput.value && lensaInput.value.trim() !== '') {
            // Enable SPH select
            sphSelect.removeAttribute('disabled');
            sphSelect.style.pointerEvents = 'auto';
            sphSelect.style.background = '';
            if (window.jQuery && $('#' + sphSelect.id).length) {
                $('#' + sphSelect.id).prop('disabled', false).trigger('change.select2');
            }
            loadSphData(sphSelect.id);
        } else {
            // Disable SPH select
            sphSelect.setAttribute('disabled', 'disabled');
            sphSelect.style.pointerEvents = 'none';
            sphSelect.style.background = '#eee';
            sphSelect.value = '';
            sphSelect.innerHTML = '<option value="">' + (side === 'R' ? 'Pilih Lensa R dulu' : 'Pilih Lensa L dulu') + '</option>';
            if (window.jQuery && $('#' + sphSelect.id).length) {
                $('#' + sphSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
        }
        updateCylinderSelectStateSingle(side);
    }

    // Fungsi enable/disable dan load Cylinder per sisi
    function updateCylinderSelectStateSingle(side) {
        const lensaInput = document.getElementById(side === 'R' ? 'r_lensa' : 'l_lensa');
        const sphSelect = document.getElementById(side === 'R' ? 'r_spheris' : 'l_spheris');
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        if (!lensaInput || !sphSelect || !cylSelect) return;
        const lensaValue = lensaInput.value ? lensaInput.value.trim() : '';
        const sphValue = sphSelect.value ? sphSelect.value.trim() : '';
        if (lensaValue && sphValue) {
            // Enable cylinder select
            cylSelect.removeAttribute('disabled');
            cylSelect.style.pointerEvents = 'auto';
            cylSelect.style.background = '';
            loadCylinderData(cylSelect.id, lensaValue, sphValue);
        } else {
            // Disable cylinder select
            cylSelect.setAttribute('disabled', 'disabled');
            cylSelect.style.pointerEvents = 'none';
            cylSelect.style.background = '#eee';
            cylSelect.value = '';
            cylSelect.innerHTML = '<option value="">Pilih Lensa & Sph dulu</option>';
            if (window.jQuery && $('#' + cylSelect.id).length) {
                $('#' + cylSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
            updateAxisSelectStateSingle(side);
        }
    }

    // Function to load cylinder data from backend
    function loadCylinderData(selectId, kodeLensa, kodeSph) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        
        // Determine side from selectId
        const side = selectId.startsWith('r_') ? 'R' : 'L';
        
        // Show loading state dengan spinner
        selectElement.innerHTML = '<option value="">🔄 Loading data cylinder...</option>';
        selectElement.disabled = false;
        
        // Update Select2 untuk show loading state
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            // Tambahkan class loading untuk styling
            $('#' + selectId).next('.select2-container').addClass('loading-cylinder');
        }
        
        // Tambahkan delay minimum agar loading terlihat
        const minLoadingTime = 500; // 500ms minimum loading
        const startTime = Date.now();

        // Build GET URL
        const url = '<?= base_url('backend/transaksi/getcylinder') ?>'
            + '?kode_lensa=' + encodeURIComponent(kodeLensa)
            + '&kode_spheris=' + encodeURIComponent(kodeSph);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Pastikan loading minimal terlihat
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            
            setTimeout(() => {
                // Enable dan clear select, lalu add default option
                selectElement.removeAttribute('disabled');
                selectElement.style.pointerEvents = 'auto';
                selectElement.style.background = '';
                selectElement.innerHTML = '<option value="">Pilih Cylinder</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.kode_cylinder;
                        option.textContent = item.kode_cylinder;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data cylinder ditemukan</option>';
                }
                // Remove loading class dan refresh Select2
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).prop('disabled', false).val('').trigger('change');
                }
                // Update axis state setelah cylinder data berhasil dimuat
                setTimeout(() => {
                    updateAxisSelectStateSingle(side);
                }, 100);
            }, remainingTime);
        })
        .catch(error => {
            // Pastikan loading minimal terlihat untuk error juga
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            
            setTimeout(() => {
                console.error('Error loading cylinder data:', error);
                selectElement.removeAttribute('disabled');
                selectElement.style.pointerEvents = 'auto';
                selectElement.style.background = '';
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).prop('disabled', false).val('').trigger('change');
                }
                // Update axis state setelah error loading cylinder
                updateAxisSelectStateSingle(side);
            }, remainingTime);
        });
    }

    // Fungsi enable/disable dan load Axis per sisi
    function updateAxisSelectStateSingle(side) {
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        const axisSelect = document.getElementById(side === 'R' ? 'r_axis' : 'l_axis');
        if (!cylSelect || !axisSelect) return;
        const cylValue = cylSelect.value ? cylSelect.value.trim() : '';
        if (cylValue && !cylSelect.disabled) {
            // Enable axis select
            axisSelect.removeAttribute('disabled');
            axisSelect.style.pointerEvents = 'auto';
            axisSelect.style.background = '';
            if (window.jQuery && $('#' + axisSelect.id).length) {
                $('#' + axisSelect.id).prop('disabled', false).trigger('change.select2');
            }
            loadAxisData(axisSelect.id);
        } else {
            // Disable axis select
            axisSelect.setAttribute('disabled', 'disabled');
            axisSelect.style.pointerEvents = 'none';
            axisSelect.style.background = '#eee';
            axisSelect.value = '';
            axisSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            if (window.jQuery && $('#' + axisSelect.id).length) {
                $('#' + axisSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
        }
        // Additional ikut update setelah axis
        updateAdditionalSelectStateSingle(side);
    }

    // Function to load SPH data from backend
    function loadSphData(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">🔄 Loading data spheris...</option>';
        // selectElement.disabled = false;
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            $('#' + selectId).next('.select2-container').addClass('loading-sph');
        }
        const minLoadingTime = 500;
        const startTime = Date.now();
        const url = '<?= base_url('backend/transaksi/getspheris') ?>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                selectElement.innerHTML = '<option value="">Pilih Sph</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.kode_spheris;
                        option.textContent = item.kode_spheris;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data spheris ditemukan</option>';
                }
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-sph');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        })
        .catch(error => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                console.error('Error loading sph data:', error);
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-sph');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        });
    }

    // Function to load axis data from backend
    function loadAxisData(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">🔄 Loading data axis...</option>';
        selectElement.disabled = false;
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            $('#' + selectId).next('.select2-container').addClass('loading-cylinder');
        }
        const minLoadingTime = 500;
        const startTime = Date.now();
        const url = '<?= base_url('backend/transaksi/getaxis') ?>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                selectElement.innerHTML = '<option value="">Pilih Axis</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.kode_axis;
                        option.textContent = item.kode_axis;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data axis ditemukan</option>';
                }
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        })
        .catch(error => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                console.error('Error loading axis data:', error);
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        });
    }

    // Fungsi untuk reset sph & cyl per sisi (R/L)
    function resetSelectSphCylSingle(side) {
        const sphSelect = document.getElementById(side === 'R' ? 'r_spheris' : 'l_spheris');
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        const axisSelect = document.getElementById(side === 'R' ? 'r_axis' : 'l_axis');
        if (sphSelect) {
            sphSelect.value = '';
            const firstOption = sphSelect.querySelector('option[value=""]');
            if (firstOption) firstOption.textContent = side === 'R' ? 'Pilih Lensa R dulu' : 'Pilih Lensa L dulu';
        }
        if (cylSelect) {
            cylSelect.value = '';
            cylSelect.innerHTML = '<option value="">Pilih Lensa & Sph dulu</option>';
        }
        if (axisSelect) {
            axisSelect.value = '';
            axisSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
        }
    }

    // Fungsi enable/disable dan load Additional per sisi
    function updateAdditionalSelectStateSingle(side) {
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        const addSelect = document.getElementById(side === 'R' ? 'r_add' : 'l_add');
        if (!cylSelect || !addSelect) return;
        const cylValue = cylSelect.value ? cylSelect.value.trim() : '';
        if (cylValue && !cylSelect.disabled) {
            // Enable additional select
            addSelect.removeAttribute('disabled');
            addSelect.style.pointerEvents = 'auto';
            addSelect.style.background = '';
            if (window.jQuery && $('#' + addSelect.id).length) {
                $('#' + addSelect.id).prop('disabled', false).trigger('change.select2');
            }
            loadAdditionalData(addSelect.id);
        } else {
            // Disable additional select
            addSelect.setAttribute('disabled', 'disabled');
            addSelect.style.pointerEvents = 'none';
            addSelect.style.background = '#eee';
            addSelect.value = '';
            addSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            if (window.jQuery && $('#' + addSelect.id).length) {
                $('#' + addSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
        }
        // Prisma ikut update setelah axis
        updatePrismaSelectStateSingle(side);
    }

    // Fungsi load data additional dari backend
    function loadAdditionalData(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">🔄 Loading data additional...</option>';
        selectElement.disabled = false;
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            $('#' + selectId).next('.select2-container').addClass('loading-cylinder');
        }
        const minLoadingTime = 500;
        const startTime = Date.now();
        const url = '<?= base_url('backend/transaksi/getadditional') ?>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                selectElement.innerHTML = '<option value="">Pilih Additional</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.kode_additional;
                        option.textContent = item.kode_additional;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data additional ditemukan</option>';
                }
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        })
        .catch(error => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                console.error('Error loading additional data:', error);
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        });
    }

    // Fungsi enable/disable dan load Prisma per sisi
    function updatePrismaSelectStateSingle(side) {
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        const prismaSelect = document.getElementById(side === 'R' ? 'r_prisma' : 'l_prisma');
        if (!cylSelect || !prismaSelect) return;
        const cylValue = cylSelect.value ? cylSelect.value.trim() : '';
        if (cylValue && !cylSelect.disabled) {
            // Enable prisma select
            prismaSelect.removeAttribute('disabled');
            prismaSelect.style.pointerEvents = 'auto';
            prismaSelect.style.background = '';
            if (window.jQuery && $('#' + prismaSelect.id).length) {
                $('#' + prismaSelect.id).prop('disabled', false).trigger('change.select2');
            }
            loadPrismaData(prismaSelect.id);
        } else {
            // Disable prisma select
            prismaSelect.setAttribute('disabled', 'disabled');
            prismaSelect.style.pointerEvents = 'none';
            prismaSelect.style.background = '#eee';
            prismaSelect.value = '';
            prismaSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            if (window.jQuery && $('#' + prismaSelect.id).length) {
                $('#' + prismaSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
        }
        updateBaseSelectStateSingle(side);
    }

    // Function to load prisma data from backend
    function loadPrismaData(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">🔄 Loading data prisma...</option>';
        selectElement.disabled = false;
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            $('#' + selectId).next('.select2-container').addClass('loading-cylinder');
        }
        const minLoadingTime = 500;
        const startTime = Date.now();
        const url = '<?= base_url('backend/transaksi/getprisma') ?>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                selectElement.innerHTML = '<option value="">Pilih Prisma</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item;
                        option.textContent = item;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data prisma ditemukan</option>';
                }
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        })
        .catch(error => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                console.error('Error loading prisma data:', error);
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        });
    }
    
    // Fungsi enable/disable dan load Base per sisi
    function updateBaseSelectStateSingle(side) {
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        const baseSelect = document.getElementById(side === 'R' ? 'r_base' : 'l_base');
        if (!cylSelect || !baseSelect) return;
        const cylValue = cylSelect.value ? cylSelect.value.trim() : '';
        if (cylValue && !cylSelect.disabled) {
            // Enable base select
            baseSelect.removeAttribute('disabled');
            baseSelect.style.pointerEvents = 'auto';
            baseSelect.style.background = '';
            if (window.jQuery && $('#' + baseSelect.id).length) {
                $('#' + baseSelect.id).prop('disabled', false).trigger('change.select2');
            }
            loadBaseData(baseSelect.id);
        } else {
            // Disable base select
            baseSelect.setAttribute('disabled', 'disabled');
            baseSelect.style.pointerEvents = 'none';
            baseSelect.style.background = '#eee';
            baseSelect.value = '';
            baseSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            if (window.jQuery && $('#' + baseSelect.id).length) {
                $('#' + baseSelect.id).val('').prop('disabled', true).trigger('change.select2');
            }
        }
        // Aktifkan field prisma2, base2, base_curve, PDF, PDN setelah cylinder dipilih
        updateAdditionalFields(side);
    }

    // Function to load base data from backend
    function loadBaseData(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">🔄 Loading data base...</option>';
        selectElement.disabled = false;
        if (window.jQuery && $('#' + selectId).length) {
            $('#' + selectId).val('').trigger('change');
            $('#' + selectId).next('.select2-container').addClass('loading-cylinder');
        }
        const minLoadingTime = 500;
        const startTime = Date.now();
        const url = '<?= base_url('backend/transaksi/getbase') ?>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                selectElement.innerHTML = '<option value="">Pilih Base</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item;
                        option.textContent = item;
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = '<option value="">Tidak ada data base ditemukan</option>';
                }
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        })
        .catch(error => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
            setTimeout(() => {
                console.error('Error loading base data:', error);
                selectElement.innerHTML = '<option value="">❌ Error saat mengambil data</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).next('.select2-container').removeClass('loading-cylinder');
                    $('#' + selectId).val('').trigger('change');
                }
            }, remainingTime);
        });
    }

    // Fungsi untuk mengaktifkan field prisma2, base2, base_curve, PDF, PDN setelah cylinder dipilih
    function updateAdditionalFields(side) {
        const cylSelect = document.getElementById(side === 'R' ? 'r_cylinder' : 'l_cylinder');
        if (!cylSelect) return;
        
        const cylValue = cylSelect.value ? cylSelect.value.trim() : '';
        const prefix = side === 'R' ? 'r_' : 'l_';
        
        // Field IDs yang akan diaktifkan/nonaktifkan
        const fieldIds = [
            prefix + 'prisma2',
            prefix + 'base2', 
            prefix + 'base_curve',
            prefix + 'pd_far',
            prefix + 'pd_near'
        ];
        
        fieldIds.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (cylValue && !cylSelect.disabled) {
                    // Aktifkan field
                    field.removeAttribute('disabled');
                    field.disabled = false;
                } else {
                    // Nonaktifkan field dan reset ke 0
                    field.setAttribute('disabled', 'disabled');
                    field.disabled = true;
                    field.value = '0';
                }
            }
        });
    }

</script>

<script>

    // Instance global modal jasa & lensa
    window.jasaModalInstance = null;
    window.lensaModalInstance = null;
    // Expose user_id to JS
    const SESSION_USER_ID = <?= json_encode($user_id) ?>;


    // Set default tanggal nota ke hari ini jika kosong
    // dan set default tanggal selesai sesuai aturan jam 15:00
    document.addEventListener('DOMContentLoaded', function() {
        var tglInput = document.getElementById('tgl_nota_input');
        var tglSelesaiInput = document.getElementById('tgl_selesai_input');
        // Set tgl_nota jika kosong
        if (tglInput && !tglInput.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            tglInput.value = `${yyyy}-${mm}-${dd}`;
        }
        // Set tgl_selesai sesuai aturan jam 15:00
        if (tglSelesaiInput && tglInput) {
            const now = new Date();
            // Buat jam 15:00 hari ini
            const batas = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 15, 0, 0);
            let hariTambah = (now <= batas) ? 1 : 2;
            // tgl_nota selalu hari ini (readonly)
            const tglSelesai = new Date(now.getFullYear(), now.getMonth(), now.getDate() + hariTambah);
            const yyyy = tglSelesai.getFullYear();
            const mm = String(tglSelesai.getMonth() + 1).padStart(2, '0');
            const dd = String(tglSelesai.getDate()).padStart(2, '0');
            tglSelesaiInput.value = `${yyyy}-${mm}-${dd}`;
        }
    });

    // Validasi MID sesuai kode lensa R/L
    document.addEventListener('DOMContentLoaded', function() {
        const midInput = document.getElementById('mid_input');
        const rLensa = document.getElementById('r_lensa');
        const lLensa = document.getElementById('l_lensa');
        function validateMid() {
            const allowedKode = ['68539', '68540', '68541', '68542'];
            const kodeR = rLensa.value;
            const kodeL = lLensa.value;
            const val = parseInt(midInput.value, 10);
            if (allowedKode.includes(kodeR) && allowedKode.includes(kodeL)) {
                // Keduanya harus kode khusus, max 999
                if (val > 999) {
                    midInput.value = 999;
                    midInput.setCustomValidity('Untuk kode lensa khusus (R & L), nilai MID maksimal 999');
                } else {
                    midInput.setCustomValidity('');
                }
            } else {
                // Salah satu/tidak keduanya, min 1001
                if (val < 1001 && midInput.value !== '') {
                    midInput.value = 1001;
                    midInput.setCustomValidity('Untuk kode lensa lain, nilai MID minimal 1001');
                } else {
                    midInput.setCustomValidity('');
                }
            }
        }
        midInput.addEventListener('input', validateMid);
        rLensa.addEventListener('input', validateMid);
        lLensa.addEventListener('input', validateMid);
    });


    // Set readonly dan default untuk WA, PT, BVD, FFV, V CODE jika jenis lensa bukan PROGRESSIVE FREE FORM/SINGLE VISION FREE FORM
    function setReadonlyFF() {
        const jenisLensa = document.getElementById('jenis_lensa');
        const wa = document.getElementById('wa_select');
        const pt = document.getElementById('pt_select');
        const bvd = document.getElementById('bvd_select');
        const ffv = document.getElementById('ffv_select');
        const vcode = document.getElementById('vcode_select');
        const allowed = ['PF', 'SF'];
        let val = jenisLensa.value;
        <?php if (isset($errors) && isset($_POST['data_lensa']['jenis_lensa'])): ?>
            val = "<?= esc($_POST['data_lensa']['jenis_lensa']) ?>";
        <?php endif; ?>
        if (!allowed.includes(val)) {
            wa.value = '5'; wa.setAttribute('readonly', 'readonly'); wa.setAttribute('disabled', 'disabled');
            pt.value = '9'; pt.setAttribute('readonly', 'readonly'); pt.setAttribute('disabled', 'disabled');
            bvd.value = '12'; bvd.setAttribute('readonly', 'readonly'); bvd.setAttribute('disabled', 'disabled');
            ffv.value = '0'; ffv.setAttribute('readonly', 'readonly'); ffv.setAttribute('disabled', 'disabled');
            vcode.value = '0'; vcode.setAttribute('readonly', 'readonly'); vcode.setAttribute('disabled', 'disabled');
        } else {
            wa.removeAttribute('readonly'); wa.removeAttribute('disabled');
            pt.removeAttribute('readonly'); pt.removeAttribute('disabled');
            bvd.removeAttribute('readonly'); bvd.removeAttribute('disabled');
            ffv.removeAttribute('readonly'); ffv.removeAttribute('disabled');
            vcode.removeAttribute('readonly'); vcode.removeAttribute('disabled');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const jenisLensa = document.getElementById('jenis_lensa');
        if (jenisLensa) {
            jenisLensa.addEventListener('change', setReadonlyFF);
            setReadonlyFF();
        }
    });

    let jasaIdx = 1;
    function addJasa() {
        const jasaList = document.getElementById('jasa-list');
        const idx = jasaList.children.length;
        var hanyaJasa = document.getElementById('hanya_jasa').value;
        
        // Tentukan atribut untuk qty input berdasarkan mode
        var qtyAttributes = '';
        if (hanyaJasa === '1') {
            qtyAttributes = 'required min="1" max="2"';
        } else {
            qtyAttributes = 'readonly';
        }
        
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><input type="text" class="form-control" name="data_jasa[${idx}][jasa_id]" id="jasa_id_${idx}" placeholder="Kode Jasa" readonly></td>
            <td><input type="text" class="form-control" name="data_jasa[${idx}][jasa_nama]" id="jasa_nama_${idx}" placeholder="Nama Jasa" readonly></td>
            <td><input type="number" class="form-control" name="data_jasa[${idx}][jasa_qty]" placeholder="Qty Jasa" style="max-width:70px;" ${qtyAttributes}></td>
            <td><div class='d-flex flex-row gap-1 justify-content-center'><button type="button" class="btn btn-outline-primary btn-sm" onclick="openJasaModal(${idx})">Pilih</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();updateJasaActionButtons();saveDraft();">Hapus</button></div></td>`;
        jasaList.appendChild(tr);
        
        // Update field properties tapi jangan reset nilai yang sudah ada
        updateJasaQtyFields();
        updateJasaActionButtons(); // <-- update tombol setelah tambah baris
    }
    function openJasaModal(idx) {
        jasaTargetIdx = idx;
        var modalEl = document.getElementById('jasaModal');
        if (!window.jasaModalInstance) {
            window.jasaModalInstance = new bootstrap.Modal(modalEl);
        }
        window.jasaModalInstance.show();
    }
    function selectJasa(kode, nama) {
        // Validasi: jika kode jasa sudah ada di input manapun, tampilkan modal info
        if (window.event) window.event.preventDefault();
        let sudahAda = false;
        document.querySelectorAll('input[name^="data_jasa"][name$="[jasa_id]"]').forEach(function(input) {
            if (input.value === kode) {
                sudahAda = true;
            }
        });
        if (sudahAda) {
            let modalEl = document.getElementById('errorModal');
            if (modalEl) {
                document.getElementById('errorModalBody').innerText = 'Jasa ini sudah dipilih!';
                if (!window.errorModalInstance) {
                    window.errorModalInstance = new bootstrap.Modal(modalEl);
                }
                window.errorModalInstance.show();
            } else {
                alert('Jasa ini sudah dipilih!');
            }
            return false;
        }
        document.getElementById('jasa_id_' + jasaTargetIdx).value = kode;
        document.getElementById('jasa_nama_' + jasaTargetIdx).value = nama;
        saveDraft();
        // Tutup modal jasa dengan instance global
        if (window.jasaModalInstance) {
            window.jasaModalInstance.hide();
        }
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
        // Reset search input dan filter table
        var searchInput = document.getElementById('searchLensa');
        if (searchInput) {
            searchInput.value = '';
        }
        var modalEl = document.getElementById('lensaModal');
        if (!window.lensaModalInstance) {
            window.lensaModalInstance = new bootstrap.Modal(modalEl);
        }
        window.lensaModalInstance.show();
    }
    function selectLensa(kode, nama) {
        if (lensaTargetKodeId && lensaTargetNamaId) {
            document.getElementById(lensaTargetKodeId).value = kode;
            document.getElementById(lensaTargetNamaId).value = nama;
            document.getElementById(lensaTargetKodeId).dispatchEvent(new Event('input'));
            // Setelah pilih lensa, update SPH & Axis select state per sisi
            if (lensaTargetKodeId === 'r_lensa') {
                updateSphSelectStateSingle('R');
                updateAxisSelectStateSingle('R');
            } else if (lensaTargetKodeId === 'l_lensa') {
                updateSphSelectStateSingle('L');
                updateAxisSelectStateSingle('L');
            }
            saveDraft(); // autosave setelah pilih lensa
        }
        if (window.lensaModalInstance) {
            window.lensaModalInstance.hide();
        }
    }


    function clearLensa(kodeId, namaId) {
        // Hapus semua input di baris yang sama (R atau L)
        const row = kodeId.startsWith('r_') ? 'r_' : 'l_';
        const fields = [
            row + 'lensa', row + 'nama_lensa', row + 'spheris', row + 'axis', row + 'additional',
            row + 'pdf', row + 'pdn', row + 'prisma', row + 'base', row + 'prisma2', row + 'base2', 
            row + 'base_curve', row + 'pd_far', row + 'pd_near', row + 'edge_thickness'
        ];
        fields.forEach(function(fid) {
            var el = document.getElementById(fid);
            if (el) {
                if (el.tagName === 'SELECT') {
                    el.value = '';
                    if (window.jQuery && $('#' + fid).length) {
                        $('#' + fid).val('').trigger('change.select2');
                    }
                } else {
                    el.value = '';
                }
            }
        });
        // Reset base select juga
        const baseSelect = document.getElementById(row + 'base');
        if (baseSelect) {
            baseSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            baseSelect.setAttribute('disabled', 'disabled');
            if (window.jQuery && $('#' + row + 'base').length) {
                $('#' + row + 'base').val('').prop('disabled', true).trigger('change.select2');
            }
        }
        // Reset cylinder select with proper placeholder
        const cylSelect = document.getElementById(row + 'cylinder');
        if (cylSelect) {
            cylSelect.innerHTML = '<option value="">Pilih Lensa & Sph dulu</option>';
            cylSelect.setAttribute('disabled', 'disabled');
            if (window.jQuery && $('#' + row + 'cylinder').length) {
                $('#' + row + 'cylinder').val('').prop('disabled', true).trigger('change.select2');
            }
        }
        // Reset prisma select juga
        const prismaSelect = document.getElementById(row + 'prisma');
        if (prismaSelect) {
            prismaSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
            prismaSelect.setAttribute('disabled', 'disabled');
            if (window.jQuery && $('#' + row + 'prisma').length) {
                $('#' + row + 'prisma').val('').prop('disabled', true).trigger('change.select2');
            }
        }
        
        // Reset disabled state untuk field additional (prisma2, base2, base_curve, pd_far, pd_near)
        const additionalFields = [row + 'prisma2', row + 'base2', row + 'base_curve', row + 'pd_far', row + 'pd_near'];
        additionalFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '0';
                field.setAttribute('disabled', 'disabled');
                field.disabled = true;
            }
        });
        
        // Update SPH select state after clearing lensa (per sisi)
        if (row === 'r_') {
        updateSphSelectStateSingle('R');
        updateAxisSelectStateSingle('R');
        updatePrismaSelectStateSingle('R');
        updateBaseSelectStateSingle('R');
        } else {
        updateSphSelectStateSingle('L');
        updateAxisSelectStateSingle('L');
        updatePrismaSelectStateSingle('L');
        updateBaseSelectStateSingle('L');
        }
        updateTotalPdfPdn();
        updateJasaQtyFields();
        updateJasaActionButtons(); // <-- update tombol setelah clear lensa
        saveDraft(); // update localStorage setelah batal pilih lensa
    }

    function updateTotalPdfPdn() {
        let pdfR = parseFloat(document.querySelector('[name="data_lensa[r_pd_far]"]').value) || 0;
        let pdfL = parseFloat(document.querySelector('[name="data_lensa[l_pd_far]"]').value) || 0;
        let pdnR = parseFloat(document.querySelector('[name="data_lensa[r_pd_near]"]').value) || 0;
        let pdnL = parseFloat(document.querySelector('[name="data_lensa[l_pd_near]"]').value) || 0;
        document.getElementById('total_pd_far').innerText = pdfR + pdfL;
        document.getElementById('total_pd_near').innerText = pdnR + pdnL;
        
        // Update MBS calculation after PD Far changes
        updateMBSCalculation();
    }

    // Function to calculate MBS based on the given formula
    function updateMBSCalculation() {
        let effectifDiameter = parseFloat(document.querySelector('[name="data_lensa[ed_measurement]"]').value) || 0;
        let lensSize = parseFloat(document.querySelector('[name="data_lensa[a_measurement]"]').value) || 0;
        let bridgeSize = parseFloat(document.querySelector('[name="data_lensa[dbl_measurement]"]').value) || 0;
        
        // Get total PDF from the display element or recalculate
        let pdfR = parseFloat(document.querySelector('[name="data_lensa[r_pd_far]"]').value) || 0;
        let pdfL = parseFloat(document.querySelector('[name="data_lensa[l_pd_far]"]').value) || 0;
        let totalPdf = pdfR + pdfL;
        
        let mbs = 0;
        if (effectifDiameter > 0 && lensSize > 0 && bridgeSize > 0 && totalPdf > 0) {
            mbs = Math.round(effectifDiameter + lensSize + bridgeSize + 2 - totalPdf);
        }
        
        document.querySelector('[name="data_lensa[mbs_measurement]"]').value = mbs;
    }
    
    // Function untuk update status SH/PY
    function updateSHPYStatus() {
        const shpyInput = document.getElementById('sh_py_measurement');
        const shpyStatus = document.getElementById('sh_py_status');
        
        if (shpyInput && shpyStatus) {
            const value = parseFloat(shpyInput.value) || 0;
            
            if (value < 18) {
                shpyStatus.textContent = 'SHORT';
                shpyStatus.className = 'form-text text-danger fw-bold';
            } else if (value >= 18) {
                shpyStatus.textContent = 'LONG';
                shpyStatus.className = 'form-text text-success fw-bold';
            } else {
                shpyStatus.textContent = '';
                shpyStatus.className = 'form-text text-muted';
            }
        }
    }
    
    document.querySelector('[name="data_lensa[r_pd_far]"]').addEventListener('input', updateTotalPdfPdn);
    document.querySelector('[name="data_lensa[l_pd_far]"]').addEventListener('input', updateTotalPdfPdn);
    document.querySelector('[name="data_lensa[r_pd_near]"]').addEventListener('input', updateTotalPdfPdn);
    document.querySelector('[name="data_lensa[l_pd_near]"]').addEventListener('input', updateTotalPdfPdn);
    
    // Event listeners for MBS calculation
    document.querySelector('[name="data_lensa[ed_measurement]"]').addEventListener('input', updateMBSCalculation);
    document.querySelector('[name="data_lensa[a_measurement]"]').addEventListener('input', updateMBSCalculation);
    document.querySelector('[name="data_lensa[dbl_measurement]"]').addEventListener('input', updateMBSCalculation);
    
    // Event listener for SH/PY status update
    document.querySelector('[name="data_lensa[sh_py_measurement]"]').addEventListener('input', updateSHPYStatus);
    
    // Inisialisasi awal
    updateTotalPdfPdn();
    updateSHPYStatus();

    function toggleLensaFields() {
        var hanyaJasa = document.getElementById('hanya_jasa').value;
        
        // Cari container seluruh bagian lensa dan free form
        var lensaSection = document.getElementById('lensa-section');
        var freeFormSection = document.getElementById('free-form-section');
        
        if (hanyaJasa === '1') {
            // Sembunyikan seluruh bagian lensa dan free form
            if (lensaSection) {
                lensaSection.style.display = 'none';
            }
            if (freeFormSection) {
                freeFormSection.style.display = 'none';
            }
        } else {
            // Tampilkan kembali bagian lensa dan free form
            if (lensaSection) {
                lensaSection.style.display = 'block';
            }
            if (freeFormSection) {
                freeFormSection.style.display = 'block';
            }
            
            // Pastikan field yang seharusnya aktif tetap aktif berdasarkan dependent logic
            // Update SPH select state for both sides
            updateSphSelectStateSingle('R');
            updateSphSelectStateSingle('L');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi nilai previousHanyaJasaValue
        previousHanyaJasaValue = document.getElementById('hanya_jasa').value;
        
        toggleLensaFields();
        // Initialize SPH & Axis state on page load per sisi
        updateSphSelectStateSingle('R');
        updateSphSelectStateSingle('L');
        updateAxisSelectStateSingle('R');
        updateAxisSelectStateSingle('L');
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
            
            // Simpan nilai saat ini untuk mencegah reset
            var currentValue = qtyInput.value;
            
            if (hanyaJasa === '1') {
                // Mode "Hanya Jasa" - input manual wajib
                qtyInput.removeAttribute('readonly');
                qtyInput.removeAttribute('disabled');
                qtyInput.setAttribute('required', 'required');
                qtyInput.setAttribute('min', '1');
                qtyInput.setAttribute('max', '2');
                // Jangan reset nilai jika sudah ada
                if (!currentValue) {
                    qtyInput.value = '';
                }
            } else {
                // Mode biasa - qty otomatis sesuai jumlah lensa
                qtyInput.removeAttribute('required');
                qtyInput.removeAttribute('min');
                qtyInput.removeAttribute('max');
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

    // Fungsi untuk reset semua data lensa
    function resetLensaData() {
        // Reset data lensa R
        const rLensaFields = ['r_lensa', 'r_nama_lensa', 'r_spheris', 'r_cylinder', 'r_axis', 'r_add'];
        rLensaFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                if (window.jQuery && $(field).length) {
                    $(field).val('').trigger('change.select2');
                }
            }
        });
        
        // Reset data lensa L
        const lLensaFields = ['l_lensa', 'l_nama_lensa', 'l_spheris', 'l_cylinder', 'l_axis', 'l_add'];
        lLensaFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                if (window.jQuery && $(field).length) {
                    $(field).val('').trigger('change.select2');
                }
            }
        });
        
        // Reset SPH selects to initial state
        const sphSelects = ['r_spheris', 'l_spheris'];
        sphSelects.forEach(function(selectId) {
            const selectElement = document.getElementById(selectId);
            if (selectElement) {
                const side = selectId.startsWith('r_') ? 'R' : 'L';
                // selectElement.setAttribute('disabled', 'disabled');
                selectElement.innerHTML = '<option value="">Pilih Lensa ' + side + ' dulu</option>';
                if (window.jQuery && $('#' + selectId).length) {
                    $('#' + selectId).val('').prop('disabled', true).trigger('change.select2');
                }
            }
        });
        
        // Reset additional fields (prisma, base, dll)
        const additionalFields = [
            'prisma2', 'base2', 'base_curve', 'pd_far', 'pd_near', 
            'r_pd_far', 'r_pd_near', 'l_pd_far', 'l_pd_near'
        ];
        additionalFields.forEach(function(fieldName) {
            const field = document.querySelector(`[name="data_lensa[${fieldName}]"]`);
            if (field) {
                field.value = '0';
                field.setAttribute('disabled', 'disabled');
            }
        });
        
        // Reset frame measurement fields
        const frameMeasurementFields = [
            { name: 'r_et', defaultValue: '0' },
            { name: 'r_ct', defaultValue: '0' },
            { name: 'l_et', defaultValue: '0' },
            { name: 'l_ct', defaultValue: '0' },
            { name: 'b_measurement', defaultValue: '0' },
            { name: 'ed_measurement', defaultValue: '0' },
            { name: 'a_measurement', defaultValue: '0' },
            { name: 'dbl_measurement', defaultValue: '0' },
            { name: 'sh_py_measurement', defaultValue: '0' },
            { name: 'mbs_measurement', defaultValue: '0' }
        ];
        frameMeasurementFields.forEach(function(fieldObj) {
            const field = document.querySelector(`[name="data_lensa[${fieldObj.name}]"]`);
            if (field) {
                field.value = fieldObj.defaultValue;
            }
        });
        
        // Reset frame-related fields
        const frameFields = ['spesial_instruksi', 'note'];
        frameFields.forEach(function(fieldName) {
            const field = document.querySelector(`[name="data_lensa[${fieldName}]"]`);
            if (field) {
                field.value = '';
            }
        });
        
        // Reset radio buttons for jenis_frame and status_frame
        const radioGroups = ['jenis_frame', 'status_frame'];
        radioGroups.forEach(function(groupName) {
            const radios = document.querySelectorAll(`[name="data_lensa[${groupName}]"]`);
            radios.forEach(function(radio) {
                radio.checked = false;
            });
        });
        
        // Reset free form fields
        const freeFormFields = ['wa', 'pt', 'bvd', 'ffv', 'v_code', 'rd', 'mid', 'pe'];
        freeFormFields.forEach(function(fieldName) {
            const field = document.querySelector(`[name="data_lensa[${fieldName}]"]`);
            if (field) {
                if (fieldName === 'wa') field.value = '5';
                else if (fieldName === 'pt') field.value = '9';
                else if (fieldName === 'bvd') field.value = '12';
                else if (fieldName === 'v_code') field.value = '0';
                else field.value = '';
            }
        });
    }
    
    // Fungsi untuk reset semua data jasa
    function resetJasaData() {
        // Reset semua input jasa
        const jasaInputs = document.querySelectorAll('#jasa-list input');
        jasaInputs.forEach(function(input) {
            input.value = '';
        });
        
        // Hapus semua baris jasa kecuali yang pertama
        const jasaList = document.getElementById('jasa-list');
        const rows = jasaList.querySelectorAll('tr');
        for (let i = rows.length - 1; i > 0; i--) {
            rows[i].remove();
        }
    }
    
    // Variabel untuk menyimpan nilai sebelumnya
    let previousHanyaJasaValue = null;
    let pendingHanyaJasaValue = null;

    document.getElementById('hanya_jasa').addEventListener('change', function(e) {
        const newValue = e.target.value;
        const currentValue = previousHanyaJasaValue;
        
        // Jika ini adalah perubahan pertama atau tidak ada data yang perlu direset
        if (currentValue === null) {
            previousHanyaJasaValue = newValue;
            updateJasaQtyFields();
            toggleLensaFields();
            return;
        }
        
        // Cek apakah ada data yang sudah diisi
        const hasLensaData = document.getElementById('r_lensa').value || document.getElementById('l_lensa').value;
        const hasJasaData = Array.from(document.querySelectorAll('#jasa-list input[name*="[jasa_id]"]')).some(input => input.value);
        
        if (hasLensaData || hasJasaData) {
            // Simpan nilai yang akan diubah
            pendingHanyaJasaValue = newValue;
            
            // Kembalikan ke nilai sebelumnya sementara
            e.target.value = currentValue;
            
            // Tampilkan modal konfirmasi
            const modal = new bootstrap.Modal(document.getElementById('confirmResetDataModal'));
            modal.show();
        } else {
            // Tidak ada data, langsung update
            previousHanyaJasaValue = newValue;
            updateJasaQtyFields();
            toggleLensaFields();
        }
    });
    
    // Event listener untuk tombol konfirmasi reset data
    document.getElementById('btnConfirmResetData').addEventListener('click', function() {
        // Reset semua data
        resetLensaData();
        resetJasaData();
        
        // Update nilai hanya_jasa ke nilai yang baru
        const hanyaJasaSelect = document.getElementById('hanya_jasa');
        hanyaJasaSelect.value = pendingHanyaJasaValue;
        previousHanyaJasaValue = pendingHanyaJasaValue;
        
        // Update UI sesuai mode baru
        updateJasaQtyFields();
        toggleLensaFields();
        updateJasaActionButtons();
        
        // Update calculations after reset
        if (typeof updateTotalPdfPdn === 'function') updateTotalPdfPdn();
        if (typeof updateMBSCalculation === 'function') updateMBSCalculation();
        if (typeof updateSHPYStatus === 'function') updateSHPYStatus();
        
        // Clear draft dari localStorage
        if (typeof clearDraft === 'function') {
            clearDraft();
        }
        
        // Tutup modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmResetDataModal'));
        modal.hide();
        
        // Save draft dengan data yang sudah direset
        if (typeof saveDraft === 'function') {
            setTimeout(saveDraft, 100);
        }
    });
    
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
        // Inisialisasi nilai previousHanyaJasaValue
        previousHanyaJasaValue = document.getElementById('hanya_jasa').value;
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
        document.getElementById('hanya_jasa').addEventListener(ev, function(){
            updateJasaActionButtons();
            toggleLensaFields();
        });
        document.getElementById('r_lensa').addEventListener(ev, function(){updateJasaActionButtons();updateSphSelectStateSingle('R');});
        document.getElementById('l_lensa').addEventListener(ev, function(){updateJasaActionButtons();updateSphSelectStateSingle('L');});
    });
    
    // Add event listeners for SPH & CYL select changes to update cylinder & axis data
    document.addEventListener('DOMContentLoaded', function() {
        const rSphSelect = document.getElementById('r_spheris');
        const lSphSelect = document.getElementById('l_spheris');
        const rCylSelect = document.getElementById('r_cylinder');
        const lCylSelect = document.getElementById('l_cylinder');
        
        if (rSphSelect) {
            rSphSelect.addEventListener('change', function() {
                updateCylinderSelectStateSingle('R');
                if (typeof saveDraft === 'function') {
                    setTimeout(saveDraft, 100);
                }
            });
        }
        if (lSphSelect) {
            lSphSelect.addEventListener('change', function() {
                updateCylinderSelectStateSingle('L');
                if (typeof saveDraft === 'function') {
                    setTimeout(saveDraft, 100);
                }
            });
        }
        
        // Event listener untuk cylinder select agar update axis
        if (rCylSelect) {
            rCylSelect.addEventListener('change', function() {
                updateAxisSelectStateSingle('R');
            });
        }
        if (lCylSelect) {
            lCylSelect.addEventListener('change', function() {
                updateAxisSelectStateSingle('L');
            });
        }
        updateJasaActionButtons();
    });

</script>

<script>
    // === AUTOSAVE DRAFT TRANSAKSI (LOCALSTORAGE) ===
    const DRAFT_KEY = SESSION_USER_ID ? `transaksi_draft_backend_${SESSION_USER_ID}` : 'transaksi_draft_backend';

    function saveDraft() {
        const form = document.querySelector('form');
        if (!form) return;
        const formData = new FormData(form);
        const obj = {};
        formData.forEach((v, k) => obj[k] = v);
        // Simpan juga jumlah baris jasa
        obj._jasaRowCount = document.querySelectorAll('#jasa-list tr').length;
        // Simpan juga select value manual (karena bisa disabled)
        obj['data_lensa[r_spheris]'] = document.getElementById('r_spheris') ? document.getElementById('r_spheris').value : '';
        obj['data_lensa[l_spheris]'] = document.getElementById('l_spheris') ? document.getElementById('l_spheris').value : '';
        obj['data_lensa[r_axis]'] = document.getElementById('r_axis') ? document.getElementById('r_axis').value : '';
        obj['data_lensa[l_axis]'] = document.getElementById('l_axis') ? document.getElementById('l_axis').value : '';
        obj['data_lensa[r_add]'] = document.getElementById('r_add') ? document.getElementById('r_add').value : '';
        obj['data_lensa[l_add]'] = document.getElementById('l_add') ? document.getElementById('l_add').value : '';
        localStorage.setItem(DRAFT_KEY, JSON.stringify(obj));
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('input', saveDraft);
    }

    // Patch selectLensa dan selectJasa agar autosave
    function selectLensa(kode, nama) {
        if (lensaTargetKodeId && lensaTargetNamaId) {
            document.getElementById(lensaTargetKodeId).value = kode;
            document.getElementById(lensaTargetNamaId).value = nama;
            document.getElementById(lensaTargetKodeId).dispatchEvent(new Event('input'));
            saveDraft();
        }
        let lensaModalEl = document.getElementById('lensaModal');
        if (!window.lensaModalInstance) {
            window.lensaModalInstance = new bootstrap.Modal(lensaModalEl);
        }
        window.lensaModalInstance.hide();
    }

    // Patch: setelah restore draft dari localStorage, trigger setReadonlyFF agar select WA/PT/BVD/FFV/V CODE aktif jika PF/SF
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof setReadonlyFF === 'function') setReadonlyFF();
            updateSphSelectStateSingle('R');
            updateSphSelectStateSingle('L');
        }, 300); // beri delay agar value sudah terisi dari draft dan Select2 sudah terinisialisasi
    });
    // (fungsi selectJasa di atas sudah benar, hapus duplikat ini)

    // Load draft saat page load hanya jika tidak ada error validasi
    window.addEventListener('DOMContentLoaded', function() {
        var errorDiv = document.querySelector('.alert-danger');
        if (!errorDiv) {
            const draft = localStorage.getItem(DRAFT_KEY);
            if (draft) {
                const data = JSON.parse(draft);
                // Generate ulang baris jasa jika perlu
                let jasaRowCount = parseInt(data._jasaRowCount || 1);
                let jasaList = document.getElementById('jasa-list');
                while (jasaList.children.length < jasaRowCount) {
                    addJasa();
                }
                // Isi semua input
                Object.keys(data).forEach(k => {
                    if (k === '_jasaRowCount') return;
                    const els = document.querySelectorAll(`[name="${k}"]`);
                    if (els.length > 1 && els[0].type === 'radio') {
                        els.forEach(function(el) {
                            if (el.value == data[k]) el.checked = true;
                        });
                    } else if (els.length === 1) {
                        const el = els[0];
                        if (el.type === 'radio') {
                            if (el.value == data[k]) el.checked = true;
                        } else {
                            el.value = data[k];
                            // Special handling for dynamic selects
                            if ([
                                'data_lensa[r_spheris]', 'data_lensa[l_spheris]',
                                'data_lensa[r_cylinder]', 'data_lensa[l_cylinder]',
                                'data_lensa[r_axis]', 'data_lensa[l_axis]',
                                'data_lensa[r_add]', 'data_lensa[l_add]'
                            ].includes(k)) {
                                if (window.jQuery && $(el).length) {
                                    $(el).val(data[k]).trigger('change.select2');
                                }
                            }
                        }
                    }
                });
                // After all values are set, ensure axis options are loaded if needed
                setTimeout(function() {
                    // For R
                    if (window.jQuery) {
                        $('#r_spheris').trigger('change.select2');
                        $('#r_cylinder').trigger('change.select2');
                        $('#r_axis').trigger('change.select2');
                        $('#l_spheris').trigger('change.select2');
                        $('#l_cylinder').trigger('change.select2');
                        $('#l_axis').trigger('change.select2');
                    }
                    updateSphSelectStateSingle('R');
                    updateSphSelectStateSingle('L');
                    updateAxisSelectStateSingle('R');
                    updateAxisSelectStateSingle('L');
                    updateAdditionalSelectStateSingle('R');
                    updateAdditionalSelectStateSingle('L');
                }, 200);
                // Pastikan tampilan qty jasa, tombol, dsb ikut terupdate
                if (typeof updateJasaQtyFields === 'function') updateJasaQtyFields();
                if (typeof updateJasaActionButtons === 'function') updateJasaActionButtons();
                if (typeof updateTotalPdfPdn === 'function') updateTotalPdfPdn();
                if (typeof updateMBSCalculation === 'function') updateMBSCalculation();
                if (typeof updateSHPYStatus === 'function') updateSHPYStatus();
                if (typeof toggleLensaFields === 'function') toggleLensaFields();
            }
        }
    });

    function clearDraft() {
        localStorage.removeItem(DRAFT_KEY);
        // Reset all dynamic selects (sph, cyl, axis R/L) and trigger change/select2 for UI
        const fields = [
            'r_spheris', 'r_cylinder', 'r_axis', 'r_add',
            'l_spheris', 'l_cylinder', 'l_axis', 'l_add'
        ];
        fields.forEach(function(fid) {
            var el = document.getElementById(fid);
            if (el) {
                el.value = '';
                if (window.jQuery && $(el).length) {
                    $(el).val('').trigger('change.select2');
                }
            }
        });
        // Optionally, also reset selects to placeholder
        ['r_', 'l_'].forEach(function(row) {
            const sphSelect = document.getElementById(row + 'spheris');
            if (sphSelect) {
                const side = row === 'r_' ? 'R' : 'L';
                sphSelect.innerHTML = '<option value="">Pilih Lensa ' + side + ' dulu</option>';
                sphSelect.setAttribute('disabled', 'disabled');
                if (window.jQuery && $('#' + row + 'spheris').length) {
                    $('#' + row + 'spheris').val('').prop('disabled', true).trigger('change.select2');
                }
            }
            const cylSelect = document.getElementById(row + 'cylinder');
            if (cylSelect) {
                cylSelect.innerHTML = '<option value="">Pilih Lensa & Sph dulu</option>';
                cylSelect.setAttribute('disabled', 'disabled');
                if (window.jQuery && $('#' + row + 'cylinder').length) {
                    $('#' + row + 'cylinder').val('').prop('disabled', true).trigger('change.select2');
                }
            }
            const axisSelect = document.getElementById(row + 'axis');
            if (axisSelect) {
                axisSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
                axisSelect.setAttribute('disabled', 'disabled');
                if (window.jQuery && $('#' + row + 'axis').length) {
                    $('#' + row + 'axis').val('').prop('disabled', true).trigger('change.select2');
                }
            }
            const addSelect = document.getElementById(row + 'additional');
            if (addSelect) {
                addSelect.innerHTML = '<option value="">Pilih Cylinder dulu</option>';
                addSelect.setAttribute('disabled', 'disabled');
                if (window.jQuery && $('#' + row + 'additional').length) {
                    $('#' + row + 'additional').val('').prop('disabled', true).trigger('change.select2');
                }
            }
        });
    }

    // === VALIDASI KODE BRAND DAN JENIS LENSA ===
    function showErrorModal(message) {
        document.getElementById('errorModalBody').innerText = message;
        if (!window.errorModalInstance) {
            let modalEl = document.getElementById('errorModal');
            window.errorModalInstance = new bootstrap.Modal(modalEl);
        }
        window.errorModalInstance.show();
    }

    function validateJenisLensaSelection() {
        const kodeBrand = document.getElementById('kd_brand').value;
        const jenisLensa = document.getElementById('jenis_lensa');
        
        if (!kodeBrand) {
            // Reset pilihan jenis lensa jika kode brand belum dipilih
            jenisLensa.value = '';
            showErrorModal('Pilih kode brand terlebih dahulu sebelum memilih jenis lensa!');
            return false;
        }
        return true;
    }

    // Event listener untuk jenis lensa
    document.addEventListener('DOMContentLoaded', function() {
        const jenisLensaSelect = document.getElementById('jenis_lensa');
        const kodeBrandSelect = document.getElementById('kd_brand');
        let pendingResetTarget = null;

        // Fungsi reset semua field lensa R/L
        function resetAllLensaFields() {
            // Reset R
            clearLensa('r_lensa','r_nama_lensa');
            // Reset L
            clearLensa('l_lensa','l_nama_lensa');
        }

        // Helper cek apakah lensa R/L sudah dipilih
        function isLensaDipilih() {
            const rLensa = document.getElementById('r_lensa').value;
            const lLensa = document.getElementById('l_lensa').value;
            return (rLensa && rLensa.trim() !== '') || (lLensa && lLensa.trim() !== '');
        }

        // Event listener untuk jenis lensa - validasi saat memilih
        jenisLensaSelect.addEventListener('change', function(e) {
            if (this.value !== '') {
                if (!validateJenisLensaSelection()) {
                    this.value = '';
                    return;
                }
            }
            if (isLensaDipilih()) {
                // Tampilkan modal konfirmasi
                pendingResetTarget = { el: this, value: this.value, type: 'jenis' };
                // Kembalikan ke value sebelumnya (biar tidak langsung berubah)
                this.value = this.getAttribute('data-prev') || '';
                let modalEl = document.getElementById('confirmResetLensaModal');
                if (!window.confirmResetLensaInstance) {
                    window.confirmResetLensaInstance = new bootstrap.Modal(modalEl);
                }
                window.confirmResetLensaInstance.show();
            }
            this.setAttribute('data-prev', this.value);
        });

        // Event listener untuk kode brand - reset jenis lensa jika kode brand diubah
        kodeBrandSelect.addEventListener('change', function(e) {
            if (this.value === '') {
                jenisLensaSelect.value = '';
            }
            if (isLensaDipilih()) {
                // Tampilkan modal konfirmasi
                pendingResetTarget = { el: this, value: this.value, type: 'brand' };
                // Kembalikan ke value sebelumnya (biar tidak langsung berubah)
                this.value = this.getAttribute('data-prev') || '';
                let modalEl = document.getElementById('confirmResetLensaModal');
                if (!window.confirmResetLensaInstance) {
                    window.confirmResetLensaInstance = new bootstrap.Modal(modalEl);
                }
                window.confirmResetLensaInstance.show();
            }
            this.setAttribute('data-prev', this.value);
        });

        // Tambahkan event listener untuk mencegah focus jika kode brand belum dipilih
        jenisLensaSelect.addEventListener('focus', function() {
            const kodeBrand = document.getElementById('kd_brand').value;
            if (!kodeBrand) {
                this.blur(); // Hilangkan focus
                showErrorModal('Pilih kode brand terlebih dahulu sebelum memilih jenis lensa!');
            }
        });

        // Handler konfirmasi modal
        document.getElementById('btnConfirmResetLensa').addEventListener('click', function() {
            if (pendingResetTarget) {
                resetAllLensaFields();
                // Lanjutkan perubahan
                if (pendingResetTarget.type === 'brand') {
                    // Tidak perlu reset jenis lensa jika ganti brand
                } else if (pendingResetTarget.type === 'jenis') {
                    // Tidak perlu reset kode brand jika ganti jenis lensa
                }
                pendingResetTarget.el.value = pendingResetTarget.value;
                if (pendingResetTarget.el.id === 'kd_brand') {
                    // Jika brand diubah, reset jenis lensa juga
                    jenisLensaSelect.value = '';
                }
                // Trigger event change agar logic lain tetap jalan
                pendingResetTarget.el.dispatchEvent(new Event('change'));
            }
            // Tutup modal
            let modalEl = document.getElementById('confirmResetLensaModal');
            if (!window.confirmResetLensaInstance) {
                window.confirmResetLensaInstance = new bootstrap.Modal(modalEl);
            }
            window.confirmResetLensaInstance.hide();
            pendingResetTarget = null;
        });
    });

    // Inisialisasi datepicker dan perhitungan usia
    $(document).ready(function() {
        // Inisialisasi datepicker
        $('#tanggal-lahir').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true
        });
        
        // Setup event listener untuk perhitungan usia setelah datepicker diinisialisasi
        var tglLahirInput = document.getElementById('tanggal-lahir');
        var usiaInput = document.getElementById('usia_input');
        
        function calculateAge() {
            if (tglLahirInput.value) {
                // Parse tanggal dengan format dd-mm-yyyy
                var dateParts = tglLahirInput.value.split('-');
                if (dateParts.length === 3) {
                    var day = parseInt(dateParts[0]);
                    var month = parseInt(dateParts[1]) - 1; // Month is 0-indexed
                    var year = parseInt(dateParts[2]);
                    
                    var today = new Date();
                    var birthDate = new Date(year, month, day);
                    var age = today.getFullYear() - birthDate.getFullYear();
                    var m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    usiaInput.value = age >= 0 ? age : '0';
                } else {
                    usiaInput.value = '0';
                }
            } else {
                usiaInput.value = '0';
            }
        }
        
        if (tglLahirInput && usiaInput) {
            // Event untuk datepicker
            $('#tanggal-lahir').on('changeDate', calculateAge);
            
            // Event untuk input manual
            $(tglLahirInput).on('change', calculateAge);
            $(tglLahirInput).on('blur', calculateAge);
        }
    });
</script>


<script>  
    // SIMPLIFIED: handleSimpleSubmit hanya untuk konfirmasi
    function handleSimpleSubmit(event) {
        console.log('🔧 handleSimpleSubmit called...');
        
        // Cek konfirmasi (logic konfirmasi tetap sama)
        if (!isConfirmedSubmit) {
            event.preventDefault();
            console.log('⚠️ Not confirmed yet - preventing submit');
            const confirmSubmitModal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
            confirmSubmitModal.show();
            return false;
        }
        
        // Reset flag setelah submit berhasil
        isConfirmedSubmit = false;
        console.log('✅ Submit confirmed - proceeding with form submission');
        
        // Log final data yang akan dikirim
        const formData = new FormData(document.getElementById('mainForm'));
        console.log('� Final data being submitted:');
        const selectFields = ['r_spheris', 'r_cylinder', 'r_axis', 'r_add', 'r_prisma', 'r_base', 
                              'l_spheris', 'l_cylinder', 'l_axis', 'l_add', 'l_prisma', 'l_base'];
        selectFields.forEach(field => {
            const value = formData.get(`data_lensa[${field}]`) || '';
            console.log(`data_lensa[${field}] = "${value}"`);
        });
        
        // Lanjutkan submit (return true)
        return true;
    }

    // === MODAL KONFIRMASI SUBMIT TRANSAKSI ===
    /* Action Form Submit */
    $("#formTambah").unbind('submit').on('submit', function() {
        // dialog_submit('Notification',"Simpan !!");
        const confirmSubmitModal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
        confirmSubmitModal.show();

        $('#btn-submit').click(function() {
            document.getElementById('formTambah').submit();
        });

        return false;
    });
    
</script>

<?= $this->endSection() ?>