<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 22/02/2017
 * Time: 09:54
 */


class Utilisateur extends \app\core\BaseClass
{

    private $rowid;

    public function getRowid(){
        return $this->rowid;
    }

    private $nom;

    public function getNom(){
        return $this->nom;
    }


    public function setNom($nom){
        $this->nom = $nom;
        return $this;
    }

    private $codeGuichet;

    public function getCodeGuichet(){
        return $this->codeGuichet;
    }


    public function setCodeGuichet($codeGuichet){
        $this->codeGuichet = $codeGuichet;
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

    private $fkProfil;

    public function getFkProfil(){
        return $this->fkProfil;
    }


    public function setFkProfil($fkProfil){
        $this->fkProfil = $fkProfil;
        return $this;
    }

    private $fkAgence;

    public function getFkAgence(){
        return $this->fkAgence;
    }


    public function setFkAgence($fkAgence){
        $this->fkAgence = $fkAgence;
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

    private $cleSecrete;

    public function getCleSecrete(){
        return $this->cleSecrete;
    }


    public function setCleSecrete($cleSecrete){
        $this->cleSecrete = $cleSecrete;
        return $this;
    }

    private $etatConnect;

    public function getEtatConnect(){
        return $this->etatConnect;
    }


    public function setEtatConnect($etatConnect){
        $this->etatConnect = $etatConnect;
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

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

}

