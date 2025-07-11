<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>TOL API | Reset Password</title>
    <meta name="description" content="Reset Password" />

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
                                <p class="h6">Please use the form to reset password.</p>
                                <p class="h6">
                                    If you are a member, please
                                    <a href="/login" style="text-decoration:underline;">login</a>
                                </p>
                            </div>

                            <div class="mt-1 mb-3">

                            <?php if (!empty($error)):  ?>
                                <div class="alert alert-danger">
                                    <?= $error ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success">
                                    <?= $success ?>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($success)): ?>
                                <?php if (isset($is_valid) && !$is_valid): ?>
                                    <div class="alert alert-danger">Token tidak valid atau sudah expired.</div>
                                <?php else: ?>
                               
                            <div>
                                <form action="/reset_password" method="post" >
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="lock-on"></i>
                                        <input class="form-control pe-7" name="password" id="password" type="password" placeholder="Password" autocomplete="off" required  />
                                        <i class="fa fa-eye" onclick="showPass()" id="eye" title="Show Password"></i>
                                    </div>

                                    <div class="mb-3 filled form-group tooltip-end-top">
                                        <i data-acorn-icon="lock-on"></i>
                                        <input class="form-control pe-7" name="password2" id="password2" type="password" placeholder="Konfirmasi Password" autocomplete="off" required  />
                                        <i class="fa fa-eye" onclick="showPassConfirm()" id="eye2" title="Show Password"></i>
                                    </div>


                                    <button type="submit" class="btn btn-lg btn-primary mr-2">Reset Password</button>
                                    
                                </form>
                            </div>

                             <?php endif ?>
                            <?php endif ?>
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
            var xx = document.getElementById("password2");
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
    </script>

</body>
</html>


