<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */

class CompteModel extends \app\core\BaseModel
{
    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES BENEFICIARES                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////
    /********Liste beneficiaires*********/
    public function allBenef($requestData = null)
    {
        try {
            $sql = "SELECT b.rowid, b.prenom, b.nom, b.email, b.adresse, c.statut as etat,c.typecompte FROM beneficiaire b 
                    INNER JOIN carte c ON c.beneficiaire_rowid=b.rowid";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( prenom LIKE '%" . $requestData . "%' ";
                $sql .= " OR nom LIKE '%" . $requestData . "%' ";
                $sql .= " OR email LIKE '%" . $requestData . "%' ";
                $sql .= " OR adresse LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['prenom', 'nom', 'email', 'adresse', 'etat'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /********Liste beneficiaires*********/
    public function allBenefCount()
    {
        try {
            $sql = "SELECT COUNT(rowid) as total FROM beneficiaire ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /*************Detail Beneficiaire**************/
    public function getBeneficiaireById($id)
    {
        try {
            $sql = "SELECT b.rowid, b.nom, b.prenom, b.prenom1,b.date_nais, b.cni, b.adresse, b.email, c.rowid as idcarte, c.numero_serie, c.numero, c.date_expiration, c.telephone, c.date_activation , c.etat as cartestatut, c.typecompte, b.user_creation, b.date_creation, b.user_modification
                    FROM beneficiaire AS b
                    LEFT OUTER JOIN carte AS c
                    ON b.rowid = c.beneficiaire_rowid
                    WHERE b.rowid = :id";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" => $id));

            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a;
        } catch (PDOException $PDOException) {
            return -1;
        }
    }

    /*******************Modifier User*****************/
    public function updateBeneficiaire($prenom, $prenom2, $nom, $datenaissance, $cni, $telephone, $email, $adresse, $commentaire, $user_modification, $rowid)
    {
        $date_modification = date('Y-m-d H:i:s');
        try {
            $sql = "UPDATE beneficiaire SET prenom=:prenom, prenom1=:prenom1, nom=:nom, date_nais=:date_nais, cni=:cni, telephone_fixe=:telephone_fixe, email=:email, 
                  adresse=:adresse,commentaire=:commentaire,date_modification=:date_modification,user_modification=:user_modification WHERE rowid=:rowid";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "prenom" => $prenom,
                "prenom1" => $prenom2,
                "nom" => $nom,
                "date_nais" => $datenaissance,
                "cni" => $cni,
                "telephone_fixe" => $telephone,
                "email" => $email,
                "adresse" => $adresse,
                "commentaire" => $commentaire,
                "date_modification" => $date_modification,
                "user_modification" => $user_modification,
                "rowid" => $rowid
            ));
            $this->closeConnexion();
            if ($res == 1) return 1;
            else return -1;
        } catch (PDOException $e) {
            return -1;
        }
    }

