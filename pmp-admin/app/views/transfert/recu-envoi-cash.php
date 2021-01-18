<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 09/09/2017
 * Time: 13:25
 */
/**********************************************************************************************************************************************/
/**
 * @param $number
 * @return string au format 1 234 567,89
 */
$thispage = 'envoi';
function getConnexion()
{   $connexion = '';
    $dsn = DB_TYPE.':dbname='.DB_NAME.';host='.DB_HOST;
    try{
        $connexion = new \PDO($dsn, DB_USER, DB_PASSWORD);
        $connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $connexion ;
    }
    catch(\PDOException $e){
        return -1;
    }

}
function nombre_form($nombre)
{
    return @number_format($nombre, 0, ',', ' ');
}
function getPaysById($id)
{
    $query_rq_transfert = "SELECT nom_fr_fr FROM pays WHERE id =:id";
    $rq_transfert = getConnexion()->prepare($query_rq_transfert);
    $rq_transfert->bindParam("id",$id);
    $rq_transfert->execute();
    $row_rq_transfert= $rq_transfert->fetchObject();
    return $row_rq_transfert->nom_fr_fr;
}
function getRegionById($id)
{
    $query_rq_transfert = "SELECT lib_region FROM region WHERE idregion =:id ";
    $rq_transfert = getConnexion()->prepare($query_rq_transfert);
    $rq_transfert->bindParam("id",$id);
    $rq_transfert->execute();
    $row_rq_transfert= $rq_transfert->fetchObject();
    return $row_rq_transfert->lib_region;
}
?>

