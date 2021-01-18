<?
$userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];

$currencyCode = 'XOF';
?>
<?php
$user_admin = $userConnecter->admin;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>Mauripost</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/ionicons.min.css">
    <!-- Icheck -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/iCheck/all.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/select2/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/AdminLTE.css">
    <!-- DataTable -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/datatables/dataTables.bootstrap.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/skins/_all-skins.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <!-- jQuery 2.1.4 -->
    <script src="<?= WEBROOT ?>assets/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/administration.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/ionicons.min.css">

    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/build/css/intlTelInput.css">
    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.theme.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.structure.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />

    <link rel="shortcut icon" href="<?= WEBROOT ?>assets/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= WEBROOT ?>assets/favicon.ico" type="image/x-icon">
    <style>
        input{
            width:100%;
        }
        .content-wrapper > section.content-header{
            padding: 10px 0 10px 10px;
        }
        .content-wrapper > section.content-header > ol.breadcrumb {
            top: 8px;
        }
        /*aside.main-sidebar {*/
            /*z-index: inherit !important;*/
        /*}*/
    </style>


    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/timepicki.css">

    <script src="<?= WEBROOT ?>assets/js/timepicki.js"></script>

    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/fancybox/ekko-lightbox.min.css">


</head>
<body class="hold-transition skin-blue sidebar-mini" >
<div class="">
   <header class="main-header">
        <a href="<?= ROOT?>accueil/accueil" class="logo" style="padding: 0">
            <span class="logo-lg"><img src="<?= WEBROOT ?>assets/images/mauripost.png" style="width: 240px;margin-top: 20px;" /></span>
        </a>
        <nav class="" role="navigation" style="margin-left:380px;">


            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-custom-menu">
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <?php if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,1)==1) {  ;?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['administration']; ?>">
                                <a href="<?= ROOT ?>admin" class="mestextesblancs" ><img src="<?= WEBROOT ?>assets/images/administration2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,2)==1) {?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_carte_postecash']; ?>">
                                <a href="<?= ROOT ?>compte" class="mestextesblancs" ><img src="<?= WEBROOT ?>assets/images/gestion_carte2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,3)==1) {?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_reporting']; ?>">
                                <a href="<?= ROOT ?>reporting" class="mestextesblancs" ><img src="<?= WEBROOT ?>assets/images/reporting2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,4)==1) {?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_transfert_argent']; ?>">
                                <a href="<?= ROOT ?>transfert" class="mestextesblancs"><img src="<?= WEBROOT ?>assets/images/gestion_transfert2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } ?>
                        <?php
                        if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,5)==1) {  ;?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_marchand']; ?>">
                                <a href="<?= ROOT ?>marchand" class="mestextesblancs" ><img src="<?= WEBROOT ?>assets/images/gest_merchant_optimise2.png" width="58px"  height="53px" class="img-responsive bordure" title="" alt="" /></a>
                            </li>

                        <?php } ?>

                        <?php if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,6)==1) {?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_carte_jula']; ?>">
                                <a href="<?= ROOT ?>jula" class="mestextesblancs"><img src="<?= WEBROOT ?>assets/images/gestion_carte_KrediVOLA2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } ?>
                        <?php if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,8)==1) {?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['gestion_support']; ?>">
                                <a href="<?= ROOT ?>support" class="mestextesblancs"><img src="<?= WEBROOT ?>assets/images/gestion_support2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php } ?>

                           <?php if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,7)==1) {?>
                           <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['interdit']; ?>">
                                <a href="<?= ROOT ?>interdit" class="mestextesblancs"><img src="<?= WEBROOT ?>assets/images/gestion_carte2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        <?php  } ?>
                        <?php /*if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,13)==1) {*/?><!--

                            <li data-toggle="tooltip" data-placement="bottom" title="<?/*= $data['lang']['mts']; */?>">
                                <a href="<?/*= ROOT */?>mts/index/" class="mestextesblancs"><img src="<?/*= WEBROOT */?>assets/images/gestion_transfert2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        --><?php /*} */?>



                       <!-- <?php /*if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,9)==1) {*/?>
                            <li data-toggle="tooltip" data-placement="bottom" title="<?/*= $data['lang']['gestion_tontine']; */?>">
                                <a href="<?/*= ROOT */?>dash" class="mestextesblancs"><img src="<?/*= WEBROOT */?>assets/images/gestion_transfert2.png" class="img-responsive" title="" alt="" /></a>
                            </li>
                        --><?php /*} */?>
                        <?php /*if($user_admin==1 || $this->utils->Acces_module($userConnecter->profil,9)==1) {*/?><!--
                            <li data-toggle="tooltip" data-placement="bottom" title="<?/*= $data['lang']['gestion_paiement']; */?>">
                                <a href="<?/*= ROOT */?>bills" class="mestextesblancs"><img src="<?/*= WEBROOT */?>assets/images/gestion_transfert2.png" class="img-responsive bordure" title="" alt="" /></a>
                            </li>
                        --><?php /*} */?>

                        <li data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['deconnecter']; ?>">
                            <a
                                    data-toggle="modal" data-target="#deconnect"
                                    class="mestextesblancs" title="<?php echo $data['lang']['deconnecter'];?>"><img src="<?= WEBROOT ?>assets/images/logout.png" class="img-responsive" title="" alt="" /></a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class="notifier-bar" style="margin-bottom:30px;color: white!important;">

        <?php
        $actual_link = explode('/', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

        /*if($actual_link[3] === 'reporting'){
            $allMessages=$this->messageModel->afficheAllMessage(3);
        }
        else if($actual_link[3] === 'admin'){
            $allMessages=$this->messageModel->afficheAllMessage(1);
        }
        else if($actual_link[3] === 'compte'){
            $allMessages=$this->messageModel->afficheAllMessage(2);
        }
        else if($actual_link[3] === 'transfert'){
            $allMessages=$this->messageModel->afficheAllMessage(4);
        }
        else if($actual_link[3] === 'marchand'){
            $allMessages=$this->messageModel->afficheAllMessage(5);
        }
        else if($actual_link[3] === 'support'){
            $allMessages=$this->messageModel->afficheAllMessage(8);
        }
        else if($actual_link[3] === 'ccp'){
            $allMessages=$this->messageModel->afficheAllMessage(7);
        }
        else if($actual_link[3] === 'jula'){
            $allMessages=$this->messageModel->afficheAllMessage(6);
        }
        else{
            $allMessages=$this->messageModel->afficheAllMessage(0);

        }*/

        if($actual_link[5] === 'reporting'){
            $allMessages=$this->messageModel->afficheAllMessage(3);
        }
        else if($actual_link[5] === 'admin'){
            $allMessages=$this->messageModel->afficheAllMessage(1);
        }
        else if($actual_link[5] === 'compte'){
            $allMessages=$this->messageModel->afficheAllMessage(2);
        }
        else if($actual_link[5] === 'transfert'){
            $allMessages=$this->messageModel->afficheAllMessage(4);
        }
        else if($actual_link[5] === 'marchand'){
            $allMessages=$this->messageModel->afficheAllMessage(5);
        }
        else if($actual_link[5] === 'support'){
            $allMessages=$this->messageModel->afficheAllMessage(8);
        }
        else if($actual_link[5] === 'ccp'){
            $allMessages=$this->messageModel->afficheAllMessage(7);
        }
        else if($actual_link[3] === 'jula'){
            $allMessages=$this->messageModel->afficheAllMessage(6);
        }
        else{
            $allMessages=$this->messageModel->afficheAllMessage(0);

        }



        ?>
        <marquee>
            <?php

            foreach ($allMessages as $oneMessage){
                echo $oneMessage['expediteur'].' : '.$oneMessage['txt_messenger'];
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

            } ?>

        </marquee>

    </div>
    <div style="float: right; color: #686868; font-weight: bold; margin-right: 20px; margin-bottom: 10px; margin-top: -25px;"><?php echo $data['lang']['solde_actuel'].': '.$this->utils->nombre_form($this->utils->getSoldeAgence($userConnecter->fk_agence)).' '.$data['lang']['currency']; ?></div>
    <div class="modal fade" role="dialog" aria-labelledby="gridSystemModalLabel" id="deconnect" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <!--                       <h4 class="modal-title" id="gridSystemModalLabel">--><?//= $data['lang']['suppression_user'] ; ?><!--</h4>-->
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="text-center"><?php echo $data['lang']['user_logout_confirm']; ?></div>
                    </div>
                </div>
                <form method="post" action="<?= ROOT ?>home/deconnecter">
                    <div class="modal-footer">

                        <input type="hidden" name="login" value="<?php echo $userConnecter->login; ?>">
                        <input type="hidden" value="<?php echo $this->utils->get_token(); ?>" name="<?= $this->utils->get_token_id(); ?>">
                        <button type="reset" class="btn btn-default pull-left" data-dismiss="modal"><?= $data['lang']['non'] ; ?></button>
                        <button type="submit" name="delete" value="delete" class="btn btn-success pull-right"><?= $data['lang']['yes'] ; ?></button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>