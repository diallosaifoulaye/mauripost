<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


require_once __DIR__.'/Utilisateur.class.php';

class ReportingModel extends \app\core\BaseModel
{
    
    /************************detailTransasction******************************/
    public function  detailTransasction($num_transac)
    {
        $sql = "SELECT DISTINCT t.rowid, t.num_transac, t.montant, t.commission, 
                t.statut, s.label, t.date_transaction, u.prenom, u.nom, a.label as nom_agence
                FROM transaction t, service s, user u, agence a
                WHERE t.statut = 1
                AND s.etat = 1 
                AND t.num_transac =:num_transac 
                
                AND t.fk_service = s.rowid 
                AND t.fkuser = u.rowid 
                AND t.fk_agence = a.rowid";
        try
        {
            $user = $this->getConnexion()->prepare($sql);
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
          $sql = "SELECT DISTINCT b.rowid, b.nom, b.prenom, b.cni, b.adresse, t.num_transac, t.fk_carte, t.montant, t.commission, 
          t.date_transaction, s.label, u.prenom as prenomuser, u.nom as nomuser, a.label as nomagence, c.telephone
          FROM  transaction t 
          INNER JOIN user u ON t.fkuser = u.rowid
          INNER JOIN agence a ON t.fk_agence = a.rowid
          INNER JOIN service s ON t.fk_service = s.rowid
          LEFT JOIN carte c ON t.fk_carte = c.rowid
          LEFT JOIN beneficiaire b ON b.rowid = c.beneficiaire_rowid 
          WHERE t.num_transac =:num_transac ";
          try
          {
                  $resultat = $this->getConnexion()->prepare($sql); 
                  $resultat->execute(array("num_transac" =>$num_transac));
                  $rs_resultat = $resultat->fetchObject();
                  return $rs_resultat;
          }
          catch(Exception $e)
          {
                echo $e; die;
          }
    }
    public function  typeProfil($id)
    {
        try
        {

            $sql = "Select p.type_profil from profil p 
                WHERE p.rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" => $id,));
            $a = $user->fetch();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }

    }
    /************************allAgence******************************/
    public function  allAgence(){
        $admin=$this->getSession()[0]->profil;
        $prof=$this->typeProfil($admin);
        $typepp=$prof["type_profil"];
        if($typepp== "3" || $typepp== "4"){
            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte, r.lib_region
                from agence as a
                LEFT OUTER JOIN region as r 
                ON a.province = r.idregion
                WHERE a.etat =1 AND a.rowid = ".$this->getSession()[0]->fk_agence."
                ORDER BY a.label ASC";
        }
        else {

            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte, r.lib_region
                from agence as a
                LEFT OUTER JOIN region as r
                ON a.province = r.idregion
                WHERE a.etat =1
                ORDER BY a.label ASC";
        }

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetchAll();
        return $a;

    }

    /************************allService******************************/
    public function  allService(){
        $sql = "SELECT service.rowid, service.label FROM service WHERE service.etat=:etat ORDER BY service.label";
        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch(Exception $e)
        {
                echo 'Error: -99'; die;
        }
        
    }

    /************************allServicePar******************************/
    public function allServicePar($service){
        $cond ="";
        if($service>0)
        {
            $cond.= " AND rowid=".$service."";
        }
        
        $sql = "SELECT rowid, label FROM service WHERE etat=:etat  ".$cond."  ORDER BY label";
        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll();
            return $a;
        }
        catch(Exception $e)
        {
                echo 'Error: -99'; die;
        }
        
    }

