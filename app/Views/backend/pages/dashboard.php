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


        <div class="row g-2">
            <div class="col-md-8">
                <div class="row">

                    <section class="scroll-section">
                        <div class="card">
                            <div class="card-body scroll-out mb-n2">
                            <h3>Recent Transaksi</h3><hr>

                            <div class="scroll sh-50">
                                <?php //for ($i=0; $i < 2 ; $i++) { ?>
                                <?php // } ?>

                                <?php //$recentTandaTerima = $this->Model_global->showRecentTandaTerima();
                                    //foreach ($recentTandaTerima as $key => $val) { ?>
                                    <div class="card mb-2 sh-15 sh-md-6">
                                        <div class="card-body pt-0 pb-0 h-100">
                                            <div class="row g-0 h-100 align-content-center">
                                                <div class="col-10 col-md-4 d-flex align-items-center mb-3 mb-md-0 h-md-100">
                                                    <a href="#" class="body-link stretched-link">
                                                        #<b> </b>

                                                    </a>

                                                </div>
                                                <div class="col-2 col-md-3 d-flex align-items-center text-black mb-1 mb-md-0 justify-content-end justify-content-md-start">
                                                    <span class="badge bg-primary me-1"> </span>
                                                </div>
                                                <div class="col-12 col-md-2 d-flex align-items-center mb-1 mb-md-0">
                                                    <span>
                                                        <b> </b> Item
                                                    </span>
                                                </div>
                                                <div class="col-12 col-md-3 d-flex align-items-center justify-content-md-end mb-1 mb-md-0">
                                                    <b> </b>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                <?php //} ?>

                            </div>

                            </div>
                        </div>
                    </section>

                </div>

            </div>

            <?php  $filterRole = session()->get('roles');
                if (is_array($filterRole) ? in_array('Super Admin', $filterRole) : $filterRole == 'Super Admin') { ?>

            <div class="col-md-4">
                <div class="card w-100 sh-60 mb-5">
                    <img src="<?= base_url('assets/admin/') ?>img/banner/cta-square-4.webp" class="card-img h-100" alt="card image" />
                    <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                        <h3 class="text-title mb-2"> Data Master</h3>
                        <div class="d-flex flex-column h-100 justify-content-between align-items-start">
                            <div>
                                <a href=" #">
                                    <div class="cta-1 text-primary mb-0">
                                    </div>
                                    <div class="lh-1-25 mb-0 text-black">Data Barang</div>
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

<?= $this->endSection() ?>