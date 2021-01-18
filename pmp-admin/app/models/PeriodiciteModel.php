<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class PeriodiciteModel extends \app\core\BaseModel
{

    /**
     * @param $labelprofil, $nombre_mois,$user_creation
     * @return int
     * CETTE METHODE PERMET D'AJOUTER UN PERIODICITE
     */
    public function insertPeriodicite($label, $nombre_mois){

        try{

            $sql = "INSERT INTO periodicite_postale(label, nombre_mois) 
                    VALUES (:label, :nombre_mois)";
            $periodicite_postale = $this->getConnexion()->prepare($sql);
            $res = $periodicite_postale->execute(array(
                "label" =>$label,
                "nombre_mois" =>$nombre_mois
            ));
            $this->closeConnexion();
            if($res==1)
            {
                return 1;
            }
        }
        catch(PDOException $Exception ){
            $res = -1;
        }
        return $res;

    }


    /**
     * @param $label, $nombre_mois, $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN PERIODICITE
     */
    public function updatePeriodicite($label, $nombre_mois, $id){

        try{

            $sql = "UPDATE periodicite_postale SET label = :label, nombre_mois = :nombre_mois
                WHERE rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "label" => $label,
                "nombre_mois" => $nombre_mois,
                "id" => intval($id),

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
    public function desactiverPeriodicite($id){

        try{
            $sql = "UPDATE periodicite_postale SET etat = :etat
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
    public function activerPeriodicite($id){

        try{

            $sql = "UPDATE periodicite_postale SET etat = :etat
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
    public function getPeriodiciteByIdInteger($id){
        $sql = "Select rowid, label, nombre_mois, etat from periodicite_postale p 
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
    public function getPeriodiciteByIdString($idString)
    {
        $sql = "Select rowid, label, nombre_mois, etat from periodicite_postale  
                WHERE rowid =" .$idString;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }


    public function  allPeriodicite__($requestData = null)
    {
        try
        {
            $sql = "Select rowid, label, nombre_mois, etat from periodicite_postale";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE (label LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat.")";
            }

            $tabCol = ['label', 'etat'];
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
    public function allPeriodicite__Count()
    {
        try {
            $sql = "SELECT COUNT(rowid) as total FROM periodicite_postale ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }




    /*****Liste Periodicites******/
    public function  allPeriodicite()
    {
        try
        {
            //$sql = "Select rowid, label, type_profil from profil WHERE etat = :etat ORDER BY label ASC";
            $sql = "Select rowid, label, nombre_mois from periodicite_postale  WHERE etat = :etat ORDER BY label ASC";
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