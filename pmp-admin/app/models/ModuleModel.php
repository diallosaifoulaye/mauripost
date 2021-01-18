<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class ModuleModel extends \app\core\BaseModel
{

    /**
     * @param $nom_moduleModule, $typeModule,$user_creation
     * @return int
     * CETTE METHODE PERMET D'AJOUTER UN Module
     */
    public function insertModule($nom_module,$user_creation){

        try{
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO module(nom_module, user_creation, date_creation) 
                    VALUES (:nom_module, :user_creation, :date_creation)";
            $Module = $this->getConnexion()->prepare($sql);
            $res = $Module->execute(array(
                "nom_module" =>$nom_module,
                "user_creation" =>$user_creation,
                "date_creation" =>$date_creation
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
     * @param $nom_module, $typeModule, $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN Module
     */
    public function updateModule($nom_module,$id,$user_modification){
//var_dump($nom_module." ".$id."/".$user_modification);exit;

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE module SET nom_module = :libelle, user_modification = :user_modification, date_modification = :date_modification
                WHERE idmodule = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "libelle" => $nom_module,
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "id" => $id,
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
     * CETTE METHODE PERMET DE DESACTIVER UN Module
     */
    public function desactiveModule($id,$user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
           // var_dump($id." ".$user_modification."/".$date_modification);exit;
            $sql = "UPDATE module SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification
                WHERE idmodule = :id";
            //var_dump($sql);exit;
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => 0,
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "id" => $id,
            ));

            $this->closeConnexion();
            if($res==1){
                return 1;
            }
        }
        catch(PDOException $Exception ){
           return 0;
        }

    }

    /**
     * @param $id,$user_modification
     * @return int
     * CETTE METHODE PERMET D'ACTIVER UN Module
     */
    public function activateModule($id,$user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE module SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification
                WHERE idmodule = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(1),
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "id" => intval($id),
            ));
            $this->closeConnexion();
            if($res)
                return 1;
            else
                return -0;
        }
        catch(PDOException $Exception ){
            return -0;
        }

    }
    /**
     * @param $idString
     * @return Objet Module
     * CETTE METHODE RETOURNE LES DETAILS D'UN Module SELON UN IDENTIFIANT CHOISI
     */
    public function getModuleByIdString($idString){
        $sql = "Select p.idmodule as rowid, p.nom_module, p.etat, p.user_creation, p.date_creation, p.user_modification, p.date_modification
                from module as p
                WHERE p.idmodule =" .$idString;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }
    /**
     * @param $idString
     * @return Objet Module
     * CETTE METHODE RETOURNE LES DETAILS D'UN Module SELON UN IDENTIFIANT CHOISI
     */
    public function getModuleByIdInteger($id){
        $sql = "Select p.rowid,p.nom_module,tp.libelle,p.etat from Module p 
                INNER JOIN type_Module tp ON tp.idtypeModule=p.type_Module
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
     * CETTE METHODE RETOURNE LES DETAILS D'UN Module SELON UN IDENTIFIANT CHOISI
     */
    public function verifyModule($Module){
        $sql = "Select nom_module from Module WHERE nom_module = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => strval($Module),
            )
        );
        $a = $user->rowCount();
        $this->closeConnexion();
        return $a;

    }
    /**
     * @param $idString
     * @return Objet Module
     * CETTE METHODE RETOURNE LES DETAILS D'UN Module SELON UN IDENTIFIANT CHOISI
     */
    public function verifyModule2($Module, $id){
        $sql = "Select nom_module from Module WHERE rowid != :id AND nom_module = :lab";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => intval($id),
                "lab" => strval($Module),
            )
        );
        $a = $user->rowCount();
        $this->closeConnexion();
        return $a;

    }
    /**
     * @param $idString
     * @return Objet Module
     * CETTE METHODE RETOURNE LES DETAILS D'UN Module SELON UN IDENTIFIANT CHOISI
     */
    public function  allModuleWithTypeModule()
    {
        try
        {
            /*$sql = "Select p.rowid, p.nom_module, tp.libelle FROM Module as p INNER JOIN type_Module as tp
                    ON p.type_Module = tp.idtypeModule
                    WHERE p.etat = :etat";*/
            $sql = "Select p.rowid, p.nom_module, tp.libelle FROM Module as p INNER JOIN type_Module as tp
                    ON p.type_Module = tp.idtypeModule";
            $user = $this->getConnexion()->prepare($sql);
            //$user->execute(array("etat" => 1,));
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }
    }
    public function  allModule__($requestData = null)
    {
        try
        {
            $sql = "Select idmodule as rowid, nom_module, etat from module";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE (nom_module LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat.")";
            }

            $tabCol = ['nom_module', 'etat'];
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
    public function allModule__Count()
    {
        try {
            $sql = "SELECT COUNT(rowid) as total FROM Module";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }






    /*****Liste Modules******/
    public function  allModule()
    {
        try
        {
            //$sql = "Select rowid, nom_module, type_Module from Module WHERE etat = :etat ORDER BY nom_module ASC";
            $sql = "Select p.rowid, p.nom_module, p.type_Module from Module p INNER JOIN type_Module tp ON p.type_Module = tp.idtypeModule WHERE p.etat = :etat ORDER BY p.nom_module ASC";
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