<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */

namespace app\core;

date_default_timezone_set('Africa/Nouakchott');

class UtilsReporting
{

    /**************Abigail********************************************/

    private $connexion;
    private $utils;

    public function __construct()
    {
        $this->connexion = new Connexion();
        $this->utils = new Utils();
    }

    /***********get Nbre, CA, Com transaction***************/
    function gettransacnumber($usertransac)
    {
        try
        {
             $sql = "SELECT COUNT(transaction.rowid) as nbtransac, SUM(transaction.montant) AS ca, SUM(transaction.commission) AS commission, 
            MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee, (SUM(transaction.commission)*service.taux_distributeur)/100 as macommission
            FROM transaction INNER JOIN service ON transaction.fk_service = service.rowid
            WHERE transaction.statut=1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fkuser=:code
            AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
                       GROUP BY annee
                       ORDER BY annee";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code"=>$usertransac));
            $a = $user->fetchObject();
            $totalrows = $user->rowCount();
            if($totalrows > 0) return $a;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

    /***********get Nbre, CA, Com transaction***************/
    function gettransacnumber1($usertransac)
    {
        try
        {
            $sql = "SELECT COUNT(rowid) as nbtransac, SUM(transaction.montant) AS ca, SUM(transaction.commission) AS commission, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee, SUM(transaction.commission)*0.65 as macommission
            FROM transaction
            WHERE transaction.statut=1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_agence=:code
            AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
                       GROUP BY annee
                       ORDER BY annee";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code"=>$usertransac));
            $a = $user->fetchObject();
            $totalrows = $user->rowCount();
            if($totalrows > 0) return $a;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

/*************************************************Nombre de Transaction Mensuel*************************************************/
  public function nbreTransactionMensuel($usertransac)
  {
    $statut = 1;
    $sql = "SELECT count(*) AS nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.fkuser=:code
    AND transaction.fk_service != 6
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
    GROUP BY mois , annee ASC";
    $return = -1;
       try
       {
        $result = $this->connexion->getConnexion()->prepare($sql);
        $result->bindParam("statut",  $statut );
        $result->bindParam("code",  $usertransac );
        $result->execute();
        $return = $result->fetchAll();
       }
       catch(PDOException $e)
       {
         echo $e;
         $return = 1001;  //Erreur Exception
       }
        return $return;
  }

/*************************************************Nombre de Transaction Mensuel*************************************************/
  public function nbreTransactionMensuel1($usertransac)
  {
    $statut = 1;
    $sql = "SELECT count(*) AS nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.fk_agence=:code
    AND transaction.fk_service != 6
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
    GROUP BY mois , annee ASC";
    $return = -1;
       try
       {
        $result = $this->connexion->getConnexion()->prepare($sql);
        $result->bindParam("statut",  $statut );
        $result->bindParam("code",  $usertransac );
        $result->execute();
        $return = $result->fetchAll();
       }
       catch(PDOException $e)
       {
         echo $e;
         $return = 1001;  //Erreur Exception
       }
        return $return;
  }

  /***************************************************vente de carte  Mensuel********************************************/
  public function venteCarteMensuel($usertransac)
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, count(*) as nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.fkuser=:code
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (18, 19)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    $returner = -1;
     try
     {
        $resultat = $this->connexion->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->bindParam("code",  $usertransac );
        $resultat->execute();
        $returner = $resultat->fetchAll();
     }
     catch(PDOException $e)
     {
       $returner = 1001;  //Erreur Exception
     }
     
    return $returner;
  }

  /***************************************************vente de carte  Mensuel********************************************/
  public function venteCarteMensuel1($usertransac)
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, count(*) as nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.fk_agence=:code
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (18, 19)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    $returner = -1;
     try
     {
        $resultat = $this->connexion->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->bindParam("code",  $usertransac );
        $resultat->execute();
        $returner = $resultat->fetchAll();
     }
     catch(PDOException $e)
     {
       $returner = 1001;  //Erreur Exception
     }

    return $returner;
  }

  /************************allServicePar******************************/
    public function allServicePar($service){
        $cond ="";
        if($service>0)
        {
            $cond.= " AND rowid=".$service."";
        }
        
        $sql = "SELECT rowid, label FROM service WHERE etat=:etat AND distributeur=1  ".$cond."  ORDER BY label";
        try
        {
            $user =  $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll();
            return $a;
        }
        catch(Exception $e)
        {
                echo 'Error: -99'; die;
        }
        
    }

