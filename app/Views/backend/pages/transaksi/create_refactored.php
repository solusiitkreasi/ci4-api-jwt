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

    /* Custom styling for disabled select2 */
    .select2-container--disabled .select2-selection {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        opacity: 0.6;
    }
    
    .select2-container--disabled .select2-selection__placeholder {
        color: #6c757d !important;
    }
    
    /* Loading state untuk select2 */
    .select2-container.loading .select2-selection {
        background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
    }
    
    .select2-container.loading .select2-selection::after {
        content: "⏳";
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

<script>
    window.addEventListener('DOMContentLoaded', function() {
        // Pre-check jQuery availability
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is required but not loaded');
            return;
        }
        console.log('🎯 Initializing Transaksi Create Page...');
    });
</script>

<!-- HEADER SECTION -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Transaksi Baru</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('backend/transaksi') ?>">Transaksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
        </ol>
    </nav>
</div>

<!-- MAIN FORM -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-plus-circle me-2"></i>Form Transaksi Baru
                </h6>
            </div>
            <div class="card-body">
                
        <form id="formTambah" method="POST" action="<?= base_url('backend/transaksi/create') ?>"> 
            
            <!-- Customer Info Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label"><strong>Nomor Nota *</strong></label>
                        <input type="text" class="form-control" name="data_lensa[no_po]" 
                               placeholder="Masukkan nomor nota" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label"><strong>Tanggal Transaksi *</strong></label>
                        <input type="date" class="form-control" name="data_lensa[tanggal_transaksi]" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label"><strong>Nama Pasien *</strong></label>
                        <input type="text" class="form-control" name="data_lensa[nama_pasien]" 
                               placeholder="Nama lengkap pasien" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label"><strong>Tanggal Lahir</strong></label>
                        <input type="date" class="form-control" id="tanggal-lahir" 
                               name="data_lensa[tanggal_lahir]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label"><strong>Usia</strong></label>
                        <input type="number" class="form-control" id="usia_input" 
                               name="data_lensa[usia]" readonly>
                    </div>
                </div>
            </div>

            <!-- Lensa Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-eye me-2"></i>Pilih Lensa
                    </h5>
                    <div id="lensa-table-container">
                        <!-- DataTable will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Prescription Data (R/L) -->
            <div class="row mb-4" id="prescription-section" style="display: none;">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-prescription me-2"></i>Data Resep
                    </h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Eye</th>
                                    <th>SPH</th>
                                    <th>CYL</th>
                                    <th>AXIS</th>
                                    <th>ADD</th>
                                    <th>PRISMA</th>
                                    <th>BASE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Right Eye -->
                                <tr>
                                    <td><strong>R</strong></td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_spheris" 
                                                name="data_lensa[r_spheris]" data-type="spheris" data-side="R" disabled>
                                            <option value="">Pilih SPH</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_cylinder" 
                                                name="data_lensa[r_cylinder]" data-type="cylinder" data-side="R" disabled>
                                            <option value="">Pilih CYL</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_axis" 
                                                name="data_lensa[r_axis]" data-type="axis" data-side="R" disabled>
                                            <option value="">Pilih AXIS</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_add" 
                                                name="data_lensa[r_add]" data-type="add" data-side="R" disabled>
                                            <option value="">Pilih ADD</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_prisma" 
                                                name="data_lensa[r_prisma]" data-type="prisma" data-side="R" disabled>
                                            <option value="">Pilih PRISMA</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="r_base" 
                                                name="data_lensa[r_base]" data-type="base" data-side="R" disabled>
                                            <option value="">Pilih BASE</option>
                                        </select>
                                    </td>
                                </tr>
                                <!-- Left Eye -->
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_spheris" 
                                                name="data_lensa[l_spheris]" data-type="spheris" data-side="L" disabled>
                                            <option value="">Pilih SPH</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_cylinder" 
                                                name="data_lensa[l_cylinder]" data-type="cylinder" data-side="L" disabled>
                                            <option value="">Pilih CYL</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_axis" 
                                                name="data_lensa[l_axis]" data-type="axis" data-side="L" disabled>
                                            <option value="">Pilih AXIS</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_add" 
                                                name="data_lensa[l_add]" data-type="add" data-side="L" disabled>
                                            <option value="">Pilih ADD</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_prisma" 
                                                name="data_lensa[l_prisma]" data-type="prisma" data-side="L" disabled>
                                            <option value="">Pilih PRISMA</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select prescription-field" id="l_base" 
                                                name="data_lensa[l_base]" data-type="base" data-side="L" disabled>
                                            <option value="">Pilih BASE</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Service Selection -->
            <div class="row mb-4" id="service-section" style="display: none;">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-tools me-2"></i>Pilih Jasa
                    </h5>
                    <div id="jasa-table-container">
                        <!-- Service table will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('backend/transaksi') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnSubmitTransaksi">
                            <i class="fas fa-save me-1"></i>Simpan Transaksi
                        </button>
                    </div>
                </div>
            </div>

        </form>
        
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Submit Transaksi -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="confirmSubmitLabel">
            <i class="fas fa-check-circle me-2"></i><b>Konfirmasi Submit Transaksi</b>
        </h5>
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
        <button type="submit" class="btn btn-success" id="btn-submit">Ya, Proses Transaksi</button>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * TRANSAKSI CREATE PAGE - REFACTORED VERSION
 * Following KISS principles and clean code best practices
 * Author: Senior Developer
 * Date: <?= date('Y-m-d') ?>
 */

