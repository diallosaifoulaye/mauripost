<?php
/**
 * Created by PhpStorm.
 * User: Developpeur
 * Date: 01/06/2018
 * Time: 15:20
 */


class EquipementModel extends \app\core\BaseModelDao
{



    /**************Liste des équipement*********/
    public function  allEquipement()
    {

        $this->table = "t_equipement e";
        $this->champs =['e.rowid','e.reference','e.libelle as label','t.libelle','e.etat'];
        $this->jointure =["LEFT JOIN t_type_equipement t ON t.rowid = e.type"];
        return $this->__processing();
        //return $this->__select() ;
    }

    public function  allEquipementCount($requestData = null)
    {

        $this->table = "t_equipement";
        return count($this->__select());
    }

    /**************Liste des types d'equipement*********/

    public function getTypes(){
        $this->table = "t_type_equipement";
        $this->champs =['rowid','libelle','etat'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    public function getAllTypes(){
        $this->table = "t_type_equipement";
        $this->champs =['rowid','libelle','etat'];
        return $this->__select() ;
    }

    public function getAllTypesCount(){
        $this->table = "t_type_equipement";
        $this->champs =['rowid','libelle','etat'];
        return count($this->__select()) ;
    }

    public function getTypeById($id){
        $this->table = "t_type_equipement e";
        $this->champs =['rowid','libelle','uiid','etat'];
        $this->condition=["e.rowid ="=>$id];
        return $this->__select()[0] ;
    }

    public function updateCollecteur($id, $uiid){
        $this->table = "t_collecteur";
        $data['uuid'] = $uiid ;
        $this->champs = $data;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function updateType($id){

        $this->table = "t_type_equipement";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
       // echo 'UPDATE: '.$update; exit ;
        return $update ;
    }

    public function insertType($data){
        $this->table = "t_type_equipement";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /************** Avoir l'équipement en question *********/
    public function getEquipementById($id){
        $this->table = "t_equipement e";
        $this->champs =['e.rowid','e.reference','e.libelle','t.libelle as labelType','e.etat','e.uiid'];
        $this->jointure =["LEFT JOIN t_type_equipement t ON t.rowid = e.type"];
        $this->condition=["e.rowid ="=>$id];
        return $this->__select()[0] ;
    }


    /************** Avoir l'équipement en question *********/
    public function getEquipementByType($id){
        $this->table = "t_equipement e";
        $this->champs =['e.rowid','e.reference','e.libelle'];
      // $this->condition=["e.type ="=>$id,"e.etat ="=>1];
        $this->condition=["e.type = ? AND e.etat = ? AND e.rowid NOT IN(SELECT fk_materiel FROM t_affectation_materiel WHERE statut=1)"];
        $this->value=[$id,1];
        return $this->__select() ;
    }


    public function isTypeaffecte($type,$collecteur){
        $this->table = "t_affectation_materiel t";
        $this->champs =['t.type,t.fk_collecteur'];
        // $this->condition=["e.type ="=>$id,"e.etat ="=>1];
        $this->condition=["t.statut = ? AND t.type = ? AND t.fk_collecteur = ? "];
        $this->value=[1,$type,$collecteur];
        return $this->__select() ;
    }

    public function hasUiid($id){
        $this->table = "t_type_equipement e";
        $this->champs =['e.rowid, e.uiid'];
        $this->condition=["e.rowid ="=>$id];
        return $this->__select()[0]['uiid'] ;
    }

    /************** Avoir l'équipement en question *********/
    public function insert($data){
        $this->table = "t_equipement";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    public function disableEquipement($id){
        $this->table = "t_equipement";
        $_POST['etat'] = 0 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }
    public function activeEquipement($id){
        $this->table = "t_equipement";
        $_POST['etat'] = 1 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function updateEquipement($id){
        $this->table = "t_equipement";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    //Affectation en cours
    public function  affectationEncours()
    {

        $this->table = "t_affectation_materiel t";
        $this->champs =['t.rowid as rowid', 'm.libelle as materiel', 'y.libelle as type_materiel','CONCAT(c.prenom, " ",c.nom) as name','DATE(t.date_debut) as date_debut'];
        $this->jointure =["LEFT JOIN t_collecteur c ON c.rowid = t.fk_collecteur LEFT JOIN t_equipement m ON m.rowid = t.fk_materiel LEFT JOIN t_type_equipement y ON y.rowid = m.type"];
        $this->condition=["m.etat = ? AND t.statut = ? "];
        $this->value = [1,1] ;
       // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__processing();
    }

    public function getAffectationById($id){

        $this->table = "t_affectation_materiel t";
        $this->champs =['t.rowid as idaffectation','t.date_debut','t.date_fin','t.fk_collecteur','t.fk_materiel','CONCAT(c.prenom, " ",c.nom) as name', 'm.libelle as materiel',
            'y.libelle as type_materiel','DATE(t.date_debut) as date_debut, y.rowid as typeid, t.etat, t.statut', 'c.uuid'];
        $this->jointure =["LEFT JOIN t_collecteur c ON c.rowid = t.fk_collecteur LEFT JOIN t_equipement m ON m.rowid = t.fk_materiel LEFT JOIN t_type_equipement y ON y.rowid = m.type"];
        $this->condition=["t.rowid = " =>$id];
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__select()[0];

    }

    public function  affectationEncoursCount()
    {

        $this->table = "t_affectation_materiel t";
        //$this->champs =['t.rowid as idaffectation','t.date_debut','t.date_fin','CONCAT(c.prenom, " ",c.nom) as name', 'm.libelle as materiel', 'y.libelle as type_materiel'];
        $this->jointure =["LEFT JOIN t_collecteur c ON c.rowid = t.fk_collecteur LEFT JOIN t_equipement m ON m.rowid = t.fk_materiel LEFT JOIN t_type_equipement y ON y.rowid = m.type"];
        $this->condition=["m.etat = ? AND t.statut = ? "];
        $this->value = [1,1] ;
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return count($this->__select());
    }

//Historique des affectations
    public function  affectationHistorique()
    {

        $this->table = "t_affectation_materiel t";
        $this->champs =['t.rowid as rowid', 'm.libelle as materiel', 'y.libelle as type_materiel','CONCAT(c.prenom, " ",c.nom) as name','DATE(t.date_debut) as date_debut','DATE(t.date_fin) as date_fin'];
        $this->jointure =["LEFT JOIN t_collecteur c ON c.rowid = t.fk_collecteur LEFT JOIN t_equipement m ON m.rowid = t.fk_materiel LEFT JOIN t_type_equipement y ON y.rowid = m.type"];
        $this->condition=["m.etat = ? AND t.statut = ? "];
        $this->value = [1,0] ;
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__processing();
    }

    public function  affectationHistoriqueCount()
    {

        $this->table = "t_affectation_materiel t";
        //$this->champs =['t.rowid as idaffectation','t.date_debut','t.date_fin','CONCAT(c.prenom, " ",c.nom) as name', 'm.libelle as materiel', 'y.libelle as type_materiel'];
        $this->jointure =["LEFT JOIN t_collecteur c ON c.rowid = t.fk_collecteur LEFT JOIN t_equipement m ON m.rowid = t.fk_materiel LEFT JOIN t_type_equipement y ON y.rowid = m.type"];
        $this->condition=["m.etat = ? AND t.statut = ? "];
        $this->value = [1,0] ;
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return count($this->__select());
    }


    public function insertAffectation($data){
        $this->table = "t_affectation_materiel";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    public function updateAffectation($id){
        $this->table = "t_affectation_materiel";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function disaffecter($id){
       // var_dump($_POST); die();

        $idCollecteur = $_POST['fk_collecteur'] ;
        unset($_POST['fk_collecteur']);

        $this->table = "t_affectation_materiel";
        $_POST['statut'] = 0 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $rs1 = $update=  $this->__update();

        if ($rs1 > 0){
            $this->table = "t_collecteur";
            $data['uuid'] = null ;
            $this->champs = $data;
            $this->condition=["rowid ="=>intval($idCollecteur)];
            $rs2 = $update=  $this->__update();
        }
        if ($rs1 > 0 && $rs2 > 0){
            return $update ;
        }

    }

    /********** verifier UUID**********/
    public function verifUuid($uuid)
    {
        $this->table = "t_equipement";
        $this->champs = ["rowid"];
        $this->condition=["uiid ="=>strval($uuid)];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;

    }
    /********** verifier Reference **********/
        public function verifReference($reference)
        {
            $this->table = "t_equipement";
            $this->champs = ["rowid"];
            $this->condition=["reference ="=>strval($reference)];
            $count = count($this->__select());
            if($count > 0) return 1;
            else return -1;

        }


    /************** Compter les Equipements *********/
    public function  nbreEquipements()
    {
        $this->table = "t_equipement";
        $this->champs =['rowid'];
        $this->condition=["etat ="=>1];
        return count($this->__select());
    }




}