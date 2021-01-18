<!-- HEADER DE LA PAGE -->

    <? include( __DIR__.'/../header.php') ;?>
    <? $thispage = 'detail_rechargement'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;" ">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['detail_rechargement']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">
						<?= $data['lang']['detail_rechargement']; ?>: du <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datedeb'])); ?> au <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datefin'])); ?>
                        </h3>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                        
                        <div class="table-responsive">
                            <table id="reporting-produit-grid" class="table table-bordered table-hover">
                                <thead>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['date']; ?></th>
                                    <th><?= $data['lang']['numero']; ?></th>
                                    <th><?= $data['lang']['agence']; ?></th>
                                    <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                    <th><?= $data['lang']['commission']; ?></th>
                                    <th><?= $data['lang']['nom_beneficiaire']; ?></th>
                                    <th><?= $data['lang']['carte_num']; ?></th>
                                </tr>
                                </thead>

                                <tfoot>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['date']; ?></th>
                                    <th><?= $data['lang']['numero']; ?></th>
                                    <th><?= $data['lang']['agence']; ?></th>
                                    <th><?= $data['lang']['montant_sans_ttc']; ?></th>
                                    <th><?= $data['lang']['commission']; ?></th>
                                    <th><?= $data['lang']['nom_beneficiaire']; ?></th>
                                    <th><?= $data['lang']['carte_num']; ?></th>
                                </tr>
                                </tfoot>
                            </table>
                            
                            <input type="hidden"  id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                            <input type="hidden" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-body margin-bottom">
                            <div class="col-xs-6">
                            
                            </div>
                            <div class="col-xs-6">
                            <form action="<?= ROOT ?>reporting/printdetaildrecharge" method="post" name="form2" target="_blank">
                                <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
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
    </section><!-- /.content -->
</div>


<? include(__DIR__.'/../footer.php') ;?>

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
                url :"<?= ROOT ?>reporting/processingdetailrecharge/"+date1+"/"+date2, // json datasource
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