<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


//require_once __DIR__.'/Utilisateur.class.php';

class CollecteurModel extends \app\core\BaseModelDao
{


    /**************Liste des collectes en cours*********/
    public function  getCollectEncours()
    {
        $this->table = "t_transaction t";
        $this->champs =['t.fk_collecteur as rowid, CONCAT(c.prenom, " ",c.nom)','COUNT(DISTINCT DATE(t.date_transaction))','SUM(t.montant)'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0];
        $this->group=['t.fk_collecteur'];
        return $this->__processing();
       //return $this->__select() ;
    }

    public function  getCollectEncours1()
    {
        $this->table = "t_transaction t";
        $this->champs =['t.fk_collecteur as rowid, CONCAT(c.prenom, " ",c.nom)','COUNT(DISTINCT DATE(t.date_transaction))','SUM(t.montant)'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0];
        $this->group=['t.fk_collecteur'];
        //return $this->__processing();
        return $this->__select() ;
    }

    /**************Liste des collectes en cours*********/
    public function  getCollectEncoursCount()
    {

        $this->table = "t_transaction t";
        $this->champs =['t.fk_collecteur as rowid, CONCAT(c.prenom, " ",c.nom)','COUNT(DISTINCT DATE(t.date_transaction))','SUM(t.montant)'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0];
        $this->group=['t.fk_collecteur'];
        return count($this->__select());
    }

