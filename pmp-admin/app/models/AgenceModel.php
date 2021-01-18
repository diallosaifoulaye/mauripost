<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class AgenceModel extends \app\core\BaseModel
{

    public function getAllProvince($id)
    {
        $sql = "Select idregion as idprovince, lib_region as province from departement WHERE region =:id ORDER BY lib_region ASC";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(["id"=>$id]);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return $a;
    }

    public function testKeyExist($data)
    {
        $sql = "SELECT code FROM code_rechargement WHERE code =:code";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return (count($a) > 0);
    }

    public function getKeyValidation($data)
    {
        $sql = "SELECT code FROM code_rechargement WHERE id IN (SELECT MAX(id) as id FROM code_rechargement WHERE statut = 0 AND fk_agence =:rowid) ";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(['rowid'=>$data['rowid']]);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return (count($a) > 0 && $a[0]['code'] == $data['code']) ? 1 : 0;
    }

    public function setKeyValidation($data)
    {
        do{
            $data['code'] = (new \app\core\Utils())->generation_code_validation();
        }while ($this->testKeyExist(['code'=>$data['code']]));
        $sql = "INSERT INTO code_rechargement(code, fk_agence) VALUES (:code,:rowid)";
        $user = $this->getConnexion()->prepare($sql);
        return ($user->execute($data)) ? $data['code'] : false;
    }

    public function changeEtatKeyValidation($data)
    {
        $sql = "UPDATE code_rechargement SET statut = 1 WHERE code =:code";
        $user = $this->getConnexion()->prepare($sql);
        return $user->execute($data);
    }

    public function getAllDepartement()
    {
        $sql = "Select idregion as idregion, lib_region as departement from region WHERE fk_pays = ".ID_PAYS." ORDER BY lib_region ASC";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return $a;
    }

    public function getAllTypeAgence()
    {
        $sql = "Select idtype_agence, libelle from type_agence WHERE etat = 1";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return $a;
    }

    public function getAgenceByIdInteger($id)
    {
        try{
            $sql = "SELECT a.rowid, a.etat, a.label as agence, a.code, a.email, a.tel, a.responsable, a.adresse, a.idtype_agence ,
                    t.libelle as typeAgence,  a.province as idregion,  dep.lib_region as departement, a.fk_quartier as idprovince, 
                    a.date_modification, a.user_modification, a.date_creation, a.user_creation, a.idtype_agence
                    FROM agence as a
                    INNER JOIN region as dep ON a.province = dep.idregion
                    INNER JOIN type_agence as t ON a.idtype_agence = t.idtype_agence
                    WHERE a.rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(["id" => $id]);
            $a = $user->fetch(PDO::FETCH_ASSOC);
            return $a;
        }catch (PDOException $e){
            return '';
        }

    }

    public function getAgenceByIdInteger__($id)
    {
        $sql = "SELECT a.rowid, a.solde,a.code,a.label as agence,a.date_creation, a.user_creation,a.adresse, a.date_modification, a.user_modification
                FROM agence as a
                WHERE a.rowid = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(["id" => strval($id)]);
        $a = $user->fetch(PDO::FETCH_ASSOC);
        return $a;
    }

    public function allAgence()
    {
        try {
            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte, r.lib_region
                from agence as a
                LEFT OUTER JOIN region as r
                ON a.province = r.idregion
                WHERE a.etat = :etat
                ORDER BY a.label ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function soldeGlobalAgence(){

        try {
            $sql = "Select SUM(solde) as solde
                from agence
              WHERE etat = :etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));

            $a = $user->fetchObject();
            return $a->solde;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function allTypeAgence()
    {
        try {
            $sql = "Select a.*
                from type_agence as a
                WHERE a.etat = :etat
                ORDER BY a.libelle ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getAllAgenceByType($idtype){
        try {
            $sql = "Select a.rowid, a.label
                from agence as a
                WHERE a.etat = :etat AND a.idtype_agence = :idtype_agence
                ORDER BY a.label ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "idtype_agence" => $idtype));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function allAgenceByType()
    {
        try {
            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte, r.lib_region
                from agence as a
                LEFT OUTER JOIN region as r
                ON a.province = r.idregion
                WHERE a.etat = :etat AND (idtype_agence = 1 OR idtype_agence = 2)
                ORDER BY a.label ASC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function allAgenceWithoutSelected($id)
    {
        try {
            $query_rq_transfert = "Select rowid,label as agence from agence WHERE rowid != :id";
            $rq_transfert = $this->getConnexion()->prepare($query_rq_transfert);
            $rq_transfert->bindParam("id",$id);
            $rq_transfert->execute();
            $row_rq_transfert= $rq_transfert->fetchAll();
            return json_encode($row_rq_transfert) ;
            //return $rq_transfert ;
        } catch (PDOException $exception) {
            return -1;
        }
    }




    public function allAgenceWithLot()
    {
        try {
            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte
                from agence as a INNER JOIN lotcarte as l ON a.rowid = l.idagence GROUP BY a.rowid";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function allAgenceWithLotJula()
    {
        try {
            $sql = "Select a.rowid, a.label as agence, a.code , a.adresse, a.num_carte
                from agence as a INNER JOIN lotcarte_jula as l ON a.rowid = l.idagence GROUP BY a.rowid";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /*public function getAllAgence($requestData = null)
    {
        try {
            $sql = "Select a.rowid,a.code,a.label as agence , a.responsable, a.adresse, prov.lib_region as province, a.etat
                from agence as a
                INNER JOIN region as prov ON a.province = prov.idregion  WHERE a.idtype_agence = 1";
                //INNER JOIN region as prov ON a.fk_quartier = prov.idregion  WHERE a.idtype_agence = 1";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" AND ( a.code LIKE '%".$requestData."%' ";
                $sql.=" OR a.label LIKE '%".$requestData."%' ";
                $sql.=" OR a.responsable LIKE '%".$requestData."%' ";
                $sql.=" OR a.adresse LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR a.etat LIKE '%".$etat."%' ";
                $sql.=" OR prov.lib_region LIKE '%".$requestData."%' )";
            }

            $tabCol = ['a.label','a.code','a.responsable','a.adresse','prov.lib_region','a.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            // echo $sql;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }*/


   /* public function getAllAgenceCount()
    {
        try {
            $sql = "SELECT COUNT(a.rowid) as total FROM agence as a
                INNER JOIN region as prov ON a.fk_quartier = prov.idregion  WHERE a.idtype_agence = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }*/








    public function getAllAgence($requestData = null)
    {
        try {
            $sql = "Select a.rowid, a.code,a.label as agence, t.libelle, a.responsable, a.adresse, prov.lib_region as province, a.etat
                from agence as a
                INNER JOIN region as prov ON a.province = prov.idregion
                INNER JOIN type_agence as t ON a.idtype_agence = t.idtype_agence  
                WHERE 1";

            if($requestData[0] == 3){
                $sql.=" AND a.idtype_agence = 3";
            }
            else{
                $sql.=" AND  a.idtype_agence != 3";
            }

            if ( (isset($_REQUEST['columns'][0]['search']) && $_REQUEST['columns'][0]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][1]['search']) && $_REQUEST['columns'][1]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][2]['search']) && $_REQUEST['columns'][2]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][3]['search']) && $_REQUEST['columns'][3]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][4]['search']) && $_REQUEST['columns'][4]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][5]['search']) && $_REQUEST['columns'][5]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][6]['search']) && $_REQUEST['columns'][6]['search']['value'] != '')) {
                $code = $_REQUEST['columns'][0]['search']['value'];
                $agence = $_REQUEST['columns'][1]['search']['value'];
                $libelle = $_REQUEST['columns'][2]['search']['value'];
                $responsable = $_REQUEST['columns'][3]['search']['value'];
                $adresse = $_REQUEST['columns'][4]['search']['value'];
                $province = $_REQUEST['columns'][5]['search']['value'];
                $etat = $_REQUEST['columns'][6]['search']['value'];
                $etat = (strtolower($etat) == 'activé' ) ? 1 : ((strtolower($etat) == 'desactivé') ? 0 : null);
                if($code != '')
                    $sql.=" AND  a.code LIKE '%".$code."%' ";
                if($agence != '')
                    $sql.=" AND a.label LIKE '%".$agence."%' ";
                if($libelle != '')
                    $sql.=" AND t.libelle LIKE '%".$libelle."%' ";
                if($responsable != '')
                    $sql.=" AND a.responsable LIKE '%".$responsable."%' ";
                if($adresse != '')
                    $sql.=" AND a.adresse LIKE '%".$adresse."%' ";
                if($province != '')
                    $sql.=" AND prov.lib_departement LIKE '%".$province."%' ";
                if($etat !== null)
                    $sql.=" AND e.etat = ".$etat;
                //$sql.=")";

                //echo $sql; die;

            }

            $tabCol = ['a.label','a.code','t.libelle','a.responsable','a.adresse','prov.lib_region','a.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            // echo $sql;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getAllAgenceCount($requestData = null)
    {
        try {
            $sql = "SELECT COUNT(a.rowid) as total FROM agence as a
                    INNER JOIN region as prov ON a.province = prov.idregion
                    INNER JOIN type_agence as t ON a.idtype_agence = t.idtype_agence
                    WHERE 1";
            if($requestData[0] == 3){
                $sql.=" AND a.idtype_agence = 3";
            }
            else{
                $sql.=" AND  a.idtype_agence != 3";
            }

            if ( (isset($_REQUEST['columns'][0]['search']) && $_REQUEST['columns'][0]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][1]['search']) && $_REQUEST['columns'][1]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][2]['search']) && $_REQUEST['columns'][2]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][3]['search']) && $_REQUEST['columns'][3]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][4]['search']) && $_REQUEST['columns'][4]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][5]['search']) && $_REQUEST['columns'][5]['search']['value'] != '') ||
                (isset($_REQUEST['columns'][6]['search']) && $_REQUEST['columns'][6]['search']['value'] != '')) {
                $code = $_REQUEST['columns'][0]['search']['value'];
                $agence = $_REQUEST['columns'][1]['search']['value'];
                $libelle = $_REQUEST['columns'][2]['search']['value'];
                $responsable = $_REQUEST['columns'][3]['search']['value'];
                $adresse = $_REQUEST['columns'][4]['search']['value'];
                $province = $_REQUEST['columns'][5]['search']['value'];
                $etat = $_REQUEST['columns'][6]['search']['value'];
                $etat = (strtolower($etat) == 'activé' ) ? 1 : ((strtolower($etat) == 'desactivé') ? 0 : null);
                if($code != '')
                    $sql.=" AND  a.code LIKE '%".$code."%' ";
                if($agence != '')
                    $sql.=" AND a.label LIKE '%".$agence."%' ";
                if($libelle != '')
                    $sql.=" AND t.libelle LIKE '%".$libelle."%' ";
                if($responsable != '')
                    $sql.=" AND a.responsable LIKE '%".$responsable."%' ";
                if($adresse != '')
                    $sql.=" AND a.adresse LIKE '%".$adresse."%' ";
                if($province != '')
                    $sql.=" AND prov.lib_region LIKE '%".$province."%' ";
                if($etat !== null)
                    $sql.=" AND e.etat = ".$etat;

            }


            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }





    public function getAllDistributeur($requestData = null)
    {
        try {
            $sql = "Select a.rowid,a.code,a.label as agence , a.responsable, a.adresse, prov.lib_region as province, a.etat
                from agence as a
                INNER JOIN region as prov ON a.fk_quartier = prov.idregion 
                WHERE a.idtype_agence = 3";
            if(!is_null($requestData)) {
                $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
                $sql.=" AND ( a.code LIKE '%".$requestData."%' ";
                $sql.=" OR a.label LIKE '%".$requestData."%' ";
                $sql.=" OR a.responsable LIKE '%".$requestData."%' ";
                $sql.=" OR a.adresse LIKE '%".$requestData."%' ";
                if($etat !== null) $sql.=" OR a.etat LIKE '%".$etat."%' ";
                $sql.=" OR prov.lib_region LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.label','a.code','a.responsable','a.adresse','prov.lib_region','a.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getAllDistributeurCount()
    {
        try {
            $sql = "SELECT COUNT(a.rowid) as total FROM agence as a
                INNER JOIN region as prov ON a.province = prov.idregion WHERE a.idtype_agence = 3";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getAllSoldeAgence(){
        try
        {
            $sql = "Select a.rowid, a.code,a.label as agence,a.adresse,a.solde
                from agence as a ORDER BY a.label ASC";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_OBJ);
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }


    public function getAllAgence__($requestData = null)
    {
        try
        {
            $sql = "Select a.rowid, a.code,a.label as agence,a.adresse,a.solde
                from agence as a  WHERE a.idtype_agence = 1";
            if($_REQUEST['search']['value']!=""){
                $sql.=" AND ( a.code LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.label LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.adresse LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.solde LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $tabCol = ['a.code','a.label','a.adresse','a.solde'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }


    public function getAllAgence__Count()
    {
        try {
            $sql = "SELECT a.rowid, a.code,a.label as agence,a.adresse,a.solde FROM agence as a WHERE a.idtype_agence = 1";
            if($_REQUEST['search']['value']!=""){
                $sql.=" AND ( a.code LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.label LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.adresse LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR a.solde LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getAllDistributeur__($requestData = null)
    {
        try
        {
            $sql = "Select a.rowid, a.code,a.label as agence,a.adresse,a.solde
                from agence as a WHERE a.idtype_agence = 3";
            if(!is_null($requestData)){
                $sql.=" AND ( a.code LIKE '%".$requestData."%' ";
                $sql.=" OR a.label LIKE '%".$requestData."%' ";
                $sql.=" OR a.adresse LIKE '%".$requestData."%' ";
                $sql.=" OR a.solde LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.code','a.label','a.adresse','a.solde'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }


    public function getAllDistributeur__Count()
    {
        try {
            $sql = "SELECT COUNT(a.rowid) as total FROM agence as a WHERE a.idtype_agence = 3";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function isValideModel($data = [])
    {
        try
        {
            $sql = "SELECT rowid FROM agence ";
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

    public function verifyCode($code)
    {
        $sql = "Select code
                from agence
                WHERE code = :etat";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => strval($code),
            )
        );
        $a = $user->rowCount();
        return $a;

    }

    public function agenceByCode($data)
    {

        $sql = "SELECT a.rowid, a.label as agence, a.code, a.tel, a.email , a.adresse, a.responsable, a.solde, a.rowid as soldeCompte
                FROM agence as a";
        if(count($data) > 0){
            $temp = $data;
            $champs = array_keys($temp);
            $champs = array_map(function($one){return $one.'=:'.$one;},$champs);
            $sql.=" WHERE ".implode(' AND ',$champs);
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute($data);
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        if(count($a) > 0)$a[0]['soldeCompte'] = $this->getSoldeCompte();
        return $a;
    }



    public function getSoldeCompte()
    {
        $sql = "SELECT solde FROM comptes WHERE rowid = 1";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        return $a[0]['solde'];
    }

    /***************DetailAgenceByCode*************/
    public function agenceDetailByCode($code)
    {
        try
        {
            $sql = "SELECT a.rowid, a.label as agence, a.code, a.tel, a.email , a.adresse, a.solde FROM agence as a WHERE a.etat =:etat AND a.code =:code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "code" => $code));
            $a = $user->fetchObject();
            $rows = $user->rowCount();
            if($rows > 0) return $a;
            else return -1;
        }
        catch (PDOException $e)
        {
            return -2;
        }
    }


    /***************DetailAgenceByCode*************/
    public function agenceDetailById($id)
    {
        try
        {
            $sql = "SELECT a.rowid, a.label as agence, a.code, a.tel, a.email , a.adresse, a.solde FROM agence as a WHERE a.etat =:etat AND a.rowid =:rowid";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "rowid" => $id));
            $a = $user->fetchObject();
            $rows = $user->rowCount();
            if($rows > 0) return $a;
            else return -1;
        }
        catch (PDOException $e)
        {
            return -2;
        }
    }




    function updateAgenceModel($data = [])
    {
        $data['date_modification'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $temp = $data;
        unset($temp['rowid']);
        $champs = array_keys($temp);
        $champs = array_map(function($one){return $one.'=:'.$one;},$champs);
        $sql = "UPDATE agence
                SET ".implode(',',$champs)."
                WHERE rowid = :rowid";
        try{


            $agence = $this->getConnexion()->prepare($sql);
            $res = $agence->execute($data);
            return $res;
        }catch(PDOException $ex){
            //echo $ex;
            //die;
            return false;
        }
    }

    function insertAgenceModel($data = [])
    {
        //var_dump($data);die;
        $data['solde'] = 0;
        $data['date_creation'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $champs = array_keys($data);
        $sql = "INSERT INTO agence(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";
        try{
            $agence = $this->getConnexion()->prepare($sql);
            return $agence->execute($data);
        }catch(PDOException $ex){
            return false;
        }
    }

    /*************Return Id Agence Par Code*************/
    public function returnIdagenceByCode($code)
    {
        try
        {
            $sql = "SELECT a.rowid from agence as a WHERE a.etat = :etat AND a.code =:code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "code" => $code));
            $a = $user->fetchObject();
            return $a->rowid;
        }
        catch(PDOException $e)
        {
            return -2;
        }

    }

    /***********Consulter solde agence***************/
    public function consulterSoldeAgence($code)
    {
        try
        {
            $sql = "SELECT solde from agence  WHERE etat=:etat AND rowid=:code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat"=>1, "code"=>$code));
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

    public function crediterAgenceByTransactionnelle($data = [])
    {
        $db = $this->getConnexion();
        $date_validation = date('Y-m-d H:i:s');
        $rowidd=base64_decode($data['rowiddmd']);
        //var_dump($rowidd);exit;
        $valide = [];
        try{
            $db->beginTransaction();
            array_push($valide,$this->debiter_soldeCompte($data['solde']));
            array_push($valide,$this->crediter_soldeAgence($data['solde'],$data['rowid']));
            array_push($valide,$this->saveTransactionCarte($this->generateNumeroTransaction(),(new \app\core\Utils())->getDateNow('WITH_TIME'),$data['solde'],1,$data['idUser'],$data['rowid'],'', 1));
            if(in_array(-1,$valide,true) || in_array(-2,$valide,true)) {
                $valide = false;
                $db->rollback();
            }
            else{

                $update=$this->validerDemandeCB($date_validation,$data['idUser'],$rowidd);
                if ($update==1){
                    $db->commit();
                    $valide = true;
                }else{
                    $db->rollback();
                }

            }
        }
        catch(PDOException $e)
        {
            $valide = false;
            $db->rollback();
        }
        return $valide;
    }

    /***************crediter solde agence***************/
    function crediter_soldeCompte($montant)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE comptes SET solde=solde+:soldes WHERE rowid=1");
            $Result1 = $req->execute(["soldes"=>$montant]);
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

    /***************debiter solde agence***************/
    function debiter_soldeCompte($montant)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE comptes SET solde=solde-:soldes WHERE rowid=1");
            $Result1 = $req->execute(["soldes"=>$montant]);
            return ($Result1 > 0) ? 1 : -1;
        } catch(PDOException $e) {
            return -2;
        }
    }

    /***************crediter solde agence***************/
    function crediter_soldeAgence($montant, $row_agence)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE agence SET solde=solde+:soldes WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$row_agence));
            if($Result1 > 0){
                return  1;
            }
            else return -1;
        }catch(PDOException $e) {
            return -2;
        }
    }

    /***************debiter solde agence***************/
    function debiter_soldeAgence($montant, $row_agence)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE agence SET solde=solde-:soldes WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$row_agence));
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

    /**********Save transaction agence***********/
    public function saveTransactionCarte($num_transac, $date_transaction, $montant, $statut, $fkuser, $fk_agence, $comment, $type=1)
    {
        try
        {
            $sql = "INSERT INTO transaction_carte (num_transac, date_transaction, montant, statut, fkuser, fk_agence, commentaire, type_transaction) VALUES (:num_transac,:date_transaction , :montant, :statut, :fkuser, :fk_agence, :comment, :type_transaction)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("num_transac" =>$num_transac, "date_transaction" =>$date_transaction, "montant" =>$montant, "statut" =>$statut, "fkuser" =>$fkuser, "fk_agence" =>$fk_agence, "comment" => $comment, "type_transaction" => $type));
            if($res==1)
            {
                return 1;
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

    /***********generer Numero Transaction Agence***************/
    public function generateNumeroTransaction(){
        $found = 0;
        do
        {
            $code = $this->random(15);
            $etat = $this->verifyTransaction($code);
            if($etat == 1)
            {
                $found = 1;
            }
        }
        while($found == 0);
        return $code;
    }

    /*********verififier Numero Transaction**********/
    public function verifyTransaction($code)
    {
        try
        {
            $sql = "SELECT rowid from transaction_carte WHERE num_transac =:code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("code" => $code));
            $a = $user->rowCount();
            if($a > 0)
            {
                return 0;
            }
            else
            {
                return 1;
            }
        }
        catch(Exception $e)
        {
            return -2;
        }
    }

    /**********************************Transfert Agence vers Agence***********************************************/
    public function transfertAgenceVersAgence($agenceSource, $agenceDestination, $montant, $fkuser, $fkagence)
    {
        $soldeAgenceSource = $this->consulterSoldeAgence($agenceSource);
        if($soldeAgenceSource > $montant)
        {
            $numtransaction = $this->generateNumeroTransaction();
            $montant1 = (-1*$montant);
            $statut = 0;
            $date_transaction = date('Y-m-d H:i:s');
            if(strlen($numtransaction) == 15)
            {
                $debiterSource = $this->debiter_soldeAgence($montant, $agenceSource);
                if($debiterSource == 1)
                {
                    $crediterDestination = $this->crediter_soldeAgence($montant, $agenceDestination);
                    if($crediterDestination==1)
                    {
                        $statut=1;
                        $comment = 'Transfert agence vers agence: Agence source'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                        //$comment2 = 'Transfert agence vers agenc: Agence'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                        $montantsource = -$montant;
                        $saveTransaction = $this->saveTransactionCarte($numtransaction, $date_transaction, $montantsource, $statut, $fkuser, $agenceSource, $comment, 2);
                        $saveTransaction = $this->saveTransactionCarte($numtransaction, $date_transaction, $montant, $statut, $fkuser, $agenceDestination, $comment, 2);
                        if($saveTransaction==1)
                        {
                            $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceSource, $montant1, 0);
                            $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceDestination, $montant, 1);
                        }
                        return 1;
                    }
                    else
                    {
                        $recrediterSource = $this->crediter_soldeAgence($montant, $agenceSource);
                        $comment = 'Transfert agence vers agence: Agence source'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                        //$comment2 = 'Transfert agence vers agenc: Agence'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                        $montantsource = -$montant;
                        $this->saveTransactionCarte($numtransaction, $date_transaction, $montantsource, $statut, $fkuser, $agenceSource, $comment, 2);
                        $this->saveTransactionCarte($numtransaction, $date_transaction, $montant, $statut, $fkuser, $agenceDestination, $comment, 2);
                        $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceSource, $montant1, 0);
                        $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceDestination, $montant, 1);
                        return -1;
                    }
                }
                else
                {
                    $comment = 'Transfert agence vers agence: Agence source'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                    //$comment2 = 'Transfert agence vers agenc: Agence'.$agenceSource.' vers agence :'.$agenceDestination.' Montant:'.$montant.' succès';
                    $montantsource = -$montant;
                    $this->saveTransactionCarte($numtransaction, $date_transaction, $montantsource, $statut, $fkuser, $agenceSource, $comment, 2);
                    $this->saveTransactionCarte($numtransaction, $date_transaction, $montant, $statut, $fkuser, $agenceDestination, $comment, 2);
                    $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceSource, $montant1, 0);
                    $this->save_DetailTransactionCarte($numtransaction, $date_transaction, $agenceDestination, $montant, 1);
                    return -2;
                }
            }
        }
        else
        {
            return -3;
        }
    }

    /**********Save detail transaction agence***********/
    public function save_DetailTransactionCarte($numtransaction, $date_transaction, $fkagence, $montant, $sens)
    {
        try
        {
            $sql = "INSERT INTO detail_transaction_carte(numtransaction, fkagence, montant, sens, date_op) VALUES (:numtransaction, :fkagence, :montant, :sens, :date_op)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("numtransaction" =>$numtransaction, "fkagence" =>$fkagence, "montant" =>$montant, "sens" =>$sens, "date_op" =>$date_transaction));
            if($res==1) return 1;
            else return -1;
        }
        catch(Exception $e)
        {
            return -2;
        }
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


    public function searchTransaction($datedeb, $datefin)
    {
        try
        {
            $sql = "SELECT DISTINCT t.rowid, t.fk_agence, t.fkuser, t.num_transac, t.montant, t.date_transaction, t.type_transaction
            FROM transaction_carte as t
            WHERE t.statut = :statut
            AND DATE(t.date_transaction) >=:date1
            AND DATE(t.date_transaction) <=:date2
            ORDER BY t.date_transaction DESC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("statut" =>1, "date1" =>$datedeb, "date2" =>$datefin));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a;
            else return -1;
        }
        catch (PDOException $e)
        {
            return -2;
        }
    }

    public function typeAgenceByIdAgence($id)
    {
        try {
            $sql = "Select a.idtype_agence
                from agence as a WHERE rowid = :etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => $id));
            $a = $user->fetchObject();
            return $a->idtype_agence;
        } catch (PDOException $exception) {
            return -1;
        }
    }



    public function saveDemande_credit_bureau($montant, $agence_a_crediter, $fk_user_demande)
    {
        try
        {
            $sql = "INSERT INTO demande_credit_bureau (montant, agence_a_crediter, fk_user_demande) VALUES (:montant, :agence_a_crediter, :fk_user_demande)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("montant" =>$montant, "agence_a_crediter" =>$agence_a_crediter, "fk_user_demande" =>$fk_user_demande));
            if($res==1)
            {
                return 1;
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

    public function allDemandeCB()
    {
        try {
            $sql = "Select d.*, a.label as agence, a.code , a.adresse, a.num_carte
                from demande_credit_bureau as d
                LEFT OUTER JOIN agence as a
                ON a.rowid = d.agence_a_crediter";
            $user = $this->getConnexion()->prepare($sql);
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /**************Liste des demandes  en attente *********/

    public function  DemandeCB_en($requestData = null)
    {
        try
        {
            $sql = "Select d. rowid, d.date_demande, a.label as agence, d.montant, CONCAT(u.nom ,' ',u.prenom) as demandeur
                    from demande_credit_bureau as d
                    LEFT OUTER JOIN agence as a
                    ON a.rowid = d.agence_a_crediter
			        LEFT OUTER JOIN user as u
			        ON u.rowid = d.fk_user_demande WHERE d.etat=0";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( d.date_demande LIKE '%".$requestData."%' )";
                $sql.=" OR a.label LIKE '%".$requestData."%' )";
                $sql.=" OR d.montant LIKE '%".$requestData."%' )";
                $sql.=" OR u.nom LIKE '%".$requestData."%' )";
                $sql.=" OR u.prenom LIKE '%".$requestData."%' )";

            }
            // var_dump($sql);exit;
            $tabCol = ['d.date_demande','agence','d.montant','demandeur'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  DemandeCB_enCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(d.rowid) as total
                    from demande_credit_bureau as d WHERE d.etat=0";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }
    /**************Liste des demandes autorisés*********/

    public function  DemandeCB_au($requestData = null)
    {
        try
        {
            $sql = "Select d. rowid, d.date_demande,a.label as agence, d.montant, CONCAT(u.nom ,' ',u.prenom) as demandeur
                    from demande_credit_bureau as d
                    LEFT OUTER JOIN agence as a
                    ON a.rowid = d.agence_a_crediter
			        LEFT OUTER JOIN user as u
			        ON u.rowid = d.fk_user_demande WHERE d.etat=1";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( d.date_demande LIKE '%".$requestData."%' )";
                $sql.=" OR a.label LIKE '%".$requestData."%' )";
                $sql.=" OR d.montant LIKE '%".$requestData."%' )";
                $sql.=" OR u.nom LIKE '%".$requestData."%' )";
                $sql.=" OR u.prenom LIKE '%".$requestData."%' )";

            }
            //var_dump($sql);exit;
            $tabCol = ['d.date_demande','agence','d.montant','demandeur'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  DemandeCB_auCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(d.rowid) as total
                    from demande_credit_bureau as d WHERE d.etat=1";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /**************Liste des demandes autorisés*********/

    public function  DemandeCB_v($requestData = null)
    {
        try
        {
            $sql = "Select d. rowid, d.date_demande,a.label as agence, d.montant, CONCAT(u.nom ,' ',u.prenom) as demandeur
                    from demande_credit_bureau as d
                    LEFT OUTER JOIN agence as a
                    ON a.rowid = d.agence_a_crediter
			        LEFT OUTER JOIN user as u
			        ON u.rowid = d.fk_user_demande WHERE d.etat=2";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( d.date_demande LIKE '%".$requestData."%' )";
                $sql.=" OR a.label LIKE '%".$requestData."%' )";
                $sql.=" OR d.montant LIKE '%".$requestData."%' )";
                $sql.=" OR u.nom LIKE '%".$requestData."%' )";
                $sql.=" OR u.prenom LIKE '%".$requestData."%' )";

            }
            //var_dump($sql);exit;
            $tabCol = ['d.date_demande','agence','d.montant','demandeur'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  DemandeCB_vCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(d.rowid) as total
                    from demande_credit_bureau as d WHERE d.etat=2";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getDemandeCBById($rowid)
    {
        $sql = "Select d. rowid, d.date_demande,a.label as agence, d.montant, CONCAT(u.nom ,' ',u.prenom) as demandeur, d.etat, a.rowid as idagence, a.solde as soldeCompte, d.date_autorisation, d.date_validation
                    from demande_credit_bureau as d
                    LEFT OUTER JOIN agence as a
                    ON a.rowid = d.agence_a_crediter
			        LEFT OUTER JOIN user as u
			        ON u.rowid = d.fk_user_demande WHERE d.rowid =" .$rowid;

        // var_dump($sql);exit;
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $a = $user->fetchAll(PDO::FETCH_ASSOC);
        $this->closeConnexion();
        return $a;

    }

    public function autoriserDemandeCB($date_autorisation,$fk_user_autorise,$rowid){

        try{

            $sql = "UPDATE demande_credit_bureau SET etat = :etat, date_autorisation  = :date_autorisation, fk_user_autorise = :fk_user_autorise
                WHERE rowid = :rowid";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(1),
                "date_autorisation" => $date_autorisation,
                "fk_user_autorise" => $fk_user_autorise,
                "rowid" => intval($rowid),
            ));
            $this->closeConnexion();
            if($res)
                return 1;
            else
                return -1;
        }
        catch(PDOException $Exception ){
            return -1;
        }

    }
    public function validerDemandeCB($date_validation,$fk_user_validation,$rowid){

        try{

            $sql = "UPDATE demande_credit_bureau SET etat = :etat, date_validation  = :date_validation, fk_user_validation = :fk_user_validation
                WHERE rowid = :rowid";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(2),
                "date_validation" => $date_validation,
                "fk_user_validation" => $fk_user_validation,
                "rowid" => intval($rowid),
            ));

            $this->closeConnexion();
            if($res)
                return 1;
            else
                return -1;
        }
        catch(PDOException $Exception ){
            return -1;
        }

    }

    /**********verifier Email**********/
    public function verifEmail($login)
    {
        try
        {
            $sql = "Select u.rowid from agence as u WHERE u.email=:login";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("login"=>$login));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

    /**********verifier Coce**********/
    public function verifCode($login)
    {
        try
        {
            $sql = "Select u.rowid from agence as u WHERE u.code=:login";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("login"=>$login));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }

    /******Service utilisé par distributeur******/
    public function allServiceDistributeur()
    {
        try
        {
           /* $sql = "SELECT s.rowid, s.label, t.taux
              FROM service s
              LEFT JOIN taux_commission_distributeur t ON t.fk_service = s.rowid
              WHERE s.distributeur = 1
              AND t.fk_distributeur = ".$distributeur;*/

            $sql = "SELECT s.rowid, s.label FROM service s WHERE s.distributeur = 1 ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch(PDOException $exception)
        {
            return -1;
        }
    }


    /******Service utilisé par distributeur******/
    public function allServiceDistributeurId($distributeur)
    {
        try
        {
               $sql = "SELECT t.rowid, s.label, t.taux
               FROM taux_commission_distributeur t
               LEFT JOIN service s ON t.fk_service = s.rowid
               WHERE s.distributeur = 1
               AND t.fk_distributeur = ".$distributeur;

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch(PDOException $exception)
        {
            return -1;
        }
    }


    /*********** SUPPRIMER TAUX COMMISSION *****/
    public function deleteTauxCommission($id)
    {
        try
        {
            $sql = "DELETE FROM taux_commission_distributeur WHERE fk_distributeur = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("id" => intval($id)));
            $this->closeConnexion();
        }
        catch(PDOException $Exception ){
            $res = false;
        }
        return $res;
    }
    /*********************PARAMETRER TAUX COMMISSION DISTRIBUTEUR****/
    public function ajouterTauxCommission($distributeur, $service, $taux)
    {
        try
        {
            $sql = "INSERT INTO taux_commission_distributeur(fk_distributeur, fk_service, taux) VALUES (:distributeur, :service, :taux)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "distributeur" => intval($distributeur),
                "service" => intval($service),
                "taux" => $taux
            ));
            $this->closeConnexion();
            if($res==1){
                return 1;
            }
            else{
                return 0;
            }
        }
        catch(PDOException $Exception )
        {
            return -3;
        }
    }



    /*************** update taux distributeur ***************/
    function updateTauxCommission($rowid, $taux)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE taux_commission_distributeur SET taux=:taux WHERE rowid=:agence");
            $Result1 = $req->execute(array("taux"=>$taux, "agence"=>$rowid));
            if($Result1 > 0) return  1;
            else return -1;
        }
        catch(PDOException $e) {
            return -2;
        }
    }


    public function tauxDetail($id)
    {
        try
        {
            $sql = "SELECT t.rowid, t.taux, s.label, t.fk_distributeur, t.fk_service
                    FROM taux_commission_distributeur t 
                    INNER JOIN service s ON t.fk_service = s.rowid
                    WHERE t.rowid = ".$id;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        }
        catch(PDOException $exception)
        {
            return -1;
        }
    }
}