<!-- HEADER DE LA PAGE -->
<?php

  $allAgences = $data['allAgence'];
  $typeagence = $data['typeAgence'];

  $allService = $data['allService'];
  $nb_transaction_mois = $data['nbreTransactionMensuel'];
  $nb_carte_mois = $data['nbreCarteMensuel'];
  $paiement_mois = $data['montantPaimentMensuel'];
  $carteacarte_mois = $data['transfertCarteAcarteMensuel'];
  $cartetoch_mois = $data['transfertCartetocashMensuel'];
  $vente_mois = $data['venteCarteMensuel'];
  $rs_service = $data['serviceRecharge'];

  $titre='';
  $tab=array();
  $tab[]=array("Mois","Nombre Transactions"); 


  $tabC=array();
  $tabC[]=array("Mois","Nombre Carte"); 
?>
<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'visualiser'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>
<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->
    <section class="content-header" >
        <h1>
            <?= $data['lang']['tableau_bord']; ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['tableau_bord']; ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                    
                            <div class="table-responsive">
                                     <div class="col-lg-4">
                                            <form class="form-horizontal" method="POST" action="<?= ROOT ?>reporting/tableaudeboard">
                                                <fieldset style="display: block;" class="scheduler-border">
                                                    <legend class="scheduler-border"><?= $data['lang']['filtrer'] ; ?></legend>
                                                		<div class="form-group">
                                                            <label for="region" class="col-sm-5 control-label">
                                                            <?= $data['lang']['produit'] ; ?> :
                                                            </label>
                                                            <div class="col-sm-7">
                                                                <select class="form-control select2" name="produit" style="width: 100%;" id="produit">
                                                                    <option selected="selected" value="0"><?= $data['lang']['select_service'] ; ?></option>
                                                                    <?php foreach($allService as $serv){ ?>
                                                                        <option value="<?= $serv['rowid']; ?>"><?= $serv['label']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="region" class="col-sm-5 control-label">
                                                            <?= $data['lang']['type_agence'] ; ?> :
                                                            </label>
                                                            <div class="col-sm-7">
                                                                <select class="form-control" name="type_agence" style="width: 100%;" id="region" onChange="request06(this);">
                                                                    <option selected="selected" value="0"><?= $data['lang']['select_type_agence'] ; ?></option>
                                                                    <?php foreach($typeagence as $row_typeagence){ ?>
                                                                        <option value="<?= $row_typeagence['idtype_agence']; ?>"><?= $row_typeagence['libelle']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                		<div class="form-group">
                                                            <label for="region" class="col-sm-5 control-label">
                                                            <?= $data['lang']['nom_agence'].' :' ; ?>
                                                            </label>
                                                            <div class="col-sm-7">
                                                                <select class="form-control select2" name="agence" style="width: 100%;" id="departement">
                                                                   
                                                                </select> 
                                                                
                                                                    
                                                            </div>
                                                        </div>             
                                                		<div class="form-group">
                                                            <label for="from" class="col-sm-5 control-label"><?= $data['lang']['date_deb'] ; ?> (*): </label>
                                                            <div class="col-sm-7">
                                                              <input type="text" name="datedeb" required class="form-control" id="from" placeholder="<?= $data['lang']['entrer_date_deb'] ; ?>">
                                                            </div>
                                                        </div>
                                                		<div class="form-group">
                                                            <label for="to" class="col-sm-5 control-label"><?= $data['lang']['date_fin'] ; ?> (*): </label>
                                                            <div class="col-sm-7">
                                                                <input type="text" name="datefin" required class="form-control" id="to" placeholder="<?= $data['lang']['entrer_date_fin'] ; ?>">
                                                            </div>
                                                        </div>
                                                		<div class="form-group">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <button type="submit" name="search" value="form1" class="btn btn-warning"><?= $data['lang']['valider'] ; ?></button>
                                                            </div>
                                                        </div>
                                              </fieldset>
                                            </form>
                                     </div>
                                     <div class="col-lg-8 table-responsive">
                                          <fieldset style="display: block;" class="scheduler-border">
                                              <legend class="scheduler-border"><?= $data['lang']['resultat'] ; ?></legend>
                                             
                                              <?php if((isset($_POST["search"])) && ($_POST["search"] == "form1")) {
                        												$date_debut = $this->utils->securite_xss($_POST['datedeb']);
                        												$date_fin = $this->utils->securite_xss($_POST['datefin']);
                        												$service = $this->utils->securite_xss($_POST['produit']);
                        												$agency = $this->utils->securite_xss($_POST['agence']);
                        												$statistique = $this->utils->getTransactionStat($date_debut, $date_fin, $service, $agency);	
                        											  ?>
                                              
                                              <div class="row">
                                              	<div class="table-responsive col-lg-8">
                                              <table class="table table-bordered table-hover">
                                              
                                                      <tr bgcolor="#CCCCCC">
                                                          <td><strong><?= $data['lang']['service'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                      </tr>
                                                        <?php 
                            														$total_montant=0;
                            														$total_cm=0;
                            														$total_ttc=0;
                            														foreach($statistique as $row_statistique) {
                            															$montant_ttc= $row_statistique['montant']+$row_statistique['frais'];
                            															$total_montant+=$row_statistique['montant'];
                            															$total_cm+=$row_statistique['frais'];
                            															$total_ttc+=$montant_ttc;
                            															$tab1[]=array($row_statistique['label'],$montant_ttc);
                            													    ?>
                                                        <tr>
                                                          <td><?php echo $row_statistique['label']; ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_statistique['montant']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_statistique['frais']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($montant_ttc); ?></td>
                                                        </tr>
                                                        <?php }?>
                                                        <tr bgcolor="#CCCCCC">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total'] ; ?>: </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_montant); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_cm); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_ttc); ?></strong>
                                                            </td>
                                                        </tr>
                                          	   </table>
                                               </div>
                                               <?php }?>
                                          	   <div  class="table-responsive col-lg-6">
                                               		<div id="chartdiv"></div>
                                                </div>
                                              </div> 
                                          </fieldset>  
                                     </div>
                            </div>
                            
                            <div class="table-responsive">
                            	<div  class="table-responsive col-lg-12">
                            			<fieldset style="display: block;" class="scheduler-border">
                                         <legend class="scheduler-border"><?= $data['lang']['recharge_mensuel_service'] ; ?></legend>
            									<div class="col-lg-6">
                                               <div class="table-responsive">
                                              <table class="table table-bordered table-hover">
                                                        <?php 
                              														$total_service=0; 
                              														$total_frais=0; 
                              														$total_ttc=0;
                              														$stack = array();  
                              														$i=1;
                              														$recup_mtt1 = array();
                              														$recup_mtt2 = array();
                              														$recup_mtt3 = array();
                              														
                              														foreach($rs_service as $row_rs_service) 
                              														{
                              														 
                              														  $tab_temp = array();
                              														  $rechargeransact = $this->utils->montantRechargementMensuel($row_rs_service['rowid']);
                              														  
                              														  //recuperation des montants 
                              														  
                              														  
                              														  foreach($rechargeransact as $r){
                              															      array_push($tab_temp, $r['mt']);
                              																  if(!in_array($r['mois'].'-'.$r['annee'], $stack)) {
                              																	array_push($stack, $r['mois'].'-'.$r['annee']);
                              																 }
                              															    
                              															  }
                              															  if($i==1) $recup_mtt1 = $tab_temp;
                              															  if($i==2) $recup_mtt2 = $tab_temp;
                              															  if($i==3) $recup_mtt3 = $tab_temp;
                              															 $i++;
														   
                                                         ?>
                                                        <tr bgcolor="#999999">
                                                          <td align="center" colspan="4"><strong><?php echo $row_rs_service['label']; ?></strong></td>
                                                        </tr>
                                                        <tr style="background-color:#EDEDED">
                                                          <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                        </tr>
                                                        <?php 
                              														$recharge_total=0;
                              														$frais_total=0;
                              														$ttc_total=0;
                              														foreach($rechargeransact as $row_rechargeransact) 
                              														{
                              														  	
                              														?>
                                                                <tr>
                                                                  <td><?php echo $this->utils->moisLettre($row_rechargeransact['mois']).' '.$row_rechargeransact['annee']; ?></td>
                                                                  <td align="right"><?php echo $this->utils->number_format($row_rechargeransact['mt']); ?></td>
                                                                  <td align="right"><?php echo $this->utils->number_format($row_rechargeransact['frais']); ?></td>
                                                                  <td align="right"><?php echo $this->utils->number_format($row_rechargeransact['mt']+$row_rechargeransact['frais']); ?></td>
                                                                </tr>
                                                                <?php 
                                                                $recharge_total+=$row_rechargeransact['mt'];
                                    																$frais_total+=$row_rechargeransact['frais'];
                                    																$ttc_total=$recharge_total+$frais_total;
                                    														}?>
														                            <tr bgcolor="#DDDDDD">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total'].' '.$row_rs_service['label'];?> : </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?= $this->utils->number_format($recharge_total); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?= $this->utils->number_format($frais_total); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?= $this->utils->number_format($ttc_total); ?></strong>
                                                            </td>
                                                        </tr>
                                  														<?php 
                                  															$total_service+=$recharge_total;
                                  															$total_frais+=$frais_total; 
                                  															$total_ttc=$total_service+$total_frais; 
                                  														 }
                                  														 // print_r($recup_mtt1);print_r($recup_mtt2);print_r($recup_mtt3);die;
                                  														 ?>
                                                        <tr bgcolor="#CCCCCC">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_service); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_frais); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_ttc); ?></strong>
                                                            </td>
                                                        </tr>
                                          	   </table>
                                          	   </div>
                                        
                        		</div>
                                                <div class="col-lg-6">
                                                      <div id="container" style="width:100%; height:600px"></div>
                                                </div>
                                		</fieldset>
                                 </div>	
        					</div>
                            
                            <div class="table-responsive">
            					<div class="col-lg-6">
                                    <fieldset style="display: block;" class="scheduler-border">
                                      <legend class="scheduler-border"><?= $data['lang']['nb_transaction_mensuel']; ?></legend>
                                      <div class="row">
                                          <div class="col-lg-4 table-responsive">
                                            <table class="table table-bordered table-hover">
                                              <thead>
                                                  <tr style="background-color:#EDEDED">
                                                      <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                      <td align="center"><strong><?= $data['lang']['number']; ?></strong></td>
                                                  </tr>
                                              </thead>
                
                                              <tbody>
                                                <?php foreach($nb_transaction_mois as $row_nb_transaction_mois) {
                      													$tab[]=array($this->utils->moisLettre($row_nb_transaction_mois['mois']).' '.$row_nb_transaction_mois['annee'],$row_nb_transaction_mois['nbre']);
                      														?>
                                                    <tr>
                                                      <td><?php echo $this->utils->moisLettre($row_nb_transaction_mois['mois']).' '.$row_nb_transaction_mois['annee']; ?></td>
                                                      <td align="center"><?php echo $row_nb_transaction_mois['nbre']; ?></td>
                                                    </tr>
                                                    <?php }?>
                                              </tbody>
                                           </table>
                                           </div>
                                           
                                           <div class="col-lg-8 table-responsive">
                                                <?php //include_once( __DIR__.'/graphe.php'); ?>
                                                <script src="<?= WEBROOT ?>assets/graphe/jsapi.js"></script>
                                                <script type="text/javascript">
                                                      google.load("visualization", "1", {packages:["corechart"]});
                                                      google.setOnLoadCallback(drawChart);
                                                      function drawChart() {
                                                        var data = google.visualization.arrayToDataTable([
                                                          <?php 
                                                      echo "['Task', 'Hours per Day'],";
                                                      $taille=sizeof($tab);
                                                      for($i=0;$i<$taille;$i++)
                                                      {
                                                        if($i==$taille-1)
                                                        echo "['".$tab[$i][0]."',     ".intval($tab[$i][1])."]";
                                                        else
                                                        echo "['".$tab[$i][0]."',     ".intval($tab[$i][1])."],";
                                                       }
                                                      ?>
                                                        ]);

                                                        var options = {
                                                           title: <?php print "'".$titre."'" ?>,
                                                          pieHole: 0.4,
                                                        pieSliceTextStyle: {color: '#000000', fontSize:14}
                                                        };
                                                        var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
                                                        chart.draw(data, options);
                                                      }
                                                </script>
                                    			<div id="donutchart" style="width:100%; height:280px;"></div>
                                           </div>
                                           
                                       </div>
                                    </fieldset>
                        		</div><!-- /.col -->
                                
                                <div class="col-lg-6">
                                        <fieldset style="display: block;" class="scheduler-border">
                                              <legend class="scheduler-border"><?= $data['lang']['carte_to_carte_mensuel']; ?></legend>
                                              <div class="table-responsive">
                                              <table class="table table-bordered table-hover">
                                                  <thead>
                                                      <tr style="background-color:#EDEDED">
                                                          <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc']; ?></strong></td>
                                                      </tr>
                                                  </thead>
                    
                                                  <tbody>
                                                        <?php 
                              														$total_cartetocarte=0;
                              														$total_commis=0;
                              														$total_cartetocartettc=0;
                              														foreach($carteacarte_mois as $row_carteacarte_mois) {?>
                                                        <tr>
                                                          <td><?php echo $this->utils->moisLettre($row_carteacarte_mois['mois']).' '.$row_carteacarte_mois['annee']; ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_carteacarte_mois['mt']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_carteacarte_mois['frais']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_carteacarte_mois['mt']+$row_carteacarte_mois['frais']); ?></td>
                                                        </tr>
                                                        <?php 
                              														$total_cartetocarte+=$row_carteacarte_mois['mt'];
                              														$total_commis+=$row_carteacarte_mois['frais'];
                              														$total_cartetocartettc=$total_cartetocarte+$total_commis;
                              														}?>
                                                        
                                                        <tr bgcolor="#CCCCCC">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_cartetocarte); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_commis); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_cartetocartettc); ?></strong>
                                                            </td>
                                                        </tr>
                                                  </tbody>
                                          	   </table>
                                          	   </div>
                                        </fieldset>
                        		</div>
                               
                                     
                                        
        					</div>
                            
                            <div class="table-responsive">
                            <div class="col-lg-12 table-responsive">
                            		<fieldset style="display: block;" class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['vente_carte']; ?></legend>
                             		   <div class="row table-responsive">
                                       		<div class="col-lg-6 table-responsive">
                                              	<table class="table table-bordered table-hover">
                                                  <thead>
                                                      <tr style="background-color:#EDEDED">
                                                          <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                          <td align="center"><strong><?= $data['lang']['number']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc']; ?></strong></td>
                                                      </tr>
                                                  </thead>
                    
                                                  <tbody>
                                                        <?php 
                              														$total_vente=0;
                              														$total_c=0;
                              														$nb_total=0;
                              														$total_cttc=0;
                              														foreach($vente_mois as $row_vente_mois) {
                              															
                              															?>
                                                        <tr>
                                                          <td><?php echo $this->utils->moisLettre($row_vente_mois['mois']).' '.$row_vente_mois['annee']; ?></td>
                                                          <td align="center"><?php echo $this->utils->number_format($row_vente_mois['nbre']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_vente_mois['mt']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_vente_mois['frais']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_vente_mois['mt']+$row_vente_mois['frais']); ?></td>
                                                        </tr>
                                                        <?php 
                            														$total_vente+=$row_vente_mois['mt'];
                            														$total_c+=$row_vente_mois['frais'];
                            														$nb_total+=$row_vente_mois['nbre'];
                            														
                            														$total_cttc=$total_vente+$total_c;
                            														$tabC[]=array($this->utils->moisLettre($row_vente_mois['mois']).' '.$row_vente_mois['annee'],$row_vente_mois['mt']+$row_vente_mois['frais']);
                            														}?>
                                                        
                                                        <tr bgcolor="#CCCCCC">
                                                           <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                            </td>
                                                           <td  align="center" valign="middle" >
                                                            	<strong><?= $nb_total;?></strong>
                                                            </td>
                                                            
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_vente); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_c); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_cttc); ?></strong>
                                                            </td>
                                                        </tr>
                                                  </tbody>
                                          	   </table>
                                        	</div>
                                			<div class="col-lg-6">
                                        	<?php// include_once('graphe_carte.php'); ?>
                                          <script src="<?= WEBROOT ?>assets/graphe/loader.js"></script>
                                          <script type="text/javascript">
                                            google.charts.load("current", {packages:["corechart"]});
                                            google.charts.setOnLoadCallback(drawChart);
                                            function drawChart() {
                                              var data = google.visualization.arrayToDataTable([
                                                <?php 
                                            echo "['Task', 'Hours per Day'],";
                                            $taille=sizeof($tabC);
                                            for($i=0;$i<$taille;$i++)
                                            {
                                              if($i==$taille-1)
                                              echo "['".$tabC[$i][0]."',     ".intval($tabC[$i][1])."]";
                                              else
                                              echo "['".$tabC[$i][0]."',     ".intval($tabC[$i][1])."],";
                                             }
                                            ?>
                                              ]);

                                              var options = {
                                                title: '',
                                                is3D: true,
                                              };

                                              var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                                              chart.draw(data, options);
                                            }
                                          </script>
                                            <div id="piechart_3d" style="width:100%; height:280px;"></div>
                                 		 </div>
                                       </div>  
                                	</fieldset>
                            </div>
        					</div>
                            
                            <div class="table-responsive">
                            			<div class="col-lg-6 table-responsive">
                                        	<fieldset style="display: block;" class="scheduler-border">
                                              <legend class="scheduler-border"><?= $data['lang']['paiement_mensuel']; ?></legend>
                                              <div class="table-responsive">
                                              <table class="table table-bordered table-hover" >
                                                  <thead>
                                                      <tr style="background-color:#EDEDED">
                                                          <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc']; ?></strong></td>
                                                      </tr>
                                                  </thead>
                    
                                                  <tbody>
                                                        <?php 
                            														$total_paiement=0;
                            														$total_com=0;
                            														foreach($paiement_mois as $row_paiement_mois) {?>
                                                        <tr>
                                                          <td><?php echo $this->utils->moisLettre($row_paiement_mois['mois']).' '.$row_paiement_mois['annee']; ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_paiement_mois['mt']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_paiement_mois['frais']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_paiement_mois['mt']+$row_paiement_mois['frais']); ?></td>
                                                        </tr>
                                                        <?php 
                              														$total_paiement+=$row_paiement_mois['mt'];
                              														$total_com+=$row_paiement_mois['frais'];
                              														$total_paiementttc=$total_paiement+$total_com;
                              														}?>
                                                        
                                                        <tr bgcolor="#CCCCCC">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_paiement); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_com); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_paiementttc); ?></strong>
                                                            </td>
                                                        </tr>
                                                  </tbody>
                                          	   </table>
                                          	   </div>
                                        </fieldset>
                                        </div>
                                        
                                        <div class="col-lg-6 table-responsive">
                                        <fieldset style="display: block;" class="scheduler-border">
                                              <legend class="scheduler-border"><?= $data['lang']['transfert_mensuel']; ?></legend>
                                              <div class="table-responsive">
                                              <table class="table table-bordered table-hover">
                                                  <thead>
                                                      <tr style="background-color:#EDEDED">
                                                          <td><strong><?= $data['lang']['mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc']; ?></strong></td>
                                                      </tr>
                                                  </thead>
                    
                                                  <tbody>
                                                        <?php 
                              														$total_transfert=0;
                              														$total_commiss=0;
                              														foreach($cartetoch_mois as $row_cartetoch_mois) {?>
                                                        <tr>
                                                          <td><?php echo $this->utils->moisLettre($row_cartetoch_mois['mois']).' '.$row_cartetoch_mois['annee']; ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_cartetoch_mois['mt']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_cartetoch_mois['frais']); ?></td>
                                                          <td align="right"><?php echo $this->utils->number_format($row_cartetoch_mois['mt']+$row_cartetoch_mois['frais']); ?></td>
                                                        </tr>
                                                        <?php 
                              														$total_transfert+=$row_cartetoch_mois['mt'];
                              														$total_commiss+=$row_cartetoch_mois['frais'];
                              														$total_transfertttc=$total_transfert+$total_commiss;
                              														}?>
                                                        
                                                        <tr bgcolor="#CCCCCC">
                                                            <td  align="right" valign="middle" >
                                                            	<strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_transfert); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_commiss); ?></strong>
                                                            </td>
                                                            <td align="right" valign="middle" >
                                                            	<strong><?php echo $this->utils->number_format($total_transfertttc); ?></strong>
                                                            </td>
                                                        </tr>
                                                  </tbody>
                                          	   </table>
                                          	   </div>
                                        </fieldset>
                        		</div>
                                        
                            </div>
                       
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>

