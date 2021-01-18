    <?php
include(__DIR__ . '/../agentheader.php');
$nb_transaction_mois = $data['nbreTransactionMensuel'];
$vente_mois = $data['venteCarteMensuel'];
$tab=array();
$tab[]=array("Mois","Nombre Transactions"); 
$titre='';
$mois = array('01','02','03','04','05','06','07','08','09','10','11','12');
$courbe = $data['courbe'];
$annee = $data['annee'];
$bureau = $data['bureau'];
$rs_service11 = $data['serviceRecharge'];
$rs_service111 = $data['serviceRecharge1'];
$cartetoch_mois = $data['transfertCartetocashMensuel'];
$total_service=0; 
$total_frais=0; 
$total_nbre=0;
$stack = array();  
$i=1;
$tab_servie= array();
$tab_mt_mois =array();

?>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title"></h4> 
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 

                <ol class="breadcrumb">
                    <li><a href="#"><?= $data['lang']['Tableau_de_bord']; ?></a></li>
                    <li class="active"><?= $data['lang']['reporting']; ?></li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- ============================================================== -->
        <!-- Different data widgets -->
        <!-- ============================================================== -->
        <!--row -->

        <div class="row">
            <div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title"><?= $data['lang']['Nbre_Transaction']; ?></h3>
                    <ul class="list-inline two-part row">
                        <li><i class="ti-pulse text-info"></i></li>
                        <li class="text-right"><span class="" style="font-size: 20px;"><?= $this->utils->number_format($data['nbtransac']->nbtransac); ?></span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title"><?= $data['lang']['Chiffre_daffaires']; ?></h3>
                    <ul class="list-inline two-part">
                        <li><i class="icon-folder text-purple"></i></li>
                        <li class="text-right"><span class="" style="font-size: 20px;"><?= $this->utils->number_format($data['nbtransac']->ca); ?> XOF</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title"><?= $data['lang']['Totale_commission']; ?></h3>
                    <ul class="list-inline two-part">
                        <li><i class="icon-folder-alt text-danger"></i></li>
                        <li class="text-right"><span class="" style="font-size: 20px;"><?= $this->utils->number_format($data['nbtransac']->commission); ?> XOF</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title"><?= $data['lang']['Ma_commission_totale']; ?></h3>
                    <ul class="list-inline two-part">
                        <li><i class="ti-wallet text-success"></i></li>
                        <li class="text-right"><span class="" style="font-size: 20px;"><?= $this->utils->number_format($data['nbtransac']->macommission); ?> XOF</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-md-12 col-lg-6 col-sm-12 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title"><?= $data['lang']['Evolution_des_Transaction_par_Service_par_Mois']; ?></h3>

                    <?php   
                    foreach($data['service'] as $row_rs_service) 
                    {
                        $tab_temp = array();  
                        $recharge_total=0;
                        $frais_total=0;
                        $nbre_total=0;
                        foreach($mois as $row_mois)
                        {
                            $rechargeransact = $this->utils->montantServiceMensuel1($row_rs_service['rowid'],$row_mois,$annee,$bureau,$courbe);
                            if(sizeof($rechargeransact)>0)
                            {
                                foreach($rechargeransact as $row_rechargeransact) 
                                { 
                                    if($courbe==1) array_push($tab_temp, $row_rechargeransact['nbre']);
                                    else if($courbe==2) array_push($tab_temp, $row_rechargeransact['mt']);
                                    else if($courbe==3) array_push($tab_temp, $row_rechargeransact['frais']);
                                    if(!in_array($row_mois, $stack)) 
                                    {
                                        array_push($stack, $row_mois);
                                    }
                                    if($courbe==1) $this->utils->number_format($row_rechargeransact['nbre']);
                                    else if($courbe==2) $this->utils->number_format($row_rechargeransact['mt']);
                                    else if($courbe==3) $this->utils->number_format($row_rechargeransact['frais']);

                                    if($courbe==1) $nbre_total+=$row_rechargeransact['nbre'];
                                    else if($courbe==2) $recharge_total+=$row_rechargeransact['mt'];
                                    else if($courbe==3) $frais_total+=$row_rechargeransact['frais'];
                                }
                            }
                            else
                            {
                                array_push($tab_temp, 0);
                                if(!in_array($row_mois, $stack)) 
                                {
                                    array_push($stack, $row_mois);
                                }
                            }
                        }
                        $i++;
                        if($courbe==1) $total_nbre+=$nbre_total;
                        else if($courbe==2) $total_service+=$recharge_total; 
                        else if($courbe==3) $total_frais+=$frais_total; 
                        $tab_servie1[]=array($row_rs_service['label'],$tab_temp);
                    }
                    ?>
                    <div id="container" style="min-width: 100%; height: 329px; margin: 0 auto"></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 col-sm-6 col-xs-12">
                <div class="bg-theme-alt">
                    <div id="ct-daily-sales" class="p-t-30" style="height: 300px;padding: 0 !important;"></div>
                </div>
                <div class="white-box">
                    <div class="row">
                        <div class="col-xs-8">
                            <h2 class="m-b-0 font-medium" style="font-size: 17px;"><?= $data['lang']['Transaction_de_la_semaine']; ?></h2>
                            <?php 

                            $tab_servie = array();

                            foreach ($data['dd'] as $key) 
                            {
                                $nbre = $this->utils->getnbre1($key, $data['user']);
                                if (is_array($nbre))
                                {
                                    foreach ($nbre as $nbre1)
                                    {
                                        $nb=$nbre1['nbtransac'];
                                        $nbT += $nb; 
                                        $tab_servie[]=  $nb;

                                    }
                                }
                                else
                                {
                                    $nb=0;
                                }
                            }
                            ?>
                            <h5 class="text-muted m-t-0"><?= $data['lang']['Totale_transaction']; ?> : <?= $nbT; ?></h5>
                        </div>
                        <div class="col-xs-4">
                            <div class="circle circle-md bg-info pull-right m-t-10"><i class="ti-pulse"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-3 col-xs-12">
                <div class="white-box">
                    <h3 class="box-title">Transactions</h3>
                    <div id="donutchart" style="width:100%; height:194px;"></div>
                    <div class="row p-t-30">
                        <div class="col-xs-8 p-t-30">
                            <h3 class="font-medium" style="line-height: 15px;font-size: 16px;"><?= $data['lang']['Nombre_de_transactions_par_Mois']; ?></h3>
                            <?php 
                            $nbTotal=0;
                            foreach($nb_transaction_mois as $row_nb_transaction_mois) 
                            {
                                $tab[]=array($this->utils->moisLettre($row_nb_transaction_mois['mois']).' '.$row_nb_transaction_mois['annee'],$row_nb_transaction_mois['nbre']);
                                $nbTotal += $row_nb_transaction_mois['nbre'];
                            }
                            ?>
                            <h5 class="text-muted m-t-0"><?= $nbTotal; ?> transactions</h5>
                        </div>
                        <div class="col-xs-4 p-t-30">
                            <div class="circle-md pull-right circle bg-info"><i class="ti-pie-chart"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- wallet, & manage users widgets -->
        <!-- ============================================================== -->
        <!-- .row -->
        <div class="row">
            <!-- col-md-9 -->
            <div class="col-md-12 col-lg-12">
                <div class="manage-users">
                    <div class="sttabs tabs-style-iconbox">
                        <nav>
                            <ul>
                                <li><a href="#section-iconbox-1" class="sticon ti-credit-card"><span><?= $data['lang']['Gestion_des_cartes']; ?></span></a></li>
                                <li><a href="#section-iconbox-2" class="sticon ti-reload"><span><?= $data['lang']['Rechargement_retrait']; ?></span></a></li>
                                <li><a href="#section-iconbox-3" class="sticon ti-exchange-vertical"><span><?= $data['lang']['Transfert']; ?></span></a></li>
                                <li><a href="#section-iconbox-4" class="sticon ti-receipt"><span><?= $data['lang']['paiement']; ?></span></a></li>
                            </ul>
                        </nav>
                        <div class="content-wrap">
                            <section id="section-iconbox-1">
                                <div class="p-20 row">
                                    <div class="col-sm-6">
                                        <h3 class="m-t-0"><?= $data['lang']['Vente_de_Cartes_par_Mois']; ?></h3>
                                    </div>
                                    <div class="col-sm-6">
                                        <ul class="side-icon-text pull-right">

                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive manage-table">

                                    <table class="table">
                                      <thead>
                                          <tr style="background-color:#EDEDED">
                                              <td><strong><?= $data['lang']['Mois']; ?></strong></td>
                                              <td align="center"><strong><?= $data['lang']['Nombres']; ?></strong></td>
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
                                            <tr class="advance-table-row">
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

                                      <tr bgcolor="#CCCCCC" class="advance-table-row">
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
                    <div class="p-10 p-t-30 row">
                        <div class="col-sm-8">
                        </div>
                        <div class="col-sm-2 pull-right m-t-10"></div>
                    </div>
                </section>
                            <section id="section-iconbox-2">
                    <div class="p-20 row">
                        <div class="col-sm-6">
                            <h3 class="m-t-0"><?= $data['lang']['Rechargement_retrait_par_mois']; ?></h3>
                        </div>
                        <div class="col-sm-6">
                            <ul class="side-icon-text pull-right">

                            </ul>
                        </div>
                    </div>
                    <div class="table-responsive manage-table">
                        <div class="panel-group col-lg-12 col-sm-12" id="accordion">
                            <?php 
                            $i11= 1;
                            $total_service11=0; 
                            $total_frais11=0; 
                            $total_ttc11=0;
                            foreach($rs_service11 as $row_rs_service11) 
                            { 
                                $rechargeransact11 = $this->utils->montantRechargementMensuel1($row_rs_service11['rowid'],$data['user']);
                                ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title" style="text-align: center;">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $i11 ?>"><?= $row_rs_service11['label'];?></a>
                                        </h4>
                                    </div>
                                    <div id="collapse<?= $i11 ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <table class="table">
                                                <thead>
                                                    <tr bgcolor="#999999" class="advance-table-row">
                                                      <td align="center" colspan="4"><strong><?php echo $row_rs_service11['label']; ?></strong></td>
                                                    </tr>
                                                    <tr style="background-color:#EDEDED" class="advance-table-row">
                                                      <td><strong><?= $data['lang']['Mois']; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                    </tr>
                                                </thead>
                                                    <tbody>
                                                    <?php  
                                                    $recharge_total11=0;
                                                    $frais_total11=0;
                                                    $ttc_total11=0;
                                                    foreach($rechargeransact11 as $row_rechargeransact11) {?>
                                                    <tr class="advance-table-row">
                                                        <td><?php echo $this->utils->moisLettre($row_rechargeransact11['mois']).' '.$row_rechargeransact11['annee']; ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact11['mt']); ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact11['frais']); ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact11['mt']+$row_rechargeransact11['frais']); ?></td>
                                                    </tr>
                                                    <?php 
                                                    $recharge_total11+=$row_rechargeransact11['mt'];
                                                    $frais_total11+=$row_rechargeransact11['frais'];
                                                    $ttc_total11=$recharge_total11+$frais_total11;
                                                    }?>
                                                    <?php 
                                                    $total_service11+=$recharge_total11;
                                                    $total_frais11+=$frais_total11; 
                                                    $total_ttc11=$total_service11+$total_frais11; 
                                                    ?>
                                                    <tr bgcolor="#DDDDDD" class="advance-table-row">
                                                        <td  align="right" valign="middle" >
                                                            <strong><?= $data['lang']['montant_total'].' '.$row_rs_service11['label'];?> : </strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($recharge_total11); ?></strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($frais_total11); ?></strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($ttc_total11); ?></strong>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $i11++;} ?>
                                <div class="col-lg-12 col-sm-12">
                                    <div class="panel panel-inverse" style="margin: 3px -15px;">
                                        <div class="panel-heading" style="text-align: center;"> TOTAL
                                            <div class="pull-right"><a href="#" data-perform="panel-collapse"><i class="ti-minus"></i></a> <a href="#" data-perform="panel-dismiss"><i class="ti-close"></i></a> </div>
                                        </div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body">
                                                <table class="table">
                                                    <thead>
                                                        <tr style="background-color:#EDEDED" class="advance-table-row">
                                                          <td><strong><?= $data['lang']['Mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                        </tr>
                                                    </thead>
                                                     <tbody>
                                                                    
                                                                <tr bgcolor="#CCCCCC" class="advance-table-row">
                                                                    <td  align="right" valign="middle" >
                                                                        <strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_service11); ?></strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_frais11); ?></strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_ttc11); ?></strong>
                                                                    </td>
                                                                </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="p-10 p-t-30 row">
                        <div class="col-sm-8">
                        </div>
                        <div class="col-sm-2 pull-right m-t-10"></div>
                    </div>
                </section>
                            <section id="section-iconbox-3">
            <div class="p-20 row">
                <div class="col-sm-6">
                    <h3 class="m-t-0"><?= $data['lang']['Transfert_cash_par_mois']; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ul class="side-icon-text pull-right">

                    </ul>
                </div>
            </div>
            <div class="table-responsive manage-table">
                <table class="table table-hover">
                    <thead>
                        <tr style="background-color:#EDEDED" class="advance-table-row">
                          <td><strong><?= $data['lang']['Mois']; ?></strong></td>
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
                        <tr class="advance-table-row">
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
                        <tr bgcolor="#CCCCCC" class="advance-table-row">
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
            <div class="p-10 p-t-30 row">
                <div class="col-sm-8">
                </div>
                <div class="col-sm-2 pull-right m-t-10"></div>
            </div>
        </section>
                            <section id="section-iconbox-4">
            <div class="p-20 row">
                <div class="col-sm-6">
                <h3 class="m-t-0"><?= $data['lang']['Gestion_des_Facturiers']; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ul class="side-icon-text pull-right">

                    </ul>
                </div>
            </div>
            <div class="table-responsive manage-table">
                        <div class="panel-group col-lg-12 col-sm-12" id="accordion">
                            <?php 
                            $i111= 1;
                            $total_service111=0; 
                            $total_frais111=0; 
                            $total_ttc111=0;
                            foreach($rs_service111 as $row_rs_service111) 
                            { 
                                $rechargeransact111 = $this->utils->montantFacturierMensuel1($row_rs_service111['rowid'],$data['user']);
                                ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title" style="text-align: center;">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $i111 ?>"><?= $row_rs_service111['label'];?></a>
                                        </h4>
                                    </div>
                                    <div id="collapse<?= $i111 ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <table class="table">
                                                <thead>
                                                    <tr bgcolor="#999999" class="advance-table-row">
                                                      <td align="center" colspan="4"><strong><?php echo $row_rs_service111['label']; ?></strong></td>
                                                    </tr>
                                                    <tr style="background-color:#EDEDED" class="advance-table-row">
                                                      <td><strong><?= $data['lang']['Mois']; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                      <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                    </tr>
                                                </thead>
                                                    <tbody>
                                                    <?php  
                                                    $recharge_total111=0;
                                                    $frais_total111=0;
                                                    $ttc_total111=0;
                                                    foreach($rechargeransact111 as $row_rechargeransact111) {?>
                                                    <tr class="advance-table-row">
                                                        <td><?php echo $this->utils->moisLettre($row_rechargeransact111['mois']).' '.$row_rechargeransact111['annee']; ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact111['mt']); ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact111['frais']); ?></td>
                                                        <td align="right"><?php echo $this->utils->number_format($row_rechargeransact111['mt']+$row_rechargeransact111['frais']); ?></td>
                                                    </tr>
                                                    <?php 
                                                    $recharge_total111+=$row_rechargeransact111['mt'];
                                                    $frais_total111+=$row_rechargeransact111['frais'];
                                                    $ttc_total111=$recharge_total111+$frais_total111;
                                                    }?>
                                                    <?php 
                                                    $total_service111+=$recharge_total111;
                                                    $total_frais111+=$frais_total111; 
                                                    $total_ttc111=$total_service111+$total_frais111; 
                                                    ?>
                                                    <tr bgcolor="#DDDDDD" class="advance-table-row">
                                                        <td  align="right" valign="middle" >
                                                            <strong><?= $data['lang']['montant_total'].' '.$row_rs_service111['label'];?> : </strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($recharge_total111); ?></strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($frais_total111); ?></strong>
                                                        </td>
                                                        <td align="right" valign="middle" >
                                                            <strong><?= $this->utils->number_format($ttc_total111); ?></strong>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $i111++;} ?>
                                <div class="col-lg-12 col-sm-12">
                                    <div class="panel panel-inverse" style="margin: 3px -15px;">
                                        <div class="panel-heading" style="text-align: center;"> TOTAL
                                            <div class="pull-right"><a href="#" data-perform="panel-collapse"><i class="ti-minus"></i></a> <!--<a href="#" data-perform="panel-dismiss"><i class="ti-close"></i></a> --></div>
                                        </div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body">
                                                <table class="table">
                                                    <thead>
                                                        <tr style="background-color:#EDEDED" class="advance-table-row">
                                                          <td><strong><?= $data['lang']['Mois']; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_sans_ttc'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['commission'] ; ?></strong></td>
                                                          <td align="right"><strong><?= $data['lang']['montant_ttc'] ; ?></strong></td>
                                                        </tr>
                                                    </thead>
                                                     <tbody>
                                                                    
                                                                <tr bgcolor="#CCCCCC" class="advance-table-row">
                                                                    <td  align="right" valign="middle" >
                                                                        <strong><?= $data['lang']['montant_total']; ?>: </strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_service111); ?></strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_frais111); ?></strong>
                                                                    </td>
                                                                    <td align="right" valign="middle" >
                                                                        <strong><?php echo $this->utils->number_format($total_ttc111); ?></strong>
                                                                    </td>
                                                                </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
            <div class="p-10 p-t-30 row">
                <div class="col-sm-8">
                </div>
                <div class="col-sm-2 pull-right m-t-10"></div>
            </div>
        </section>
