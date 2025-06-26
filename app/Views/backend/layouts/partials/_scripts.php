
<!-- warning modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="warningModal">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Modal title</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

            <div class="modal-body">
                <div id="messages_modal_warning"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
            </div>
		</div>
	</div>
</div>


<!-- modal alert item list -->
<div class="modal fade" tabindex="-1" role="dialog" id="modalAlert">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="title-notif">Info</h3>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div id="messages"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-warning" data-bs-dismiss="modal" id='btn-info'>Close</a>
            </div>
        </div>
    </div>
</div>
<!-- modal alert item list -->

<!-- modal alert item list -->
<div class="modal fade" tabindex="-1" role="dialog" id="submitModal">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="title-notif">Notif</h3>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <h3><b> Yakin Simpan!!</b></h3>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-warning" data-bs-dismiss="modal" >Close</a>
                <button type="submit" class="btn btn-success" id="btn-submit" >Save</button>
            </div>
        </div>
    </div>
</div>
<!-- modal alert item list -->


<!-- Vendor Scripts Start -->
<script src="<?= base_url('assets/admin/') ?>js/vendor/jquery-3.5.1.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/OverlayScrollbars.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/autoComplete.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/clamp.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>icon/acorn-icons.js"></script>
<script src="<?= base_url('assets/admin/') ?>icon/acorn-icons-interface.js"></script>
<script src="<?= base_url('assets/admin/') ?>icon/acorn-icons-learning.js"></script>
<script src="<?= base_url('assets/admin/') ?>icon/acorn-icons-commerce.js"></script>
<script src="<?= base_url('assets/admin/') ?>icon/acorn-icons-medical.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/jquery.validate/jquery.validate.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/jquery.validate/additional-methods.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/bootstrap-submenu.js"></script>

<script src="<?= base_url('assets/admin/') ?>js/datatables.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/bootstrap-notify.min.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/vendor/mousetrap.min.js"></script>
<!-- Vendor Scripts End -->

<!-- Template Base Scripts Start -->
<script src="<?= base_url('assets/admin/') ?>js/base/helpers.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/base/globals.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/base/nav.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/base/settings.js"></script>
<!-- Template Base Scripts End -->

<script src="<?= base_url('assets/admin/') ?>js/pages/customers.detail.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/pages/profile.standard.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/plugins/notifies.js"></script>



<script src="<?= base_url('assets/admin/') ?>js/vendor/tagify.min.js"></script>

<script src="<?= base_url('assets/admin/') ?>js/vendor/select2.full.min.js"></script>

<script src="<?= base_url('assets/admin/') ?>js/vendor/datepicker/bootstrap-datepicker.min.js"></script>

<script src="<?= base_url('assets/admin/') ?>js/vendor/datepicker/locales/bootstrap-datepicker.es.min.js"></script>

<script src="<?= base_url('assets/admin/') ?>js/vendor/fancybox.umd.js"></script>





<!-- Page Specific Scripts Start -->
<script src="<?= base_url('assets/admin/') ?>js/common.js"></script>
<script src="<?= base_url('assets/admin/') ?>js/scripts.js"></script>
<!-- Page Specific Scripts End -->



<script type="text/javascript">
    $(document).ready(function() {

        $('.select2-single').select2();

        $(function() {
            $('#selectTanggal').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
            });
        });
        $(function() {
            $('#selectTanggalAwal').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
            });
        });
        $(function() {
            $('#selectTanggalAkhir').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
            });
        });
    });

    Fancybox.bind('[data-fancybox="gallery"]', {

    Toolbar: {
        display: {
        left: ["infobar"],
        middle: [
            "zoomIn",
            "zoomOut",
            "toggle1to1",
            "rotateCCW",
            "rotateCW",
            "flipX",
            "flipY",
        ],
        right: ["slideshow", "thumbs", "close"],
        },
    },
    });

    Fancybox.bind('[data-fancybox="foto-profil"]', {

        Toolbar: {
            display: {
            left: ["infobar"],
            middle: [
                "zoomIn",
                "zoomOut",
                "toggle1to1",
                "rotateCCW",
                "rotateCW",
                "flipX",
                "flipY",
            ],
            right: ["slideshow", "thumbs", "close"],
            },
        },
    });
</script>

<script type="text/javascript">
$(window).on('load',function(){
    $('.loader').fadeOut(1000, function () {
        $('.content-loader').show();
    });
});

function remove(id)
{
    $("#btn-delete").removeAttr('class');
    $("#btn-delete").text('Remove');
    $("#btn-delete").addClass('btn btn-danger');
    $("#removeModal h5").text('Remove ');
    $("#messages_modal_remove").html('');
    $("#id span").html('Remove '+' <strong> '+id+'</strong>');
    if(id){
        $("#removeForm").on('submit', function() {
            var form = $(this);
            // remove the text-danger
            $(".text-danger").remove();

            if(id !== null){
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: { id:id },
                    dataType: 'json',
                    success:function(response) {

                        tables.ajax.reload(null, false);

                        if(response.success === true) {
                            $("#messages").html('<div class="alert alert-success alert-dismissible fade show" role="alert">'+
                                '<strong>'+response.messages+ '</strong>' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                            // hide the modal
                            $("#removeModal").modal('hide');

                        } else {

                            $("#messages_modal_remove").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+
                                '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span>  '+response.messages+ '</strong>' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>' +
                            '</div>');
                        }
                    }
                });
            }
            id = null;
            return false;
        });
    }
}

function dialog_warning(title,messages)
{
    $('#title-notif').html('<strong>'+title+'</strong>');
    $("#messages").html( '<strong><h5>'+messages+ '</h5></strong>');
    $('#modalAlert').modal("show");
}

function dialog_submit(title,messages)
{
    $('#title-notif').html('<strong>'+title+'</strong>');
    $("#messages").html( '<strong><h5>'+messages+ '</h5></strong>');
    $('#submitModal').modal("show");
}

function dialog_success(title,messages)
{
    $('.modal-title').html('<strong>'+title+'</strong>');
    $("#messages").html( '<strong><h3>'+messages+ '</h3></strong>');
    $('#modalAlert').modal("show");
}


</script>




