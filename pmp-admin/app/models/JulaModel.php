<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class JulaModel extends \app\core\BaseModel
{

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES COMPTES                                             //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function __getReference($arg = 'lotcarte_reception_jula')
    {
        do {
            $code = (new \app\core\Utils())->generateur();
        } while ($this->testReferenceExist(['num_reference' => $code], $arg));
        return $code;
    }

    public function getHistoriqueReception($requestData = null)
    {
        $data = null;
        $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, montant, stock_init, stock
                FROM lotcarte_reception_jula ";

        /*if(is_array($requestData)){
            $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, montant, stock_init, stock, user_add, date_add, agence_retour
                    FROM lotcarte_reception_jula ";
            $champs = array_keys($requestData);
            $champs = array_map(function ($one) {return $one . ' =:' . $one;}, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
            $data = $requestData;
        }else{
            if (!is_null($requestData)) {
                $sql .= " WHERE ( num_reference LIKE '%?%' ";
                $sql .= " OR date_reception LIKE '%?%' ";
                $sql .= " OR num_debut LIKE '%?%' ";
                $sql .= " OR num_fin LIKE '%?%' ";
                $sql .= " OR stock_init LIKE '%?%' ";
                $sql .= " OR stock LIKE '%?%' ) ";
                $data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
            }
            $tabCol = ['num_reference', 'date_reception', 'num_debut', 'num_fin', 'montant', 'stock_init', 'stock'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        }*/


        if ($_REQUEST['search']['value']!="") {
            $sql .= " WHERE ( num_reference LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR date_reception LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR num_debut LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR num_fin LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR stock LIKE '%".$_REQUEST['search']['value']."%' ) ";
            //$data = [$requestData, $requestData, $requestData, $requestData, $requestData, $requestData];
        }
        $tabCol = ['num_reference', 'date_reception', 'num_debut', 'num_fin', 'montant', 'stock_init', 'stock'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
        $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $result = $user->fetchAll(PDO::FETCH_ASSOC);
        for($i = 0 ; $i < count($result) ; $i++){
            $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
        }
        return $result;
    }


    /********Liste beneficiaires*********/
    public function getHistoriqueReceptionCount()
    {
        try {
            $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, montant, stock_init, stock FROM lotcarte_reception_jula ";

            if ($_REQUEST['search']['value']!="") {
                $sql .= " WHERE ( num_reference LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR date_reception LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR num_debut LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR num_fin LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR stock LIKE '%".$_REQUEST['search']['value']."%' ) ";
            }

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }





    public function getHistoriqueDistribution($requestData = null)
    {
        $data = null;
        $sql = "SELECT l.idlotcarte as rowid, l.num_reference, l.date_vente, l.num_debut, l.num_fin, l.montant, l.stock_init, l.stock, a.label
                FROM lotcarte_jula as l
                INNER JOIN agence as a ON l.idagence = a.rowid";


        if ($_REQUEST['search']['value']!="") {
            $sql .= " WHERE ( l.num_reference LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.date_vente LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.num_debut LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.num_fin LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
            //$sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR a.label LIKE '%".$_REQUEST['search']['value']."%' ) ";
        }

        $tabCol = ['l.num_reference', 'l.date_vente', 'l.num_debut', 'l.num_fin', 'l.montant', 'l.stock_init', 'l.stock', 'a.label'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
        $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];


        try{
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($requestData)){
                for($i = 0 ; $i < count($result) ; $i++){
                    $result[$i]['stock'] = (intval($result[$i]['stock_init']) - intval($result[$i]['carte_retour']));
                    $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
                    //$result[$i]['stock'] = (new \app\core\Utils())->number_format($result[$i]['stock']);
                }
            }
            return $result;
        }catch(Exception $ex){
            return [];
        }
    }



    /********Liste beneficiaires*********/
    public function getHistoriqueDistributionCount()
    {
        try {

            $sql = "SELECT l.idlotcarte as rowid, l.num_reference, l.date_vente, l.num_debut, l.num_fin, l.montant, l.stock_init,l.stock, a.label 
                    FROM lotcarte_jula as l                                                                                          
                    INNER JOIN agence as a ON l.idagence = a.rowid";

            if ($_REQUEST['search']['value']!="") {
                $sql .= " WHERE ( l.num_reference LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.date_vente LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.num_debut LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.num_fin LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR a.label LIKE '%".$_REQUEST['search']['value']."%' ) ";
            }

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }




    public function getDisponibiliteCartes($requestData = null)
    {
        $data = null;
        $sql = "SELECT a.rowid, a.code, a.label, l.stock_init, l.stock as carte_vendu, l.carte_retour, l.stock, l.montant
                FROM lotcarte_jula as l
                    INNER JOIN agence as a ON l.idagence = a.rowid ";
        if ($_REQUEST['search']['value']!="") {

            $sql .= " WHERE ( a.code LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR a.label LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.carte_retour LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ) ";


        }
        $tabCol = ['a.code', 'a.label', 'l.stock_init', 'carte_vendu', 'l.carte_retour', 'l.stock', 'l.montant'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= ' ORDER BY '.$tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
        $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        try{
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $result = $user->fetchAll(PDO::FETCH_ASSOC);
            for($i = 0 ; $i < count($result) ; $i++){
                $result[$i]['carte_vendu'] = (intval($result[$i]['stock_init']) - intval($result[$i]['stock']));
                $result[$i]['stock'] = ($result[$i]['stock']) - intval($result[$i]['carte_retour']);
                $result[$i]['stock_init'] = (new \app\core\Utils())->number_format($result[$i]['stock_init']);
                $result[$i]['carte_retour'] = (new \app\core\Utils())->number_format($result[$i]['carte_retour']);
                //$result[$i]['montant'] = (new \app\core\Utils())->number_format($result[$i]['montant']);
                //$result[$i]['montant'] = $result[$i]['montant'];
            }
            return $result;
        }catch(Exception $ex){
            return [];
        }
    }


    /********Liste beneficiaires*********/
    public function getDisponibiliteCartesCount()
    {
        try {
            $sql = "SELECT a.rowid, a.code, a.label, l.stock_init, l.stock as carte_vendu, l.carte_retour, l.stock, l.montant 
                    FROM lotcarte_jula as l                                                                                   
                        INNER JOIN agence as a ON l.idagence = a.rowid ";

            if ($_REQUEST['search']['value']!="") {
                $sql .= " WHERE ( a.code LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR a.label LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.stock_init LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.carte_retour LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql .= " OR l.stock LIKE '%".$_REQUEST['search']['value']."%' ) ";

            }
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getLotReception($data = null)
    {
        $sql = "SELECT idlotcarte_recu as rowid, num_reference, date_reception, num_debut, num_fin, montant, stock_init, stock FROM lotcarte_reception_jula WHERE stock > 0 ";
        if (!is_null($data)) {
            if(isset($data['add'])){
                $sql .= $data['add']['champs'];
                $value = $data['add']['value'];
                unset($data['add']);
            }
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function ($one) {

                return $one . ' =:' . $one;
            }, $champs);
            $sql .= " AND " . implode(' AND ', $champs);
        }

        if(isset($value)) $data = array_merge($data,$value);
        try{
            $user = $this->getConnexion()->prepare($sql);
            $user->execute($data);
            return $user->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $ex){
            return false;
        }
    }

    public function getCartesSaleAndReturnByIntevale($data)
    {
        $sql = "SELECT numero_serie FROM carte_jula WHERE idagence = :idagence AND (CAST(numero_serie AS SIGNED INTEGER ) BETWEEN :debut AND :fin) ORDER BY numero_serie ASC";
        try{
            $data['debut'] = intval($data['debut']);
            $data['fin'] = intval($data['fin']);
            $user = $this->getConnexion()->prepare($sql);
            $user->execute($data);
            $resultSQL = $user->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($resultSQL as $item) array_push($result,str_pad((intval($item['numero_serie'])), 13, "0", STR_PAD_LEFT));
            $data = $this->getLotReception(['agence_retour'=>$data['idagence'],'add'=>['champs'=>' AND (CAST(num_debut AS SIGNED INTEGER ) >=:debut AND CAST(num_fin AS SIGNED INTEGER ) <=:fin) ','value'=>['debut'=>($data['debut'].""),'fin'=>($data['fin']."")]]]);
            foreach ($data as $item) {
                for($i = 0 ; $i < intval($item['stock_init']) ; $i++)
                    array_push($result,str_pad((intval($item['num_debut']) + $i), 13, "0", STR_PAD_LEFT));
            }

            return $result;
        }catch(PDOException $ex){
            return false;
        }
    }

    public function getLotByAgence($data = null)
    {
        $sql = "SELECT * FROM lotcarte_jula ";
        if (!is_null($data)) {
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function ($one) { return $one . '=:' . $one; }, $champs);
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
            $champs = array_map(function ($one) { return $one . '=:' . $one; }, $champs);
            $sql .= " WHERE " . implode(' AND ', $champs);
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        return $user->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testReferenceExist($data, $table = 'lotcarte_reception_jula')
    {
        $sql = "SELECT num_reference FROM $table WHERE num_reference =:num_reference";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return (count($a) > 0);
    }

    public function testValideLot($data)
    {
        $sql = "SELECT num_reference FROM lotcarte_reception_jula 
                WHERE agence_retour IS NULL AND (CAST(num_debut AS SIGNED INTEGER ) BETWEEN :num_debut AND :num_fin) OR (CAST(num_fin AS SIGNED INTEGER ) BETWEEN :num_debut AND :num_fin)";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        return ($user->rowCount() === 0);
    }

    public function insertReception($data = [])
    {
        $result = -2;
        $data['stock_init'] = $data['stock'] = (intval($data['num_fin']) - intval($data['num_debut'])) + 1;
        $champs = array_keys($data);
        $sql = "INSERT INTO lotcarte_reception_jula(" . implode(',', $champs) . ") ";
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
        $sql = "INSERT INTO lotcarte_reception_jula(" . implode(',', $champs) . ") ";
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

    public function getMontantCarteLOt($idlot)
    {
        $sql = "SELECT montant FROM lotcarte_jula WHERE idlotcarte = ".$idlot;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        return $user->fetchObject()->montant;
    }

    public function getCurrentStock($idlot)
    {
        $sql = "SELECT stock FROM lotcarte_jula WHERE idlotcarte = ".$idlot;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        return $user->fetchObject()->stock;
    }



    public function updateReception($data = [], $rowid)
    {
        $champs = array_keys($data);
        $champs = array_map(function ($one) {
            return $one . '=:' . $one;
        }, $champs);
        $data['rowid'] = $rowid;
        $sql = "UPDATE lotcarte_reception_jula SET " . implode(',', $champs) . " WHERE idlotcarte_recu =:rowid";
        $user = $this->getConnexion()->prepare($sql);
        return $user->execute($data) ? -1 : -2;
    }

    public function updateDistribution($data = [], $rowid)
    {
        try {
            $champs = array_keys($data);
            $champs = array_map(function ($one) {
                return ($one == 'carte_retour') ? $one . ' = ' . $one . ' + :' . $one : $one . '=:' . $one;
            }, $champs);
            $data['rowid'] = $rowid;
            $sql = "UPDATE lotcarte_jula SET " . implode(',', $champs) . " WHERE idlotcarte =:rowid";
            $user = $this->getConnexion()->prepare($sql);
            return $user->execute($data) ? -1 : -2;
        }
        catch (PDOException $ex) {
            return -2;
        }
    }

    public function insertDistribution($data = [])
    {
        $dataTemp = $this->getLotReception(['idlotcarte_recu' => $data['idreception']])[0];
        $data['num_debut'] = str_pad(intval($dataTemp['num_debut']) + (intval($dataTemp['stock_init']) - intval($dataTemp['stock'])), 13, "0", STR_PAD_LEFT);;
        $result = -2;
        if (intval($data['num_debut']) > intval($data['num_fin']) || intval($data['num_fin']) > intval($dataTemp['num_fin']))
            return $result;
        $data['stock_init'] = $data['stock'] = (intval($data['num_fin']) - intval($data['num_debut'])) + 1;
        $data['montant'] = $dataTemp['montant'];
        $champs = array_keys($data);
        $sql = "INSERT INTO lotcarte_jula(" . implode(',', $champs) . ") ";
        $champs = array_map(function ($one) { return ':' . $one; }, $champs);
        $sql .= "VALUE (" . implode(',', $champs) . ")";
        try {
            $agence = $this->getConnexion()->prepare($sql);
            return ($agence->execute($data)) ? $this->updateReception(['stock' => (intval($dataTemp['stock']) - $data['stock'])], $data['idreception']) : $result;
        } catch (PDOException $ex) {
            return $result;
        }
    }

}