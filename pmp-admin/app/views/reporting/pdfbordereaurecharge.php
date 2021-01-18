<?php 
$data['recu'];
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
            <p><?= $data['lang']['bordereau_rechargement']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?></p>
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
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $data['lang']['bordereau_rechargement']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
  </tr>
</table>

<br/>

<table width="642" align="center" cellpadding="10" cellspacing="0" border="1" style="font-size:12px">
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
         
          foreach($data['recu'] as $row_rs_resultat)
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
          <td  align="right"  valign="middle" nowrap="nowrap"><strong><?= $data['lang']['TOTAL']; ?>  : </strong></td>
          <td  align="center"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($nombre_total); ?></strong></td>
          <td  align="right"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($montant_total); ?></strong></td>
          <td  align="right"  valign="middle" nowrap="nowrap"><strong><?php echo $this->utils->number_format($commission_total); ?></strong></td>
          <td  align="center"  valign="middle" nowrap="nowrap"><strong><?php echo $annulation_total; ?></strong></td>
        </tr>
      </tfoot>
</table>

</page>