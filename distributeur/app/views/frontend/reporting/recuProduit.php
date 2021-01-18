<?php 
$data['recu'];
$data['date'];
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


th, td
{
    padding: 4px 10px;
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
            <td align="left" valign="middle" style="font-style:italic; color:#CCC;"><p><?= $data['lang']['historique_transaction']; ?> du <?php echo $this->utils->date_fr4($data['date']); ?></p></td>
            <td>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </p>
             </td>
             <td align="right" valign="middle" style="font-style:italic; color:#CCC;"><p><?php echo date('d-m-Y H:i:s'); ?></p></td>
        </tr>
    </table>
</page_header> 


<table width="642" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr align="center" valign="top" nowrap="nowrap">
        <td width="8%" align="left" valign="middle"><img src="<?= __DIR__.'/../../../../assets/plugins/images/postecash-black.png'?>" width="170" height="53" /></td>
        <td valign="middle" class="txt_form1">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $data['lang']['historique_transaction']; ?> du <?php echo $this->utils->date_fr4($data['date']); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
  </tr>
</table>

<br/>
<table width="642" border="1" align="center" cellpadding="20" cellspacing="0">
  <tr align="center" valign="top">
    	<td width="11%"><strong><?= $data['lang']['date']; ?></strong></td>
        <td width="9%"><strong><?= $data['lang']['numero']; ?></strong></td>
        <td width="13%"><strong><?= $data['lang']['tel_mobile']; ?></strong></td>
        <td width="11%"><strong><?= $data['lang']['produit']; ?></strong></td>
        <td width="11%"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
        <td width="11%"><strong><?= $data['lang']['frais']; ?></strong></td>
        <td width="11%"><strong><?= $data['lang']['montant_ttc']; ?></strong></td>
        <td width="11%"><strong><?= $data['lang']['effectuer_par']; ?></strong></td>
        <td width="12%"><strong><?= $data['lang']['agence']; ?></strong></td>
  </tr>
    <?php 
	 $total=0;
	 $totalttc=0;
	 $nb=0;
	 $com=0;
   
   foreach($data['recu'] as $row_transact)
   { 
	   $montant_ttc=$row_transact['montant']+$row_transact['commission'];
	   ?>
			  
			  <tr align="center" valign="middle">
					<td><?php echo $this->utils->date_fr4($row_transact['date_transaction']); ?></td>
					<td><?php echo $row_transact['num_transac']; ?></td>
					<td align="right"><?php echo $this->utils->truncate_carte($row_transact['telephone']); ?></td>
					<td align="right"><?php echo $row_transact['label']; ?></td> 
					<td align="right"><?php echo $this->utils->number_format($row_transact['montant']);?></td>
					<td align="right"><?php echo $this->utils->number_format($row_transact['commission']); ?></td>
					<td align="right"><?php echo $this->utils->number_format($montant_ttc); ?></td>
					<td align="left"><?php echo $row_transact['prenom'].' '.$row_transact['nom']; ?></td>
					<td align="left"><?php echo $row_transact['nom_agence']; ?></td>
			  </tr>
	  <?php 
			  $total+= $row_transact['montant']; 
			  $nb+= 1; 
			  $com+= $row_transact['commission'];
			  $totalttc+= $montant_ttc; 
  } ?>
  
</table>
<br/>
<table width="40%" border="1" align="center" cellpadding="10" cellspacing="2" class="table_form">
      <tr class="txt_form1">
            <td width="20%" align="center" valign="top">Montant Total </td>
            <td width="21%" align="center" valign="top">Total Commission </td>
            <td width="24%" align="center" valign="top">Montant Total TTC </td>
            <td width="29%" align="center" valign="top">Nombre de Transactions </td>
      </tr>
      <tr>
            <td align="center" valign="top" bgcolor="#FFFFFF"><strong><?php echo $this->utils->number_format($total);?></strong></td>
            <td align="center" valign="top" bgcolor="#FFFFFF"><strong><?php echo $this->utils->number_format($com);?></strong></td>
            <td align="center" valign="top" bgcolor="#FFFFFF"><strong><?php echo $this->utils->number_format($totalttc);?></strong></td>
            <td align="center" valign="top" bgcolor="#FFFFFF"><strong><?php echo $nb;?></strong></td>
      </tr>
</table>

</page>