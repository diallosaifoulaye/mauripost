<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 22/02/2017
 * Time: 09:54
 */

//namespace app\models ;

class Participant extends \app\core\BaseClass
{

    private $id;
    private $nom;
    private $prenom;
    private $telephone;
    private $password;
    private $adresse;
    private $email;
    private $etatparticipant;

    /**
     * @return mixed
     */
    public function getEtatparticipant()
    {
        return $this->etatparticipant;
    }

    /**
     * @param mixed $etatparticipant
     */
    public function setEtatparticipant($etatparticipant)
    {
        $this->etatparticipant = $etatparticipant;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param mixed $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param mixed $adresse
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }




    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

}