    /****************Detail des Rechargement Postecash par Date*************************/
    public function detailRechargementParDate($date_debut, $date_fin)
    {
         try
         {
              $sql = "SELECT DISTINCT t.num_transac, t.fk_carte, t.montant, t.commission, t.fk_agence, t.date_transaction, t.fk_service, a.label
              FROM transaction t JOIN agence a ON t.fk_agence = a.rowid
              WHERE DATE(t.date_transaction) >=:date_debut
              AND DATE(t.date_transaction) <=:date_fin
              AND t.fk_service = 12
              AND t.statut = 1 AND a.idtype_agence <> 3" ;
              
              $rs_carteActive = $this->getConnexion()->prepare($sql); 
              $rs_carteActive->bindParam("date_debut", $date_debut);
              $rs_carteActive->bindParam("date_fin", $date_fin);
              $rs_carteActive->execute();
              
              $row_rs_carteActive = $rs_carteActive->fetchAll();
              $totalRows = $rs_carteActive->rowCount();
              if($totalRows > 0 ) return $row_rs_carteActive;
              else return 0;
         }
         catch(PDOException $e)
         {
                return 0;
         }  
    }

    /****************Detail des Retraits Transfert Postecash par Date*************************/
    public function detailRetraitParDate($date_debut, $date_fin)
    {
             try
             {
                    $sql="SELECT t.num_transac, t.montant, t.date_tranfert, t.tel_sender, t.prenom_sender, t.nom_sender, t.prenom_receiver,
                     t.nom_receiver, t.tel_receiver, t.date_receiver, t.user_receiver, t.frais, a.label
                     FROM tranfert t JOIN user u ON t.user_receiver = u.rowid JOIN agence a ON a.rowid = u.fk_agence
                    WHERE DATE(t.date_receiver) >=:date_debut AND DATE(t.date_receiver) <=:date_fin AND t.statut = 1 AND t.fk_service IN (11,20)" ;
                    
                    $rs_carteActive = $this->getConnexion()->prepare($sql); 
                    $rs_carteActive->bindParam("date_debut", $date_debut);
                    $rs_carteActive->bindParam("date_fin", $date_fin);
                    
                    $rs_carteActive->execute();
                    $row_rs_carteActive = $rs_carteActive->fetchAll();
                    $totalRows = $rs_carteActive->rowCount();
                    if($totalRows > 0 ) return $row_rs_carteActive;
                    else return 0;
             }
             catch(PDOException $e)
             {
                    return 0;
             }  
    }

    /****************Detail des Retraits Titulaire Postecash par Date*************************/
    public function detailRetraitTitulaireParDate($date_debut, $date_fin)
    {
             try
             {
                 $sql ="SELECT t.num_transac, t.montant, t.date_transaction, a.label, t.fk_carte FROM transaction t JOIN agence a ON t.fk_agence = a.rowid JOIN carte c ON t.fk_carte = c.rowid
                        WHERE t.fk_service IN (10,17) AND t.statut = 1 AND DATE(t.date_transaction) >=:date_debut AND DATE(t.date_transaction) <=:date_fin " ;
                    
                  $rs_carteActive = $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  
                  $rs_carteActive->execute();
                  $row_rs_carteActive = $rs_carteActive->fetchAll();
                  $totalRows = $rs_carteActive->rowCount();
                  if($totalRows > 0 ) return $row_rs_carteActive;
                  else return 0;
             }
             catch(PDOException $e)
             {
                    return 0;
             }  
    }

    /****************Bordereau des rechargements*************************/
    public function bordereauRechargement($date_debut, $date_fin, $agence)
    {
        try
        {
            $sql = "SELECT DATE(t.date_transaction) as datet, COUNT(t.rowid) as nombre, SUM(t.montant) as montant, SUM(t.commission) as commission, t.fk_agence
                FROM transaction t WHERE t.fk_agence=:bureau AND DATE(t.date_transaction)>=:date_debut AND DATE(t.date_transaction)<=:date_fin AND t.fk_service=12 AND t.statut=1 GROUP BY datet" ;
            $rs = $this->getConnexion()->prepare($sql); 
            $rs->bindParam("bureau", $agence); 
            $rs->bindParam("date_debut", $date_debut);
            $rs->bindParam("date_fin", $date_fin);
            $rs->execute();
            $rs_resultat = $rs->fetchAll();
            $totalRows_rs_Resultat  = $rs->rowCount();
            if($totalRows_rs_Resultat > 0 ) return $rs_resultat;
            else return 0;
        }
        catch(PDOException $e)
        {
            return 0;
        }  
    }

