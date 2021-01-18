<!-- HEADER DE LA PAGE -->
<?php include(__DIR__ . '/../header.php'); ?>

<?php $thispage = '' ?>

<?php include("sidebar.php"); ?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- END Content Header (Page header) -->
    <!-- CONTENT DE LA PAGE -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><?= $data['lang']['gestion_reporting']; ?></h3>

                    </div><!-- /.box-header -->

                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div>
<!-- END CONTENT DE LA PAGE -->


<!-- FOOTER DE LA PAGE -->
<? include(__DIR__ . '/../footer.php'); ?>
<!-- END FOOTER DE LA PAGE -->

