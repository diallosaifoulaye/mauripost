<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


class VirementModel extends \app\core\BaseModel
{

    /***********Ajouter Virement************/
    public function addVirement($montant, $user_creation, $langue){
        try
        {
            $date_virement = date('Y-m-d H:i:s');
            $sql = "INSERT INTO virement (datevirement, statut,  user_crea, montant) VALUES (:datevirement, :statut, :user_crea, :montant)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("datevirement" => $date_virement, "statut" => 0, "user_crea" => $user_creation, "montant" => $montant));
            $this->closeConnexion();
            if($res==1)
            {
                $this->envoiVirement($montant, $user_creation, $langue);
                return 1;
            }
            else return -1;
        }
        catch(PDOException $e)
        {
            return -2;
        }

    }

    /*****************Envoi virement***************/
    public function envoiVirement($montant,$use_creation, $langue){
        try
        {
            $sql = "SELECT message, destinataire, prenom, nom FROM mail_virement WHERE type = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $rs_result = $user->fetchObject();
            $totrows = $user->rowCount();
            if($totrows > 0)
            {
                $sql2 = "SELECT prenom, nom FROM user WHERE user.rowid =:rowid";
                $result2 = $this->getConnexion()->prepare($sql2);
                $result2->execute(array("rowid"=>$use_creation));
                $rs_result2 = $result2->fetchObject();
                $res = $this->alerte_virement($rs_result->destinataire, $rs_result->nom, $rs_result->prenom, $montant, $rs_result->message, $rs_result2->prenom.' '.$rs_result2->nom, $langue);
            }
            $this->closeConnexion();
            return $res;
        }
        catch(PDOException $e)
        {
            return -2;
        }
    }

    /*************Mail alerte virement************/
    public function alerte_virement($destinataire, $prenom,  $nom, $montant,$messages, $par, $langue) {

        $sujet = $langue['nouveau_virement'];
        $entete = '';
        $vers_nom = $prenom.' '.$nom;
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> ".$langue['cher']." ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$messages."<br />";

        $message.= "<b>".$langue['montant_virement']." : </b>".$montant." ".$langue['currency']."<br />";
        $message.= "<b>".$langue['effectuer_par']." : </b>".$par."";

        $message.= "</p></td>";
        $message.= "</tr>";

        $message.= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:PosteCash <no-reply@postecash.bj>\r\n";

        return mail($vers_mail, $sujet, $message, $entete);
    }

    /************Historique des virements*********/
    public function  histoVirement($date1, $date2)
    {
        try
        {
            $sql = "SELECT v.rowid, v.montant, v.statut, v.datevirement FROM virement as v WHERE DATE(v.datevirement)>=:date1 AND DATE(v.datevirement)<=:date2 ORDER BY v.rowid DESC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute( array("date1" => $date1, "date2" => $date2));
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

    /***********Valider virement**********/
    public function validerVirement($id, $uservalidation){
        try
        {
            $sql = "SELECT virement.rowid, virement.montant FROM virement WHERE virement.rowid = :rowid";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("rowid" => $id));
            $virement = $user->fetchObject();
            $retour = 0;
            if($user->rowCount() > 0)
            {
                $solde = $virement->montant;
                $sql = "UPDATE comptes SET solde = solde+:soldes WHERE rowid =:rowid";
                $user2 = $this->getConnexion()->prepare($sql);
                $res2 = $user2->execute(array("soldes" =>$solde, "rowid" =>1));

                if($res2)
                {
                    $datevalidation = date('Y-m-d H:i:s');
                    $sql = "UPDATE virement SET statut=:statut, datevalidation=:datev, uservalidation=:userv WHERE rowid=:rowid";
                    $user3 = $this->getConnexion()->prepare($sql);
                    $res3 = $user3->execute(array("statut"=>1, "datev"=>$datevalidation, "userv"=>$uservalidation, "rowid"=>$id));
                    if($res3==1)
                    {
                        $this->envoiValidationVirement($solde, $uservalidation);
                        $retour = 1;
                    }
                    else $retour= -1;
                }
            }
            return $retour;

        }
        catch(PDOException $e)
        {
           return -2;
        }
    }



    /***********Mail validation************/
    public function validation_virement($destinataire, $prenom,  $nom, $montant, $messages, $par) {

        $entete = '';
        $sujet = "Validation virement"; //Sujet du mail

        $vers_nom = $prenom.' '.$nom;
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Cher ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$messages."<br />";
        $message.= "<b>Montant vir&eacute; : </b>".$montant." F CFA<br />";
        $message.= "<b>Effectu&eacute; par : </b>".$par."";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "</table>";

        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:PosteCash <no-reply@postecash.bj>\r\n";

        return mail($vers_mail, $sujet, $message, $entete);
    }

    /***********Mail validation************/
    public function envoiValidationVirement($montant, $user_creation)
    {
        try
        {
            $res = 0;
            $sql = "SELECT message, destinataire, prenom, nom FROM mail_virement WHERE type =2";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $rs_result = $user->fetchObject();
            if($user->rowCount() > 0)
            {
                $query_rs_Result2 = "SELECT prenom, nom FROM user WHERE user.rowid =:rowid";
                $result2 = $this->getConnexion()->prepare($query_rs_Result2);
                $result2->execute(array("rowid" =>$user_creation));
                $rs_result2 = $result2->fetchObject();
                $res = $this->validation_virement($rs_result->destinataire, $rs_result->nom,  $rs_result->prenom, $montant, $rs_result->message, $rs_result2->prenom.' '.$rs_result2->nom);
            }

            return $res;
        }
        catch(PDOException $e)
        {
            return -2;
        }
    }

    /*****Solde Compte Poste******/
    public function  soldeComptePoste()
    {
        try
        {
            $sql = "SELECT  comptes.solde FROM comptes";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a->solde;
        }
        catch(PDOException $e)
        {
            return -1;
        }
    }


}