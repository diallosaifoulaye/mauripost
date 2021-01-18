<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class ProfilModel extends \app\core\BaseModel
{

    /**
     * @param $labelprofil, $typeprofil,$user_creation
     * @return int
     * CETTE METHODE PERMET D'AJOUTER UN PROFIL
     */
    public function insertProfil($labelprofil, $typeprofil,$user_creation){

        try{
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO profil(label, type_profil, user_creation, date_creation) 
                    VALUES (:label, :type_profil, :user_creation, :date_creation)";
            $profil = $this->getConnexion()->prepare($sql);
            $res = $profil->execute(array(
                "label" =>$labelprofil,
                "type_profil" =>$typeprofil,
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
     * @param $labelprofil, $typeprofil,$user_creation
     * @return int
     * CETTE METHODE PERMET D'AJOUTER UN PROFIL
     */
    public function insertNotif($nom,$email, $type){

        try{
            $sql = "INSERT INTO notifcation_stock(nom, email, type) 
                    VALUES (:nom, :email, :type)";
            $profil = $this->getConnexion()->prepare($sql);
            $res = $profil->execute(array(
                "nom" =>$nom,
                "email" =>$email,
                "type" =>$type
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
     * @param $label, $typeprofil, $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN PROFIL
     */
    public function updateProfil($label, $typeprofil, $id,$user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE profil SET label = :libelle, type_profil = :typeprofil, user_modification = :user_modification, date_modification = :date_modification
                WHERE rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "libelle" => $label,
                "typeprofil" => $typeprofil,
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
     * @param $label, $typeprofil, $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN PROFIL
     */
    public function updateNotif($nom,$email, $type, $id){

        try{
            $sql = "UPDATE notifcation_stock SET nom = :nom, email = :email, type = :type
                WHERE rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "nom" => $nom,
                "email" => $email,
                "type" => $type,
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
     * CETTE METHODE PERMET DE DESACTIVER UN PROFIL
     */
    public function disableProfil($id,$user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE profil SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification
                WHERE rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(0),
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
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
     * CETTE METHODE PERMET D'ACTIVER UN PROFIL
     */
    public function enableProfil($id,$user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE profil SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification
                WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(1),
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
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
     * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
     */
    public function getProfilByIdString($idString){
        $sql = "Select p.rowid, p.label, p.type_profil, p.etat, p.user_creation, p.date_creation, p.user_modification, p.date_modification, t.libelle
                from profil as p
                LEFT OUTER JOIN type_profil as t
                ON p.type_profil = t.idtypeprofil
                WHERE SHA1(CONCAT('NUMH', rowid)) = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => strval($idString),
            )
        );
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }
    /**
 * @param $idString
 * @return Objet profil
 * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
 */
    public function getProfilByIdInteger($id){
        $sql = "Select p.rowid,p.label,tp.libelle,p.etat from profil p 
                INNER JOIN type_profil tp ON tp.idtypeprofil=p.type_profil
                WHERE rowid = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("id" => strval($id),));
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }


    /**
     * @param $idString
     * @return Objet notif
     * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
     */
    public function getNotifByIdInteger($id){
        $sql = "Select p.rowid,p.nom,p.email,p.type,p.etat from notifcation_stock p 
                WHERE rowid = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("id" => strval($id),));
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;

    }

    /**
     * @param $idString
     * @return Objet profil
     * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
     */
    public function verifyProfil($profil){
        $sql = "Select label from profil WHERE label = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => strval($profil),
            )
        );
        $a = $user->rowCount();
        $this->closeConnexion();
        return $a;

    }
    /**
     * @param $idString
     * @return Objet profil
     * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
     */
    public function verifyProfil2($profil, $id){
        $sql = "Select label from profil WHERE rowid != :id AND label = :lab";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => intval($id),
                "lab" => strval($profil),
            )
        );
        $a = $user->rowCount();
        $this->closeConnexion();
        return $a;

    }
    /**
     * @param $idString
     * @return Objet profil
     * CETTE METHODE RETOURNE LES DETAILS D'UN PROFIL SELON UN IDENTIFIANT CHOISI
     */
    public function  allProfilWithTypeProfil()
    {
        try
        {
            /*$sql = "Select p.rowid, p.label, tp.libelle FROM profil as p INNER JOIN type_profil as tp
                    ON p.type_profil = tp.idtypeprofil
                    WHERE p.etat = :etat";*/
            $sql = "Select p.rowid, p.label, tp.libelle FROM profil as p INNER JOIN type_profil as tp
                    ON p.type_profil = tp.idtypeprofil";
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
    public function  allProfil__($requestData = null)
    {
        try
        {
            $sql = "Select p.rowid, p.label, tp.libelle, p.etat from profil p INNER JOIN type_profil tp ON p.type_profil = tp.idtypeprofil ";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( p.label LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR p.etat = ".$etat;
                $sql.=" OR tp.libelle LIKE '%".$requestData."%' )";
            }
            $tabCol = ['p.label', 'tp.libelle', 'p.etat'];
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
    public function allProfil__Count()
    {
        try {
            $sql = "SELECT COUNT(p.rowid) as total FROM profil p INNER JOIN type_profil tp ON p.type_profil = tp.idtypeprofil ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }



    public function  allNotif__($requestData = null)
    {
        try
        {
            $sql = "Select n.rowid, n.nom, n.email, n.etat from notifcation_stock n ";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( n.nom LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR p.etat = ".$etat;
                $sql.=" OR tp.email LIKE '%".$requestData."%' )";
            }
            $tabCol = ['n.nom', 'n.email', 'n.etat'];
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
    public function allNotif__Count()
    {
        try {
            $sql = "SELECT COUNT(n.rowid) as total FROM notifcation_stock n ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }





    /*****Liste Profils******/
    public function  allProfil()
    {
        try
        {
            //$sql = "Select rowid, label, type_profil from profil WHERE etat = :etat ORDER BY label ASC";
            $sql = "Select p.rowid, p.label, p.type_profil from profil p INNER JOIN type_profil tp ON p.type_profil = tp.idtypeprofil WHERE p.etat = :etat ORDER BY p.label ASC";
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

    /**
     * @param $id,$user_modification
     * @return int
     * CETTE METHODE PERMET D'ACTIVER UN PROFIL
     */
    public function enableNotif($id){

        try{
            $sql = "UPDATE notifcation_stock SET etat = :etat
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
    public function desableNotif($id){

        try{
            $sql = "UPDATE notifcation_stock SET etat = :etat
                WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(0),
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

    /**********verifier Identifiant**********/
    public function verifEmail($login)
    {
        try
        {
            $sql = "Select u.rowid from notifcation_stock as u WHERE u.email=:login";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("login"=>$login));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }


}