<link href="../style.css" rel="stylesheet" type="text/css">
<page backtop="2mm" backbottom="0mm" backleft="10mm" backright="10mm">

    <table width="642" border="0" align="center" cellpadding="0" cellspacing="2">
        <tr>
            <td width="29%" align="left" valign="middle"><img src="<?= __DIR__.'/../../../assets/images/mauripost.png'?>" width="80" /></td>
            <td width="25%" align="right" valign="middle" class="textNormal">&nbsp;</td>
            <td width="23%" align="right" valign="middle" class="textNormal">&nbsp;</td>
            <td width="23%" align="left" valign="top" class="textNormal"><span class="txt_form">Agence:</span> <?php echo $data['agence'] ?></td>
        </tr>
        <tr>
            <td colspan="4" align="center" valign="middle" class="txt_legend"><?= $data['lang']['TITRE_RECU_ENVOI'] ?></td>
        </tr>

        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">
                <span class="txt_form1">Transaction</span>
                <hr align="center" style="border:#999 solid 1px" />
            </td>
        </tr>
        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; transaction:</td>
            <td width="25%" align="left" valign="middle" class="txt_resultat"><?php echo $data['infoenvoie']->num_transac; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Code:</td>
            <td width="23%" align="left" valign="middle" class="txt_resultat"><?php echo $data['infoenvoie']->code; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Date:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo  $this->utils->date_fr4($data['infoenvoie']->date_tranfert); ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Effectué par:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['effectuerpar'] ?></td>
        </tr>

        <tr>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Montant:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo nombre_form($data['infoenvoie']->montant). " ".$data['lang']['currency'] ; ?></td>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Frais:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo nombre_form($data['infoenvoie']->frais) . " ".$data['lang']['currency'] ; ?></td>
        </tr>


        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Montant total:</td>
            <td colspan="3" align="left" valign="middle" class="txt_lister"><?php $montant_total = $data['infoenvoie']->montant + $data['infoenvoie']->frais; echo nombre_form($montant_total) . " ".$data['lang']['currency'] ; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form"></td>
            <td colspan="3" align="left" valign="middle" class="txt_lister"></td>
        </tr>



        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form"><span class="txt_form1">Exp&eacute;diteur</span>
                <hr align="center" style="border:#999 solid 1px" /></td>
        </tr>


        <tr>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Nom:</td>
            <td width="25%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->nom_sender; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Pr&eacute;nom:</td>
            <td width="23%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->prenom_sender; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Type de la pi&egrave;ce:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->type_piece_sender; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; de pi&egrave;ce d'identit&eacute;:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->cin_sender; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; de t&eacute;l&eacute;phone :</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->tel_sender; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Pays:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo getPaysById($data['infoenvoie']->pays_sender); ?></td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form"><span class="txt_form1">D&eacute;stinataire</span>
                <hr align="center" style="border:#999 solid 1px" /></td>
        </tr>

        <tr>
            <td width="29%" align="left" valign="middle" nowrap class="txt_form">Nom:</td>
            <td width="25%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->nom_receiver; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_lister"><span class="txt_form">Pr&eacute;nom:</span></td>
            <td width="23%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->prenom_receiver; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap class="txt_form">N&ordm; de t&eacute;l&eacute;phone:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->tel_receiver; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_lister"><span class="txt_form">Pays:</span></td>
            <td align="left" valign="middle" class="txt_lister"><?php echo getPaysById($data['infoenvoie']->pays_receiver); ?></td>
        </tr>

        <tr>
            <td colspan="2" align="center" valign="top" nowrap class="txt_form1">&nbsp;</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_form1">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="center" valign="top" nowrap class="txt_form1">Signature client</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_form1">Cachet de l'agent</td>
        </tr>

        <tr>
            <td height="31" colspan="2" align="center" valign="top" nowrap class="txt_form">&nbsp;</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_lister">&nbsp;</td>
        </tr>

        <tr>
            <td height="31" colspan="4" align="center" valign="top" nowrap class="txt_form">
                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-bottom:#CCC solid 0.5px"></td>
                    </tr>
                    <tr>
                        <td align="center" valign="middle" colspan="4">
                            <span style="font-size:13px; color: #666;">
                                <?= $data['lang']['contact']; ?>
                                : +222 45 25 72 27 / <?= $data['lang']['email']; ?>
                                : serviceclient@postecash.mr /
                                <?= $data['lang']['site_web']; ?>
                                : www.postecash.mr
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" valign="middle">-------------------------------------------------------------------------------------------------------------------</td>
        </tr>
    </table>


    <table width="642" border="0" align="center" cellpadding="0" cellspacing="2">
        <tr>
            <td width="29%" align="left" valign="middle"><img src="<?= __DIR__.'/../../../assets/images/mauripost.png'?>" width="80" /></td>
            <td width="25%" align="right" valign="middle" class="textNormal">&nbsp;</td>
            <td width="23%" align="right" valign="middle" class="textNormal">&nbsp;</td>
            <td width="23%" align="left" valign="top" class="textNormal"><span class="txt_form">Agence:</span> <?php echo $data['agence'] ?></td>
        </tr>
        <tr>
            <td colspan="4" align="center" valign="middle" class="txt_legend"><?= $data['lang']['TITRE_RECU_ENVOI'] ?></td>
        </tr>

        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">
                <span class="txt_form1">Transaction</span>
                <hr align="center" style="border:#999 solid 1px" />
            </td>
        </tr>
        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; transaction:</td>
            <td width="25%" align="left" valign="middle" class="txt_resultat"><?php echo $data['infoenvoie']->num_transac; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Code:</td>
            <td width="23%" align="left" valign="middle" class="txt_resultat"><?php echo $data['infoenvoie']->code; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Date:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo  $this->utils->date_fr4($data['infoenvoie']->date_tranfert); ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Effectué par:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['effectuerpar'] ?></td>
        </tr>

        <tr>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Montant:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo nombre_form($data['infoenvoie']->montant). " ".$data['lang']['currency'] ; ?></td>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Frais:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo nombre_form($data['infoenvoie']->frais) . " ".$data['lang']['currency'] ; ?></td>
        </tr>


        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Montant total:</td>
            <td colspan="3" align="left" valign="middle" class="txt_lister"><?php $montant_total = $data['infoenvoie']->montant + $data['infoenvoie']->frais; echo nombre_form($montant_total) . " ".$data['lang']['currency'] ; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form"></td>
            <td colspan="3" align="left" valign="middle" class="txt_lister"></td>
        </tr>



        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form"><span class="txt_form1">Exp&eacute;diteur</span>
                <hr align="center" style="border:#999 solid 1px" /></td>
        </tr>


        <tr>
            <td width="29%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Nom:</td>
            <td width="25%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->nom_sender; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_form">Pr&eacute;nom:</td>
            <td width="23%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->prenom_sender; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Type de la pi&egrave;ce:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->type_piece_sender; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; de pi&egrave;ce d'identit&eacute;:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->cin_sender; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">N&ordm; de t&eacute;l&eacute;phone :</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->tel_sender; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_form">Pays:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo getPaysById($data['infoenvoie']->pays_sender); ?></td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="left" valign="middle" nowrap="nowrap" class="txt_form"><span class="txt_form1">D&eacute;stinataire</span>
                <hr align="center" style="border:#999 solid 1px" /></td>
        </tr>

        <tr>
            <td width="29%" align="left" valign="middle" nowrap class="txt_form">Nom:</td>
            <td width="25%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->nom_receiver; ?></td>
            <td width="23%" align="left" valign="middle" nowrap="nowrap" class="txt_lister"><span class="txt_form">Pr&eacute;nom:</span></td>
            <td width="23%" align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->prenom_receiver; ?></td>
        </tr>

        <tr>
            <td align="left" valign="middle" nowrap class="txt_form">N&ordm; de t&eacute;l&eacute;phone:</td>
            <td align="left" valign="middle" class="txt_lister"><?php echo $data['infoenvoie']->tel_receiver; ?></td>
            <td align="left" valign="middle" nowrap="nowrap" class="txt_lister"><span class="txt_form">Pays:</span></td>
            <td align="left" valign="middle" class="txt_lister"><?php echo getPaysById($data['infoenvoie']->pays_receiver); ?></td>
        </tr>

        <tr>
            <td colspan="2" align="center" valign="top" nowrap class="txt_form1">&nbsp;</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_form1">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="center" valign="top" nowrap class="txt_form1">Signature client</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_form1">Cachet de l'agent</td>
        </tr>

        <tr>
            <td height="31" colspan="2" align="center" valign="top" nowrap class="txt_form">&nbsp;</td>
            <td colspan="2" align="center" valign="top" nowrap="nowrap" class="txt_lister">&nbsp;</td>
        </tr>

        <tr>
            <td height="31" colspan="4" align="center" valign="top" nowrap class="txt_form">
                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-bottom:#CCC solid 0.5px"></td>
                    </tr>
                    <tr>
                        <td align="center" valign="middle" colspan="4">
                            <span style="font-size:13px; color: #666;">
                                <?= $data['lang']['contact']; ?>
                                : +222 45 25 72 27 / <?= $data['lang']['email']; ?>
                                : serviceclient@postecash.mr /
                                <?= $data['lang']['site_web']; ?>
                                : www.postecash.mr
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</page>
