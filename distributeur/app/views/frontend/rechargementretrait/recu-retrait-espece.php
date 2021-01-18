<?php //$data['benef']; $data['transaction'];?>
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

    <table width="642" align="center" cellpadding="0" cellspacing="3" >
        <tr>
            <td height="28" align="left" valign="top"><img src="<?= __DIR__.'/../../../../assets/plugins/images/postecash-black.png'?>" width="170" height="53" /></td>
            <td height="28" align="left" valign="top">&nbsp;</td>
            <td height="28" colspan="2" align="left" valign="top"><span class="txt_form" style="text-align:center"><b><?= $data['lang']['agence']; ?></b> : <?= $this->utils->geAgence($data['transaction']->fk_agence); ?></span></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" class="txt_form1" style="text-align:center; text-transform: uppercase;"><b><?= $data['lang']['retait']; ?></b><hr align="center" style="border:#999 solid 2px" /></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" style="text-align:center">&nbsp;</td>
        </tr>
        <tr>
            <td width="170" align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['beneficiaire']; ?>   : </strong></td>
            <td width="172" align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->prenom.' '.$data['benef']->prenom1.' '.$data['benef']->nom; ?></span></td>

            <?php if($data['benef']->numero!= '') { ?>

            <td width="121" align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['num_carte']; ?>  :</strong></td>
            <td width="111" align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->truncate_carte($data['benef']->numero); ?></span></td>

            <?php } ?>

        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['tel']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->telephone; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['adresse']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->adresse; ?></span></td>
        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->montant); ?> <?= $data['lang']['currency']; ?></span></td>

            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['frais']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->commission); ?><?= ' '.$data['lang']['currency']; ?></span></td>
        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant_ttc']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->montant+$data['transaction']->commission); ?> <?= ' '.$data['lang']['currency']; ?></span></td>

            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['effectuer_par']; ?>  :</strong></td>
            <td align="left" valign="middle" class="textNormal"><?= $this->utils->getUser($data['transaction']->fkuser); ?></td>
        </tr>

        <tr class="b1">
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['date']; ?>    : </strong></td>
            <td align="left" valign="middle"><?= $this->utils->date_fr4($data['transaction']->date_transaction); ?></td>

            <td align="left" valign="middle" nowrap="nowrap"><span class="txt_form"><strong><?= $data['lang']['numero_transaction']; ?>    : </strong></span></td>
            <td align="left" valign="middle"><span class="txt_resultat"><?= $data['transaction']->num_transac; ?></span></td>
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
                        <td align="center" valign="middle" colspan="4"><span style="font-size:13px; color: #666;">
                  <?= $data['lang']['contact']; ?>
                                : <?= CONTACT ?> <?= $data['lang']['email']; ?>
                                : <?= EMAIL?> /
                                <?= $data['lang']['site_web']; ?>: <?=SITEWEB?></span></td>
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
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
    </table>


    <table width="642" align="center" cellpadding="0" cellspacing="3" >
        <tr>
            <td height="28" align="left" valign="top"><img src="<?= __DIR__.'/../../../../assets/plugins/images/postecash-black.png'?>" width="170" height="53" /></td>
            <td height="28" align="left" valign="top">&nbsp;</td>
            <td height="28" colspan="2" align="left" valign="top"><span class="txt_form" style="text-align:center"><b><?= $data['lang']['agence']; ?></b> : <?= $this->utils->geAgence($data['transaction']->fk_agence); ?></span></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" class="txt_form1" style="text-align:center; text-transform: uppercase;"><b><?= $data['lang']['retait']; ?></b><hr align="center" style="border:#999 solid 2px" /></td>
        </tr>
        <tr>
            <td height="33" colspan="4" align="center" valign="middle" nowrap="nowrap" style="text-align:center">&nbsp;</td>
        </tr>
        <tr>
            <td width="170" align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['beneficiaire']; ?>   : </strong></td>
            <td width="172" align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->prenom.' '.$data['benef']->prenom1.' '.$data['benef']->nom; ?></span></td>

            <?php if($data['benef']->numero!= '') { ?>

                <td width="121" align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['num_carte']; ?>  :</strong></td>
                <td width="111" align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->truncate_carte($data['benef']->numero); ?></span></td>

            <?php } ?>

        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['tel']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->telephone; ?></span></td>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['adresse']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $data['benef']->adresse; ?></span></td>
        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->montant); ?> <?= $data['lang']['currency']; ?></span></td>

            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['frais']; ?>   :</strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->commission); ?><?= ' '.$data['lang']['currency']; ?></span></td>
        </tr>

        <tr>
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['montant_ttc']; ?>   : </strong></td>
            <td align="left" valign="middle" class="textNormal"><span class="txt_lister"><?= $this->utils->number_format($data['transaction']->montant+$data['transaction']->commission); ?> <?= ' '.$data['lang']['currency']; ?></span></td>

            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['effectuer_par']; ?>  :</strong></td>
            <td align="left" valign="middle" class="textNormal"><?= $this->utils->getUser($data['transaction']->fkuser); ?></td>
        </tr>

        <tr class="b1">
            <td align="left" valign="middle" class="txt_form"><strong><?= $data['lang']['date']; ?>    : </strong></td>
            <td align="left" valign="middle"><?= $this->utils->date_fr4($data['transaction']->date_transaction); ?></td>

            <td align="left" valign="middle" nowrap="nowrap"><span class="txt_form"><strong><?= $data['lang']['numero_transaction']; ?>    : </strong></span></td>
            <td align="left" valign="middle"><span class="txt_resultat"><?= $data['transaction']->num_transac; ?></span></td>
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
                        <td align="center" valign="middle" colspan="4"><span style="font-size:13px; color: #666;">
                 <?= $data['lang']['contact']; ?>
                                : <?= CONTACT ?> <?= $data['lang']['email']; ?>
                                : <?= EMAIL?> /
                                <?= $data['lang']['site_web']; ?>: <?=SITEWEB?></span></td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>


</page>