// =============================================================================
// CONFIGURATION & CONSTANTS
// =============================================================================
const CONFIG = {
    baseUrl: '<?= base_url() ?>',
    endpoints: {
        spheris: 'backend/transaksi/getspheris',
        cylinder: 'backend/transaksi/getcylinder',
        axis: 'backend/transaksi/getaxis',
        additional: 'backend/transaksi/getadditional',
        prisma: 'backend/transaksi/getprisma',
        base: 'backend/transaksi/getbase'
    },
    select2: {
        width: '100%',
        theme: 'bootstrap-5'
    },
    loadingDelay: 500, // minimum loading time for UX
    debug: true
};

const FIELD_CONFIGS = {
    spheris: { placeholder: 'Pilih SPH', endpoint: 'spheris', loadingText: '🔄 Loading SPH...', dataKey: 'kode_spheris' },
    cylinder: { placeholder: 'Pilih CYL', endpoint: 'cylinder', loadingText: '🔄 Loading CYL...', dataKey: 'kode_cylinder' },
    axis: { placeholder: 'Pilih AXIS', endpoint: 'axis', loadingText: '🔄 Loading AXIS...', dataKey: 'kode_axis' },
    add: { placeholder: 'Pilih ADD', endpoint: 'additional', loadingText: '🔄 Loading ADD...', dataKey: 'kode_additional' },
    prisma: { placeholder: 'Pilih PRISMA', endpoint: 'prisma', loadingText: '🔄 Loading PRISMA...', dataKey: 'kode_prisma' },
    base: { placeholder: 'Pilih BASE', endpoint: 'base', loadingText: '🔄 Loading BASE...', dataKey: 'kode_base' }
};

// =============================================================================
// UTILITIES
// =============================================================================
const Utils = {
    log: (message, data = null) => {
        if (CONFIG.debug) {
            console.log(`🎯 ${message}`, data || '');
        }
    },
    
    error: (message, error = null) => {
        console.error(`❌ ${message}`, error || '');
    },
    
    buildUrl: (endpoint, params = {}) => {
        const url = new URL(`${CONFIG.baseUrl}${endpoint}`);
        Object.keys(params).forEach(key => {
            if (params[key]) url.searchParams.append(key, params[key]);
        });
        return url.toString();
    },
    
    delay: (ms) => new Promise(resolve => setTimeout(resolve, ms)),
    
    getElementById: (id) => {
        const element = document.getElementById(id);
        if (!element) Utils.error(`Element with id '${id}' not found`);
        return element;
    }
};

// =============================================================================
// SELECT2 MANAGER
// =============================================================================
const Select2Manager = {
    initialize: () => {
        Utils.log('Initializing Select2 fields...');
        
        $('.prescription-field').each(function() {
            const $this = $(this);
            const fieldType = $this.data('type');
            const config = FIELD_CONFIGS[fieldType];
            
            if (config) {
                $this.select2({
                    ...CONFIG.select2,
                    placeholder: config.placeholder,
                    allowClear: true
                });
                Utils.log(`Select2 initialized for: ${this.id}`);
            }
        });
    },
    
    enable: (selectId) => {
        const $select = $(`#${selectId}`);
        $select.prop('disabled', false);
        Utils.log(`Select2 enabled: ${selectId}`);
    },
    
    disable: (selectId) => {
        const $select = $(`#${selectId}`);
        $select.prop('disabled', true).val('').trigger('change');
        Utils.log(`Select2 disabled: ${selectId}`);
    },
    
    setLoading: (selectId, isLoading = true) => {
        const $container = $(`#${selectId}`).next('.select2-container');
        if (isLoading) {
            $container.addClass('loading');
        } else {
            $container.removeClass('loading');
        }
    },
    
    populateOptions: (selectId, data, config) => {
        const $select = $(`#${selectId}`);
        $select.empty().append(`<option value="">${config.placeholder}</option>`);
        
        if (data && data.length > 0) {
            data.forEach(item => {
                const value = item[config.dataKey];
                $select.append(`<option value="${value}">${value}</option>`);
            });
            Utils.log(`Populated ${data.length} options for: ${selectId}`);
        } else {
            $select.append(`<option value="">Tidak ada data ditemukan</option>`);
            Utils.log(`No data found for: ${selectId}`);
        }
        
        $select.trigger('change');
    }
};

