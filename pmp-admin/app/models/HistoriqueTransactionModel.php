<?php


class HistoriqueTransactionModel extends \app\core\BaseModel
{

    /************Historique des virements*********/
    public function  historiqueTransaction($date1, $date2,$idCarte)
    {
        try
        {
            $sql = "SELECT * FROM transaction WHERE fk_carte =:idCarte AND DATE(date_transaction)>=:date1 AND DATE(date_transaction)<=:date2 ORDER BY date_transaction DESC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute( array("idCarte" => $idCarte,"date1" => $date1, "date2" => $date2));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $totrows = $user->rowCount();
            $this->closeConnexion();
            if($totrows > 0) return $a;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }



}