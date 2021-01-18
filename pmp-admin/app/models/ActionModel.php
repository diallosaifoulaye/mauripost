<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */



class ActionModel extends \app\core\BaseModel
{
    /******************Insertion action**********************/
    public function insertAction($label, $module,$user_creation)
    {
        try
        {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO action(label, module, user_creation, date_creation) VALUES (:label, :module, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("label"=>$label, "module"=>$module, "user_creation"=>$user_creation, "date_creation"=>$date_creation));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }

    }

    /************Liste des actions*********/
    public function  allAction($requestData = null)
    {
        try{
            $sql = "Select a.rowid, a.label, m.nom_module from action as a JOIN module as m ON a.module=m.idmodule ";
            if(!is_null($requestData)){
                $sql.=" WHERE ( a.label LIKE '%".$requestData."%' ";
                $sql.=" OR m.nom_module LIKE  '%".$requestData."%' )";
            }
            $tabCol = ['a.label', 'm.nom_module'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return $Exception;
        }
    }

    /********Liste beneficiaires*********/
    public function allActionCount()
    {
        try {
            $sql = "SELECT COUNT(a.rowid) as total FROM action as a JOIN module as m ON a.module=m.idmodule ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }



    /************Detail action*********/
    public function getActionByIdString($id){
        try{
            $sql = "Select a.rowid, a.label, a.module,a.etat, m.nom_module, a.user_creation, a.date_creation, a.user_modification, a.date_modification
                from action as a
                LEFT OUTER JOIN module as m
                ON a.module = m.idmodule
                WHERE a.rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));
            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    /**********Update action********/
    public function updateAction($label, $module, $id, $user_modification){

        try
        {
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE action SET label = :libelle, module = :typeprofil, user_modification = :user_modification, date_modification = :date_modification WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "libelle" =>$label,
                "typeprofil" =>$module,
                "user_modification" =>$user_modification,
                "date_modification" =>$date_modification,
                "id" => $id
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    /***************Delete action*******************/
    public function desactiveAction($etat,$id, $user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE action SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" =>$etat,
                "user_modification" =>$user_modification,
                "date_modification" =>$date_modification,
                "id" =>$id
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    /***************Delete action*******************/
    public function deleteAction($id, $user_modification){

        try{
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE action SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" =>0,
                "user_modification" =>$user_modification,
                "date_modification" =>$date_modification,
                "id" =>$id
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

     /************AllModule************/
    public function  allmodule(){
        $sql = "SELECT module.idmodule, module.nom_module FROM module WHERE module.etat=:etat ORDER BY module.nom_module";
        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch(Exception $e)
        {
           return -2;
        }

    }



    /*public function verifyActionModule($lab, $mod){
        $sql = "Select label
                from action
                WHERE label = :id
                AND module = :mod";
        $user = $this->pdo->prepare($sql);
        $user->execute(
            array(
                "id" => strval($lab),
                "mod" => strval($mod),
            )
        );
        $a = $user->rowCount();
        return $a;

    }

    public function verifyActionModule2($lab, $mod, $id){
        $sql = "Select label
                from action
                WHERE rowid != :id
                AND module = :mod
                AND label = :lab";
        $user = $this->pdo->prepare($sql);
        $user->execute(
            array(
                "id" => intval($id),
                "mod" => strval($mod),
                "lab" => strval($lab),
            )
        );
        $a = $user->rowCount();
        return $a;

    }*/

    /************AllActionByModule************/
    public function allActionsByModule($idString){
        $sql = "Select a.rowid, a.label, a.module, a.user_creation, a.date_creation, a.user_modification, a.date_modification
                from action as a
                WHERE a.module = :id AND etat = 1";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => intval($idString),
            )
        );
        $a = $user->fetchAll();
        $this->closeConnexion();
        return $a;

    }

    /************ActionByProfil**************/
    public function allActionsAutoriseByProfil($idString){
        $mesactionsAutorises = array();
        $sql = "Select * from affectation_droit WHERE profil = :id";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "id" => intval($idString),
            )
        );
        $a = $user->fetchAll();
        $this->closeConnexion();
        foreach($a as $t){
            array_push($mesactionsAutorises, $t['action']);
        }
        return $mesactionsAutorises;
    }

    /***********SUPPRIMER ACTION AFFECTEE*****/
    public function deleteAutoriseAction($id){

        try{
            $sql = "DELETE FROM affectation_droit WHERE profil = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "id" => intval($id),
            ));
            $this->closeConnexion();
        }
        catch(PDOException $Exception ){
            $res = false;
        }
        return $res;

    }
    /*********************AFFECTATION DROIT PROFIL****/
    public function autoriseAction($action, $profil,$user_creation){

        try{
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO affectation_droit(action, profil, valide, user_creation, date_creation) VALUES (:label, :profil, :valide, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "label" => intval($action),
                "profil" => intval($profil),
                "valide" => intval(1),
                "user_creation" => $user_creation,
                "date_creation" => $date_creation,
            ));
            $this->closeConnexion();
            if($res==1){
                return 3;
            }
        }
        catch(PDOException $Exception ){
            return -3;
        }
    }

    /************Liste des actions*********/
    /************Liste des actions*********/
    public function  suiviAtion($tab=[],$requestData = null)
    {
        extract($tab);
        try
        {
            $sql = "SELECT f.date, f.action, f.action_object, f.commentaire, r.prenom, r.nom";
            $sql.= " FROM action_utilisateur f, user r";
            $sql.= " WHERE f.IDUSER = r.rowid";
            $sql.= " AND DATE(f.date) >= :date1";
            $sql.= " AND DATE(f.date) <= :date2";
            if(intval($tab['module']) > 0) {
                $sql .= " AND f.type = :module";
            }
            if($_REQUEST['search']['value']!=""){
                $sql.=" AND ( f.date LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.action LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.action_object LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.commentaire LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  r.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR r.nom LIKE  '%".$_REQUEST['search']['value']."%' )";
            }
            $tabCol = ['f.date', 'f.action', 'f.action_object', 'f.commentaire', 'r.prenom', 'r.nom'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);

            if(intval($tab['module']) > 0)
            {
                $user->execute(array("date1" => $tab['date1'], "date2" => $tab['date2'], "module" => $tab['module']));
            }
            else{
                $user->execute(array("date1" => $tab['date1'], "date2" => $tab['date2']));
            }

            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return -1;
        }
    }



    /********Liste beneficiaires*********/
    public function suiviAtionCount($tab=[])
    {
        try {
            if(intval($tab['module']) > 0)
            {
                $sql = "SELECT COUNT(idaction) as total FROM action_utilisateur f, user r
                        WHERE f.IDUSER = r.rowid AND DATE(f.date) >= '".$tab['date1']."' AND DATE(f.date) <= '".$tab['date2']."' AND f.type = ".$tab['module'];
            }
            else{
                $sql = "SELECT COUNT(idaction) as total FROM action_utilisateur f, user r
                        WHERE f.IDUSER = r.rowid AND DATE(f.date) >= '".$tab['date1']."' AND DATE(f.date) <= '".$tab['date2']."'";
            }
            if($_REQUEST['search']['value']!=""){
                $sql.=" AND ( f.date LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.action LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.action_object LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  f.commentaire LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR  r.prenom LIKE '%".$_REQUEST['search']['value']."%' ";
                $sql.=" OR r.nom LIKE  '%".$_REQUEST['search']['value']."%' )";
            }
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];

        } catch (PDOException $exception) {
            return -1;
        }
    }
}