    /************Date comprise entre deux dates************/
    public function getDatesBetween($start, $end)
    {
        if($start > $end)
        {
            return false;
        }
        $sdate = strtotime($start);
        $edate = strtotime($end);
        
        $dates = array();
        
        for($i = $sdate; $i <= $edate; $i += strtotime('+1 day', 0))
        {
            $dates[] = date('Y-m-d', $i);
        }
        
        return $dates;
    }
    
    /****************Nommbre des Retraits Tiers Postecash par Date et Bureau*************************/
    public function nombreRetraitTiers($datereceiver, $agence)
    {
             try
             {
                    $sql="SELECT COUNT(t.num_transac) as nombre, DATE(t.date_receiver) as  datereceiver 
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid  
                    WHERE DATE(t.date_receiver) =:datereceiver AND s.fk_agence =:agence AND t.statut = 1 
                    GROUP BY datereceiver" ;
                    
                    $rs_carteActive = $this->getConnexion()->prepare($sql); 
                    $rs_carteActive->bindParam("datereceiver", $datereceiver);
                    $rs_carteActive->bindParam("agence", $agence);
                    $rs_carteActive->execute();
                    $row_rs_carteActive = $rs_carteActive->fetchObject();
                    $totalRows = $rs_carteActive->rowCount();
                    if($totalRows > 0 ) return $row_rs_carteActive->nombre;
                    else return 0;
             }
             catch(PDOException $e)
             {
                    return -1;
             }  
    }
    
    /****************Montant des Retraits Tiers Postecash par Date et Bureau*************************/
    public function montantRetraitTiers($datereceiver, $agence)
    {
             try
             {
                    $sql="SELECT SUM(montant) as montant, DATE(t.date_receiver) as  datereceiver 
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid  
                    WHERE DATE(t.date_receiver) =:datereceiver AND s.fk_agence =:agence AND t.statut = 1 
                    GROUP BY datereceiver" ;
                    
                    $rs_carteActive = $this->getConnexion()->prepare($sql); 
                    $rs_carteActive->bindParam("datereceiver", $datereceiver);
                    $rs_carteActive->bindParam("agence", $agence);
                    $rs_carteActive->execute();
                    $row_rs_carteActive = $rs_carteActive->fetchObject();
                    $totalRows = $rs_carteActive->rowCount();
                    if($totalRows > 0 ) return $row_rs_carteActive->montant;
                    else return 0;
             }
             catch(PDOException $e)
             {
                    return -1;
             }  
    }
    
    /****************Nombre des Retraits Titulaire Postecash par Date et Bureau*************************/
    public function nombreRetraitTitulaire($date_transaction, $agence)
    {
        
             try
             {
                    $sql = "SELECT DATE(date_transaction) as datetransaction, COUNT(rowid) as nombre
                    FROM transaction  
                    WHERE DATE(date_transaction) =:date_transaction
                    AND fk_agence=:agence
                    AND fk_service = 10
                    AND statut = 1
                    GROUP BY datetransaction" ;
                    
                    $rs_carteActive = $this->getConnexion()->prepare($sql); 
                    $rs_carteActive->bindParam("date_transaction", $date_transaction);
                    $rs_carteActive->bindParam("agence", $agence);
                    $rs_carteActive->execute();
                    $row_rs_carteActive = $rs_carteActive->fetchObject();
                    $totalRows = $rs_carteActive->rowCount();
                    if($totalRows > 0 ) return $row_rs_carteActive->nombre;
                    else return 0;
             }
             catch(PDOException $e)
             {
                    return -1;
             }  
    }
    
