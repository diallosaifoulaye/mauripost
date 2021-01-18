<?php
include(__DIR__.'/../agentheader.php');
?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page Content -->
        <!-- ============================================================== -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title"><?= $data['lang']['recharge_retrait1']; ?></h4> 
                    </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                        
                        <ol class="breadcrumb">
                            <li><a href="#"><?= $data['lang']['Tableau_de_bord']; ?></a></li>
                            <li class="active"><?= $data['lang']['recharge_retrait']; ?></li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                
                                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-wrapper collapse in">
                                <div class="panel-body">
									<center><a href="<?= ROOT.'recharge/rechargement/';?>"><img src="<?= WEBROOT.'/assets/images/002-coins.png';?>" width="64" height="64" alt=""/></a></center> 
							  </div>
                            </div>
							<a href="<?= ROOT.'recharge/rechargement/';?>"><div class="panel-heading boite"><center><?= $data['lang']['RECHARGEMENT_EN_ESPECES']; ?></center></div></a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-wrapper collapse in">
                                <div class="panel-body">
									<center><a href="<?= ROOT.'recharge/searchRetraitEspece/';?>"><img src="<?= WEBROOT.'/assets/images/001-money.png';?>" width="64" height="64" alt=""/></a></center> 
							  </div>
                            </div>
							<a href="<?= ROOT.'recharge/searchRetraitEspece/';?>"><div class="panel-heading boite"><center><?= $data['lang']['RETRAIT_EN_ESPECES']; ?></center></div></a>
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