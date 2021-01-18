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

                            <li><a href="#" data-toggle="modal" data-target="#addUser"><i class="fa fa-user"></i> <?= $data['lang']['nouvel_user']; ?></a></li>

                    </ol>

            </div>
            <?php if ($type_alert != '' && $alert != '') { ?>

                <div class="alert <?= $type_alert ?> alert-dismissable" id="success-alert">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                    </button>
                    <p><?= $alert ?> </p>
                </div>

            <?php } ?>
            <!-- /.col-lg-12 -->
        </div>
        <!-- CONTENT DE LA PAGE -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?= $data['lang']['list_users']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="false">
                        <div class="panel-body">

                         <table id="user-grid" class="table table-bordered table-hover table-responsive">
                                <thead>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['prenom']; ?></th>
                                    <th><?= $data['lang']['nom']; ?></th>
                                    <th><?= $data['lang']['email']; ?></th>
                                    <th><?= $data['lang']['tel']; ?></th>
                                    <th><?= $data['lang']['profil']; ?></th>
                                    <th><?= $data['lang']['statut']; ?></th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>

                                <tfoot>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['prenom']; ?></th>
                                    <th><?= $data['lang']['nom']; ?></th>
                                    <th><?= $data['lang']['email']; ?></th>
                                    <th><?= $data['lang']['tel']; ?></th>
                                    <th><?= $data['lang']['profil']; ?></th>
                                    <th><?= $data['lang']['statut']; ?></th>
                                    <th>&nbsp;</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.content -->
</div>
<!-- END CONTENT DE LA PAGE -->


