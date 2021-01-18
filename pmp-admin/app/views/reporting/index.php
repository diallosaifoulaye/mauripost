<!-- HEADER DE LA PAGE -->

    <? include( __DIR__.'/../header.php') ;?>
    <? $thispage = 'reporting_jour'; ?>

<!-- END HEADER DE LA PAGE -->




<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['list_transac_jr']; ?>
        </h1>
        <ol class="breadcrumb">
            <!-- <li><a href="#" data-toggle="modal" data-target="#addUser"><i class="fa fa-user"></i> <?= $data['lang']['nouvel_user']; ?></a></li> -->
        </ol>
    </section>

    <!-- END Content Header (Page header) -->


<!-- CONTENT DE LA PAGE -->
<section class="content" style="min-height: 0px !important;">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><?= $data['lang']['list_transac_jr']; ?></h3><!-- transaction du jour -->

                </div><!-- /.box-header -->
                <div class="box-body">


                    <div class="table-responsive">
                        <table id="user-grid" class="table table-bordered table-hover">
                            <thead>
                            <tr class="titre_table">
                                <th><?= $data['lang']['date_transac']; ?></th>
                                <th><?= $data['lang']['numero_transac']; ?></th>
                                <th><?= $data['lang']['produit_transac']; ?></th>
                                <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                <th><?= $data['lang']['commission_transac']; ?></th>
                                <th><?= $data['lang']['montant_ttc']; ?></th>
                                <th><?= $data['lang']['statut_transac']; ?></th>
                                <th><?= $data['lang']['effectuer_par']; ?></th>
                                <th><?= $data['lang']['agence_transac']; ?></th>
                                <th><?= $data['lang']['details_transac']; ?></th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr class="titre_table">
                                <th><?= $data['lang']['date_transac']; ?></th>
                                <th><?= $data['lang']['numero_transac']; ?></th>
                                <th><?= $data['lang']['produit_transac']; ?></th>
                                <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                <th><?= $data['lang']['commission_transac']; ?></th>
                                <th><?= $data['lang']['montant_ttc']; ?></th>
                                <th><?= $data['lang']['statut_transac']; ?></th>
                                <th><?= $data['lang']['effectuer_par']; ?></th>
                                <th><?= $data['lang']['agence_transac']; ?></th>
                                <th><?= $data['lang']['details_transac']; ?></th>
                            </tr>
                            </tfoot>
                        </table>
                        <input type="hidden" id="date1" value="<?php echo date('Y-m-d') ?>" />
                        <input type="hidden" id="date2" value="<?php echo date('Y-m-d') ?>" />
                    </div>
                </div><!-- /.box-body -->
                <div class="box-body margin-bottom">
                    <div class="col-xs-6">
                        <form action="<?= ROOT ?>reporting/facturejour" method="post" name="form2" target="_blank">
                            <input type="hidden" name="date1" id="date1" value="<?php echo date('Y-m-d'); ?>" />
                            <input type="hidden" name="date2" id="date2" value="<?php echo date('Y-m-d'); ?>" />
                            <input name="PDF" type="hidden" value="PDF" />
                            <button name="PDF" type="submit" value="PDF" class="btn btn text-red" title="<?= $data['lang']['export_pdf']; ?>">
                                <i class="fa fa-4x fa-file-pdf-o"></i> 
                            </button>
                        </form>
                    </div>
                    <div class="col-xs-6">
                        <form action="<?= ROOT ?>reporting/facturejourExcel" method="post" name="form2" target="_blank">
                            <input type="hidden" name="date1" id="date1" value="<?php echo date('Y-m-d'); ?>" />
                            <input type="hidden" name="date2" id="date2" value="<?php echo date('Y-m-d'); ?>" />
                            <input name="XSL" type="hidden" value="XSL" />
                            <button name="XSL" type="submit" value="XSL" class="btn btn text-green" title="<?= $data['lang']['export_excel']; ?>">
                                <i class="fa fa-4x fa-file-excel-o"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
</div>
<!-- END CONTENT DE LA PAGE -->


<!-- FOOTER DE LA PAGE -->
<? include(__DIR__.'/../footer.php') ;?>
<!-- END FOOTER DE LA PAGE -->

<!--<script src="<?/*= ROOT.'/assets/plugins/datatables/jquery.dataTables.min.js';*/?>"></script>
<script src="<?/*= ROOT.'/assets/plugins/datatables/dataTables.bootstrap.min.js';*/?>"></script>
-->

<script type="text/javascript" language="javascript" >
    $(document).ready(function() {
        var date1=$('#date1').val();
        var date2=$('#date2').val();
        var dataTable = $('#user-grid').DataTable( {
            "language": {
                url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"  },
            "processing": true,
            "serverSide": true,
            "ajax":{
                    url : "<?= ROOT ?>reporting/processingUser/"+date1+"/"+date2, // json datasource
                type: "post",  // method  , by default get
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("table","none");

                }
            }
        } );
    } );
</script>
