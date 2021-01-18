<?php
/**
 * Created by PhpStorm.
 * User: Developpeur
 * Date: 01/06/2018
 * Time: 15:20
 */


class OffreModel extends \app\core\BaseModelDao
{



    /**************Liste des équipement*********/
    public function  allOffre()
    {
        $this->champs =['rowid','libelle','duree','versement','cagnotte','frais','etat'];
       return $this->__processing();
        //return $this->__select() ;
    }

    public function  allOffreCount()
    {
        $this->table = "t_offres";
        return count($this->__select());
    }

    /************** Insertion d'offre *********/
    public function insert($data){
        $this->champs =  $data;
        $insert = $this->__insert() ;
        return $insert ;
    }

    /**************Liste des types d'equipement*********/

    public function getOffres(){
        $this->champs =['rowid','libelle','duree','versement','cagnotte','frais','etat'];
        $this->condition=["etat ="=>1];
        return $this->__select() ;
    }

    public function getOffreByIdJson($id){
        $this->champs =['rowid','libelle','duree','versement','cagnotte','frais','etat'];
        $this->condition=["rowid ="=>$id];
        return $this->__select()[0] ;
    }


    /************** Avoir l'élément en question *********/
    public function getOffreById($id){

        $this->champs =['*'];
        $this->condition=["rowid ="=>$id];
        return $this->__select()[0] ;
    }


    public function disableOffre($id){
        $_POST['etat'] = 0 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }
    public function activeOffre($id){
        $_POST['etat'] = 1 ;
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }

    public function updateOffre($id){
        $this->champs = $_POST;
        $this->condition=["rowid ="=>intval($id)];
        $update=  $this->__update();
        return $update ;
    }







}