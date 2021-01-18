<?php
/**
 * Created by PhpStorm.
 * User: Mansour
 * Date: 08/06/2018
 * Time: 10:00
 */


class ClientModel extends \app\core\BaseModelDao
{

    /**************Liste des clients *********/
    public function  client()
    {
        $this->table = "t_client c";
        $this->champs =['c.rowid','c.nom','c.prenom','c.cin','c.telephone','c.etat'];
        $this->jointure =["
            LEFT JOIN agence a on c.fk_agence = a.rowid
        "];
        //$this->condition=["c.etat ="=>1];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les clients *********/
    public function  clientCount()
    {
        $this->table = "t_client c";
        $this->jointure =["
            LEFT JOIN agence a on c.fk_agence = a.rowid
        "];
        //$this->condition=["c.etat ="=>1];
        return count($this->__select());
    }

    /************** Insertion client *********/
    public function insert($data){
        $this->table = "t_client";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /************** Insertion client *********/
    public function demarrerTontine($data){
        $this->table = "t_tontine";
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /**************Liste de tous les clients *********/
    public function getClient()
    {
        $this->champs =['rowid','nom','prenom','email','telephone','etat'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    /**************Liste de tous les collecteurs*********/
    public function allClients()
    {
        $this->table = "t_client";
        $this->champs =['rowid','CONCAT(prenom, " ",nom) as client'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }


    /************** Liste de toutes les agences *********/
    public function allAgence()
    {
        $this->table ='agence';
        $this->champs =['rowid','label as agence'];
        return $this->__select() ;
    }

    /********** verifier Email **********/
    public function verifEmail($email)
    {
        $this->table = "t_client";
        $this->champs = ["rowid"];
        $this->condition=["email ="=>$email];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;
    }

    /**********verifier Telephone**********/
    public function verifTelephone($tel)
    {
        $this->table = "t_client";
        $this->champs = ["rowid"];
        $this->condition=["telephone ="=>$tel];
        $count = count($this->__select());
        if($count > 0) return 1;
        else return -1;
    }

    /************** Avoir l'élément en question *********/
    public function getClientById($id){
       // echo $id ;
        if (intval($id)>0){
            $this->table = "t_client c";
            $this->champs =['c.*', 'a.label as agence', 't.fk_client', 't.fk_offre', 't.libelle', 't.montant_encours', 't.statut', 'CONCAT(col.prenom," ",col.nom) as collecteur'];
            $this->jointure =["
            LEFT JOIN agence a on c.fk_agence = a.rowid
            LEFT JOIN t_tontine t on t.fk_client = c.rowid
            LEFT JOIN t_collecteur col on c.fk_collecteur = col.rowid
        "];
            $this->condition=["c.rowid ="=>$id];
            //if (count($this->__select())>0)
            return $this->__select()[0];
        }else
            return null ;

    }


    public function getClientByTel($tel){
        $this->table = "t_client c";
        $this->champs =['c.*', 'a.label as agence', 't.fk_client', 't.fk_offre', 't.libelle', 't.montant_encours', 't.statut', 'CONCAT(col.prenom," ",col.nom) as collecteur'];
        $this->jointure =["
            LEFT JOIN agence a on c.fk_agence = a.rowid
            LEFT JOIN t_tontine t on t.fk_client = c.rowid
            LEFT JOIN t_collecteur col on c.fk_collecteur = col.rowid
        "];
        $this->condition=["c.telephone ="=>$tel];
       // var_dump($this->__select());die();
        return $this->__select();
    }


    public function disableClient($id){
        $_POST['etat'] = 0 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function activeClient($id){
        $_POST['etat'] = 1 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function updateClient($id){
        //var_dump($_POST);exit;
        $this->table = "t_client";
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    /************** Compter les Clients *********/
    public function  nbreClientsActifs()
    {
        $this->table = "t_client";
        $this->champs =['rowid'];
        $this->condition=["etat ="=>1];
        return count($this->__select());
    }

    /**************Liste des clients souscrits à une offre *********/
    public function  clientSouscrit($id)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','CONCAT(c.prenom," ",c.nom) as client','c.telephone','c.etat'];
        $this->jointure =["
            INNER JOIN t_client c on t.fk_client = c.rowid
            INNER JOIN t_offres o on t.fk_offre = o.rowid
        "];
        $this->condition=["t.fk_offre =" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les clients souscrits à une offre *********/
    public function  clientSouscritCount($id)
    {
        $this->table = "t_tontine t";
        $this->jointure =["
            INNER JOIN t_client c on t.fk_client = c.rowid
            INNER JOIN t_offres o on t.fk_offre = o.rowid
        "];
        $this->condition=["t.fk_offre =" =>$id[0]];
        return count($this->__select());
    }

    /**************Liste des transactions du client*********/
    public function  transactionsDuClient($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','CONCAT(c.prenom, " ", c.nom) as collecteur','t.montant','tne.libelle', 'o.libelle as offre', 't.statut'];
        $this->jointure =
            ["
            LEFT JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["t.statut ="=>1, "t.fk_client=" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }



    public function  transactionsDuClientTable($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','tne.libelle','t.montant_cotise','t.commentaire', 't.statut'];
        $this->jointure =
            ["
           
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
           
            "];
        $this->condition=["t.statut ="=>1, "t.fk_client=" =>$id];
        return $this->__select();
        //return $this->__select() ;
    }

    /**************compter les transactions du client*********/
    public function  transactionsDuClientCount($id)
    {
        $this->table = "t_transaction t";
        $this->jointure =
            ["
            LEFT JOIN t_collecteur c on t.fk_collecteur = c.rowid
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
            INNER JOIN t_offres o on tne.fk_offre = o.rowid
            "];
        $this->condition=["t.statut ="=>1, "t.fk_client=" =>$id[0]];
        return count($this->__select());
    }



    /**************Liste des transactions du client*********/
    public function  transactionsDuClientBV($id)
    {
        $this->table = "t_transaction t";
        $this->champs =['t.rowid','t.date_transaction as date_avec_heure','tne.libelle','t.montant','t.commentaire', 't.statut'];
        $this->jointure =
            ["
           
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
           
            "];
        $this->condition=["t.statut ="=>1, "t.fk_client=" =>$id[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les transactions du client*********/
    public function  transactionsDuClientBVCount($id)
    {
        $this->table = "t_transaction t";
        $this->jointure =
            ["
          
            INNER JOIN t_tontine tne on t.fk_tontine = tne.rowid
          
            "];
        $this->condition=["t.statut ="=>1, "t.fk_client=" =>$id[0]];
        return count($this->__select());
    }

    /**************Liste des Souscriptions du Client *********/
    public function  souscriptionsDuClient($idclient)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.date_creation as date_avec_heure','t.libelle','o.libelle as offre','t.statut'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.etat ="=>1, "t.fk_client=" =>$idclient[0]];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************Liste des Souscriptions du Client *********/
    public function  souscriptionsByClient($idclient)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.fk_offre','t.libelle as labelTontine','t.montant_encours','t.mois_c','t.mois_r','t.duree','o.libelle as labelFrais','o.frais','o.cagnotte','t.etat as etatTontine','t.statut'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.statut ="=>1,"t.etat ="=>1, "t.fk_client=" =>$idclient];
        return $this->__select();
        //return $this->__select() ;
    }

    /**************Liste des Souscriptions du Client *********/
    public function  nbTontinesByClient($idclient)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.fk_offre','t.libelle as labelTontine','t.montant_encours','t.mois_c','t.mois_r','t.duree','o.libelle as labelFrais','o.frais','o.cagnotte','t.etat as etatTontine','t.statut'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.etat ="=>1, "t.fk_client=" =>$idclient];
        return $this->__select();
        //return $this->__select() ;
    }

 /**************Liste des Souscriptions du Client *********/
    public function  tontinesByClient($idclient)
    {
        $this->table = "t_tontine t";
        $this->champs =['t.rowid','t.fk_offre','t.libelle as labelTontine','t.montant_encours','t.mois_c','t.mois_r','t.mise','t.penalite','t.duree','o.libelle as labelFrais','o.frais','o.cagnotte','t.etat as etatTontine','t.statut','t.periode'];
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.etat ="=>1,"t.retrait ="=>0, "t.fk_client=" =>$idclient];
        return $this->__select();
        //return $this->__select() ;
    }

    /************** Compter les Souscriptions du Client *********/
    public function  souscriptionsDuClientCount($idclient)
    {
        $this->table = "t_tontine t";
        $this->jointure =["
            INNER JOIN t_offres o ON t.fk_offre = o.rowid
            INNER JOIN t_client c ON t.fk_client = c.rowid
        "];
        $this->condition=["t.etat ="=>1, "t.fk_client=" =>$idclient[0]];
        return count($this->__select());
    }


}