<!-- HEADER DE LA PAGE -->
<?php
  $dates = $data['dates'];
  $transact = $data['transact'];
  $bureau = $data['agence'];
?>
<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'bordereau_retrait'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['bordereau_retrait']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">
						                <?= $data['lang']['bordereau_retrait']; ?>: du <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datedeb'])); ?> au <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datefin'])); ?>
                        </h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive">
                           <?php if($transact > 0){ ?>
                            <table width="80%" border="1" align="center" cellpadding="5" cellspacing="0" style="font-size:16px">
                                  <tr>
                                    <td width="14%" rowspan="2" align="center" valign="middle"><strong><?= $data['lang']['date']; ?></strong></td>
                                    <td colspan="3" align="center" valign="middle"><strong><?= $data['lang']['RETRAIT_TIERS']; ?></strong></td>
                                    <td colspan="2" align="center" valign="middle" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['RETRAIT_TITULAIRE(CASHOUT)']; ?></strong></td>
                                    <td width="23%" align="center" valign="middle"><strong><?= $data['lang']['ANNULATION']; ?></strong></td>
                                  </tr>
                                  <tr>
                                    <td width="8%" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Nombre']; ?></strong></td>
                                    <td width="14%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Montant_(MRU)']; ?></strong></td>
                                    <td width="17%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Commission_(MRU)']; ?></strong></td>
                                    <td width="8%" align="center"  valign="top" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['Nombre']; ?></strong></td>
                                    <td width="16%" align="right"  valign="top" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['Montant_(MRU)']; ?></strong></td>
                                    <td align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['(Montant)']; ?></strong></td>
                                  </tr>
                                  <?php  
                                    $nombre_retrait_total = 0;
                                    $montant_retrait_total = 0;
                                    $montant_total = 0;
                                    $nombre_total = 0;
                                    $commision_total = 0;
                                    for($i = 0; $i < sizeof($dates); $i++)
                                    { 
                                        $date_transaction = $dates[$i];
                                        $nombre_retrait = $this->utils->nombreRetraitTiers($date_transaction, $bureau);
                                        $nombre_retrait_total+=$nombre_retrait;

                                        $montant_retrait = $this->utils->montantRetraitTiers($date_transaction, $bureau);
                                        $montant_retrait_total+=$montant_retrait;

                                        $montant = $this->utils->montantRetraitTitulaire($date_transaction, $bureau);
                                        $montant_total+=$montant;

                                        $commision = 300 * $nombre_retrait;
                                        $commision_total+=$commision;

                                        $nombre = $this->utils->nombreRetraitTitulaire($date_transaction, $bureau);
                                        $nombre_total+=$nombre;

                                        if($nombre_retrait > 0 || $nombre > 0){

                                    ?>
                                                    <tr>
                                                          <td width="14%" align="center" valign="middle"><?php echo $this->utils->date_fr2($date_transaction); ?></td>
                                                          <td align="center" valign="top">
                                                          <?php 

                                        echo $this->utils->number_format($nombre_retrait);
                                      ?>
                                                          </td>
                                                          <td align="right" valign="top">
                                      <?php 

                                        echo $this->utils->number_format($montant_retrait);
                                      ?>
                                                          </td>
                                                          <td align="right" valign="top">
                                      <?php 

                                        echo $this->utils->number_format($commision);
                                      ?>
                                                      </td>
                                                          <td align="center" valign="top" bgcolor="#CCCCCC">
                                                          <?php 

                                        echo $this->utils->number_format($nombre);
                                      ?>
                                                          </td>
                                                        <td align="right" valign="top" bgcolor="#CCCCCC">
                                      <?php 

                                      echo $this->utils->number_format($montant);
                                      ?>
                                                          </td>
                                                          <td align="center" valign="middle"><?php echo 0; ?></td>
                                                    </tr>
                                      <?php }
                                    }

                                    ?>
                                  <tr>
                                    <td align="right"  valign="middle" nowrap="nowrap"><strong><?= $data['lang']['TOTAL']; ?> :</strong></td>
                                    <td align="center" valign="middle"><strong><?php echo $this->utils->number_format($nombre_retrait_total);?></strong></td>
                                    <td align="right"  valign="middle"><strong><?php echo $this->utils->number_format($montant_retrait_total);?></strong></td>
                                    <td align="right"  valign="middle"><strong><?php echo $this->utils->number_format($commision_total);?></strong></td>
                                    <td align="center" valign="middle" bgcolor="#CCCCCC"><strong><?php echo $this->utils->number_format($nombre_total);?></strong></td>
                                    <td align="right"  valign="middle" bgcolor="#CCCCCC"><strong><?php echo $this->utils->number_format($montant_total);?></strong></td>
                                    <td align="center" valign="middle"><strong><?php echo 0; ?></strong></td>
                                  </tr>
                             </table>
                      <div class="box-body margin-bottom">
                            <div class="col-xs-6">
                                <form action="<?= ROOT ?>reporting/printbordereauretraitExcel" method="post" name="form2" target="_blank">
                                    <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                    <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                    <input type="hidden" name="agence" id="agence" value="<?php echo $this->utils->securite_xss($_POST['agence']); ?>" />
                                    <input name="XSL" type="hidden" value="XSL" />
                                    <button name="XSL" type="submit" value="XSL" class="btn btn-default text-gray-new" title="<?= $data['lang']['export_pdf']; ?>">
                                        <i class="fa fa-4x fa-file-excel-o"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-xs-6">
                            
                            <form action="<?= ROOT ?>reporting/printbordereauretrait" method="post" name="form2" target="_blank">
                                <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                <input type="hidden" name="agence" id="agence" value="<?php echo $this->utils->securite_xss($_POST['agence']); ?>" />
                                <input name="PDF" type="hidden" value="PDF" />
                                <button name="PDF" type="submit" value="PDF" class="btn btn-default text-gray-new" title="<?= $data['lang']['export_pdf']; ?>">
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