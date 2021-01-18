<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


//require_once __DIR__.'/Utilisateur.class.php';

class VersementModel extends \app\core\BaseModelDao
{
    /**************Liste des collecteurs*********/
    public function  versement()
    {
        $this->table = "t_versement v";
        $this->champs =['v.rowid','v.date_creation as date_debut', 'v.date_versement as date_c', 'CONCAT(c.prenom," ",c.nom) as collecteur','a.label as agence','v.montant_collect','v.montant_verse','v.manquant'];
        $this->jointure =["
            INNER JOIN agence a on v.fk_agence = a.rowid
            INNER JOIN t_collecteur c on v.fk_collecteur = c.rowid
        "];
        //$this->condition=["v.etat ="=>1];
       // $this->group=["v.date_creation","v.fk_collecteur"];
        return $this->__processing();
        //return $this->__select() ;
    }

    /**************compter les collecteurs *********/
    public function  versementCount()
    {
        $this->table = "t_versement v";
        $this->jointure =["
            INNER JOIN agence a on v.fk_agence = a.rowid
            INNER JOIN t_collecteur c on v.fk_collecteur = c.rowid
        "];
        //$this->condition=["c.etat ="=>1];
        //$this->group=["v.date_creation","v.fk_collecteur"];
        return count($this->__select());
    }

    /**************Liste de tous les collecteurs*********/
    public function getCollecteurs()
    {
        $this->table = "t_collecteur";
        $this->champs =['rowid','nom','prenom','email','telephone','etat'];
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

    /************** Avoir l'élément en question *********/
    public function getVersementById($id){
        $this->table = "t_versement v";
        $this->champs =['v.*', 'a.label as agence', 'CONCAT(c.prenom," ",c.nom) as collecteur'];
        $this->jointure =["
            INNER JOIN agence a on v.fk_agence = a.rowid
            INNER JOIN t_collecteur c on v.fk_collecteur = c.rowid
        "];
        $this->condition=["v.rowid ="=>$id];
        return $this->__select()[0] ;
    }

    /**************Liste des collecteurs*********/
    public function  imprimer($idversement)
    {
        $this->table = "t_versement v";
        $this->champs =['v.rowid','v.date_creation as date_debut', 'CONCAT(c.prenom," ",c.nom) as collecteur', 'c.telephone','a.label as agence','v.montant_collect','v.montant_verse','v.manquant'];
        $this->jointure =["
            INNER JOIN agence a on v.fk_agence = a.rowid
            INNER JOIN t_collecteur c on v.fk_collecteur = c.rowid
        "];
        $this->condition=["v.etat =" =>1, "v.rowid =" =>$idversement];
        //return $this->__processing();
        return $this->__select()[0] ;
        //var_dump($this->__select()[0]); die();
    }

}