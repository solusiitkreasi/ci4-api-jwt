<?= $this->extend('backend/layouts/template') ?>

<?= $this->section('content') ?>

    <!-- Title and Top Buttons Start -->
    <div class="page-title-container">
        <div class="row">
            <!-- Title Start -->
            <div class="col-12 col-md-7">
                <a class="muted-link pb-2 d-inline-block hidden" href="#">
                    <span class="align-middle lh-1 text-small">&nbsp; </span>
                </a>
                <h1 class="mb-0 pb-0 display-4 text-black font-weight-bold" id="title">Welcome, <b><?= strtoupper(session()->get('name')); ?></b> ! <br>
            </div>
            <!-- Title End -->
        </div>
    </div>
    <!-- Title and Top Buttons End -->

    <hr>

    <!-- Stats Start -->
    <div class="mb-5">
        <!-- Summary Cards -->
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-default bg-opacity-10 text-primary rounded-circle icon-stat">
                                    <i class="bi bi-receipt" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?= number_format($dashboard_stats['total_transactions']) ?></h5>
                                <p class="text-muted mb-0 small">Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-default bg-opacity-10 text-info rounded-circle icon-stat">
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?= number_format($dashboard_stats['completed_transactions']) ?></h5>
                                <p class="text-muted mb-0 small">Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-default bg-opacity-10 text-warning rounded-circle icon-stat">
                                    <i class="bi bi-clock" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?= number_format($dashboard_stats['pending_transactions']) ?></h5>
                                <p class="text-muted mb-0 small">Dalam Proses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-<?= $dashboard_stats['monthly_growth'] >= 0 ? 'success' : 'danger' ?> bg-opacity-10 text-<?= $dashboard_stats['monthly_growth'] >= 0 ? 'success' : 'danger' ?> rounded-circle icon-stat">
                                    <i class="bi bi-graph-<?= $dashboard_stats['monthly_growth'] >= 0 ? 'up' : 'down' ?>-arrow" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?= $dashboard_stats['monthly_growth'] >= 0 ? '+' : '' ?><?= number_format($dashboard_stats['monthly_growth'], 1) ?>%</h5>
                                <p class="text-muted mb-0 small">Growth Bulanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <!-- Charts Section -->
            <div class="col-12 mb-4">
                <div class="row g-2">
                    <!-- Monthly Chart -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Transaksi Bulanan <?= date('Y') ?></h5>
                                    <select id="yearSelect" class="form-select form-select-sm" style="width: auto;">
                                        <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                            <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <div id="monthlyChartLoading" class="chart-loading d-none">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <small class="text-muted">Memuat data...</small>
                                        </div>
                                    </div>
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Yearly Chart -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Transaksi Tahunan (5 Tahun Terakhir)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="yearlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions and Master Data -->
            <div class="col-md-8">
                <div class="row">
                    <section class="scroll-section">
                        <div class="card">
                            <div class="card-body scroll-out mb-n2">
                                <h3>Recent Transaksi</h3><hr>

                                <div class="scroll sh-50">
                                    <?php if(!empty($recent_transactions)): ?>
                                        <?php foreach($recent_transactions as $transaction): ?>
                                            <div class="card mb-2 sh-15 sh-md-6 recent-transaction-item">
                                                <div class="card-body pt-0 pb-0 h-100">
                                                    <div class="row g-0 h-100 align-content-center">
                                                        <div class="col-10 col-md-4 d-flex align-items-center mb-3 mb-md-0 h-md-100">
                                                            <a href="<?= base_url('backend/transaksi/detail/' . $transaction['id']) ?>" class="body-link stretched-link">
                                                                #<b><?= esc($transaction['no_po']) ?></b>
                                                            </a>
                                                        </div>
                                                        <div class="col-2 col-md-3 d-flex align-items-center text-black mb-1 mb-md-0 justify-content-end justify-content-md-start">
                                                            <?php if($transaction['is_proses_tol'] == 1): ?>
                                                                <span class="badge bg-success me-1">Selesai</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-info me-1">Proses</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-12 col-md-2 d-flex align-items-center mb-1 mb-md-0">
                                                            <span>
                                                                <b><?= esc($transaction['kode_customer']) ?></b>
                                                            </span>
                                                        </div>
                                                        <div class="col-12 col-md-3 d-flex align-items-center justify-content-md-end mb-1 mb-md-0">
                                                            <small class="text-muted">
                                                                <?= date('d M Y H:i', strtotime($transaction['wkt_input'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">Belum ada transaksi</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <?php $filterRole = session()->get('roles');
                if (is_array($filterRole) ? in_array('Super Admin', $filterRole) : $filterRole == 'Super Admin') { ?>
                <div class="col-md-4">
                    <div class="card w-100 sh-60 mb-5">
                        <img src="<?= base_url('assets/admin/') ?>img/banner/cta-square-4.webp" class="card-img h-100" alt="card image" />
                        <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                            <h3 class="text-title mb-2">Data Master</h3>
                            <div class="d-flex flex-column h-100 justify-content-between align-items-start">
                                <div>
                                    <a href="<?= base_url('backend/transaksi') ?>">
                                        <div class="cta-1 text-primary mb-0">
                                            Kelola Transaksi
                                        </div>
                                        <div class="lh-1-25 mb-0 text-black">Manajemen Data Transaksi</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- Stats End -->

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }
    
    .card-header .form-select {
        min-width: 100px;
    }
    
    .scroll-section .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .recent-transaction-item {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    
    .recent-transaction-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card {
        transition: transform 0.2s ease-in-out;
    }
    
    .summary-card:hover {
        transform: translateY(-2px);
    }
    
    .chart-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }
    
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .icon-stat {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    @media (max-width: 576px) {
        .icon-stat {
            width: 2.5rem;
            height: 2.5rem;
        }
        
        .icon-stat i {
            font-size: 1.2rem !important;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial chart data from server
        const monthlyData = <?= json_encode($monthly_stats) ?>;
        const yearlyData = <?= json_encode($yearly_stats) ?>;
        
        // Chart color scheme
        const colors = {
            primary: {
                bg: 'rgba(54, 162, 235, 0.6)',
                border: 'rgba(54, 162, 235, 1)'
            },
            success: {
                bg: 'rgba(75, 192, 192, 0.6)',
                border: 'rgba(75, 192, 192, 1)'
            },
            danger: {
                bg: 'rgba(255, 99, 132, 0.6)',
                border: 'rgba(255, 99, 132, 1)'
            },
            warning: {
                bg: 'rgba(255, 206, 86, 0.6)',
                border: 'rgba(255, 206, 86, 1)'
            }
        };
        
        // Chart configurations
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Math.floor(value) === value ? value : '';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y} transaksi`;
                        }
                    }
                }
            }
        };

        // Monthly Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        let monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [
                    {
                        label: 'Total Transaksi',
                        data: monthlyData.map(item => item.total_transactions),
                        backgroundColor: colors.primary.bg,
                        borderColor: colors.primary.border,
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Transaksi Selesai',
                        data: monthlyData.map(item => item.completed_transactions),
                        backgroundColor: colors.success.bg,
                        borderColor: colors.success.border,
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: chartOptions
        });

        // Yearly Chart
        const yearlyCtx = document.getElementById('yearlyChart').getContext('2d');
        const yearlyChart = new Chart(yearlyCtx, {
            type: 'bar',
            data: {
                labels: yearlyData.map(item => item.year),
                datasets: [
                    {
                        label: 'Total Transaksi',
                        data: yearlyData.map(item => item.total_transactions),
                        backgroundColor: colors.danger.bg,
                        borderColor: colors.danger.border,
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Transaksi Selesai',
                        data: yearlyData.map(item => item.completed_transactions),
                        backgroundColor: colors.warning.bg,
                        borderColor: colors.warning.border,
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: chartOptions
        });

        // Year selector change handler with loading state
        const yearSelect = document.getElementById('yearSelect');
        const loadingIndicator = document.getElementById('monthlyChartLoading');
        
        yearSelect.addEventListener('change', function() {
            const selectedYear = this.value;
            const monthlyCard = document.querySelector('#monthlyChart').closest('.card');
            const chartCanvas = document.getElementById('monthlyChart');
            
            // Show loading state
            loadingIndicator.classList.remove('d-none');
            chartCanvas.style.opacity = '0.3';
            yearSelect.disabled = true;
            
            // Fetch new data for selected year
            fetch(`<?= base_url('backend/dashboard/getChartData') ?>?type=monthly&year=${selectedYear}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Update chart data with animation
                    monthlyChart.data.labels = data.map(item => item.month_name);
                    monthlyChart.data.datasets[0].data = data.map(item => item.total_transactions);
                    monthlyChart.data.datasets[1].data = data.map(item => item.completed_transactions);
                    
                    // Animate the update
                    monthlyChart.update('active');
                    
                    // Update chart title
                    monthlyCard.querySelector('h5').textContent = `Transaksi Bulanan ${selectedYear}`;
                    
                    // Hide loading state with delay for smooth transition
                    setTimeout(() => {
                        loadingIndicator.classList.add('d-none');
                        chartCanvas.style.opacity = '1';
                        yearSelect.disabled = false;
                    }, 300);
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    
                    // Show error toast notification
                    showErrorToast('Gagal memuat data chart. Silakan coba lagi.');
                    
                    // Hide loading state
                    loadingIndicator.classList.add('d-none');
                    chartCanvas.style.opacity = '1';
                    yearSelect.disabled = false;
                });
        });
        
        // Add smooth hover effects to recent transactions
        const transactionItems = document.querySelectorAll('.recent-transaction-item');
        transactionItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.2s ease-in-out';
            });
        });
        
        // Error toast function
        function showErrorToast(message) {
            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '1055';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-triangle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Initialize and show toast
            const toastElement = document.getElementById(toastId);
            const bsToast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            
            bsToast.show();
            
            // Remove from DOM after hide
            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }
        
        // Add fade-in animation to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('fade-in');
            }, index * 100);
        });
    });
    </script>

<?= $this->endSection() ?>