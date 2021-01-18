<!-- HEADER DE LA PAGE -->

    <? include( __DIR__.'/../header.php') ;?>
    <? $thispage = 'reporting_jour'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['detail_transaction']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['detail_transaction']; ?></h3>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                        
                        
                        <div class="col-md-offset-3 col-md-6 col-xs-offset-1 col-sm-10">
                                <dl class="dl-horizontal">
                                    <dt><?= $data['lang']['numero_transaction'].': '; ?></dt>
                                    <dd><?= $data['detail']->num_transac; ?></dd>
                                    <dt><?= $data['lang']['date'].': '; ?></dt>
                                    <dd><?= $this->utils->date_fr4($data['detail']->date_transaction); ?></dd>
                                    <dt><?= $data['lang']['tel_mobile'].': '; ?></dt>
                                    <dd><?= $data['detail']->telephone; ?></dd>
                                    <?php if($data['detail']->numero != ''): ?>
                                    <dt><?= $data['lang']['numero_carte'].': '; ?></dt>
                                    <dd><?= $this->utils->truncate_carte($data['detail']->numero); ?></dd>
                                    <?php endif; ?>
                                    <dt><?= $data['lang']['produit'].': '; ?></dt>
                                    <dd><?=  $data['detail']->label;?></dd>
                                    
                                    <dt><?= $data['lang']['montant_sans_ttc'].': '; ?></dt>
                                    <dd><?= $this->utils->number_format($data['detail']->montant).' '.$data['lang']['currency'];?> </dd>
                                    <dt><?= $data['lang']['commission'].': '; ?></dt>
                                    <dd><?= $this->utils->number_format($data['detail']->commission).' '.$data['lang']['currency']; ?></dd>
                                    <dt><?= $data['lang']['montant_ttc'].': '; ?></dt>
                                    <dd><?= $this->utils->number_format($data['detail']->montant+$data['detail']->commission).' '.$data['lang']['currency']; ?> </dd>
                                    <dt><?= $data['lang']['effectuer_par'].': '; ?></dt>
                                    <dd><?= $data['detail']->prenom.' '.$data['detail']->nom; ?></dd>
                                    <dt><?= $data['lang']['agence'].': '; ?></dt>
                                    <dd><?= $data['detail']->nom_agence; ?></dd>
                                    
                                </dl>
                            </div>
                        <table class="table">
                                <tr>
                                    <td align="center">
                                    <form method="POST" action="<?= ROOT ?>reporting/factureduplicata" target="new">
                                        <input type="hidden" name="num_transac" value="<?= $data['detail']->num_transac; ?>">
                                    	<button type="submit" name="search" value="search" class="btn btn-primary"><?= $data['lang']['duplicata']; ?></button>
                                    </form>
                                    </td>  
                                    <td align="center">
                                    <a href="javascript:history.back()" class="btn btn-default">
									<?= $data['lang']['retour'] ; ?>
                                    </a>
                                    </td>
                                </tr>
                            </table>
                        
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>


<?php include(__DIR__.'/footer.php'); ?>
</body>
</html>