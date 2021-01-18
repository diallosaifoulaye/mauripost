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
        $requete = "SELECT marchand.idmarchand as rowid, marchand.nom_marchand, marchand.rc, marchand.ninea, marchand.email, marchand.telmobile, marchand.adresse, marchand.type as typemarchand, marchand.solde, marchand.statut";
        if(isset($data['champs'])) {
            $requete .= $data['champs'];
            unset($data['champs']);
        }
        $requete .= " FROM marchand";
        if(count($data) > 0){
            $champs = array_keys($data);
            $champs = array_map(function($one){return 'marchand.'.$one.'=:'.$one;},$champs);
            $requete.=" WHERE ".implode(' AND ',$champs);
            if($_REQUEST['search']['value']!=""){
                $requete.=" AND ( marchand.nom_marchand LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.rc LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.ninea LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.telmobile LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.solde LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.adresse LIKE '%".$_REQUEST['search']['value']."%' )";
            }
        }elseif($_REQUEST['search']['value']!=""){
            $requete.=" WHERE( marchand.nom_marchand LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.rc LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.ninea LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.telmobile LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.adresse LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.solde LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.statut LIKE '%".$_REQUEST['search']['value']."%' )";
        }
        if(!isset($data['idmarchand'])) {
            $tabCol = ['marchand.nom_marchand', 'marchand.rc', 'marchand.ninea', 'marchand.email', 'marchand.telmobile', 'marchand.adresse', 'marchand.type as typemarchand', 'marchand.solde', 'marchand.statut'];
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
    public function getMarchandCount($data = [])
    {
        try {
            //$sql = "SELECT COUNT(idmarchand) as total FROM marchand ";

            $requete = "SELECT marchand.idmarchand as rowid, marchand.nom_marchand, marchand.rc, marchand.ninea, marchand.email, marchand.telmobile, marchand.adresse, marchand.type as typemarchand, marchand.statut";
            if(isset($data['champs'])) {
                $requete .= $data['champs'];
                unset($data['champs']);
            }
            $requete .= " FROM marchand";
            if(count($data) > 0){
                $champs = array_keys($data);
                $champs = array_map(function($one){return 'marchand.'.$one.'=:'.$one;},$champs);
                $requete.=" WHERE ".implode(' AND ',$champs);
                if($_REQUEST['search']['value']!=""){
                    $requete.=" AND ( marchand.nom_marchand LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR marchand.rc LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR marchand.ninea LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR marchand.telmobile LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR marchand.adresse LIKE '%".$_REQUEST['search']['value']."%' )";
                }
            }elseif($_REQUEST['search']['value']!=""){
                $requete.=" WHERE( marchand.nom_marchand LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.rc LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.ninea LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.telmobile LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.adresse LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR marchand.statut LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($requete);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getDetailsMarchand($idmarchand)
    {
        $requete = "SELECT marchand.idmarchand as rowid, marchand.nom_marchand, marchand.rc, marchand.ninea, marchand.email, marchand.telmobile, marchand.adresse, marchand.statut, marchand.solde, marchand.user_creation, marchand.date_creation, marchand.type";
        $requete .= " FROM marchand  WHERE marchand.idmarchand = ".$idmarchand;

        $bd = $this->getConnexion()->prepare($requete);
        $bd->execute();
        return $bd->fetch(PDO::FETCH_ASSOC);
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
    public function addProfilMarchandModel($data = [])
    {
        $data['date_creation'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $champs = array_keys($data);
        $sql = "INSERT INTO profil_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";

        try{
            $bd = $this->getConnexion();
            $reult = $bd->prepare($sql);
            $reult->execute($data);
            return $bd->lastInsertId("rowid");
        }catch(PDOException $ex) {
            //echo $ex; die;
            return false;
        }
    }

    function Generer_codeMarchand()
    {
        $found=0;
        while ($found==0)
        {
            $code_carte=rand(0,9).rand(0,9).rand(0,9).rand(0,9);
            $colname_rq_code_existe =$code_carte;
            $query_rq_code_existe = "SELECT rowid FROM caisse_marchand WHERE codemarchand ='".$colname_rq_code_existe."'";
            $numero = $this->getConnexion()->prepare($query_rq_code_existe);
            $numero->execute();
            $num_transac=$numero->fetchObject();
            $totalRows_rq_code_existe =$numero->rowCount();
            if($totalRows_rq_code_existe==0)
            {
                //CODE GENERER
                $code_generer=$code_carte;
               // var_dump($code_generer);exit;
                $found=1;
                break;
            }

        }
        return $code_generer;
    }
    public function getMaxNumCaisse($fk_marchand)
     {
         try
         {
             $sql = "Select numcaisse from caisse_marchand WHERE fk_marchand = :fk_marchand ORDER BY rowid DESC";
             $user = $this->getConnexion()->prepare($sql);
             $user->execute(array("fk_marchand" => $fk_marchand,));
             $a = $user->fetchAll();
             $this->closeConnexion();
             return $a;
         }
         catch(PDOException $e){
             return -1;
         }
     }



    public function addUserMarchandModel($data = [])
    {
        $champs = array_keys($data);
        $sql = "INSERT INTO user_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";
       // var_dump($sql);exit;

        try{
            $reult = $this->getConnexion()->prepare($sql);
            return $reult->execute($data);
        }catch(PDOException $ex) {
            return false;
        }
    }

    public function addCaisseMarchandModel($data = [])
    {
        $data['date_creation'] = \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T");
        $champs = array_keys($data);
        $sql = "INSERT INTO caisse_marchand(".implode(',',$champs).") ";
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

    public function particulierMarchandModel($tel){
        if($tel[0] != '0'){
            $tel = '00'.trim($tel);
        }
        try {
            $sql = "SELECT carte.rowid, b.prenom, b.nom FROM carte LEFT JOIN beneficiaire b ON carte.beneficiaire_rowid = b.rowid WHERE carte.telephone = '".$tel."' AND carte.statut = 1 AND b.statut = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            if($a != null){
                return $a->rowid.'-'.$a->prenom.' '.$a->nom;
            }
            else{
                return -1;
            }
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function associerMarchandModel($marchand, $carte){
        try {
            $sql = "UPDATE carte_marchand SET carte = ".$carte." WHERE idmarchand = ".$marchand;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->rowCount();
            if($a == 1){
                return 1;
            }
            else{
                return -1;
            }
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /*********** Update cle secrete marchand***************/
    public function updateCleMarchand($id,$cle){
        try{
            $sql = "UPDATE marchand SET cle = ".$cle." WHERE idmarchand = '".$id."' ";

            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }
 /*********** Update mot de passe user marchand***************/
    public function updatePwdMarchand($id,$password){
        try{
            $sql = "UPDATE user_marchand SET password = '".$password."', is_already_connect = 0 WHERE iduser = '".$id."' ";
//var_dump($sql);exit;
            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }
/*********** Update code marchand***************/
    public function updateCodeMarchand($id,$codemarchand){
        try{
            $sql = "UPDATE caisse_marchand SET codemarchand = ".$codemarchand."  WHERE rowid = '".$id."' ";
//var_dump($sql);exit;
            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }


    //*********** Marchand *****************//

    public function  getMarchandInfo($id)
    {
        try
        {
            $sql = "SELECT solde,nom_marchand,email,telmobile FROM marchand where idmarchand=".$id;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
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


//*********** Appel de Fond *****************//

    public function AppelFond($param1,$param2,$param3,$param4)
    {

        if($param1>0){
            $where=" where a.fk_marchand=".$param1." AND Date(a.date)>='".$param2."' AND Date(a.date)<='".$param3."' AND a.etat=".$param4."";

        }else{
            $where=" where Date(a.date)>='".$param2."' AND Date(a.date)<='".$param3."' AND a.etat=".$param4."";
        }

        try
        {
            $sql = "SELECT a.rowid, a.montant, a.etat, a.date, m.nom_marchand as nom, a.fk_marchand, u.iduser FROM appels_fond_marchand as a INNER JOIN marchand m ON m.idmarchand = a.fk_marchand INNER JOIN user_marchand u ON u.iduser = a.fk_user_marchand".$where." ORDER BY a.rowid DESC";
         //var_dump($sql);exit;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
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

    /***********Consulter solde marchand***************/
    public function consulterSoldeMarchand($idmarchand)
    {
        try
        {
            $sql = "SELECT solde from marchand  WHERE statut=:statut AND idmarchand=:idmarchand";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("statut"=>1, "idmarchand"=>$idmarchand));
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

    /*********** Update solde marchand***************/
    public function updateSoldeMarchand($id,$montant){
        try{
            $sql = "UPDATE marchand SET solde = solde - ".$montant." WHERE idmarchand = '".$id."' ";

            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }


    public function insertReleve($data = [])
    {
        $champs = array_keys($data);
        $sql = "INSERT INTO releve_compte_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";
        // var_dump($sql);exit;

        try{
            $reult = $this->getConnexion()->prepare($sql);
            return $reult->execute($data);
        }catch(PDOException $ex) {
            return false;
        }
    }

     public function insertTransactionCompte($data = [])
    {
        $champs = array_keys($data);
        $sql = "INSERT INTO transaction_compte_marchand(".implode(',',$champs).") ";
        $champs = array_map(function($one){return ':'.$one;},$champs);
        $sql .= "VALUE (".implode(',',$champs).")";
        // var_dump($sql);exit;

        try{
            $reult = $this->getConnexion()->prepare($sql);
            return $reult->execute($data);
        }catch(PDOException $ex) {
            return false;
        }
    }






    public function validateAppel($etat, $fk_date_validation, $rowid,$fk_user_validation){

        try{
           // $fk_date_validation = date('Y-m-d H:i:s');
            $sql = "UPDATE appels_fond_marchand SET etat = :etat, fk_date_validation = :fk_date_validation, fk_user_validation = :fk_user_validation
                WHERE rowid = :rowid";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => $etat,
                "fk_date_validation" => $fk_date_validation,
                "fk_user_validation" => $fk_user_validation,
                "rowid" => $rowid,
            ));
            $this->closeConnexion();
            if($res==1){
                return 1;
            }
        }
        catch(PDOException $Exception ){
            return -1;
        }


    }
    public function autoriseAppel($etat, $fk_date_autorisation, $rowid,$fk_user_autorisation){

        try{
           // $fk_date_autorisation = date('Y-m-d H:i:s');
            $sql = "UPDATE appels_fond_marchand SET etat = :etat, fk_date_autorisation = :fk_date_autorisation, fk_user_autorisation = :fk_user_autorisation
                WHERE rowid = :rowid";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => $etat,
                "date_autorisation" => $fk_date_autorisation,
                "fk_user_autorisation" => $fk_user_autorisation,
                "rowid" => $rowid,
            ));
            $this->closeConnexion();
            if($res==1){
                return 1;
            }
        }
        catch(PDOException $Exception ){
            return -1;
        }


    }


    public function getUserMarchand($data = [],$requestData = null)
    {
        //var_dump($data);exit;
        $requete = "SELECT user_marchand.iduser as rowid, user_marchand.prenom, user_marchand.nom, user_marchand.email, user_marchand.telephone, user_marchand.statut";
        if(isset($data['champs'])) {
            $requete .= $data['champs'];
            unset($data['champs']);
        }
        $requete .= " FROM user_marchand";
        if(count($data) > 0){
           // $champs = array_keys($data);
          // var_dump($champs);exit;
            //$champs = array_map(function($one){return 'user_marchand.'.$one.'=:'.$one;},$champs);
            //var_dump($champs);exit;
            $requete.=" WHERE user_marchand.fk_marchand =".$data[0];
            if($_REQUEST['search']['value']!=""){
                $requete.=" AND ( user_marchand.nom LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.telephone LIKE '%".$_REQUEST['search']['value']."%' ";

            }
        }elseif($_REQUEST['search']['value']!=""){
            $requete.=" WHERE( user_marchand.nom LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR user_marchand.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR user_marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR user_marchand.telephone LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR user_marchand.statut LIKE '%".$_REQUEST['search']['value']."%' )";
        }
        if(!isset($data['iduser'])) {
            $tabCol = ['user_marchand.nom', 'user_marchand.prenom', 'user_marchand.email', 'user_marchand.telephone', 'user_marchand.statut'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $requete.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $requete .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        }
        //var_dump($requete);exit;
        $bd = $this->getConnexion()->prepare($requete);
        if(count($data) > 0) $bd->execute($data);
        else $bd->execute();
        return $bd->fetchAll(PDO::FETCH_ASSOC);
    }




    /********Liste tot user marchand *********/
    public function getUserMarchandCount($data = [])
    {
        try {

            $requete = "SELECT user_marchand.iduser as rowid, user_marchand.prenom, user_marchand.nom, user_marchand.email, user_marchand.telephone, user_marchand.statut";
            if(isset($data['champs'])) {
                $requete .= $data['champs'];
                unset($data['champs']);
            }
            $requete .= " FROM user_marchand";
            if(count($data) > 0){
                //$champs = array_keys($data);
                //$champs = array_map(function($one){return 'user_marchand.'.$one.'=:'.$one;},$champs);
                $requete.=" WHERE user_marchand.fk_marchand =".$data[0];
                if($_REQUEST['search']['value']!=""){
                    $requete.=" AND ( user_marchand.nom LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR user_marchand.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR user_marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR user_marchand.telephone LIKE '%".$_REQUEST['search']['value']."%' ";

                }
            }elseif($_REQUEST['search']['value']!=""){
                $requete.=" WHERE( user_marchand.nom LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.telephone LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR user_marchand.statut LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($requete);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }

 public function getCaisseMarchand($data = [],$requestData = null)
    {
        //var_dump($data);exit;
        $requete = "SELECT caisse_marchand.rowid as rowid, caisse_marchand.codemarchand, caisse_marchand.numcaisse, caisse_marchand.etat";
        if(isset($data['champs'])) {
            $requete .= $data['champs'];
            unset($data['champs']);
        }
        $requete .= " FROM caisse_marchand";
        if(count($data) > 0){
           // $champs = array_keys($data);
          // var_dump($champs);exit;
            //$champs = array_map(function($one){return 'caisse_marchand.'.$one.'=:'.$one;},$champs);
            //var_dump($champs);exit;
            $requete.=" WHERE caisse_marchand.fk_marchand =".$data[0];
            if($_REQUEST['search']['value']!=""){
                $requete.=" AND ( caisse_marchand.codemarchand LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR caisse_marchand.numcaisse LIKE '%".$_REQUEST['search']['value']."%' ";


            }
        }elseif($_REQUEST['search']['value']!=""){
            $requete.=" WHERE( caisse_marchand.codemarchand LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR caisse_marchand.numcaisse LIKE '%".$_REQUEST['search']['value']."%' ";
            $requete.=" OR caisse_marchand.etat LIKE '%".$_REQUEST['search']['value']."%' )";
        }
        if(!isset($data['rowid'])) {
            $tabCol = ['caisse_marchand.codemarchand', 'caisse_marchand.numcaisse', 'caisse_marchand.etat'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $requete.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $requete .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        }
        //var_dump($requete);exit;
        $bd = $this->getConnexion()->prepare($requete);
        if(count($data) > 0) $bd->execute($data);
        else $bd->execute();
        return $bd->fetchAll(PDO::FETCH_ASSOC);
    }




    /********Liste Caisse marchand*********/
    public function getCaisseMarchandCount($data = [])
    {
        try {

            $requete = "SELECT caisse_marchand.rowid as rowid, caisse_marchand.codemarchand, caisse_marchand.numcaisse, caisse_marchand.etat";
            if(isset($data['champs'])) {
                $requete .= $data['champs'];
                unset($data['champs']);
            }
            $requete .= " FROM caisse_marchand";
            if(count($data) > 0){
                //$champs = array_keys($data);
                //$champs = array_map(function($one){return 'caisse_marchand.'.$one.'=:'.$one;},$champs);
                $requete.=" WHERE caisse_marchand.fk_marchand =".$data[0];
                if($_REQUEST['search']['value']!=""){
                    $requete.=" AND ( caisse_marchand.codemarchand LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR caisse_marchand.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR caisse_marchand.email LIKE '%".$_REQUEST['search']['value']."%' ";
                    $requete.=" OR caisse_marchand.telephone LIKE '%".$_REQUEST['search']['value']."%' ";

                }
            }elseif($_REQUEST['search']['value']!=""){
                $requete.=" WHERE( caisse_marchand.codemarchand LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR caisse_marchand.numcaisse LIKE '%".$_REQUEST['search']['value']."%' ";
                $requete.=" OR caisse_marchand.etat LIKE '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($requete);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $user->rowCount();
        } catch (PDOException $exception) {
            return -1;
        }
    }
    //*********** Detail User Marchand *****************//

    public function  getUserMarchandId($id)
    {

            $sql = "SELECT u.iduser as rowid, u.prenom, u.nom, u.email, u.telephone, u.statut, p.libelle as profil, u.login, u.fk_profil, m.solde from user_marchand u INNER JOIN profil_marchand p ON u.fk_profil = p.rowid INNER JOIN marchand m ON u.fk_marchand = m.idmarchand where u.iduser=".$id;
            $bd = $this->getConnexion()->prepare($sql);
            $bd->execute();
            return $bd->fetch(PDO::FETCH_ASSOC);
    }

 //*********** Detail Caisse Marchand *****************//

    public function  getCaisseMarchandId($id)
    {

            $sql = "SELECT caisse_marchand.rowid as rowid, caisse_marchand.codemarchand, caisse_marchand.numcaisse, caisse_marchand.etat, caisse_marchand.date_creation, caisse_marchand.user_creation, m.solde from caisse_marchand INNER JOIN marchand m ON caisse_marchand.fk_marchand = m.idmarchand where rowid=".$id;
            $bd = $this->getConnexion()->prepare($sql);
            $bd->execute();
            return $bd->fetch(PDO::FETCH_ASSOC);
    }
//*********** Profil du Marchand *****************//

    public function  getProfil()
    {

        try
        {

        $sql = "SELECT profil_marchand.rowid as rowid, profil_marchand.libelle from profil_marchand where etat=1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
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
    /***************Historique des appels de fond***************/
    public function getHistoAppelFond($param1,$param2,$param3)
    {
        //var_dump($param1,$param2,$param3);die();

        if($param1 > 0){
            $where=" where t.fk_marchand=".$param1." AND Date(t.date_transaction)>='".$param2."' AND Date(t.date_transaction)<='".$param3."'";

        }else{
            $where="";
        }
        try
        {
            $sql = "SELECT t.date_transaction, t.num_transaction, t.montant, t.statut ,m.`nom_marchand`  FROM `marchand` as m INNER JOIN `transaction_compte_marchand` as t ON m.`idmarchand`= t.fk_marchand ".$where." ORDER BY t.date_transaction DESC";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
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


    //*********** Liste des Marchands appels de fond *****************//

    public function  getMarchands()
    {

        try
        {

            $sql = "SELECT marchand.idmarchand, marchand.nom_marchand from marchand where statut=1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
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

      