<?php
include(__DIR__ . '/../agentheader.php');
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
                    <li class="active"><?= $data['lang']['Reserv_transpost']; ?></li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <?php if ($type_alert != '' && $alert != '') { ?>

            <div class="alert <?= $type_alert ?> alert-dismissable" id="success-alert">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                </button>
                <p><?= $alert ?> </p>
            </div>

        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?= $data['lang']['nouveau_paiement']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="true">
                        <div class="panel-body">
                            <div class="container-fluid">
                                <div class="row">

                                    <?php if(2 == 1){ ?>
                                        <div class="alert <?= $type; ?> alert-dismissable">
                                            <h4><?= $data['lang']['notification']; ?></h4>
                                            <?= $msg; ?>
                                        </div>
                
                                    <?php } ?>
                                    <div class="col-md-offset-4 col-md-4 col-xs-offset-2 col-sm-8">
                                        <dl class="dl-horizontal">

                            <dt><?= $data['lang']['prenom']; ?> : </dt>
                            <dd><?= $data['user']->prenom; ?></dd>

                            <dt><?= $data['lang']['nom']; ?> : </dt>
                            <dd><?= $data['user']->nom; ?></dd>

                            <dt><?= $data['lang']['email']; ?> : </dt>
                            <dd><?= $data['user']->email; ?></dd>

                            <dt><?= $data['lang']['tel']; ?> : </dt>
                            <dd><?= $data['user']->telephone; ?></dd>

                            <dt><?= $data['lang']['profil']; ?> : </dt>
                            <dd><?= $data['user']->profil; ?></dd>

                            <dt><?= $data['lang']['agence']; ?> : </dt>
                            <dd><?= $data['user']->agence; ?></dd>

                            <dt><?= $data['lang']['identifiant']; ?> : </dt>
                            <dd><?= $data['user']->login; ?></dd>

                            <dt><?= $data['lang']['date_creation']; ?> : </dt>
                            <dd><?= $this->utils->date_fr4($data['user']->date_creation); ?></dd>

                            <dt><?= $data['lang']['user_creation']; ?> : </dt>
                            <dd><?= $this->utils->getUser($data['user']->user_creation); ?></dd>

                            <dt><?= $data['lang']['date_modification']; ?> : </dt>
                            <dd><?= $this->utils->date_fr4($data['user']->date_modification); ?></dd>

                            <dt><?= $data['lang']['user_modification']; ?> : </dt>
                            <dd><?= $this->utils->getUser($data['user']->user_modification);?></dd>

                        </dl>
                    </div>

                    <div class="col-sm-offset-3 col-sm-2">
                        <a href="javascript:history.back()"><button class="btn btn-primary"><?= $data['lang']['retour'] ; ?></button></a>
                    </div>

                    <?php
                   /* if ($user_admin == 1 || $this->utils->Est_autoriser(85, $userConnecter->profil) == 1) { */?>
                    <div class="col-sm-2">
                        <?php if ($data['user']->etat==0){?>
                            <div class="col-sm-2">
                                <button class="btn btn-success" data-toggle="modal" data-target="#activeUser"><?= $data['lang']['activer'] ; ?></button>
                            </div>
                        <?php }?>

                        <?php if ($data['user']->etat==1){?>
                            <div class="col-sm-2">
                                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteUser"><?= $data['lang']['desactiver'] ; ?></button>
                            </div>
                        <?php }?>

                    </div>
                    <?php /*} */?>

                    <?php
                    /*if ($user_admin == 1 || $this->utils->Est_autoriser(84, $userConnecter->profil) == 1) { */?>
                    <div class="col-sm-2">
                        <button class="btn btn-warning" data-toggle="modal" data-target="#updateUser"><?= $data['lang']['modifier'] ; ?></button>
                    </div>
                   <!-- --><?php /*} */?>

                    <?php
                   /* if ($user_admin == 1 || $this->utils->Est_autoriser(164, $userConnecter->profil) == 1) { */?>
                    <div class="col-sm-2">
                        <button class="btn btn-success" data-toggle="modal" data-target="#validation"><?= $data['lang']['reinitialiser']; ?></button>
                    </div>
                    <?php /*} */?>

                </div>



                <!-- /.box-body -->
            </div><!-- /.box -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.content -->
</div>
        </div>
    </div>
</div><!-- END CONTENT DE LA PAGE -->



<div class="modal fade bs-example-modal-lg" role="dialog" aria-labelledby="myLargeModalLabel" id="updateUser" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="panel-title"><?= $data['lang']['modification_user']; ?></h3>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" method="post" action="<?= ROOT ?>utilisateur/updateUser">
                        <input type="hidden" name="iduser" value="<?= base64_encode($data['user']->rowid); ?>" />

                        <div class="form-group">
                            <label for="prenom" class="col-sm-5 control-label"><?= $data['lang']['prenom'].'(*) :' ; ?></label>
                            <div class="col-sm-7">
                                <input type="text" required class="form-control"  id="prenom" name="prenom" value="<?= $data['user']->prenom; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nom" class="col-sm-5 control-label"><?= $data['lang']['nom'].'(*) :' ; ?></label>
                            <div class="col-sm-7">
                                <input type="text" required class="form-control"  id="nom" name="nom" value="<?= $data['user']->nom; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="col-sm-5 control-label"><?=  $data['lang']['email'].'(*) :' ; ?></label>
                            <div class="col-sm-7">
                                <input type="email" required class="form-control"  id="email" name="email" value="<?= $data['user']->email; ?>" onchange="validateMAIL()">

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tel" class="col-sm-5 control-label"><?= $data['lang']['tel'].'(*) :' ; ?></label>
                            <div class="col-sm-7">
                                <input type="text" required class="form-control"  id="tel" name="phone" value="<?= $data['user']->telephone; ?>">

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cat" class="col-sm-5 control-label"><?= $data['lang']['profil'].'(*) :' ; ?></label>
                            <div class="col-sm-7">
                                <select class="form-control select2" required name="profil" style="width: 100%;" id="cat">
                                    <?php foreach($data['profil'] as $cat){ ?>
                                        <option value="<?= $cat['rowid']; ?>" <?php if($data['user']->profil === $cat['label']) echo 'selected="selected"'; ?>><?= $cat['label']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="cni_form-group" style="<?= ($data['user']->profil == 'Agent guichet') ? 'display: block' : 'display: none'?> ">
                            <label for="cni"
                                   class="col-sm-5 control-label"><?= $data['lang']['cni_piece'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="text" id="cni" name="cni" value="<?= $data['user']->code_guichet; ?>" placeholder="<?= $data['lang']['cni_piece']; ?>">
                                <span id="cni_error"></span>
                            </div>
                        </div>

                       <!-- <div class="form-group">
                            <label for="region" class="col-sm-5 control-label"><?/*= $data['lang']['agence'].'(*) :' ; */?></label>
                            <div class="col-sm-7">
                                <select class="form-control select2" required name="agence" style="width: 100%;" id="region">

                                    <?php /*foreach($data['agence'] as $reg){ */?>
                                        <option value="<?/*= $reg['rowid']; */?>" <?php /*if($data['user']->agence === $reg['agence']) echo 'selected="selected"'; */?>><?/*= $reg['agence']; */?></option>
                                    <?php /*} */?>
                                </select>
                            </div>
                        </div>
-->

                        <div class="form-group">

                        </div>
                        <div class="modal-footer">
                            <div class="col-sm-offset-2 col-sm-4">
                                <button type="reset" class="btn btn-danger" data-dismiss="modal"><?= $data['lang']['annuler'] ; ?></button>
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" name="update" value="update" class="btn btn-success"><?= $data['lang']['enregistrer'] ; ?></button>
                            </div>


                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" role="dialog" aria-labelledby="gridSystemModalLabel" id="deleteUser" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><?= $data['lang']['suppression_user'] ; ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center"><?= $data['lang']['user_suppression_message_confirm'].' '.$data['user']->prenom.' '.$data['user']->nom.' ?' ; ?></div>
                </div>


            </div>
            <form method="post" action="<?= ROOT ?>utilisateur/desactiverUser">
                <input type="hidden" name="iduser" value="<?= base64_encode($data['user']->rowid); ?>">
                <div class="modal-footer">
                    <button type="reset" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['non'] ; ?></button>
                    <button type="submit" name="delete" value="delete" class="btn btn-success"><?= $data['lang']['yes'] ; ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" role="dialog" aria-labelledby="gridSystemModalLabel" id="activeUser" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><?= $data['lang']['activation_user'] ; ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center"><?= $data['lang']['user_activation_message_confirm'].' '.$data['user']->prenom.' '.$data['user']->nom.' ?' ; ?></div>
                </div>


            </div>
            <form method="post" action="<?= ROOT ?>utilisateur/activerUser">
                <input type="hidden" name="iduser" value="<?= base64_encode($data['user']->rowid); ?>">
                <div class="modal-footer">
                    <button type="reset" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['non'] ; ?></button>
                    <button type="submit" name="delete" value="delete" class="btn btn-success"><?= $data['lang']['yes'] ; ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" role="dialog" aria-labelledby="gridSystemModalLabel" id="validation" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><?= $data['lang']['regenere_pass'] ; ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center"><?= $data['lang']['user_regeneration_message_confirm']; ?></div>
                </div>

            </div>
            <form method="post" action="<?= ROOT ?>utilisateur/resetPasswordUser">
                <input type="hidden" name="iduser" value="<?= base64_encode($data['user']->rowid); ?>">
                <input type="hidden" name="email" value="<?= $data['user']->email; ?>">
                <input type="hidden" name="prenom" value="<?= $data['user']->prenom; ?>">
                <input type="hidden" name="nom" value="<?= $data['user']->nom; ?>">
                <input type="hidden" name="login" value="<?= base64_encode($data['user']->login); ?>">
                <div class="modal-footer">
                    <button type="reset" class="btn btn-default" data-dismiss="modal"><?= $data['lang']['non'] ; ?></button>
                    <button type="submit" name="delete" value="delete" class="btn btn-success"><?= $data['lang']['yes'] ; ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- FOOTER DE LA PAGE -->
<? include(__DIR__.'/../footer.php') ;?>
<!-- END FOOTER DE LA PAGE -->

<script>

    function checkCNI() {  //CNI
        $.ajax({
            type: "POST",
            url: "<?= ROOT . 'utilisateur/checkCNI'; ?>",

            data: "cni=" + document.getElementById('cni').value+"&_type=2&id=+<?= base64_encode($data['user']->rowid); ?>+",
            success: function (data) {
                if (data == 1) {
                    $('#cni_error').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>le CNI existe deja !!!</p>");
                    $("#valider").attr("disabled", "disabled");
                    document.getElementById('cni').value='';
                }
                else if (data == -2) {
                    $('#cni_error').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Erreur verification CNI !!!</p>");
                    $("#valider").attr("disabled", "disabled");
                    document.getElementById('cni').value='';
                }
                else if (data == -1) {
                    $('#cni_error').html("");
                    //$("#valider").removeAttr("disabled", "disabled");
                }
            }
        });
    }

    /*if($('#cni').val() == '')
    {
        $('#cni').prop('required', true)
    } else {
        $('#cni').prop('required', false)
    }*/

    $(".select2").on('select2:select', function (e) {
        var selectedValue = $(this).select2('val')
        if(selectedValue == "5") {
            $("#cni_form-group").css("display", "block")
            $('#cni').prop('required', true)
        } else {
            $("#cni_form-group").css("display", "none")
            $('#cni').prop('required', false)
        }
    });

    $(document).ready(function() {
        $('#updateUser')
            .find('[name="phone"]')
            .intlTelInput({
                utilsScript: '<?= WEBROOT ?>assets/plugins/build/js/utils.js',
                autoPlaceholder: true,
                preferredCountries: ['mr','bf', 'gm', 'sn', 'gb'],
                initialDialCode: true,
                nationalMode: false
            });


        var cni = document.getElementById('cni');

        cni.addEventListener('input', function (e) {
            checkCNI()
        })
    });
</script>

<script>

    $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
    });
</script>

<script>
    function verifIdentifiant(){  //identifiant
        $.ajax({
            type: "POST",
            url: "<?= ROOT.'utilisateur/verifLogin'; ?>",

            data: "identifiant="+document.getElementById('identifiant').value,
            success: function(data) {
                if(data == 1){
                    $('#msg2').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Identifiant existe deja !!!</p>");
                    $("#valider").attr("disabled","disabled");
                }
                else if(data == -2){
                    $('#msg2').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Erreur verification identifiant !!!</p>");
                    $("#valider").attr("disabled","disabled");
                }
                else if(data== -1){
                    $('#msg2').html("");
                    $("#valider").removeAttr("disabled","disabled");
                }
            }
        });
    }
</script>

<script>
    var gagnote = document.getElementById("phone");
    gagnote.onkeydown = function(e){
        if(!((e.keyCode > 95 && e.keyCode < 106)
            || (e.keyCode > 47 && e.keyCode < 58)
            || e.keyCode == 8)){
            return false;
        }
    }



    function validateMAIL() {

        var $email = $('form input[name="email'); //change form to id or containment selector
        var re = /[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/igm;
        if ($email.val() == '' || !re.test($email.val()))
        {
            alert('Merci de donner un email valide');
            return false;
        }
    }



</script>