<div class="modal fade bs-example-modal-lg" role="dialog" aria-labelledby="myLargeModalLabel" id="addUser"
     data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h3 class="panel-title"><?= $data['lang']['nouvel_user']; ?></h3>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" method="post" id="ajoutuser" action="<?= ROOT ?>utilisateur/inserUser">
                        <input type="hidden" name="code_guichet" value="0"/>
                        <input type="hidden" name="type_compte" value="0"/>

                        <div class="form-group">
                            <label for="identifiant"
                                   class="col-sm-5 control-label"><?= $data['lang']['identifiant'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">

                                <input class="form-control" type="text" required id="identifiant" name="login" placeholder="<?= $data['lang']['entrer_login_user']; ?>" onchange="verifIdentifiant()">
                                <span id="msg2"></span>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prenom"
                                   class="col-sm-5 control-label"><?= $data['lang']['prenom'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">

                                <input class="form-control" type="text" required id="prenom" name="prenom" placeholder="<?= $data['lang']['prenom']; ?>">

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nom"
                                   class="col-sm-5 control-label"><?= $data['lang']['nom'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="text" required id="nom" name="nom" placeholder="<?= $data['lang']['nom']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email"
                                   class="col-sm-5 control-label"><?= $data['lang']['email'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="email" required id="email" name="email" placeholder="<?= $data['lang']['email']; ?>" onchange="validateMAIL()">
                                <span id="msg222"></span>
                            </div>
                        </div>

                        <div class="form-group" style="color: #FB9100">
                            <label for="phone"
                                   class="col-sm-5 control-label"><?= $data['lang']['tel'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="tel" required id="phone" name="phone" placeholder="" pattern="^[0-9-+]*$">

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cat"
                                   class="col-sm-5 control-label"><?= $data['lang']['profil'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">

                                <select class="select2" required name="profil" style="width: 100%;" id="cat">
                                    <option selected="selected" value=""><?= $data['lang']['profil']; ?></option>
                                    <?php foreach ($data['profil'] as $cat) { ?>
                                        <option value="<?= $cat['rowid']; ?>"><?= $cat['label']; ?></option>
                                    <?php } ?>
                                </select>

                            </div>
                        </div>
                        <div class="form-group" id="cni_form-group" style="display: none">
                            <label for="cni"
                                   class="col-sm-5 control-label"><?= $data['lang']['cni_piece'] . ' (*) :'; ?></label>
                            <div class="col-sm-7">
                                <input class="form-control" type="text" id="cni" name="cni" placeholder="<?= $data['lang']['cni_piece']; ?>" onchange="checkCNI()">
                                <span id="cni_error"></span>
                            </div>
                        </div>

                        <div class="form-group">

                        </div>
                        <div class="modal-footer">
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="reset" class="btn btn-danger"
                                        data-dismiss="modal"><?= $data['lang']['annuler']; ?></button>
                            </div>
                            <div class="col-sm-offset-2 col-sm-3">
                                <button type="submit" name="valider" id="valider" class="btn btn-success"
                                        disabled="disabled"><?= $data['lang']['enregistrer']; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- /.container-fluid -->
<footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
</div>

<!-- FOOTER DE LA PAGE -->
<? include(__DIR__ . '/../footer.php'); ?>
<!-- END FOOTER DE LA PAGE -->

<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->

<script type="text/javascript" src="<?= WEBROOT ?>assets/js/oXHR.js"></script>
<script src="<?= WEBROOT.'/assets/datatables/jquery.dataTables.min.js';?>"></script>
<script src="<?= WEBROOT.'/assets/datatables/dataTables.bootstrap.min.js';?>"></script>
<script type="text/javascript" language="javascript" >
    $(document).ready(function() {
        var lang = '<?= $_COOKIE['lang'];?>';
        // alert(lang);
        var lang_url = '';
        if(lang == 'fr') {
            lang_url = '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json';
        }
        else {
            lang_url = '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/English.json';
        }

        var dataTable = $('#user-grid').DataTable( {
            "language": {
                url: lang_url  },
            "processing": true,
            "serverSide": true,
            "ajax":{
                url :"<?= ROOT .'utilisateur/processingUser'; ?>", // json datasource
                type: "post",  // method  , by default get
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3"><?= $data['lang']['no_data'];?></th></tr></tbody>');
                    $("#employee-grid_processing").css("table","none");

                }
            }
        } );
    } );
</script>


<script type="text/javascript" language="javascript">
    $(document).ready(function () {

        $('#ajoutuser').find('[name="phone"]').intlTelInput({
            utilsScript: '<?= WEBROOT ?>assets/plugins/build/js/utils.js',
            autoPlaceholder: true,
            preferredCountries: ['mr','bj', 'bf', 'gm', 'sn', 'gb'],
            initialDialCode: true,
            nationalMode: false
        });
    });
    $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
        $(".select3").select2();
        $(".select4").select2();
    });
    function verifIdentifiant() {  //identifiant
        $.ajax({
            type: "POST",
            url: "<?= ROOT . 'utilisateur/verifLogin'; ?>",

            data: "identifiant=" + document.getElementById('identifiant').value,
            success: function (data) {
                if (data == 1) {
                    $('#msg2').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Identifiant existe deja !!!</p>");
                    $("#valider").attr("disabled", "disabled");
                    document.getElementById('identifiant').value='';
                }
                else if (data == -2) {
                    $('#msg2').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Erreur verification identifiant !!!</p>");
                    $("#valider").attr("disabled", "disabled");
                    document.getElementById('identifiant').value='';
                }
                else if (data == -1) {
                    $('#msg2').html("");
                    $("#valider").removeAttr("disabled", "disabled");
                }
            }
        });
    }
    var gagnote = document.getElementById("phone");
    gagnote.onkeydown = function (e) {
        if (!((e.keyCode > 95 && e.keyCode < 106)
            || (e.keyCode > 47 && e.keyCode < 58)
            || e.keyCode == 8)) {
            return false;
        }
    };

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

    function checkCNI() {  //CNI
        $.ajax({
            type: "POST",
            url: "<?= ROOT . 'utilisateur/checkCNI'; ?>",

            data: "cni=" + document.getElementById('cni').value+"&_type=1",
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

</script>

<script>

    function setDataProv(id){
        $.get("<?= ROOT.'utilisateur/setDataAgence/'; ?>"+id, function(data, status){
            if(status === 'success'){
                var container = $("#selector-agence");
                $(".select3").remove();
                container.html(data);
                $(".select3").select2();
            }
        });
    }

    function validateMAIL() {

        var email = $('form input[name="email'); //change form to id or containment selector
        var re = /[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/igm;
        if (email.val() == '' || !re.test(email.val())) {
            alert('Merci de donner un email valide');
            return false;
        }
        else{
            $.ajax({
                type: "POST",
                url: "<?= ROOT . 'utilisateur/verifEmail'; ?>",

                data: "email=" + document.getElementById('email').value,
                success: function (data) {

                    if (data == 1) {
                        $('#msg222').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Adresse email existe deja !!!</p>");
                        $("#valider").attr("disabled", "disabled");
                        document.getElementById('email').value='';
                    }
                    else if (data == -2) {
                        $('#msg222').html("<p style='color:#F00;display: inline;border: 1px solid #F00'>Erreur verification adresse email !!!</p>");
                        $("#valider").attr("disabled", "disabled");
                        document.getElementById('email').value='';
                    }
                    else if (data == -1) {
                        $('#msg222').html("");
                        $("#valider").removeAttr("disabled", "disabled");
                    }
                }
            });
        }
    }
</script>


