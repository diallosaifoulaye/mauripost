<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 22/02/2017
 * Time: 09:54
 */

//namespace app\models ;

class Utilisateur extends \app\core\BakendClass
{

    private $rowid;

    public function getRowid(){
        return $this->rowid;
    }

    public function setRowid($rowid){
        $this->rowid = $rowid;
        return $this;
    }

    private $nom;

    public function getNom(){
        return $this->nom;
    }


    public function setNom($nom){
        $this->nom = $nom;
        return $this;
    }

    private $code_guichet;

    public function getCode_guichet(){
        return $this->code_guichet;
    }


    public function setCode_guichet($codeGuichet){
        $this->code_guichet = $codeGuichet;
        return $this;
    }

    private $prenom;

    public function getPrenom(){
        return $this->prenom;
    }


    public function setPrenom($prenom){
        $this->prenom = $prenom;
        return $this;
    }

    private $email;

    public function getEmail(){
        return $this->email;
    }


    public function setEmail($email){
        $this->email = $email;
        return $this;
    }

    private $telephone;

    public function getTelephone(){
        return $this->telephone;
    }


    public function setTelephone($telephone){
        $this->telephone = $telephone;
        return $this;
    }

    private $login;

    public function getLogin(){
        return $this->login;
    }


    public function setLogin($login){
        $this->login = $login;
        return $this;
    }

    private $password;

    public function getPassword(){
        return $this->password;
    }


    public function setPassword($password){
        $this->password = $password;
        return $this;
    }

    private $fk_profil;

    public function getFk_profil(){
        return $this->fk_profil;
    }


    public function setFk_profil($fk_Profil){
        $this->fk_profil = $fk_Profil;
        return $this;
    }

    private $fk_agence;

    public function getFk_agence(){
        return $this->fk_agence;
    }


    public function setFk_agence($fk_Agence){
        $this->fk_agence = $fk_Agence;
        return $this;
    }

    private $type_compte;

    public function getType_compte(){
        return $this->type_compte;
    }


    public function setType_compte($typeCompte){
        $this->type_compte = $typeCompte;
        return $this;
    }

    private $date_modif;

    public function getDate_modif(){
        return $this->date_modif;
    }


    public function setDate_modif($dateModif){
        $this->date_modif = $dateModif;
        return $this;
    }

    private $admin;

    public function getAdmin(){
        return $this->admin;
    }


    public function setAdmin($admin){
        $this->admin = $admin;
        return $this;
    }

    private $connect;

    public function getConnect(){
        return $this->connect;
    }


    public function setConnect($connect){
        $this->connect = $connect;
        return $this;
    }

    private $etat;

    public function getEtat(){
        return $this->etat;
    }


    public function setEtat($etat){
        $this->etat = $etat;
        return $this;
    }

    private $user_crea;

    public function getUser_crea(){
        return $this->user_crea;
    }


    public function setUser_crea($userCrea){
        $this->user_crea = $userCrea;
        return $this;
    }

    private $date_crea;

    public function getDate_crea(){
        return $this->date_crea;
    }


    public function setDate_crea($dateCrea){
        $this->date_crea = $dateCrea;
        return $this;
    }

    private $user_modif;

    public function getUser_modif(){
        return $this->user_modif;
    }


    public function setUser_modif($userModif){
        $this->user_modif = $userModif;
        return $this;
    }

    private $user_supp;

    public function getUser_supp(){
        return $this->user_supp;
    }


    public function setUser_supp($userSupp){
        $this->user_supp = $userSupp;
        return $this;
    }

    private $date_supp;

    public function getDate_supp(){
        return $this->date_supp;
    }


    public function setDate_supp($dateSupp){
        $this->date_supp = $dateSupp;
        return $this;
    }

    private $token;

    public function getToken(){
        return $this->token;
    }


    public function setToken($token){
        $this->token = $token;
        return $this;
    }

    private $clesecrete;

    public function getClesecrete(){
        return $this->clesecrete;
    }


    public function setClesecrete($clesecrete){
        $this->clesecrete = $clesecrete;
        return $this;
    }

    private $superviseur;

    public function getSuperviseur(){
        return $this->superviseur;
    }


    public function setSuperviseur($superviseur){
        $this->superviseur = $superviseur;
        return $this;
    }

    private $idtype_agence;

    public function getIdtype_agence(){
        return $this->idtype_agence;
    }

    public function setIdtype_agence($idtype_agence){
        $this->idtype_agence = $idtype_agence;
        return $this;
    }

    private $code;

    public function getCode(){
        return $this->code;
    }

    public function setCode($code){
        $this->code = $code;
        return $this;
    }

    private $label;

    public function getLabel(){
        return $this->label;
    }

    public function setLabel($label){
        $this->label = $label;
        return $this;
    }

    private $DR;

    public function getDR(){
        return $this->DR;
    }

    public function setDR($DR){
        $this->DR = $DR;
        return $this;
    }

    private $type_profil;

    public function getType_profil(){
        return $this->type_profil;
    }

    public function setType_profil($type_profil){
        $this->type_profil = $type_profil;
        return $this;
    }



    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

}