// =============================================================================
// DATA LOADER - GENERIC FOR ALL SELECT FIELDS
// =============================================================================
const DataLoader = {
    async loadSelectData(selectId, fieldType, params = {}) {
        const config = FIELD_CONFIGS[fieldType];
        if (!config) {
            Utils.error(`Unknown field type: ${fieldType}`);
            return;
        }
        
        const startTime = Date.now();
        Utils.log(`Loading data for ${selectId} (${fieldType})...`);
        
        try {
            // Set loading state
            Select2Manager.setLoading(selectId, true);
            const $select = $(`#${selectId}`);
            $select.html(`<option value="">${config.loadingText}</option>`);
            
            // Build URL with parameters
            const url = Utils.buildUrl(CONFIG.endpoints[config.endpoint], params);
            
            // Fetch data
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Ensure minimum loading time for better UX
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, CONFIG.loadingDelay - elapsedTime);
            await Utils.delay(remainingTime);
            
            // Populate select options
            Select2Manager.populateOptions(selectId, data, config);
            
        } catch (error) {
            Utils.error(`Failed to load data for ${selectId}:`, error);
            const $select = $(`#${selectId}`);
            $select.html(`<option value="">❌ Error loading data</option>`);
        } finally {
            // Remove loading state
            Select2Manager.setLoading(selectId, false);
        }
    }
};

// =============================================================================
// PRESCRIPTION MANAGER
// =============================================================================
const PrescriptionManager = {
    selectedLensaCode: null,
    
    initialize: () => {
        Utils.log('Initializing Prescription Manager...');
        
        // Setup field dependencies and event listeners
        $('.prescription-field').on('select2:select', function() {
            const $this = $(this);
            const fieldType = $this.data('type');
            const side = $this.data('side');
            const value = $this.val();
            
            Utils.log(`Field selected: ${this.id} = ${value}`);
            PrescriptionManager.handleFieldChange(fieldType, side, value);
        });
    },
    
    handleFieldChange: (fieldType, side, value) => {
        switch (fieldType) {
            case 'spheris':
                PrescriptionManager.onSphChange(side, value);
                break;
            case 'cylinder':
                PrescriptionManager.onCylChange(side, value);
                break;
            // Add other field dependencies as needed
        }
    },
    
    onSphChange: (side, sphValue) => {
        if (sphValue && PrescriptionManager.selectedLensaCode) {
            const cylSelectId = `${side.toLowerCase()}_cylinder`;
            DataLoader.loadSelectData(cylSelectId, 'cylinder', {
                kode_lensa: PrescriptionManager.selectedLensaCode,
                kode_spheris: sphValue
            });
        }
    },
    
    onCylChange: (side, cylValue) => {
        // Enable dependent fields when cylinder is selected
        const dependentFields = ['axis', 'add', 'prisma', 'base'];
        dependentFields.forEach(fieldType => {
            const selectId = `${side.toLowerCase()}_${fieldType}`;
            Select2Manager.enable(selectId);
            DataLoader.loadSelectData(selectId, fieldType);
        });
    },
    
    enableForLensa: (lensaCode) => {
        Utils.log(`Enabling prescription fields for lensa: ${lensaCode}`);
        PrescriptionManager.selectedLensaCode = lensaCode;
        
        // Enable SPH fields first
        ['R', 'L'].forEach(side => {
            const sphSelectId = `${side.toLowerCase()}_spheris`;
            Select2Manager.enable(sphSelectId);
            DataLoader.loadSelectData(sphSelectId, 'spheris');
        });
        
        // Show prescription section
        document.getElementById('prescription-section').style.display = 'block';
    },
    
    disable: () => {
        Utils.log('Disabling all prescription fields...');
        $('.prescription-field').each(function() {
            Select2Manager.disable(this.id);
        });
        document.getElementById('prescription-section').style.display = 'none';
    }
};

