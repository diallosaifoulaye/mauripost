<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


//require_once __DIR__.'/Utilisateur.class.php';

class TontineModel extends \app\core\BaseModelDao
{

    /******************************************************************************** /
    /****************************** GESTION TONTINE *******************************/
    /****************************************************************************** /

    /**************Liste des Tontines *********/
    public function  tontine($id)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','o.libelle as offre','CONCAT(c.prenom, " ", c.nom) as client','t.mise*t.mois_c','t.mise*t.periode','t.statut'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $condition = [];

        $this->condition=["t.type ="=>$id[0]];

       return $this->__processing();
        //return $this->__select() ;
    }

    public function updateTontineC($montant,$mc, $row_tontine,$mois_r,$statut,$penalite)
    {
//echo 'M='.$mois_r.' '.$statut ; exit;
        $return=0;
        try
        {
            $req =  $this->getConnexion()->prepare("UPDATE t_tontine SET montant_encours=montant_encours+:mtc, mois_c=mois_c+:mc, mois_r=:mois_r, statut=:statut, penalite=:penalite WHERE rowid=:tontine");
            $Result1=$req->execute(array("mtc" => $montant,"mc" => $mc, "tontine" => $row_tontine,"mois_r" => $mois_r,"statut" => $statut,"penalite" => $penalite));
            if($Result1 > 0)
            {
                $return = 1;
            }
            else $return = 0;
        }
        catch(\PDOException $e)
        {
            echo -1;
        }

        return $return;
    }

    /************** Avoir l'élément en question *********/
    public function getClientByPhone($telephone){
        $this->table = "t_client c";
        $this->champs =['c.*', 't.fk_client', 't.fk_offre','t.rowid as id_ton', 't.libelle', 't.montant_encours', 't.statut',
            'o.libelle as libelle_offre','o.cagnotte', 'o.frais'];
        $this->jointure =["
            LEFT JOIN t_tontine t on t.fk_client = c.rowid
            LEFT JOIN t_offres o on t.fk_offre = o.rowid
        "];
        $this->condition=["c.telephone ="=>$telephone,"t.retrait !="=>1];
        //var_dump($this->__select()[0] );die();
        return $this->__select();
    }

    /************** Compter les Tontines *********/
    public function  tontineCount($id)
    {
        $this->table = "t_tontine t";
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.type ="=>$id[0]];
        //$this->condition=["t.etat ="=>1];
        return count($this->__select());
    }

    /************** Insertion Tontine *********/
    public function insert($data){
        $this->table = "t_tontine";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    public function insertTrasanction($data){
        $this->table = "t_transaction";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /************** Liste de toutes les Tontines *********/
    public function getTontines()
    {
        $this->champs =['rowid','libelle'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    public function getAgent($id)
    {
        $this->table ='user';
        $this->champs =['CONCAT(prenom, " ", nom) as agent'];
        $this->condition=["rowid ="=>$id];
       // var_dump($this->__select()[0]['agent']);
        return $this->__select()[0]['agent'] ;
    }

    /************** Liste de toutes les offres *********/
    public function allOffres()
    {
        $this->table ='t_offres';
        $this->champs =['rowid','libelle as offres'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    /************** Liste de tous les clients *********/
    public function allClients()
    {
        $this->table ='t_client';
        $this->champs =['rowid','CONCAT(prenom, " ", nom) as clients'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;

    }

    /************** Avoir l'élément en question *********/
    public function getTontineByIdBis($id){

        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','CONCAT(c.prenom, " ", c.nom) as client','t.montant_encours as montant_verse', 't.montant_retrait','o.cagnotte', 'o.frais', 'c.telephone',
            'c.cin','t.retrait','o.libelle as loffre' ,'t.mise','t.penalite','t.periode','t.duree','t.mois_c','t.montant_bv','t.com_bv','t.statut', 't.date_retrait'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        
        "];
        $this->condition = ["t.rowid =" =>$id];


        /*$this->table = "t_tontine t";
        $this->champs =['t.*','o.libelle as offre','o.cagnotte','CONCAT(c.nom, " ", c.prenom) as client'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.rowid ="=>$id];*/
        return $this->__select()[0] ;
    }

    /************** Avoir l'élément en question *********/
    public function getTontineById($id){
        $this->table = "t_tontine t";
        $this->champs =['t.*','o.libelle as offre','o.cagnotte','CONCAT(c.nom, " ", c.prenom) as client'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.rowid ="=>$id];
        return $this->__select()[0] ;
    }

    /************** Abandonner Tontine *********/
    public function abortTontine($id){
        $this->table = "t_tontine";
        $_POST['statut'] = 3 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************** Cloturer Tontine *********/
    public function closeTontine($id){
        $this->table = "t_tontine";
        $_POST['statut'] = 2 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************** Modifier Tontine *********/
    public function updateTontine($id){
        //var_dump($_POST);exit;
        $this->table = "t_tontine";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /**************Liste des Tontines *********/
    public function  cotisationsTontine($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','t.montant', 't.statut'];
        $this->condition=["t.statut ="=>1, "t.fk_tontine=" =>$id];
        return $this->__processing();
        //return $this->__select() ;
    }

    /************** Compter les Tontines *********/
    public function  cotisationsTontineCount($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','t.montant', 't.statut'];
        $this->condition=["t.statut ="=>1, "t.fk_tontine=" =>$id];
        return count($this->__select());
    }

    /******************************************************************************** /
    /****************************** GESTION RETRAIT *******************************/
    /****************************************************************************** /

    /**************Liste des Retraits *********/
    public function  retrait()
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','CONCAT(c.prenom, " ", c.nom) as client','t.montant_encours as montant_verse', 't.montant_retrait','o.cagnotte','t.retrait'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        //$this->condition = ["t.statut >"=>1];
        $this->condition = ["t.etat ="=>1, "t.retrait ="=>1];
        return $this->__processing();
        //return $this->__select() ;
    }

    /************** Compter les Retraits *********/
    public function  retraitCount()
    {
        $this->table = "t_tontine t";
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        //$this->condition = ["t.statut >"=>1];
        $this->condition = ["t.etat ="=>1, "t.retrait ="=>1];
        return count($this->__select());
    }

    /************** Enregistrer Retrait *********/
    public function enregRetrait($id){
        //var_dump($_POST);exit;
        $this->table = "t_tontine";
        $_POST['statut'] = 2 ;
        $_POST['retrait'] = 1 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************** Avoir l'élément en question *********/
    public function getRetraitById($id){
       /* $this->table = "t_tontine t";
        $this->champs =['t.*','o.libelle as offre','o.cagnotte','CONCAT(c.nom, " ", c.prenom) as client'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.rowid ="=>$id];*/
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','CONCAT(c.prenom, " ", c.nom) as client','t.montant_encours as montant_verse', 't.montant_retrait','o.cagnotte', 'o.frais', 'c.telephone',
            'c.cin','t.retrait','o.libelle as loffre' ,'t.mise','t.penalite','t.periode','t.duree','t.mois_c','t.montant_bv','t.com_bv','t.statut', 't.date_retrait'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        
        "];
        $this->condition = ["t.rowid =" =>$id];
        return $this->__select()[0] ;
    }

    /************** Modifier Tontine *********/
    public function updateRetrait($id){
        //var_dump($_POST);exit;
        $this->table = "t_tontine";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************** Liste des Tontines cloturées ou abandonnées *********/
    public function getTontinesWithCustomers()
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','CONCAT(t.libelle,"---",c.prenom," ", c.nom,"---", c.telephone) as latontine'];
        $this->jointure =["
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.statut >"=>1];
        return $this->__select() ;
    }

    /************** Liste de tous les clients dont leur tontine est cloturée ou abandonnée *********/
    public function allClientsAPayer()
    {
        $this->table ='t_tontine t';
        $this->champs =['c.rowid','CONCAT(c.prenom, " ", c.nom) as clients'];
        $this->jointure =["
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["c.etat ="=>1, "t.statut >"=>1];
        return $this->__select() ;

    }

    /************** recup info clients *********/
    public function getDonneesClients($idclient)
    {
        $this->table = "t_tontine t";
        $this->champs = ['t.rowid','CONCAT(t.libelle,"---",c.prenom," ", c.nom,"---", c.telephone) as latontine','t.libelle', 't.montant_encours', 'o.cagnotte', 't.montant_retrait'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition = ["t.fk_client =" =>$idclient];
        return $this->__select();
    }

    /************** Archiver Retrait *********/
    public function archiverRetrait($id){
        //var_dump($_POST);exit;
        $this->table = "t_tontine";
        $_POST['etat'] = 0 ;
        //var_dump($_POST);exit;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /**************Liste des Retraits *********/
    public function  recu($idtontine)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','CONCAT(c.prenom, " ", c.nom) as client','t.montant_encours as montant_verse', 't.montant_retrait','o.cagnotte', 'o.frais', 'c.telephone',
         'a.email','a.tel','c.cin','t.retrait','o.libelle as loffre' ,'t.mise','t.penalite','t.periode','t.duree','t.mois_c','t.montant_bv','t.com_bv', 'a.label as agence', 't.date_retrait'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
            INNER JOIN agence a ON c.fk_agence = a.rowid
        "];
        $this->condition = ["t.statut >"=>1, "t.rowid =" =>$idtontine];
        //return $this->__processing();
        return $this->__select()[0] ;

    }

    public function  recuTransaction($idTransaction)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.libelle','CONCAT(c.prenom, " ", c.nom) as client','t.montant_encours as montant_verse', 't.montant_retrait','o.cagnotte', 'o.frais', 'c.telephone',
         'a.email','a.tel','c.cin','t.retrait','o.libelle as loffre' ,'t.mise','t.penalite','t.periode','t.duree','t.mois_c','t.montant_bv','t.com_bv', 'a.label as agence', 'tr.date_transaction as date_retrait',
            'tr.montant as mt_transaction','tr.nb_cotisation','tr.num_cotisation','tr.montant_cotise','tr.penalite as penaliteTontine'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
            INNER JOIN agence a ON c.fk_agence = a.rowid
            INNER JOIN t_transaction tr ON t.rowid = tr.fk_tontine
        "];
        $this->condition = ["tr.rowid =" =>$idTransaction];
        //return $this->__processing();
        return $this->__select()[0] ;
    }

    /************** Compter les Tontines *********/
    public function  nbreTontinesEnCours()
    {
        $this->table = "t_tontine";
        $this->champs =['rowid'];
        $this->condition=["statut ="=>1];
        return count($this->__select());
    }

    /************** Compter les Retraits *********/
    public function  nbreDeRetraits()
    {
        $this->table = "t_tontine";
        $this->champs =['rowid'];
        $this->condition=["retrait ="=>1];
        return count($this->__select());
    }

    /**************Liste des collecteurs*********/
    public function  retraitsDuClient($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','t.montant', 'o.libelle as offre', 'tne.retrait'];
        $this->jointure =
            ["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["tne.retrait ="=>1, "t.fk_client=" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les transactions *********/
    public function  retraitsDuClientCount($id)
    {
        $this->table = "t_transaction t";
        $this->jointure =
            ["
            INNER JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["tne.retrait ="=>1, "t.fk_client=" =>$id[0]];
        return count($this->__select());
    }


}