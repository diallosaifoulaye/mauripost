<!-- HEADER DE LA PAGE -->
<?php
  $dates = $data['dates'];
  $transact = $data['transact'];
  $produit = $data['produit'];
  $allAgences = $data['allAgences'];
  $date_debut = $data['date1'];
  $date_fin = $data['date2']; 
?>
<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'tableau_bord_general'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['tableau_bord_general']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">
						                <?= $data['lang']['tableau_bord_general']; ?>: du <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datedeb'])); ?> au <?php echo $this->utils->date_fr2($this->utils->securite_xss($_POST['datefin'])); ?>
                        </h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive">
                           <?php if($transact > 0){ ?>
                             <table width="80%" border="1" align="center" cellpadding="5" cellspacing="0" style="font-size:16px">
                                      <tr>
                                        <td width="14%" rowspan="2" align="center" valign="middle"><strong><span class="txt_form1"><?= $data['lang']['Bureaux']; ?></span></strong></td>
                                        <td colspan="3" align="center" valign="middle"><strong><span class="txt_form1"><?php echo $this->utils->getNomService($produit); ?></span></strong></td>
                                        <td width="23%" align="center" valign="middle"><strong><span class="txt_form1"><?= $data['lang']['ANNULATION']; ?></span></strong></td>
                                      </tr>
                                      <tr>
                                        <td width="8%" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Nombre']; ?></strong></td>
                                        <td width="14%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                        <td width="17%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['commission_transac']; ?></strong></td>
                                        <td align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['(Montant)']; ?></strong></td>
                                      </tr>
                                      <?php         
                                      $montant_total = 0;
                                      $nombre_total = 0;
                                      $commision_total = 0;
                                      foreach($allAgences as $row_rs_resultat)
                                      { 
                                        $idagence = $row_rs_resultat['rowid'];
                                        $label = $row_rs_resultat['agence'];
                                        
                                        $nombre = $this->utils->nbretableauBordParDate($date_debut, $date_fin, $produit, $idagence);
                                        $montant = $this->utils->mttableauBordParDate($date_debut, $date_fin, $produit, $idagence);
                                        
                                        if($produit==20) $commision = 300 * $nombre;
                                        else $commision = $this->utils->commissiontableauBordParDate($date_debut, $date_fin, $produit, $idagence);
                                        
                                        $montant_total+= $montant;
                                        $nombre_total+= $nombre;
                                        if($produit==20) $commision_total = 300 * $nombre_total;
                                        else $commision_total+= $commision;
                                              
                                      ?>
                                      <tr>
                                        <td width="14%" align="left" valign="middle"><?php echo $label; ?></td>
                                        <td align="center" valign="middle"><?php echo $this->utils->number_format($nombre);?></td>
                                        <td align="right" valign="middle"><?php echo $this->utils->number_format($montant);?></td>
                                        <td align="right" valign="middle"><?php echo $this->utils->number_format($commision); ?></td>
                                        <td align="center" valign="middle"><?php echo 0; ?></td>
                                      </tr>
                                      <?php 
                                      
                                      }
                                       ?>
                                      <tr>
                                        <td align="right" valign="middle"><strong><?= $data['lang']['TOTAL']; ?> :</strong></td>
                                        <td align="center" valign="middle"><strong><?php echo $this->utils->number_format($nombre_total);?></strong></td>
                                        <td align="right" valign="middle"><strong><?php echo $this->utils->number_format($montant_total);?></strong></td>
                                        <td align="right" valign="middle"><strong><?php echo $this->utils->number_format($commision_total);?></strong></td>
                                        <td align="center" valign="middle"><strong><?php echo 0; ?></strong></td>
                                      </tr>
                            </table>
                      <div class="box-body margin-bottom">
                            <div class="col-xs-6">
                            
                            </div>
                            <div class="col-xs-6">
                            
                            <form action="<?= ROOT ?>reporting/printdashboard" method="post" name="form2" target="_blank">
                                <input type="hidden" name="date1" id="date1" value="<?php echo $this->utils->securite_xss($_POST['datedeb']); ?>" />
                                <input type="hidden" name="date2" id="date2" value="<?php echo $this->utils->securite_xss($_POST['datefin']); ?>" />
                                <input type="hidden" name="produit" id="produit" value="<?php echo $this->utils->securite_xss($_POST['produit']); ?>" />
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