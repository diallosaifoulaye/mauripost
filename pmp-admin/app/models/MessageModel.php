<?php

/**
 * Created by PhpStorm.
 * User: finance3
 * Date: 25/08/2017
 * Time: 12:39
 */
class MessageModel extends \app\core\BaseModel
{

    public function insertMessage($expediteur, $module, $txt, $user_creation)
    {
        try {
            $date_creation = date('Y-m-d H:i:s');
            $sql = "INSERT INTO messenger (expediteur,txt_messenger, module, statut, user_creation, date_creation)
                  VALUES (:expediteur,:txt_messenger, :module, :stat, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array("expediteur" => $expediteur,
                    "txt_messenger" => $txt,
                    "module" => $module,
                    "stat" => strval('actif'),
                    "user_creation" => $user_creation,
                    "date_creation" => $date_creation)
            );
            //echo $sql;exit;
            if ($res == 1) return 1;
            else return -1;
            $this->closeConnexion();
        } catch (PDOException $Exception) {
            return -1;
        }

    }

    public function allModule()
    {
        try {
            $sql = "Select idmodule, nom_module from module WHERE etat = :etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1,));
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        } catch (PDOException $e) {
            return -1;
        }
    }

    public function allMessage($requestData = null)
    {
        try {
            $sql = "SELECT m.idmessenger as rowid, m.expediteur, m.txt_messenger, s.nom_module as nom_module, m.etat FROM messenger as m LEFT JOIN module as s ON m.module = s.idmodule WHERE m.etat = 1";
            if (!is_null($requestData)) {
                $sql .= " AND ( m.expediteur LIKE '%" . $requestData . "%' ";
                $sql .= " OR  m.txt_messenger LIKE '%" . $requestData . "%' ";
                $sql .= " OR s.nom_module LIKE '%" . $requestData . "%' ";
                $sql .= " OR m.etat LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['m.expediteur', 'm.txt_messenger', 's.nom_module', 'm.etat'];
            if($_REQUEST['order'][0]['column'] < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        } catch (PDOException $e) {
            return -1;
        }
    }

    public function afficheAllMessage($id)
    {
        try {
                $sql = "SELECT m.idmessenger as rowid, m.expediteur, m.txt_messenger, m.etat
                    FROM messenger as m
                    WHERE m.etat = 1 AND m.module = ".$id;

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        } catch (PDOException $e) {
            return -1;
        }
    }



    /********Liste beneficiaires*********/
    public function allMessageCount()
    {
        try {
            //$sql = "SELECT COUNT(idmessenger) as total FROM messenger ";
            $sql = "SELECT COUNT(m.idmessenger) as total FROM messenger as m LEFT JOIN module as s ON m.module = s.idmodule WHERE m.etat = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function getMessageByIdInteger($idmessenger)
    {
        $sql = "SELECT m.idmessenger,m.expediteur,m.txt_messenger,s.nom_module,m.etat,m.user_creation,m.date_creation,m.user_modification,m.date_modification
                FROM messenger as m INNER JOIN module as s  ON m.module = s.idmodule
                WHERE idmessenger = :idmessenger";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "idmessenger" => $idmessenger,
            )
        );
        $a = $user->fetch();
        $this->closeConnexion();
        return $a;
    }

    /**
     * @param $id ,$user_modification
     * @return int
     * CETTE METHODE PERMET DE DESACTIVER UN MESSAGE D'ENTETE
     */
    public function disableMessage($id, $user_modification)
    {

        try {
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE messenger SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification,statut=:statut
                WHERE idmessenger = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(0),
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "statut" => strval('non actif'),
                "id" => intval($id),
            ));
            $this->closeConnexion();
            if ($res == 1) {
                return 2;
            }
        } catch (PDOException $Exception) {
            return -2;
        }

    }

    /**
     * @param $id ,$user_modification
     * @return int
     * CETTE METHODE PERMET D'ACTIVER UN MESSAGE D'ENTETE
     */
    public function enableMessage($id, $user_modification)
    {

        try {
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE messenger SET etat = :etat, user_modification = :user_modification, date_modification = :date_modification,statut=:statut
                WHERE idmessenger = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "etat" => intval(1),
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "statut" => strval('actif'),
                "id" => intval($id),
            ));
            $this->closeConnexion();
            if ($res == 1) {
                return 4;
            }
        } catch (PDOException $Exception) {
            return -4;
        }

    }

    /**
     * @param $label , $typeprofil, $id,$user_modification
     * @return int
     * CETTE METHODE PERMET DE METTRE A JOUR UN MESSAGE
     */
    public function updateMessage($expediteur, $module, $txt_messenger, $id, $user_modification)
    {

        try {
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE messenger SET  expediteur= :expediteur, module = :module, txt_messenger=:txt_messenger, user_modification = :user_modification, date_modification = :date_modification
                WHERE idmessenger = :id";

            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "expediteur" => $expediteur,
                "module" => $module,
                "txt_messenger" => $txt_messenger,
                "user_modification" => $user_modification,
                "date_modification" => $date_modification,
                "id" => $id
            ));
            $this->closeConnexion();
            if ($res == 1) {
                return 1;
            }
        } catch (PDOException $Exception) {
            return -1;
        }


    }




}