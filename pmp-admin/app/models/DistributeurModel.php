<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */

class DistributeurModel extends \app\core\BaseModel
{
    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION  RECHARGEMEBNT DES DISTRIBUTEUR                                       //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    /*************Detail Beneficiaire**************/
    public function getDistributeur($code)
    {
        try {
            $sql = "SELECT * FROM agence  WHERE code= :code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("code" => $code));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
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

    /*************generate Numero Transaction Operation carte************/
    public function generateNumeroTransactionOperationcarte()
    {
        $found = 0;
        do {
            $code = $this->random(15);
            $etat = $this->verifyTransactionOperationcarte($code);
            if ($etat == 1) {
                $found = 1;
            }
        } while ($found == 0);
        return $code;
    }


    /*************calcul Taxe*************/
    public function calculTaxe($montant, $serviceID)
    {
        try {
            $query_rq_service = "SELECT frais FROM service WHERE rowid=:serviceID";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->bindParam("serviceID", $serviceID);
            $service->execute();
            $row_rq_service = $service->fetchObject();
            $row_rq_count = $service->rowCount();
            if ($row_rq_count === 1) {
                if ($row_rq_service->frais >= 0) {
                    return $row_rq_service->frais;
                } else {
                    $query_rq_service = "SELECT tarif_frais.* FROM service, tarif_frais 
                      WHERE service.rowid =  tarif_frais.service
                      AND tarif_frais.montant_deb <= :mtt
                      AND tarif_frais.montant_fin >= :mtt1
                      AND  service.rowid=:serviceID";

                    $service = $this->con->prepare($query_rq_service);
                    $service->bindParam("mtt", $montant);
                    $service->bindParam("mtt1", $montant);
                    $service->bindParam("serviceID", $serviceID);
                    $service->execute();
                    $row_rq_service = $service->fetchObject();
                    $row_rq_count = $service->rowCount();
                    if ($row_rq_count === 1) {
                        return $row_rq_service->valeur;
                    } else {
                        return $montant * 0.01;
                    }
                }
            } else {
                return $montant * 0.01;
            }

        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }


    /*************verif Code recharge*************/
    public function verifCodeRechargement($fk_agence, $codesecret)
    {
        try {
            $query_rs_coderetrait = "SELECT MAX(id) AS id FROM code_rechargement WHERE fk_agence= " . $fk_agence . "  AND statut = 0 ";
            $resultatcode = $this->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute();
            $rs_resultatcode = $resultatcode->fetchObject();
            $id = $rs_resultatcode->id;
            $query_rs_coderetrait = "SELECT code FROM code_rechargement	WHERE id ='" . $id . "'";
            $resultatcode = $this->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute();
            $rs_resultatcode = $resultatcode->fetchObject();
            $code = $rs_resultatcode->code;
            if ($codesecret == $code) return 1;
            else return 0;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*************verif Code Retrait*************/
    public function verifCodeRetrait($fkcarte, $codesecret)
    {
        try {
            $query_rs_coderetrait = "SELECT MAX(idcode_retrait) AS id FROM code_retrait WHERE num_carte= " . $fkcarte . "  AND statut = 0 ";
            $resultatcode = $this->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute();
            $rs_resultatcode = $resultatcode->fetchObject();

            $id = $rs_resultatcode->id;

            $query_rs_coderetrait = "SELECT code_retrait FROM code_retrait	WHERE idcode_retrait ='" . $id . "'";
            $resultatcode = $this->getConnexion()->prepare($query_rs_coderetrait);
            $resultatcode->execute();
            $rs_resultatcode = $resultatcode->fetchObject();
            $code = $rs_resultatcode->code_retrait;
            if ($codesecret == $code) return 1;
            else return 0;
        } catch (Exception $e) {
            return -2;
        }
    }



    public function soldeAgence($agence)
    {
        $sql = "SELECT solde FROM agence WHERE rowid = :agence";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'agence' => $agence
            )
        );
        $a = $user->fetchObject();
        return $a->solde;
    }
    /***************debiter solde agence***************/
    public function debiterSoldeAgence($agence, $montant, $pdo=null)
    {
        $sql = "UPDATE agence SET solde = solde - :montant WHERE rowid = :agence";
        if ($pdo != null )
            $user = $pdo->prepare($sql) ;
        else  $user = $this->getConnexion()->prepare($sql);
        $res = $user->execute(
            array(
                'montant' => $montant,
                'agence' => $agence
            )
        );

        return $res;
    }

    /***************crediter solde agence***************/
    function creditersoldeAgence($montant,$premier_appro,$row_agence)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE agence SET solde=solde+:soldes, premier_appro =:premier_appro WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "premier_appro" => $premier_appro, "agence"=>$row_agence));
            if($Result1 > 0){
                return  1;
            }
            else return -1;
        }catch(PDOException $e) {
            return -2;
        }
    }

    public function getStockJULA($agence, $montant)
    {
        $sql = "SELECT SUM(stock) as stocktotal FROM lotcarte_jula WHERE idagence = :agence AND montant = :montant";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'agence' => $agence,
                'montant' => $montant
            )
        );
        $a = $user->fetchObject();
        return $a->stocktotal;
    }

    public function retourneCommission($montant){
        @require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = SHA1( ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $params = array("idmarchand" => ID_WS_JULA, "idpartenaire" => IDPARTENAIRE_WS_JULA,  "cle_hachage" => $cle, "montant" =>$montant);

        $ResulatRenvoyes = $s->call('ReturnCommissionCarte', $params);
        $decodes = json_decode($ResulatRenvoyes);
        //var_dump($decodes); exit;
       // $code = $decodes;
        return  $decodes ;

    }

    public function stockJULAByAgence($agence, $montant)
    {
        $sql = "SELECT SUM(stock) as stock FROM lotcarte_jula WHERE idagence = :agence AND montant = :montant";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'agence' => $agence,
                'montant' => $montant
            )
        );
        $a = $user->fetchObject();
        return $a->stock;
    }

    public function dernierCarteJULAVendueInSerie($agence, $montant,$pdo=null)
    {
        $sql = "SELECT MIN(idlotcarte), num_debut, stock_init, stock FROM lotcarte_jula WHERE idagence = :agence AND montant = :montant AND stock > 0";
        if ($pdo != null )
            $user = $pdo->prepare($sql) ;
        else  $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'agence' => $agence,
                'montant' => $montant
            )
        );
        $a = $user->fetchObject();
        if ($a != '' && is_object($a)) {
            $num_deb = $a->num_debut;
            $num_serie = $num_deb + ($a->stock_init - $a->stock);
            if ($num_deb <= $num_serie) {
                echo 'E1';
                return $num_serie;
            } else {
                echo 'E2';
                return -1;
            }
        } else {
            echo 'E3';
            return -1;
        }
    }

    public function saveCarteJULAVendue($serie, $agence, $montant, $date, $utilisateur,$pdo=null)
    {
        //var_dump($pdo); exit;
        try {
            $sql = "INSERT INTO carte_jula(numero_serie, montant, idagence, iduser, date_vente) VALUE(:numero_serie, :montant, :idagence, :iduser, :date_vente)";

            if ($pdo != null )
                $user = $pdo->prepare($sql) ;
            else  $user = $this->getConnexion()->prepare($sql);

            $res = $user->execute(
                array(
                    'numero_serie' => $serie,
                    'montant' => $montant,
                    'idagence' => $agence,
                    'iduser' => $utilisateur,
                    'date_vente' => $date
                )
            );

            return $res;
        } catch (PDOException $ex) {
            return -1;
        }

    }

    public function destock($num_serie, $agence, $montant,$pdo=null)
    {
        $sql = "UPDATE lotcarte_jula SET stock = stock - 1 WHERE idagence = :agence AND num_debut <= :deb AND num_fin >= :fin AND montant = :mont AND stock > 0";
        if ($pdo != null )
            $user = $pdo->prepare($sql) ;
        else  $user = $this->getConnexion()->prepare($sql);

        $res = $user->execute(
            array(
                'agence' => $agence,
                'deb' => $num_serie,
                'fin' => $num_serie,
                'mont' => $montant
            )
        );

        return $res;
    }


    public function updateLotCarte($numDebut, $numFin, $statut=0){
        @require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = SHA1( ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $params = array("idpartenaire" => IDPARTENAIRE_WS_JULA, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle, "num_debut" =>$numDebut, "num_fin" =>$numFin, "statut" =>$statut);

        $ResulatRenvoyes = $s->call('updateLotCarte', $params);
        $decodes = json_decode($ResulatRenvoyes);
        //var_dump($decodes); exit;
        $code = $decodes->{'result'};
        return  $code ;

    }
    public function testReferenceExist($data, $table = 'lotcarte_reception_jula')
    {
        $sql = "SELECT num_reference FROM $table WHERE num_reference =:num_reference";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return (count($a) > 0);
    }
    public function __getReference($arg = 'lotcarte_reception_jula')
    {
        do {
            $code = (new \app\core\Utils())->generateur();
        } while ($this->testReferenceExist(['num_reference' => $code], $arg));
        return $code;
    }

    public function getIdReception($agence, $montant)
    {
        $sql = "SELECT idreception as idrecep FROM lotcarte_jula WHERE idagence = :agence AND montant = :montant ORDER BY idlotcarte DESC LIMIT 1";
        //var_dump($sql);exit;
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'agence' => $agence,
                'montant' => $montant
            )
        );
        $a = $user->fetchObject();
        return $a->idrecep;
    }

    public function saveLotcarteDist($num_reference, $num_debut, $num_fin, $montant, $idreception, $idagence, $user_add, $date_add, $stock_init, $stock, $pdo=null)
    {
        //var_dump($pdo); exit;
        try {
            $sql = "INSERT INTO lotcarte_jula(num_reference, num_debut, num_fin, montant, idreception, idagence, user_add, date_add, stock_init, stock) VALUE(:num_reference, :num_debut, :num_fin, :montant, :idreception, :idagence, :user_add, :date_add, :stock_init, :stock)";

            if ($pdo != null )
                $user = $pdo->prepare($sql) ;
            else  $user = $this->getConnexion()->prepare($sql);

            $res = $user->execute(
                array(
                    'num_reference' => $num_reference,
                    'montant' => $montant,
                    'num_debut' => $num_debut,
                    'num_fin' => $num_fin,
                    'idreception' => $idreception,
                    'idagence' => $idagence,
                    'user_add' => $user_add,
                    'date_add' => $date_add,
                    'stock_init' => $stock_init,
                    'stock' => $stock
                )
            );

            return $res;
        } catch (PDOException $ex) {
            return -1;
        }

    }



    public function vendreCarteJULA($montant, $commission, $nombre, $agence, $utilisateur, $agenceDist, $utils=null)
    {

        $num_reference = $this->__getReference();
        $idreception=$this->getIdReception($agence, $montant);
        //var_dump($num_reference." ".$idreception." ag : ".$agence." mnt : " .$montant);exit;

        if ($utils != null )
            $this->utils = $utils ;
        else
            $this->utils = new app\core\Utils();
            $pdo = $this->utils->getPDO();
        try{
            $stockRestant = $this->stockJULAByAgence($agence, $montant);
            $nombreCarteJULA = 0;
            $date = date('Y-m-d H:i:s');
            $service = ID_SERVICE_VENTE_CARTE_KREDIVOLA;

            $num_transac = $this->utils->Generer_numtransaction();

            $pdo->beginTransaction() ;
            if ($stockRestant >= $nombre) {
                $cpt = 0;
                for ($i = 0; $i < $nombre; $i++) {
                    $num_serie_a_vendre = $this->dernierCarteJULAVendueInSerie($agence, $montant);

                    if ($num_serie_a_vendre > 0) {
                        $etasave = $this->saveCarteJULAVendue($num_serie_a_vendre, $agence, $montant, $date, $utilisateur,$pdo);
                        if ($etasave == 1) {
                            $this->debiterSoldeAgence($agence, $montant,$pdo);
                            $this->utils->SaveTransaction($num_transac, $service, $montant, $fk_carte = 0, $utilisateur, $statut = 1, $commentaire = 'Vente carte Kredivola', $commission, $agence, $transactId = $num_serie_a_vendre, $fkuser_support = 0, $fkagence_support = 0);
                            $this->destock($num_serie_a_vendre, $agence, $montant,$pdo);
                            var_dump($num_serie_a_vendre. " debitsolde ".$this->debiterSoldeAgence($agence, $montant,$pdo)." dest: ".$this->destock($num_serie_a_vendre, $agence, $montant,$pdo));exit;
                            $cpt++;
                        } else {
                            return -3;
                        }
                    } else {
                        return -2;
                    }
                }
                if ($cpt > 0) {

                    $numDebutSerie  = $num_serie_a_vendre ;
                    $numFinSerie =  $numDebutSerie + $nombre - 1 ;
                     //$code = $this->updateLotCarte($numDebutSerie , $numFinSerie, 1);
                    //var_dump($code);exit;
                    $result=$this->saveLotcarteDist($num_reference, $numDebutSerie, $numFinSerie, $montant, $idreception, $agenceDist, $utilisateur, $date, $nombre, $nombre, $pdo);

                    if ($result == 1){
                        $pdo->commit() ;
                        return array('num_transac'=>$num_transac , 'num_fin'=>$num_serie_a_vendre);
                    }else{
                        $pdo->rollBack() ;
                        return -1;
                    }

                } else {
                    return -4;
                }
            } else {
                return -1;
            }
        }catch (Exception $e){
            return -1;

        }

    }

    public function cartesVente($numtransaction)
    {
        $sql = "SELECT c.* FROM transaction t
                LEFT JOIN carte_jula c ON t.transactionID = c.numero_serie
                WHERE num_transac = :num ORDER BY c.numero_serie DESC";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                'num' => $numtransaction
            )
        );
        $a = $user->fetchAll(PDO::FETCH_OBJ);
        return $a;
    }

    /**********verifier Identifiant**********/
    public function getPremierAppro($agence)
    {
        try
        {
            $sql = "Select premier_appro as appro from agence WHERE rowid=".$agence;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $app=$a->appro;
           if($app == 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }



}