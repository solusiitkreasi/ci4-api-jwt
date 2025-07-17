<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

<!-- Title and Top Buttons Start -->
<div class="page-title-container">
    <div class="row">
        <!-- Title Start -->
        <div class="col-12 col-md-7">
            <h1 class="mb-0 pb-0 display-4" id="title"><?= $title ?></h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
                <ol class="breadcrumb pt-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                </ol>
            </nav>
        </div>
        <!-- Title End -->
    </div>
</div>
<!-- Title and Top Buttons End -->

<!-- Content Start -->
<div class="row">
    <div class="col-12">
        <div class="card mb-5">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="small-title">Akses Dokumentasi API</h2>
                        </div>
                        <p class="text-muted mb-4">
                            Pilih format dokumentasi API yang ingin Anda akses. Setiap format memiliki keunggulan tersendiri untuk kebutuhan yang berbeda.
                        </p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <?php foreach ($api_docs_links as $link): ?>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i data-acorn-icon="<?= $link['icon'] ?>" class="icon text-<?= $link['color'] ?>" data-acorn-size="40"></i>
                                </div>
                                <h5 class="card-title"><?= $link['title'] ?></h5>
                                <p class="card-text text-muted small"><?= $link['description'] ?></p>
                                <a href="<?= $link['url'] ?>" 
                                   target="_blank" 
                                   class="btn btn-<?= $link['color'] ?> btn-sm">
                                    <i data-acorn-icon="external-link" data-acorn-size="14"></i>
                                    Buka
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <hr class="my-5">
                
                <div class="row">
                    <div class="col-12">
                        <h3 class="small-title mb-3">Informasi API</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Base URL</h6>
                                <code><?= base_url('/api/v1') ?></code>
                                
                                <h6 class="mt-3">Authentication</h6>
                                <ul class="list-unstyled">
                                    <li><strong>JWT Token:</strong> Bearer Token untuk endpoint yang memerlukan autentikasi</li>
                                    <li><strong>API Key:</strong> X-API-KEY header untuk endpoint public</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Response Format</h6>
                                <p class="text-muted small">Semua response menggunakan format JSON dengan struktur standar:</p>
                                <pre class="bg-light p-3 rounded small"><code>{
  "success": boolean,
  "message": "string",
  "data": object|array,
  "timestamp": "ISO 8601 datetime"
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <h6 class="alert-heading">
                                <i data-acorn-icon="info" data-acorn-size="16"></i>
                                Tips Penggunaan
                            </h6>
                            <ul class="mb-0">
                                <li><strong>Swagger UI:</strong> Terbaik untuk testing dan eksplorasi API secara interaktif</li>
                                <li><strong>Markdown:</strong> Cocok untuk dokumentasi yang mudah dibaca dan dipahami</li>
                                <li><strong>Postman Collection:</strong> Import ke Postman untuk testing komprehensif</li>
                                <li><strong>OpenAPI JSON:</strong> Untuk integrasi dengan tools development lainnya</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
<!-- Content End -->

<?= $this->endSection() ?>

<?= $this->section('js_after') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize icons
        if (typeof Acorn !== 'undefined') {
            Acorn.init();
        }
    });
</script>
<?= $this->endSection() ?>
