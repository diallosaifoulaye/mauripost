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
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['code_retrait']; ?></legend>


                                        <div class="col-lg-offset-3 col-lg-6 col-md-offset-2 col-md-8 col-sm-12 col-xs-12 text-center">
                                            <form class="form-horizontal" method="post" id='formespece' action='<?= ROOT ?>recharge/retraitEspeceValidation'>

                                                <input type="hidden" name="telephone" value="<?= $data['telephone']; ?>" />
                                                <input type="hidden" name="fkcarte" id="fkcarte" value="<?= $data['fkcarte']; ?>" />
                                                <input type="hidden" name="soldecarte" id="soldecarte" value="<?= $data['soldeCarte'] ?>" />
                                                <input type="hidden" name="montant" id="montant" value="<?= $data['montant']; ?>" />
                                                <input type="hidden" name="frais" id="frais" value="<?= $data['frais']; ?>" />
                                                <input type="hidden" name="fkagence" id="fkagence" value="<?= $data['fkagence'] ?>" />
                                                <input type="hidden" name="typeagence" id="typeagence" value="<?= $data['typeagence'] ?>" />

                                                <div class="form-group">
                                                    <label for="code" class="col-sm-5 control-label"><?= $data['lang']['code_retrait']; ?></label>
                                                    <div class="col-sm-7">
                                                        <input type="hidden" value="0" min="0" id="code_check">
                                                        <input type="text" required class="form-control" id="code" data-inputmask='"mask": "9999999999"' data-mask name="code" placeholder="<?= $data['lang']['entrer_code_retrait']; ?>">
                                                        <span id="msg__error_code"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="cni" class="col-sm-5 control-label"><?= $data['lang']['cni_numero']; ?></label>
                                                    <div class="col-sm-7">
                                                        <input type="hidden" value="0" min="0" id="cni_check">
                                                        <input type="text" required class="form-control" id="cni" data-inputmask='"mask": "9999999999999"' data-mask name="cni" placeholder="<?= $data['lang']['entrer_cni_numero']; ?>" >
                                                        <span id="msg__error_cni"></span>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-success" name="recharger" value="recharger" id="recharger">
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
            <footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
        </div>
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>
<script type="text/javascript" src="<?= WEBROOT ?>assets/js/oXHR.js"></script>

<!-- END FOOTER DE LA PAGE -->
<script>

    /*$(function () {
        $("[data-mask]").inputmask();
    });*/

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

    $('#code').on('input',function ()
    {
        var codesecret = document.getElementById('code').value;
        var fkcarte = document.getElementById('fkcarte').value;
        if(codesecret != '')
        {
            $.ajax({
                url: "<?= ROOT.'recharge/codeRetrait'; ?>",
                type: "post",
                data : 'codesecret='+codesecret+'&fkcarte='+fkcarte,
                success:function(texte){
                    if(parseInt(texte) == 0)
                    {
                        $('#msg__error_code').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Code incorrect !!!</p>");
                        $('#code_check').val("0")
                        //$('#recharger').attr("disabled", "disabled");

                    }
                    else if(parseInt(texte) == 1)
                    {
                        $('#msg__error_code').html("<p style='color:green;display: inline;'>Code correct !!!</p>");
                        $('#code_check').val("1")

                        //$("#recharger").removeAttr("disabled", "disabled");
                    }
                    else
                    {
                        $('#code_check').val("0")
                        alert('un problème est survenu, Merci de Réessayer');
                        //document.getElementById('msg').innerHTML='un problème est survenu, Merci de Réessayer';
                    }
                    validator()
                }

            });
        }
        else
        {
            console.log("Merci de renseigner le code d'activation");
            //document.getElementById('msg').innerHTML="Merci de renseigner le code d'activation";
        }

    })

    $('#cni').on('input',function ()
    {
        var cni = document.getElementById('cni').value;
        var fkcarte = document.getElementById('fkcarte').value;
        if(cni != '')
        {
            $.ajax({
                url: "<?= ROOT.'recharge/cniCheck'; ?>",
                type: "post",
                data : 'cni='+cni+'&fkcarte='+fkcarte,
                success:function(texte){
                    if(parseInt(texte) == 0)
                    {
                        $('#msg__error_cni').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>CNI incorrect !!!</p>");
                        $('#cni_check').val("0")
                        //$('#recharger').attr("disabled", "disabled");
                    }
                    else if(parseInt(texte) == 1)
                    {
                        $('#msg__error_cni').html("<p style='color:green;display: inline;'>CNI correct !!!</p>");
                        $('#cni_check').val("1")

                        //$("#recharger").removeAttr("disabled", "disabled");
                    }
                    else
                    {
                        $('#cni_check').val("0")
                        alert('un problème est survenu, Merci de Réessayer');
                        //document.getElementById('msg').innerHTML='un problème est survenu, Merci de Réessayer';
                    }

                    validator()
                }
            });
        }
        else
        {
            console.log("Merci de renseigner le numéro de la CNI");
            //document.getElementById('msg').innerHTML="Merci de renseigner le numéro de la CNI";
        }
    })

    function validator() {
        var check_1 = $('#code_check').val()
        var check_2 = $('#cni_check').val()

        if(check_1 != "1" || check_2 != "1"){
            console.log("no")
            $('#recharger').attr("disabled", "disabled");
        }else{
            console.log("ok")
            $("#recharger").removeAttr("disabled");
        }
    }

</script>