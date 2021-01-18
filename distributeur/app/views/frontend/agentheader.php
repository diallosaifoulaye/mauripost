<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 22/08/2017
 * Time: 16:58
 */

$obj = $this->getSession()->getAttribut('objconnect');

$agence = $obj->getFk_agence();
$user = $obj->getRowid();

$username = $obj->getPrenom().' '.$obj->getNom();
$nomagence = $obj->getLabel();
$solde_carte =  $this->utils->soldeCarteDist($agence, $user);
$solde_compte = $this->utils->soldeCompteDist($agence);

$user_admin = $obj->getAdmin();
$profil = $obj->getFk_profil();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="icon" type="image/png" sizes="16x16" href="<?= WEBROOT;?>/assets/plugins/images/favicon.ico">

    <title><?= $data['lang']['title']; ?></title>
    <link href="<?= WEBROOT.'/assets/bootstrap/dist/css/bootstrap.min.css';?>" rel="stylesheet">
    <link href="<?= WEBROOT.'/assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css';?>" rel="stylesheet">
    <link href="<?= WEBROOT.'/assets/css/animate.css';?>" rel="stylesheet">
    <link href="<?= WEBROOT.'/assets/css/style.css';?>" rel="stylesheet">
    <link href="<?= WEBROOT.'/assets/css/colors/default.css';?>" id="theme" rel="stylesheet">
    <link rel="stylesheet" href="<?= WEBROOT.'/assets/datatables/dataTables.bootstrap.css';?>" >
    <link rel="stylesheet" href="<?= WEBROOT ?>/assets/select2/select2.min.css">

    <link href="<?= WEBROOT ?>/assets/js/jquery-ui/jquery-ui.theme.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>/assets/js/jquery-ui/jquery-ui.structure.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>/assets/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />

    <!-- toast CSS -->
    <link href="<?= WEBROOT ?>/assets/plugins/bower_components/toast-master/css/jquery.toast.css" rel="stylesheet">
    <!-- morris CSS -->
    <link href="<?= WEBROOT ?>/assets/plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- chartist CSS -->
    <link href="<?= WEBROOT ?>/assets/plugins/bower_components/chartist-js/dist/chartist.min.css" rel="stylesheet">
    <link href="<?= WEBROOT ?>/assets/plugins/bower_components/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet">
    <!-- Calendar CSS -->
    <link href="<?= WEBROOT ?>/assets/plugins/bower_components/calendar/dist/fullcalendar.css" rel="stylesheet" />
    <link href="<?= WEBROOT ?>/assets/plugins/build/css/intlTelInput.css" rel="stylesheet">

</head>

<body class="fix-header">
<!-- ============================================================== -->

