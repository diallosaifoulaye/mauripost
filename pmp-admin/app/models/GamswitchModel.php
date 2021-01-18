<?php

require_once __DIR__.'/Utilisateur.class.php';
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class GamswitchModel extends \app\core\BaseModel
{

    public function  transactionJour ($date1, $date2,  $type_profil,$user,$agence)
    {
        //$utils = new Utils_Gamswitch();
        $next = '';
        $where = '';

       // $type_profil=$utils->typeProfil($_SESSION['profil']);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        if($type_profil==3)
        {
            $where.=" AND h.user=".$user;
        }
        if($type_profil==2 || $type_profil==3 || $type_profil==4)
        {
            $where.=" AND h.agence=".$agence;
        }


        $sql = "SELECT DISTINCT h.id, h.num_transaction, h.compteur, h.montant, h.frais, h.statut,
			h.date_vente, h.order_number, h.montant_total, h.nom as client, user.prenom, user.nom, agence.label as nom_agence
			FROM histoAchatCodeGamswitch h, user, agence
			WHERE h.statut = 1
			AND DATE(h.date_vente) >=:date1 
			AND DATE(h.date_vente) <=:date2 
			AND h.user = user.rowid 
			AND user.fk_agence = agence.rowid ".$where."";

        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
           // var_dump($user);die;
            $user->execute();

            $rows = $user->fetchAll();
            $totalData = $user->rowCount();
            //$a = $user->fetchAll();
            return $rows;
        }
        catch(Exception $e)
        {
           echo $e;
            echo 'Error: -99'; die;
        }
        //$this->getConnexion()-> =NULL;
    }

    public function  detailTransasction($num_transac){
        $sql = "SELECT DISTINCT t.id, t.num_transaction, t.compteur, t.montant, t.frais, t.telephone,
				t.statut, t.date_vente, t.prenom as client, t.order_number, t.code, t.montant, user.prenom, user.nom, agence.label as nom_agence
				FROM histoAchatCodeGamswitch t, user, agence
				WHERE t.statut = 1
				AND t.num_transaction =:num_transac 
				AND t.user = user.rowid 
				AND user.fk_agence = agence.rowid";
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

/////API
   public function register()
    {
        $ch = curl_init();
        $data = array(
            "Username" => "Numherit",
            "FirstName" => "Numherit",
            "LastName" => "Numherit",
            "Password" => "ToubaM28@1927",
            "PhoneNumber" => "00221775373761",
            "Email" => "bocar@numherit.com"
        );
        $request = json_encode($data);
        $headers = array(
            "Content-Type: application/json"
        );
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/api/account/register");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }

    public function generate_token()
    {
        $ch = curl_init();
        $data = array(
            "Username" => "Numherit",
            "Password" => "ToubaM28@1927",
            "Grant_Type" => "password"
        );
        $request = json_encode($data);
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "Username=Numherit&Password=ToubaM28@1927&Grant_Type=password");
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }


public function customer_validation($MeterNumber,$PhoneNumber,$Amount)
    {
        $nonce = time();
        $url = 'https://197.231.130.7/';
        $key = "17c51a73-3189-4d18-a58b-be2eeac32a59";
        $timestamp = time();
        $token = "RpR-PqK_3EE1IQuBVJyKHip6POnioCm9pAQTjgOjT7WFJhGEqVAcmtjDOpfVufItDV15hJzVI7DFkQoVT0334Fce-IipUfhSI6NAu_QW4p1MKB2YUI-YGqDKnn5bKdVcBMSafTnTzPKtEjWhns-m4dbXC7cDdxVcroL3Qd2UHeVczRN8Ng_Xgk64NPse_jU1N1IOE7g2du4zx25PZ1Wkl6OkfSZpDlWgOYAJY-bhKn-ZzZPQcGqkT2Mcw2dVZNjFe3XbSPo1K7VPYWUWB9lkYg0uAaZ9kEVW7gbUngDEu5G40eeHvZfd9nitKv01KP1stYY8YUFlI-KNRFX8p3EDcRf5G1wW-dBdm1wKUZy2jNLvE6vkSfIugbW1IXf_H25YHNEiELPGa5Z9klg8QYBTemQrtJtxam0N8SUF5KLvUXvRV5ZolRV8BN77OasASipbAzBMgXsnfudnV6xo4QHe_Eu__M3zxN70rF3IkgRWWTZxzSQ6AUpZD0HMT4URrnW5CFKZNRkskXAefwt9EmP6cQ";

        $baseStringToBeSigned = $key."nawec"."consumercheck".$nonce.$timestamp.$MeterNumber;
        $signature = hash('sha512',$baseStringToBeSigned);
        $ch = curl_init();

        $data = array(
            "Type" => "Consumercheck",
            "MeterNumber" => $MeterNumber,
            "PhoneNumber" => $PhoneNumber,
            "Amount" => $Amount
        );
        $request = json_encode($data);
        $headers = array(
            'Authorization: Bearer '.$token,
            "Content-Type: application/json",
            "Accept: application/json",
            "Nonce: ".$nonce,
            "Signature: ".$signature,
            "Timestamp: ".$timestamp
        );
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/api/nawec/gswtest");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        //var_dump($result);die;
       // //echo $error = curl_error($ch);
        return $result;
    }

    public function payment($MeterNumber,$PhoneNumber,$Amount)
    {
        $nonce = time();
        $url = 'https://197.231.130.7/';
        $key = "17c51a73-3189-4d18-a58b-be2eeac32a59";
        $timestamp = time();
        $token = "RpR-PqK_3EE1IQuBVJyKHip6POnioCm9pAQTjgOjT7WFJhGEqVAcmtjDOpfVufItDV15hJzVI7DFkQoVT0334Fce-IipUfhSI6NAu_QW4p1MKB2YUI-YGqDKnn5bKdVcBMSafTnTzPKtEjWhns-m4dbXC7cDdxVcroL3Qd2UHeVczRN8Ng_Xgk64NPse_jU1N1IOE7g2du4zx25PZ1Wkl6OkfSZpDlWgOYAJY-bhKn-ZzZPQcGqkT2Mcw2dVZNjFe3XbSPo1K7VPYWUWB9lkYg0uAaZ9kEVW7gbUngDEu5G40eeHvZfd9nitKv01KP1stYY8YUFlI-KNRFX8p3EDcRf5G1wW-dBdm1wKUZy2jNLvE6vkSfIugbW1IXf_H25YHNEiELPGa5Z9klg8QYBTemQrtJtxam0N8SUF5KLvUXvRV5ZolRV8BN77OasASipbAzBMgXsnfudnV6xo4QHe_Eu__M3zxN70rF3IkgRWWTZxzSQ6AUpZD0HMT4URrnW5CFKZNRkskXAefwt9EmP6cQ";
        $baseStringToBeSigned = $key."nawec"."vend".$nonce.$timestamp.$MeterNumber;
        $signature = hash('sha512',$baseStringToBeSigned);

        $ch = curl_init();

        $data = array(
            "Type" => "Vend",
            "MeterNumber" => $MeterNumber,
            "PhoneNumber" => $PhoneNumber,
            "Amount" => $Amount
        );
        $request = json_encode($data);
        $headers = array(
            'Authorization: Bearer '.$token,
            "Content-Type: application/json",
            "Accept: application/json",
            "Nonce: ".$nonce,
            "Timestamp: ".$timestamp,
            "Signature: ".$signature
        );
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/api/nawec/gswtest");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }

    public function last_token_reprint($MeterNumber,$PhoneNumber,$Amount)
    {
        $nonce = time();
        $url = 'https://197.231.130.7/';
        $key = "17c51a73-3189-4d18-a58b-be2eeac32a59";
        $timestamp = time();
        $token = "RpR-PqK_3EE1IQuBVJyKHip6POnioCm9pAQTjgOjT7WFJhGEqVAcmtjDOpfVufItDV15hJzVI7DFkQoVT0334Fce-IipUfhSI6NAu_QW4p1MKB2YUI-YGqDKnn5bKdVcBMSafTnTzPKtEjWhns-m4dbXC7cDdxVcroL3Qd2UHeVczRN8Ng_Xgk64NPse_jU1N1IOE7g2du4zx25PZ1Wkl6OkfSZpDlWgOYAJY-bhKn-ZzZPQcGqkT2Mcw2dVZNjFe3XbSPo1K7VPYWUWB9lkYg0uAaZ9kEVW7gbUngDEu5G40eeHvZfd9nitKv01KP1stYY8YUFlI-KNRFX8p3EDcRf5G1wW-dBdm1wKUZy2jNLvE6vkSfIugbW1IXf_H25YHNEiELPGa5Z9klg8QYBTemQrtJtxam0N8SUF5KLvUXvRV5ZolRV8BN77OasASipbAzBMgXsnfudnV6xo4QHe_Eu__M3zxN70rF3IkgRWWTZxzSQ6AUpZD0HMT4URrnW5CFKZNRkskXAefwt9EmP6cQ";
        $baseStringToBeSigned = $key."nawec"."reprint".$nonce.$timestamp.$MeterNumber;
        $signature = hash('sha512',$baseStringToBeSigned);

        $ch = curl_init();

        $data = array(
            "Type" => "Reprint",
            "MeterNumber" => $MeterNumber,
            "PhoneNumber" => $PhoneNumber,
        );
        $request = json_encode($data);
        $headers = array(
            'Authorization: Bearer '.$token,
            "Content-Type: application/json",
            "Accept: application/json",
            "Nonce: ".$nonce,
            "Timestamp: ".$timestamp,
            "Signature: ".$signature
        );
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/api/nawec/gswtest");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }

    public function buyCredit($type,$PhoneNumber,$Amount)
    {
        $nonce = time();
        $url = 'https://197.231.130.7/';
        $key = "17c51a73-3189-4d18-a58b-be2eeac32a59";
        $timestamp = time();
        $token = "RpR-PqK_3EE1IQuBVJyKHip6POnioCm9pAQTjgOjT7WFJhGEqVAcmtjDOpfVufItDV15hJzVI7DFkQoVT0334Fce-IipUfhSI6NAu_QW4p1MKB2YUI-YGqDKnn5bKdVcBMSafTnTzPKtEjWhns-m4dbXC7cDdxVcroL3Qd2UHeVczRN8Ng_Xgk64NPse_jU1N1IOE7g2du4zx25PZ1Wkl6OkfSZpDlWgOYAJY-bhKn-ZzZPQcGqkT2Mcw2dVZNjFe3XbSPo1K7VPYWUWB9lkYg0uAaZ9kEVW7gbUngDEu5G40eeHvZfd9nitKv01KP1stYY8YUFlI-KNRFX8p3EDcRf5G1wW-dBdm1wKUZy2jNLvE6vkSfIugbW1IXf_H25YHNEiELPGa5Z9klg8QYBTemQrtJtxam0N8SUF5KLvUXvRV5ZolRV8BN77OasASipbAzBMgXsnfudnV6xo4QHe_Eu__M3zxN70rF3IkgRWWTZxzSQ6AUpZD0HMT4URrnW5CFKZNRkskXAefwt9EmP6cQ";
        $baseStringToBeSigned = $key."airtime".$type.$nonce.$timestamp.$PhoneNumber.$Amount;
        $signature = hash('sha512',$baseStringToBeSigned);

        $ch = curl_init();
        //var_dump($ch);
        $data = array(
            "Type" =>  $type,
            "PhoneNumber" => $PhoneNumber,
            "Amount" => $Amount
        );
        $request = json_encode($data);
      //  var_dump($request);
        $headers = array(
            'Authorization: Bearer '.$token,
            "Content-Type: application/json",
            "Accept: application/json",
            "Nonce: ".$nonce,
            "Timestamp: ".$timestamp,
            "Signature: ".$signature
        );
         // var_dump($headers);
        curl_setopt($ch, CURLOPT_URL, "https://197.231.130.7/api/airtime/gswtest");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
      // echo $error = curl_error($ch);die;
        return $result;
    }

    public function generateNumeroTransaction(){
        $found = 0;

        do{
            $code = $this->random(8);
            $etat = $this->verifyTransaction($code);
            if($etat == 1){
                $found = 1;
                //$this->insertNumeroTransaction($code);
            }
        }
        while($found == 0);
        return $code;
    }

    public function random($car) {
        $string = "";
        $chaine = "1234567890";
        srand((double)microtime()*1000000);
        for($i=0; $i<$car; $i++) {
            $string .= $chaine[rand()%strlen($chaine)];
        }
        return $string;
    }

    public function verifyTransaction($code){
        try{
            $sql = "SELECT id
                    from histoAchatCodeGamswitch
                    WHERE num_transaction = :code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "code" => strval($code),
                )
            );
            $a = $user->rowCount();
            //$this->pdo = NULL;
            if($a > 0){
                return 0;
            }
            else{
                return 1;
            }
        }
        catch(Exception $e)
        {
            //$this->pdo = NULL;
            echo $e;die;
        }
    }

    public function saveTransaction($compteur,$code,$order_number,$telephone,$montant,$mont_frais,$statut,$num_transaction,$prenom,$nom,$agence,$user){

            $mt=$mont_frais+$montant;
        try
        {
            $requette = "INSERT INTO histoAchatCodeGamswitch(compteur,code,order_number,telephone,montant,frais,montant_total,statut,num_transaction,prenom,nom,agence,user) values('".$compteur."','".$code."','".$order_number."','".$telephone."','".$montant."','".$mont_frais."','".$mt."','".$statut."','".$num_transaction."','".$prenom."','".$nom."','".$agence."','".$user."')";
            $stmt = $this->getConnexion()->prepare($requette);
            $resultat = $stmt->execute();
        }
        catch(PDOException $e)
        {
            $resultat = -1;
        }
        return $resultat;
    }

   public function debiter_soldeAgence($montant, $frais, $row_agence,$type)
    {
        $return=0;
        if($type==1||$type==2)
        {
            $montant_total = $montant + $frais;
        }else{
            $montant_total = $montant + 200;
        }

        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE agence SET solde=solde-:soldes WHERE rowid=:agence");
            $Result1=$req->execute(array("soldes" => $montant_total, "agence" => $row_agence));
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(PDOException $e)
        {
            return -1;
        }

        return $return;
    }

    public function getFrais()
    {
        try
        {
            $query_rq_service = "SELECT frais FROM service WHERE rowid=$this->service";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->execute();
            $row_rq_service= $service->fetchObject();
            return $frais= $row_rq_service->frais;
        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function Get_carteCommisssion()
    {
        try
        {
            $query_rq_service = "SELECT numero_carte FROM carte_parametrable WHERE idcarte=1";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->execute();
            $row_rq_service= $service->fetchObject();
            return $row_rq_service->numero_carte;
        }
        catch(PDOException $e)
        {
            return 0 ;
        }
    }

    public function addCommission($montant_commission,$idservices,$idpartenaire_commission,$carte='',$observations="")
    {
        $date_envoie=date("Y-m-d H:i:s");
        try
        {
            $query_insert = "INSERT INTO transaction_commission( montant_commission, idservices, observations, idagence,num_carte) " ;
            $query_insert .= " VALUES (:montant_commission,:idservices,:observations,:idagence,:num_carte )";
            $rs_insert = $this->getConnexion()->prepare($query_insert);
            $rs_insert->bindParam(':montant_commission', $montant_commission);
            $rs_insert->bindParam(':idservices', $idservices);
            $rs_insert->bindParam(':observations', $observations);
            $rs_insert->bindParam(':idagence', $idpartenaire_commission);
            $rs_insert->bindParam(':num_carte', $carte);
            $result = $rs_insert->execute();
            if($result>0) return 1;
            else return 0;
        }
        catch(PDOException $e)
        {
            echo $e;
        }
    }

    public function addCommission_afaire($montant_commission,$idservices,$idpartenaire_commission,$carte='', $observations="")
    {
        $date_envoie=date("Y-m-d H:i:s");
        try
        {
            $query_insert = "INSERT INTO transaction_commission_afaire( montant_commission, idservices, observations, idagence, num_carte) " ;
            $query_insert .= " VALUES (:montant_commission,:idservices,:observations,:idagence, :num_carte)";
            $rs_insert = $this->getConnexion()->prepare($query_insert);
            $rs_insert->bindParam(':montant_commission', $montant_commission);
            $rs_insert->bindParam(':idservices', $idservices);
            $rs_insert->bindParam(':observations', $observations);
            $rs_insert->bindParam(':idagence', $idpartenaire_commission);
            $rs_insert->bindParam(':num_carte', $carte);
            $rs_insert->execute();

        }
        catch(PDOException $e)
        {
            return -1;
        }
    }

    public function GetSoldeAgence($idagence=0)
    {
        try
        {
            $query_rq_service = "SELECT solde, idcard, num_carte,idtype_agence FROM agence WHERE rowid=:idagence";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->execute(array("idagence" => $idagence));
            $row_rq_service= $service->fetchObject();
            $solde =  $row_rq_service->solde;
            $type=$row_rq_service->idtype_agence;
            $idcard=$row_rq_service->idcard;
            $numcard=$row_rq_service->num_carte;
        }
        catch(PDOException $e)
        {
            $solde = -1;
        }

        return $solde;
    }

    public function getTypeAgence($agence)
    {
        $query_rq_service = "SELECT  idtype_agence FROM agence 
		WHERE agence.rowid= :agence";
        $service = $this->getConnexion()->prepare($query_rq_service);
        $service ->bindParam("agence",$agence);
        $service->execute();
        $row_rq_service= $service->fetchObject();

        return $row_rq_service->idtype_agence;
    }

    public function GetcarteDistributeur($agence_connecte)
    {
        try
        {
            $query_rq_service = "SELECT idcard FROM agence WHERE rowid=:idagence";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->execute(array("idagence" => $agence_connecte));
            $row_rq_service= $service->fetchObject();
            $idcard=$row_rq_service->idcard;
        }
        catch(PDOException $e)
        {
            return $e;
        }

        return $idcard;
    }

    public function crediter_compteParametrable($montant,$idcompte)
    {
        $return=0;
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=:idcompte");
            $Result1=$req->execute(array("soldes" => $montant, "idcompte" => $idcompte));
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(PDOException $e)
        {
            $return=-1;
        }

        return $return;
    }

    public function GetcarteGamswitch()
    {
        $sql = "SELECT numero_carte FROM carte_parametrable WHERE idcarte=7";
        try
        {
            $rq_code_existe = $this->getConnexion()->prepare($sql);
            $rq_code_existe->execute();
            $row_rq_code_existe = $rq_code_existe->fetchObject();
            $totalRows_rq_code_existe = $rq_code_existe->rowCount();
            if($totalRows_rq_code_existe==1)
            {
                //insertion de carte
                return $row_rq_code_existe->numero_carte;
            }
        }
        catch(Exception $e)
        {
            return 0;
        }
    }

    function Generer_numtransaction()
    {
        $found=0;
        while ($found==0)
        {
            $code_carte=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
            $colname_rq_code_existe = $code_carte;
            $query_rq_code_existe = $this->getConnexion()->prepare("SELECT rowid FROM transaction WHERE num_transac ='".$colname_rq_code_existe."'");
            $query_rq_code_existe->execute();
            $totalRows_rq_code_existe = $query_rq_code_existe->rowCount();
            if($totalRows_rq_code_existe==0)
            {
                //CODE GENERER
                $code_generer=$code_carte;
                $found=1;
                break;
            }
        }
        return $code_generer;
    }

    function SaveTransaction_carte($service=0,$montant=0,$fk_carte=0,$user=1, $statut=1, $num_transac="", $commission, $agence_connecte, $transactionId=0)
    {
        $ladate = date('Y-m-d H:i:s');
        try
        {
            $req =  $this->getConnexion()->prepare("INSERT INTO transaction(num_transac, fk_carte, date_transaction, montant, statut, fkuser, fk_service, commission, fk_agence, transactionID)
					VALUES (:num_transac, :fk_carte, :date_transaction, :montant, :statut, :fkuser, :fk_service, :commission, :agence_connecte, :transactionId)");
            $req->execute(array(
                "num_transac"=>$num_transac,
                "fk_carte"=>$fk_carte,
                "date_transaction" => $ladate,
                "montant"=>$montant,
                "statut" => $statut,
                "fkuser" => $user,
                "fk_service" => $service,
                "commission" => $commission,
                "agence_connecte" => $agence_connecte,
                "transactionId" => $transactionId
            ));

            return 1 ;
        }
        catch(PDOException $e)
        {
            //echo $e;
            $e ;
        }
    }
    function getTransaction($num_transac){
        $query_reglement="SELECT * FROM histoAchatCodeGamswitch WHERE num_transaction=:numtransaction";
        $rs_reglement = $this->getConnexion()->prepare($query_reglement);
        $rs_reglement->bindParam("numtransaction",$num_transac);
        $rs_reglement->execute();
        $row_rs_reglement = $rs_reglement->fetchObject();
        return $row_rs_reglement;
    }


}