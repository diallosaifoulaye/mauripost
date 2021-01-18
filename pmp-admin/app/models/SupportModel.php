<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class SupportModel extends \app\core\BaseModel
{

    public function getAllRegion()
    {
        try {
            $sql = "SELECT * FROM region WHERE fk_pays =".ID_PAYS." ORDER BY lib_region ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return $exception;
        }
    }


    public function getAllAgenceByRegion($id)
    {
        try {
            $sql = "SELECT rowid, label, code FROM agence WHERE province = :region ORDER BY label ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'region' => $id
                )
            );
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($a);
        } catch (PDOException $exception) {
            return $exception;
        }
    }


    public function getAllUsersByAgence($id)
    {
        try {
            $sql = "SELECT rowid, CONCAT(prenom, ' ', nom) as nom FROM user WHERE fk_agence = :agence ORDER BY nom ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'agence' => $id
                )
            );
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($a);
        } catch (PDOException $exception) {
            return $exception;
        }
    }

    public function getAllUsersByAgenceAndSoldeAgence($id)
    {
        try {
            $sql = "SELECT rowid, CONCAT(prenom, ' ', nom) as nom FROM user WHERE fk_agence = :agence ORDER BY nom ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'agence' => $id
                )
            );
            $a = $user->fetchAll(PDO::FETCH_ASSOC);


            $sql = "SELECT solde FROM agence WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'id' => $id
                )
            );
            $solde = $user->fetchObject();
            return json_encode(array('users' => $a, 'solde' => $solde->solde));
        } catch (PDOException $exception) {
            return $exception;
        }
    }


    public function getAllUsersByAgenceAndSoldeAgence2($id)
    {
        try {
            $sql = "SELECT rowid, CONCAT(prenom, ' ', nom) as nom FROM user WHERE fk_agence = :agence ORDER BY nom ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'agence' => $id
                )
            );
            $a = $user->fetchAll(PDO::FETCH_ASSOC);


            $sql = "SELECT solde FROM agence WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'id' => $id
                )
            );
            $solde = $user->fetchObject();
            return json_encode(array('users' => $a, 'solde' => $solde->solde));
        } catch (PDOException $exception) {
            return $exception;
        }
    }

    public function getoldeAgence($id)
    {
        try {


            $sql = "SELECT solde FROM agence WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'id' => $id
                )
            );
            $solde = $user->fetchObject();
            return $solde->solde;
        } catch (PDOException $exception) {
            return $exception;
        }
    }


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
        else
            return 0;
    }

    public function saveInfosTransfert($num_transac,$code,$montant,$frais,$montant_total,$date_tranfert,$nom_sender,$prenom_sender,$type_piece_sender,$cin_sender,$tel_sender,$pays_sender,$ville_sender,$adresse_sender,$nom_receiver,$prenom_receiver,$tel_receiver,$pays_receiver,$ville_receiver,$adresse_receiver,$fk_service,$user_sender,$agencesender,$refund, $user_sender_support )
    {
        $insertSQL = "INSERT INTO tranfert ( num_transac,code, montant,frais,montant_total, date_tranfert, nom_sender, prenom_sender,type_piece_sender, cin_sender, tel_sender, pays_sender, ville_sender,adresse_sender, nom_receiver, prenom_receiver, tel_receiver, pays_receiver,ville_receiver, adresse_receiver, statut,refound, fk_service, user_sender, user_receiver,agencesender, user_support_sender )";
        $insertSQL .= " VALUES(:num_transac,:code_transfert, :montant,:frais,:montant_total, :date_tranfert, :nom_sender, :prenom_sender,:type_piece_sender, :cin_sender, :tel_sender, :pays_sender,:ville_sender, :adresse_sender, :nom_receiver, :prenom_receiver, :tel_receiver, :pays_receiver,:ville_receiver,:adresse_receiver, :statut, :refound,:fk_service, :user_sender, :user_receiver,:agencesender, :user_sender_support)";
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
            "refound"=>$refund,
            "fk_service"=>$fk_service,
            "user_sender"=>$user_sender,
            "user_receiver"=>0,
            "agencesender"=>$agencesender,
            "user_sender_support"=>$user_sender_support));
        if($rs_insert->rowCount() === 1)
            return 1;
        else
            return 0;
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



    public function updateInfosPaiementA($user,$date_receive,$type_piece,$piece,$idtransfert,$date_delivrance,$date_expiration, $fkusersupport, $fkagence)
    {
        try{

            $Update = "UPDATE tranfert SET statut = 1, user_receiver = :user_receiver ,date_receiver = :datereception, type_piece_receiver = :type_piece_receiver, cin_receiver = :cin_receiver, date_exp_receiver = :date_exp, date_delivrance_receiver = :date_delivrance, agencereceiver = :agencereceiver, user_supprot_receiver = :user_supprot_receiver WHERE idtransfert = :idtransfert ";
            //$Update = "UPDATE tranfert SET statut = 1, user_receiver = ".$user." ,date_receiver = '".$date_receive."', type_piece_receiver = '".$type_piece."', cin_receiver = '".$piece."', date_exp_receiver = '".$date_expiration."', date_delivrance_receiver = '".$date_delivrance."', agencereceiver = ".$fkagence.", user_supprot_receiver = ".$fkusersupport." WHERE idtransfert = ".$idtransfert;

            $rq_Update = $this->getConnexion()->prepare($Update);
            $rq_Update->bindParam("user_receiver",$user);
            $rq_Update->bindParam("datereception",$date_receive);
            $rq_Update->bindParam("type_piece_receiver",$type_piece);
            $rq_Update->bindParam("cin_receiver",$piece);
            $rq_Update->bindParam("date_delivrance",$date_delivrance);
            $rq_Update->bindParam("date_exp",$date_expiration);
            $rq_Update->bindParam("agencereceiver",$fkagence);
            $rq_Update->bindParam("user_supprot_receiver",$fkusersupport);
            $rq_Update->bindParam("idtransfert",$idtransfert);
            $rq_Update->execute();
            if($rq_Update->rowCount() === 1)
                return 1;
            else
                return -1;

        }
        catch (\PDOException $e){
            return $e;
        }

    }

    /***************** Fonction de recuperation des envoies via code et telephone *********************/
    public function getInfosTransfertByCode($code,$tel)
    {
        $query_rq_transfert = "SELECT * FROM tranfert WHERE code  =:code AND tel_receiver = :tel AND statut=0 ";
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
    public function getInfosTransfertByCode1($code,$tel)
    {
        $query_rq_transfert = "SELECT * FROM tranfert WHERE code  =:code AND tel_sender = :tel AND statut=0 ";
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


    /***************update statut remboursement***************/
    public function update_statut_remboursement($idtransfert, $fk_agence, $fk_user, $fkusersupport)
    {
        //$dbh = Connection();
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE tranfert SET refound=1, user_reverse = :agent, agencereverse = :agence, user_support_reverse = :usersupport, date_reverse = :datereverse WHERE idtransfert=:idtransfert");
            $Result1=$req->execute(
                array(
                    "agent" => $fk_user,
                    "agence" => $fk_agence,
                    "usersupport" => $fkusersupport,
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
           //var_dump($e);
        }

        return $return;
    }

    public function getHistoCarte($requestData = null){
        try {
            $sql = "SELECT t.rowid, t.num_transac, t.date_transaction, s.label as service, t.montant, c.telephone, CONCAT(u.prenom, ' ', u.nom) as agent, a.label as agence, CONCAT(us.prenom, ' ', us.nom) as support 
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN carte c ON c.rowid = t.fk_carte 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    INNER JOIN user us ON us.rowid = t.fkuser_support
                    WHERE t.statut = 1";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_transac LIKE '%" . $requestData . "%' ";
                $sql .= " OR date_transaction LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE '%" . $requestData . "%' ";
                $sql .= " OR service LIKE '%" . $requestData . "%' ";
                $sql .= " OR agent LIKE '%" . $requestData . "%' ";
                $sql .= " OR telephone LIKE '%" . $requestData . "%' ";
                $sql .= " OR agence LIKE '%" . $requestData . "%' ";
                $sql .= " OR support LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['num_transac', 'date_transaction', 'service', 'montant', 'telephone', 'agent', 'agence', 'support'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getHistoCarteCount()
    {
        try {
            $sql = "SELECT count(t.rowid) as total FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN carte c ON c.rowid = t.fk_carte 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    INNER JOIN user us ON us.rowid = t.fkuser_support
                    WHERE t.statut = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getDetailsHistoCarte($arg, $requestData = null){

        try {
            $sql = "SELECT t.rowid, t.num_transac, t.date_transaction, s.label as service, t.montant 
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    WHERE t.statut = 1 AND t.fk_carte = ".$arg[0];
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_transac LIKE '%" . $requestData . "%' ";
                $sql .= " OR date_transaction LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE '%" . $requestData . "%' ";
                $sql .= " OR service LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['num_transac', 'date_transaction', 'service', 'montant'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getDetailsHistoCarteCount($arg)
    {
        try {
            $sql = "SELECT count(t.rowid) as total FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    WHERE t.statut = 1 AND t.fk_carte = ".$arg[0];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getHistoCarteByUserSupport($arg, $requestData = null)
    {
       //AND t.fkuser_support = ".$arg[0];
        try {
            $sql = "SELECT t.rowid, t.num_transac, t.date_transaction, s.label as service, t.montant, c.telephone, CONCAT(u.prenom, ' ', u.nom) as agent, a.label as agence
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN carte c ON c.rowid = t.fk_carte 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    WHERE t.statut = 1 AND t.fkuser_support = ".$arg[0];

            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_transac LIKE '%" . $requestData . "%' ";
                $sql .= " OR date_transaction LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE '%" . $requestData . "%' ";
                $sql .= " OR service LIKE '%" . $requestData . "%' ";
                $sql .= " OR agent LIKE '%" . $requestData . "%' ";
                $sql .= " OR telephone LIKE '%" . $requestData . "%' ";
                $sql .= " OR agence LIKE '%" . $requestData . "%' ";
                $sql .= " OR support LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['num_transac', 'date_transaction', 'service', 'montant', 'telephone', 'agent', 'agence', 'support'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getHistoCarteByUserSupportCount($arg)
    {
        try {
            $sql = "SELECT count(t.rowid) as total FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN carte c ON c.rowid = t.fk_carte 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    WHERE t.statut = 1 AND t.fkuser_support = ".$arg[0];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getInfosTransfertByMontant($montant, $tel, $code=''){
        $sql = '';
        if($code != ''){
            $sql .= ' AND code = '.$code;
        }
        $query_rq_transfert = "SELECT * FROM tranfert WHERE montant  =:montant AND tel_sender = :tel AND statut=0 ".$sql;
        $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("montant",$montant);
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

    public function getInfosTransfertByCodeIdMontant($code, $tel, $id, $montant){

        try{
            $query_rq_transfert = "SELECT * FROM tranfert WHERE montant  = :montant AND tel_sender = :tel AND statut=0 AND idtransfert = :id AND code = :code";
            $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
            $rq_transfert->bindParam("montant",$montant);
            $rq_transfert->bindParam("tel",$tel);
            $rq_transfert->bindParam("id",$id);
            $rq_transfert->bindParam("code",$code);
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
    public function update_statut_annulation($idtransfert, $fk_agence, $fk_user, $fkusersupport)
    {
        //$dbh = Connection();
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE tranfert SET statut = 3, user_cancel = :agent, agencecancel = :agence, user_support_cancel = :usersupport, date_cancel = :datereverse WHERE idtransfert = :idtransfert");
            $Result1=$req->execute(
                array(
                    "agent" => $fk_user,
                    "agence" => $fk_agence,
                    "usersupport" => $fkusersupport,
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
            //var_dump($e);
        }

        return $return;
    }

    public function getHistoTeliman($requestData = null){
        try {

            $sql = "SELECT t.rowid, t.num_transac, t.date_transaction, s.label as service, t.montant, CONCAT(c.prenom_sender, ' ', c.nom_sender) as expediteur, c.tel_sender, CONCAT(c.prenom_receiver, ' ', c.nom_receiver) as destinataire, c.tel_receiver, c.tel_receiver, CONCAT(u.prenom, ' ', u.nom) as agent, a.label as agence, CONCAT(us.prenom, ' ', us.nom) as support 
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN tranfert c ON c.num_transac = t.num_transac 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    INNER JOIN user us ON us.rowid = t.fkuser_support
                    WHERE t.statut = 1";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_transac LIKE '%" . $requestData . "%' ";
                $sql .= " OR date_transaction LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE '%" . $requestData . "%' ";
                $sql .= " OR service LIKE '%" . $requestData . "%' ";
                $sql .= " OR expediteur LIKE '%" . $requestData . "%' ";
                $sql .= " OR tel_sender LIKE '%" . $requestData . "%' ";
                $sql .= " OR destinataire LIKE '%" . $requestData . "%' ";
                $sql .= " OR tel_receiver LIKE '%" . $requestData . "%' ";
                $sql .= " OR agent LIKE '%" . $requestData . "%' ";
                $sql .= " OR agence LIKE '%" . $requestData . "%' ";
                $sql .= " OR support LIKE '%" . $requestData . "%')";
            }
            $tabCol = ['num_transac', 'date_transaction', 'service', 'montant', 'expediteur', 'tel_sender', 'destinataire', 'tel_receiver', 'agent', 'agence', 'support'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getHistoTelimanCount()
    {
        try {
            $sql = "SELECT count(t.rowid) as total
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN tranfert c ON c.num_transac = t.num_transac 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    INNER JOIN user us ON us.rowid = t.fkuser_support
                    WHERE t.statut = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getHistoTelimanByUserSupport($arg, $requestData = null){
        try {
            $sql = "SELECT t.rowid, t.num_transac, t.date_transaction, s.label as service, t.montant, CONCAT(c.prenom_sender, ' ', c.nom_sender) as expediteur, c.tel_sender, CONCAT(c.prenom_receiver, ' ', c.nom_receiver) as destinataire, c.tel_receiver, c.tel_receiver, CONCAT(u.prenom, ' ', u.nom) as agent, a.label as agence
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN tranfert c ON c.num_transac = t.num_transac 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    WHERE t.statut = 1 AND t.fkuser_support = ".$arg[0];
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_transac LIKE '%" . $requestData . "%' ";
                $sql .= " OR date_transaction LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE '%" . $requestData . "%' ";
                $sql .= " OR service LIKE '%" . $requestData . "%' ";
                $sql .= " OR expediteur LIKE '%" . $requestData . "%' ";
                $sql .= " OR tel_sender LIKE '%" . $requestData . "%' ";
                $sql .= " OR destinataire LIKE '%" . $requestData . "%' ";
                $sql .= " OR tel_receiver LIKE '%" . $requestData . "%' ";
                $sql .= " OR agent LIKE '%" . $requestData . "%' ";
                $sql .= " OR agence LIKE '%" . $requestData . "%' ";
                $sql .= " OR support LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['num_transac', 'date_transaction', 'service', 'montant', 'expediteur', 'tel_sender', 'destinataire', 'tel_receiver', 'agent', 'agence', 'support'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getHistoTelimanByUserSupportCount($arg)
    {
        try {
            $sql = "SELECT count(t.rowid) as total
                    FROM transaction t 
                    INNER JOIN service s ON s.rowid = t.fk_service 
                    INNER JOIN user u ON u.rowid = t.fkuser 
                    INNER JOIN transfert c ON c.num_transac = t.num_transac 
                    INNER JOIN agence a ON a.rowid = t.fk_agence
                    WHERE t.statut = 1 AND t.fkuser_support = ".$arg[0];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

}