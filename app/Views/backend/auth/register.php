<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>TOL API | Register Page</title>
    <meta name="description" content="Register Page" />

    <!-- Favicon Tags Start -->
    <link rel="shortcut icon" href="https://place-hold.it/100x100/00362b/fff/fff?text=TOL&fontsize=30&bold" type="image/x-icon" >
    <link rel="icon" type="image/png" href="https://place-hold.it/128x128/00362b/fff/fff?text=TOL&fontsize=30&bold" sizes="128x128" />
    <meta name="application-name" content="TOL" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <!-- Favicon Tags End -->

    <!-- Font Tags Start -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>font/CS-Interface/style.css" />
    <!-- Font Tags End -->

    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/OverlayScrollbars.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Vendor Styles End -->

    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/styles.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/main.css" />
    <!-- Template Base Styles End -->


    <style>
        .fa-eye{
            position: absolute;
            top: 34%;
            right: 4%;
            cursor: pointer;
            color: #7c7676;
        }
        .fa-eye-slash{
            position: absolute;
            top: 34%;
            right: 4%;
            cursor: pointer;
            color: #7c7676;
        }
        .logo-default-login{
            background-image: url("https://place-hold.it/150x75/00362b/fff/fff?text=TOL API&fontsize=20&bold");
            width: 150px;
            min-height: 75px;
            object-position: left;
            object-fit: cover;
            background-repeat: no-repeat;
        }

    </style>
    <script src="<?= base_url('assets/admin/') ?>js/base/loader.js"></script>
</head>