</div>
<!-- /content -->
</div>
<!-- /tabs -->
</div>
</div>
<!-- /col-md-9 -->
<!-- col-md-3 -->

<!-- /col-md-3 -->
</div>
<!-- /.row -->
</div>

</div>
<!-- /.container-fluid -->
<footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
</div>
<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->
<?php include_once(__DIR__ . '/../footer1.php'); ?>
<script type="text/javascript">
    //ct-bar-chart
    new Chartist.Bar('#ct-daily-sales', {
     labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
     series: [

     <?php 

     $taille=sizeof($tab_servie);?>
     [
     <?php for($i=0;$i<$taille;$i++)
     {
         if($i==$taille-1){ ?>
             <?= $tab_servie[$i]; ?>
             <?php 
         }
         else{ ?>

            <?= $tab_servie[$i].','; ?>
            <?php }  
        }
        ?>

        ]
        ]
    }, {
     axisX: {
         showLabel: true,
         showGrid: false,
             // On the x-axis start means top and end means bottom
             position: 'start'
         },

         chartPadding: {
             top: -20,
             left: 45,
         },
         axisY: {
             showLabel: false,
             showGrid: false,
             // On the y-axis start means left and end means right
             position: 'end'
         },
         height: 335,
         plugins: [
         Chartist.plugins.tooltip()
         ]
     });
 </script>
 <script src="<?= WEBROOT ?>/assets/graphe/jsapi.js"></script>
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
     pieHole: 0.5,
     pieSliceTextStyle: {color: '#000000', fontSize:14}
 };
 var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
 chart.draw(data, options);
}
</script>
<script src="<?= WEBROOT ?>/assets/SpryAssets/highcharts.js"></script>
<script src="<?= WEBROOT ?>/assets/SpryAssets/exporting.js"></script>
<script>
    $(function () {
        $('#container').highcharts({
            title: {
                text: '',
            x: -20 //center
        },
        subtitle: {
            text: 'Source: Postecash.sn',
            x: -20
        },
        xAxis: {
            categories: [
            <?php foreach($stack as $t){?>
                '<?php echo $this->utils->moisLettre($t); ?>',
                <?php } ?>

                ],
                crosshair: true
            },
            yAxis: {
                title: {
                    text: <?php if($courbe==1) echo "'Nombre de Transactions'"; elseif($courbe==2) echo "'Montant (F CFA)'";elseif($courbe==3) echo "'Commission (F CFA)'";?>
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: <?php if($courbe==1) echo  "''"; else echo  "'F CFA'";?>
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },

            <?php 
            $taille=sizeof($tab_servie1);?>
            series: [
            <?php for($i=0;$i<$taille;$i++)
            {
             if($i==$taille-1){ ?>
                 <?= "{ 
                    name: '" ?><?= $tab_servie1[$i][0]; ?> <?="', data: [ " ?><?= $tab_servie1[$i][1][0] ?>, <?= $tab_servie1[$i][1][1] ?>, <?= $tab_servie1[$i][1][2] ?>, <?= $tab_servie1[$i][1][3] ?>, <?= $tab_servie1[$i][1][4] ?>, <?= $tab_servie1[$i][1][5] ?>, <?= $tab_servie1[$i][1][6] ?>, <?= $tab_servie1[$i][1][7] ?>, <?= $tab_servie1[$i][1][8] ?>, <?= $tab_servie1[$i][1][9] ?>, <?= $tab_servie1[$i][1][10] ?>, <?= $tab_servie1[$i][1][11] ?> <?= "]
                } " ?>
                <?php 
            }
            else{ ?>

                <?= "{ 
                    name: '" ?><?= $tab_servie1[$i][0]; ?> <?="', data: [ " ?><?= $tab_servie1[$i][1][0] ?>, <?= $tab_servie1[$i][1][1] ?>, <?= $tab_servie1[$i][1][2] ?>, <?= $tab_servie1[$i][1][3] ?>, <?= $tab_servie1[$i][1][4] ?>, <?= $tab_servie1[$i][1][5] ?>, <?= $tab_servie1[$i][1][6] ?>, <?= $tab_servie1[$i][1][7] ?>, <?= $tab_servie1[$i][1][8] ?>, <?= $tab_servie1[$i][1][9] ?>, <?= $tab_servie1[$i][1][10] ?>, <?= $tab_servie1[$i][1][11] ?> <?= "]
                }, " ?>
                <?php }  
            }
            ?>
            ]
        });
    });
</script>