<!-- ============================================================== -->
<!-- Wrapper -->
<!-- ============================================================== -->
<div id="wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <nav class="navbar navbar-default navbar-static-top m-b-0">
        <div class="navbar-header">
            <div class="top-left-part">
                <!-- Logo -->
                <a class="logo" href="<?= ROOT.'accueil/dashbord'; ?>">
                        <span class="hidden-xs">
                        <img src="<?= WEBROOT.'/assets/plugins/images/postecash-black.png';?>" alt="home" class="light-logo" height="50px"/>
                     </span>
                </a>
            </div>
            <!-- /Logo -->
            <!-- Search input and Toggle icon -->
            <ul class="nav navbar-top-links navbar-left">
                <li><a href="javascript:void(0)" class="open-close waves-effect waves-light"><i class="ti-menu"></i></a></li>
                <li class="mega-dropdown">
                    <a class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#"><span class="hidden-xs">Menu</span> <i class="icon-options-vertical"></i></a>

                    <ul class="dropdown-menu mega-dropdown-menu animated bounceInDown">


                        <li class="col-sm-2">

                        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(70, $profil) == 1) { ?>

                            <ul>
                                <li class="dropdown-header"><?= $data['lang']['gestion_user'];?></li>
                                <li>
                                    <a href="<?= ROOT.'utilisateur/users';?>"><i class="ti-user fa-fw"></i> <span class="hide-menu">
                                            <?= $data['lang']['gestion_user'];?></span>
                                    </a>
                                </li>
                            </ul>

                        <?php }  if($user_admin == 1 || $this->utils->Est_autoriser(9, $profil) == 1 || $this->utils->Est_autoriser(37, $profil) == 1) { ?>

                        </li>

                        <li class="col-sm-2">

                            <ul>

                                <li class="dropdown-header"><?= $data['lang']['gestion_carte'];?></li>

                                <?php if ($user_admin == 1 || $this->utils->Est_autoriser(9, $profil) == 1) { ?>

                                    <li><a href="<?= ROOT.'carte/createCompte';?>"><i class="ti-user fa-fw" ></i><span class="hide-menu"><?= $data['lang']['creer_compte'];?></span></a></li>

                                    <!--<li><a href="<?/*= ROOT.'carte/createCarte';*/?>"><i class="ti-user fa-fw"></i><span class="hide-menu"><?/*= $data['lang']['enrole_carte'];*/?></span></a></li>-->

                                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(37, $profil) == 1) { ?>

                                <li><a href="<?= ROOT.'carte/beneficiaire';?>"><i class="ti-align-justify  fa-fw"></i><span class="hide-menu"><?= $data['lang']['liste_compte'];?></span></a></li>

                                <?php } ?>

                            </ul>

                        </li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(46, $profil) == 1 || $this->utils->Est_autoriser(47, $profil) == 1) { ?>

                        <li class="col-sm-2">

                            <ul>

                                <li class="dropdown-header"><?= $data['lang']['recharge_retrait'];?></li>

                                <?php if ($user_admin == 1 || $this->utils->Est_autoriser(46, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'recharge/rechargement/';?>"><i class="ti-reload  fa-fw"></i><span class="hide-menu"><?= $data['lang']['recharge'];?></span></a>
                                    </li>

                                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(47, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'recharge/searchRetraitEspece/';?>"><i class="ti-export fa-fw"></i><span class="hide-menu"><?= $data['lang']['retait'];?></span></a>
                                    </li>

                                <?php } ?>

                            </ul>

                        </li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(224, $profil) == 1 || $this->utils->Est_autoriser(240, $profil) == 1 || $this->utils->Est_autoriser(280, $profil) == 1) { ?>


                            <li class="col-sm-2">
                            <ul>
                                <li class="dropdown-header"><?= $data['lang']['transfert'];?></li>
                                <li><a href="<?= ROOT.'transfert/envoi';?>"><i class="ti-credit-card fa-fw"></i><span class="hide-menu"><?= $data['lang']['envoi'];?></span></a></li>
                                <li><a href="<?= ROOT.'transfert/paiement';?>"><i class="ti-share fa-fw"></i><span class="hide-menu"><?= $data['lang']['paiement'];?></span></a></li>
                                <li><a href="<?= ROOT.'transfert/historiqueReception';?>"><i class="ti-comments-smiley fa-fw"></i><span class="hide-menu"><?= $data['lang']['transfert_jour'];?></span></a></li>
                            </ul>
                            </li>



                        <li class="col-sm-2">

                            <ul>

                                <li class="dropdown-header"><?= $data['lang']['paiement'];?></li>

                                <?php if ($user_admin == 1 || $this->utils->Est_autoriser(224, $profil) == 1) { ?>

                                <li>
                                    <a href="<?= ROOT.'facturier/paiementpostale';?>">
                                        <i data-icon="&#xe026;" class="linea-icon linea-basic fa-fw"></i> <span class="hide-menu"><?= $data['lang']['boite_postal'];?></span>
                                    </a>
                                </li>

                                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(240, $profil) == 1) { ?>

                                <li>
                                    <a href="<?= ROOT.'reservation/reservationtranspost';?>">
                                        <i data-icon="&#xe025;" class="linea-icon linea-basic fa-fw"></i> <span class="hide-menu"><?= $data['lang']['Reserv_transpost'];?></span>
                                    </a>
                                </li>

                                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(280, $profil) == 1) { ?>

                                <li>
                                    <a href="<?= ROOT.'facturier/paiementjirama';?>">
                                        <i class="ti-layout-menu fa-fw"></i> <span class="hide-menu"><?= $data['lang']['jirama'];?></span>
                                    </a>
                                </li>

                                <?php } ?>

                            </ul>

                        </li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(35, $profil) == 1 || $this->utils->Est_autoriser(34, $profil) == 1 || $this->utils->Est_autoriser(33, $profil) == 1 || $this->utils->Est_autoriser(32, $profil) == 1) { ?>

                        <li class="col-sm-2">

                            <ul>
                                <li class="dropdown-header"><?= $data['lang']['reporting'];?></li>

                                <?php if($user_admin == 1 || $this->utils->Est_autoriser(32, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'reporting/reportingdujour';?>">
                                            <i data-icon="&#xe026;" class="mdi mdi-clock-fast fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_jour'];?></span>
                                        </a>
                                    </li>

                                <?php } if($user_admin == 1 || $this->utils->Est_autoriser(33, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'reporting/reportingsearchdate';?>">
                                            <i data-icon="&#xe025;" class="mdi mdi-chart-line fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_date'];?></span>
                                        </a>
                                    </li>

                                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(34, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'reporting/reportingsearchproduit';?>">
                                            <i data-icon="&#xe025;" class="mdi mdi-chart-line fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_prod'];?></span>
                                        </a>
                                    </li>

                                <?php } if($user_admin == 1 || $this->utils->Est_autoriser(35, $profil) == 1) { ?>

                                    <li>
                                        <a href="<?= ROOT.'reporting/index';?>" class="waves-effect">
                                            <i class="mdi mdi-content-copy fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting'];?></span>
                                        </a>
                                    </li>

                                <?php } ?>

                            </ul>

                        </li>

                        <?php }  ?>

                    </ul>
                </li>
            </ul>

            <div class="col-sm-4 pull-right text-right" style="padding-top: 20px; padding-left: 20px">
                <span style="font-weight: bold; color: #2ca73a"><?= $data['lang']['distributeur'].':';?> </span>
                <span><?= $nomagence;?> </span>
                <span style="font-weight: bold; color: #2ca73a">|</span>

                <?php //if ($user_admin == 1 || $this->utils->Est_autoriser(286, $profil) == 1) {?>

                <span style="font-weight: bold; color: #2ca73a"><?= $data['lang']['solde_actuel'].': ';?> </span>
                <span ><?= $this->utils->number_format($solde_compte);?> </span>
                <span ><?= $data['lang']['currency']; ?> </span>

                <?php //} ?>
            </div>

        </div>
        <!-- /.navbar-header -->
        <!-- /.navbar-top-links -->
        <!-- /.navbar-static-side -->
    </nav>
    <!-- End Top Navigation -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav slimscrollsidebar">
            <div class="sidebar-head">
                <h3><span class="fa-fw open-close"><i class="ti-close ti-menu"></i></span> <span class="hide-menu">Navigation</span></h3> </div>

            <div class="user-profile">
                <div class="dropdown user-pro-body">
                    <div><img src="<?= WEBROOT.'/assets/plugins/images/users/usr.png';?>" alt="user-img" class="img-circle"></div>
                    <a href="#" class="dropdown-toggle u-dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= $obj->getPrenom().' '.$obj->getNom(); ?><span class="caret"></span></a>
                    <ul class="dropdown-menu animated flipInY">
                        <li><a href="<?= ROOT.'login/editerPass';?>"><i class="ti-settings"></i> <?= $data['lang']['change_pass'];?></a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="<?= ROOT . 'login/logout'; ?>"><i class="fa fa-power-off"></i> <?= $data['lang']['loggout'];?></a></li>
                    </ul>
                </div>
            </div>

            <ul class="nav" id="side-menu">

                <!--Gestion des utilisateurs-->

                <?php if ($user_admin == 1 || $this->utils->Est_autoriser(70, $profil) == 1) { ?>

                    <li><a href="<?= ROOT.'utilisateur/users';?>"><i class="ti-user  fa-fw"></i><span class="hide-menu"><?= $data['lang']['gestion_user'];?></span></a></li>

                <?php }  ?>

                <?php if($user_admin == 1 || $this->utils->Est_autoriser(9, $profil) == 1 || $this->utils->Est_autoriser(37, $profil) == 1) { ?>

                <!--Gestion des cartes-->

                <li>
                    <a href="#" class="waves-effect"><i class="mdi mdi-credit-card-multiple fa-fw"></i> <span class="hide-menu"><?= $data['lang']['gestion_carte'];?><span class="fa arrow"></span></span></a>

                    <ul class="nav nav-second-level">

                        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(9, $profil) == 1) { ?>

                            <li>
                                <a href="<?= ROOT.'carte/createCompte';?>">
                                    <i class="ti-user fa-fw" ></i><span class="hide-menu"><?= $data['lang']['creer_compte'];?></span>
                                </a>
                            </li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(9, $profil) == 1) { ?>

                           <!-- <li>
                                <a href="<?/*= ROOT.'carte/createCarte';*/?>">
                                    <i class="ti-user fa-fw"></i><span class="hide-menu"><?/*= $data['lang']['enrole_carte'];*/?></span>
                                </a>
                            </li>-->

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(37, $profil) == 1) { ?>

                            <li>
                                <a href="<?= ROOT.'carte/beneficiaire';?>">
                                    <i class="ti-align-justify  fa-fw"></i><span class="hide-menu"><?= $data['lang']['liste_compte'];?></span>
                                </a>
                            </li>

                        <?php } ?>

                    </ul>

                </li>

                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(46, $profil) == 1 || $this->utils->Est_autoriser(47, $profil) == 1) { ?>

                <!--Rechergement retrait-->
                <li><a href="#" class="waves-effect"><i class="mdi mdi-format-rotate-90 fa-fw"></i> <span class="hide-menu"><?= $data['lang']['recharge_retrait'];?><span class="fa arrow"></span></span></a>
                    <ul class="nav nav-second-level">

                        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(46, $profil) == 1) { ?>

                            <li><a href="<?= ROOT.'recharge/rechargement/';?>"><i class="ti-reload  fa-fw"></i><span class="hide-menu"><?= $data['lang']['recharge'];?></span></a></li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(47, $profil) == 1) { ?>

                            <li><a href="<?= ROOT.'recharge/searchRetraitEspece/';?>"><i class="ti-export fa-fw"></i><span class="hide-menu"><?= $data['lang']['retait'];?></span></a></li>

                       <?php }?>

                    </ul>
                </li>

                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(224, $profil) == 1 || $this->utils->Est_autoriser(240, $profil) == 1 || $this->utils->Est_autoriser(280, $profil) == 1) { ?>


                <!--Transfert d'argent-->
               <!-- <li><a href="#" class="waves-effect"><i class="mdi mdi-apps fa-fw"></i> <span class="hide-menu"><?/*= $data['lang']['transfert'];*/?><span class="fa arrow"></span></span></a>
                    <ul class="nav nav-second-level">

                        <li><a href="<?/*= ROOT.'transfert/envoi';*/?>"><i class="ti-credit-card fa-fw"></i><span class="hide-menu"><?/*= $data['lang']['envoi'];*/?></span></a></li>
                        <li><a href="<?/*= ROOT.'transfert/paiement';*/?>"><i class="ti-share fa-fw"></i><span class="hide-menu"><?/*= $data['lang']['paiement'];*/?></span></a></li>
                        <li><a href="<?/*= ROOT.'transfert/historiqueReception';*/?>"><i class="ti-comments-smiley fa-fw"></i><span class="hide-menu"><?/*= $data['lang']['transfert_jour'];*/?></span></a></li>

                    </ul>
                </li>-->

                <li> <a href="#" class="waves-effect"><i class="mdi mdi-book-open fa-fw"></i> <span class="hide-menu"><?= $data['lang']['paiement'];?><span class="fa arrow"></span></span></a>

                    <ul class="nav nav-second-level">

                        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(224, $profil) == 1) { ?>

                        <li><a href="<?= ROOT.'facturier/paiementpostale';?>"><i data-icon="&#xe026;" class="linea-icon linea-basic fa-fw"></i> <span class="hide-menu"><?= $data['lang']['boite_postal'];?></span></a></li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(240, $profil) == 1) { ?>

                        <li><a href="<?= ROOT.'reservation/reservationtranspost';?>"><i data-icon="&#xe025;" class="linea-icon linea-basic fa-fw"></i> <span class="hide-menu"><?= $data['lang']['Reserv_transpost'];?></span></a></li>

                        <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(280, $profil) == 1) { ?>

                        <li><a href="<?= ROOT.'facturier/paiementjirama';?>"><i class="ti-layout-menu fa-fw"></i> <span class="hide-menu"><?= $data['lang']['jirama'];?></span></a></li>

                        <?php }  ?>

                    </ul>

                </li>

                <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(32, $profil) == 1 || $this->utils->Est_autoriser(33, $profil) == 1 || $this->utils->Est_autoriser(35, $profil) == 1) { ?>

                <!--Reporting-->


                    <li> <a href="#" class="waves-effect"><i class="mdi mdi-chart-histogram fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting'];?><span class="fa arrow"></span></span></a>

                        <ul class="nav nav-second-level">

                            <?php if ($user_admin == 1 || $this->utils->Est_autoriser(32, $profil) == 1) { ?>

                                <li><a href="<?= ROOT.'reporting/reportingdujour';?>"><i data-icon="&#xe026;" class="mdi mdi-clock-fast fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_jour'];?></span></a></li>

                            <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(33, $profil) == 1) { ?>

                                <li><a href="<?= ROOT.'reporting/reportingsearchdate';?>"><i data-icon="&#xe025;" class="mdi mdi-chart-line fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_date'];?></span></a></li>


                            <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(34, $profil) == 1) { ?>

                                <li><a href="<?= ROOT.'reporting/reportingsearchproduit';?>"><i data-icon="&#xe025;" class="mdi mdi-chart-line fa-fw"></i> <span class="hide-menu"><?= $data['lang']['reporting_prod'];?></span></a></li>


                            <?php } if ($user_admin == 1 || $this->utils->Est_autoriser(276, $profil) == 1) { ?>

                                <li> <a href="<?= ROOT.'reporting/index';?>" class="waves-effect"><i class="mdi mdi-chart-pie fa-fw"></i> <span class="hide-menu"><?= $data['lang']['dash'];?></span></a></li>
                                <li> <a href="<?= ROOT.'reporting/commisionTotaleDistributeur';?>" class="waves-effect"><i class="mdi mdi-chart-pie fa-fw"></i> <span class="hide-menu"><?= $data['lang']['Ma_commission_totale'];?></span></a></li>

                            <?php }  ?>

                        </ul>

                    </li>


                <?php } ?>

                <li class="devider"></li>

            </ul>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- End Left Sidebar -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page Content -->
    <!-- ============================================================== -->