    /****************Montant des Retraits Titulaire Postecash par Date et Bureau*************************/
    public function montantRetraitTitulaire($date_transaction, $agence)
    {
         try
         {
              $sql="SELECT DATE(date_transaction) as datetransaction, SUM(montant) as montant FROM transaction WHERE DATE(date_transaction) =:date_transaction AND fk_agence=:agence AND fk_service = 10 
              AND statut = 1 GROUP BY datetransaction" ;
              
              $rs_carteActive = $this->getConnexion()->prepare($sql); 
              $rs_carteActive->bindParam("date_transaction", $date_transaction);
              $rs_carteActive->bindParam("agence", $agence);
              $rs_carteActive->execute();
              $row_rs_carteActive = $rs_carteActive->fetchObject();
              $totalRows = $rs_carteActive->rowCount();
              if($totalRows > 0 ) return $row_rs_carteActive->montant;
              else return 0;
         }
         catch(PDOException $e)
         {
            return -1;
         }  
    }

    
    /****************Tableau de Bord Général*************************/
    public function nbretableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
       try
       {
              $sql='';
              if($service == 20)
              {
                  $sql="SELECT COUNT(t.num_transac) as nombre
                  FROM tranfert t JOIN user s ON t.user_receiver = s.rowid
                  WHERE DATE(t.date_receiver) >=:date_debut  
                  AND DATE(t.date_receiver) <=:date_fin 
                  AND s.fk_agence =:agence 
                  AND t.statut = 1 
                  GROUP BY s.fk_agence " ;
                  $rs_carteActive =  $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  $rs_carteActive->bindParam("agence", $agence);
              }
              else
              {
                  $sql="SELECT COUNT(t.num_transac) as nombre
                  FROM transaction t
                  WHERE DATE(t.date_transaction) >=:date_debut
                  AND DATE(t.date_transaction) <=:date_fin 
                  AND t.fk_service =:service
                  AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence" ;
                  
                  $rs_carteActive =  $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  $rs_carteActive->bindParam("service", $service);
                  $rs_carteActive->bindParam("fk_agence", $agence);
              }
              
              $rs_carteActive->execute();
              $row_rs_carteActive = $rs_carteActive->fetchObject();
              $totalRows = $rs_carteActive->rowCount();
              if($totalRows > 0 ) return $row_rs_carteActive->nombre;
              else return 0;
       }
       catch(PDOException $e)
       {
              return 0;
       }  
    }
    
    
    public function mttableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
         try
         {
                $sql='';
                if($service == 20)
                {
                    $sql="SELECT SUM(t.montant) as montant
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid
                    WHERE DATE(t.date_receiver) >=:date_debut  
                    AND DATE(t.date_receiver) <=:date_fin 
                    AND s.fk_agence =:agence 
                    AND t.statut = 1 
                   GROUP BY s.fk_agence " ;
                  $rs_carteActive =  $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  $rs_carteActive->bindParam("agence", $agence);
                }
                else
                {
                    $sql="SELECT SUM(t.montant) as montant
                    FROM transaction t
                    WHERE DATE(t.date_transaction) >=:date_debut
                    AND DATE(t.date_transaction) <=:date_fin 
                    AND t.fk_service =:service
                    AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence" ;
                  
                  $rs_carteActive =  $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  $rs_carteActive->bindParam("service", $service);
                  $rs_carteActive->bindParam("fk_agence", $agence);
                }
                
                $rs_carteActive->execute();
                $row_rs_carteActive = $rs_carteActive->fetchObject();
                $totalRows = $rs_carteActive->rowCount();
                if($totalRows > 0 ) return $row_rs_carteActive->montant;
                else return 0;
         }
         catch(PDOException $e)
         {
                return 0;
         }  
    }
    
    public function commissiontableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
         try
         {
              $sql='';
              if($service != 20)
              {
                  $sql="SELECT SUM(t.commission) as commission
                  FROM transaction t
                  WHERE DATE(t.date_transaction) >=:date_debut
                  AND DATE(t.date_transaction) <=:date_fin 
                  AND t.fk_service =:service
                  AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence" ;
                  
                  $rs_carteActive =  $this->getConnexion()->prepare($sql); 
                  $rs_carteActive->bindParam("date_debut", $date_debut);
                  $rs_carteActive->bindParam("date_fin", $date_fin);
                  $rs_carteActive->bindParam("service", $service);
                  $rs_carteActive->bindParam("fk_agence", $agence);
              }
              
              $rs_carteActive->execute();
              $row_rs_carteActive = $rs_carteActive->fetchObject();
              $totalRows = $rs_carteActive->rowCount();
              if($totalRows > 0 ) return $row_rs_carteActive->commission;
              else return 0;
         }
         catch(PDOException $e)
         {
                return 0;
         }  
    }
    
    
    /****************get Nom Service par rowid****************/
    public function getNomService($service)
    {
        $sql_agence = "SELECT label FROM service WHERE rowid =:service";        
        try
        {
            $stmt_agence = $this->getConnexion()->prepare($sql_agence);  
            $stmt_agence->execute(array("service"=>$service));
            $row_agence = $stmt_agence->fetchObject(); 
            $count_row = $stmt_agence->rowCount(); 
            if($count_row > 0) return $row_agence->label;
            else return '';
        }
        catch(PDOException $e)
        {
            return -1;
        }  
    }

    /*************************************************Nombre de Transaction Mensuel*************************************************/
  public function nbreTransactionMensuel()
  {
    $statut = 1;
    $sql = "SELECT count(*) AS nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.fk_service != 6
    GROUP BY mois , annee ASC   LIMIT 0, 6";
    $return = -1;
       try
       {
        $result = $this->getConnexion()->prepare($sql);
        $result->bindParam("statut",  $statut );
        $result->execute();
        $return = $result->fetchAll();
       }
       catch(PDOException $e)
       {
         echo $e;
         $return = 1001;  //Erreur Exception
       }
        //$this->pdo = NULL;  
     
    return $return;
  }

  /*************************************************Nombre de Transaction Mensuel*************************************************/
  public function nbreCarteMensuel()
  {
    $statut = 1;
    $sql = "SELECT count(*) AS nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND (transaction.fk_service = 18 OR transaction.fk_service = 19)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    //GROUP BY mois , annee ASC   LIMIT 0, 6";
    $return = -1;
    
       try{
        $result = $this->getConnexion()->prepare($sql);
        $result->bindParam("statut",  $statut );
        $result->execute();
        $return = $result->fetchAll();
       }catch(PDOException $e){
         $return = 1001;  //Erreur Exception
       }
       //$this->pdo = NULL;  
    
    return $return;
  }

  /*************************************************paiement  Mensuel*************************************************************/
  public function montantPaimentMensuel()
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (10,17)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    //GROUP BY  mois, annee ASC   LIMIT 0, 6";
    $returner = -1;
    
       try
       {
          $resultat = $this->getConnexion()->prepare($sql);
          $resultat->bindParam("statut",  $statut );
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
  public function transfertCarteAcarteMensuel()
  {
    $statut = 1;
    $service = 5;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service =:service
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee ";
    //GROUP BY  mois, annee ASC   LIMIT 0, 6";
    $returner = -1;
     try
     {
        $resultat = $this->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->bindParam("service",  $service );
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
  public function transfertCartetocashMensuel()
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (20, 25)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    $returner = -1;
     try
     {
        $resultat = $this->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
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
  public function venteCarteMensuel()
  {
    $statut = 1;
    $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, count(*) as nbre, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
    FROM transaction
    WHERE transaction.statut=:statut
    AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
    AND transaction.fk_service IN (18, 19)
    AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
               ORDER BY annee";
    $returner = -1;
     try
     {
        $resultat = $this->getConnexion()->prepare($sql);
        $resultat->bindParam("statut",  $statut );
        $resultat->execute();
        $returner = $resultat->fetchAll();
     }
     catch(PDOException $e)
     {
       $returner = 1001;  //Erreur Exception
     } 

    return $returner;
  }

  /***************************************************Transfert carte à carte  Mensuel********************************************/
  public function serviceRecharge()
  {
    $statut =1;
    $sql = "SELECT service.rowid, service.label FROM service 
    WHERE service.etat =:statut
    AND service.rowid IN (12,4,22)";
    $return = -1;
     try
     {
      $result = $this->getConnexion()->prepare($sql);
      $result->bindParam(":statut", $statut);
      $result->execute();
      $return = $result->fetchAll();
      
     }
     catch(PDOException $e)
     {
       $return = 1001;  //Erreur Exception
     }  
         
    return $return;
  }

  /***************************************************Type agence********************************************/
  public function getTypeAgence1()
  {
    try
    {
      $sql = "SELECT idtype_agence, libelle FROM type_agence  WHERE etat=:etat ORDER BY libelle ASC";
      $user = $this->getConnexion()->prepare($sql);
      $user->execute(array("etat" => 1));
      $a = $user->fetchAll();
      return $a;
    }
    catch(PDOEXception $e)
    {
        return 1001;  //Erreur Exception
    }
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
            $next.="INNER JOIN region ON agence.province = region.idregion";

        }
        else{
            $where.=" AND transaction.fkuser=".$user;
        }

        if($numserie != ''){
            $where="  AND carte.telephone ='".$numserie."'";
        }

            $sql = "SELECT DISTINCT (transaction.rowid), transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction 
            LEFT JOIN carte ON transaction.fk_carte = carte.rowid
            INNER JOIN service ON transaction.fk_service = service.rowid
            INNER JOIN user ON transaction.fkuser = user.rowid
            INNER JOIN agence ON transaction.fk_agence = agence.rowid
            ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            ".$where;


        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            return  $user->fetchAll(PDO::FETCH_OBJ);
        }
        catch(Exception $e)
        {
            return -1;
        }
    }

    public function transactionByProduitDate($date1, $date2, $produit, $user, $agence, $type_profil, $agency, $admin){

        $next = '';
        $where = '';
        try{

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
                $next.="INNER JOIN region ON agence.province = region.idregion";
            }
            else{
                $where.=" AND transaction.fkuser=".$user;
            }

            if($agency > 0){
                $where.=" AND agence.rowid = :agence";
            }


            $sql = "SELECT DISTINCT (transaction.rowid), transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction 
            LEFT JOIN carte ON transaction.fk_carte = carte.rowid
            INNER JOIN service ON transaction.fk_service = service.rowid
            INNER JOIN user ON transaction.fkuser = user.rowid
            INNER JOIN agence ON transaction.fk_agence = agence.rowid
            ".$next."
            WHERE transaction.statut = 1
             AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
           
            AND DATE(transaction.date_transaction) >=:date1
            AND DATE(transaction.date_transaction) <=:date2
            AND transaction.fk_service = :produit
            ".$where;


            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1);
            $user->bindParam("date2",  $date2);
            $user->bindParam("produit",  $produit);
            if($agency > 0) $user->bindParam("agence", $agency);
            $user->execute();
            return $user->fetchAll(PDO::FETCH_OBJ);
        }
        catch (PDOException $e){
            echo $e;
            return -1;
        }
    }

    /*************************************************** commission Par Produit ********************************************/


    public function commissionParProduits($produit, $datedeb, $datefin)
    {
        try
        {
            $and ='';
            if($produit>0) $and = " AND t.idservices = :produit";

            $sql = "SELECT count(s.rowid) as nbre, s.label, SUM(t.montant_commission) as somme 
                    FROM transaction_commission t
                    INNER JOIN service s ON s.rowid = t.idservices 
                    WHERE s.etat =1 
                    AND DATE(t.date_tansaction) >=:date1
                    AND DATE(t.date_tansaction) <=:date2 ".$and."
                    GROUP BY t.idservices";


            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $datedeb);
            $user->bindParam("date2",  $datefin);

            if($produit>0) {
                $user->bindParam("produit",  $produit);
            }

            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch(PDOEXception $e)
        {
            return $e;  //Erreur Exception
        }
    }



}