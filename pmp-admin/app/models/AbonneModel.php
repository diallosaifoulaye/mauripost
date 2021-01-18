<?php
/**
 * Created by PhpStorm.
 * User: Developpeur
 * Date: 01/06/2018
 * Time: 15:20
 */


class AbonneModel extends \app\core\BaseModel
{



    /**************Liste des Aboonés*********/

    public function  allAboone($requestData = null)
    {
        try
        {
            $sql = "Select a.id as rowid, a.nom_complet, a.email, a.tel, a.adresse
                    from beneficiaire_postale as a";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( a.nom_complet LIKE '%".$requestData."%' ";
                $sql.=" OR a.email LIKE '%".$requestData."%' ";
                $sql.=" OR a.tel LIKE '%".$requestData."%' ";
                $sql.=" OR a.adresse LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.nom_complet','a.email', 'a.tel', 'a.adresse'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  allAbooneCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(a.id) as total
                from beneficiaire_postale as a";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($a);die;
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    public function  insertBp($param)
    {
        try
        {
            $sql = "INSERT INTO beneficiaire_postale(nom_complet, email, tel, adresse) VALUES (:nom_complet, :email, :tel, :adresse)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                    "nom_complet"=>$param['nom_complet'],
                    "email"=>$param['email'],
                    "tel"=>$param['tel'],
                    "adresse"=>$param['adresse'] )
            );
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            return -2;
        }
    }


    public function getAbooneById($id){
        try{
            $sql = "Select a.* 
                from beneficiaire_postale as a
                WHERE a.id = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("id" =>$id));
            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    public function updateAbonne($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE beneficiaire_postale SET nom_complet =?, email =?, tel =?, adresse =? WHERE id =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['nom_complet'],
                $param['email'],
                $param['tel'],
                $param['adresse'],
                $id
            ]);
            $this->closeConnexion();

            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }

    }

    /**********verifier Identifiant**********/
    public function verifEmail($email)
    {
        try
        {
            $sql = "Select u.id from beneficiaire_postale as u WHERE u.email=:email";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("email"=>$email));
            $a = $user->fetchObject();
            $this->closeConnexion();
            $rowcount = $user->rowCount();
            if($rowcount > 0) return 1;
            else return -1;
        }
        catch (PDOException $exception)
        {
            return -2;
        }
    }


}