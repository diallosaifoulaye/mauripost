<?php
/**
 * Created by PhpStorm.
 * User: finance3
 * Date: 08/09/2017
 * Time: 09:57
 */
class CarteModel extends \app\core\BaseModel
{
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

    public function verifyBeneficiaire($nomchamp,$valeur,$table){
        try{
            $sql = "SELECT rowid FROM ".$table."
                    WHERE ".$nomchamp." = '".$valeur."'
                    AND etat = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            return $user->rowCount();
        }
        catch(Exception $e){
            return  -99;
        }
    }

    public function verifyBeneficiaire2($nomchamp,$valeur, $idbenef,$table){
        try{
            $sql = "SELECT rowid FROM ".$table."
                    WHERE ".$nomchamp." = :nomchamp AND rowid != ".$idbenef."
                    AND etat = :etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "nomchamp" => $valeur,
                    "etat" => 1,
                )
            );
            return $user->rowCount();
        }
        catch(Exception $e){
            return -99;
        }
    }

    public function departement($region){
        try{
            $sql = "Select iddepartement,lib_departement from departement WHERE region = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => $region
                )
            );
            $a = $user->fetchAll();
            return $a;
        }
        catch(Exception $e){
            return 'Pas de departement';
        }
    }

    public function verifierNumSerie($numserie){
        try {
            $sql = "SELECT id FROM carte_stock WHERE num_serie = :numserie AND etatvente = 0";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam('numserie', $numserie);
            $user->execute();
            if($user->rowCount() === 1){
                return 1;
            }
            else{
                return -1;
            }
        } catch (\PDOException $exception) {
            return $exception;
        }
    }

    public function venteCarte($numserie, $agence){
        try {
            $sql = "UPDATE FROM carte_stock SET etatvente = 1, agencevendeur = :agence WHERE num_serie = :numserie AND etatvente = 0";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam('agence', $agence);
            $user->bindParam('numserie', $numserie);
            $user->execute();
            if($user->rowCount() === 1){
                return 1;
            }
            else{
                return -1;
            }
        } catch (\PDOException $exception) {
            return -1;
        }
    }

    public function verifierBeneficiaire($serie, $tel){
        try{
            $sql = "SELECT carte.`rowid`
			FROM carte
			WHERE carte.numero_serie = :numero
			OR carte.telephone = :tel";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "numero" => $serie,
                    "tel" => $tel,
                )
            );
            $a = $user->rowCount();
            if($a > 0)
                return -1;
            else
                return 1;
        }
        catch(Exception $e){
            return -1;
        }
    }

    public function insertBeneficiaire($prenom, $prenom1, $nom, $sexe, $datenais, $email, $profession, $adresse,
                                       $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                                       $nationalite, $region,$departement, $codepostal, $telfixe,
                                       $commentaire, $errorCodes, $errorMessage,$user_creation,$agence,$date){
        try{
            $mot_passe='';
            $code_sms='';
            $code_marchand='';
            $cle_marchand='';
            $registrationCustomerId='';
            $sql = "INSERT INTO beneficiaire (nom, prenom, cni, date_delivrance, adresse, telephone_fixe,
                    email, mot_de_passe, date_nais, commentaire, statut, code_sms,
                    prenom1, adress1, adress2, codepostal, sexe, fk_pays, fk_profession, fk_nationalite,
                    fk_typecni, fk_region, fk_ville, fk_agence, code_marchand, cle_marchand,
                    registrationCustomerId,user_creation,
                    date_creation, errorCodes, errorMessage)
	    			
	    			VALUES ('".$nom."', '".$prenom."', '".$piece."', '".$datedelivrancepiece."', '".$adresse."', '".$telfixe."', '".$email."', '".$mot_passe."',
	    			'".$datenais."', '".$commentaire."', 1, '".$code_sms."',  '".$prenom1."', '".$adresse1."',
	    			'".$adresse2."' ,'".$codepostal."', '".$sexe."', '".$pays."', '".$profession."', '".$nationalite."',
	    			'".$typepiece."', '".$region."','".$departement."','".$agence."', '".$code_marchand."', '".$cle_marchand."', '".$registrationCustomerId."',
	    			'".$user_creation."', '".$date."', '".$errorCodes."', '".$errorMessage."')";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            $lastBeneficiare = $this->lastInsertBeneficiaire($user_creation,$agence);
                return $lastBeneficiare;
        }
        catch(Exception $e){
            return -99;
        }

    }

    public function insertCarte($idbeneficiaire, $cardId, $typecarte, $telephone, $embossage, $numeroserie,
                                $dateexpirationcarte,$user_creation,$agence,$date){
        try{

            $cutim='';
            $sql = "INSERT INTO carte (numero, date_expiration, numero_serie, statut, beneficiaire_rowid,
                    telephone,typecompte, user_crea, date_activation,embossage,type_carte,cutim,fk_agence)
					VALUES ('".$cardId."', '".$dateexpirationcarte."', '".$numeroserie."', 1, '".$idbeneficiaire."', '".$telephone."',1, '".$user_creation."',
					'".$date."', '".$embossage."', '".$typecarte."','".$cutim."', '".$agence."')";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            return $res;

        }
        catch(Exception $e){
            return -99;
        }

    }

    public function insertEchecBenef($prenom, $prenom1, $nom, $sexe, $datenais, $email, $profession, $adresse,
                                     $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                                     $nationalite, $region,$departement, $codepostal, $telfixe,
                                     $commentaire, $errorCodes, $errorMessage,$user_creation,$agence,$date){
        try{
            $mot_passe='';
            $code_sms='';
            $code_marchand='';
            $cle_marchand='';
            $registrationCustomerId='';
            $sql = "INSERT INTO beneficiaire_echec (nom, prenom, cni, date_delivrance, adresse, telephone_fixe,
                    email, mot_de_passe, date_nais, commentaire, statut, code_sms,
                    prenom1, adress1, adress2, codepostal, sexe, fk_pays, fk_profession, fk_nationalite,
                    fk_typecni, fk_region, fk_ville, fk_agence, code_marchand, cle_marchand,
                    registrationCustomerId,user_creation,
                    date_creation, errorCodes, errorMessage)
	    			VALUES ('".$nom."', '".$prenom."', '".$piece."', '".$datedelivrancepiece."', '".$adresse."', '".$telfixe."', '".$email."', '".$mot_passe."',
	    			'".$datenais."', '".$commentaire."', 1, '".$code_sms."',  '".$prenom1."', '".$adresse1."',
	    			'".$adresse2."' ,'".$codepostal."', '".$sexe."', '".$pays."', '".$profession."', '".$nationalite."',
	    			'".$typepiece."', '".$region."','".$departement."','".$agence."', '".$code_marchand."', '".$cle_marchand."', '".$registrationCustomerId."',
	    			'".$user_creation."', '".$date."', '".$errorCodes."', '".$errorMessage."')";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            $lastBeneficiare = $this->lastInsertBeneficiaire($user_creation,$agence);
            if($res){
                return $lastBeneficiare;
            }
            else{
                return 0;
            }
        }
        catch(Exception $e){
            echo 'Error: -99'.$e;
        }

    }

    public function insertEchecCarte($idbeneficiaire, $cardId, $typecarte, $telephone, $embossage, $numeroserie,
                                     $dateexpirationcarte,$user_creation,$agence,$date){
        try{
            $cutim='';
            $sql = "INSERT INTO carte (numero, date_expiration, numero_serie, statut, beneficiaire_rowid,
                    telephone,typecompte, user_crea, date_activation,embossage,type_carte,cutim,fk_agence)
					VALUES ('".$cardId."', '".$dateexpirationcarte."', '".$numeroserie."', 1, '".$idbeneficiaire."', '".$telephone."',1, '".$user_creation."',
					'".$date."', '".$embossage."', '".$typecarte."','".$cutim."', '".$agence."')";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            return $res;
        }
        catch(Exception $e){
            echo $e;
        }

    }
    
    public function updateBeneficiaire($prenom1,$profession, $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                                       $nationalite, $region,$departement, $codepostal, $telfixe,
                                       $commentaire, $errorCodes, $errorMessage,$user_modification,$agence,$date_modification,$idBenef){
        try{
            $code_sms='';
            $code_marchand='';
            $cle_marchand='';
            $registrationCustomerId='';
            $sql = "UPDATE beneficiaire SET
                    cni='".$piece."',date_delivrance='".$datedelivrancepiece."',telephone_fixe='".$telfixe."',adress2='".$adresse2."',adress1='".$adresse1."',commentaire='".$commentaire."',statut=1,code_sms='".$code_sms."',
                    prenom1='".$prenom1."',codepostal='".$codepostal."',fk_pays='".$pays."',fk_profession='".$profession."',fk_nationalite='".$nationalite."',fk_typecni='".$typepiece."',fk_region='".$region."',
                    fk_ville='".$departement."',fk_agence='".$agence."',code_marchand='".$code_marchand."',cle_marchand='".$cle_marchand."',registrationCustomerId='".$registrationCustomerId."',user_modification='".$user_modification."',
                    date_modification='".$date_modification."',errorCodes='".$errorCodes."',errorMessage='".$errorMessage."'
                    WHERE rowid='".$idBenef."' ";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            return $res;
        }
        catch(Exception $e){
            return -99;
        }

    }

    public function updateCarte($cardId, $typecarte, $telephone, $embossage, $numeroserie,
                                $dateexpirationcarte,$user_modif,$agence,$date, $idcarte){
        try{

            $cutim='';
            $sql = "UPDATE carte SET 
                    numero='".$cardId."',date_expiration= '".$dateexpirationcarte."',numero_serie='".$numeroserie."',typecompte=1,user_modif='".$user_modif."',date_modif='".$date."',
                    date_activation='".$date."',embossage='".$embossage."',type_carte='".$typecarte."',cutim='".$cutim."',fk_agence='".$agence."'
                   WHERE telephone='".$telephone."' AND rowid = ".$idcarte;
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            return $res;

        }
        catch(Exception $e){
            return -99;
        }

    }

    public function updateCarteEnroler($cardId, $typelot, $etatvente, $datevente,$agence){
        try{
            $sql = "UPDATE carte_stock SET 
                    typelot='".$typelot."',etatvente= '".$etatvente."',datevente='".$datevente."',agence_vendeur='".$agence."'
                   WHERE num_serie=".$cardId;
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute();
            return $res;

        }
        catch(Exception $e){
            return -99;
        }

    }

    public function lastInsertBeneficiaire($user_creation,$agence){
        try
        {
            $query_rs_Resultat = "SELECT rowid
						FROM beneficiaire
						WHERE fk_agence = '".$agence."'
						AND user_creation ='".$user_creation."'
						ORDER BY rowid DESC LIMIT 1";
            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->execute();
            $row_rq = $resultat->fetchObject();
            $totalRows_rs_Resultat  = $resultat->rowCount();
            if($totalRows_rs_Resultat > 0)
                return  $row_rq->rowid;
            else
                return -99;
        }
        catch(PDOException $e)
        {
            return -99;
        }
    }

    public function lastInsertCarte($user_creation,$agence){
        try
        {
            $query_rs_Resultat = "SELECT rowid
						FROM carte
						WHERE fk_agence = '".$agence."'
						AND user_crea ='".$user_creation."'
						ORDER BY rowid DESC LIMIT 1";
            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->execute();
            $row_rq = $resultat->fetchObject();
            $totalRows_rs_Resultat  = $resultat->rowCount();
            if($totalRows_rs_Resultat > 0)
                return  $row_rq->rowid;
            else
                return -99;
        }
        catch(PDOException $e)
        {
            return -99;
        }
    }

    public function CarteExiste($code, $agence, $typecarte)
    {
        try{
            $query_rs_Resultat = "SELECT *
                                  FROM carte_stock
                                   WHERE num_serie = :code";

            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->bindParam("code",$code);
            $resultat->execute();
            $totalRows_rs_Resultat  = $resultat->rowCount();

            if($totalRows_rs_Resultat === 1)
            {
                $ligne = $resultat->fetchObject();

                if((int)$ligne->idagence === (int)$agence){
                    if((int)$ligne->typecarte === (int)$typecarte){
                        if($ligne->etatvente == 0){
                            if($ligne->typelot == 1 && ($ligne->niveau == 'AGENCE' || $ligne->niveau == 'PARTENAIRE') ){
                                return 1;
                            }
                            else{
                                return 5;
                            }
                        }
                        else{
                            return 4;
                        }
                    }
                    else{
                        return 3;
                    }
                }
                else{
                    return 2;
                }
            }
            else
            {
                return 0;
            }
        }
        catch(Exception $e)
        {
            return 0;
        }
    }

    public function lotDistribution($agence, $num_serie)
    {
        try
        {
            $query_rs_Resultat = "SELECT lotcarte.idlotcarte
						FROM lotcarte
						WHERE lotcarte.stock > 0
						AND lotcarte.num_debut <=:idlot
						AND lotcarte.num_fin >=:idlot1
						AND lotcarte.idagence =:agence";

            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->execute(
                array(
                    "idlot" => $num_serie,
                    "idlot1" => $num_serie,
                    "agence" => $agence,
                )
            );
            $row_rq_vente = $resultat->fetchObject();
            $totalRows_rs_Resultat  = $resultat->rowCount();
            if($totalRows_rs_Resultat > 0)
                $return = $row_rq_vente->idlotcarte;
            else
                $return = 0;
        }
        catch(PDOException $e)
        {
            echo 'Error: -99';
        }
        return $return;
    }

    public function getLotCarte($numeroserie,$agence){
        try{
            $query_rs_Resultat = "SELECT num_debut, num_fin
						FROM lotcarte_reception
						WHERE lotcarte_reception.agence_retour =:agence
						AND lotcarte_reception.num_debut <=:idlot
						AND lotcarte_reception.num_fin >=:idlot1";

            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->execute(
                array(
                    "idlot" => $numeroserie,
                    "idlot1" => $numeroserie,
                    "agence" => $agence,
                )
            );
            $row_rq_vente = $resultat->fetchObject();
            $totalRows_rs_Resultat  = $resultat->rowCount();

            if($totalRows_rs_Resultat > 0) return $row_rq_vente->idlotcarte_recu;
            else return 0;
        }
        catch(PDOException $e)
        {
            return -2;
        }
    }

    public function lotDistribution2($agence, $num_serie)
    {
        try
        {
            $req2="SELECT numero_serie FROM carte WHERE fk_agence =:idagence AND (CAST(numero_serie AS SIGNED INTEGER ) BETWEEN :debut AND :fin) ORDER BY numero_serie ASC";

            $query_rs_Resultat = "SELECT num_debut, num_fin
						FROM lotcarte_reception
						WHERE lotcarte_reception.agence_retour =?
						AND lotcarte_reception.num_debut <=?
						AND lotcarte_reception.num_fin >=?";
            $resultat = $this->getConnexion()->prepare($query_rs_Resultat);
            $resultat->execute([$agence,$num_serie,$num_serie]);
            $totalRows_rs_Resultat  = $resultat->rowCount();
            return $resultat->rowCount();

        }
        catch(PDOException $e)
        {
            return  -99;
        }

    }

    public function regionourEnrollement($idregion){
        try
        {
            $query_rs_region = "SELECT lib2 FROM region WHERE region.idregion =:region ";
            $region = $this->getConnexion()->prepare($query_rs_region);
            $region->execute(array(
                "region" => intval($idregion),
            ));
            $rs_region = $region->fetchObject();

            return $rs_region->lib2;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }
    }

    public function getRegionsByIdPays($pays)
    {
        try
        {
            $query_rs_region = "SELECT idregion, lib_region FROM region WHERE fk_pays = :region ";
            $region = $this->getConnexion()->prepare($query_rs_region);
            $region->execute(array(
                "region" => intval($pays),
            ));
            $rs_region = $region->fetchAll(PDO::FETCH_OBJ);
            return json_encode($rs_region);
        }
        catch(Exception $e)
        {
            return [];
        }
    }

    public function paysPourEnrollement($idpays){
        try
        {
            $query_rs_pays = "SELECT alpha2 FROM pays WHERE pays.id = :pays";
            $pays = $this->getConnexion()->prepare($query_rs_pays);
            $pays->execute(array(
                "pays" => intval($idpays),
            ));
            $rs_pays = $pays->fetchObject();

            return $rs_pays->alpha2;
        }
        catch(Exception $e)
        {

            echo 'Error: -99'; die;
        }
    }

    public function cniPourEnrollement($idcni){
        try
        {
            $query_rs_cni= "SELECT id_typecni FROM typecni WHERE (typecni.lib_typecni=:lib_typecni OR id_typecni = :id_typecni)";
            $cni = $this->getConnexion()->prepare($query_rs_cni);
            $cni->execute(array(
                "lib_typecni" => $idcni,
                "id_typecni" => $idcni,
            ));
            $rs_cni = $cni->fetchObject();

            return $rs_cni->id_typecni;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'.$e; die;
        }
    }

    public function dateFormatEnrollement($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);
            switch ($mm) {
                case "01":
                    $mm = "JAN";
                    break;
                case "02":
                    $mm = "FEB";
                    break;
                case "03":
                    $mm = "MAR";
                    break;
                case "04":
                    $mm = "APR";
                    break;
                case "05":
                    $mm = "MAY";
                    break;
                case "06":
                    $mm = "JUN";
                    break;
                case "07":
                    $mm = "JUL";
                    break;
                case "08":
                    $mm = "AUG";
                    break;
                case "09":
                    $mm = "SEP";
                    break;
                case "10":
                    $mm = "OCT";
                    break;
                case "11":
                    $mm = "NOV";
                    break;
                case "12":
                    $mm = "DEC";
                    break;
            }
            $date_fr = $jj.'-'.$mm.'-'.$aa;
        }
        return $date_fr;
    }

    public function vendreCarte($cardId, $serie,$user_creation,$agence,$date){
        $serie = strval($serie);
        try{
            $sql = "INSERT INTO carte_active (numero_carte, numero_serie, fk_agence, fk_user, date_operation)
					VALUES (:numero_carte, :numero_serie, :fk_agence, :fk_user, :date_operation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(
                array(
                    "numero_carte"=>$cardId,
                    "numero_serie"=>$serie,
                    "fk_agence"=>$agence,
                    "fk_user"=>$user_creation,
                    "date_operation"=>$date
                )
            );

            return $res;
        }
        catch(PDOException $e){
            echo $e;
        }

    }

    public function updateLotDistribution($idlot){
        $idlot = intval($idlot);
        try{
            $sql = "UPDATE lotcarte SET stock = stock - 1 WHERE idlotcarte = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(
                array(
                    "id"=>$idlot
                )
            );
            return $res;
        }
        catch(PDOException $e){
            echo $e;
        }

    }

    public function insertJournal($action, $object, $commentaires, $type,$user_creation){

        try {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO action_utilisateur( date, action, action_object, IDUSER, commentaire, type) VALUES(:dat, :act, :obj,  :iduser, :comment, :typ)";
            $req = $this->getConnexion()->prepare($sql);
            $res = $req->execute(array(
                "dat" => strval($date_creation),
                "act" => strval($action),
                "obj" => strval($object),
                "iduser" => intval($user_creation),
                "comment" => strval($commentaires),
                "typ" => intval($type),

            ));
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }
        return $res;

    }

    //retourner CustomerId 7 premiers caracteres de numero
    public function returnCustomerId($cardid)
    {
        if(strlen($cardid) > 7 ) return substr($cardid, 0, 7);
        else return -1;
    }

    public function returnLast4Digits($cardid)
    {
        if(strlen($cardid) > 4 ) return substr($cardid, -4);
        else return -1;
    }

    /**************Fichier log txt********************************************/
    public function ecrire_journal($errtxt){

        if (!file_exists(__DIR__."/../logs/".date('Y')))
        {
            mkdir (__DIR__."/../logs/".date('Y'), 0777);

        }
        if (!file_exists(__DIR__."/../logs/".date('Y')."/".date('m')))
        {
            mkdir (__DIR__."/../logs/".date('Y')."/".date('m'), 0777);

        }


        $fp = fopen(__DIR__."/../logs/".date('Y')."/".date('m')."/".date("d_m_Y")."".".txt",'a+'); // ouvrir le fichier ou le créer
        fseek($fp,SEEK_END); // poser le point de lecture à la fin du fichier
        $nouvel_ligne=$errtxt."\r\n"; // ajouter un retour à la ligne au fichier
        fputs($fp,$nouvel_ligne); // ecrire ce texte
        fclose($fp); //fermer le fichier
    }

    public function log_journal($action, $objet, $comment, $type,$user_creation,$user_prenom,$user_nom){
        $this->insertJournal($action, $objet, $comment, $type,$user_creation);
        $log="Date:".date('d-m-Y H:i:s')." - Utilisateur:".$user_prenom." ".$user_nom."- Module: Gestion Carte - Action:".$action." - Object:".$objet." - IP:". $_SERVER["REMOTE_ADDR"];
        $this->ecrire_journal($log);

    }

    public function insertTransaction($num_transac, $montant, $statut, $service, $carte, $errorCode, $commission, $transactionID,$user_creation,$agence,$date){

        try {
            $sql = "INSERT INTO transaction (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_carte, commentaire, commission, fk_agence, transactionID)
		   VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_carte, :commentaire, :commission, :agence, :transactionID)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => strval($num_transac),
                "date_transaction" => strval($date),
                "montant" => intval($montant),
                "statut" => intval($statut),
                "fkuser" => intval($user_creation),
                "fk_service" => intval($service),
                "fk_carte" => strval($carte),
                "commentaire" => strval($errorCode),
                "commission" => $commission,
                "agence" => intval($agence),
                "transactionID" => strval($transactionID)
            ));
            return $res;
        }
        catch(Exception $e){
            echo -99; die;
        }
    }

    public function enrollerCarte($prenom, $prenom1, $nom, $sexe, $datenais, $email, $profession, $adresse, $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays, $nationalite, $region, $departement, $codepostal, $telfixe, $typecarte, $telephone, $embossage, $numeroserie, $dateexpirationcarte, $commentaire, $user_creation, $agence, $prenomuser, $nomuser, $idtransaction){
        @require_once (__DIR__.'/../../vendor/ApiGTP/ApiBanque.php');
        $date = date('Y-m-d H:i:s');



        $regionenrollement = $this->regionourEnrollement($region);
        $paysenrollement = $this->paysPourEnrollement($pays);
        $typecnienrollement = $this->cniPourEnrollement($typepiece);
        $lastName = $this->trimUltime($nom);
        $firstName = $this->trimUltime($prenom);
        $datenaisInsert = $datenais;
        $datenais = $this->dateFormatEnrollement($datenais);
        if($prenom1 != '')
            $middleName = $this->trimUltime($prenom1);
        else
            $middleName = '';

        if($adresse1 != '')
            $adresse1 = $this->trimUltime($adresse1);
        else
            $adresse1 = '';

        if($adresse2 != '')
            $adresse2 = $this->trimUltime($adresse2);
        else
            $adresse2 = '';
        $cni = $this->trimUltime($piece);
        if($email != '')
            $email = $this->trimUltime($email);
        else
            $email = '';

        if($embossage != '')
            $preferedName = $this->trimUltime($embossage);
        else
            $preferedName = $firstName.$lastName;

        if($codepostal != '')
            $codepostal = $this->trimUltime($codepostal);
        else
            $codepostal = $agence;

        $mobileNumber = $this->trimUltime($telephone);
        $adresse = $this->trimUltime($adresse);


        $SubCompany = 1189447;
        $CustomerSource = 'OTHER';


        $donnees = $numeroserie . "-" . $firstName . "-" . $middleName . "-" . $lastName . "-" . $preferedName . "-" . $adresse . "-" . $adresse1 . "-" . $adresse2 . "-" . $regionenrollement . "-" . $paysenrollement . "-" . $regionenrollement . "-" . $codepostal . "-" . $datenais . "-" . $typecnienrollement . "-" . $cni . "-" . $mobileNumber . "-" . $email;
        $numeroacarte = $this->CarteExiste($numeroserie, $agence, $typecarte);


        if($numeroacarte === 1){
            $verifben = $this->verifierBeneficiaire($numeroserie, $telephone);

            if ($verifben === 1) {
                $errorCodes = -1;
                $api = new ApiBAnque();
                $numeroserie2 = (int)$numeroserie;
                $card = @$api->RegistrationCard($numeroserie2, $idtransaction, $firstName, $middleName, $lastName, $preferedName,
                    $adresse, $adresse1, $adresse2, $regionenrollement, $paysenrollement, $regionenrollement,
                    $codepostal, $datenais, $typecnienrollement, $cni, $mobileNumber, $email, $CustomerSource, $SubCompany);

                $montant = 0;
                if ($typecarte == 1) {
                    $montant = 3000;
                    $commision = 2000;
                    $service = 18;
                }
                if ($typecarte == 2) {
                    $montant = 6000;
                    $commision = 4000;
                    $service = 19;
                }

                $json = json_decode($card);

                if(is_object($json))
                {
                    if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                        $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                        $this->insertTransaction($idtransaction, $montant, 1, $service, $numeroserie, $errorCodes.' '.$errorMessage, $commision,'0000ECHEC',$user_creation,$agence,$date);
                        $this->log_journal('Enrollement beneficiaire', $donnees,'Enrollement beneficiaire echoue ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                        return $card;
                    }
                    elseif(isset($json->{'ResponseData'}->{'RegistrationCustomerID'}))//if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $cardId = $json->{'ResponseData'}->{'RegistrationCustomerID'}.$json->{'ResponseData'}->{'RegistrationLast4Digits'};

                        $result1 = $this->insertBeneficiaire(
                            $prenom, $prenom1, $nom, $sexe, $datenaisInsert, $email, $profession, $adresse,
                            $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                            $nationalite, $region,$departement, $codepostal, $telfixe,
                            $commentaire, $errorCodes, $errorMessage= 'ok',$user_creation,$agence,$date);
                        $lastCarte=0;

                        if ($result1 > 0) {
                            $enrol=$this->updateCarteEnroler($numeroserie, 3, 1, $date,$agence);
                            $result2 = $this->insertCarte($result1, $cardId, $typecarte, $telephone, $embossage, $numeroserie,$dateexpirationcarte,$user_creation,$agence,$date);
                            if ($result2) {
                                $lastCarte=$this->lastInsertCarte($user_creation,$agence);
                                $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                                $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire réussi','',$user_creation,$prenomuser,$nomuser);
                                return 1;
                            }
                            else {
                                $this->insertCarte($result1, $cardId, $typecarte, $telephone, $embossage, $numeroserie, $dateexpirationcarte,$user_creation,$agence,$date);
                                $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                                $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire reussi mais elle n\'a pas ete enregistre dans la table carte','',$user_creation,$prenomuser,$nomuser);
                                return 2; //Erreur insertion carte
                            }
                        }
                        else {
                            $this->insertEchecBenef($prenom, $prenom1, $nom, $sexe, $datenaisInsert, $email, $profession, $adresse,
                                $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                                $nationalite, $region,$departement, $codepostal, $telfixe,
                                $commentaire, $errorCodes, $errorMessage= 'Erreur insertion beneficiaire',$user_creation,$agence,$date);
                            $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire reussi mais elle n\'a pas ete enregistre dans la table beneficiare et ni celle de carte','',$user_creation,$prenomuser,$nomuser);
                            return 3; //Erreur insertion beneficiaire
                        }
                    }else{
                        $this->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision,'',$user_creation,$agence,$date);
                        $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                        return 4; //Erreur web service
                    }
                }
                else{
                    $this->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision,'',$user_creation,$agence,$date);
                    $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                    return 4; //Erreur web service
                }
            }
            else {
                $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, le numero de telephone ou le numero de serie existe deja dans la base','',$user_creation,$prenomuser,$nomuser);
                return 5;
            }
        }
        else if($numeroacarte === 2) {
            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, la carte ne peut pas etre active par cette agence','',$user_creation,$prenomuser,$nomuser);
            return 22; //Agence non autorise a activer la carte
        }
        else if($numeroacarte === 3) {
            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, le type de carte est incorrect','',$user_creation,$prenomuser,$nomuser);
            return 33; //Type de carte est incorrect
        }
        else if($numeroacarte === 4) {
            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, la carte est deja vendue','',$user_creation,$prenomuser,$nomuser);
            return 44; //Carte deja vendue
        }
        else if($numeroacarte === 5) {
            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, la carte n\'est pas autorise a etre activer','',$user_creation,$prenomuser,$nomuser);
            return 55; //Carte non autorise a etre active
        }
        else {
            $this->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, le numero de serie inexistant ','',$user_creation,$prenomuser,$nomuser);
            return 66; //Erreur numero de carte
        }
    }

    public function deplacementFonds($telephone, $numtrans, $user_creation, $prenomuser, $nomuser){
        try
        {
            $query_rs_cni= "SELECT numero, numero_serie, solde FROM carte WHERE telephone = :tel AND typecompte = 1 AND statut = 1 AND etat = 1";
            $cni = $this->getConnexion()->prepare($query_rs_cni);
            $cni->execute(array(
                "tel" => $telephone,
            ));
            $nb_carte = $cni->rowCount();
            if($nb_carte === 1){
                $carte = $cni->fetchObject();

                if($carte->solde > 0){
                    @require_once (__DIR__.'/../../vendor/ApiGTP/ApiBanque.php');
                    $date = date('Y-m-d H:i:s');
                    $api = new ApiBAnque();
                    $currency = 'XOF';
                    $memo = 'Transfertdefonds';
                    $customerId = $this->returnCustomerId($carte->numero);
                    $last4digit = $this->returnLast4Digits($carte->numero);
                    $json = NULL;
                    $return = json_decode($json);
                    $response = $return->{'ResponseData'};
                    $donnees = $numtrans.'-'.$customerId.'-'.$last4digit.'-'.$carte->solde.'-'.$telephone;
                    $response = json_encode(array('TransactionID' => '234423232'));
                    if($response !== NULL && is_object($response))
                    {
                        if(array_key_exists('ErrorNumber', $response))
                        {

                            $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                            $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                            $this->log_journal('Deplacement de fonds', $donnees,'Echec liaison carte a compte ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                            return -3;
                        }
                        else
                        {
                            $transactionId = $response->{'TransactionID'};
                            if($transactionId > 0)
                            {
                                $errorMessage = '';
                                $this->log_journal('Deplacement de fonds', $donnees,'Echec liaison carte a compte ' . ': Transaction Id-'.$transactionId.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);

                                $sql = "UPDATE carte SET solde = 0 WHERE telephone = :id";
                                $user = $this->getConnexion()->prepare($sql);
                                return $user->execute(
                                    array(
                                        "id"=>$telephone
                                    )
                                );

                            }
                            else{
                                $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                                $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                                $this->log_journal('Deplacement de fonds', $donnees,'Echec liaison carte a compte ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                                return -5;
                            }

                        }
                    }
                    else
                    {
                        $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                        $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                        $this->log_journal('Deplacement de fonds', $donnees,'Echec liaison carte a compte ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                        return -4;
                    }
                }

            }
            else
                return -2;


        }
        catch(Exception $e)
        {
            echo 'Error: -99'.$e; die;
        }
    }

    public function lierCarteACompte($prenom, $prenom1, $nom, $sexe, $datenais, $email, $profession, $adresse, $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays, $nationalite, $region, $departement, $codepostal, $telfixe, $typecarte, $telephone, $embossage, $numeroserie, $dateexpirationcarte, $commentaire, $user_creation, $agence, $prenomuser, $nomuser, $idtransaction,$idBenef, $idcarte){
        @require_once (__DIR__.'/../../vendor/ApiGTP/ApiBanque.php');
        $date = date('Y-m-d H:i:s');
        $api = new ApiBAnque();


        $regionenrollement = $this->regionourEnrollement($region);
        $paysenrollement = $this->paysPourEnrollement($pays);
        $typecnienrollement = $this->cniPourEnrollement($typepiece);
        $lastName = $this->trimUltime($nom);
        $firstName = $this->trimUltime($prenom);
        $datenaisInsert = $datenais;
        $datenais = $this->dateFormatEnrollement($datenais);
        if($prenom1 != '')
            $middleName = $this->trimUltime($prenom1);
        else
            $middleName = '';

        if($adresse1 != '')
            $adresse1 = $this->trimUltime($adresse1);
        else
            $adresse1 = '';

        if($adresse2 != '')
            $adresse2 = $this->trimUltime($adresse2);
        else
            $adresse2 = '';
        $cni = $this->trimUltime($piece);
        if($email != '')
            $email = $this->trimUltime($email);
        else
            $email = '';

        if($embossage != '')
            $preferedName = $this->trimUltime($embossage);
        else
            $preferedName = $firstName.$lastName;

        if($codepostal != '')
            $codepostal = $this->trimUltime($codepostal);
        else
            $codepostal = $agence;

        $mobileNumber = $this->trimUltime($telephone);
        $adresse = $this->trimUltime($adresse);


        $SubCompany = 1189447;
        $CustomerSource = 'OTHER';


        $donnees = $numeroserie . "-" . $firstName . "-" . $middleName . "-" . $lastName . "-" . $preferedName . "-" . $adresse . "-" . $adresse1 . "-" . $adresse2 . "-" . $regionenrollement . "-" . $paysenrollement . "-" . $regionenrollement . "-" . $codepostal . "-" . $datenais . "-" . $typecnienrollement . "-" . $cni . "-" . $mobileNumber . "-" . $email. "-" . $idcarte;

        $numeroacarte = $this->CarteExiste($numeroserie, $agence, $typecarte);

        if ($numeroacarte === 1) {
                $errorCodes = -1;
                $card = @$api->RegistrationCard((int)$numeroserie, $idtransaction, $firstName, $middleName, $lastName, $preferedName,
                    $adresse, $adresse1, $adresse2, $regionenrollement, $paysenrollement, $regionenrollement,
                    $codepostal, $datenais, $typecnienrollement, $cni, $mobileNumber, $email, $CustomerSource, $SubCompany);


                $montant = 0;
                if ($typecarte == 1) {
                    $montant = 3000;
                    $commision = 2000;
                    $service = 18;
                }
                if ($typecarte == 2) {
                    $montant = 6000;
                    $commision = 4000;
                    $service = 19;
                }

                $json = json_decode($card);

                if(is_object($json))
                {
                    if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                        $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                        $this->insertTransaction($idtransaction, $montant, 1, $service, $numeroserie, $errorCodes.' '.$errorMessage, $commision,'0000ECHEC',$user_creation,$agence,$date);
                        $this->log_journal('Lier carte a compte', $donnees,'Echec liaison carte a compte ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                        return $card;
                    }
                    elseif(isset($json->{'ResponseData'}->{'RegistrationCustomerID'}))//if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $cardId = $json->{'ResponseData'}->{'RegistrationCustomerID'}.$json->{'ResponseData'}->{'RegistrationLast4Digits'};


                        $result1 = $this->updateBeneficiaire($prenom1,$profession, $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                            $nationalite, $region,$departement, $codepostal, $telfixe,
                            $commentaire, $errorCodes, $errorMessage= 'ok',$user_creation,$agence,$date,$idBenef);
                        $lastCarte=0;

                        if ($result1 > 0) {
                            $result2 = $this->updateCarte($cardId, $typecarte, $telephone, $embossage, $numeroserie, $dateexpirationcarte,$user_creation,$agence,$date, $idcarte);

                            if ($result2) {
                                $lastCarte=$this->lastInsertCarte($user_creation,$agence);
                                $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$cardId,$user_creation,$agence,$date);
                                $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte réussi','',$user_creation,$prenomuser,$nomuser);
                                $this->deplacementFonds($telephone, $idtransaction, $user_creation, $prenomuser, $nomuser);
                                return 1;
                            }
                            else {
                                $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$cardId,$user_creation,$agence,$date);
                                $this->log_journal('Lier carte a compte', $donnees, 'Liaison carte a compte reussi mais elle n\'a pas ete enregistre dans la table carte','',$user_creation,$prenomuser,$nomuser);
                                return 2; //Erreur insertion carte
                            }
                        }
                        else {
                            $this->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$cardId,$user_creation,$agence,$date);
                            $this->log_journal('Lier carte a compte', $donnees, 'Liaison carte a compte reussi mais elle n\'a pas ete enregistre dans la table beneficiare et ni celle de carte','',$user_creation,$prenomuser,$nomuser);
                            return 3; //Erreur insertion beneficiaire
                        }
                    }
                    else{
                        $this->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision,'',$user_creation,$agence,$date);
                        $this->log_journal('Lier carte a compte', $donnees, 'Echec liaison carte a compte' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                        return 4; //Erreur web service
                    }
                }
                else{
                    $this->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision,'',$user_creation,$agence,$date);
                    $this->log_journal('Lier carte a compte', $donnees, 'Echec liaison carte a compte' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                    return 4; //Erreur web service
                }
        }
        else if($numeroacarte === 2) {
            $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte echoue, la carte ne peut pas etre active par cette agence','',$user_creation,$prenomuser,$nomuser);
            return 22; //Agence non autorise a activer la carte
        }
        else if($numeroacarte === 3) {
            $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte echoue, le type de carte est incorrect','',$user_creation,$prenomuser,$nomuser);
            return 33; //Type de carte est incorrect
        }
        else if($numeroacarte === 4) {
            $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte echoue, la carte est deja vendue','',$user_creation,$prenomuser,$nomuser);
            return 44; //Carte deja vendue
        }
        else if($numeroacarte === 5) {
            $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte echoue, la carte n\'est pas autorise a etre activer','',$user_creation,$prenomuser,$nomuser);
            return 55; //Carte non autorise a etre active
        }
        else {
            $this->log_journal('Liaison carte a compte', $donnees, 'Liaison carte a compte echoue, le numero de serie inexistant ','',$user_creation,$prenomuser,$nomuser);
            return 66; //Erreur numero de carte
        }

    }

    public function beneficiaireByNumeroTel($serie){
        try{

            $sql = "SELECT beneficiaire.`rowid`, beneficiaire.nom, beneficiaire.prenom, beneficiaire.cni, beneficiaire.adresse, beneficiaire.date_nais, beneficiaire.sexe,
			beneficiaire.email, carte.`rowid` as idcarte, carte.numero_serie, carte.numero, carte.date_expiration, carte.telephone, carte.type_carte, carte.embossage,
			carte.date_activation , carte.statut as cartestatut
			FROM beneficiaire
			LEFT OUTER JOIN carte
			ON beneficiaire.`rowid` = carte.beneficiaire_rowid
			WHERE carte.telephone = '".$serie."'";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetch();
            return $a;
        }
        catch(Exception $e){
            echo 'Error: -99';
        }
    }

    public function isValideModel($data = [])
    {
        try
        {
            $sql = "SELECT rowid FROM beneficiaire ";
            if(count($data) > 0){
                $temp = $data;
                $champs = array_keys($temp);
                $champs = array_map(function($one){return $one.'=:'.$one;},$champs);
                $sql.=" WHERE ".implode(' AND ',$champs);
            }
            $user = $this->getConnexion()->prepare($sql);
            $user->execute($data);
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return (count($a) > 0);
        }catch (PDOException $exception){
            return false;
        }
    }

}