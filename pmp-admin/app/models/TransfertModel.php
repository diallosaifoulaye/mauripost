<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class TransfertModel extends \app\core\BaseModel
{
    /***************** Fonction envoie des transferts *********************/


    public function saveInfosTransfertRefound($num_transac,$code,$montant,$frais,$montant_total,$date_tranfert,$nom_sender,$prenom_sender,$type_piece_sender,$cin_sender,$tel_sender,$pays_sender,$ville_sender,$adresse_sender,$nom_receiver,$prenom_receiver,$tel_receiver,$pays_receiver,$ville_receiver,$adresse_receiver,$fk_service,$user_sender,$agencesender)
    {
        $insertSQL = "INSERT INTO tranfert ( num_transac,code, montant,frais,montant_total, date_tranfert, nom_sender, prenom_sender,type_piece_sender, cin_sender, tel_sender, pays_sender, ville_sender,adresse_sender, nom_receiver, prenom_receiver, tel_receiver, pays_receiver,ville_receiver, adresse_receiver, statut, refound, fk_service, user_sender, user_receiver)";
        $insertSQL .= " VALUES(:num_transac,:code_transfert, :montant,:frais,:montant_total, :date_tranfert, :nom_sender, :prenom_sender,:type_piece_sender, :cin_sender, :tel_sender, :pays_sender,:ville_sender, :adresse_sender, :nom_receiver, :prenom_receiver, :tel_receiver, :pays_receiver,:ville_receiver,:adresse_receiver, :statut, :refound, :fk_service, :user_sender, :user_receiver,:agencesender)";
        $rs_insert = $this->getConnexion()->prepare($insertSQL);
        $rs_insert->execute(array("num_transac"=>$num_transac,
            "code_transfert"=>$code,
            "montant"=>$montant,
            "frais"=>$frais,
            "montant_total"=>$montant_total,
            "date_tranfert"=>$date_tranfert,
            "nom_sender"=>$nom_sender,
            "prenom_sender"=>$prenom_sender,
            "type_piece_sender"=>$type_piece_sender,
            "cin_sender"=>$cin_sender,
            "tel_sender"=>$tel_sender,
            "pays_sender"=>$pays_sender,
            "ville_sender"=>$ville_sender,
            "adresse_sender"=>$adresse_sender,
            "nom_receiver"=>$nom_receiver,
            "prenom_receiver"=>$prenom_receiver,
            "tel_receiver"=>$tel_receiver,
            "pays_receiver"=>$pays_receiver,
            "ville_receiver"=>$ville_receiver,
            "adresse_receiver"=>$adresse_receiver,
            "statut"=>0,
            "refound"=>1,
            "fk_service"=>$fk_service,
            "user_sender"=>$user_sender,
            "user_receiver"=>0,
            "agencesender"=>$agencesender));
        if($rs_insert->rowCount() === 1)
            return 1;
            else return -1;

    }

    public function saveInfosTransfert($num_transac,$code,$montant,$frais,$montant_total,$date_tranfert,$nom_sender,$prenom_sender,$type_piece_sender,$cin_sender,$tel_sender,$pays_sender,$ville_sender,$adresse_sender,$nom_receiver,$prenom_receiver,$tel_receiver,$pays_receiver,$ville_receiver,$adresse_receiver,$fk_service,$user_sender,$agencesender,$refund, $statut=0 )
    {
        $insertSQL = "INSERT INTO tranfert ( num_transac,code, montant,frais,montant_total, date_tranfert, nom_sender, prenom_sender,type_piece_sender, cin_sender, tel_sender, pays_sender, ville_sender,adresse_sender, nom_receiver, prenom_receiver, tel_receiver, pays_receiver,ville_receiver, adresse_receiver, statut,refound, fk_service, user_sender, user_receiver,agencesender )";
        $insertSQL .= " VALUES(:num_transac,:code_transfert, :montant,:frais,:montant_total, :date_tranfert, :nom_sender, :prenom_sender,:type_piece_sender, :cin_sender, :tel_sender, :pays_sender,:ville_sender, :adresse_sender, :nom_receiver, :prenom_receiver, :tel_receiver, :pays_receiver,:ville_receiver,:adresse_receiver, :statut, :refound,:fk_service, :user_sender, :user_receiver,:agencesender)";
        $rs_insert = $this->getConnexion()->prepare($insertSQL);
        $rs_insert->execute(array("num_transac"=>$num_transac,
            "code_transfert"=>$code,
            "montant"=>$montant,
            "frais"=>$frais,
            "montant_total"=>$montant_total,
            "date_tranfert"=>$date_tranfert,
            "nom_sender"=>$nom_sender,
            "prenom_sender"=>$prenom_sender,
            "type_piece_sender"=>$type_piece_sender,
            "cin_sender"=>$cin_sender,
            "tel_sender"=>$tel_sender,
            "pays_sender"=>$pays_sender,
            "ville_sender"=>$ville_sender,
            "adresse_sender"=>$adresse_sender,
            "nom_receiver"=>$nom_receiver,
            "prenom_receiver"=>$prenom_receiver,
            "tel_receiver"=>$tel_receiver,
            "pays_receiver"=>$pays_receiver,
            "ville_receiver"=>$ville_receiver,
            "adresse_receiver"=>$adresse_receiver,
            "statut"=>$statut,
            "refound"=>$refund,
            "fk_service"=>$fk_service,
            "user_sender"=>$user_sender,
            "user_receiver"=>0,
            "agencesender"=>$agencesender));
        if($rs_insert->rowCount() === 1)
            return 1;
        else return -1;
    }
    /***************** Fonction historique des transferts *********************/
    public function getInfosTransfert($num_transac)
    {
        $query_rq_transfert = "SELECT * FROM tranfert WHERE num_transac =:num_transac";
        $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("num_transac",$num_transac);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
        return $row_rq_transfert;
    }
    /***************** Fonction historique des transferts *********************/
    public function historiqueEnvoie($datedeb, $datefin, $agence)
    {
        require_once('../../lib/classes/Profil.php');
        $profils = new Profil();
        $date1 = $datedeb;
        $date2 = $datefin;
        $agency = $agence;
        $cond="";
        $select= "";
        $from="";
        $inclu
            ="";

        $type_profil=$profils->getProfilByIdInteger($this->idprofil);
        if($type_profil==1)
        {
            $cond.=" AND tranfert.user_receiver=".$_SESSION['rowid'];
        }
        if($type_profil==2 || $type_profil==3 || $type_profil==4)
        {
            $cond.=" AND tranfert.user_receiver = user.rowid AND  user.fk_agence =".$this->idagence;
        }

        if($type_profil==6)
        {
            $from.=", region ";
            $cond.=" AND  user.fk_agence = agence.rowid AND agence.region = region.idregion AND region.DR =".$_SESSION['fk_DR'] ;
        }

        if($agency!=0)
        {
            $inclu.=" AND agence.rowid =:agency";
        }else{
            $inclu.=" ";
        }

        $query_rq_historique = " SELECT tranfert.idtransfert,tranfert.num_transac,tranfert.montant,tranfert.code, tranfert.frais, tranfert.montant_total, ";
        $query_rq_historique.= " tranfert.date_tranfert, tranfert.nom_sender, tranfert.prenom_sender, tranfert.cin_sender, tranfert.tel_sender, tranfert.pays_sender, ";
        $query_rq_historique.= " tranfert.nom_receiver, tranfert.prenom_receiver, tranfert.tel_receiver, tranfert.cin_receiver, tranfert.pays_receiver, tranfert.ville_receiver, ";
        $query_rq_historique.= " tranfert.adresse_receiver,transaction.num_transac, tranfert.user_receiver ";
        $query_rq_historique.= " FROM tranfert, transaction, agence, user ".$from;
        $query_rq_historique.= " WHERE tranfert.statut=1 ".$cond."
			 AND DATE(tranfert.date_tranfert) >= :date1
			 AND DATE(tranfert.date_tranfert) <= :date2
			 ".$inclu."
			 AND tranfert.num_transac = transaction.num_transac
			 AND transaction.fkuser = user.rowid
			 AND user.fk_agence = agence.rowid";
        $query_rq_historique.= " ORDER BY transaction.date_transaction DESC ";
        $historique = $this->getConnexion()->prepare($query_rq_historique);
        $historique->bindParam("date1",  $date1);
        $historique->bindParam("date2",  $date2);
        if($agency!=0) $historique->bindParam("agency", $agency);
        $historique->execute();
        $rs_historique= $historique->fetchAll();
        return $rs_historique;
    }
    /***************** Fonction recapitulatif sur un des transferts *********************/

    /***************** Fonction historique des transferts *********************/
    public function historiqueTransfert($datedeb, $datefin, $agence)
    {
        try{
            $date1 = $datedeb;
            $date2 = $datefin;
            $agency = $agence;
            $inclu ="";
            if($agency!=0)
            {
                $inclu.=" AND agence.rowid =:agency";
            }else{
                $inclu.=" ";
            }


            $query_rq_historique = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant,u.statut, a.label,u.refound,u.idtransfert 
                                FROM tranfert as u 
                                LEFT JOIN agence as a ON u.agencesender = a.rowid 
                                WHERE DATE(u.date_tranfert) >= :date1 
                                AND DATE(u.date_tranfert) <= :date2 ".$inclu."";

            $historique = $this->getConnexion()->prepare($query_rq_historique);
            $historique->bindParam("date1",  $date1);
            $historique->bindParam("date2",  $date2);
            if($agency!=0) $historique->bindParam("agency", $agency);
            $historique->execute();
            $rs_historique= $historique->fetchAll();
            return $rs_historique;
        }
        catch (PDOException $e){
            echo -2;
        }

    }


    /***************** Fonction recapitulatif pour le paiement *********************/
    public function updateInfosTransfert($nom_sender,$prenom_sender,$type_piece_sender,$piece_sender,$pays_sender,$ville_sender,$adresse_sender,$nom_receiver,$prenom_receiver,$pays_receiver,$ville_receiver,$adresse_receiver,$idtransfert)
    {
        $insertSQL = "UPDATE tranfert SET nom_sender=:nom_sender, prenom_sender=:prenom_sender, type_piece_sender=:type_piece_sender, cin_sender=:cin_sender, pays_sender=:pays_sender, ville_sender=:ville_sender, adresse_sender=:adresse_sender, nom_receiver=:nom_receiver, prenom_receiver=:prenom_receiver, pays_receiver=:pays_receiver, ville_receiver=:ville_receiver,adresse_receiver=:adresse_receiver WHERE idtransfert=:id AND statut=0 AND refound=0";
        $rs_insert = $this->getConnexion()->prepare($insertSQL);
        $rs_insert->execute(array(
            "nom_sender"=>$nom_sender,
            "prenom_sender"=>$prenom_sender,
            "type_piece_sender"=>$type_piece_sender,
            "cin_sender"=>$piece_sender,
            "pays_sender"=>$pays_sender,
            "ville_sender"=>$ville_sender,
            "adresse_sender"=>$adresse_sender,
            "nom_receiver"=>$nom_receiver,
            "prenom_receiver"=>$prenom_receiver,
            "pays_receiver"=>$pays_receiver,
            "ville_receiver"=>$ville_receiver,
            "adresse_receiver"=>$adresse_receiver,
            "id"=>$idtransfert));
        return $rs_insert;
    }

    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    public function getInfosTransfertByCode($code,$tel)
    {
        $query_rq_transfert = "SELECT * FROM tranfert WHERE code =:code AND tel_receiver=:tel AND statut=0 ";
        $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("code",$code);
        $rq_transfert->bindParam("tel",$tel);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
        $row_count=$rq_transfert->rowCount();
        if($row_count>0){
            return $row_rq_transfert;
        }
            else{
                return -1;
            }

    }

    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    function getCarteCommisssion()
    {
        $query_rq_service = "SELECT numero_carte FROM carte_parametrable WHERE idcarte=1";
        $service = $this->getConnexion()->prepare($query_rq_service);
        $service->execute();
        $row_rq_service= $service->fetchObject();
        return $row_rq_service->numero_carte;
    }
    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    function getCarteOperation()
    {
        $query_rq_service = "SELECT numero_carte FROM carte_parametrable WHERE idcarte=2";
        $service = $this->getConnexion()->prepare($query_rq_service);
        $service->execute();
        $row_rq_service= $service->fetchObject();
        return $row_rq_service->numero_carte;
    }
    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    public function getNumeroCarteAgence()
    {
        $query_rq_numero = "SELECT agence.idcard FROM agence WHERE agence.rowid = :agence";
        $numero = $this->getConnexion()->prepare($query_rq_numero);
        $numero ->bindParam("agence",$this->idagence);
        $numero->execute();
        $row_rq_numero= $numero->fetchObject();
        //return $this->idagence;
        return $row_rq_numero->idcard;
    }
    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    function getTypeAgence($agence)
    {
        $query_rq_service = "SELECT  agence.idtype_agence FROM  agence WHERE agence.rowid= :agence";
        $service = $this->getConnexion()->prepare($query_rq_service);
        $service ->bindParam("agence",$agence);
        $service->execute();
        $row_rq_service= $service->fetchObject();
        return $row_rq_service->idtype_agence;
    }
    function genererNumtransaction()
    {

        $found=0;
        while ($found==0)
        {
            $code_carte=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
            $colname_rq_code_existe =$code_carte;
            $query_rq_code_existe = "SELECT rowid FROM transaction WHERE num_transac ='".$colname_rq_code_existe."'";
            $numero = $this->getConnexion()->prepare($query_rq_code_existe);
            $numero->execute();
            $num_transac=$numero->fetchObject();
            $totalRows_rq_code_existe =$numero->rowCount();
            if($totalRows_rq_code_existe==0)
            {
                //CODE GENERER
                $code_generer=$code_carte;
                $found=1;
                break;
            }


        }
        return $code_generer;
    }


    public function updateInfosPaiementA($user,$date_receive,$type_piece,$piece,$idtransfert,$date_delivrance,$date_expiration, $agencereceiver)
    {
        try{

            $Update = "UPDATE tranfert SET   statut =1, user_receiver=:user_receiver ,date_receiver =:datereception ,type_piece_receiver=:type_piece_receiver,cin_receiver=:cin_receiver, date_exp_receiver=:date_exp,    date_delivrance_receiver=:date_delivrance, agencereceiver = :agencereceiver   WHERE idtransfert=:idtransfert ";
            $rq_Update = $this->getConnexion()->prepare($Update);
            $rq_Update->bindParam("user_receiver",$user);
            $rq_Update->bindParam("datereception",$date_receive);
            $rq_Update->bindParam("type_piece_receiver",$type_piece);
            $rq_Update->bindParam("cin_receiver",$piece);
            $rq_Update->bindParam("idtransfert",$idtransfert);
            $rq_Update->bindParam("date_delivrance",$date_delivrance);
            $rq_Update->bindParam("date_exp",$date_expiration);
            $rq_Update->bindParam("agencereceiver",$agencereceiver);
            $rq_Update->execute();
            return $rq_Update;
        }
       catch (\PDOException $e){
            return -1;
       }

    }

    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    public function getInfosTransfertByCode1($code,$tel,$agence)
    {
        try{
            $query_rq_transfert = "SELECT * FROM tranfert WHERE code =:code AND tel_sender=:tel AND  agencesender=:agence AND statut=0 AND refound=0";
            $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
            $rq_transfert->bindParam("code",$code);
            $rq_transfert->bindParam("tel",$tel);
            $rq_transfert->bindParam("agence",$agence);
            $rq_transfert->execute();
            $row_rq_transfert= $rq_transfert->fetchObject();
            return $row_rq_transfert;
        }
        catch (PDOException $ex){
            return '';
        }

    }

    /***************update statut remboursement***************/
    public function update_statut_remboursement($idtransfert)
    {
        //$dbh = Connection();
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE tranfert SET refound=1 WHERE idtransfert=:idtransfert");
            $Result1=$req->execute(array("idtransfert" => $idtransfert));
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(PDOException $e)
        {
            echo -1;
        }

        return $return;
    }

    public function getInfosTransfertByMontant($montant, $tel, $agence, $user, $code=''){
        $sql = '';
        if($code != ''){
            $sql .= ' AND code = '.$code;
        }
        $query_rq_transfert = "SELECT * FROM tranfert WHERE montant =:montant AND tel_sender = :tel AND statut=0 AND agencesender = :agence AND user_sender = :user".$sql;
        $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("montant",$montant);
        $rq_transfert->bindParam("tel",$tel);
        $rq_transfert->bindParam("agence",$agence);
        $rq_transfert->bindParam("user",$user);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
        $row_count=$rq_transfert->rowCount();
        if($row_count>0){
            return $row_rq_transfert;
        }
        else{
            return -1;
        }
    }

    public function getInfosTransfertByCodeIdMontant($code, $tel, $id, $montant, $agence, $user){

        try{
            $query_rq_transfert = "SELECT * FROM tranfert WHERE montant  = :montant AND tel_sender = :tel AND statut=0 AND idtransfert = :id AND code = :code AND agencesender = :agence AND user_sender = :user";
            $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
            $rq_transfert->bindParam("montant",$montant);
            $rq_transfert->bindParam("tel",$tel);
            $rq_transfert->bindParam("id",$id);
            $rq_transfert->bindParam("code",$code);
            $rq_transfert->bindParam("agence",$agence);
            $rq_transfert->bindParam("user",$user);
            $rq_transfert->execute();
            $row_rq_transfert= $rq_transfert->fetchObject();
            $row_count=$rq_transfert->rowCount();
            if($row_count>0){
                return $row_rq_transfert;
            }
            else{
                return -1;
            }
        }
        catch (PDOException $de){
            return -1;
        }

    }

    /***************update statut remboursement***************/
    public function update_statut_annulation($idtransfert, $fk_agence, $fk_user)
    {
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE tranfert SET statut = 3, user_cancel = :agent, agencecancel = :agence, date_cancel = :datereverse WHERE idtransfert = :idtransfert");
            $Result1=$req->execute(
                array(
                    "agent" => $fk_user,
                    "agence" => $fk_agence,
                    "datereverse" => date('Y-m-d H:i:s'),
                    "idtransfert" => $idtransfert
                )
            );
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(PDOException $e)
        {
            $return = -2;
        }

        return $return;
    }



    /***************update statut Transfert***************/
    public function update_statut_transfert($idtransfert,$statut)
    {
        //$dbh = Connection();
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE tranfert SET statut =  ".$statut." WHERE idtransfert=:idtransfert");
            $Result1=$req->execute(array("idtransfert" => $idtransfert));
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(PDOException $e)
        {
            echo -1;
        }

        return $return;
    }

    public function getEmailByCode($rowid)
    {

        $query_rq_transfert = "SELECT email,code,responsable FROM agence WHERE rowid =:rowid";
        $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("rowid",$rowid);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
       // var_dump($rq_transfert);exit;
        return $row_rq_transfert;
    }
}