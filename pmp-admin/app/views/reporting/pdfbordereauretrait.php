<?php 
  $dates = $data['dates'];
  //$transact = $data['transact'];
  $bureau = $data['agence'];
  $date1 = $data['date1'];
  $date2 = $data['date2'];
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
            <p><?= $data['lang']['bordereau_retrait']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?></p>
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
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $data['lang']['bordereau_retrait']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
  </tr>
</table>

<br/>

<table width="80%" border="1" align="center" cellpadding="5" cellspacing="0" style="font-size:16px">
    <tr>
      <td width="14%" rowspan="2" align="center" valign="middle"><strong><?= $data['lang']['date']; ?></strong></td>
      <td colspan="3" align="center" valign="middle"><strong><?= $data['lang']['RETRAIT_TIERS']; ?></strong></td>
      <td colspan="2" align="center" valign="middle" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['RETRAIT_TITULAIRE(CASHOUT)']; ?></strong></td>
      <td width="23%" align="center" valign="middle"><strong><?= $data['lang']['ANNULATION']; ?></strong></td>
    </tr>
    <tr>
      <td width="8%" align="center"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['Nombre']; ?></strong></td>
      <td width="14%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
      <td width="17%" align="right"  valign="top" nowrap="nowrap"><strong><?= $data['lang']['commission_transac']; ?></strong></td>
      <td width="8%" align="center"  valign="top" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['Nombre']; ?></strong></td>
      <td width="16%" align="right"  valign="top" nowrap="nowrap" bgcolor="#CCCCCC"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
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
            ?>
    <tr>
        <td width="14%" align="center" valign="middle"><?php echo $this->utils->date_fr2($date_transaction); ?></td>
        <td align="center" valign="top">
        <?php 
          $nombre_retrait = $this->utils->nombreRetraitTiers($date_transaction, $bureau);
          $nombre_retrait_total+=$nombre_retrait;
          echo $this->utils->number_format($nombre_retrait);
        ?>
        </td>
        <td align="right" valign="top">
        <?php 
          $montant_retrait = $this->utils->montantRetraitTiers($date_transaction, $bureau);
          $montant_retrait_total+=$montant_retrait;
          echo $this->utils->number_format($montant_retrait);
        ?>
        </td>
        <td align="right" valign="top">
        <?php 
          $commision = 300 * $nombre_retrait;
          $commision_total+=$commision;
          echo $this->utils->number_format($commision);
        ?>
        </td>
        <td align="center" valign="top" bgcolor="#CCCCCC">
        <?php 
          $nombre = $this->utils->nombreRetraitTitulaire($date_transaction, $bureau);
          $nombre_total+=$nombre;
          echo $this->utils->number_format($nombre);
        ?>
        </td>
        <td align="right" valign="top" bgcolor="#CCCCCC">
        <?php 
          $montant = $this->utils->montantRetraitTitulaire($date_transaction, $bureau);
          $montant_total+=$montant;
          echo $this->utils->number_format($montant);
        ?>
        </td>
        <td align="center" valign="middle"><?php echo 0; ?></td>
    </tr>
        <?php } ?>
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

</page>