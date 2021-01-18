<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class MarchandModel extends \app\core\BaseModel
{
    public function getMarchand($data = [],$requestData = null)
    {
        $requete = "SELECT marchand.idmarchand as rowid, marchand.nom_marchand, carte_marchand.code_guichet, carte_marchand.carte, marchand.rc_ninea, marchand.email, marchand.telmobile, marchand.adresse, marchand.statut";
        if(isset($data['champs'])) {
            $requete .= $data['champs'];
            unset($data['champs']);
        }
        $requete .= " FROM marchand 
                          INNER JOIN carte_marchand ON marchand.idmarchand = carte_marchand.idmarchand ";
        if(count($data) > 0){
            $champs = array_keys($data);
            $champs = array_map(function($one){return 'marchand.'.$one.'=:'.$one;},$champs);
            $requete.=" WHERE ".implode(' AND ',$champs);
            if(!is_null($requestData)){
                $requete.=" AND ( marchand.nom_marchand LIKE '%".$requestData."%' ";
                $requete.=" OR carte_marchand.code_guichet LIKE '%".$requestData."%' ";
                $requete.=" OR carte_marchand.carte LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.rc_ninea LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.email LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.telmobile LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.adresse LIKE '%".$requestData."%' )";
            }
        }elseif(!is_null($requestData)){
            $requete.=" WHERE( marchand.nom_marchand LIKE '%".$requestData."%' ";
                $requete.=" OR carte_marchand.code_guichet LIKE '%".$requestData."%' ";
                $requete.=" OR carte_marchand.carte LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.rc_ninea LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.email LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.telmobile LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.adresse LIKE '%".$requestData."%' ";
                $requete.=" OR marchand.statut LIKE '%".$requestData."%' )";
        }
        if(!isset($data['idmarchand'])) {
            $tabCol = ['marchand.nom_marchand', 'carte_marchand.code_guichet', 'carte_marchand.carte', 'marchand.rc_ninea', 'marchand.email', 'marchand.telmobile', 'marchand.adresse', 'marchand.statut'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $requete.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $requete .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        }
        $bd = $this->getConnexion()->prepare($requete);
        if(count($data) > 0) $bd->execute($data);
        else $bd->execute();
        return $bd->fetchAll(PDO::FETCH_ASSOC);
    }




    /********Liste beneficiaires*********/
    public function getMarchandCount()
    {
        try {
            $sql = "SELECT COUNT(idmarchand) as total FROM marchand ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }





    public function verifierTelMarchand($tel)
    {
        try {
            $sql = "SELECT idmarchand FROM marchand WHERE telmobile = :tel";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'tel' => $tel
                )
            );
            $a = $user->fetchObject();
            if($a != null){
                return 1;
            }
            else{
                return 0;
            }
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function verifierEmailMarchand($tel)
    {
        try {
            $sql = "SELECT idmarchand FROM marchand WHERE email = :tel";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    'tel' => $tel
                )
            );
            $a = $user->fetchObject();
            if($a != null){
                return 1;
            }
            else{
                return 0;
            }
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function addMarchandModel($data = [])
    {
        $data['date_creation'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $champs = array_keys($data);
        $sql = "INSERT INTO marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";

        try{
            $bd = $this->getConnexion();
            $reult = $bd->prepare($sql);
            $reult->execute($data);
            return $bd->lastInsertId("idmarchand");
        }catch(PDOException $ex) {
            //echo $ex; die;
            return false;
        }
    }

    public function addUserMarchandModel($data = [])
    {
        reload:;
        $data['guichet'] = (new \app\core\Utils())->generation_code_validation(4);
        $champs = array_keys($data);
        $sql = "INSERT INTO user_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";

        try{
            $bd = $this->getConnexion();
            $reult = $bd->prepare($sql);
            $reult->execute($data);
            return $data['guichet'];
        }catch(PDOException $ex) {
            echo $ex;
            $test = "Duplicate entry '".$data['guichet']."' for key 'guichet'";
            if($ex->errorInfo[2] == $test) goto reload;
            else return false;
        }
    }

    public function addCarteMarchandModel($data = [])
    {
        $data['date_creation'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $champs = array_keys($data);
        $sql = "INSERT INTO carte_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";

        try{
            $reult = $this->getConnexion()->prepare($sql);
            return $reult->execute($data);
        }catch(PDOException $ex) {
            return false;
        }
    }

    public function updateMarchandModel($data = [])
    {
        $temp = $data;
        unset($temp['idmarchand']);
        $champs = array_keys($temp);
        $champs = array_map(function($one){return $one.'=:'.$one;},$champs);
        $sql = "UPDATE marchand SET ".implode(',',$champs)." WHERE idmarchand =:idmarchand";
        try{
            $bd = $this->getConnexion()->prepare($sql);
            return $bd->execute($data);
        }catch(PDOException $ex){
            return false;
        }
    }

    public function rollBackMarchand($table,$data = [])
    {
        $champ = array_keys($data)[0];
        $sql = "DELETE FROM ".$table." WHERE ".$champ."=:".$champ;
        try{
            $reult = $this->getConnexion()->prepare($sql);
            return $reult->execute($data);
        }catch(PDOException $ex) {
            return false;
        }
    }
}