<!-- HEADER DE LA PAGE -->

<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'detail_retrait_titulaire'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['detail_retrait_titulaire']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['detail_retrait_titulaire']; ?></h3>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                         <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                                <form class="form-horizontal" method="POST" action="<?= ROOT ?>reporting/detailtitulaire">
                                    <fieldset style="display: block;" class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['detail_retrait_titulaire']; ?></legend>
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
    </section><!-- /.content -->
</div>

<? include(__DIR__.'/footer.php') ;?>

<!-- page script -->
<script>
    $(function () {
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

</body>
</html>