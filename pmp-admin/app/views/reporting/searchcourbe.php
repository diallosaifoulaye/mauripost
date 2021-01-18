<!-- HEADER DE LA PAGE -->

    <? include( __DIR__.'/../header.php') ;?>
    <? $thispage = 'courbe_evolution'; ?>

<!-- END HEADER DE LA PAGE -->


<?php include("sidebar.php"); ?>

<div class="content-wrapper" style="min-height: 0px !important;">
    <!-- Content Header (Page header) -->

    <section class="content-header" >
        <h1>
            <?= $data['lang']['courbe_evolution']; ?>
        </h1>
        
    </section>

    <!-- Main content -->
    <section class="content" style="min-height: 0px !important;">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['courbe_evolution']; ?></h3>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                         <div class="col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                                <form class="form-horizontal" method="POST" action="<?= ROOT ?>reporting/courbeevolution">
                                	<fieldset style="display: block;" class="scheduler-border">
                                        <legend class="scheduler-border"><?= $data['lang']['courbe_evolution']; ?></legend>
                                   
                                    <div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?= $data['lang']['annee']; ?> (*):
                                        </label>
                                        <div class="col-sm-7">
                                           
                                           <?php 
											  // Variable qui ajoutera l'attribut selected de la liste déroulante
											  $selected = '';
											   // Parcours du tableau
											   echo '<select name="annee" required="required" class="form-control select2" id="produit" style="width: 100%;">',"\n";
											   for($i=date('Y')-5; $i<=date('Y') + 20; $i++)
											   {
												// L'année est-elle l'année courante ?
											   if($i == date('Y'))
											   {
											   $selected = ' selected="selected"';
											   }   
											   // Affichage de la ligne
											   echo "\t",'<option value="', $i ,'"', $selected ,'>', $i ,'</option>',"\n";   
											   // Remise à zéro de $selected
											   $selected='';  }  echo '</select>',"\n";
											?>
                                        </div>
                                    </div>
                                
                                    		<div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?= $data['lang']['produit']; ?> (*):
                                        </label>
                                        <div class="col-sm-7">
                                            <select class="form-control select2" required name="service" style="width: 100%;" id="service">
                                                <option selected="selected" value=""><?= $data['lang']['select_service'] ; ?></option>
                                                <option  value="0">Tous</option>
                                                <?php foreach($data['service'] as $serv){ ?>
                                                    <option value="<?= $serv['rowid']; ?>"><?= $serv['label']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                 
                                    		<div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?= $data['lang']['nom_agence'].' :' ; ?>
                                        </label>
                                        <div class="col-sm-7">
                                            <select class="form-control select2" name="agence" style="width: 100%;" id="agence">
                                                <option selected="selected" value=""><?= $data['lang']['select_agence'] ; ?></option>
                                                <option  value="0">Tous</option>
                                                <?php foreach($data['agence'] as $reg){ ?>
                                                    <option value="<?= $reg['rowid']; ?>"><?= $reg['agence']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                                      
                               				<div class="form-group">
                                        <label for="region" class="col-sm-5 control-label">
                                        <?= $data['lang']['choix_courbe']; ?> (*):
                                        </label>
                                        <div class="col-sm-7">
                                 
                                            <select name="courbe" id="courbe" required="required" class="form-control select2" style="width: 100%;">
                                                <option value="1"><?= $data['lang']['transact_nombre']; ?></option>
                                                <option value="2"><?= $data['lang']['chiffre_affaire']; ?></option>
                                                <option value="3"><?= $data['lang']['commission']; ?></option>
                                              </select>
                                        </div>
                                    </div>
                                   
                                            <div class="form-group">
                                                <div class="col-sm-offset-6 col-sm-6">
                                                    <button type="submit" name="search" value="search" class="btn btn-warning"><?= $data['lang']['continuer']; ?></button>
                                                </div>
                                            </div>
                                  </fieldset>
                                </form>
                         </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>

<? include(__DIR__.'/footer.php') ;?>
<!-- page script -->

<script>

    $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
    });
</script>

</body>
</html>