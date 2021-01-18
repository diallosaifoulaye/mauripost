

<aside class="main-sidebar">

    <? include( __DIR__.'/../entete_sidebar.php') ;?>


    <ul class="sidebar-menu">
        <li class="header"><?= $data['lang']['postecash_reporting'] ; ?></li>
        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(32, $userConnecter->profil) == 1) { ?>
        <li <?php if( $thispage === 'reporting_jour'){ echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/reportingdujour"><i class="fa fa-sitemap"></i> <span><?= $data['lang']['list_transac_jr']; ?></span></a>
        </li>
        <?php } ?>
        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(33, $userConnecter->profil) == 1) { ?>
        <li <?php if( $thispage === 'reporting_date'){ echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/reportingsearchdate"><i class="fa fa-sitemap"></i> <span><?= $data['lang']['reporting_date']; ?></span></a>
        </li>
        <?php } ?>
        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(34, $userConnecter->profil) == 1) { ?>
        <li <?php if( $thispage === 'reporting_produit'){ echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/reportingsearchproduit"><i class="fa fa-cogs"></i> <span><?= $data['lang']['reporting_produit']; ?></span></a>
        </li>
        <?php } ?>
        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(35, $userConnecter->profil) == 1) { ?>
        <li class="header"><?= $data['lang']['tableau_bord'] ; ?></li>
        <li <?php if($thispage === 'visualiser'){ echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/tableaudeboard"><i class="fa fa-cogs"></i> <span><?= $data['lang']['visualiser']; ?></span></a>
        </li>
        <li <?php if($thispage === 'courbe_evolution'){ echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/searchcourbe"><i class="fa fa-cogs"></i> <span><?= $data['lang']['courbe_evolution']; ?></span></a>
        </li>
        <li <?php if($thispage === 'detail_rechargement') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/detailsearchrecharge"><i class="fa fa-cogs"></i> <span><?= $data['lang']['detail_rechargement']; ?></span></a>
        </li>
        <li <?php if($thispage === 'detail_retrait_transfert') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/detailsearchretrait"><i class="fa fa-cogs"></i> <span><?= $data['lang']['detail_retrait_tiers']; ?></span></a>
        </li>
        <li <?php if($thispage === 'detail_retrait_titulaire') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/detailsearchtitulaire"><i class="fa fa-cogs"></i> <span><?= $data['lang']['detail_retrait_titulaire']; ?></span></a>
        </li>
        <li <?php if($thispage === 'bordereau_rechargement') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/bordereausearch"><i class="fa fa-cogs"></i> <span><?= $data['lang']['bordereau_rechargement']; ?></span></a>
        </li>
        <li <?php if($thispage === 'bordereau_retrait') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/bordereauretraitsearch"><i class="fa fa-cogs"></i> <span><?= $data['lang']['bordereau_retrait']; ?></span></a>
        </li>
        <li <?php if($thispage === 'tableau_bord_general') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/dashboardsearch"><i class="fa fa-cogs"></i> <span><?= $data['lang']['tableau_bord_general']; ?></span></a>
        </li>
        <?php } ?>
        <?php if ($user_admin == 1 || $this->utils->Est_autoriser(276, $userConnecter->profil) == 1) { ?>
        <li <?php if($thispage === 'commissionParProduit') { echo 'class="active"'; } ?>>
            <a href="<?= ROOT ?>reporting/reportingSearchCommission"><i class="fa fa-cogs"></i> <span><?= $data['lang']['commission_produit']; ?></span></a>
        </li>
        <?php } ?>
    </ul>


        </ul>
    </section>
    <!-- /.sidebar -->
</aside>


