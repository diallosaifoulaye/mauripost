<?php
/**
 * Created by PhpStorm.
 * User: Developpeur
 * Date: 01/06/2018
 * Time: 15:20
 */


class DashModel extends \app\core\BaseModelDao
{

    public function  allOffreCount()
    {
        $this->table = "t_tontine t";
        $this->table = "t_offres";
        return count($this->__select());
    }


    /*************************************************Nombre de Transaction Mensuel*************************************************/
    public function nbreTransactionMensuel($date1='',$date2='',$client='', $collecteur='')
    {
      
        $statut = 1;
        $sql = "SELECT count(*) AS nbre, SUM(t_transaction.montant) AS montant, MONTH(t_transaction.date_transaction) AS mois, YEAR(t_transaction.date_transaction) AS annee
    FROM t_transaction
    WHERE t_transaction.statut=:statut ";
        if($date1 != ''){
            $sql.="AND DATE(t_transaction.date_transaction) >=:date1 ";
        }

        if($date2 != ''){
            $sql.="AND DATE(t_transaction.date_transaction) <=:date2 ";
        }
        if($client != ''){
            $sql.="AND fk_client=:client ";
        }

        if($collecteur != ''){
            $sql.="AND fk_collecteur=:collecteur ";
        }

    $sql.="GROUP BY mois , annee ASC ";
      //  echo $sql; exit ;
        $return = -1;
        try
        {
            $result = $this->getConnexion()->prepare($sql);
            $result->bindParam("statut",  $statut );
            if($date1 != ''){
                $result->bindParam("date1",  $date1);
            }
            if($date2 != ''){
                $result->bindParam("date2",  $date2);
            }

            if($client != ''){
                $result->bindParam("client",  $client);
            }

            if($collecteur != ''){
                $result->bindParam("collecteur",  $collecteur);
            }

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



}