/***************************************************Transfert carte à carte  Mensuel********************************************/
  public function serviceRecharge()
  {
    $statut =1;
    $sql = "SELECT service.rowid, service.label FROM service 
    WHERE service.etat =:statut
    AND service.rowid IN (12,17)";
    $return = -1;
     try
     {
      $result = $this->connexion->getConnexion()->prepare($sql);
      $result->bindParam(":statut", $statut);
      $result->execute();
      $return = $result->fetchAll();
      
     }
     catch(PDOException $e)
     {
       echo $e;
       $return = 1001;  //Erreur Exception
     }  
         
    return $return;
  }

  /***************************************************Transfert carte à carte  Mensuel********************************************/
  public function serviceRecharge1()
  {
    $sql = "SELECT DISTINCT rowid, label 
    FROM service 
    WHERE etat = 1
    AND rowid IN (29,63,51,56,27)";
    $return = -1;
     try
     {
      $result = $this->connexion->getConnexion()->prepare($sql);
      $result->execute();
      $return = $result->fetchAll(\PDO::FETCH_ASSOC);
      
     }
     catch(PDOException $e)
     {
       echo $e;
       $return = 1001;  //Erreur Exception
     }  
         
    return $return;
  }

  /***************************************************Transfert carte à carte  Mensuel********************************************/
  public function transfertCartetocashMensuel($usertransac)
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN ('".ID_SERVICE_TRANSFERT."', '".ID_SERVICE_TRANSFERT_CASHTOCASH."')
    AND transaction.fkuser=:code
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    //GROUP BY  mois, annee ASC   LIMIT 0, 6";
    $returner = -1;
     try
     {
        $resultat = $this->connexion->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->bindParam("code",  $usertransac );
        $resultat->execute();
        $returner = $resultat->fetchAll();
     }
     catch(PDOException $e)
     {
       $returner = 1001;  //Erreur Exception
     } 
     //$this->pdo = NULL;   
     
    return $returner;
  }

  /***************************************************Transfert carte à carte  Mensuel********************************************/
  public function transfertCartetocashMensuel1($usertransac)
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (20, 25)
    AND transaction.fk_agence=:code
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    
    $returner = -1;
     try
     {
        $resultat = $this->connexion->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->bindParam("code",  $usertransac );
        $resultat->execute();
        $returner = $resultat->fetchAll();
     }
     catch(PDOException $e)
     {
       $returner = 1001;  //Erreur Exception
     }

    return $returner;
  }


    public function transactionByDateAndOrUmSerie($date1, $date2, $numserie, $user, $agence, $type_profil, $admin){
        $next = '';
        $where = '';

        if($admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$user;
        }

        if($numserie != ''){
            $where="  AND carte.telephone ='".$numserie."'";
        }

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction, service, user, agence, carte ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.fk_carte = carte.rowid
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND user.fk_agence = agence.rowid ".$where;
        try
        {
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            return  $user->fetchAll();
        }
        catch(Exception $e)
        {
            return -1;
        }
    }


/************************detailTransasction******************************/
    public function  detailTransasction($num_transac){
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, carte.numero, transaction.montant, transaction.commission, 
                transaction.statut, service.label, transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
                FROM transaction, carte, service, user, agence
                WHERE transaction.statut = 1
                AND service.etat = 1 
                AND transaction.num_transac =:num_transac 
                AND transaction.fk_carte = carte.rowid
                AND transaction.fk_service = service.rowid 
                AND transaction.fkuser = user.rowid 
                AND user.fk_agence = agence.rowid";
        try
        {
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("num_transac" =>$num_transac));
            $a = $user->fetchObject();
            return $a;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }

    }


    /************************recu duplicata******************************/
    public function  dupliquerRecu($num_transac)
    {
        $sql = "SELECT DISTINCT beneficiaire.rowid, beneficiaire.nom, beneficiaire.prenom, beneficiaire.cni, beneficiaire.adresse, carte.telephone, 
          carte.statut as cartestatut, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, 
          transaction.date_transaction, service.label, user.prenom as prenomuser, user.nom as nomuser, agence.label as nomagence 
          FROM carte, beneficiaire, transaction, user, agence, service
          WHERE transaction.num_transac =:num_transac 
          AND beneficiaire.rowid = carte.beneficiaire_rowid 
          AND transaction.fk_carte = carte.rowid
          AND transaction.fk_service = service.rowid
          AND transaction.fkuser = user.rowid
          AND user.fk_agence = agence.rowid";
        try
        {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->execute(array("num_transac" =>$num_transac));
            $rs_resultat = $resultat->fetchObject();
            return $rs_resultat;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }
    }


    /************************allService******************************/
    public function  allService(){
        $sql = "SELECT service.rowid, service.label FROM service WHERE distributeur=1 and service.etat=:etat ORDER BY service.label";
        try
        {
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll();
            return $a;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }

    }

    /************************Taux de Distribution par Service******************************/


    public function  getTauxDistributeurService($distributeur)
    {
        try
        {

            $sql = "SELECT s.label, t.taux, t.fk_service, t.fk_distributeur
                    FROM taux_commission_distributeur t
                    INNER JOIN service s ON t.fk_service = s.rowid
                    WHERE t.fk_distributeur =". $distributeur."
                    AND s.etat = 1
                    AND s.distributeur = 1";

            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(\PDO::FETCH_ASSOC);
            $this->connexion->closeConnexion();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }
    }

    public function  getTauxDistributeurParService($distributeur, $service)
    {
        try
        {
            $sql = "SELECT t.taux FROM taux_commission_distributeur t WHERE t.fk_distributeur =". $distributeur." AND t.fk_service = ".$service;
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
            return $a->taux;
        }
        catch(PDOException $e)
        {
            return -1;
        }
    }




}