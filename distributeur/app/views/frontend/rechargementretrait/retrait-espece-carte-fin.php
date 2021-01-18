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
                                    <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12 text-center">
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border"><?= $data['lang']['resultat_retrait']; ?></legend>


                                            <div class="col-lg-offset-3 col-lg-6 col-md-offset-2 col-md-8 col-sm-12 col-xs-12 text-center">

                                                <?php if( $type_alert != '' && $alert !=''){ ?>

                                                    <div class="alert <?= $type_alert ?> alert-dismissable" id="success-alert">
                                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                        <p><?= $alert ?> </p>
                                                    </div>

                                                <?php } ?>

                                               <br/>

                                            </div>

                                            <div class="col-sm-offset-3 col-sm-3 col-xs-6 margin-bottom">
                                                <a href="<?= ROOT ?>recharge/searchRetraitEspece" class="btn btn-default"><?= $data['lang']['retour']; ?></a>
                                            </div>
                                            <div class="col-sm-offset-2 col-sm-3 col-xs-6">

                                                <?php if($data['montant'] > 0 && strlen($data['numtransact']) == 15 && $data['telephone'] != '') { ?>
                                                    <form action="<?= ROOT ?>recharge/recuRetraitEspece" method="post" name="form" id="form2" target="new">

                                                        <input name="telephone" type="hidden" value="<?= base64_encode($data['telephone']); ?>" />
                                                        <input name="montant" type="hidden" value="<?= $data['montant']; ?>" />
                                                        <input name="frais" type="hidden" value="<?= $data['frais']; ?>" />
                                                        <input name="numtransact" type="hidden" value="<?= $data['numtransact']; ?>" />

                                                        <input name="imprime2" type="submit" class="btn btn-warning" id="imprime2" value="<?= $data['lang']['imprimer_recu']; ?>" />
                                                    </form>
                                                <?php } ?>

                                            </div>
                                        </fieldset>
                        </div>
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
<script type="text/javascript" src="<?= WEBROOT ?>assets/js/oXHR.js"></script>
