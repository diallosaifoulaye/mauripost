<?php
include(__DIR__ . '/../agentheader.php');
?>


<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title"></h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">

                <ol class="breadcrumb">
                    <li><a href="#"><?= $data['lang']['Tableau_de_bord']; ?></a></li>
                    <li class="active"><?= $data['lang']['historique_transaction']; ?></li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- CONTENT DE LA PAGE -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?= $data['lang']['historique_transaction']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="false">
                        <div class="panel-body">

                         <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                                <form class="form-horizontal" method="POST" id="ajoutuser" action="<?= ROOT ?>reporting/reportingdate">
                                	<fieldset style="display: block;" class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['historique_transaction']; ?></legend>
                                    <div class="form-group">
                                        <label for="numserie" class="col-sm-5 control-label"><?= $data['lang']['tel_mobile_marchand']; ?> :</label>
                                        <div class="col-sm-7">
                                            <input type="tel"  class="form-control" id="numserie" name="numserie" placeholder="<?= $data['lang']['tel_mobile_marchand']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="from" class="col-sm-5 control-label"><?= $data['lang']['date_deb']; ?> (*): </label>
                                        <div class="col-sm-7">

                                            <input type="text" name="datedeb" required class="form-control" id="from" placeholder="<?= $data['lang']['entrer_date_deb']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="to" class="col-sm-5 control-label"><?= $data['lang']['date_fin']; ?> (*): </label>
                                        <div class="col-sm-7">
                                            <input type="text" required class="form-control" id="to" name="datefin" placeholder="<?= $data['lang']['entrer_date_fin']; ?>">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <div class="col-sm-offset-6 col-sm-6">
                                            <button type="submit" name="search" value="search" class="btn btn-warning"><?= $data['lang']['continuer']; ?></button>
                                        </div>
                                    </div>
                                  </fieldset>
                                </form>
                         </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
</div>

    </div>
</div>

<!-- /.container-fluid -->
<footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
</div>
<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>


<!-- page script -->
<script>
    $(document).ready(function() {
        $('#numserie')
            .intlTelInput({
                utilsScript: '<?= WEBROOT.'/assets/plugins/build/js/utils.js';?>',
                autoPlaceholder: true,
                preferredCountries: ['mr', 'bf', 'gb'],
                initialDialCode: true,
                nationalMode: false
            });


    });
</script>
<script>
    $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();
    });
</script>
<script>
        $(function() {
            $( "#from" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( "#to" ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( "#to" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                }
            });


        });
</script>




