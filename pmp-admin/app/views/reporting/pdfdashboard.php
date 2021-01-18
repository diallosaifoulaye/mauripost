<?php 
  $dates = $data['dates'];
  $transact = $data['transact'];
  $produit = $data['produit'];
  $allAgences = $data['allAgences'];
  $date_debut = $data['date1'];
  $date_fin = $data['date2']; 
?>
<style type="text/css">
body,td,th 
{
	font-family: "Times New Roman", Times, serif;
}
body 
{
	margin-left: 10px;
	margin-top: 1px;
	margin-right: 10px;
	margin-bottom: 1px;
	text-align:center;
}	
table 
{
	 border-collapse:collapse;
	 font-size:10px;
}
th, td#bc 
{
 	border:1px solid black;
}
caption 
{
 	font-weight: lighter;
}

</style>
<link rel="stylesheet" href="../../css/style.css">
<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm">

<page_header>
    <table width="642">
        <tr>
            <td align="left" valign="middle" style="font-style:italic; color:#CCC;">
            <p><?= $data['lang']['tableau_bord_general']; ?> du <?php echo $this->utils->date_fr2($date_debut); ?> au <?php echo $this->utils->date_fr2($date_fin); ?></p>
            </td>
            <td>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </p>
             </td>
             <td align="right" valign="middle" style="font-style:italic; color:#CCC;"><p><?php echo date('d-m-Y H:i:s'); ?></p></td>
        </tr>
    </table>
</page_header> 


<table width="642" border="0" align="center" cellpadding="0" cellspacing="0" style="font-weight:bold; font-size:16px">
  <tr align="center" valign="top" nowrap="nowrap">
        <td width="244" align="left" valign="middle"><img src="<?= __DIR__.'/../../../assets/images/mauripost.png'?>" class="img-responsive image" width="250"  height="78" /></td>
        <td width="398" valign="middle">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $data['lang']['tableau_bord_general']; ?> du <?php echo $this->utils->date_fr2($date_debut); ?> au <?php echo $this->utils->date_fr2($date_fin); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
  </tr>
</table>

<br/>

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

</page>