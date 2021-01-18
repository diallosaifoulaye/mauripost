<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */

namespace app\core;


date_default_timezone_set('Indian/Antananarivo');

class UtilsUtilisateur
{
    /**************Abigail********************************************/

    private $connexion;
    private $utils;

    public function __construct()
    {
        $this->connexion = new Connexion();
        $this->utils = new Utils();
    }

    /*****Liste Profils******/
    public function  allProfil()
    {
        try
        {
            //$sql = "Select rowid, label, type_profil from profil WHERE etat = :etat ORDER BY label ASC";
            $sql = "Select p.rowid, p.label, p.type_profil from profil p INNER JOIN type_profil tp ON p.type_profil = tp.idtypeprofil WHERE p.etat = :etat ORDER BY p.label ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1,));
            $a = $user->fetchAll();
             $this->connexion->closeConnexion();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }
    }

    /**********verifier Identifiant**********/
    public function verifEmail($login)
    {
        try
        {
            $sql = "Select u.rowid from user as u WHERE u.email=:login";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("login"=>$login));
            $a = $user->fetchObject();
             $this->connexion->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

    /*************Detail User**************/
    public function getUser($id)
    {
        try
        {
            $sql = "Select u.rowid, u.prenom, u.nom, u.code_guichet, u.login, u.telephone, u.email, u.date_creation,
                    u.user_creation, u.date_modification, u.user_modification, p.label as profil,
                    a.label as agence, u.etat
                from user as u
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid
                WHERE u.rowid =:id";

            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));

            $a = $user->fetchObject();
             $this->connexion->closeConnexion();
            return $a;
        }
        catch (PDOException $PDOException)
        {
            return -1;
        }
    }

    /*******************Modifier User*****************/
    public function updateUtilisateur($nom, $cni,$prenom, $email, $telephone, $profil, $agence, $id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql="UPDATE user SET nom=:nom,code_guichet=:cni,prenom=:prenom, email=:mail, telephone=:tel, fk_profil=:profil, fk_agence=:agence, date_modification=:date_modification, user_modification=:user_modification WHERE rowid=:id";
            $user= $this->connexion->getConnexion()->prepare($sql);
            $res= $user->execute(array(
                "nom" =>$nom,
                "cni" =>$cni,
                "prenom"=>$prenom,
                "mail"=>$email,
                "tel"=>$telephone,
                "profil"=>$profil,
                "agence"=>$agence,
                "date_modification"=>$date_modification,
                "user_modification"=>$user_modification,
                "id" =>$id
            ));
             $this->connexion->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $e )
        {
            return -2;
        }
    }

    /***********Regénérer Password************/
    public function resetPasswordUtilisateur($id, $password, $user_modification){

        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE user SET password=:pwd, connect=:conn, date_modification=:date_modification, user_modification=:user_modification WHERE rowid=:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("pwd"=>sha1('NUMH'.$password), "conn"=>0, "date_modification"=>$date_modification, "user_modification"=>$user_modification, "id"=>$id));
             $this->connexion->closeConnexion();
            if($res) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }

    /***********Desactivation User************/
    public function deleteUtilisateur($id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE user SET etat= :etat, date_modification=:date_modification, user_modification=:user_modification WHERE rowid = :id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("etat"=>0, "id" =>$id, "date_modification"=>$date_modification, "user_modification"=>$user_modification));
             $this->connexion->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception)
        {
            return -2;
        }
    }

    /***********Activation User************/
    public function activerUtilisateur($id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE user SET etat=:etat, date_modification=:date_modification, user_modification=:user_modification WHERE rowid = :id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("etat"=>1, "id" =>$id, "date_modification"=>$date_modification, "user_modification"=>$user_modification));
             $this->connexion->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception)
        {
            return -2;
        }
    }
    /**************Liste User*********/
    public function  allUser($requestData = null)
    {
        try
        {
            $sql = "Select u.rowid, u.nom, u.prenom, u.email, u.telephone, p.label as profil, a.label as agence, u.etat
                from user as u
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid ";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( u.prenom LIKE '%".$requestData."%' ";
                $sql.=" OR u.nom LIKE '%".$requestData."%' ";
                $sql.=" OR  u.email LIKE '%".$requestData."%' ";
                $sql.=" OR u.telephone LIKE '%".$requestData."%' ";
                $sql.=" OR p.label LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR u.etat = ".$etat;
                $sql.=" OR a.label LIKE  '%".$requestData."%' )";
            }
            $tabCol = ['u.nom', 'u.prenom', 'u.email', 'u.telephone', 'p.label', 'u.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
             $this->connexion->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function allUserCount()
    {
        try {
            $sql = "SELECT COUNT(u.rowid) as total FROM user as u
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid ";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getAllAgenceByType($idtype){
        try {
            $sql = "Select a.rowid, a.label
                from agence as a
                WHERE a.etat = :etat AND a.idtype_agence = :idtype_agence
                ORDER BY a.label ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "idtype_agence" => $idtype));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    /**********verifier Identifiant**********/
    public function verifIdentifiant($login)
    {
        try
        {
            $sql = "Select u.rowid from user as u WHERE u.login=:login";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("login"=>$login));
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }
    /********Ajouter Utilisateur******/
    public function insertUser($nom, $cni ,$prenom, $email, $telephone, $profil, $agence, $login, $password, $user_creation){

        try
        {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO user (nom, code_guichet ,prenom, email, telephone, login, password, fk_profil, fk_agence, admin, connect, etat, user_creation, date_creation) 
            VALUES (:nom, :cni,:prenom, :mail, :tel, :login, :pwd, :profil, :agence, :admin, :connecter, :etat, :user_creation, :date_creation)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "nom" =>$nom,
                "cni" =>$cni,
                "prenom" =>$prenom,
                "mail" =>$email,
                "tel" => $telephone,
                "login" => $login,
                "pwd" => sha1('NUMH'.$password),
                "profil" =>$profil,
                "agence" => $agence,
                "admin" =>0,
                "connecter" =>0,
                "etat" => 1,
                "user_creation" =>$user_creation,
                "date_creation" =>$date_creation
            ));
            $this->connexion->closeConnexion();
            if($res==1)
            {
                return 1;
            }
        }
        catch(PDOException $Exception ){
            return -1;
        }

    }

    /**********verifier CNI**********/
    public function verifCNI($cni)
    {
        try
        {
            $sql = "Select u.code_guichet from user as u WHERE u.code_guichet=:cni";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("cni"=>$cni));
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

    public function verifCNIUpdate($cni, $id)
    {
        try
        {
            $sql = "Select u.code_guichet from user as u WHERE u.code_guichet=:cni AND u.rowid!=:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("cni"=>$cni, "id"=>$id));
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
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