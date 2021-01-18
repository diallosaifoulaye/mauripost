<?php
include(__DIR__.'/../agentheader.php');
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
                            <li class="active"><?= $data['lang']['retrait_carte']; ?></li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <div class="row">
                    
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?= $data['lang']['retrait_carte']; ?></div>
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body">
                                    <?php if($data['benef'] != -1 && $data['benef'] != -2) { ?>

                                        <fieldset class="scheduler-border">

                                            <legend class="scheduler-border"><?= $data['lang']['beneficiaire']; ?></legend>

                                            <div class="row">

                                                <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                    <dl class="dl-horizontal">

                                                        <?php if($data['benef']->numero != '') { ?>

                                                            <dt><?= $data['lang']['numero_carte'];?> : </dt>
                                                            <dd><?= $this->utils->truncate_carte($data['benef']->numero); ?></dd>

                                                        <?php } ?>

                                                        <dt><?= $data['lang']['nom_beneficiaire'];?> : </dt>
                                                        <dd><?= $data['benef']->prenom.' '.$data['benef']->nom; ?></dd>

                                                        <dt><?= $data['lang']['num_tel_associe'];?> : </dt>
                                                        <dd><?= $this->utils->truncate_carte($data['benef']->telephone); ?></dd>

                                                        <!--<dt><?/*= $data['lang']['solde_carte']; */?> : </dt>
                                                        <dd><strong><?/*= $this->utils->number_format($data['soldeCarte']); */?> F CFA</strong></dd>-->

                                                    </dl>

                                                </div>
                                                <div class="col-lg-offset-0 col-lg-6 col-md-offset-0 col-md-6 col-sm-offset-1 col-sm-10 col-xs-12">
                                                    <dl class="dl-horizontal">

                                                        <?php if($data['benef']->date_expiration != '') { ?>

                                                            <dt><?= $data['lang']['date_expiration']; ?> : </dt>
                                                            <dd><?= $this->utils->date_fr2($data['benef']->date_expiration); ?></dd>

                                                        <?php } ?>

                                                        <dt><?= $data['lang']['email_beneficiaire']; ?> : </dt>
                                                        <dd><?= $data['benef']->email; ?></dd>

                                                        <dt><?= $data['lang']['adresse_beneficiaire'];?> : </dt>
                                                        <dd><?= $data['benef']->adresse; ?></dd>

                                                    </dl>
                                                </div>

                                            </div>

                                        </fieldset>

                                        <?php if($data['benef']->cartestatut == 1) { ?>

                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border"><?= $data['lang']['retrait_carte']; ?></legend>

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
                                                                <input type="hidden" name="typeagence" id="typeagence" value="<?= $data['typeagence']; ?>" />
                                                                <input type="hidden" name="free" id="free" value="<?php if($data['typeagence']==3) echo $this->utils->calculFraisab(17); else echo $this->utils->calculFraisab(10); ?>" />
                                                                <!-- <input type="hidden" name="soldecarte" id="soldecarte" value="<?= $data['soldeCarte']; ?>" /> -->
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="frais" class="col-sm-5 control-label"><?= $data['lang']['frais']; ?></label>
                                                            <div class="col-sm-7" style="text-align: left;">
                                                                <span id="frais"  >0</span> <span><?= $data['lang']['currency']; ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-sm-offset-2 col-sm-3 col-xs-6">
                                                                <a href="<?= ROOT ?>recharge/searchRetraitEspece" class="btn btn-warning"><?= $data['lang']['retour']; ?></a>
                                                            </div>
                                                            <div class="col-sm-offset-4 col-sm-3 col-xs-6">
                                                                <button type="button" class="btn btn-success" id="recharger" onclick="clicked();"><?= $data['lang']['retrait']; ?></button>
                                                            </div>
                                                        </div>

                                                    </form>

                                                </div>

                                            </fieldset>
                                        <?php } else{ ?>
                                            <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                                <p class="text-red"><?= $data['lang']['carte_errone'];?></p>
                                            </div>
                                            <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                                <a href="<?= ROOT ?>recharge/searchRetraitEspece" class="btn btn-warning"><?= $data['lang']['retour']; ?></a>
                                            </div>
                                        <?php } ?>

                                    <?php } else { ?>

                                        <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                            <p><?= $data['lang']['message_error_search_carte']; ?></p>
                                        </div>
                                        <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                            <a href="<?= ROOT ?>recharge/searchRetraitEspece" class="btn btn-warning"><?= $data['lang']['retour']; ?></a>
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
                <p><?= $data['lang']['confirm_debiter_carte'].'<strong id="montant3"></strong> <strong>'.$data['lang']['currency'].'</strong> ?'; ?></p>
            </div>
            <div class="modal-footer">
                <form method="post" action="<?= ROOT ?>recharge/retraitEspeceCodeValidation" id="formespece" >

                    <input type="hidden" name="telephone" value="<?= base64_encode($data['benef']->telephone); ?>" />
                    <input type="hidden" name="soldecarte" id="soldecarte" value="<?= $data['soldeCarte']; ?>" />
                    <input type="hidden" name="typeagence" id="soldecarte" value="<?= $data['typeagence']; ?>" />
                    <input type="hidden" name="montantbis" id="montantbis" value="0" />
                    <input type="hidden" name="frais2" id="frais2" value="0" />
                    <input type="hidden" name="fkcarte" id="fkcarte" value="<?= base64_encode($data['benef']->idcarte); ?>" />

                    <button type="reset" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['non']; ?></button>
                    <button type="button" name="recharger" value="recharger" class="btn btn-warning" id="recharger" onclick="validerEnvoi();"><?= $data['lang']['yes']; ?></button>

                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="alertModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-red text-center"><?= $data['lang']['veuillez_saisir_montant_retirer']; ?></p>
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
                <p class="text-red text-center"><?= $data['lang']['min_retrait_montant']; ?></p>
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
                <p class="text-red text-center"><?= $data['lang']['solde_insuffisant']; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['ok']; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php include_once(__DIR__.'/../footer.php'); ?>
<!--<script>
    $(function () {
        $("[data-mask]").inputmask();
    });
</script>-->
<script>
    /*function clicked()
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
        else if(montant < 2000)
        {
            $('#alertModalMontant').modal('show');
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
    }*/

    function clicked()
    {
        var montant = document.getElementById('montant').value;
        var frais = document.getElementById('frais').innerHTML;
        montant = montant.replace("_", "");
        var soldecompte = document.getElementById('soldecarte').value;
        montant = parseFloat(montant);
        frais = parseFloat(frais);
        var montant_total = montant+frais
        if(montant <= 0 || montant == '_' || isNaN(montant))
        {
            $('#alertModal').modal('show');
        }
        else if(montant < 10000)
        {
            $('#alertModalMontant').modal('show');
        }
        else if(soldecompte < montant_total)
        {
            $('#alertsoldeModal').modal('show');
        }
        else
        {
                document.getElementById('montantbis').setAttribute('value', montant);
                document.getElementById('frais2').setAttribute('value', frais);
                document.getElementById('montant3').innerHTML = montant;
                $('#myModal').modal('show');
        }
    }
</script>
<script>
    //frais du retrait en espece





    function request15() {
        var value = parseInt(document.getElementById('montant').value);
        if(value >= 2000){
            $.ajax({
                url: "<?= ROOT.'recharge/calculFraisRetrait'; ?>",
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

    function validerEnvoi()
    {
        document.getElementById('formespece').submit();
        document.getElementById("recharger").disabled = true;
    }


</script>
