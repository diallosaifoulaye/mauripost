<?php

include(__DIR__.'/../agentheader.php');

//var_dump($data['lang']['beneficiaire']['telephone']);die();

#38 (15) { ["rowid"]=> string(1) "2" ["nom"]=> string(6) "DIAGNE" ["prenom"]=> string(5)
# "Fatou" ["prenom1"]=> string(0) "" ["cni"]=> string(13) "1255198500987" ["adresse"]=> string(9)
# "Fass Mbao" ["email"]=> string(17) "younaby@gmail.com" ["idcarte"]=> string(1) "2"
# ["numero_serie"]=> NULL ["numero"]=> NULL ["date_expiration"]=> NULL ["telephone"]=> string(14) "00221774119645"
# ["date_activation"]=> NULL ["cartestatut"]=> string(1) "1" ["typecompte"]=> string(1) "0" }

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
                            <li class="active"><?= $data['lang']['recharge']; ?></li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>

                <?php if(isset($data['erreur'])){  ?>

                    <div class="col-md-12 text-center">
                        <div class="alert alert-danger alert-dismissable" id="success-alert">
                            <p><?= $data['lang']['message_error_paiement']; ?> </p>
                        </div>
                    </div>
                <?php } ?>
                <div class="row">
                    
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?= $data['lang']['rechargement_par_espece']; ?></div>
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body">
                                    <?php if($data['benef'] != -1 && $data['benef'] != -2) { ?>

                                    <fieldset class="scheduler-border">

                                        <legend class="scheduler-border"><?= $data['lang']['beneficiaire']; ?></legend>

                                        <div class="row">

                                            <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                <dl class="dl-horizontal">



                                                    <dt><?= $data['lang']['num_tel_associe'];?> : </dt>
                                                    <dd><?= $this->utils->truncate_carte($data['benef']->telephone); ?></dd>

                                                    <!--<dt><?/*= $data['lang']['solde_carte']; */?> : </dt>
                                                    <dd><strong><?/*= $this->utils->number_format($data['soldeCarte']); */?> F CFA</strong></dd>-->

                                                </dl>

                                            </div>
                                            <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                <dl class="dl-horizontal">
                                                    <dt><?= $data['lang']['prenom_beneficiaire'];?> : </dt>
                                                    <dd><?= $data['benef']->prenom; ?></dd>

                                                </dl>
                                            </div>

                                            <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                <dl class="dl-horizontal">
                                                    <dt><?= $data['lang']['email_beneficiaire'];?> : </dt>
                                                    <dd><?= $data['benef']->email; ?></dd>

                                                </dl>
                                            </div>

                                            <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                <dl class="dl-horizontal">
                                                    <dt><?= $data['lang']['nom_beneficiaire'];?> : </dt>
                                                    <dd><?= $data['benef']->nom; ?></dd>

                                                </dl>
                                            </div>

                                        </div>

                                    </fieldset>



                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border"><?= $data['lang']['rechargement_par_espece']; ?></legend>

                                            <div class="col-lg-offset-3 col-lg-6 col-md-offset-3 col-md-6 col-sm-12 col-xs-12 text-center">
                                                <form class="form-horizontal" method="post" action="">


                                                  <?php if($data['benef']->numero_serie != '') { ?>

                                                        <div class="form-group">
                                                            <label for="serie" class="col-sm-5 control-label"><?= $data['lang']['numero_serie']; ?></label>
                                                            <div class="col-sm-7">
                                                                <input type="text" name="serie" class="form-control" value="<?= $data['benef']->numero_serie; ?>" readonly id="serie">
                                                            </div>
                                                        </div>

                                                  <?php } ?>

                                                    <div class="form-group">
                                                        <label for="montant" class="col-sm-5 control-label"><?= $data['lang']['montant']; ?></label>
                                                        <div class="col-sm-7">
                                                            <input type="text" class="form-control" required id="montant" data-inputmask='"mask": "99999999"' data-mask name="montant" placeholder="<?= $data['lang']['entrer_montant_recharger']; ?>" onchange="request15();" oninput="request15();" onkeyup="request15();">
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label for="frais" class="col-sm-5 control-label"><?= $data['lang']['frais']; ?></label>
                                                        <div class="col-sm-7" style="text-align: left;">
                                                            <span id="frais" style="margin-top: -7px;">0</span> <span><?= $data['lang']['currency']; ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-3 col-xs-6">
                                                            <a href="<?= ROOT.'recharge/rechargement';?>" class="btn btn-warning"><?= $data['lang']['retour']; ?></a>
                                                        </div>
                                                        <div class="col-sm-offset-4 col-sm-3 col-xs-6">
                                                            <button type="button" class="btn btn-success" id="recharger" onclick="clicked();"><?= $data['lang']['recharger']; ?></button>
                                                        </div>
                                                    </div>

                                                </form>

                                            </div>

                                        </fieldset>

                                <?php } else { ?>

                                    <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                        <p><?= $data['lang']['message_error_search_carte']; ?></p>
                                    </div>
                                    <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                        <a href="<?= ROOT.'recharge/rechargement';?>" class="btn btn-warning"><?= $data['lang']['retour']; ?></a>
                                    </div>

                                <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                    
                                       

                </div>
                
            </div>
            <!-- /.container-fluid -->
            <footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
        </div>
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
<div class="modal fade" tabindex="-1" role="dialog" id="myModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p><?= $data['lang']['confirm_crediter_carte'].'<strong id="montant3"></strong> <strong>'.$data['lang']['currency'].'</strong> ?'; ?></p>
            </div>
            <div class="modal-footer">
                <form method="post" action="<?= ROOT ?>recharge/rechargeEspeceCodeValidation">

                    <input type="hidden" name="telephone" value="<?= base64_encode($data['benef']->telephone); ?>" />

                    <input type="hidden" name="montantbis" id="montantbis" value="0" />
                    <input type="hidden" name="frais2" id="frais2" value="0" />
                    <input type="hidden" name="fkcarte" id="fkcarte" value="<?= base64_encode($data['benef']->idcarte); ?>" />
                    <input type="hidden" name="soldeagence" id="soldeagence" value="<?= $data['soldeAgence']; ?>" />
                    <button type="reset" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['non']; ?></button>
                    <button type="submit" name="recharger" value="recharger" class="btn btn-warning"><?= $data['lang']['yes']; ?></button>

                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="alertModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-red text-center"><?= $data['lang']['veuillez_saisir_montant']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['ok']; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="alertModalMontant" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-red text-center"><?= $data['lang']['min_montant']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['ok']; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="alertModalMontant1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-red text-center"><?= $data['lang']['min_montant1']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['ok']; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="alertsoldeModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-red text-center"><?= $data['lang']['solde_agence_insuffisant']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['ok']; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php include_once(__DIR__.'/../footer.php'); ?>
<script>
    $(function () {
        $("[data-mask]").inputmask();
    });
</script>
<script>
    function clicked()
    {
        var montant = document.getElementById('montant').value;
        var frais = document.getElementById('frais').innerHTML;
        montant = montant.replace("_", "");
        var soldeagence = document.getElementById('soldeagence').value;
        montant = parseFloat(montant);
        frais = parseFloat(frais);
        soldeagence = parseFloat(soldeagence);


        if(montant <= 0 || montant == '_' || isNaN(montant))
        {
            $('#alertModal').modal('show');
        }
        else if(montant < 100)
        {
            $('#alertModalMontant').modal('show');
        }
        else if(montant > 5000000)
        {
            $('#alertModalMontant1').modal('show');
        }
        else
        {
            if(frais == '')
            {
                frais = 0;
            }
            if(soldeagence >= (montant + frais))
            {
                document.getElementById('montantbis').setAttribute('value', montant);
                document.getElementById('frais2').setAttribute('value', frais);
                document.getElementById('montant3').innerHTML = montant;
                $('#myModal').modal('show');
            }
            else
            {
                $('#alertsoldeModal').modal('show');
            }
        }
    }
</script>
<script>
//frais du rechargement en espece
    function request15() {
            var value = parseInt(document.getElementById('montant').value);
            if(value >= 100){
                $.ajax({
                    url: "<?= ROOT.'recharge/calculFrais'; ?>",
                    type: "post",
                    data : 'montant='+value,
                    success: function(result){
                        document.getElementById('frais').innerHTML = result;
                    }
                });
            }else{
                document.getElementById('frais').innerHTML = 0;
            }
        }
</script>
