<?
$userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $data['lang']['titre_administration']; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/ionicons.min.css">

    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/AdminLTE.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/skins/_all-skins.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->

    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/css/administration.css">

    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="<?= WEBROOT ?>assets/plugins/build/css/intlTelInput.css">

    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.theme.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.structure.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= WEBROOT ?>assets/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    
    <link href="<?= WEBROOT ?>assets/SpryAssets/SpryAccordion.css" rel="stylesheet" type="text/css" />

    <style>
        input{
            width:100%;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="">

