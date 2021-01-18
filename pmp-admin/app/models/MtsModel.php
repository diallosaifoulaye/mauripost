<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class MtsModel extends \app\core\BaseModel{

    public function historiques($requestData = null){

        $datedeb = $requestData['deb'];
        $datefin = $requestData['fin'];
        if(!is_null($requestData['deb'])){ $requestData = null; }

            if($datedeb != '' && $datefin != ''){
                $query = "SELECT  t.idtransfert as rowid, t.numtrans, t.datetrans as date_debut, t.montant, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, t.telephone_rec, a.label, CONCAT(u.prenom, ' ', u.nom) as createur
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) BETWEEN '".$datedeb."' AND '".$datefin."'";
            }
            else if($datedeb != '' && $datefin == ''){
                $query = "SELECT  t.idtransfert as rowid, t.numtrans, t.datetrans as date_debut, t.montant, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, t.telephone_rec, a.label, CONCAT(u.prenom, ' ', u.nom) as createur
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) >='".$datedeb."'";
            }
            else if($datedeb == '' && $datefin != ''){
                $query = "SELECT  t.idtransfert as rowid, t.numtrans, t.datetrans as date_debut, t.montant, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, t.telephone_rec, a.label, CONCAT(u.prenom, ' ', u.nom) as createur
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) <='".$datefin."'";
            }
            else{
                $query = "SELECT  t.idtransfert as rowid, t.numtrans, t.datetrans as date_debut, t.montant, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, t.telephone_rec, a.label, CONCAT(u.prenom, ' ', u.nom) as createur
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1";
            }

        if(!is_null($requestData)) {
            $query.=" WHERE (numtrans LIKE '%".$requestData."%' ";
            $query.=" OR datetrans LIKE '%".$requestData."%' ";
            $query.=" OR montant LIKE '%".$requestData."%' ";
            $query.=" OR rec LIKE '%".$requestData."%' ";
            $query.=" OR telephone_rec LIKE '%".$requestData."%' ";
            $query.=" OR a.label LIKE '%".$requestData."%' ";
            $query.=" OR u.prenom LIKE '%".$requestData."%' ";
            $query.=" OR u.nom LIKE '%".$requestData."%' )";
        }

        $tabCol = ['numtrans','datetrans', 'montant', 'rec', 'telephone_rec', 'label', 'createur'];

        if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $query.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
        $query .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        try{
            $user = $this->getConnexion()->prepare($query);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (Exception $e){
            return $e;
        }

    }

    public function  historiquesCount($requestData = null)
    {
        $datedeb = $requestData['deb'];
        $datefin = $requestData['fin'];
        if(!is_null($requestData['deb'])){ $requestData = null; }

        if($datedeb != '' && $datefin != ''){
            $query = "SELECT t.idtransfert as rowid, t.*, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, CONCAT(u.prenom, ' ', u.nom) as createur, a.label
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) BETWEEN '".$datedeb."' AND '".$datefin."'";
        }
        else if($datedeb != '' && $datefin == ''){
            $query = "SELECT t.idtransfert as rowid, t.*, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, CONCAT(u.prenom, ' ', u.nom) as createur, a.label
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) >='".$datedeb."'";
        }
        else if($datedeb == '' && $datefin != ''){
            $query = "SELECT t.idtransfert as rowid, t.*, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, CONCAT(u.prenom, ' ', u.nom) as createur, a.label
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1 
                      AND DATE(t.datetrans) <='".$datefin."'";
        }
        else{
            $query = "SELECT t.idtransfert as rowid, t.*, CONCAT(t.prenom_rec, ' ', t.nom_rec) as rec, CONCAT(u.prenom, ' ', u.nom) as createur, a.label
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.statut = 1";
        }

        try
        {
            $user = $this->getConnexion()->prepare($query);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function genererNumtransaction()
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
        $this->closeConnexion();

        return $code_generer;
    }

    public function saveTransfert($numtrans, $datetrans, $montant, $commission, $prenom_sender, $nom_sennder, $telephone_sender, $prenom2_sender, $adresse_sender, $ville_sender, $etat_sender, $pays_sender, $prenom_rec, $nom_rec, $prenom2_rec, $adresse_rec, $telephone_rec, $ville_rec, $etat_rec, $pays_rec, $type_piece_rec, $numero_piece_rec, $trannsfertNO, $montant_reel, $devise, $taux_de_change, $user_creation, $statut, $agence){
        try{
            $query = "INSERT INTO `mts_transfert`(`numtrans`, `datetrans`, `montant`, `commission`, `prenom_sender`, `nom_sennder`, `telephone_sender`, `prenom2_sender`, `adresse_sender`, `ville_sender`, `etat_sender`, `pays_sender`, `prenom_rec`, `nom_rec`, `prenom2_rec`, `adresse_rec`, `telephone_rec`, `ville_rec`, `etat_rec`, `pays_rec`, `type_piece_rec`, `numero_piece_rec`, `trannsfertNO`, `montant_reel`, `devise`, `taux_de_change`, `user_creation`, `statut`, `agence`) 
                  VALUES (:numtrans, :datetrans, :montant, :commission, :prenom_sender, :nom_sennder, :telephone_sender, :prenom2_sender, :adresse_sender, :ville_sender, :etat_sender, :pays_sender, :prenom_rec, :nom_rec, :prenom2_rec, :adresse_rec, :telephone_rec, :ville_rec, :etat_rec, :pays_rec, :type_piece_rec, :numero_piece_rec, :trannsfertNO, :montant_reel, :devise, :taux_de_change, :user_creation, :statut, :agence)";
            $rs_carte = $this->getConnexion()->prepare($query);
            $rs_carte->bindParam('numtrans', $numtrans);
            $rs_carte->bindParam('datetrans', $datetrans);
            $rs_carte->bindParam('montant', $montant);
            $rs_carte->bindParam('commission', $commission);
            $rs_carte->bindParam('prenom_sender', $prenom_sender);
            $rs_carte->bindParam('nom_sennder', $nom_sennder);
            $rs_carte->bindParam('telephone_sender', $telephone_sender);
            $rs_carte->bindParam('prenom2_sender', $prenom2_sender);
            $rs_carte->bindParam('adresse_sender', $adresse_sender);
            $rs_carte->bindValue('ville_sender', $ville_sender);
            $rs_carte->bindValue('etat_sender', $etat_sender);
            $rs_carte->bindValue('pays_sender', $pays_sender);
            $rs_carte->bindValue('prenom_rec', $prenom_rec);
            $rs_carte->bindValue('nom_rec', $nom_rec);
            $rs_carte->bindValue('prenom2_rec', $prenom2_rec);
            $rs_carte->bindValue('adresse_rec', $adresse_rec);
            $rs_carte->bindValue('telephone_rec', $telephone_rec);
            $rs_carte->bindValue('ville_rec', $ville_rec);
            $rs_carte->bindValue('etat_rec', $etat_rec);
            $rs_carte->bindValue('pays_rec', $pays_rec);
            $rs_carte->bindValue('type_piece_rec', $type_piece_rec);
            $rs_carte->bindValue('numero_piece_rec', $numero_piece_rec);
            $rs_carte->bindValue('trannsfertNO', $trannsfertNO);
            $rs_carte->bindValue('montant_reel', $montant_reel);
            $rs_carte->bindValue('devise', $devise);
            $rs_carte->bindValue('taux_de_change', $taux_de_change);
            $rs_carte->bindValue('user_creation', $user_creation);
            $rs_carte->bindValue('statut', $statut);
            $rs_carte->bindValue('agence', $agence);
            $this->closeConnexion();
            return $rs_carte->execute();

        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function changeStatutTransaction($num_transac, $commentaire, $statut){
        try{
            $query = "UPDATE `transaction` SET commentaire = CONCAT(commentaire, '-', :commentaire), statut = :statut WHERE num_transac = :num_transac";
            $rs_carte = $this->getConnexion()->prepare($query);
            $rs_carte->bindParam('num_transac', $num_transac);
            $rs_carte->bindValue('statut', $statut);
            $rs_carte->bindValue('commentaire', $commentaire);
            $this->closeConnexion();
            return $rs_carte->execute();

        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function crediterSoldeAgence($idagence, $montant){
        try{
            $query = "UPDATE `Agence` SET solde = solde + :soldes WHERE idAgence = :id";
            $rs_carte = $this->getConnexion()->prepare($query);
            $rs_carte->bindParam('soldes', $montant);
            $rs_carte->bindValue('id', $idagence);
            $this->closeConnexion();
            return $rs_carte->execute();

        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function debiterSoldeAgence($idagence, $montant){
        try{
            $query = "UPDATE `Agence` SET solde = solde - :soldes WHERE idAgence = :id";
            $rs_carte = $this->getConnexion()->prepare($query);
            $rs_carte->bindParam('soldes', $montant);
            $rs_carte->bindValue('id', $idagence);
            $this->closeConnexion();
            return $rs_carte->execute();

        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function detailsHistorique($numtrans){
        try{
            $query = "SELECT t.*, CONCAT(u.prenom, ' ', u.nom) as createur, a.label
                      FROM mts_transfert t 
                      LEFT JOIN user u ON u.rowid = t.user_creation 
                      LEFT JOIN agence a ON t.agence = a.rowid 
                      WHERE t.idtransfert ='".$numtrans."'";
            $rs_carte = $this->getConnexion()->prepare($query);
            $rs_carte->execute();
            $allTrans = $rs_carte->fetchObject();
            $this->closeConnexion();
            return $allTrans;

        }catch(PDOException $e)
        {
            return -1;
        }
    }

}