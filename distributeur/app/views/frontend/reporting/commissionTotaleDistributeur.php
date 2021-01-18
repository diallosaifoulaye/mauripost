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
                    <li class="active"><?= $data['lang']['Ma_commission_totale']; ?></li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>


        <!-- CONTENT DE LA PAGE -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?= $data['lang']['Ma_commission_totale']; ?></div>
                    <div class="panel-wrapper collapse in" aria-expanded="false">
                        <div class="panel-body">

                            <div class="table-responsive">
                                <table id="user-grid" class="table table-bordered table-hover">
                                    <thead>
                                    <tr class="titre_table">
                                        <th><?= $data['lang']['service']; ?></th>
                                        <th style="text-align: right !important;"><?= $data['lang']['nombre_transaction'] ; ?></th>
                                        <th style="text-align: right !important;"><?= strtoupper($data['lang']['commission']) .' '.$data['lang']['currency']  ; ?></th>

                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                        $totale = 0;
                                        $nbreTransactionTotale = 0;
                                        foreach ($data['commission'] as $com) {

                                        $commission = $this->utils->commissionDistributeurService($com['fk_distributeur'], $com['fk_service'], $com['taux']);
                                        $nbreTransaction = $this->utils->nombreTransactionDistributeurService($com['fk_distributeur'], $com['fk_service']);

                                        $totale += $commission;
                                        $nbreTransactionTotale += $nbreTransaction ;

                                        ?>
                                    <tr>
                                        <td><?= $com['label'] ?></td>
                                        <td style="text-align: right !important;"><?php if($nbreTransaction > 0) echo $this->utils->number_format($nbreTransaction); else echo 0; ?></td>
                                        <td style="text-align: right !important;"><?php if($commission > 0) echo $this->utils->number_format($commission); else echo 0; ?></td>

                                    </tr>
                                    <?php  } ?>
                                    </tbody>



                                    <tfoot>
                                    <tr class="titre_table">
                                        <th style="text-align: right !important;"><?= $data['lang']['commission_totale']; ?></th>
                                        <th style="text-align: right !important;"><?= $this->utils->number_format($nbreTransactionTotale); ?></th>
                                        <th style="text-align: right !important;">
                                            <?php echo $this->utils->number_format($totale); ?>
                                        </th>

                                    </tr>
                                    </tfoot>
                                </table>
                                <!--<input type="hidden" id="date1" value="<?php /*echo date('Y-m-d') */?>" />
                                <input type="hidden" id="date2" value="<?php /*echo date('Y-m-d') */?>" />-->
                            </div>
                        </div><!-- /.box-body -->


                    </div><!-- /.box -->

                </div><!-- /.col -->
            </div><!-- /.row -->

        </div>
    </div>
</div>
<!-- END CONTENT DE LA PAGE -->


<!-- /.container-fluid -->
<footer class="footer text-center"> <?= $data['lang']['copyright']; ?> </footer>
</div>
<!-- ============================================================== -->
<!-- End Page Content -->
<!-- ============================================================== -->
<?php include_once(__DIR__.'/../footer.php'); ?>

<!--<script type="text/javascript" src="<?/*= WEBROOT */?>assets/js/oXHR.js"></script>-->





