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
                    
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?= $data['lang']['rechargement_par_espece']; ?></div>
                            <div class="panel-wrapper collapse in" aria-expanded="true">
                                <div class="panel-body">
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['envoi_mail_code_rechargement_3']; ?></legend>
                                        <div class="col-lg-offset-3 col-lg-6 col-md-offset-2 col-md-8 col-sm-12 col-xs-12 text-center">
                                            <form class="form-inline" method="post" id='formespece' >


                                                <input type="hidden" name="telephone" value="<?= $data['telephone']; ?>" />
                                                <input type="hidden" name="fkcarte" value="<?= $data['fkcarte']; ?>" />
                                                <input type="hidden" name="soldecarte" id="soldecarte" value="<?= $data['soldeCarte'] ?>" />
                                                <input type="hidden" name="montant" id="montant" value="<?= $data['montant']; ?>" />
                                                <input type="hidden" name="frais" id="frais" value="<?= $data['frais']; ?>" />
                                                <input type="hidden" name="soldeagence" id="soldeagence" value="<?= $data['soldeAgence'] ?>" />
                                                <input type="hidden" name="fkagence" id="fkagence" value="<?= $data['fkagence'] ?>" />

                                                <div class="form-group">
                                                    <label for="code" class="sr-only"><?= $data['lang']['envoi_mail_code_rechargement_3']; ?></label>
                                                    <input type="text" required class="form-control" id="code" name="code" placeholder="<?= $data['lang']['entrer_code_validation'] ; ?>" onkeypress="refuserToucheEntree(event);"/>
                                                    <div id="msg"></div>
                                                </div>
                                                <input type="hidden" name="recharger" value="recharger" />
                                                <button type="button" class="btn btn-success" name="recharger" value="recharger" id="recharger" onclick="verifcode_rechargement()">
                                                    <?= $data['lang']['valider']; ?>
                                                </button>
                                            </form>

                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                    
                                       

                </div>
                
            </div>
            <!-- /.container-fluid -->
            <footer class="footer text-center"> 2017 &copy; By NUMHERIT SA </footer>
        </div>
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>
<script type="text/javascript" src="<?= WEBROOT ?>assets/js/oXHR.js"></script>

<!-- END FOOTER DE LA PAGE -->
<script>

    $(function () {
        $("[data-mask]").inputmask();
    });

    function refuserToucheEntree(event)
    {
        //Compatibilité IE / Firefox
        if(!event && window.event) {
            event = window.event;
        }
        //IE
        if(event.keyCode == 13) {
            event.returnValue = false;
            event.cancelBubble = true;
        }
        //DOM
        if(event.which == 13) {
            event.preventDefault();
            event.stopPropagation();
        }
    }

    var gagnote = document.getElementById("code");
    gagnote.onkeydown = function(e){
        if(!((e.keyCode > 95 && e.keyCode < 106)
            || (e.keyCode > 47 && e.keyCode < 58)
            || e.keyCode == 8)){
            return false;
        }
    }

    function verifcode_rechargement()
    {
        var codesecret = document.getElementById('code').value;
        var fkagence = document.getElementById('fkagence').value;
        if(codesecret != '')
        {
            $.ajax({
                url: "<?= ROOT.'recharge/codeRechargement'; ?>",
                type: "post",
                data : 'codesecret='+codesecret+'&fkagence='+fkagence,
                success:function(texte){

                    
                    if(parseInt(texte) == 0)
                    {
                        alert('Code Incorrect !!!!', 'Erreur Carte');
                        document.getElementById('msg').innerHTML='Code Incorrect';
                    }
                    else if(parseInt(texte) == 1)
                    {
                        document.getElementById('formespece').action='<?= ROOT ?>recharge/rechargeEspeceValidation';
                        document.getElementById('recharger').style.display='none';
                        document.getElementById('code').style.display='none';
                        document.getElementById('msg').innerHTML="Merci de Patientez, traitement en cours...";
                        document.getElementById('formespece').submit();
                    }
                    else
                    {
                        alert('un problème est survenu, Merci de Réessayer');
                        document.getElementById('msg').innerHTML='un problème est survenu, Merci de Réessayer';
                    }
                }
            });
        }
        else
        {
            alert("Merci de renseigner le code d'activation");
            document.getElementById('msg').innerHTML="Merci de renseigner le code d'activation";
        }
    }
</script>