<body class="h-100">
    <div class="fixed-background"></div>

    <div id="root" class="h-100">

        <div class="container-fluid p-0 h-100 position-relative">
            <div class="row g-0 h-100">

                <!-- Left Side Start -->
                <div class="col-12 col-lg-auto h-100 pb-4 px-4 pt-0 p-lg-0">
                    <div class="sw-lg-70 min-h-100 bg-foreground d-flex justify-content-center align-items-center shadow-deep py-5 full-page-content-left-border">
                        <div class="sw-lg-50 px-5">
                            <div class="sh-11">
                                <a href="<?= site_url() ?>">
                                    <div class="logo-default-login"></div>
                                </a>
                            </div>
                            <div class="mb-5">
                                <p class="h6">Please use the form to register.</p>
                                <p class="h6">
                                    If you are a member, please
                                    <a href="/login" style="text-decoration:underline;">login</a>
                                </p>
                            </div>

                            <div class="mt-1 mb-3">

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger">
                                    <?= session()->getFlashdata('error') ?>
                                </div>
                            <?php endif; ?>
                            <?php if (session()->getFlashdata('success')): ?>
                                <div class="alert alert-success">
                                    <?= session()->getFlashdata('success') ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <form action="<?= base_url('/register') ?>" method="post" >
                                    <?= csrf_field() ?>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="loaf"></i>
                                        <select id="group_store" name="group_store" required class="form-control select select2">
                                            <option label="&nbsp; Select Group Store"></option>
                                            <?php foreach ($group_customer as $key => $value) { ?>
                                                <option value="<?= $value->kode_group ?>" <?= old('group_store') == $value->kode_group ? 'selected' : '' ?>><?= $value->nama_group ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="home"></i>
                                        <select id="customer" name="store" required class="form-control select select2">
                                            <option label="&nbsp; Select Group Store First"></option>
                                            <?php if(old('group_store') && isset($customer) && is_array($customer)) {
                                                foreach($customer as $c) {
                                                    echo '<option value="'.$c->kode_customer.'" '.((old('store') == $c->kode_customer) ? 'selected' : '').'>'.$c->nama_customer.'</option>';
                                                }
                                            } ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="user"></i>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama Store" autocomplete="off" required tabIndex="1" readonly value="<?= old('name') ?>" />
                                    </div>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="email"></i>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" autocomplete="off" required tabIndex="2" value="<?= old('email') ?>" />
                                    </div>
                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="lock-on"></i>
                                        <input class="form-control pe-7" name="password" id="password" type="password" placeholder="Password" autocomplete="off" required tabIndex="3" />
                                        <i class="fa fa-eye" onclick="showPass()" id="eye" title="Show Password"></i>
                                    </div>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="lock-on"></i>
                                        <input class="form-control pe-7" name="password_confirm" id="password_confirm" type="password" placeholder="Konfirmasi Password" autocomplete="off" required tabIndex="4" />
                                        <i class="fa fa-eye" onclick="showPassConfirm()" id="eye2" title="Show Password"></i>
                                    </div>

                                    <div hidden class="mb-3 position-relative form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="registerCheck" name="registerCheck" />
                                            <label class="form-check-label" for="registerCheck">
                                            I have read and accept the
                                            <a href="index.html" target="_blank">terms and conditions.</a>
                                            </label>
                                        </div>
                                    </div>

                                    <button tabIndex="5" type="submit" class="btn btn-lg btn-primary mr-2">Signup</button>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Left Side End -->

                <!-- Right Side Start -->
                <div class="offset-0 col-12 d-none d-lg-flex offset-md-1 col-lg ">  <!-- h-lg-100 -->
                    <div class="min-h-100 d-flex align-items-center">
                        
                    </div>
                </div>
                <!-- Right Side End -->

            </div>
        </div>

    </div>



    <!-- Vendor Scripts Start -->
    <script src="<?= base_url('assets/admin/') ?>js/vendor/jquery-3.5.1.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/OverlayScrollbars.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/autoComplete.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/clamp.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>icon/acorn-icons.js"></script>
    <script src="<?= base_url('assets/admin/') ?>icon/acorn-icons-interface.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/jquery.validate/jquery.validate.min.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/vendor/jquery.validate/additional-methods.min.js"></script>
    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="<?= base_url('assets/admin/') ?>js/base/helpers.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/base/globals.js"></script>
    <script src="<?= base_url('assets/admin/') ?>js/base/nav.js"></script>
    <!-- <script src="<?= base_url('assets/admin/') ?>js/base/search.js"></script> -->
    <script src="<?= base_url('assets/admin/') ?>js/base/settings.js"></script>
    <!-- Template Base Scripts End -->

    <!-- Page Specific Scripts Start -->
    <script src="<?= base_url('assets/admin/') ?>js/scripts.js"></script>
    <!-- Page Specific Scripts End -->

    <!-- Modal Notifikasi Pilih Group -->
    <div class="modal fade" id="modalPilihGroup" tabindex="-1" aria-labelledby="modalPilihGroupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalPilihGroupLabel">Warning !!</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            Please select group store first!!
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Modal Notifikasi Tidak Ada Store -->
    <div class="modal fade" id="modalNoStore" tabindex="-1" aria-labelledby="modalNoStoreLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalNoStoreLabel">Peringatan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Group yang Anda pilih belum memiliki store. Silakan hubungi PIC Anda.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">

        $(document).on('keypress', 'input,select', function (e) {
            if (e.which == 13) {
                e.preventDefault();
                var $next = $('[tabIndex=' + (+this.tabIndex + 1) + ']');
                console.log($next.length);
                if (!$next.length) {
                $next = $('[tabIndex=1]');        }
                $next.focus() .click();
            }
        });


        function showPass() {
            var x = document.getElementById("password");
            var e = document.getElementById("eye");
            if (x.type === "password") {
                x.type = "text";
                e.classList = "fa fa-eye-slash";
                e.title = "Hidden";
            } else {
                x.type = "password";
                e.classList = "fa fa-eye";
                e.title = "Show Password";
            }
        }

        function showPassConfirm() {
            var xx = document.getElementById("password_confirm");
            var ee = document.getElementById("eye2");
            if (xx.type === "password") {
                xx.type = "text";
                ee.classList = "fa fa-eye-slash";
                ee.title = "Hidden";
            } else {
                xx.type = "password";
                ee.classList = "fa fa-eye";
                ee.title = "Show Password Confirm";
            }
        }

        $('#group_store').on('change', function() {
            var group = $(this).val();
            $('#name').val('');
            if(!group) {
                $('#customer').html('<option label="&nbsp; Select Store"></option>');
                return;
            }
            $('#customer').html('<option>Loading...</option>');
            $.post('/register/get-customer-by-group', {group: group}, function(data) {
                var html = '<option label="&nbsp; Select Store"></option>';
                if(data.length === 0) {
                    var modal = new bootstrap.Modal(document.getElementById('modalNoStore'));
                    modal.show();
                }
                $.each(data, function(i, v) {
                    html += '<option value="'+v.kode_customer+'">'+v.nama_customer+'</option>';
                });
                $('#customer').html(html);
            });
        });
        $('#customer').on('focus', function() {
            var group = $('#group_store').val();
            if(!group) {
                var modal = new bootstrap.Modal(document.getElementById('modalPilihGroup'));
                modal.show();
                $('#group_store').focus();
            }
        });
        $('#customer').on('change', function() {
            var selectedText = $(this).find('option:selected').text();
            if(selectedText && selectedText !== '' && selectedText !== 'Select Store') {
                $('#name').val(selectedText);
            } else {
                $('#name').val('');
            }
        });
    </script>

</body>
</html>


