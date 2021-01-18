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
                            <li class="active"><?= $data['lang']['recharge']; ?></li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?= $data['lang']['Rechercher_une_carte']; ?></div>
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body">
                                    <form action="<?= ROOT.'recharge/rechargeEspeceCarte';?>" id="search" method="post">
                                        <div class="form-body">
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label"><?= $data['lang']['Entrer_le_numero_de_telephone']; ?></label>
                                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-success pull-right" id="btnRecharge"> <i class="fa fa-check"></i> <?= $data['lang']['Rechercher']; ?></button>
                                            <button type="button" class="btn btn-default pull-left"><?= $data['lang']['Annuler']; ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?= $data['lang']['Historique']; ?></div>
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body" style="padding: 35px;">

                                    <?php if($data['transaction']->montant > 0){ ?>

                                    <p><b><?= $data['lang']['date']; ?> :</b> Le <?= $this->utils->date_fr4($data['transaction']->date_transaction); ?></p>
                               		<p><b><?= $data['lang']['N_Téléphone']; ?> :</b> <?= $this->utils->truncate_carte($data['benef']->telephone); ?></p>
                               		<p><b><?= $data['lang']['montant']; ?> :</b> <?= $this->utils->number_format($data['transaction']->montant).' '.$data['lang']['currency']; ?></p>
                               		<div class="button-box">
                               		<a href="<?= ROOT.'recharge/Histo_rechargeEspeceCarte';?>"><button class="fcbtn btn btn-warning btn-outline btn-1b"><?= $data['lang']['voir_historique']; ?> </button></a>
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
<?php include_once(__DIR__.'/../footer.php'); ?>
<script type="text/javascript">
    var code = document.getElementById("phone");
    code.onkeydown = function(e){
        if(!((e.keyCode > 95 && e.keyCode < 106)
            || (e.keyCode > 47 && e.keyCode < 58)
            || e.keyCode == 8)){
            return false;
        }
    }
</script>

<script>
    $('#btnRecharge').click(function () {

    });

    function confirmSned() {
        //alert(12);
        $('#search').submit();
       // $('#recharger').attr("disabled", true);
       document.getElementById("recharger").disabled = true;
    }
</script>

<div class="modal" tabindex="-1" role="dialog" id="sendModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $data['lang']['confirm_send']; ?></h5>
            </div>
            <div class="modal-body">
                <p><?= $data['lang']['message_confirm_1']; ?> <span id="montant2"></span> Ar<?= $data['lang']['message_confirm_2']; ?><span id="benef"></span><?= $data['lang']['message_confirm_3']; ?><span id="tel"></span></p>
                <p><?= $data['lang']['frais']; ?>: <span id="frais2"></span> Ar</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                <button type="button" onclick="confirmSned()" value="recharger" id="recharger" class="btn btn-primary">Oui</button>

            </div>
        </div>
    </div>
</div>
