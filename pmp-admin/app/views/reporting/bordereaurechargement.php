<!-- HEADER DE LA PAGE -->
<?php
  $transact = $data['recu'];
?>
<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'bordereau_rechargement'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['bordereau_rechargement']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">
						                <?= $data['lang']['bordereau_rechargement']; ?>: du <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datedeb'])); ?> au <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datefin'])); ?>
                        </h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive">
                           <?php if($transact > 0){ ?>
                            <table width="70%" align="center" cellpadding="10" cellspacing="0" border="1" style="font-size:14px">
                                  <thead>
                                    <tr>
                                      <td width="19%" rowspan="2" align="center"  valign="middle" nowrap="nowrap" class="txt_form1"><strong><?= $data['lang']['date_transac']; ?></strong></td>
                                      <td colspan="3" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['RECHARGEMENT']; ?></strong></td>
                                      <td width="25%" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['ANNULATION']; ?></strong></td>
                                    </tr>
                                    <tr>
                                      <td width="11%" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Nombre_de_transactions']; ?></strong></td>
                                      <td width="23%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                      <td width="22%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['commission_transac']; ?></strong></td>
                                      <td align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['(Montant)']; ?></strong></td>
                                      
                                     </tr>
                                  </thead>
                                  <tbody>
                                    <?php 
									  $nombre_total = 0;
									  $montant_total = 0;
									  $commission_total = 0;
									  $annulation_total = 0;
									 
									  foreach($transact as $row_rs_resultat)
									  { 
											$date_transaction = $row_rs_resultat['datet'];
											$nombre = $row_rs_resultat['nombre'];
											$montant = $row_rs_resultat['montant'];
											$commission = $row_rs_resultat['commission'];
											$annulation = 0;
											
											$nombre_total+= $nombre;
											$montant_total+=$montant;
											$commission_total+= $commission;
								    ?>
                                    <tr>
                                          <td align="center" valign="middle" class="textNormal"><?php echo $this->utils->date_fr2($date_transaction); ?></td>
                                          <td align="center" valign="middle" class="textNormal"><?php echo $this->utils->number_format($nombre); ?></td>
                                          <td align="right" valign="middle"  class="textNormal"><?php echo $this->utils->number_format($montant); ?></td>
                                          <td align="right" valign="middle"  class="textNormal"><?php echo $this->utils->number_format($commission); ?></td>
                                          <td align="center" valign="middle" class="textNormal"><?php echo $annulation; ?></td>
                                    </tr>
                                    <?php } ?>
                                  </tbody>
                                  <tfoot>
                                    <tr>
                                      <td  align="right"  valign="middle" nowrap="nowrap"><strong><?= $data['lang']['TOTAL']; ?>  : 
                                      </th>
                                      </strong>
                                      <td  align="center"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($nombre_total); ?></strong></td>
                                      <td  align="right"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($montant_total); ?></strong></td>
                                      <td  align="right"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($commission_total); ?></strong></td>
                                      <td  align="center"  valign="middle" nowrap="nowrap"><strong><?php echo $annulation_total; ?></strong></td>
                                    </tr>
                                  </tfoot>
                			</table>
                      <div class="box-body margin-bottom">
                            <div class="col-xs-6">
                                <form action="<?= ROOT ?>reporting/printbordereaurechargeExcel" method="post" name="form2" target="_blank">
                                    <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                    <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                    <input type="hidden" name="agence" id="agence" value="<?php echo $this->utils->securite_xss($_POST['agence']); ?>" />
                                    <input name="XSL" type="hidden" value="XSL" />
                                    <button name="XSL" type="submit" value="XSL" class="btn btn-default text-red" title="<?= $data['lang']['export_pdf']; ?>">
                                        <i class="fa fa-4x fa-file-excel-o"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-xs-6">
                            
                            <form action="<?= ROOT ?>reporting/printbordereaurecharge" method="post" name="form2" target="_blank">
                                <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                <input type="hidden" name="agence" id="agence" value="<?php echo $this->utils->securite_xss($_POST['agence']); ?>" />
                                <input name="PDF" type="hidden" value="PDF" />
                                <button name="PDF" type="submit" value="PDF" class="btn btn-default text-red" title="<?= $data['lang']['export_pdf']; ?>">
                                <i class="fa fa-4x fa-file-pdf-o"></i> 
                                </button>
                            </form>
                            
                            </div>
                            
                    </div>
                           <?php } else { ?> 
                           <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                              <tr>
                                <td align="center" valign="middle" class="txt_form"><?= $data['lang']['Pas_de_transaction_effectuee_pour_cet_intervalle']; ?></td>
                              </tr>
                            </table>
                           <?php } ?>
                        </div>
                    </div><!-- /.box-body -->
                    
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>
<? include(__DIR__.'/footer.php') ;?>

</body>
</html>