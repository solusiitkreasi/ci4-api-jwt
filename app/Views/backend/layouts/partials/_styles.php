
    
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/OverlayScrollbars.min.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/datatables.min.css" />
    <!-- Vendor Styles End -->

    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/styles.css" />
    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/main.css" />
    <!-- Template Base Styles End -->

    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/tagify.css" />

    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/select2.min.css" />

    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/select2-bootstrap4.min.css" />

    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/bootstrap-datepicker3.standalone.min.css" />

    <link rel="stylesheet" href="<?= base_url('assets/admin/') ?>css/vendor/fancybox.css"/>

    <style>
        .dataTables_length {
            margin-bottom: 2em;
        }
        .dataTables_scrollHeadInner {
            width: 100% !important;
        }
        div.dataTables_scrollHead{
            width: 100% !important;
        }


        .dataTables_scroll
        {
            overflow:auto;
        }

        /* .table-bordered>:not(caption)>*>*{
            border-width: 0 0px !important;
        } */

        .content-loader
        {
            display: none;
        }

        .load-wrapper{
            position: absolute;
            left: 46%;
            top: 35%;
            z-index: 1000;
        }

        .loader
        {
            width: 100px;
            padding: 8px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: #25b09b;
            --_m:
                conic-gradient(#0000 10%, #000),
                linear-gradient(#000 0 0) content-box;
            -webkit-mask: var(--_m);
            mask: var(--_m);
            -webkit-mask-composite: source-out;
            mask-composite: subtract;
            animation: l3 1s infinite linear;
        }

        @keyframes l3 {
            to {
                transform: rotate(1turn)
            }
        }

        span.select2-selection.select2-selection--single {
            height: 100% !important;
        }
    </style>