    public function insertVersement($data){
        $this->table = "t_versement";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    public function updateTransaction($id, $date){
        $data['verse'] = 1 ;
        $this->table = "t_transaction";
        $this->champs = $data;
        $this->condition=["fk_collecteur ="=>intval($id),"DATE(date_transaction) ="=>$date];
        $update=  $this->__update();
        return $update ;
    }


    public function getCollectById($id){

        $this->table = "t_transaction t";
        $this->champs =['t.fk_collecteur as rowid, CONCAT(c.prenom, " ",c.nom) as name','DATE(t.date_transaction) date_transaction','SUM(t.montant) as montant'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0,"c.rowid = " =>$id];
        $this->group=['DATE(t.date_transaction)'];
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__select();

    }


    public function getCollectByIdByTontine($id,$date){

        $this->table = "t_transaction t";
        $this->champs =['o.rowid as rowid, t.date_transaction, o.libelle as libelle', 'of.libelle as offre', 'CONCAT(cli.prenom, " ",cli.nom) as client','SUM(t.montant) as montant'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_client cli on t.fk_client = cli.rowid
            INNER JOIN t_tontine o on t.fk_tontine = o.rowid
            INNER JOIN t_offres of on o.fk_offre = of.rowid    
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0,"c.rowid = " =>$id,'DATE(t.date_transaction)=' =>$date];
        $this->group=['rowid'];
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__select();

    }

    public function getCollectToDay(){

        $this->table = "t_transaction t";
        $this->champs =['SUM(t.montant) as montant'];
        $this->condition=["t.statut ="=>1];
        //$this->group=['DATE(t.date_transaction)'];
        $this->condition=["DATE(t.date_transaction) ="=>date('Y-m-d')];
        return $this->__select()[0];

    }


    public function getVersementToDay(){

        $this->table = "t_versement t";
        $this->champs =['SUM(t.montant_verse) as montant'];
        $this->condition=["DATE(t.date_creation) ="=>date('Y-m-d')];
        return $this->__select()[0];

    }


    public function getDateCollectByIdByTontine($id){

        $this->table = "t_transaction t";
        $this->champs =['DATE(t.date_transaction) as date'];
        $this->jointure =["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_tontine o on t.fk_tontine = o.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.verse ="=>0,"c.rowid = " =>$id];
        $this->group=['DATE(t.date_transaction)'];
        // $this->condition=["u.code_entite ="=>$entite[0]];
        return $this->__select();

    }





    /**************Liste des collecteurs*********/
    public function  collecteur()
    {
        $this->table = "t_collecteur c";
        $this->champs =['c.rowid','c.nom','c.prenom','c.email','c.telephone','c.montant_collection','a.label as agence','c.etat'];
        $this->jointure =["
            INNER JOIN agence a on c.fk_agence = a.rowid
        "];
        //$this->condition=["c.etat ="=>1];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les collecteurs *********/
    public function  collecteurCount()
    {
        $this->table = "t_collecteur c";
        $this->jointure =["
            INNER JOIN agence a on c.fk_agence = a.rowid
        "];
        //$this->condition=["c.etat ="=>1];
        return count($this->__select());
    }

    /************** Insertion collecteur *********/
    public function insert($data){
        $this->table = "t_collecteur";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /**************Liste de tous les collecteurs*********/
    public function getCollecteurs()
    {
        $this->champs =['rowid','nom','prenom','email','telephone','etat'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    /**************Liste de tous les collecteurs*********/
    public function allCollecteurs()
    {
        $this->table = "t_collecteur";
        $this->champs =['rowid','CONCAT(prenom, " ",nom) as collecteur'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    /**************Liste de toutes les agences*********/
    public function allAgence()
    {
        $this->table ='agence';
        $this->champs =['rowid','label as agence'];
        return $this->__select() ;

    }

    /**********verifier Email**********/
    public function verifEmail($email)
    {
        $this->table = "t_collecteur";
        $this->champs = ["rowid"];
        $this->condition=["email ="=>$email];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;

    }

    /**********verifier Identifiant**********/
    public function verifIdentifiant($login)
    {
        $this->table = "t_collecteur";
        $this->champs = ["rowid"];
        $this->condition=["login ="=>$login];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;

    }

    /**********verifier Telephone**********/
    public function verifTelephone($tel)
    {
        $this->table = "t_collecteur";
        $this->champs = ["rowid"];
        $this->condition=["telephone ="=>$tel];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;

    }

    /************** Avoir l'élément en question *********/
    public function getCollecteurById($id){
        $this->table = "t_collecteur c";
        $this->champs =['c.*', 'a.label as agence'];
        $this->jointure =["
            INNER JOIN agence a on c.fk_agence = a.rowid
        "];
        $this->condition=["c.rowid ="=>$id];
        return $this->__select()[0] ;
    }

    /**************Liste des collecteurs*********/
    public function  transactionsDuCollecteur($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure', 'o.libelle as offre','CONCAT(c.prenom, " ", c.nom) as client','t.montant', 't.statut'];
        $this->jointure =
            ["
            INNER JOIN t_client c on t.fk_client = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["t.statut ="=>1, "t.fk_collecteur=" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les transactions *********/
    public function  transactionsDuCollecteurCount($id)
    {
        $this->table = "t_transaction t";
        $this->jointure =
            ["
            INNER JOIN t_client c on t.fk_client = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["t.statut ="=>1, "t.fk_collecteur=" =>$id[0]];
        return count($this->__select());
    }

    /**************Liste des clients enrollés *********/
    public function  enrollementsDuCollecteur($id)
    {
        $this->table = "t_client c";
        $this->champs =['c.rowid','c.date_creation as date_avec_heure', 'CONCAT(c.prenom," ", c.nom) as client','c.telephone'];
        $this->condition=["c.fk_collecteur ="=>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les clients enrollés *********/
    public function  enrollementsDuCollecteurCount($id)
    {
        $this->table = "t_client c";
        $this->condition=["c.fk_collecteur ="=>$id[0]];
        return count($this->__select());
    }

    public function disableCollecteur($id){
        $_POST['etat'] = 0 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function activeCollecteur($id){
        $_POST['etat'] = 1 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function updateCollecteur($id){
        //var_dump($_POST);exit;
        $this->table = "t_collecteur";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }


    public function resetCaisseCollecteur($id){
        //var_dump($_POST);exit;
        $data['montant_collection'] = 0 ;
        $this->table = "t_collecteur";
        $this->champs = $data;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************reset Password ad_utilisateurs************/
    public function resetPasswordCollecteur(){
        $this->table = "t_collecteur";
        $id = $_POST['rowid'];
        unset($_POST['rowid']);
        $this->champs = $_POST;
        $this->condition=["rowid ="=>$id];
        return $this->__update();
        //var_dump($this->__update());
    }

    /************** Compter les Collecteurs *********/
    public function  nbreCollecteursEnExercice()
    {
        $this->table = "t_collecteur";
        $this->champs =['rowid'];
        $this->condition=["etat ="=>1];
        return count($this->__select());
    }

    /**************Liste des équipements du collecteur*********/
    public function  equipementsDuCollecteur($id)
    {
        $this->table = "t_affectation_materiel a";
        $this->champs =['a.rowid','a.date_creation as date_avec_heure',
            'CONCAT(e.libelle, "-", e.reference) as equipement',
            't.libelle as type_equipement',
            'a.statut'];
        $this->jointure =
            ["
            INNER JOIN t_collecteur c on a.fk_collecteur = c.rowid
            INNER JOIN t_equipement e on a.fk_materiel = e.rowid
            INNER JOIN t_type_equipement t on e.type = t.rowid
            "];
        $this->condition=["a.fk_collecteur=" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les équipements *********/
    public function  equipementsDuCollecteurCount($id)
    {
        $this->table = "t_affectation_materiel a";
        $this->jointure =
            ["
            INNER JOIN t_collecteur c on a.fk_collecteur = c.rowid
            INNER JOIN t_equipement e on a.fk_materiel = e.rowid
            INNER JOIN t_type_equipement t on e.type = t.rowid
            "];
        $this->condition=["a.fk_collecteur=" =>$id[0]];
        return count($this->__select());
    }

    /************** reporting *********/
    public function reporting( $date_debut='', $date_fin='', $client='', $collecteur='')
    {
        $statut = 1;
        $this->table = "t_transaction t";
        $this->champs =['t.*', 'cli.*', 'col.*'];
        $this->jointure =
            ["
            INNER JOIN t_collecteur col on t.fk_collecteur = col.rowid
            INNER JOIN t_client cli on t.fk_client = cli.rowid
            "];

        /**Si date début et date fin non null et client et collecteur null****/
        if($date_debut != '' && $date_fin != '' && $client == '' && $collecteur == '')
        {
            $this->condition=["t.statut ="=>1, "DATE(t.date_transaction) >="=>$date_debut, "DATE(t.date_transaction) <="=>$date_fin];
            // var_dump($this->condition);die;
        }
        /**Si date début, date fin et client non null et collecteur null****/
        elseif ($date_debut != '' && $date_fin != '' && $client != '' && $collecteur == '')
        {
            $this->condition=["t.statut = ? AND DATE(t.date_transaction)  >= ? AND DATE(t.date_transaction) <= ? AND t.fk_client = ? AND t.fk_collecteur = ?"];
            $this->value=[1, $date_debut, $date_fin, $client, $collecteur];

        }
        /**Si date début, date fin et collecteur non null et client null****/
        elseif ($date_debut != '' && $date_fin != '' && $client == '' && $collecteur != '')
        {
            $this->condition=["t.statut = ? AND DATE(t.date_transaction)  >= ? AND DATE(t.date_transaction) <= ? AND t.fk_client = ? AND t.fk_collecteur = ?"];
            $this->value=[1, $date_debut, $date_fin, $client, $collecteur];

        }
        /**Si date début, date fin, client et collecteur non null ****/
        elseif ($date_debut != '' && $date_fin != '' && $client != '' && $collecteur != '')
        {
            $this->condition=["t.statut = ? AND DATE(t.date_transaction)  >= ? AND DATE(t.date_transaction) <= ? AND t.fk_client = ? AND t.fk_collecteur = ?"];
            $this->value=[1, $date_debut, $date_fin, $client, $collecteur];
        }
        else {
            $this->condition = ["t.statut ="=>$statut];
        }
        return $this->__select() ;
    }



}