<?php include(__DIR__.'/footer.php') ;?>
<!-- page script -->
<script>
    $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();
    });
</script>
<script>
        $(function() {
            $( "#from" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( "#to" ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( "#to" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                }
            });
        });
</script>
<script src="<?= WEBROOT ?>assets/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?= WEBROOT ?>assets/amcharts/amcharts.js"></script>
<script src="<?= WEBROOT ?>assets/amcharts/funnel.js"></script>
<script src="<?= WEBROOT ?>assets/amcharts/themes/light.js"></script>
<script>
var chart = AmCharts.makeChart( "chartdiv", {
  "type": "funnel",
  "theme": "light",
  "dataProvider": [ 
  <?php
   $taille=sizeof($tab1);
		  for($i=0;$i<$taille;$i++)
		  {
			  if($i==$taille-1)
			  echo "{
				 'title': '".$tab1[$i][0]."',
				'value': ".intval($tab1[$i][1])."
			  }"; 
  			  else
  				echo "{
				 'title': '".$tab1[$i][0]."',
				'value': ".intval($tab1[$i][1])."
			  },"; 
   }?>
   ],
  "balloon": {
    "fixedPosition": true
  },
  "valueField": "value",
  "titleField": "title",
  "marginRight": 240,
  "marginLeft": 50,
  "startX": -500,
  "depth3D": 100,
  "angle": 40,
  "outlineAlpha": 1,
  "outlineColor": "#FFFFFF",
  "outlineThickness": 2,
  "labelPosition": "right",
  "balloonText": "[[title]]: [[value]] [[description]]",
  "export": {
    "enabled": true
  }
} );
jQuery( '.chart-input' ).off().on( 'input change', function() {
  var property = jQuery( this ).data( 'property' );
  var target = chart;
  var value = Number( this.value );
  chart.startDuration = 0;

  if ( property == 'innerRadius' ) {
    value += "%";
  }

  target[ property ] = value;
  chart.validateNow();
} );
</script>

