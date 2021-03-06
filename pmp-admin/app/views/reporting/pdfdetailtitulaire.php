<?php 
$data['recu'];
$date1 = $data['date1'];
$date2 = $data['date2'];
$total = 0;
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
            <p><?= $data['lang']['detail_retrait_titulaire']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?></p>
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
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $data['lang']['detail_retrait_titulaire']; ?> du <?php echo $this->utils->date_fr2($date1); ?> au <?php echo $this->utils->date_fr2($date2); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
  </tr>
</table>

<br/>
<table width="642" border="1" align="center" cellpadding="20" cellspacing="0" style="font-size:12px">
  <tr align="center" valign="top" >
    	
          <th style="font-weight:bold"><?= $data['lang']['date_paiement']; ?></th>
          <th style="font-weight:bold"><?= $data['lang']['numero_transaction']; ?></th>
          <th style="font-weight:bold"><?= $data['lang']['bureau_payeur']; ?></th>
          <th style="font-weight:bold"><?= $data['lang']['montant_sans_ttc']; ?></th>
          <th style="font-weight:bold"><?= $data['lang']['nom_beneficiaire']; ?></th>
  </tr>
    <?php 
   foreach($data['recu'] as $row_transact)
   {
       $total = $total + $row_transact['montant'];
	   ?>
        <tr align="center" valign="middle">
            <td><?php echo $this->utils->date_fr4($row_transact['date_transaction']); ?></td>
            <td><?php echo $row_transact['num_transac']; ?></td>
            <td align="right"><?php echo $row_transact['label']; ?></td> 
            <td align="right"><?php echo $this->utils->number_format($row_transact['montant']);?></td>
            <td align="right"><?php echo $this->utils->nomBeneficiareParCarte($row_transact["fk_carte"]); ?></td>
        </tr>
<?php } ?>
    <tfoot>
    <tr style="font-weight: bold">
        <td colspan="3" align="right">TOTAL TTC: </td>
        <td colspan="2" align="left"><?= $this->utils->number_format($total+$total_com); ?> F CFA </td>
    </tr>
    </tfoot>
</table>

</page>