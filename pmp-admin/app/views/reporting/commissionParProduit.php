<!-- HEADER DE LA PAGE -->

<? include( __DIR__.'/../header.php') ;?>
<? $thispage = 'commissionParProduit'; ?>

<!-- END HEADER DE LA PAGE -->




<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['commission_produit']; ?>
        </h1>
        <ol class="breadcrumb">
            <!-- <li><a href="#" data-toggle="modal" data-target="#addUser"><i class="fa fa-user"></i> <?= $data['lang']['nouvel_user']; ?></a></li> -->
        </ol>
    </section>

    <!-- END Content Header (Page header) -->


    <!-- CONTENT DE LA PAGE -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['commission_produit']; ?></h3><!-- transaction du jour -->

                    </div><!-- /.box-header -->
                    <div class="box-body">


                        <div class="table-responsive">
                            <table id="user-grid" class="table table-bordered table-hover">
                                <thead>
                                <tr class="titre_table">
                                    <th><?= $data['lang']['service']; ?></th>
                                    <th style="text-align: center !important;"><?= $data['lang']['transact_nombre']; ?></th>
                                    <th style="text-align: right !important;"><?= $data['lang']['commission']; ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $totaleSomme = 0;
                                $nbreTransactionTotale = 0;
                                foreach ($data['commissionParProduit'] as $com) {
                                    $somme = $com['somme'];
                                    $nbreTransaction =  $com['nbre'];
                                    $totaleSomme += $somme;
                                    $nbreTransactionTotale += $nbreTransaction;


                                ?>
                                    <tr>
                                        <td><?= $com['label'] ?></td>
                                        <td style="text-align: center !important;"><?php if($nbreTransaction > 0) echo $this->utils->number_format($nbreTransaction); else echo 0; ?></td>
                                        <td style="text-align: right !important;"><?php if($somme > 0) echo $this->utils->number_format($somme); else echo 0; ?></td>


                                    </tr>
                                <?php  } ?>
                                </tbody>



                                <tfoot>
                                <tr class="titre_table">
                                    <th style="text-align: right !important;"><?= $data['lang']['total']; ?></th>
                                    <th style="text-align: center !important;"><?= $this->utils->number_format($nbreTransactionTotale); ?></th>
                                    <th style="text-align: right !important;"><?= $this->utils->number_format($totaleSomme); ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div><!-- /.box-body -->


                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>
<!-- END CONTENT DE LA PAGE -->


<!-- FOOTER DE LA PAGE -->
<? include(__DIR__.'/../footer.php') ;?>
<!-- END FOOTER DE LA PAGE -->

<!--<script src="<?/*= ROOT.'/assets/plugins/datatables/jquery.dataTables.min.js';*/?>"></script>
<script src="<?/*= ROOT.'/assets/plugins/datatables/dataTables.bootstrap.min.js';*/?>"></script>
-->

