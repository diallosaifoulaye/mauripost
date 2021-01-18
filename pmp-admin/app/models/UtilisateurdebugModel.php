<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


require_once __DIR__.'/Utilisateur.class.php';

class UtilisateurdebugModel extends \app\core\BaseModel
{
    /**
     * Fonction de connexion d'un utilisateur
     */
    public function seConnecter($login,$password){

        $sql = "Select u.rowid, u.nom, u.prenom, u.email, u.telephone, u.login, u.password, ";
        $sql .= "u.fk_agence, p.rowid as profil, a.label as agence, a.num_carte, a.idtype_agence, u.admin, u.connect, u.etat  ";
        $sql .= " from user as u " ;
        $sql .= " LEFT OUTER JOIN profil as p " ;
        $sql .= " ON u.fk_profil = p.rowid " ;
        $sql .= " LEFT OUTER JOIN agence as a  " ;
        $sql .= " ON u.fk_agence = a.rowid " ;
        $sql .= " LEFT OUTER JOIN region r " ;
        $sql .= " ON a.province = r.idregion " ;
        $sql .= " WHERE login = :login " ;
        $sql .= " AND password = :password " ;

        try
        {
            $st = $this->getConnexion()->prepare($sql);
            $st->bindValue(':login', $login);
            $st->bindValue(':password', sha1('NUMH'.$password));
            $st->execute();
            $object = $st->fetchAll(\PDO::FETCH_OBJ);

            $sql1 = "UPDATE user SET deja_connecter=1 WHERE login=:login AND password=:password";
            $user1 = $this->getConnexion()->prepare($sql1);
            $user1->bindValue(':login', $login);
            $user1->bindValue(':password', sha1('NUMH'.$password));
            $res1 = $user1->execute();


                $this->closeConnexion();
                if(!empty($object)){
                    return $object;
                }
                else return null ;
        }
        catch (\PDOException $e)
        {
            return $e->getMessage();
        }
    }

    /********Ajouter Utilisateur******/
    public function insertUser($nom, $prenom, $email, $telephone, $profil, $agence, $login, $password, $user_creation){

        try
        {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO user (nom, prenom, email, telephone, login, password, fk_profil, fk_agence, admin, connect, etat, user_creation, date_creation) 
            VALUES (:nom, :prenom, :mail, :tel, :login, :pwd, :profil, :agence, :admin, :connecter, :etat, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "nom" =>$nom,
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
            $this->closeConnexion();
            if($res==1)
            {
               return 1;
            }
        }
        catch(PDOException $Exception ){
            return -1;
        }

    }

    /**********verifier Identifiant**********/
    public function verifIdentifiant($login)
    {
        try
        {
            $sql = "Select u.rowid from user as u WHERE u.login=:login";
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

    /**********verifier Identifiant**********/
    public function verifEmail($login)
    {
        try
        {
            $sql = "Select u.rowid from user as u WHERE u.email=:login";
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

    /*************Detail User**************/
    public function getUser($id)
    {
        try
        {
            $sql = "Select u.rowid, u.prenom, u.nom, u.login, u.telephone, u.email, u.date_creation,
                    u.user_creation, u.date_modification, u.user_modification, p.label as profil,
                    a.label as agence, u.etat
                from user as u
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid
                WHERE u.rowid =:id";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));

            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $PDOException)
        {
            return -1;
        }
    }

    /*******************Modifier User*****************/
    public function updateUtilisateur($nom, $prenom, $email, $telephone, $profil, $agence, $id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql="UPDATE user SET nom=:nom, prenom=:prenom, email=:mail, telephone=:tel, fk_profil=:profil, fk_agence=:agence, date_modification=:date_modification, user_modification=:user_modification WHERE rowid=:id";
            $user= $this->getConnexion()->prepare($sql);
            $res= $user->execute(array(
                "nom" =>$nom,
                "prenom"=>$prenom,
                "mail"=>$email,
                "tel"=>$telephone,
                "profil"=>$profil,
                "agence"=>$agence,
                "date_modification"=>$date_modification,
                "user_modification"=>$user_modification,
                "id" =>$id
            ));
            $this->closeConnexion();
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
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("pwd"=>sha1('NUMH'.$password), "conn"=>0, "date_modification"=>$date_modification, "user_modification"=>$user_modification, "id"=>$id));
            $this->closeConnexion();
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
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("etat"=>0, "id" =>$id, "date_modification"=>$date_modification, "user_modification"=>$user_modification));
            $this->closeConnexion();
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
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("etat"=>1, "id" =>$id, "date_modification"=>$date_modification, "user_modification"=>$user_modification));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception)
        {
            return -2;
        }
    }

    /**************Liste User*********/
    public function  allUserAgence($agence)
    {
        try
        {
            $sql = "Select u.rowid, u.nom, u.prenom
                from user as u
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid 
                WHERE a.rowid = :agence";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("agence" =>$agence));

            $a = $user->fetchAll(PDO::FETCH_OBJ);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
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
            $tabCol = ['u.nom', 'u.prenom', 'u.email', 'u.telephone', 'p.label', 'a.label', 'u.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
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
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /**********verifier Identifiant**********/
    public function verifPassword($password)
    {
        try
        {
            $password = sha1('NUMH'.$password);
            $sql = "Select u.rowid from user as u WHERE u.password=:password";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("password"=>$password));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }



    /***********Update Password************/
    public function updatePasswordUtilisateur($id, $password, $user_modification){

        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE user SET password=:pwd, connect=:conn, date_modification=:date_modification, user_modification=:user_modification WHERE rowid=:id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("pwd"=>sha1('NUMH'.$password), "conn"=>0, "date_modification"=>$date_modification, "user_modification"=>$user_modification, "id"=>$id));
            $this->closeConnexion();
            if($res) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }


    public function getDejaConnecter($login,$password){
        try
        {
            $sql = "SELECT deja_connecter from user WHERE login=:login AND password=:password";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array('login'=>$login, 'password'=>sha1('NUMH'.$password)));
            $a = $user->fetchObject();
            $this->closeConnexion();
            if($a->deja_connecter==1){
                return 1;
            }
            if($a->deja_connecter==0){
                return -1;
            }
        }
        catch (PDOException $exception)
        {
            return -99;
        }
    }

    public function dropDejaConnecter($login){
        try
        {
            $sql = "UPDATE user SET deja_connecter=0 WHERE login=:login";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array('login'=>$login));
            $this->closeConnexion();
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }

    public function getEtatUserConnecter($login,$password){
        try
        {
            //echo $sql2 = "SELECT etat from user WHERE login=$login AND password=".sha1('NUMH'.$password); die;
            $sql = "SELECT etat from user WHERE login=:login AND password=:password";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue(':login', $login);
            $user->bindValue(':password', sha1('NUMH'.$password));
            $user->execute();
            //$user->execute(array('login'=>$login, 'password'=>sha1('NUMH'.$password)));
            $a = $user->fetchObject();
            $this->closeConnexion();
            if($a->etat==1){
                return 1;
            }
            else{
                return -1;
            }
        }
        catch (PDOException $exception)
        {
            return -99;
        }
    }


}