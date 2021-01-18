<!-- HEADER DE LA PAGE -->

    <? include( __DIR__.'/../header.php') ;?>
    <? $thispage = 'reporting_produit'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <section class="content-header">
        <h1>
            <?= $data['lang']['reporting_produit']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['reporting_produit']; ?></h3>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                        
                        <div class="table-responsive">
                            <table id="reporting-produit-grid" class="table table-bordered table-hover">
                                <thead>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['date']; ?></th>
                                    <th><?= $data['lang']['numero']; ?></th>
<!--                                    <th>--><?//= $data['lang']['tel_mobile']; ?><!--</th>-->
                                    <th><?= $data['lang']['produit']; ?></th>
                                    <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                    <th><?= $data['lang']['commission']; ?></th>
                                    <th><?= $data['lang']['montant_ttc']; ?></th>
                                    <th><?= $data['lang']['statut']; ?></th>
                                    <th><?= $data['lang']['effectuer_par']; ?></th>
                                    <th><?= $data['lang']['agence']; ?></th>
                                    <th style="width: 50px; margin: 0;"><?= $data['lang']['details']; ?></th>
                                </tr>
                                </thead>



                                <tfoot>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['date']; ?></th>
                                    <th><?= $data['lang']['numero']; ?></th>
                                    <!--                                    <th>--><?//= $data['lang']['tel_mobile']; ?><!--</th>-->
                                    <th><?= $data['lang']['produit']; ?></th>
                                    <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                    <th><?= $data['lang']['commission']; ?></th>
                                    <th><?= $data['lang']['montant_ttc']; ?></th>
                                    <th><?= $data['lang']['statut']; ?></th>
                                    <th><?= $data['lang']['effectuer_par']; ?></th>
                                    <th><?= $data['lang']['agence']; ?></th>
                                    <th style="width: 50px; margin: 0;"><?= $data['lang']['details']; ?></th>
                                </tr>
                                </tfoot>
                            </table>
                            
                            <input type="hidden"  id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                            <input type="hidden" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                            <input type="hidden" id="produit" value="<?php echo intval($_POST['produit']); ?>" />
                            <input type="hidden" id="agence" value="<?php echo intval($_POST['agence']); ?>" />
                        </div>
                    </div><!-- /.box-body -->

                    <div class="box-body margin-bottom">
                        <div class="col-lg-12">
                            <div class="col-lg-6">
                                <form action="<?= ROOT ?>reporting/factureproduit" method="post" name="form2" target="_blank">
                                    <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                    <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                    <input type="hidden" name="produit" id="produit" value="<?php echo intval($_POST['produit']); ?>" />
                                    <input type="hidden" name="agence" id="agence" value="<?php echo intval($_POST['agence']); ?>" />
                                    <input name="PDF" type="hidden" value="PDF" />
                                    <button name="PDF" type="submit" value="PDF" class="btn btn text-red" title="<?= $data['lang']['export_pdf']; ?>">
                                        <i class="fa fa-4x fa-file-pdf-o"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-lg-6">
                                <form action="<?= ROOT ?>reporting/factureproduitExcel" method="post" name="form2" target="_blank">
                                    <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                    <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                    <input type="hidden" name="produit" id="produit" value="<?php echo intval($_POST['produit']); ?>" />
                                    <input type="hidden" name="agence" id="agence" value="<?php echo intval($_POST['agence']); ?>" />
                                    <input name="XSL" type="hidden" value="XSL" />
                                    <button name="XSL" type="submit" value="XSL" class="btn btn text-green" title="<?= $data['lang']['export_excel']; ?>">
                                        <i class="fa fa-4x fa-file-excel-o"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>


<? include(__DIR__.'/footer.php') ;?>


<!-- page script -->

<script type="text/javascript" language="javascript" >
    $(document).ready(function() {
		var date1=$('#date1').val();
		var date2=$('#date2').val();
		var produit=$('#produit').val();
		var agence=$('#agence').val();
        var dataTable = $('#reporting-produit-grid').DataTable( {
        	"language": {
            url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"  },
            "processing": true,
            "serverSide": true,
            "ajax":{
                url :"<?= ROOT ?>reporting/processingproduit/"+date1+"/"+date2+"/"+produit+"/"+agence, // json datasource
                type: "POST",  // method  , by default get

                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("table","none");

                }
            }
        } );
    } );
</script>


</body>
</html>