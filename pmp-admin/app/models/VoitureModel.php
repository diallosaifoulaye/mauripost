<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class VoitureModel extends \app\core\BaseModel
{

    /**
     * @param $marque profil, $modele ,$user_creation
     * @return int
     * CETTE METHODE PERMET D'AJOUTER UN PERIODICITE
     */
    public function insertVoiture($marque, $modele, $matricule, $nb_place, $user_creation){

        try{

            $sql = "INSERT INTO transpost_voiture(marque, modele, matricule, nb_place, user_creation) 
                    VALUES (:marque, :modele, :matricule, :nb_place, :user_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "marque" =>$marque,
                "modele" =>$modele,
                "matricule" =>$matricule,
                "nb_place" =>$nb_place,
                "user_creation" =>intval($user_creation)
            ));

            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }


    /**
     * @param $marque , $modele , $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN PERIODICITE
     */
    public function updateVoiture($marque , $modele , $matricule, $nb_place, $user_modification, $rowid){

        try{

            $sql = "UPDATE transpost_voiture SET marque  = :marque, modele  = :modele, matricule  = :matricule, nb_place  = :nb_place, user_modification = :user_modification
                WHERE rowid = :rowid";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "marque" => $marque,
                "modele" => $modele,
                "matricule" => $matricule,
                "nb_place" => $nb_place,
                "user_modification" => $user_modification,
                "rowid" => intval($rowid),

            ));
            $this->closeConnexion();
            if($res==1){
                return 1;
            }
        }
        catch(PDOException $Exception ){
            return -1;
        }


    }
    /**
     * @param $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE DESACTIVER UN PERIODICITE
     */
    public function desactiverVoiture($id){

        try{
            $sql = "UPDATE transpost_voiture SET etat = :etat
                WHERE rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(0),
                "id" => intval($id),
            ));
            $this->closeConnexion();
            if($res==1){
                return 2;
            }
        }
        catch(PDOException $Exception ){
           return -2;
        }

    }

    /**
     * @param $id,$user_modification
     * @return int
     * CETTE METHODE PERMET D'ACTIVER UN PERIODICITE
     */
    public function activerVoiture($id){

        try{

            $sql = "UPDATE transpost_voiture SET etat = :etat
                WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(1),
                "id" => intval($id),
            ));
            $this->closeConnexion();
            if($res)
                return 4;
            else
                return -4;
        }
        catch(PDOException $Exception ){
            return -4;
        }

    }

    /**
     * @param $idString
     * @return Objet profil
     * CETTE METHODE RETOURNE LES DETAILS D'UN PERIODICITE SELON UN IDENTIFIANT CHOISI
     */
    public function getVoitureByIdInteger($id){
        $sql = "Select rowid, marque , modele , etat, matricule from transpost_voiture  
                WHERE rowid = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("id" => strval($id),));
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }

    /**
     * @param $idString
     * @return Objet Module
     * CETTE METHODE RETOURNE LES DETAILS D'UNE PERIODICITE SELON UN IDENTIFIANT CHOISI
     */
    public function getVoitureByIdString($idString)
    {
        $sql = "Select rowid, marque , modele , etat, matricule, nb_place from transpost_voiture  
                WHERE rowid =" .$idString;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }


    public function  allVoiture__($requestData = null)
    {
        try
        {
            $sql = "Select rowid, marque , modele , matricule,	nb_place,  etat from transpost_voiture";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE (marque  LIKE '%".$requestData."%' ";
                $sql.=" OR ( modele LIKE '%".$requestData."%' ";
                $sql.=" OR ( matricule LIKE '%".$requestData."%' ";
                $sql.=" OR ( nb_place LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat.")";
            }

            $tabCol = ['marque ','modele ','matricule ','nb_place ', 'etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;

        }
        catch(PDOException $e){
            return -1;
        }
    }


    /********Liste beneficiaires*********/
    public function allVoiture__Count()
    {
        try {
            $sql = "SELECT COUNT(rowid) as total FROM transpost_voiture ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }




    /*****Liste Voitures******/
    public function  allVoiture()
    {
        try
        {
            //$sql = "Select rowid, marque , type_profil from profil WHERE etat = :etat ORDER BY marque  ASC";
            $sql = "Select rowid, marque , modele, matricule  from transpost_voiture  WHERE etat = :etat ORDER BY marque  ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1,));
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }
    }
}