<script src="<?= WEBROOT ?>assets/js/oXHR.js"></script>
<script language = "JavaScript" type = "text/JavaScript">
     function request06(oSelect) {
            var value = oSelect.options[oSelect.selectedIndex].value;
            var xhr   = getXMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
                    readData06(xhr.responseXML);
                    //document.getElementById("loader").style.display = "none";
                } else if (xhr.readyState < 4) {
                    //document.getElementById("loader").style.display = "inline";
                }
            };
            xhr.open("POST", "<?= ROOT ?>reporting/req1", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("region=" + value);
        }
        function readData06(oData) {
            var nodes   = oData.getElementsByTagName("item");
            var oSelect = document.getElementById("departement");
            var oOption, oInner;
            oSelect.innerHTML = "";
            for (var i=0, c=nodes.length; i<c; i++) {
                oOption = document.createElement("option");
                oInner  = document.createTextNode(nodes[i].getAttribute("name"));
                oOption.value = nodes[i].getAttribute("id");

                oOption.appendChild(oInner);
                oSelect.appendChild(oOption);
            }
        }
</script>



<!--<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>-->


<script src="<?= WEBROOT ?>assets/js/jspaositra/highcharts.js"></script>
<script src="<?= WEBROOT ?>assets/js/jspaositra/data.js"></script>
<script src="<?= WEBROOT ?>assets/js/jspaositra/exporting.js"></script>


<script type="text/javascript">
$(function () {
    $('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Rechargement mensuel par service'
        },
        subtitle: {
            text: 'Source: paositra.mg'
        },
        xAxis: {
        
                categories: [
                <?php foreach($stack as $t){
					$t=explode("-",$t);
					?>
                    '<?php echo $this->utils->moisLettre($t[0])."-".$t[1]; ?>',
		             <?php } ?>
				 
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Montant (Ar)'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f} Ar</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: 'Recharge par Carte Kredivola',
            data: [<?php foreach($recup_mtt1 as $t){
					?>
                    <?php echo $t ?>, <?php } ?>]

        }, {
            name: 'Recharge par Espèce',
            data: [<?php foreach($recup_mtt2 as $t){
					?>
                    <?php echo $t ?>, <?php } ?>]

        }, {
            name: 'Recharge par Neosurf',
            data: [<?php foreach($recup_mtt3 as $t){
					?>
                    <?php echo $t ?>, <?php } ?>]

        }]
    });
});


</script>
</body>
</html>