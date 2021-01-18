<?php
$data['recu'];
?>
<style>
    .b{
        border:#CCC solid 2px;
    }

    p#c{
        text-align:center;
    }

</style>
<link href="../style.css" rel="stylesheet" type="text/css" />
<page  backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm">
    <page_header>
        <table width="642" align="center">
            <tr>
                <td align="center" valign="middle" style="font-style:italic; color:#CCC;"><?= $data['lang']['recu_duplicata']; ?></td>
            </tr>
        </table>
    </page_header>
    <table width="642" align="center" cellpadding="2" cellspacing="10" class="b">
        <tr>
            <td height="28" align="left" valign="top"><img src="<?= __DIR__.'/../../../../assets/plugins/images/postecash-black.png'?>" width="170" height="53" /></td>
            <td height="28" align="left" valign="top">&nbsp;</td>
            <td height="28" colspan="2" align="left" valign="top"><span class="txt_form" style="text-align:center"><b><?= $data['lang']['agence']; ?></b> : <?= $data['recu']->nomagence; ?></span></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" class="txt_form1" style="text-align:center; text-transform: uppercase;">
                <?= $data['recu']->label;
                ?>
                <hr align="center" style="border:#999 solid 2px" /></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" style="text-align:center">&nbsp;</td>
        </tr>
        <tr>
            <td width="170" align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['beneficiaire']; ?>   : </strong></td>
            <td width="172" align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['recu']->prenom.' '.$data['recu']->nom; ?></span></td>
            <td width="121" align="left" valign="middle" class="txt_form"></td>
            <td width="111" align="left" valign="middle" class="textNormal"><span class="txt_lister"></span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['tel']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['recu']->telephone; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['adresse']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['recu']->adresse; ?></span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['recu']->montant); ?> <?= $data['lang']['currency']; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['frais']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $this->utils->number_format($data['recu']->commission) ?><?= ' '.$data['lang']['currency']; ?></span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant_total']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $this->utils->number_format($data['recu']->montant+$data['recu']->commission); ?> <?= $data['lang']['currency']; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['effectuer_par']; ?>  :</strong></td>
            <td align="left" valign="middle" class="textNormal"><?= $data['recu']->prenomuser ." ".$data['recu']->nomuser; ?></td>
        </tr>
        <tr class="b1">
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['date']; ?>    : </strong></td>
            <td align="left" valign="middle"><?= $this->utils->date_fr4($data['recu']->date_transaction); ?></td>
            <td align="left" valign="middle" nowrap="nowrap"><span class="txt_form"><strong><?= $data['lang']['numero_transaction']; ?>    : </strong></span></td>
            <td align="left" valign="middle"><span class="txt_resultat"><?= $data['recu']->num_transac; ?></span></td>
        </tr>
        <tr>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
        </tr>
        <tr class="txt_form1">
            <td align="right" valign="middle"><?= $data['lang']['signateur_client']; ?> </td>
            <td align="center" valign="middle">&nbsp;</td>
            <td colspan="2" align="center" valign="middle"><?= $data['lang']['cachet_agent']; ?></td>
        </tr>
        <tr>
            <td height="40" colspan="2" align="center" valign="middle">&nbsp;</td>
            <td colspan="2" align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="center" valign="middle">

                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-bottom:#CCC solid 0.5px"></td>
                    </tr>
                    <tr>
                        <td align="center" valign="middle" colspan="4">
                            <span style="font-size:13px; color: #666;">
                                <?= $data['lang']['contact']; ?>
                                : <?php echo CONTACT; ?> / <?= $data['lang']['email']; ?>
                                : <?php echo EMAIL; ?> /
                                <?= $data['lang']['site_web']; ?>
                                :<?php echo SITEWEB; ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="middle">-------------------------------------------------------------------------------------------------------------------------------------</td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
    </table>
    <table width="642" align="center" cellpadding="2" cellspacing="10" class="b">
        <tr>
            <td height="28" align="left" valign="top"><img src="<?= __DIR__.'/../../../../assets/plugins/images/postecash-black.png'?>" width="170" height="53" /></td>
            <td height="28" align="left" valign="top">&nbsp;</td>
            <td height="28" colspan="2" align="left" valign="top"><span class="txt_form" style="text-align:center"><b>
      <?= $data['lang']['agence']; ?>
      </b> :
                    <?= $data['recu']->nomagence; ?>
    </span></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" class="txt_form1" style="text-align:center; text-transform: uppercase;">
                <?= $data['recu']->label; ?>
                <hr align="center" style="border:#999 solid 2px" /></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" style="text-align:center">&nbsp;</td>
        </tr>
        <tr>
            <td width="170" align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['beneficiaire']; ?>
                    : </strong></td>
            <td width="172" align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $data['recu']->prenom.' '.$data['recu']->nom; ?>
    </span></td>
            <td width="121" align="left" valign="middle" class="txt_form"><strong>
                </strong></td>
            <td width="111" align="left" valign="middle" class="textNormal"><span class="txt_lister">

    </span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['tel']; ?>
                    : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $data['recu']->telephone; ?>
    </span></td>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['adresse']; ?>
                    :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $data['recu']->adresse; ?>
    </span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['montant']; ?>
                    : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $this->utils->number_format($data['recu']->montant); ?>
      <?= $data['lang']['currency']; ?>
    </span></td>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['frais']; ?>
                    :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $this->utils->number_format($data['recu']->commission) ?>
      <?= ' '.$data['lang']['currency']; ?>
    </span></td>
        </tr>
        <tr>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['montant_total']; ?>
                    : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister">
      <?= $this->utils->number_format($data['recu']->montant+$data['recu']->commission); ?>
      <?= $data['lang']['currency']; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['effectuer_par']; ?>
                    :</strong></td>
            <td align="left" valign="middle" class="textNormal"><?= $data['recu']->prenomuser ." ".$data['recu']->nomuser; ?></td>
        </tr>
        <tr class="b1">
            <td align="left" valign="middle" class="txt_form"><strong>
                    <?= $data['lang']['date']; ?>
                    : </strong></td>
            <td align="left" valign="middle"><?= $this->utils->date_fr4($data['recu']->date_transaction); ?></td>
            <td align="left" valign="middle" nowrap="nowrap"><span class="txt_form"><strong>
      <?= $data['lang']['numero_transaction']; ?>
                        : </strong></span></td>
            <td align="left" valign="middle"><span class="txt_resultat">
      <?= $data['recu']->num_transac; ?>
    </span></td>
        </tr>
        <tr>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="middle">&nbsp;</td>
        </tr>
        <tr class="txt_form1">
            <td align="right" valign="middle"><?= $data['lang']['signateur_client']; ?></td>
            <td align="center" valign="middle">&nbsp;</td>
            <td colspan="2" align="center" valign="middle"><?= $data['lang']['cachet_agent']; ?></td>
        </tr>
        <tr>
            <td height="40" colspan="2" align="center" valign="middle">&nbsp;</td>
            <td colspan="2" align="center" valign="middle">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="center" valign="middle">
                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-bottom:#CCC solid 0.5px"></td>
                    </tr>
                    <tr>
                        <td align="center" valign="middle" colspan="4">
                            <span style="font-size:13px; color: #666;">
                                <?= $data['lang']['contact']; ?>
                                : <?php echo CONTACT; ?> / <?= $data['lang']['email']; ?>
                                : <?php echo EMAIL; ?> /
                                <?= $data['lang']['site_web']; ?>
                                :<?php echo SITEWEB; ?>
                            </span>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <page_footer>
        <table width="642" align="center">
            <tr>
                <td align="center" valign="middle" style="font-style:italic; color:#CCC;"><?= $data['lang']['recu_duplicata']; ?></td>
            </tr>
        </table>
    </page_footer>
</page>