<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */

namespace app\core;

date_default_timezone_set('Indian/Antananarivo');

class Utils
{
    /**************Abigail********************************************/

    private $connexion;


    public function __construct()
    {
        $this->connexion = new Connexion();
    }

    /***********************************************truncate_carte*******************************************/
    public function truncate_carte($carte)
    {
        $nb_caractere = strlen($carte);
        $premier = "";
        if ($nb_caractere > 0) {

            for ($i = 0; $i < $nb_caractere - 4; $i++) {
                $premier .= "*";
            }
            $truncate = $premier . substr($carte, $nb_caractere - 4);
            return $truncate;
        } else {
            return -1;
        }
    }

    public function date_fr2($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janvier";
                    break;
                case "02":
                    $mm = "Fevrier";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juillet";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Septembre";
                    break;
                case "10":
                    $mm = "Octobre";
                    break;
                case "11":
                    $mm = "Novembre";
                    break;
                case "12":
                    $mm = "Décembre";
                    break;
            }
            ////////////////
            $date_fr = $jj . " " . $mm . " " . $aa;
        }
        return $date_fr;
    }

    /*****************rechercher Code rechargement*************/
    public function rechercherCoderechargement($code, $fkagence)
    {
        try
        {
            $sql = "SELECT id from code_rechargement WHERE code =:code AND fk_agence =:num AND statut =:statut";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code" =>$code, "num" => $fkagence, "statut" => 0));
            $a = $user->fetchObject();
            $rowtot = $user->rowCount();
            if($rowtot > 0) return $a->id;
            else return -1;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }


    /**************Fichier log txt********************************************/
    public function getInfoUserLog($user)
    {
        $sql = "SELECT CONCAT(user.prenom,' ',user.nom) as user_name,agence.label, agence.rowid
				FROM user, agence
				WHERE user.fk_agence = agence.rowid AND user.rowid =:user  ";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->bindParam("user", $user);
            $stmt->execute();
            $beneficiare = $stmt->fetchObject();
            $this->connexion->closeConnexion();
            return 'user-'.$user.$beneficiare->user_name . ":agence-" .$beneficiare->rowid.$beneficiare->label;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /********************crediter carte Parametrable****************/
    public function crediter_carteParametrable($connexion, $montant, $idcarte)
    {
        try
        {
            $req = $connexion->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=:idcarte");
            $Result1 = $req->execute(array("soldes" => $montant, "idcarte" => $idcarte));
            if($Result1 > 0) return 1;
            else return 0;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }



    /*public function crediter_carteParametrable($conn, $montant, $idcarte)
    {
        try
        {
            $req = $conn->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=:idcarte");
            $Result1 = $req->execute(array("soldes" => $montant, "idcarte" => $idcarte));
            if($Result1 > 0) return 1;
            else return 0;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }*/

    /*********change Statut Code rechargement**********/
    public function changeStatutCoderechargement($id)
    {
        try
        {
            $sql = "UPDATE code_rechargement SET statut = :statut WHERE id = :id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("statut"=>1, "id"=>$id));
            return $res;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }

    /*********Recuperer mail receveur**********/
    function recup_mail($connecter)
    {
        try
        {
            $query_rs_coderetrait = "SELECT email FROM agence WHERE rowid =:mail";
            $resultatcode = $this->connexion->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" =>$connecter));
            $totalRows_rs_resultatcode  = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if($totalRows_rs_resultatcode > 0) return $rs_resultatcode->email;
            else return -1;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }

    /*********Recuperer mail receveur**********/
    function recup_tel($connecter)
    {
        try {
            $query_rs_coderetrait = "SELECT tel FROM agence WHERE rowid =:mail";
            $resultatcode = $this->connexion->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" => $connecter));
            $totalRows_rs_resultatcode = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if ($totalRows_rs_resultatcode > 0) return $rs_resultatcode->tel;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    function recup_mailBenef($telephone)
    {
        try {
            $query_rs_coderetrait = "SELECT b.email FROM beneficiaire b INNER JOIN carte c ON c.beneficiaire_rowid = b.rowid WHERE c.telephone = :mail";
            $resultatcode = $this->connexion->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" => $telephone));

            $totalRows_rs_resultatcode = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if ($totalRows_rs_resultatcode > 0) return $rs_resultatcode->email;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    public function nombre_format($nombre)
    {
        return @number_format($nombre, 0, ' ', ' ');
    }

    /****************************************************************************************************/



    /*************generate Code Rechargement**************/
    public function generateCodeRechargement($fkagence){
        $found = 0;
        do
        {
            $code = $this->random(10);
            $etat = $this->verifyCoderechargement($code);
            if($etat == 1){
                $found = 1;
                $this->insertCoderechargement($code, $fkagence);
            }
        }
        while($found == 0);
        return $code;
    }

    public function random($car)
    {
        $string = "";
        $chaine = "1234567890";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < $car; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }

    /*************verify Code rechargement**************/
    public function verifyCoderechargement($code){

        try
        {
            $sql = "SELECT id from code_rechargement WHERE code = :code";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code" =>$code));
            $a = $user->rowCount();
            if($a > 0) return 0;
            else return 1;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }

    /****************Insert Code Rechargement**************/
    public function insertCoderechargement($code, $fkagence)
    {
        try
        {
            $date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO code_rechargement(code, fk_agence, statut, date) VALUES (:code, :num_carte, :statut, :dat)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("code"=>$code, "num_carte"=>$fkagence, "statut"=>0, "dat"=>$date));
            if($res == 1) return 1;
            else return -1;
        }
        catch(\PDOException $e)
        {
            return -2;
        }
    }

    public function envoiCodeRechargement($dest, $nom, $code, $montant, $tel_client, $langue)
    {
        $de_nom = $langue['plateforme_postecash'];
        $entete ='';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet =  $langue['rechargement_carte_particulier'];
        $message = "<div align='left'>" .$de_nom. "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>".$sujet."</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_1']."</div></br>";
        $message .= "<div align='left'>".$langue['envoi_mail_code_rechargement_2']."</div></br>";
        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_3']." </b>: ".$code."</div></br>";

        $message .= "<div align='left'><b>".$langue['amount']." </b>: ".$montant."</div></br>";
        $message .= "<div align='left'><b>".$langue['tel']." </b>: ".$tel_client."</div></br>";


        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_4']." : </b>".date('d-m-Y H:i:s')."</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:POSTECASH<no-reply@postecash.mr>\r\n";
        $envoi = mail("", $sujet, $message, $entete);
        if($envoi) return 1;
        else return 0;
    }

     public function envoiCodeRetrait($dest, $nom, $code,$mnt)
    {
        $de_nom = 'POSTECASH';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Retrait espece ";
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>Cashout : </b> : " . $mnt . "</div></br>";
        $message .= "<div align='left'><b>Veuillez donner ce code:  </b>: " . $code . " à l'agent pour valider la transaction MAURIPOST.</div></br>";
        $message .= "<div align='left'><b>  Si vous n'en êtes pas à l'origine appelez le service client</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:POSTECASH <no-reply@postecash.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }

   /* public function envoiCodeRetrait($dest, $nom, $code,$mnt)
    {
        $de_nom = 'Paositra Malagasy';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Retrait Espece";
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>Pour valider votre retrait espece</div></br>";
        $message .= "<div align='left'>Merci de valider votre transaction avec le code ci-dessous</div></br>";
        $message .= "<div align='left'><b>Code de validation </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>Montant </b> : " . $mnt . "</div></br>";
        $message .= "<div align='left'><b>Date et heure: </b>" . date('d-m-Y H:i:s') . "</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:Paositra <no-reply@paositra.mg>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }*/

    public function envoiparametre($destinataire, $email, $login, $password)
    {

        $sujet = "Création compte PMP"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre compte d'accès à la plateforme POSTECASH vient d'être créé.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "Identifiant :" . $login . "<br />";
        $message .= "Mot de passe :" . $password . "<br />";
        //$message .= "<a href='http://pmp-burkina.com/' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<a href='" . BASE_URL2 . "' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.<br /><br />Equipe MAURIPOST</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:POSTECASH <no-reply@postecash.mr>\r";
        mail($vers_mail, $sujet, $message, $entete);


    }


    public function envoiparametreDistributeur($destinataire, $email, $login, $password)
    {

        $sujet = "Création compte distributeur"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre compte d'accès à la plateforme distributeur POSTECASH vient d'être créé.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "Identifiant :" . $login . "<br />";
        $message .= "Mot de passe :" . $password . "<br />";
        $message .= "<a href='" . BASE_URL2 . "' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.<br /><br />Equipe MAURIPOST</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:POSTECASH <no-reply@postecash.mr>\r";
        return mail($vers_mail, $sujet, $message, $entete);


    }


    /**************Fichier log txt*******************************************
    public function ecrire_journal($errtxt)
    {
        if (!file_exists(__DIR__."/../logs/" . date('Y') . "-" . date('W'))) {
            mkdir(__DIR__."/../logs/" . date('Y') . "-" . date('W'), 0777);
        }

        $fp = fopen(__DIR__."/../logs/" . date('Y') . "-" . date('W') . "/" . date("d_m_Y") . "" . ".txt", 'a+'); // ouvrir le fichier ou le créer
        fseek($fp, SEEK_END); // poser le point de lecture à la fin du fichier
        $nouvel_ligne = $errtxt . "\r\n"; // ajouter un retour à la ligne au fichier
        fputs($fp, $nouvel_ligne); // ecrire ce texte
        fclose($fp); //fermer le fichier
    }*/

    public function addMouvementCompteClient($num_transac, $soldeavant, $soldeapres, $montant, $compte, $operation, $commentaire, $connexion)
    {
        try
        {
            $date = date("Y-m-d H:i:s");
            $statut = 1;
            $query_insert = "INSERT INTO releve_comptes_client( num_transac, date_transaction, solde_avant, solde_apres, montant, idcompte, operation, commentaire) ";
            $query_insert .= " VALUES (:num_transac, :date_transaction, :solde_avant, :solde_apres, :montant, :compte, :operation, :commentaire)";
            $rs_insert = $connexion->prepare($query_insert);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':date_transaction', $date);
            $rs_insert->bindParam(':solde_avant', $soldeavant);
            $rs_insert->bindParam(':solde_apres', $soldeapres);
            $rs_insert->bindParam(':montant', $montant);
            $rs_insert->bindParam(':compte', $compte);
            $rs_insert->bindParam(':operation', $operation);
            $rs_insert->bindParam(':commentaire', $commentaire);
            $res = $rs_insert->execute();
            return $res;
        }
        catch (\Exception $e) {
            return -1;
        }
    }

    public function addMouvementCompteAgence($num_transac, $soldeavant, $soldeapres, $montant, $agence, $operation, $commentaire, $connexion)
    {
        try
        {
            $date = date("Y-m-d H:i:s");

            $query_insert = "INSERT INTO releve_solde_agence ( num_transac, date_transaction, solde_avant, solde_apres, montant, fk_agence, operation, commentaire) ";
            $query_insert .= " VALUES (:num_transac, :date_transaction, :solde_avant, :solde_apres, :montant, :fk_agence, :operation, :commentaire)";
            $rs_insert = $connexion->prepare($query_insert);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':date_transaction', $date);
            $rs_insert->bindParam(':solde_avant', $soldeavant);
            $rs_insert->bindParam(':solde_apres', $soldeapres);
            $rs_insert->bindParam(':montant', $montant);
            $rs_insert->bindParam(':fk_agence', $agence);
            $rs_insert->bindParam(':operation', $operation);
            $rs_insert->bindParam(':commentaire', $commentaire);
            $res = $rs_insert->execute();
            return $res;
        }
        catch (\Exception $e) {
            return -1;
        }
    }

    /***********insert journal************/
    public function insertJournal($action, $object, $commentaires, $type, $iduser)
    {
        $date = date('Y-m-d H:i:s');
        try {
            $sql = "INSERT INTO action_utilisateur(date, action, action_object, IDUSER, commentaire, type) VALUES(:dat, :act, :obj,  :iduser, :comment, :typ)";
            $req = $this->connexion->getConnexion()->prepare($sql);
            $res = $req->execute(array(
                "dat" => $date,
                "act" => $action,
                "obj" => $object,
                "iduser" => $iduser,
                "comment" => $commentaires,
                "typ" => $type
            ));
        } catch (\PDOException $e) {
            return -2;
        }
        return $res;
    }

    /**************Fichier log txt********************************************/
    public function ecrire_journal($errtxt)
    {

        if (!file_exists(__DIR__ . '/../logs/' . date('Y'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y'), 0777);

        }
        if (!file_exists(__DIR__ . '/../logs/' . date('Y') . "/" . date('m'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y') . '/' . date('m'), 0777);

        }
        if (!file_exists(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W'), 0777);

        }
        $fp = fopen(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W') . '/' . date("d_m_Y") . '' . '.txt', 'a+'); // ouvrir le fichier ou le créer
        fseek($fp, SEEK_END); // poser le point de lecture à la fin du fichier
        $nouvel_ligne = $errtxt . "\r\n"; // ajouter un retour à la ligne au fichier
        fputs($fp, $nouvel_ligne); // ecrire ce texte
        fclose($fp); //fermer le fichier
    }

    /***********************************************Ecrire dans le fichier********************************************/
    public function log_journal($action, $objet, $comment, $module, $iduser)
    {
        $this->insertJournal($action, $objet, $comment, $module, $iduser);
        $log = "Date:" . date('d-m-Y H:i:s') . " - Idutilisateur:" . $iduser . " - Module: " . $module . " - Action:" . $action . " - Object:" . $objet . " - IP:" . $_SERVER["REMOTE_ADDR"];
        $this->ecrire_journal($log);
    }

    /***********************************************Genere un numero de transaction********************************************/
    public function Generer_numtransaction()
    {
        $found = 0;
        while ($found == 0) {
            $code_carte = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $colname_rq_code_existe = $code_carte;
            $query_rq_code_existe = $this->connexion->getConnexion()->prepare("SELECT rowid FROM transaction WHERE num_transac ='" . $colname_rq_code_existe . "'");
            $query_rq_code_existe->execute();
            $totalRows_rq_code_existe = $query_rq_code_existe->rowCount();
            if ($totalRows_rq_code_existe == 0) {
                //CODE GENERER
                $code_generer = $code_carte;
                $found = 1;
                break;
            }
        }
        $this->connexion->closeConnexion();
        return $code_generer;
    }

    /***********************************************Solde compte distributeur********************************************/
    public function soldeCompteDist($agence)
    {
        $sql = "SELECT solde FROM agence WHERE rowid=:agence";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("agence" => $agence));
            $result = $stmt->fetchObject();
            $this->connexion->closeConnexion();
            return $result->solde;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    public function soldeCarteDist($agence, $user)
    {
        $sql = "SELECT * FROM agence WHERE rowid=:agence";
        try
        {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("agence" => $agence));
            $result = $stmt->fetchObject();
            return $result;

        } catch (\PDOException $e) {
            return -1;
        }
    }

    public function infsoCarteDist($agence)
    {
        $sql = "SELECT * FROM agence WHERE rowid=:agence";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("agence" => $agence));
            $result = $stmt->fetchObject();
            //return $result->solde;
            return $result;

        } catch (\PDOException $e) {
            return -1;
        }
    }

    public function getTauxCommissionPart($service, $marchand){
        try
        {
            $query_rs_user= "SELECT pourcentage_part as part FROM commission_dispatch WHERE service=".$service." AND agence=".$marchand." ";
            $user = $this->connexion->getConnexion()->prepare($query_rs_user);
            $user->execute();
            $rs_user = $user->fetchColumn();
            $this->connexion->closeConnexion();
            return $rs_user->part;
        }
        catch(\PDOException $e){
            return -1;
        }
    }

    public function getTauxCommissionNum($service, $marchand)
    {
        try
        {
            $query_rs_user= "SELECT pourcentage_num as num FROM commission_dispatch WHERE service=".$service." AND agence=".$marchand." ";
            $user = $this->connexion->getConnexion()->prepare($query_rs_user);
            $user->execute();
            $rs_user = $user->fetchObject();
            $this->connexion->closeConnexion();
            return $rs_user->num;
        }
        catch(\PDOException $e){
            return -1;
        }
    }

    //-----------------------------------------------debiter solde agence -----------------------------------------------
     public function debiterSoldeAgence($montant, $agence, $connexion)
     {
         try
         {
             $req = $connexion->prepare("UPDATE agence SET solde=solde-:soldes WHERE rowid=:agence");
             $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$agence));
             if($Result1 > 0)
             {
                 return  1;
             }
             else return -1;
         }
         catch(PDOException $e)
         {
             return -2;
         }
    }

    /***************crediter solde agence***************/
    function crediter_soldeAgence($montant, $row_agence, $connexion)
    {
        try
        {
            $req = $connexion->prepare("UPDATE agence SET solde=solde+:soldes WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$row_agence));
            if($Result1 > 0){
                return  1;
            }
            else return -1;
        }
        catch(PDOException $e)
        {
            return -2;
        }
    }

    /************************************Calcul des frais*****************************/
    public function getFraisByService($serviceID, $montant = 0)
    {
            if ($serviceID == 12)
            {
                $query_rq_service = "SELECT * FROM tarif_frais
					WHERE montant_deb <= :mtt
					AND montant_fin >= :mtt1
					AND  service=:serviceID ";

                $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                $service->bindParam("mtt", $montant);
                $service->bindParam("mtt1", $montant);
                $service->bindParam("serviceID", $serviceID);
                $service->execute();
                $row_rq_service = $service->fetchObject();

                if ($row_rq_service->valeur == 0.01) $return = ($montant * 0.01); else
                    $return = $row_rq_service->valeur;
            }
            if ($serviceID == 38)
            {
                $query_rq_service = "SELECT * FROM tarif_frais
					WHERE montant_deb <= :mtt
					AND montant_fin >= :mtt1
					AND  service=:serviceID ";

                $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                $service->bindParam("mtt", $montant);
                $service->bindParam("mtt1", $montant);
                $service->bindParam("serviceID", $serviceID);
                $service->execute();
                $row_rq_service = $service->fetchObject();

                if ($row_rq_service->valeur == 0.01) $return = ($montant * 0.2); else
                    $return = $row_rq_service->valeur;
            } else {
                $query_rq_service = "SELECT frais FROM service
					WHERE rowid=:serviceID";

                $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                $service->bindParam("serviceID", $serviceID);
                $service->execute();
                $row_rq_service = $service->fetchObject();

                $return = $row_rq_service->frais;
            }
            $this->connexion->closeConnexion();
            return $return;
    }

    private function convert_date($date)
    {
        if ($date != "") {
            $aa = substr($date, 4, 4);
            $mm = substr($date, 2, 2);
            $jj = substr($date, 0, 2);
            return $aa . "-" . $mm . "-" . $jj;
        } else {
            return $date;
        }
    }

    public function crediterCarteCommissionJirama($montant, $connexion)
    {
            try {
                $req = $connexion->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=8");
                $Result1 = $req->execute(array("soldes" => $montant));
                $this->connexion->closeConnexion();
                if ($Result1 > 0) {
                    $return = 1;
                } else $return = 0;
            } catch (\PDOException $e) {
                $return = -1;
            }
            return $return;
    }

    public function crediterCarteCommission($montant, $connexion)
    {
        $return = 0;
        try {
            $req = $connexion->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=1");
            $Result1 = $req->execute(array("soldes" => $montant));
            $this->connexion->closeConnexion();
            if ($Result1 > 0) {
                $return = 1;
            } else $return = 0;
        } catch (\PDOException $e) {
            $return = -1;
        }
        return $return;
    }

    public function crediterAbonnementPostal($montant, $connexion)
    {
        $return = 0;
        try {
            $req = $connexion->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=6");
            $Result1 = $req->execute(array("soldes" => $montant));
            $this->connexion->closeConnexion();
            if ($Result1 > 0) {
                $return = 1;
            } else $return = 0;
        } catch (\PDOException $e) {
            $return = -1;
        }
        return $return;
    }

    /****************************Enregistrer la transaction****************************************************************/
    function SaveTransactionCom4($num_transac,$service=0,$montant=0,$fk_carte=0,$user=1,$numero_caisse=0, $statut=1,$commentaire, $commission=0, $fk_agence=0, $transactionID=0, $fk_marchand=NULL)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO transaction (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_carte, numero_caisse, commentaire, commission, fk_agence, transactionID, fk_marchand)
            VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_carte, :numero_caisse, :commentaire, :commission, :fk_agence, :transactionID, :fk_marchand)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => ($num_transac),
                "date_transaction" => ($datetransaction),
                "montant" => intval($montant),
                "statut" => intval($statut),
                "fkuser" => intval($user),
                "fk_service" => intval($service),
                "fk_carte" => ($fk_carte),
                "numero_caisse" => intval($numero_caisse),
                "commentaire" => ($commentaire),
                "commission" => intval($commission),
                "fk_agence" => intval($fk_agence),
                "transactionID" => intval($transactionID),
                "fk_marchand" => intval($fk_marchand)
            ));
            $this->connexion->closeConnexion();
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return -1;
        }
    }

    public function SaveTransaction($num_transac, $service, $montant, $rowid_carte, $fkuser, $statut = 0, $commentaire = '', $commission = 0, $fk_agence = 0, $transactId = 0, $fkuser_support = 0, $fkagence_support = 0)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO transaction (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_carte, commentaire, commission, fk_agence, transactionID, fkuser_support,fkagence_support )
				    VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_carte, :commentaire, :commission, :fk_agence, :transactionID, :fkuser_support, :fkagence_support )";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => $num_transac,
                "date_transaction" => $datetransaction,
                "montant" => $montant,
                "statut" => $statut,
                "fkuser" => $fkuser,
                "fk_service" => $service,
                "fk_carte" => $rowid_carte,
                "commentaire" => $commentaire,
                "commission" => $commission,
                "fk_agence" => $fk_agence,
                "transactionID" => $transactId,
                "fkuser_support" => $fkuser_support,
                "fkagence_support" => $fkagence_support
            ));
            //var_dump($res);die;
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return $Exception;
        }
    }

    /********************************Ajouter les comsissions******************************************/

    function addCommission($montant_commission, $idservices, $idpartenaire_commission, $carte = '', $observations = "")
    {
        $date_envoie = date("Y-m-d H:i:s");
            try {
                $query_insert = "INSERT INTO transaction_commission( montant_commission, idservices, observations, idagence,num_carte) ";
                $query_insert .= " VALUES (:montant_commission,:idservices,:observations,:idagence,:num_carte )";
                $rs_insert = $this->connexion->getConnexion()->prepare($query_insert);
                $rs_insert->bindParam(':montant_commission', $montant_commission);
                $rs_insert->bindParam(':idservices', $idservices);
                $rs_insert->bindParam(':observations', $observations);
                $rs_insert->bindParam(':idagence', $idpartenaire_commission);
                $rs_insert->bindParam(':num_carte', $carte);
                $result = $rs_insert->execute();

                $this->connexion->closeConnexion();
                if ($result > 0) return 1;
                else return 0;

            } catch (\PDOException $e) {
                return -1;
            }
    }

    /********************************Ajouter les comsissions a faire******************************************/

    function addCommission_afaire($montant_commission, $idservices, $idpartenaire_commission, $carte = '', $observations = "")
    {
        $date_envoie = date("Y-m-d H:i:s");
            try {
                $query_insert = "INSERT INTO transaction_commission_afaire( montant_commission, idservices, observations, idagence, num_carte) ";
                $query_insert .= " VALUES (:montant_commission,:idservices,:observations,:idagence, :num_carte)";
                $rs_insert = $this->connexion->getConnexion()->prepare($query_insert);
                $rs_insert->bindParam(':montant_commission', $montant_commission);
                $rs_insert->bindParam(':idservices', $idservices);
                $rs_insert->bindParam(':observations', $observations);
                $rs_insert->bindParam(':idagence', $idpartenaire_commission);
                $rs_insert->bindParam(':num_carte', $carte);
                $rs_insert->execute();
                $this->connexion->closeConnexion();

            } catch (\PDOException $e) {
                return -1;
            }
    }

    /*****************get Transaction by Num transaction***************/
    public function transactionByNum($numeroTransact)
    {
        try
        {
            $sql = "SELECT DISTINCT rowid, fk_carte, num_transac, fkuser, fk_agence,  montant, date_transaction, statut, commission  
                    FROM transaction 
                    WHERE statut=1 AND num_transac = :num 
                    ORDER BY rowid DESC ";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("num" => strval($numeroTransact)));
            $a = $user->fetchObject();
            $ligne = $user->rowCount();
            if($ligne > 0) return $a;
            else return -1;
        }
        catch(\PDOException $e)
        {
           return -2;
        }
    }

    /************************************************ NOM AGENCE****************************************************/
    public function geAgence($id)
    {
        try
        {
            $sql = "SELECT label FROM agence WHERE rowid =:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("id" => $id));
            $a = $user->fetchObject();
            return $a->label;
        }
        catch (\PDOException $e)
        {
            $this->pdo = NULL;
            return "Pas de nom";
        }
    }

    public function simpleDate($date)
    {
        $date_fr = "";
        if ($date != "") {
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Jan";
                    break;
                case "02":
                    $mm = "Fev";
                    break;
                case "03":
                    $mm = "Mar";
                    break;
                case "04":
                    $mm = "Avr";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Jui";
                    break;
                case "07":
                    $mm = "Juil";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Sept";
                    break;
                case "10":
                    $mm = "Oct";
                    break;
                case "11":
                    $mm = "Nov";
                    break;
                case "12":
                    $mm = "Dec";
                    break;
            }
            ////////////////
            $date_fr = $jj . "-" . $mm . "-" . $aa . " </b>";
        }
        return $date_fr;
    }

    public function dateEtHeure($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Jan";
                    break;
                case "02":
                    $mm = "Fev";
                    break;
                case "03":
                    $mm = "Mar";
                    break;
                case "04":
                    $mm = "Avr";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Jui";
                    break;
                case "07":
                    $mm = "Juil";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Sept";
                    break;
                case "10":
                    $mm = "Oct";
                    break;
                case "11":
                    $mm = "Nov";
                    break;
                case "12":
                    $mm = "Dec";
                    break;
            }
            ////////////////
            $date_fr = $jj . "-" . $mm . "-" . $aa . " <b>" . $hh . ":" . $ii . ":" . $ss . " </b>";
        }
        return $date_fr;
    }

    public function sendTransactionByMail($mail, $transaction, $badge, $montant, $statut) {

        $sujet = "Nouvelle recharge RAPIDO";

        $msg = "Transaction ID : PC".$transaction."<br/>";
        $msg .= "Date : ".date('Y-m-d H:i:s')."<br/>";
        $msg .= "CustomerID : ".$badge."<br/>";
        $msg .= "Amount : ".$montant." FCFA<br/>";
        $msg .= "Transaction status : ".$statut."<br/>";
        $msg .= "Freefield 1 : 92222"."<br/>";

        $vers_mail = $mail;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$msg."<br />";

        $message.= "</p></td>";
        $message.= "</tr>";

        $message.= "</table>";
        /** Envoi du mail **/
        $entete = "Content-type: text/html; charset=utf8\r\n";
        //$entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From: POSTECASH <no-reply@postecash.com>\r\n";
        mail($vers_mail, $sujet, $message, $entete);


    }

    /**************Abigail********************************************/

    /**********************************************************************************************************************************************/
    /**
     * @param $string
     * @return string
     */
    public function securite_xss($string)
    {
        $string = htmlspecialchars($string, ENT_QUOTES);
        $string = strip_tags($string);
        $string = stripslashes($string);
        return $string;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_numero($id_length = 6){

        // add any character / digit
        $alfa = '1234567890';
        $token = '';
        for($i = 1; $i < $id_length; $i ++) {

            // generate randomly within given character/digits
            @$token .= $alfa[rand(1, strlen($alfa))];

        }
        return $token;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_code_validation($id_length = 10)
    {
        $string = "";
        $chaine = "0123456789";
        \srand((double)\microtime() * 1000000);
        for ($i = 0; $i < $id_length; $i++)
            $string .= $chaine[\rand() % \strlen($chaine)];
        return $string;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_code($id_length = 10){
        // add any character / digit
        $alfa = 'abcdefghijklmnopqrstuvwxyz@@@@1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ@@@@0987654321';
        $token = '';
        for($i = 1; $i < $id_length; $i ++) {

            // generate randomly within given character/digits
            @$token .= $alfa[rand(1, strlen($alfa))];

        }
        return $token;
    }

    public function generateur($length = 10)
    {
        $random = "";
        srand((double)microtime()*1000000);
        $data = "AbcDE123IJKLMN67QRSTUVWXYZaBCdefghijklmn123opq45rs67tuv89wxyz0FGH45OP89";
        for($i = 0; $i < $length; $i++)
            $random .= substr($data, (rand()%(strlen($data))), 1);
        return $random;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_numTransaction($id_length = 12){
        $found = 0;
        while ($found == 0) {
            $code_carte = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $colname_rq_code_existe = $code_carte;
            $sql = "SELECT rowid FROM transaction WHERE num_transac =:num";
            try {
                $stmt = $this->connexion->getConnexion()->prepare($sql);
                $stmt->bindParam("num", $colname_rq_code_existe);
                $stmt->execute();
                $employee = $stmt->fetchObject();
                $totalRows_rq_code_existe = $stmt->rowCount();
                if ($totalRows_rq_code_existe == 0) {
                    $code_generer = $code_carte;
                    $found = 1;
                    break;
                }
            } catch (\PDOException $e) {
                $code_generer = -2;
                die;
            }
        }
        return $code_generer;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj moisenlettre aaaa a hh:ii:ss
     */
    public function mois_en_lettre($date)
    {/*
        $yearMonth = date('m-d',strtotime($finAbonn));

        if($yearMonth=='12-31'){
            $newEndingDate = date("Y", strtotime("+ 1 year", strtotime($finAbonn)));
        }else{
            $newEndingDate=date('Y', strtotime($finAbonn));
        }*/

        $date_fr = '';
        if ($date != '') {
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Janvier';
                    break;
                case '02':
                    $mm = 'Février';
                    break;
                case '03':
                    $mm = 'Mars';
                    break;
                case '04':
                    $mm = 'Avril';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juillet';
                    break;
                case '08':
                    $mm = 'Août';
                    break;
                case '09':
                    $mm = 'Septembre';
                    break;
                case '10':
                    $mm = "Octobre";
                    break;
                case '11':
                    $mm = 'Novembre';
                    break;
                case '12':
                    $mm = 'Décembre';
                    break;
            }
            ////////////////
            //$date_fr = $jj . ' ' . $mm . ' -' . $aa ;
            $date_fr =  $mm . '-' . $aa ;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj moisenlettre aaaa
     */
    public function date_mois_en_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Janvier';
                    break;
                case '02':
                    $mm = 'Février';
                    break;
                case '03':
                    $mm = 'Mars';
                    break;
                case '04':
                    $mm = 'Avril';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juillet';
                    break;
                case '08':
                    $mm = 'Août';
                    break;
                case '09':
                    $mm = 'Septembre';
                    break;
                case '10':
                    $mm = "Octobre";
                    break;
                case '11':
                    $mm = 'Novembre';
                    break;
                case '12':
                    $mm = 'Décembre';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa ;
        }
        return $date_fr;
    }


    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj moisentroislettre aaaa a hh:ii:ss
     */
    public function date_heure_mois_en_trois_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Jan';
                    break;
                case '02':
                    $mm = 'Fév';
                    break;
                case '03':
                    $mm = 'Mar';
                    break;
                case '04':
                    $mm = 'Avr';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juil';
                    break;
                case '08':
                    $mm = 'Aoû';
                    break;
                case '09':
                    $mm = 'Sep';
                    break;
                case '10':
                    $mm = "Oct";
                    break;
                case '11':
                    $mm = 'Nov';
                    break;
                case '12':
                    $mm = 'Déc';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa . ' à ' . $hh . ':' . $ii .':' . $ss;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj moisentroislettre aaaa
     */
    public function date_mois_en_trois_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Jan';
                    break;
                case '02':
                    $mm = 'Fév';
                    break;
                case '03':
                    $mm = 'Mar';
                    break;
                case '04':
                    $mm = 'Avr';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juil';
                    break;
                case '08':
                    $mm = 'Aoû';
                    break;
                case '09':
                    $mm = 'Sep';
                    break;
                case '10':
                    $mm = "Oct";
                    break;
                case '11':
                    $mm = 'Nov';
                    break;
                case '12':
                    $mm = 'Déc';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa ;
        }
        return $date_fr;
    }
    /**********************************************************************************************************************************************/

    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj/mm/aaaa a hh:ii:ss
     */
    public function date_jj_mm_aaaa_hh_ii_ss($date)
    {
        $date_fr = '';
        if ($date != '') {
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa . ' à ' . $hh . ':' . $ii .':' . $ss;
        }
        return $date_fr;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj/mm/aaaa
     */
    public function date_jj_mm_aaaa($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            ////////////////
            $date_fr = $jj . '-' . $mm . '-' . $aa ;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj/mm/aaaa
     */
    public function date_aaaa_mm_jj($date)
    {
        $date_eng = '';
        if ($date != '') {

            $aa = substr($date, 6, 4);
            $mm = substr($date, 3, 2);
            $jj = substr($date, 0, 2);

            ////////////////
            $date_fr = $aa . '-' . $mm . '-' . $jj ;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $number
     * @return string au format 1 234 567,89
     */
    public function number_format($number){
        return number_format($number, 0, ',', ' ');
    }

    function nombre_form($nombre)
    {
        return @number_format($nombre, 0, ',', ' ');
    }
    /**********************************************************************************************************************************************/

    public function returnLast4Digits($cardid)
    {
        if (strlen($cardid) > 4) return substr($cardid, -4);
        else return -1;
    }

    public function returnCustomerId($cardid)
    {
        if (strlen($cardid) > 7) return substr($cardid, 0, 7);
        else return -1;
    }

     public function envoiCodeValidationCreditercarte($dest, $nom, $code, $langue){
        $de_nom = $langue['plateforme_postecash'];
        $de_mail = "no-reply@postecash.com";

        $vers_nom = $nom;
        //$vers_mail = "administrateur@postecash.sn";
        $vers_mail = $dest;
        $sujet =  $langue['rechargement_carte_agence'];
        $message = "<div align='left'>" .$de_nom. "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>".$sujet."</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_5']."</div></br>";
        $message .= "<div align='left'>".$langue['envoi_mail_code_rechargement_2']."</div></br>";
        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_3']."</b>: ".$code."</div></br>";
        $message .= "<div align='left'><b>".$langue['envoi_mail_code_rechargement_4']." : </b>".$this->getDateNow('WITH_TIME')."</div>";

        $entete = '';
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:POSTE CASH <no-reply@postecash.com>\r\n";
        return mail('', $sujet, $message, $entete);
    }

    public function envoiMailAlert($data){
        $de_nom = 'PosteCash Espace Distributeur';
        $de_mail = "no-reply@distributeur.pmp-admin.com";

        $vers_nom = 'Cher Papa NGOM';
        //$vers_mail = "administrateur@postecash.sn";
        $vers_mail = 'papa.ngom@numherit.com';
        $sujet =  'Alert disfonctionnement espace distributeur';
        $message = "<div align='left'>" .$de_nom. "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>".$sujet."</br>";
        $message .= "</br>";
        $message .= "<div align='left'>".$data."</div></br>";

        $entete = '';
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:PosteCash <no-reply@ditributeur.pmp-admin.com>\r\n";
        return mail('', $sujet, $message, $entete);
    }

    /************************************************ NOM PRENOM UTILISATEUR****************************************************/
    public function getUser($id)
    {
        try {
            $sql = "SELECT nom,prenom FROM user WHERE rowid =:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => $id,
                )
            );
            $a = $user->fetch();
            return $a['prenom'] . ' ' . $a['nom'];
        } catch (Exception $e) {
            $this->pdo = NULL;
            return "Pas de nom";
        }
    }

    /***********************************************date_fr4*******************************************/
    public function date_fr4($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janv";
                    break;
                case "02":
                    $mm = "Fev";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juil";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Sept";
                    break;
                case "10":
                    $mm = "Oct";
                    break;
                case "11":
                    $mm = "Nov";
                    break;
                case "12":
                    $mm = "Dec";
                    break;
            }
            ////////////////
            $date_fr = $jj . " " . $mm . " " . $aa . "  " . $hh . ":" . $ii . ":" . $ss;
        }
        return $date_fr;
    }

    function calculFraisab($serviceID, $montant=0)
    {
        if($serviceID ==12 || $serviceID ==4)
        {
            try {
                    $query_rq_service = "SELECT tarif_frais.* FROM service, tarif_frais 
                    WHERE service.rowid =  tarif_frais.service
                    AND tarif_frais.montant_deb <= :mtt
                    AND tarif_frais.montant_fin >= :mtt1
                    AND  service.rowid=:serviceID";

                    $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                    $service ->bindParam("mtt",$montant);
                    $service ->bindParam("mtt1",$montant);
                    $service ->bindParam("serviceID", $serviceID);
                    $service->execute();
                    $row_rq_service= $service->fetchObject();
                    if($row_rq_service->valeur == 0.01) $return = ($montant * 0.01);
                    else $return = $row_rq_service->valeur;
                }catch (\PDOException $e) {
                    return $e->getMessage();
                }
        }
        elseif($serviceID == 11)
        {
            try {
                    $query_rq_service = "SELECT tarif_frais.* FROM service, tarif_frais
                    WHERE service.rowid =  tarif_frais.service
                    AND tarif_frais.montant_deb <= :mtt
                    AND tarif_frais.montant_fin >= :mtt1
                    AND  service.rowid=:serviceID";

                    $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                    $service ->bindParam("mtt",$montant);
                    $service ->bindParam("mtt1",$montant);
                    $service ->bindParam("serviceID",$serviceID);
                    $service->execute();
                    $row_rq_service= $service->fetchObject();
                    $tab = array('ht' => $row_rq_service->ht,'tva' => $row_rq_service->tva, 'frais' => $row_rq_service->valeur);
                    $return = json_encode($tab);
                }catch (\PDOException $e) {
                    return $e->getMessage();
                }
        }
        else
        {
            try {
            $query_rq_service = "SELECT frais FROM service WHERE rowid=:serviceID";
            $service = $this->connexion->getConnexion()->prepare($query_rq_service);
            $service ->bindParam("serviceID",$serviceID);
            $service->execute();
            $row_rq_service= $service->fetchObject();
            $return = $row_rq_service->frais;
            }catch (\PDOException $e) {
                return $e->getMessage();
            }
        }
        return $return;
    }

    /***************send SMS*************/
    function sendSMS($sender,$destinataire,$message)
    {
        //echo $sender = 'Paositra'; die;
        if($destinataire[0] == '+'){
            $destinataire = substr($destinataire, 1);
        }
        else if($destinataire[0] == '0' && $destinataire[1] == '0'){
            $destinataire = substr($destinataire, 2);
        }
        $operateur = substr($destinataire, 3, 2);

        try {

            $destinataire = str_replace(' ', '', $destinataire);
            $query_rq_service = "SELECT * FROM operateur WHERE statut=1";
            $service = $this->connexion->getConnexion()->prepare($query_rq_service);
            $service->execute();
            $row_rq_service = $service->fetchObject();
            //var_dump($row_rq_service); die;
            $to_day = date('Y-m-d');
            $expire = date('Y-m-d', strtotime($to_day . ' + 3 days'));
            if ($expire >= $row_rq_service->date_fin) {
                $this->tokenSMSOrange();
            }
            if ((int)$row_rq_service->rowid === 1) {

                $destinataire = '+' . $destinataire;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.orange.com/smsmessaging/v1/outbound/tel%3A%2B221000000000/requests',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{"outboundSMSMessageRequest":{"address":"tel:' . $destinataire . '","outboundSMSTextMessage":{"message":"' . $message . '"},"senderAddress":"tel:+221000000000","senderName":"' . $sender . '"}}',
                    CURLOPT_HTTPHEADER => array(
                        'accept: application/json',
                        'authorization: Bearer ' . $row_rq_service->token,
                        'content-type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    $messages = "Madagascar<br/>Erreur WS Envoi SMS Orange: " . $err . "</b>.<br/>Tel: ".$destinataire."<br/>Merci de faire le necessairee (Urgence).";
                    @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                    @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                    @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                    return -1;
                } else {
                    $json = json_decode($response);
                    //var_dump($json); die;
                    if (!array_key_exists('outboundSMSMessageRequest', $json) && array_key_exists('code', $json) && (int)$json->code === 42) {
                        $this->tokenSMSOrange();

                        return -1;
                    } else if (!array_key_exists('outboundSMSMessageRequest', $json) && array_key_exists('code', $json) && (int)$json->code === 41) {
                        $messages = "Madagascar<br/>Le nombre de SMS restant dans le compte est arrive a epuisement.: <b>0 sms</b>.<br/>Merci de recharger le compte (Urgence).";
                        @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                        @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                        @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        return -1;
                    } else if (!array_key_exists('outboundSMSMessageRequest', $json)) {
                        $messages = "Madagascar<br/>Erreur WS Envoi SMS Orange: " . json_encode($json) . "</b>.<br/>Tel: ".$destinataire."<br/>Merci de faire le necessairee (Urgence).";
                        @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                        @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                        @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        return -1;
                    }
                    else{
                        $nb_sms_restant = $this->soldeSMSOrange($row_rq_service->token);
                        if (($nb_sms_restant <= 500 && $nb_sms_restant % 10 === 0) || $nb_sms_restant <= 100) {
                            $messages = "Madagascar<br/>Le nombre de SMS restant dans le compte est faible: <b>" . $nb_sms_restant . " sms</b>.<br/>Merci de recharger le compte (Urgence).";
                            @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                            @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                            @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        }
                        return 1;
                    }
                }


            } else if ((int)$row_rq_service->rowid === 2) {
                $token = base64_encode('jula-login:jula1986');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{ \"from\":\" " . $sender . "\", \"to\":\"" . $destinataire . "\", \"text\":\"" . $message . "\" }",
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "authorization: Basic " . $token . "",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
                if ($err) {
                    return "cURL Error #:" . $err;
                } else {
                    return $response;
                }
            } else if ((int)$row_rq_service->rowid === 3) {
                $destinataire = '+' . $destinataire;
                //$destinataire = '+221774246535';
                $url = 'https://api.primotexto.com/v2/notification/messages/send';
                $curl = curl_init($url);
                /*if (isSet(baseManager::$CURLOPT_PROXY)) {
                    curl_setopt($curl, CURLOPT_PROXY, baseManager::$CURLOPT_PROXY);
                }*/

                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-Primotexto-ApiKey: fd772fc697e680b76a07a71e9cd58209',
                ));
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"number\":\"$destinataire\",\"message\":\"$message\",\"sender\":\"$sender\",\"campaignName\":\"Code de confirmation\",\"category\":\"codeConfirmation\"}");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($curl);
                //echo("$result\n");
                curl_close($curl);
                return $result;
            } else if ((int)$row_rq_service->rowid === 4) {
                $params = array(

                    'access_token' => 'Jhc5qS0eCRx5s8JEhMe5a39Bht5YDVPqHUsoiZ9O',          //sms api access token
                    'to' => '+' . $destinataire,         //destination number
                    'from' => 'PosteCash',                //sender name has to be active
                    'message' => $message,    //message content
                );

                if ($params['access_token'] && $params['to'] && $params['message'] && $params['from']) {
                    $date = '?' . http_build_query($params);
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, 'https://api.smsapi.com/sms.do'.$date);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        // echo 'Error:' . curl_error($ch);
                        return curl_errno($ch);
                    }

                    curl_close ($ch);
                    return $result;
                    /* $file = fopen('https://api.smsapi.com/sms.do' . $date, 'r');
                     $result = fread($file, 1024);
                     fclose($file);
                     return $result;*/
                }

            } else {
                $token = base64_encode('jula-login:jula1986');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{ \"from\":\" " . $sender . "\", \"to\":\"" . $destinataire . "\", \"text\":\"" . $message . "\" }",
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "authorization: Basic " . $token . "",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
                if ($err) {
                    return "cURL Error #:" . $err;
                } else {
                    return $response;
                }
            }
        } catch (Exception $e) {
            return $e;
        }

    }

    public function soldeSMSOrange($token)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.orange.com/sms/admin/v1/contracts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token,
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        }
        else{
            $json = json_decode($response);
            //var_dump($json); die;
            //$sms = $json->{'partnerContracts'}->{'contracts'}[0]->{'serviceContracts'}[0]->{'availableUnits'};
            //return $json;
            $sms = $json->{'partnerContracts'}->{'contracts'}[0]->{'serviceContracts'}[1]->{'availableUnits'};
            return $sms;
        }
    }

    public function tokenSMSOrange()
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.orange.com/oauth/v2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Authorization: Basic VUhmUHB2QWt4NlN0c0FZY0V2N2N1QkNidmU4VEIwZEg6S3ZONkRkOU5uTFY2VGNlag==";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        else{
            $messages = '';
            $res = 0;
            $json = json_decode($result);
            if(array_key_exists('token_type', $json) && array_key_exists('access_token', $json) && array_key_exists('expires_in', $json)){
                $date_debut = date('Y-m-d H:i:s');
                $date_fin = date('Y-m-d', strtotime(date($date_debut)) + (int)$json->{'expires_in'});

                try {

                    $query_rq_service = "UPDATE operateur SET token = :token, date_fin = :expire WHERE rowid = 1";
                    $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                    $service->bindParam('token', $json->{'access_token'});
                    $service->bindParam('expire', $date_fin);
                    $service->execute();
                    if($service->rowCount() === 1)
                        $res = 1;
                }
                catch (PDOException $e){
                    $messages .= $e;
                }
            }

            if($res === 0){
                $messages .= "<br/>La regénération du token a échoué.<br/>Le token current expire dans moins de trois(3) jours.";
                @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
            }
        }
        curl_close($ch);

    }

    public function alerteSMS($destinataire, $vers_nom, $messages) {

        $sujet = "Plateforme Paositra"; //Sujet du mail
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Cher ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$messages."</p></td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>Merci de faire le necessaire.</td>";
        $message.= "</tr>";

        $message.= "</table>";

        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:PosteCash <no-reply@pmp-admin.com>\r\n";
        mail($vers_mail, $sujet, $message, $entete);

    }

    function sendSMSInfos_bip($sender,$destinataire,$message)
    {

        $destinataire = (int)$destinataire;
        $destinataire = '+'.$destinataire;

        $postUrl = "http://api2.infobip.com/api/sendsms/xml";
        //XML-formatted data
        $xmlString =
            "<SMS> 
                                <authentification> 
                                    <username>jula-login</username> 
                                    <password>jula1986</password> 
                                </authentification> 
                                <message> 
                                    <sender>".$sender."</sender> 
                                    <text>".$message."</text> 
                                </message> 
                                <recipients> 
                                    <gsm>".$destinataire."</gsm> 
                                </recipients> 
                        </SMS>";
        //previously formatted XML data becomes value of "XML" POST variable
        $fields = "XML=" . urlencode($xmlString);
        //in this example, POST request was made using PHP's CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        //response of the POST request CURLOPT_RETURNTRANSFER
        //$response = 1;
        $response = curl_exec($ch);
        curl_close($ch);

        //write out the response
        return $response;

    }


    /****************generate Code Retrait******************/
    public function generateCodeRetrait($carte, $montant)
    {
        $found = 0;
        do
        {
            $code = $this->random(10);
            $etat = $this->verifyCodeRetrait($code);
            if($etat == 1)
            {
                $found = 1;
                $this->insertCodeRetrait($code, $carte, $montant);
            }
        }
        while($found == 0);
        return $code;
    }

    /************verify Code Retrait**********/
    public function verifyCodeRetrait($code)
    {
        try
        {
            $sql = "SELECT idcode_retrait from code_retrait WHERE code_retrait = :code";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code" => strval($code)));
            $a = $user->rowCount();
            if($a > 0) return 0;
            else return 1;
        }
        catch(Exception $e)
        {
            return -2;
        }
    }

    /******************insert Code Retrait*******************/
    public function insertCodeRetrait($code, $numcarte, $montant)
    {
        try
        {
            $sql = "INSERT INTO code_retrait(code_retrait, montant, num_carte, statut) VALUES (:code, :montant, :num_carte, :statut)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array("code"=>strval($code), "montant"=>intval($montant), "num_carte"=>intval($numcarte), "statut"=>intval(0)));
            if($res == 1) return 1;
            else return -1;
        }
        catch(Exception $e)
        {

        }
    }

    /*************Recherchercher code retrait***************/
    public function rechercherCodeRetrait($code, $montant)
    {
        try
        {
            $sql = "SELECT idcode_retrait from code_retrait WHERE code_retrait = :code AND montant = :num AND statut = :statut";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("code" => strval($code), "num" =>intval($montant), "statut" =>intval(0)));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if($totrows > 0)
            {
                return $a->idcode_retrait;
            }
            else
            {
                return -1;
            }
        }
        catch(Exception $e)
        {
           return -2;
        }
    }

    /***********valider Code Retrait**************/
    public function validerCodeRetrait($id, $cni)
    {
        try
        {
            $date_retrait = date('Y-m-d H:i:s');
            $sql = "UPDATE code_retrait SET cni =:cni, statut =:statut WHERE idcode_retrait =:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array( "cni"=>strval($cni), "statut"=>intval(1),  "id"=>intval($id)));
            if($res==1) return 1;
            else return -1;
        }
        catch(Exception $e)
        {
            return -2;
        }
    }

    /***********************************************Liste des profession*****************************************************/
    public function professions()
    {
        try {
            $sql = "SELECT * FROM profession ORDER BY profession.`libelle` ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';
            die;
        }
    }

    /************************************** Liste des type de pieces**************************************************************/
    public function typepiece()
    {
        try {
            $sql = "SELECT * FROM typecni ORDER BY lib_typecni ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';
            die;
        }
    }

    /****************************************Liste des pays************************************************************/
    public function listePays()
    {
        try {
            $sql = "Select id, code, alpha2, nom_fr_fr
                from pays ORDER BY alpha2 ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';
            die;
        }
    }

    /************************************Liste des nationalités****************************************************************/
    public function nationalites()
    {
        try {
            $sql = "Select rowid, libelle
                from nationalite ORDER BY libelle ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';
            die;
        }
    }

    /***********************************************Liste des regions*****************************************************/
    public function allRegion()
    {
        try {
            $sql = "Select idregion, lib_region
                    from region
                    /*AND idregion IN ( 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31 )*/
                    ORDER BY lib_region ASC";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "etat" => 1,
                )
            );
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';
            die;
        }
    }

    public function trimUltime($chaine) {
        $chaine = trim($chaine);
        $chaine = str_replace("\t", "", $chaine);
        $chaine = str_replace("\r", "", $chaine);
        $chaine = str_replace("\n", "", $chaine);
        $chaine = preg_replace("( +)", "", $chaine);
        $chaine = str_replace("_", "", $chaine);
        $chaine = str_replace("-", "", $chaine);
        $chaine = str_replace("`", "", $chaine);
        $chaine = str_replace("^", "", $chaine);
        $chaine = str_replace("°", "", $chaine);
        $chaine = str_replace("'", "", $chaine);
        $chaine = str_replace("$", "", $chaine);
        $chaine = str_replace("¨", "", $chaine);
        $chaine = str_replace("^", "", $chaine);
        $chaine = str_replace("&", "", $chaine);
        $chaine = str_replace("\"", "", $chaine);
        $chaine = str_replace("?", "", $chaine);
        $chaine = str_replace("÷", "", $chaine);
        $chaine = str_replace("≠", "", $chaine);
        $chaine = str_replace("…", "", $chaine);
        $chaine = str_replace("°", "", $chaine);
        $chaine = str_replace(">", "", $chaine);
        $chaine = str_replace("<", "", $chaine);
        $chaine = str_replace(";", "", $chaine);
        $chaine = str_replace(":", "", $chaine);
        $chaine = str_replace("=", "", $chaine);
        $chaine = str_replace("€", "", $chaine);
        $chaine = strtr($chaine,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
        return $chaine;
    }

    /****************************************************************************************************/
    public function typeProfil($profil)
    {
        try {
            $sql = "Select type_profil
                from profil
                WHERE type_profil = :id
                AND etat = :etat";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => intval($profil),
                    "etat" => intval(1),
                )
            );
            $a = $user->fetch();
            if ($a != '')
                return $a['type_profil'];
            else
                return -1;
        } catch (Exception $e) {
            echo -99;
            die;
        }

    }

    /***********get Solde agence***************/
    public function getSoldeAgence($idagence)
    {
        try
        {
            $sql = "SELECT solde from agence  WHERE etat=:etat AND rowid=:code";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("etat"=>1, "code"=>$idagence));
            $a = $user->fetchObject();
            $totalrows = $user->rowCount();

            if($totalrows > 0) return $a->solde;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }


    public function saveCodeJula($carte,$numserie,$montant,$date_expire,$user_crea,$agence)
        {
            try {
                $date = date("Y-m-d H:i:s");
                $sql = "INSERT INTO carte_jula_partenaire( carte, numserie, montant, date_expire, user_crea, date_crea, agence)
                VALUES (:carte,:numserie,:montant, :date_expire, :user_crea, :date_crea, :agence)";
                $user = $this->connexion->getConnexion()->prepare($sql);
                $res = $user->execute(array(
                        "carte" => $carte,
                        "numserie" => $numserie,
                        "montant" => $montant,
                        "date_expire"=>$date_expire,
                        "user_crea"=>$user_crea,
                        "date_crea"=>$date,
                        "agence"=>$agence
                ));
                $this->log_user("Generation de carte par partenaire","Code-".$numserie, $user_crea,"generer carte user partenaire: Carte:".$carte, 36);
                $this->connexion->closeConnexion();
                if ($res == 1) return 1;
                else return -1;
            } catch (\PDOException $Exception) {
                return -1;
            }
        }

    // fonctions utiles
    public function date_fr1($date)
    {
        $date_fr="";
        if($date!=""){
        $date_en = substr($date,0,10);
        $jj = substr($date,8,2);
        $mm = substr($date,5,2);
        $aa = substr($date,0,4);

        //mois en lettre
        switch ($mm) {
            case "01":
                $mm = "Jan";
                break;
            case "02":
                $mm = "Fev";
                break;
            case "03":
                $mm = "Mar";
                break;
            case "04":
                $mm = "Avr";
                break;
            case "05":
                $mm = "Mai";
                break;
            case "06":
                $mm = "Jui";
                break;
            case "07":
                $mm = "Juil";
                break;
            case "08":
                $mm = "Aout";
                break;
            case "09":
                $mm = "Sept";
                break;
            case "10":
                $mm = "Oct";
                break;
            case "11":
                $mm = "Nov";
                break;
            case "12":
                $mm = "Dec";
                break;
        }
        ////////////////
        $date_fr = $jj."-".$mm."-".$aa;
        }
        return $date_fr;
    }
    function getPaysById($id)
    {
        $query_rq_transfert = "SELECT nom_fr_fr FROM pays WHERE id =:id";
        $rq_transfert = $this->connexion->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("id",$id);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
        return $row_rq_transfert->nom_fr_fr;
    }
    function getRegionById($id)
    {
        $query_rq_transfert = "SELECT lib_region FROM region WHERE idregion =:id ";
        $rq_transfert = $this->connexion->getConnexion()->prepare($query_rq_transfert);
        $rq_transfert->bindParam("id",$id);
        $rq_transfert->execute();
        $row_rq_transfert= $rq_transfert->fetchObject();
        return $row_rq_transfert->lib_region;
    }

    /***********get Nbre***************/
    function getnbre($date, $rowid)
        {
            try
            {
                $sql = "SELECT DATE(date_transaction) as datet, count(rowid) as nbtransac 
                FROM transaction 
                WHERE DATE(date_transaction) = :code
                AND fkuser = :rowid
                group by datet";
                $user = $this->connexion->getConnexion()->prepare($sql);
                $user->execute(array("code"=>$date,"rowid"=>$rowid));
                $a = $user->fetchAll();
                if (count($a)>0) {
                    return $a;
                }else{
                    return 0;
                }

            }
            catch (PDOException $exception)
            {
                return -2;
            }
        }

    /***********get Nbre***************/
    function getnbre1($date, $rowid)
        {
            try
            {
                $sql = "SELECT DATE(date_transaction) as datet, count(rowid) as nbtransac 
                FROM transaction 
                WHERE DATE(date_transaction) = :code
                AND fk_agence = :rowid
                group by datet";
                $user = $this->connexion->getConnexion()->prepare($sql);
                $user->execute(array("code"=>$date,"rowid"=>$rowid));
                $a = $user->fetchAll();
                if (count($a)>0) {
                    return $a;
                }else{
                    return 0;
                }

            }
            catch (PDOException $exception)
            {
                return -2;
            }
        }

    function joursemaine($date)
    {
        setlocale(LC_TIME, 'fr_FR', 'french', 'fre', 'fra');
        $auj = $date;
        $array= null ;
        $t_auj = strtotime($auj);
        $p_auj = date('N', $t_auj);
        if($p_auj == 1){
         $deb = $t_auj;
         $fin = strtotime($auj.' + 6 day');
        }
        else if($p_auj == 7){
         $deb = strtotime($auj.' - 6 day');
         $fin = $t_auj;
        }
        else{
         $deb = strtotime($auj.' - '.(6-(7-$p_auj)).' day');
         $fin = strtotime($auj.' + '.(7-$p_auj).' day');
        }
        while($deb <= $fin){
         $j= strftime('%Y-%m-%d', $deb);
         $array[]= $j;
         $deb += 86400;
        }
        return $array;
    }

    function moisLettre($date)
    {
        $date_fr = '';
        if ($date != "") {
            $mm = $date;
            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janvier";
                    break;
                case "02":
                    $mm = "Février";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juillet";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Septembre";
                    break;
                case "10":
                    $mm = "Octobre";
                    break;
                case "11":
                    $mm = "Novembre";
                    break;
                case "12":
                    $mm = "Décembre";
                    break;
            }

            $date_fr = $mm;
        }
        return $date_fr;
    }

    /*********Courbe Evolution*********/
    public function montantServiceMensuel($service, $mois, $annee, $bureau, $courbe)
    {
        $cond = '';
        if ($service > 0) {
            $cond .= " AND h.fk_service=" . $service;
        }
        if ($bureau > 0) {
            $cond .= " AND h.fkuser=" . $bureau;
        }
        $statut = 1;
        if ($courbe == 1) {
            $sql = "SELECT COUNT(h.rowid) AS nbre, MONTH(h.date_transaction) AS mois, YEAR(h.date_transaction) AS annee
                FROM transaction h
                WHERE h.statut=1 " . $cond . "
                AND MONTH(h.date_transaction) =:mois
                AND YEAR(h.date_transaction) =:annee
                GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 2) {
            $sql = "SELECT SUM(CASE  
                             WHEN h.fk_service=18 THEN 3000 
                             WHEN h.fk_service=19 THEN 3000
                             WHEN h.montant=0 THEN 0  
                             ELSE h.montant
                           END) AS mt ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 3) {
            $sql = "SELECT 
                       SUM(CASE  
                             WHEN h.fk_service=18 THEN 2000 
                             WHEN h.fk_service=19 THEN 7000
                             ELSE h.commission
                           END) AS frais ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";

        }

        try {
            $stmt_stat_service = $this->connexion->getConnexion()->prepare($sql);
            $stmt_stat_service->bindParam("mois", $mois);
            $stmt_stat_service->bindParam("annee", $annee);
            $stmt_stat_service->execute();
            $returner = $stmt_stat_service->fetchAll();
        } catch (PDOException $e) {
            $return = 1001;
        }
        return $returner;
    }

    /*********Courbe Evolution*********/
    public function montantServiceMensuel1($service, $mois, $annee, $bureau, $courbe)
    {
        $cond = '';
        if ($service > 0) {
            $cond .= " AND h.fk_service=" . $service;
        }
        if ($bureau > 0) {
            $cond .= " AND h.fk_agence=" . $bureau;
        }
        $statut = 1;
        if ($courbe == 1) {
            $sql = "SELECT COUNT(h.rowid) AS nbre, MONTH(h.date_transaction) AS mois, YEAR(h.date_transaction) AS annee
                FROM transaction h
                WHERE h.statut=1 " . $cond . "
                AND MONTH(h.date_transaction) =:mois
                AND YEAR(h.date_transaction) =:annee
                GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 2) {
            $sql = "SELECT SUM(CASE  
                             WHEN h.fk_service=18 THEN 3000 
                             WHEN h.fk_service=19 THEN 3000
                             WHEN h.montant=0 THEN 0  
                             ELSE h.montant
                           END) AS mt ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 3) {
            $sql = "SELECT 
                       SUM(CASE  
                             WHEN h.fk_service=18 THEN 2000 
                             WHEN h.fk_service=19 THEN 7000
                             ELSE h.commission
                           END) AS frais ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";

        }

        try {
            $stmt_stat_service = $this->connexion->getConnexion()->prepare($sql);
            $stmt_stat_service->bindParam("mois", $mois);
            $stmt_stat_service->bindParam("annee", $annee);
            $stmt_stat_service->execute();
            $returner = $stmt_stat_service->fetchAll();
        } catch (PDOException $e) {
            $return = 1001;
        }
        return $returner;
    }

    /*************************************************rechargement  Mensuel par Service*********************************************/
    public function montantRechargementMensuel($service,$user)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        AND transaction.fkuser=:user
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";
        $returner = -1;

        try {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->bindParam("user", $user);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /*************************************************rechargement  Mensuel par Service*********************************************/
    public function montantRechargementMensuel1($service,$user)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        AND transaction.fk_agence=:user
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";
        $returner = -1;

        try {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->bindParam("user", $user);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /*************************************************rechargement  Mensuel par Service*********************************************/
    public function montantFacturierMensuel($service,$user)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        AND transaction.fkuser=:user
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";
        $returner = -1;

        try {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->bindParam("user", $user);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /*************************************************rechargement  Mensuel par Service*********************************************/
    public function montantFacturierMensuel1($service,$user)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        AND transaction.fk_agence=:user
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";
        $returner = -1;

        try {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->bindParam("user", $user);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /*************************************************Paiement  Mensuel par Service*********************************************/
    public function montantPaiementMensuel($service,$user)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        
        AND transaction.fk_agence=:user
        
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";

        try {
            $resultat = $this->connexion->getConnexion()->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->bindParam("user", $user);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /************************************************ NOM PRENOM UTILISATEUR****************************************************/
    public function getQuotePartDistreibuteur($id)
    {
        try {
            $sql = "SELECT taux_commission_distributeur FROM service WHERE rowid =:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => $id,
                )
            );
            $a = $user->fetchObject();
            return $a->taux_commission_distributeur;
        } catch (Exception $e) {
            $this->pdo = NULL;
            return 0;
        }
    }

    public function saveReleveAgence($num_transac, $soldeavant, $soldeapres, $montant, $agence, $operation)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO releveAgence (numtransac, solde_avant, solde_apres, montant, operation, datetransac, agence)
			VALUES (:numtransac, :solde_avant, :solde_apres, :montant, :operation, :datetransac, :agence)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "numtransac" => $num_transac,
                "solde_avant" => $soldeavant,
                "solde_apres" => $soldeapres,
                "montant" => $montant,
                "operation" => $operation,
                "datetransac" => $datetransaction,
                "agence" => $agence
            ));
            $dbh = null;
            if ($res == 1) return 1;
            else return -1;
        } catch (PDOException $Exception) {
            return -1;
        }
    }

    /************************************************ NOM PRENOM UTILISATEUR****************************************************/
    public function getPartnerKey($id)
    {
        try {
            $sql = "SELECT key_partenaire FROM agence WHERE rowid =:id";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => $id,
                )
            );
            $a = $user->fetchObject();
            return $a->key_partenaire;
        } catch (Exception $e) {
            $this->pdo = NULL;
            return '';
        }
    }

    /****************************Enregistrer la transaction****************************************************************/
    public function SaveTransactionDistributeur($num_transac, $service = 0, $montant = 0, $fk_carte = 0, $fkuser = 1, $numero_caisse = 0, $statut = 1, $commentaire, $commission = 0, $fk_agence, $transactionID)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO transaction_distributeur (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_carte, numero_caisse, commentaire, commission, fk_agence, transactionID)
			VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_carte, :numero_caisse, :commentaire, :commission, :fk_agence, :transactionID)";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => ($num_transac),
                "date_transaction" => ($datetransaction),
                "montant" => intval($montant),
                "statut" => intval($statut),
                "fkuser" => intval($fkuser),
                "fk_service" => intval($service),
                "fk_carte" => ($fk_carte),
                "numero_caisse" => intval($numero_caisse),
                "commentaire" => ($commentaire),
                "commission" => intval($commission),
                "fk_agence" => intval($fk_agence),
                "transactionID" => intval($transactionID)
            ));
            $this->connexion->closeConnexion();
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return -1;
        }
    }

    /****************************Enregistrer la transaction****************************************************************/
    public function updateTransactionDistributeur($num_transac, $statut, $transactionID)
    {
        try {
            $sql = "UPDATE transaction_distributeur SET statut = :statut, transactionID = transactionID WHERE num_transac = :num_transac";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => $num_transac,
                "statut" => $statut,
                "transactionID" => $transactionID
            ));
            $this->connexion->closeConnexion();
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return -1;
        }
    }

    /***********************************************Liste des regions par pays == beni*****************************************************/
    public function allRegionByPays($pays = '')
    {
        try {
            if ($pays != '') {
                $sql = "Select idregion, lib_region
                    from region
                    WHERE etat = :etat AND fk_pays = :pays
                    ORDER BY lib_region ASC";
                $user = $this->connexion->getConnexion()->prepare($sql);
                $user->execute(
                    array(
                        "etat" => 1,
                        "pays" => $pays
                    )
                );
                $this->connexion->closeConnexion();
            } else {
                $sql = "Select idregion, lib_region
                    from region
                    WHERE etat = :etat AND fk_pays = ".ID_PAYS."
                    ORDER BY lib_region ASC";
                $user = $this->connexion->getConnexion()->prepare($sql);
                $user->execute(
                    array(
                        "etat" => 1,
                    )
                );
                $this->connexion->closeConnexion();
            }

            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (\Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';

        }
    }

    public function getCarteTelephone($string)
    {
        try {
            $sql = "SELECT carte.* FROM carte  WHERE carte.telephone = :num AND carte.statut = 1";
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->bindParam("num", $string);
            $stmt->execute();
            $this->connexion->closeConnexion();
            $employee = $stmt->fetchObject();
            return $employee->rowid;
        } catch (\Exception $e) {
            return "";
        }

    }

    /****************************TOKEN PARTENAIRE*****************************************/
    public function getToken($id)
    {
        try {
            $sql = "SELECT token FROM authToken WHERE userId =:userId";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("userId" => $id,));
            $this->connexion->closeConnexion();
            $a = $user->fetch();
            return $a['token'];
        } catch (\PDOException $e) {
            return -1;
        }

    }

    function calculFrais($serviceID, $montant=0)
    {
        try {
            $query_rq_service = "SELECT frais FROM service WHERE rowid=:serviceID";
            $service = $this->connexion->getConnexion()->prepare($query_rq_service);
            $service ->bindParam("serviceID",$serviceID);
            $service->execute();
            $row_rq_service= $service->fetchObject();
            $row_rq_count = $service->rowCount();
            //$this->closeConnexion();
            if($row_rq_count === 1){
                if($row_rq_service->frais >= 0){
                    return $row_rq_service->frais;
                }
                else{
                    $query_rq_service = "SELECT tarif_frais.* FROM service, tarif_frais 
                      WHERE service.rowid =  tarif_frais.service
                      AND tarif_frais.montant_deb <= :mtt
                      AND tarif_frais.montant_fin >= :mtt1
                      AND  service.rowid=:serviceID";

                    $service = $this->connexion->getConnexion()->prepare($query_rq_service);
                    $service ->bindParam("mtt",$montant);
                    $service ->bindParam("mtt1",$montant);
                    $service ->bindParam("serviceID", $serviceID);
                    $service->execute();
                    $row_rq_service= $service->fetchObject();
                    $row_rq_count = $service->rowCount();
                    if($row_rq_count === 1){
                        return $row_rq_service->valeur;
                    }
                    else{
                        return $montant * 0.01;
                    }
                }
            }
            else{
                return $montant * 0.01;
            }

        }catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function securite_xss_array(array $array)
    {
        foreach ($array as $key => $value) {
            if (!\is_array($value)) $array[$key] = self::securite_xss($value);
            else self::securite_xss_array($value);
        }
        return $array;
    }

    public function getCommission($fk_agence)
    {
        try {
            $sql = "SELECT taux_commission FROM agence WHERE rowid=:id_agence";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("id_agence" => $fk_agence));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->taux_commission;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*************************************************date_fr*******************************************/
    public function date_fr($date)
    {

        $datefr = "";
        $jj = substr($date, 8, 2);
        $mm = substr($date, 5, 2);
        $aa = substr($date, 0, 4);
        $datefr = $jj . "-" . $mm . "-" . $aa;
        return $datefr;
    }

    public function saveInfosPaiement($num_transac,$vNUM_SESSION,$vnum_fact,$vref_client,$vdate_paie,$vtel_client,$vtel_jirama,$vmontant,$frais,$fk_user,$fk_agence, $statut=0 )
    {
        $insertSQL = "INSERT INTO paiement_jirama (num_transac,vNUM_SESSION, vnum_fact, vref_client, vdate_paie, vtel_client, vtel_jirama, montant,frais,fk_user,fk_agence, statut)";
        $insertSQL .= " VALUES(:num_transac,:vNUM_SESSION, :vnum_fact,:vref_client,:vdate_paie, :vtel_client, :vtel_jirama,:montant,:frais,:fk_user,:fk_agence,:statut)";
        $rs_insert = $this->getConnexion()->prepare($insertSQL);
        $rs_insert->execute(array("num_transac"=>$num_transac,
            "vNUM_SESSION"=>$vNUM_SESSION,
            "vnum_fact"=>$vnum_fact,
            "vref_client"=>$vref_client,
            "vdate_paie"=>$vdate_paie,
            "vtel_client"=>$vtel_client,
            "vtel_jirama"=>$vtel_jirama,
            "montant"=>$vmontant,
            "frais"=>$frais,
            "fk_user"=>$fk_user,
            "fk_agence"=>$fk_agence,
            "statut"=>$statut));


        if($rs_insert->rowCount() === 1)
            return 1;
        else return -1;
    }

    /**********Taux Commission Distributeur******/
    /*public function getTauxDistributeur($service)
    {
        try
        {
            $sql = "SELECT taux_distributeur FROM service WHERE rowid=:service";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("service" => $service));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->taux_distributeur;
            else return -1;
        }
        catch (Exception $e)
        {
            return -2;
        }
    }*/


    public function getTauxDistributeur($service, $distributeur)
    {
        try
        {
            $sql = "SELECT taux FROM taux_commission_distributeur WHERE fk_service=:service AND fk_distributeur=:dist";
            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(array("service" => $service, "dist" => $distributeur));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->taux;
            else return -1;
        }
        catch (Exception $e)
        {
            return -2;
        }
    }


    /************** Permet de verifier  si le user est autoisé a voir cet action ********************************************/
    public function Est_autoriser($action, $profil)
    {
        try
        {
            $query = "SELECT valide FROM affectation_droit WHERE action=:action AND profil=:profil AND valide = 1";

            $result = $this->connexion->getConnexion()->prepare($query);
            $result->bindParam("action", $action);
            $result->bindParam("profil", $profil);
            $result->execute();
            $result->fetchObject();
            $totalRows = $result->rowCount();
            if ($totalRows > 0)
            {
                return 1;
            }
            else {
                return -1;
            }
        }
        catch (\PDOException $e) {
            return -2;
        }
    }



    /**
     * @param array $paramFiles
     * @param string $url
     * @param string $nameFile
     * @return bool
     */
    public function setUploadFiles($paramFiles = [], $url = "", $nameFile = "")
    {
        if (\count($paramFiles) > 0 && $paramFiles["error"] != "4" && $url != "") {
            if ($nameFile == "") $nameFile = $this->getDateNow('WITH_TIME');
            $nameFile .= "." . \pathinfo($paramFiles['name'], PATHINFO_EXTENSION);

            $res = move_uploaded_file($paramFiles['tmp_name'], $url .$nameFile);
            return $res;
        }
        return false;
    }


    /*************verif CNI*************/
    public function verifCNI($fkcarte, $cni)
    {
        try {
            $query_rs_cni = "SELECT b.cni FROM `beneficiaire` as b INNER JOIN carte as c ON c.beneficiaire_rowid = b.rowid WHERE c.rowid= " . $fkcarte . "  AND c.statut = 1 AND b.cni = '".$cni."'";
            $resultat = $this->connexion->getConnexion()->prepare($query_rs_cni);
            $resultat->execute();
            $rs_resultat = $resultat->rowCount();
            if($rs_resultat > 0) return 1;
            else return 0;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*******************************Ajouter les detail des transactions******************************************/
    function saveDetailsTranscation($numtransaction, $numcarte, $montant, $sens, $date_op)
    {
        try {

            $insertSQL = "INSERT INTO detail_transaction(numtransac, fkcarte, montant, sens, date_op)";
            $insertSQL .= "VALUES ('" . $numtransaction . "', '" . $numcarte . "', '" . $montant . "', '" . $sens . "', '" . $date_op . "') ";
            $resultat = $this->connexion->getConnexion()->prepare($insertSQL);
            $return = $resultat->execute();
            if ($return == 1) return $return;
            else return -1;
        } catch (\PDOException $Exception) {
            return -2;
        }
    }


    public function  commissionDistributeurService($distributeur, $fk_service, $taux)
    {
        try
        {

            $sql = "SELECT (SUM(t.commission) * ".$taux.") / 100 as ma_comm
                    FROM transaction t
                    WHERE t.statut = 1 
                    AND t.fk_agence = ". $distributeur." 
                    AND t.fk_service = ". $fk_service."
                    GROUP By t.fk_service";

            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
            return $a->ma_comm;
        }
        catch(PDOException $e){
            return -1;
        }
    }


    public function  nombreTransactionDistributeurService($distributeur, $fk_service)
    {
        try
        {

            $sql = "SELECT count(t.rowid) as nb
                    FROM transaction t
                    WHERE t.statut = 1 
                    AND t.fk_agence = ". $distributeur." 
                    AND t.fk_service = ". $fk_service."
                    GROUP By t.fk_service";

            $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $this->connexion->closeConnexion();
            return $a->nb;
        }
        catch(PDOException $e){
            return -1;
        }
    }
}