// =============================================================================
// FORM MANAGER
// =============================================================================
const FormManager = {
    initialize: () => {
        Utils.log('Initializing Form Manager...');
        
        // Form submission with confirmation modal
        $("#formTambah").on('submit', function(e) {
            e.preventDefault();
            Utils.log('Form submitted - showing confirmation modal...');
            
            const modal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
            modal.show();
        });
        
        // Confirmation button in modal
        $('#btn-submit').on('click', function() {
            Utils.log('User confirmed submission - submitting form...');
            document.getElementById('formTambah').submit();
        });
        
        // Age calculation
        FormManager.setupAgeCalculation();
    },
    
    setupAgeCalculation: () => {
        const tglLahirInput = Utils.getElementById('tanggal-lahir');
        const usiaInput = Utils.getElementById('usia_input');
        
        if (tglLahirInput && usiaInput) {
            tglLahirInput.addEventListener('change', function() {
                if (this.value) {
                    const birthDate = new Date(this.value);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    
                    usiaInput.value = age;
                    Utils.log(`Age calculated: ${age} years`);
                }
            });
        }
    }
};

// =============================================================================
// LENSA TABLE MANAGER
// =============================================================================
const LensaTableManager = {
    dataTable: null,
    
    initialize: () => {
        Utils.log('Initializing Lensa Table...');
        
        const tableHtml = `
            <table id="lensa-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Aksi</th>
                        <th>Kode Lensa</th>
                        <th>Brand</th>
                        <th>Nama Lensa</th>
                        <th>Jenis</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `;
        
        document.getElementById('lensa-table-container').innerHTML = tableHtml;
        
        LensaTableManager.initDataTable();
    },
    
    initDataTable: () => {
        LensaTableManager.dataTable = $('#lensa-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: Utils.buildUrl('backend/transaksi/getlensa'),
                type: 'POST'
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<button type="button" class="btn btn-sm btn-primary select-lensa-btn" 
                                       data-kode="${row.kode_lensa}" data-nama="${row.nama_lensa}">
                                   <i class="fas fa-check me-1"></i>Pilih
                               </button>`;
                    }
                },
                { data: 'kode_lensa' },
                { data: 'brand_name' },
                { data: 'nama_lensa' },
                { data: 'jenis_lensa' },
                { 
                    data: 'harga_modal',
                    render: function(data) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(data);
                    }
                }
            ],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
        
        // Handle lensa selection
        $('#lensa-table').on('click', '.select-lensa-btn', function() {
            const kodeMedia = $(this).data('kode');
            const namaLensa = $(this).data('nama');
            
            Utils.log(`Lensa selected: ${kodeMedia} - ${namaLensa}`);
            
            // Enable prescription fields
            PrescriptionManager.enableForLensa(kodeMedia);
            
            // Show service section
            document.getElementById('service-section').style.display = 'block';
            
            // Update button text to show selection
            $(this).html(`<i class="fas fa-check-circle me-1"></i>Terpilih`).addClass('btn-success').removeClass('btn-primary');
            
            // Disable other selection buttons
            $('.select-lensa-btn').not(this).prop('disabled', true);
        });
    }
};

// =============================================================================
// MAIN APPLICATION INITIALIZATION
// =============================================================================
document.addEventListener('DOMContentLoaded', function() {
    Utils.log('='.repeat(60));
    Utils.log('TRANSAKSI CREATE PAGE - REFACTORED VERSION');
    Utils.log('='.repeat(60));
    
    try {
        // Initialize all managers in order
        Select2Manager.initialize();
        PrescriptionManager.initialize();
        FormManager.initialize();
        LensaTableManager.initialize();
        
        Utils.log('✅ All managers initialized successfully');
        
    } catch (error) {
        Utils.error('Failed to initialize application:', error);
    }
});

// =============================================================================
// CLEANUP & ERROR HANDLING
// =============================================================================
window.addEventListener('beforeunload', function() {
    // Cleanup DataTables
    if (LensaTableManager.dataTable) {
        LensaTableManager.dataTable.destroy();
    }
});

// Global error handler
window.addEventListener('error', function(event) {
    Utils.error('Global error:', event.error);
});

</script>

<?= $this->endSection() ?>