    /*************get Beneficiaire by telephone**************/
    public function beneficiaireByTelephone($tel)
    {
        try {
            $sql = "SELECT b.rowid, b.nom, b.prenom, b.prenom1, b.cni, b.adresse, b.email, c.rowid as idcarte, c.numero_serie, c.numero, c.date_expiration, c.telephone, c.date_activation , c.statut as cartestatut, c.typecompte
			FROM beneficiaire as b
			LEFT OUTER JOIN carte as c
			ON b.rowid = c.beneficiaire_rowid
			WHERE c.telephone =:telephone
			AND b.etat =:etat
			AND c.etat =:etatcarte";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("telephone" => $tel, "etat" => 1, "etatcarte" => 1));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*************get Beneficiaire by telephone1**************/
    public function beneficiaireByTelephone1($tel)
    {
        try {
            $sql = "SELECT b.rowid, b.nom, b.prenom, b.prenom1, b.cni, b.adresse, b.email, c.rowid as idcarte, c.numero_serie, c.numero, c.date_expiration, c.telephone, c.date_activation , c.statut as cartestatut, c.typecompte
			FROM beneficiaire as b
			LEFT OUTER JOIN carte as c
			ON b.rowid = c.beneficiaire_rowid
			WHERE c.telephone =:telephone";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("telephone" => $tel));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*************get Beneficiaire by telephone2**************/
    public function beneficiaireByTelephone2($tel)
    {
        try {
            $sql = "SELECT b.rowid,b.prenom,b.nom,b.sexe,b.date_nais,b.email,b.adresse,c.rowid as idcompte,c.telephone
			FROM beneficiaire as b
			INNER JOIN carte as c
			ON b.rowid = c.beneficiaire_rowid
			WHERE c.telephone ='" . $tel . "'
			AND c.typecompte=0";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetch();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /********Activer Statut Carte*********/
    public function enableCarte($telephone, $user_modif)
    {

        try {
            $date_modif = date('Y-m-d H:i:s');
            $sql = "UPDATE carte SET statut = :statut, user_modif =:user_modif, date_modif =:date_modif WHERE telephone =:id";
            $type_carte = $this->getConnexion()->prepare($sql);
            $res = $type_carte->execute(array("statut" => 1, "id" => $telephone, "user_modif" => $user_modif, "date_modif" => $date_modif));
            if ($res) return 1;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /********Desactiver Statut Carte*********/
    public function deseableCarte($telephone, $user_modif)
    {

        try {
            $date_modif = date('Y-m-d H:i:s');
            $sql = "UPDATE carte SET statut = :statut, user_modif =:user_modif, date_modif =:date_modif WHERE telephone =:id";
            $type_carte = $this->getConnexion()->prepare($sql);
            $res = $type_carte->execute(array("statut" => 0, "id" => $telephone, "user_modif" => $user_modif, "date_modif" => $date_modif));
            if ($res) return 1;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /**********Save Operation Carte***********/
    function saveOperation_carte($carte, $memo, $errorCodes, $num_transac, $fkuser, $fkagence)
    {
        try {
            $date_operation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO operations_carte (num_transac, numero, libelle_operation, date_operation, utilisateur, agence, resultat_operation)
		   VALUES (:num_transac, :numero, :libelle_operation, :date_operation, :utilisateur, :agence, :resultat_operation)";

            $type_carte = $this->getConnexion()->prepare($sql);
            $res = $type_carte->execute(
                array(
                    "num_transac" => $num_transac,
                    "numero" => $carte,
                    "libelle_operation" => $memo,
                    "date_operation" => $date_operation,
                    "utilisateur" => $fkuser,
                    "agence" => $fkagence,
                    "resultat_operation" => $errorCodes,
                )
            );
            if ($res == 1) return 1;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*********get Numero Carte************/
    public function getNumCarte($telephone)
    {
        try {
            $sql = "SELECT numero FROM carte WHERE telephone =:numero";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("numero" => $telephone));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->numero;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    /*********get Type Compte************/
    public function getTypeCompte($telephone)
    {
        try {
            $sql = "SELECT typecompte FROM carte WHERE telephone =:numero";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("numero" => $telephone));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->typecompte;
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

    /*********verify Transaction Operation carte*******/
    public function verifyTransactionOperationcarte($code)
    {
        try {
            $sql = "SELECT rowid from operations_carte WHERE num_transac = :code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("code" => $code));
            $a = $user->rowCount();
            if ($a > 0) return 0;
            else return 1;
        } catch (Exception $e) {
            return -2;
        }
    }

    public function allProfession()
    {
        try {
            $sql = "SELECT * FROM profession ORDER BY profession.`libelle` ASC";
            $user = $this->getConnexion()->prepare($sql);
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

    /*************calcul Taxe*************/
    public function calculTaxe($montant, $serviceID)
    {
        try {
            $query_rq_service = "SELECT frais FROM service WHERE rowid=:serviceID";
            $service = $this->con->prepare($query_rq_service);
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
        /*try {
            $sql = "SELECT tarif_frais.valeur FROM service, tarif_frais WHERE service.rowid =  tarif_frais.service
	  		AND tarif_frais.montant_deb <=:deb AND tarif_frais.montant_fin >=:fin AND tarif_frais.service = 12";
            $groupes = $this->getConnexion()->prepare($sql);
            $groupes->execute(array("deb" => $montant, "fin" => $montant));
            $back = $groupes->fetchObject();
            $totalRows_classe = $groupes->rowCount();
            if ($totalRows_classe > 0) {
                return $back->valeur;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            return -2;
        }*/
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

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES COMPTES                                             //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function __getReference($arg = 'lotcarte_reception')
    {
        do {
            $code = (new \app\core\Utils())->generateur();
        } while ($this->testReferenceExist(['num_reference' => $code], $arg));
        return $code;
    }

    public function getHistoriqueEnvCaveau($requestData = null)
    {
        $data = null;
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init, (lc.stock_init - lc.stock) as stock_rst
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencedest = :idcaveau AND lc.destinataire = 'CAVEAU' AND lc.typelot = 0 ";

        if (is_array($requestData)) {
            $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init, (lc.stock_init - lc.stock) as stock_rst
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( lc.reference LIKE '%?%' ";
                $sql .= " OR lc.num_debut LIKE '%?%' ";
                $sql .= " OR lc.num_fin LIKE '%?%' ";
                $sql .= " OR tc.libelle LIKE '%?%' ";
                $sql .= " OR lc.date_add LIKE '%?%' ";
                $sql .= " OR lc.stock_init LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['lc.reference', 'lc.num_debut', 'lc.num_fin', 'tc.libelle', 'lc.date_add', 'lc.stock_init', 'lc.stock_init'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }

        try {
            $user = $this->getConnexion()->prepare($sql);

            $user->bindValue('idcaveau', CAVEAU_ID);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "<pre>";
            var_dump($ex->getMessage());
            exit();
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['date_add'] = (new \app\core\Utils())->date_fr4($result[$i]['date_add']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueEnvCaveauCount()
    {
        try {
            $sql = "SELECT COUNT(lc.idlotcarte) as total
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte 
                    WHERE lc.idagencedest = :idcaveau AND lc.destinataire = 'CAVEAU' AND lc.typelot = 0 ";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('idcaveau', CAVEAU_ID);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getHistoriqueReceptionCaveau($requestData = null)
    {
        $data = null;
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencedest = :idcaveau AND lc.destinataire = 'CAVEAU' AND lc.typelot = 1 ";

        if (is_array($requestData)) {
            $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( lc.reference LIKE '%?%' ";
                $sql .= " OR lc.num_debut LIKE '%?%' ";
                $sql .= " OR lc.num_fin LIKE '%?%' ";
                $sql .= " OR tc.libelle LIKE '%?%' ";
                $sql .= " OR lc.date_add LIKE '%?%' ";
                $sql .= " OR lc.stock_init LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['lc.reference', 'lc.num_debut', 'lc.num_fin', 'tc.libelle', 'lc.date_add', 'lc.stock_init'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }

        try {
            $user = $this->getConnexion()->prepare($sql);

            $user->bindValue('idcaveau', CAVEAU_ID);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "<pre>";
            var_dump($ex->getMessage());
            exit();
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['date_add'] = (new \app\core\Utils())->date_fr4($result[$i]['date_add']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueReceptionCaveauCount()
    {
        try {
            $sql = "SELECT COUNT(lc.idlotcarte) as total
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte 
                    WHERE lc.idagencedest = :idcaveau AND lc.destinataire = 'CAVEAU' AND lc.typelot = 1 ";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('idcaveau', CAVEAU_ID);
            $user->execute();
            $a = $user->fetchAll(\PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (\PDOException $exception) {
            return 0;
        }
    }

    public function getHistoriqueDistributionCaveau($requestData = null)
    {
        $data = null;
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init, (lc.stock_init - lc.stock) as stock_rst
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencesource = :idagencesource AND lc.expediteur = 'CAVEAU' AND lc.typelot = 0 ";

        if (is_array($requestData)) {
            $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init, (lc.stock_init - lc.stock) as stock_rst
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( lc.reference LIKE '%?%' ";
                $sql .= " OR lc.num_debut LIKE '%?%' ";
                $sql .= " OR lc.num_fin LIKE '%?%' ";
                $sql .= " OR tc.libelle LIKE '%?%' ";
                $sql .= " OR lc.date_add LIKE '%?%' ";
                $sql .= " OR lc.stock_init LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['lc.reference', 'lc.num_debut', 'lc.num_fin', 'tc.libelle', 'lc.date_add', 'lc.stock_init', 'lc.stock_init'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }

        try {
            $user = $this->getConnexion()->prepare($sql);

            $user->bindValue('idagencesource', CAVEAU_ID);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "<pre>";
            var_dump($ex->getMessage());
            exit();
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['date_add'] = (new \app\core\Utils())->date_fr4($result[$i]['date_add']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueDistributionCaveauCount()
    {
        try {
            $sql = "SELECT COUNT(lc.idlotcarte) as total
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte 
                    WHERE lc.idagencesource = :idagencesource AND lc.expediteur = 'CAVEAU' AND lc.typelot = 0";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('idagencesource', CAVEAU_ID);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getHistoriqueReceptionAgence($requestData = null)
    {
        $data = null;
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencesource = :idagencesource AND lc.expediteur = 'CAVEAU' AND lc.typelot = 1 ";

        if (is_array($requestData)) {
            $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( lc.reference LIKE '%?%' ";
                $sql .= " OR lc.num_debut LIKE '%?%' ";
                $sql .= " OR lc.num_fin LIKE '%?%' ";
                $sql .= " OR tc.libelle LIKE '%?%' ";
                $sql .= " OR lc.date_add LIKE '%?%' ";
                $sql .= " OR lc.stock_init LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['lc.reference', 'lc.num_debut', 'lc.num_fin', 'tc.libelle', 'lc.date_add', 'lc.stock_init'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }

        try {
            $user = $this->getConnexion()->prepare($sql);

            $user->bindValue('idagencesource', CAVEAU_ID);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "<pre>";
            var_dump($ex->getMessage());
            exit();
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['date_add'] = (new \app\core\Utils())->date_fr4($result[$i]['date_add']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueReceptionAgenceCount()
    {
        try {
            $sql = "SELECT COUNT(lc.idlotcarte) as total
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte 
                    WHERE lc.idagencesource = :idagencesource AND lc.expediteur = 'CAVEAU' AND lc.typelot = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('idagencesource', CAVEAU_ID);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getHistoriqueReception($requestData = null)
    {
        $data = null;
        $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, stock_init, stock
                FROM lotcarte_reception ";
        if (is_array($requestData)) {
            $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, stock_init, stock, user_add, date_add, agence_retour
                    FROM lotcarte_reception ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_reference LIKE '%?%' ";
                $sql .= " OR date_reception LIKE '%?%' ";
                $sql .= " OR num_debut LIKE '%?%' ";
                $sql .= " OR num_fin LIKE '%?%' ";
                $sql .= " OR stock_init LIKE '%?%' ";
                $sql .= " OR stock LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['num_reference', 'date_reception', 'num_debut', 'num_fin', 'stock_init', 'stock'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $result = $user->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueReceptionCount()
    {
        try {
            $sql = "SELECT COUNT(idlotcarte_recu) as total FROM lotcarte_reception ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getHistoriqueRetourLotAgence($requestData = null)
    {
        $data = null;
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencedest = :idagencedest AND lc.destinataire = 'CAVEAU' AND lc.typelot = 2 ";

        if (is_array($requestData)) {
            $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, tc.libelle, lc.date_add, lc.stock_init
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( lc.reference LIKE '%?%' ";
                $sql .= " OR lc.num_debut LIKE '%?%' ";
                $sql .= " OR lc.num_fin LIKE '%?%' ";
                $sql .= " OR tc.libelle LIKE '%?%' ";
                $sql .= " OR lc.date_add LIKE '%?%' ";
                $sql .= " OR lc.stock_init LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['lc.reference', 'lc.num_debut', 'lc.num_fin', 'tc.libelle', 'lc.date_add', 'lc.stock_init'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }

        try {
            $user = $this->getConnexion()->prepare($sql);

            $user->bindValue('idagencedest', CAVEAU_ID);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "<pre>";
            var_dump($ex->getMessage());
            exit();
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['date_add'] = (new \app\core\Utils())->date_fr4($result[$i]['date_add']);
        }
        return $result;
    }

    /********Liste beneficiaires*********/
    public function getHistoriqueRetourLotAgenceCount()
    {
        try {
            $sql = "SELECT COUNT(lc.idlotcarte) as total
                    FROM lotcarte lc
                    INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte 
                    WHERE lc.idagencedest = :idagencedest AND lc.destinataire = 'CAVEAU' AND lc.typelot = 2 ";
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('idagencedest', CAVEAU_ID);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return 0;
        }
    }

    /********Liste beneficiaires*********/
    public function getTypeCarte2()
    {
        try {
            $sql = "SELECT * FROM type_carte ORDER BY libelle";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            return $user->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return -1;
        }
    }


    public function getHistoriqueDistribution($requestData = null)
    {
        $data = null;
        $sql = "SELECT l.idlotcarte as rowid, l.num_reference, l.date_vente, l.num_debut, l.num_fin, l.stock, a.label
                FROM lotcarte as l
                INNER JOIN agence as a ON l.idagence = a.rowid";
        if (is_array($requestData)) {
            $sql = "SELECT l.idlotcarte as rowid, l.num_reference, l.num_debut, l.num_fin, l.stock_init, l.stock , l.carte_retour, a.rowid as idagence, a.label, l.user_add, l.date_add, l.idtypecarte
                    FROM lotcarte as l 
                        INNER JOIN agence as a ON l.idagence = a.rowid ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {
                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        } else {
            if (!is_null($requestData)) {
                $sql .= " WHERE ( l.num_reference LIKE '%?%' ";
                $sql .= " OR l.date_vente LIKE '%?%' ";
                $sql .= " OR l.num_debut LIKE '%?%' ";
                $sql .= " OR l.num_fin LIKE '%?%' ";
                $sql .= " OR l.stock LIKE '%?%' ";
                $sql .= " OR a.label LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['l.num_reference', 'l.date_vente', 'l.num_debut', 'l.num_fin', 'l.stock', 'a.label'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        }
        try {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute($data);
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($requestData)) {
                for ($i = 0; $i < count($result); $i++) {
                    $result[$i]['stock'] = (intval($result[$i]['stock_init']) - intval($result[$i]['carte_retour']));
                    $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
                    $result[$i]['stock'] = (new \app\core\Utils())->number_format($result[$i]['stock']);
                }
            }
            return $result;
        } catch (Exception $ex) {
            return [];
        }
    }


    /********Liste beneficiaires*********/
    public function getHistoriqueDistributionCount()
    {
        try {
            $sql = "SELECT COUNT(idlotcarte) as total FROM lotcarte ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getDisponibiliteCartes($requestData = null)
    {
        $data = null;
        $sql = "SELECT a.rowid, a.code, a.label, SUM(l.stock_init) as stock_init, SUM(l.stock) as carte_vendu, SUM(l.carte_retour) as carte_retour, SUM(l.stock) as stock
                FROM lotcarte as l
                    INNER JOIN agence as a ON l.idagence = a.rowid ";
        if (!is_null($requestData)) {

            $sql .= " WHERE ( a.code LIKE '%" . $requestData . "%' ";
            $sql .= " OR a.label LIKE '%" . $requestData . "%' ";
            $sql .= " OR l.stock_init LIKE '%" . $requestData . "%' ";
            $sql .= " OR l.carte_vendu LIKE '%" . $requestData . "%' ";
            $sql .= " OR l.carte_retour LIKE '%" . $requestData . "%' ";
            $sql .= " OR l.stock LIKE '%" . $requestData . "%' ) ";


        }
        $sql .= " GROUP BY a.rowid ";
        $tabCol = ['a.code', 'a.label', 'l.stock_init', 'carte_vendu', 'l.carte_retour', 'l.stock'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= ' ORDER BY ' . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
        $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        try {
            $user = $this->getConnexion()->prepare($sql);
            //$user->execute($data);
            $user->execute();
            $result = $user->fetchAll(PDO::FETCH_ASSOC);

            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['carte_vendu'] = (intval($result[$i]['stock_init']) - intval($result[$i]['stock']));
                $result[$i]['stock'] = (intval($result[$i]['stock']) - intval($result[$i]['carte_retour']));
                $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
                $result[$i]['carte_retour'] = (new \app\core\Utils())->number_format($result[$i]['carte_retour']);
            }
            return $result;
        } catch (Exception $ex) {
            echo $ex;
            return [];
        }
    }


    /********Liste beneficiaires*********/
    public function getDisponibiliteCartesCount()
    {
        try {
            $sql = "SELECT COUNT(idlotcarte) as total FROM lotcarte ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getLotReception($exp = 'NUMHERIT', $idExp = NUMHERIT_ID, $agence = null)
    {
        $sql = "SELECT lc.idlotcarte as rowid, lc.reference, lc.num_debut, lc.num_fin, lc.stock_init, lc.stock, lc.idtypecarte
				FROM lotcarte lc
				INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte
				WHERE lc.idagencesource = :idagencesource AND lc.expediteur = :exp AND lc.typelot = 1 AND stock > 0";

        if($agence !== null) $sql .= ' AND idagencedest = :idagencedest';

        try {
            $user = $this->getConnexion()->prepare($sql);
            $user->bindValue('exp', $exp);
            $user->bindValue('idagencesource', $idExp);
            if($agence !== null) $user->bindValue('idagencedest', $agence);
            $user->execute();
            return $user->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $ex) {
            return false;
        }
    }

    public function getCartesSaleByIntevale($data)
    {
        $sql = "SELECT numero_serie FROM carte WHERE fk_agence =:idagence AND (CAST(numero_serie AS SIGNED INTEGER ) BETWEEN :debut AND :fin) ORDER BY numero_serie ASC";
        try {
            $data['debut'] = intval($data['debut']);
            $data['fin'] = intval($data['fin']);
            $user = $this->getConnexion()->prepare($sql);
            $user->execute($data);
            $resultSQL = $user->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($resultSQL as $item) array_push($result, str_pad((intval($item['numero_serie'])), 10, "0", STR_PAD_LEFT));
            $data = $this->getLotReception(['agence_retour' => $data['idagence'], 'add' => ['champs' => ' AND (CAST(num_debut AS SIGNED INTEGER ) >=:debut AND CAST(num_fin AS SIGNED INTEGER ) <=:fin) ', 'value' => ['debut' => ($data['debut'] . ""), 'fin' => ($data['fin'] . "")]]]);
            if (count($result) > 0) {

                foreach ($data as $item) {
                    for ($i = 0; $i < intval($item['stock_init']); $i++)
                        array_push($result, str_pad((intval($item['num_debut']) + $i), 10, "0", STR_PAD_LEFT));
                }
            }
            return $result;
        } catch (PDOException $ex) {
            return false;
        }
    }

    public function getLot($data = null)
    {
        $sql = "SELECT lc.*, tc.libelle as libTypeCarte FROM lotcarte lc INNER JOIN type_carte tc ON lc.idtypecarte = tc.idtypecarte ";
        if (!is_null($data)) {
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function ($one) {
                return $one . ' = ?';
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array_values($data));
        return $user->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLotByAgence($data = null)
    {
        $sql = "SELECT * FROM lotcarte ";
        if (!is_null($data)) {
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function ($one) {
                return $one . '=:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        return $user->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTypeCarte($data = null)
    {
        $sql = "SELECT * FROM type_carte ";
        if (!is_null($data)) {
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function ($one) {
                return $one . '=:' . $one;
            }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        return $user->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testReferenceExist($data, $table = 'lotcarte_reception')
    {
        $sql = "SELECT num_reference FROM $table WHERE num_reference =:num_reference";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return (count($a) > 0);
    }

    public function testValideLot($data)
    {
        $sql = "SELECT num_reference FROM lotcarte_reception 
                WHERE agence_retour is NULL AND (CAST(num_debut AS SIGNED INTEGER ) BETWEEN :num_debut AND :num_fin) OR (CAST(num_fin AS SIGNED INTEGER ) BETWEEN :num_debut AND :num_fin)";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        return ($user->rowCount() === 0);
    }

    public function insertReception($data = [])
    {
        $result = -2;
        if (intval($data['num_debut']) > intval($data['num_fin']) ||
            !$this->testValideLot(['num_debut' => intval($data['num_debut']), 'num_fin' => intval($data['num_fin'])])
        ) return $result;
        $data['stock_init'] = $data['stock'] = (intval($data['num_fin']) - intval($data['num_debut'])) + 1;
        $champs = array_keys($data);
        $sql = "INSERT INTO lotcarte_reception(" . implode(',', $champs) . ") ";
        $champs = array_map(function ($one) {
            return ':' . $one;
        }, $champs);
        $sql .= "VALUE (" . implode(',', $champs) . ")";
        try {
            $agence = $this->getConnexion()->prepare($sql);
            return ($agence->execute($data)) ? -1 : $result;
        } catch (PDOException $ex) {
            return $result;
        }
    }

    public function insertReceptionRetour($data = [])
    {

        $result = -2;
        $data['stock_init'] = $data['stock'] = (intval($data['num_fin']) - intval($data['num_debut'])) + 1;
        $champs = array_keys($data);
        $sql = "INSERT INTO lotcarte_reception(" . implode(',', $champs) . ") ";
        $champs = array_map(function ($one) {
            return ':' . $one;
        }, $champs);
        $sql .= "VALUE (" . implode(',', $champs) . ")";
        try {
            $agence = $this->getConnexion()->prepare($sql);
            //echo $rs = $agence->execute($data); die;
            return ($agence->execute($data)) ? -1 : $result;
        } catch (PDOException $ex) {
            //var_dump($ex); die;
            return $result;
        }
    }

    public function updateReception($data = [], $rowid)
    {
        $champs = array_keys($data);
        $champs = array_map(function ($one) {
            return $one . '=:' . $one;
        }, $champs);
        $data['rowid'] = $rowid;
        $sql = "UPDATE lotcarte_reception SET " . implode(',', $champs) . " WHERE idlotcarte_recu =:rowid";
        $user = $this->getConnexion()->prepare($sql);
        return $user->execute($data) ? -1 : -2;
    }

    public function updateDistribution($data = [], $rowid)
    {
        $champs = array_keys($data);
        $champs = array_map(function ($one) {
            return ($one == 'carte_retour') ? $one . ' = ' . $one . ' + :' . $one : $one . '=:' . $one;
        }, $champs);
        $data['rowid'] = $rowid;
        $sql = "UPDATE lotcarte SET " . implode(',', $champs) . " WHERE idlotcarte =:rowid";
        $user = $this->getConnexion()->prepare($sql);
        return $user->execute($data) ? -1 : -2;
    }

    public function soustraireStockLotCarte($param)
    {
        $sql = "UPDATE lotcarte SET stock = stock - :stock WHERE idlotcarte =:rowid";
        $user = $this->getConnexion()->prepare($sql);
        return $user->execute($param);
    }

    public function insertDistribution($data = [])
    {
        $dataTemp = $this->getLotReception(['idlotcarte_recu' => $data['idreception']])[0];
        $data['num_debut'] = str_pad(intval($dataTemp['num_debut']) + (intval($dataTemp['stock_init']) - intval($dataTemp['stock'])), 10, "0", STR_PAD_LEFT);;
        $result = -2;
        if (intval($data['num_debut']) > intval($data['num_fin']) || intval($data['num_fin']) > intval($dataTemp['num_fin']))
            return $result;
        $data['stock_init'] = $data['stock'] = (intval($data['num_fin']) - intval($data['num_debut'])) + 1;
        $champs = array_keys($data);
        $sql = "INSERT INTO lotcarte(" . implode(',', $champs) . ") ";
        $champs = array_map(function ($one) {
            return ':' . $one;
        }, $champs);
        $sql .= "VALUE (" . implode(',', $champs) . ")";
        try {
            $agence = $this->getConnexion()->prepare($sql);
            return ($agence->execute($data)) ? $this->updateReception(['stock' => (intval($dataTemp['stock']) - $data['stock'])], $data['idreception']) : $result;
        } catch (PDOException $ex) {
            return $result;
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

    public function commissionJULA($montant)
    {
        //A faire
        return 0;
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

    public function debiterSoldeAgence($agence, $montant)
    {
        $sql = "UPDATE agence SET solde = solde - :montant WHERE rowid = :agence";
        $user = $this->getConnexion()->prepare($sql);
        $res = $user->execute(
            array(
                'montant' => $montant,
                'agence' => $agence
            )
        );

        return $res;
    }

    public function vendreCarteJULA($montant, $commission, $nombre, $agence, $utilisateur)
    {
        $stockRestant = $this->stockJULAByAgence($agence, $montant);
        $nombreCarteJULA = 0;
        $date = date('Y-m-d H:i:s');
        $service = 30;
        $this->utils = new app\core\Utils();
        $num_transac = $this->utils->Generer_numtransaction();
        if ($stockRestant >= $nombre) {
            $cpt = 0;
            for ($i = 0; $i < $nombre; $i++) {
                $num_serie_a_vendre = $this->dernierCarteJULAVendueInSerie($agence, $montant);
                if ($num_serie_a_vendre > 0) {
                    $etasave = $this->saveCarteJULAVendue($num_serie_a_vendre, $agence, $montant, $date, $utilisateur);
                    if ($etasave == 1) {
                        $this->debiterSoldeAgence($agence, $montant);
                        $this->utils->SaveTransaction($num_transac, $service, $montant, $fk_carte = 0, $utilisateur, $statut = 1, $commentaire = 'Vente carte JULA', $commission, $agence, $transactId = $num_serie_a_vendre, $fkuser_support = 0, $fkagence_support = 0);
                        $this->destock($num_serie_a_vendre, $agence, $montant);
                        $cpt++;
                    } else {
                        return -3;
                    }
                } else {
                    return -2;
                }
            }
            if ($cpt > 0) {
                return $num_transac;
            } else {
                return -4;
            }
        } else {
            return -1;
        }


    }

    public function dernierCarteJULAVendueInSerie($agence, $montant)
    {
        $sql = "SELECT MIN(idlotcarte), num_debut, stock_init, stock FROM lotcarte_jula WHERE idagence = :agence AND montant = :montant AND stock > 0";
        $user = $this->getConnexion()->prepare($sql);
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
                return $num_serie;
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

    public function saveCarteJULAVendue($serie, $agence, $montant, $date, $utilisateur)
    {
        try {
            $sql = "INSERT INTO carte_jula(numero_serie, montant, idagence, iduser, date_vente) VALUE(:numero_serie, :montant, :idagence, :iduser, :date_vente)";
            $user = $this->getConnexion()->prepare($sql);
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

    public function destock($num_serie, $agence, $montant)
    {
        $sql = "UPDATE lotcarte_jula SET stock = stock - 1 WHERE idagence = :agence AND num_debut <= :deb AND num_fin >= :fin AND montant = :mont AND stock > 0";
        $user = $this->getConnexion()->prepare($sql);
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

    function addLot($reference, $num_debut, $num_fin, $agencesource, $agencedest, $stock, $stock_avant, $expediteur, $destinataire, $typelot, $typecarte, $idlot_origine)
    {

        $date = date('Y-m-d H:i:s');

        $dbh = $this->getConnexion();
        if ($idlot_origine < 0) {
            $idlot_origine = 0;
        }

        try {
            $insertSQL = "INSERT INTO lotcarte (reference, num_debut, num_fin, idagencesource, idagencedest, user_add, date_add, stock, stock_init, stock_avant, stock_apres, idtypecarte, typelot, expediteur, destinataire, idlot_origine) 
				  VALUES (:num_reference, :num_debut, :num_fin, :idagencesource, :idagencedest, :user_add, :date_add, :stock, :stock_init, :stock_avant, :stock_apres, :idtypecarte, :typelot, :expediteur, :destinataire, :idlot_origine)";

            $stock_apres = $stock + $stock_avant;
            $insertlotcarte = $dbh->prepare($insertSQL);
            $insertlotcarte->bindValue('num_reference', $reference);
            $insertlotcarte->bindValue('num_debut', $num_debut);
            $insertlotcarte->bindValue('num_fin', $num_fin);
            $insertlotcarte->bindValue('idagencesource', $agencesource);
            $insertlotcarte->bindValue('idagencedest', $agencedest);
            $insertlotcarte->bindValue('user_add', $_SESSION['rowid']);
            $insertlotcarte->bindValue('date_add', $date);
            $insertlotcarte->bindValue('stock', $stock);
            $insertlotcarte->bindValue('stock_init', $stock);
            $insertlotcarte->bindValue('stock_avant', $stock_avant);
            $insertlotcarte->bindValue('stock_apres', $stock_apres);
            $insertlotcarte->bindValue('idtypecarte', $typecarte);
            $insertlotcarte->bindValue('typelot', $typelot);
            $insertlotcarte->bindValue('expediteur', $expediteur);
            $insertlotcarte->bindValue('destinataire', $destinataire);
            $insertlotcarte->bindValue('idlot_origine', $idlot_origine);
            $res = $insertlotcarte->execute();
            return ($res == 1) ? $dbh->lastInsertId('lotcarte') : $res;

        } catch (\PDOException $e) {
            return -2;
        }
    }

    function saveCarte($num_debut, $num_fin, $idlot, $niveau, $typelot, $typecarte, $agence)
    {
        $num_debut = intval($num_debut);
        $num_fin = intval($num_fin);
        $cpt = false;
        while ($num_debut <= $num_fin) {
            $dbh = $this->getConnexion();
            $insertSQL = "INSERT INTO carte_stock (num_serie, idlot, niveau, typelot, typecarte, idagence) 
				  VALUES (:num_serie, :idlot, :niveau, :typelot, :typecarte, :idagence)";
            try {
                $num_debut2 = str_pad($num_debut, 10,"0", STR_PAD_LEFT);
                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('num_serie', $num_debut2);
                $insertlotcarte->bindValue('idlot', $idlot);
                $insertlotcarte->bindValue('niveau', $niveau);
                $insertlotcarte->bindValue('typelot', $typelot);
                $insertlotcarte->bindValue('typecarte', $typecarte);
                $insertlotcarte->bindValue('idagence', $agence);
                $res = $insertlotcarte->execute();
            } catch (PDOException $e) {
                return -2;
            }

            if ($res == 1) {
                if ($num_debut == $num_fin) {
                    $cpt = true;
                    break;
                }
                $num_debut++;
            } else {
                break;
            }
        }
        return $cpt;
    }

    function moveCarte($num_debut, $num_fin, $niveau = null, $typelot = null, $agence = null, $idLot){

        $num_debut = intval($num_debut);
        $num_fin = intval($num_fin);
        $cpt = 0;
        while($num_debut <= $num_fin) {
            $dbh = $this->getConnexion();

            $insertSQL = "UPDATE carte_stock SET commentaire = CONCAT(commentaire, ',', :comments), idlot = :idlot";
            if(!is_null($niveau)) $insertSQL .= ", niveau = :niveau";
            if(!is_null($typelot)) $insertSQL .= ", typelot = :typelot";
            if(!is_null($agence)) $insertSQL .= ", idagence = :agence";
            $insertSQL .= " WHERE CONVERT(`num_serie`, SIGNED INTEGER) = :num_serie";
            try {
                $num_debut2 = str_pad($num_debut, 10,"0", STR_PAD_LEFT);
                $comment = $num_debut2.'_'.$niveau.'_'.$typelot.'_'.$agence.'|';
                $insertlotcarte = $dbh->prepare($insertSQL);
                if(!is_null($niveau)) $insertlotcarte->bindValue('niveau', $niveau);
                if(!is_null($typelot)) $insertlotcarte->bindValue('typelot', $typelot);
                if(!is_null($agence)) $insertlotcarte->bindValue('agence', $agence);
                $insertlotcarte->bindValue('comments', $comment);
                $insertlotcarte->bindValue('idlot', $idLot);
                $insertlotcarte->bindValue('num_serie', intval($num_debut2));
                $res = $insertlotcarte->execute();
            } catch (PDOException $e) {
                return -2;
            }

            if ($res == 1) {
                $cpt++;
                if($num_debut == $num_fin){

                    break;
                }
                $num_debut++;
            } else {
                break;
            }
        }
        return $cpt;
    }

    public function carteSaleInLot($num_debut, $num_fin, $niveau = 'AGENCE')
    {
        $sql = "SELECT * FROM carte_stock WHERE typelot = 3 AND etatvente = 1 AND niveau = :niveau AND (CAST(num_serie AS SIGNED INTEGER ) BETWEEN :debut AND :fin) ORDER BY num_serie ASC";
        try {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(['debut'=>intval($num_debut), 'fin'=>intval($num_fin), 'niveau'=>$niveau]);
            return $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return false;
        }
    }

    public function carteDistToAgence($num_debut, $num_fin, $niveau = 'AGENCE')
    {
        $sql = "SELECT * FROM carte_stock WHERE typelot = 3 AND etatvente = 1 AND niveau = :niveau AND (CAST(num_serie AS SIGNED INTEGER ) BETWEEN :debut AND :fin) ORDER BY num_serie ASC";
        try {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(['debut'=>intval($num_debut), 'fin'=>intval($num_fin), 'niveau'=>$niveau]);
            return $user->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return false;
        }
    }

}