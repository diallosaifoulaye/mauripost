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
                    <div class="panel-heading"><?= $data['lang']['reporting_produit']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="false">
                        <div class="panel-body">

                        <!-- Main content -->

                         <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                                <form class="form-horizontal" method="POST" action="<?= ROOT ?>reporting/reportingproduit">
                                	<fieldset style="display: block;" class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['reporting_produit']; ?></legend>
                                   
                                   <div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?= $data['lang']['produit']; ?> (*):
                                        </label>
                                        <div class="col-sm-7">
                                            <select class="form-control select2" required name="produit" style="width: 100%;" id="produit">
                                                <option selected="selected" value=""><?= $data['lang']['select_service'] ; ?></option>
                                                <?php foreach($data['service'] as $serv){ ?>
                                                    <option value="<?= $serv['rowid']; ?>"><?= $serv['label']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                   
                                   
                                   
                                <!--    <div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?/*= $data['lang']['nom_agence'].' :' ; */?>
                                        </label>
                                        <div class="col-sm-7">
                                            <select class="form-control select2" name="agence" style="width: 100%;" id="agence">
                                                <option selected="selected" value=""><?/*= $data['lang']['select_agence'] ; */?></option>
                                                <?php /*foreach($data['agence'] as $reg){ */?>
                                                    <option value="<?/*= $reg['rowid']; */?>"><?/*= $reg['agence']; */?></option>
                                                <?php /*} */?>
                                            </select>
                                        </div>
                                    </div>-->
                                                      
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
    </div><!-- /.content -->
</div>

</div>
-- /.container-fluid -->
<footer class="footer text-center"> 2017 &copy; By NUMHERIT SA </footer>
</div>
<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>


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

