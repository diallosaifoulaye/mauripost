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
                    <li class="active"><?= $data['lang']['list_transac_jr']; ?></li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>


<!-- CONTENT DE LA PAGE -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?= $data['lang']['list_transac_jr']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="false">
                        <div class="panel-body">

                    <div class="table-responsive">
                        <table id="user-grid" class="table table-bordered table-hover">
                            <thead>
                            <tr class="titre_table">
                                <th><?= $data['lang']['date_transac']; ?></th>
                                <th><?= $data['lang']['numero_transac']; ?></th>
                                <th><?= $data['lang']['tel'];?></th>
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
                                <th><?= $data['lang']['tel']; ?></th>
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
                    <div class="col-xs-6"></div>
                    <div class="col-xs-6">
                        <form action="<?= ROOT ?>reporting/facturejour" method="post" name="form2" target="_blank">
                            <input type="hidden" name="date1" id="date1" value="<?php echo date('Y-m-d'); ?>" />
                            <input type="hidden" name="date2" id="date2" value="<?php echo date('Y-m-d'); ?>" />
                            <input name="PDF" type="hidden" value="PDF" />
                            <button name="PDF" type="submit" value="PDF" class="btn btn-default text-red" title="<?= $data['lang']['export_pdf']; ?>">
                                <i class="fa fa-4x fa-file-pdf-o"></i> 
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
</div>
</div>
<!-- END CONTENT DE LA PAGE -->


<!-- /.container-fluid -->
<footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
</div>
<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>

<!--<script type="text/javascript" src="<?/*= WEBROOT */?>assets/js/oXHR.js"></script>-->
<script src="<?= WEBROOT.'/assets/datatables/jquery.dataTables.min.js';?>"></script>
<script src="<?= WEBROOT.'/assets/datatables/dataTables.bootstrap.min.js';?>"></script>
<script type="text/javascript" language="javascript" >
    $(document).ready(function() {
        var lang = '<?= $_COOKIE['lang'];?>';
        // alert(lang);
        var lang_url = '';
        if(lang == 'fr') {
            lang_url = '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json';
        }
        else {
            lang_url = '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/English.json';
        }

        var dataTable = $('#user-grid').DataTable( {
            "language": {
                url: lang_url  },
            "processing": true,
            "serverSide": true,
            "ajax":{
                url :"<?= ROOT .'reporting/processingUser'; ?>", // json datasource
                type: "post",  // method  , by default get
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3"><?= $data['lang']['no_data'];?></th></tr></tbody>');
                    $("#employee-grid_processing").css("table","none");

                }
            }
        } );
    } );
</script>




