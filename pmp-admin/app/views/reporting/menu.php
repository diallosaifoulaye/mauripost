
<header class="main-header">
        <!-- Logo -->
        <a href="<?= ROOT ?>accueil/accueil" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <!--<span class="logo-mini"><img src="<?= WEBROOT ?>assets/images/logo.png" width="40" height="34"/></span>-->
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><img src="<?= WEBROOT ?>assets/images/postecash.png" /></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation" style="margin-left:380px;">
            <!-- Sidebar toggle button-->

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">


                    <li style="color: white; font-size: medium" data-toggle="tooltip" data-placement="bottom" title="<?= $data['lang']['deconnecter']; ?>">
                        <a href="<?= ROOT ?>admin/logout" class="mestextesblancs" title="<?= $data['lang']['deconnecter']; ?>"><img src="<?= WEBROOT ?>assets/images/logout.png" class="img-responsive" title="" alt="" /></a>
                    </li>

                </ul>
            </div>
        </nav>
    </header>

    <div class="notifier-bar" style="margin-bottom:30px;"><marquee><?= $data['lang']['txt_messenger']; ?></marquee></div>
