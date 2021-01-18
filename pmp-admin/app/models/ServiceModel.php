<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class ServiceModel extends \app\core\BaseModel
{


    /********Ajouter Utilisateur*****
    public function insertTarif($service, $montant, $user_creation){
        try
        {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO service (label, frais, etat, user_creation, date_creation) 
            VALUES (:label, :frais, :etat, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "label" =>$service,
                "frais" =>$montant,
                "etat" => 1,
                "user_creation" =>$user_creation,
                "date_creation" =>$date_creation
            ));
            if($res==1)
            {
                $sql = "SELECT MAX(rowid) as id FROM service WHERE label = :label AND frais = :frais AND etat = :etat AND user_creation = :user_creation AND date_creation = :date_creation";
                $user = $this->getConnexion()->prepare($sql);
                $user->execute(array(
                    "label" =>$service,
                    "frais" =>$montant,
                    "etat" => 1,
                    "user_creation" =>$user_creation,
                    "date_creation" =>$date_creation
                ));
                $a = $user->fetchObject();
               return $a->id;
            }
            $this->closeConnexion();
        }
        catch(PDOException $Exception ){
            return -1;
        }
    }*/


    /********Ajouter Utilisateur******/
    public function insertTarif($service, $montant, $user_creation,$distributeur,$taux){

        try
        {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO service (label, frais, etat, user_creation, date_creation,distributeur,taux_distributeur) 
            VALUES (:label, :frais, :etat, :user_creation, :date_creation,:distributeur,:taux_distributeur)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "label" =>$service,
                "frais" =>$montant,
                "etat" => 1,
                "user_creation" =>$user_creation,
                "date_creation" =>$date_creation,
                "distributeur" =>$distributeur,
                "taux_distributeur" =>$taux
            ));


            if($res==1)
            {
                $sql = "SELECT MAX(rowid) as id FROM service WHERE label = :label AND frais = :frais AND etat = :etat AND user_creation = :user_creation AND date_creation = :date_creation";
                $user = $this->getConnexion()->prepare($sql);
                $user->execute(array(
                    "label" =>$service,
                    "frais" =>$montant,
                    "etat" => 1,
                    "user_creation" =>$user_creation,
                    "date_creation" =>$date_creation
                ));
                $a = $user->fetchObject();
                return $a->id;
            }
            $this->closeConnexion();
        }
        catch(PDOException $Exception ){
            return -1;
        }

    }


    /*************Detail Tarif**************/
    public function getTarif($id)
    {

        try
        {
            $sql = "Select *
                from service
                WHERE rowid =:id";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));

            $a = $user->fetchObject();

            $this->closeConnexion();
            return $a;
        }
        catch (\PDOException $PDOException)
        {
            //echo $PDOException;
            return -1;
        }
    }

    public function getMinMontant($id){
        try
        {
            $sql = "Select MAX(montant_fin) as montant
                from tarif_frais
                WHERE service =:id";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));
            $a = $user->fetchObject();
            if($user->rowCount() === 1 && (int)$a->montant > 0){
                $last = $a->montant + 1;
            }
            else{
                $last = 0;
            }



            $this->closeConnexion();
            return $last;
        }
        catch (\PDOException $PDOException)
        {
            //echo $PDOException;
            return -1;
        }
    }

    public function addPallier($mtmin,$mtmax , $montant, $rowid, $taux_tva){
        try
        {
            $mt_tva = ($montant * $taux_tva)/100;
            $ht = $montant - $mt_tva;
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO tarif_frais (montant_deb, montant_fin, ht, tva, valeur, service) 
            VALUES (:montant_deb, :montant_fin, :ht, :tva, :valeur, :service)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "montant_deb" =>$mtmin,
                "montant_fin" =>$mtmax,
                "ht" => $ht,
                "tva" =>$mt_tva,
                "valeur" =>$montant,
                "service" =>$rowid
            ));


            $this->closeConnexion();

            return $res;

        }
        catch(PDOException $Exception ){
            //echo $Exception; die;
            return -1;
        }
    }


    public function verifierPallier($mtmin, $rowid){
        try
        {

            $sql = "SELECT idfrais FROM tarif_frais WHERE montant_fin >= :mtmin AND service = :service";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array(
                "mtmin" =>$mtmin,
                "service" =>$rowid
            ));
            $this->closeConnexion();

            return $user->rowCount();

        }
        catch(PDOException $Exception ){
            return -1;
        }
    }

    public function deletePallier($idfrais, $service){
        try
        {

            $sql = "DELETE FROM tarif_frais WHERE idfrais = :mtmin AND service = :service";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array(
                "mtmin" =>$idfrais,
                "service" =>$service
            ));
            $res = $user->rowCount();
            $this->closeConnexion();

            return $res;

        }
        catch(PDOException $Exception ){
            return -1;
        }
    }


    /*************Detail Tarif**************/
    public function getPallierTarif($id)
    {
        try
        {
            $sql = "Select *
                from tarif_frais
                WHERE service =:id ORDER BY montant_deb ASC";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));

            $a = $user->fetchAll(PDO::FETCH_OBJ);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $PDOException)
        {
            return -1;
        }
    }

    /*******************Modifier User****************
    public function updateTarif($service, $montant, $id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql="UPDATE service SET label=:label, frais=:frais, date_modification=:date_modification, user_modification=:user_modification WHERE rowid=:id";
            $user= $this->getConnexion()->prepare($sql);
             $user->execute(array(
                "label" =>$service,
                "frais"=>$montant,
                "date_modification"=>$date_modification,
                "user_modification"=>$user_modification,
                "id" =>$id
            ));
            $res = $user->rowCount();
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $e )
        {
            return -2;
        }
    }*/


    /*******************Modifier User*****************/
    public function updateTarif($service, $montant, $id, $user_modification, $distributeur,$taux)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql="UPDATE service SET label=:label, frais=:frais, date_modification=:date_modification, user_modification=:user_modification,distributeur=:distributeur,taux_distributeur=:taux_distributeur WHERE rowid=:id";
            $user= $this->getConnexion()->prepare($sql);
            $user->execute(array(
                "label" =>$service,
                "frais"=>$montant,
                "date_modification"=>$date_modification,
                "user_modification"=>$user_modification,
                "distributeur"=>$distributeur,
                "taux_distributeur"=>$taux,
                "id" =>$id
            ));
            $res = $user->rowCount();
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $e )
        {
            return -2;
        }
    }

    /***********Desactivation User************/
    public function deleteTarif($id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE service SET etat= :etat, date_modification=:date_modification, user_modification=:user_modification WHERE rowid = :id";
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
    public function activerTarif($id, $user_modification)
    {
        $date_modification = date('Y-m-d H:i:s');
        try
        {
            $sql = "UPDATE service SET etat=:etat, date_modification=:date_modification, user_modification=:user_modification WHERE rowid = :id";
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
   /* public function  allTarifs($requestData = null)
    {
        try
        {
            $sql = "Select rowid, rowid as id, label, frais as frees, etat
                from service ";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( rowid LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat;
                $sql.=" OR label LIKE '%".$requestData."%' ";
                $sql.=" OR frees LIKE '%".$requestData."%' )";
            }
            $tabCol = ['rowid','label', 'frees', 'etat'];
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
    }*/


    /**************Liste User*********/
    public function  allTarifs($requestData = null)
    {
        try
        {
            $sql = "Select rowid, rowid as id, label, frais as frees, etat
                from service ";
            if($_REQUEST['search']['value']!="") {
                $etat = (strtolower($_REQUEST['search']['value']) == 'activer' ) ? 1 : ((strtolower($_REQUEST['search']['value']) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( rowid LIKE '%".$_REQUEST['search']['value']."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat;
                $sql.=" OR label LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR frais LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $tabCol = ['rowid','label', 'frees', 'etat'];
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


   /*
    public function allTarifsCount()
    {
        try {
            $sql = "SELECT COUNT(rowid) as total FROM service";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }*/

    public function allTarifsCount($requestData = null)
    {
        try {
            $sql = "SELECT COUNT(rowid) as total, rowid as id, label, frais, etat FROM service";
            if($_REQUEST['search']['value']!="") {
                $etat = (strtolower($_REQUEST['search']['value']) == 'activer' ) ? 1 : ((strtolower($_REQUEST['search']['value']) == 'desactiver') ? 0 : null);
                $sql.=" WHERE ( rowid LIKE '%".$_REQUEST['search']['value']."%' ";
                if($etat !== null) $sql.=" OR etat = ".$etat;
                $sql.=" OR label LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR frais LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }






    /**********verifier Identifiant**********/
    public function verifService($service)
    {
        try
        {

            $sql = "Select u.rowid from service as u WHERE LOWER(u.label)=:label";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("label"=>strtolower($service)));
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