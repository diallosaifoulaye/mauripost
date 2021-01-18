<?php


class TypeProfilModel extends \app\core\BaseModel
{

    /**
     * INSERTION TYPE PROFIL
     */
    public function insertProfil($libelletypeprofil,$user_creation){

        try{
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO type_profil(libelle, user_creation, date_creation) 
                    VALUES (:libelle,:user_creation, :date_creation)";
            $profil = $this->getConnexion()->prepare($sql);
            $res = $profil->execute(array(
                "libelle" =>$libelletypeprofil,
                "user_creation" =>$user_creation,
                "date_creation" =>$date_creation
            ));
            if($res==1)
            {
                return 1;
            }
        }
        catch(PDOException $Exception ){
            $res = false;
        }
        return $res;

    }

    /*****Liste Profils******/
    public function  allTypeProfil()
    {
        try
        {
            $sql = "Select idtypeprofil, libelle from type_profil WHERE etat = :etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1,));
            $a = $user->fetchAll();
            return $a;
        }
        catch(PDOException $e){
            return -1;
        }

    }
}