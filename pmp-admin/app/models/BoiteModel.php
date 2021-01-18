<?php
/**
 * Created by PhpStorm.
 * User: Developpeur
 * Date: 01/06/2018
 * Time: 15:20
 */


class BoiteModel extends \app\core\BaseModel
{



    /**************Liste des Aboonés*********/

    public function  allBoite_affect($requestData = null)
    {  //var_dump("test");die;
        try
        {
            $sql = "Select a.id as rowid, a.numero as num_boite, b.nom_complet
                    from boite_postale as a 
                    INNER JOIN beneficiaire_postale b ON b.id = a.fk_beneficiaire_postale";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( a.numero LIKE '%".$requestData."%'";
                $sql.=" OR b.nom_complet LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.numero','b.nom_complet'];
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

    public function  allBoite_affectCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(a.id) as total
                    from boite_postale as a
                    INNER JOIN beneficiaire_postale b ON b.id = a.fk_beneficiaire_postale";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }


    /**************Liste des Aboonés*********/

    public function  allBoite_no($requestData = null)
    {  //var_dump("test");die;
        try
        {
            $sql = "Select a.id as rowid, a.numero
                    from boite_postale as a WHERE a.etat=0";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( a.numero LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.numero'];
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

    public function  allBoite_noCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(a.id) as total
                    from boite_postale as a WHERE a.fk_beneficiaire_postale=null";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getBeneficiaire(){
        try
        {
            $sql = "Select a.id, a.nom_complet
                    from beneficiaire_postale as a ";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function  insertBt($param)
    {
        try
        {
            $sql = "INSERT INTO boite_postale(numero,fk_beneficiaire_postale,etat) VALUES (:numero,:fk_beneficiaire_postale, :etat)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array( "numero"=>$param['numero'],"fk_beneficiaire_postale"=>$param['fk_beneficiaire_postale'],"etat"=>$param["etat"] )
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


    public function getBoiteById($id){
        try{
            $sql = "Select a.* , b.nom_complet
                from boite_postale as a
                LEFT JOIN beneficiaire_postale b ON b.id = a.fk_beneficiaire_postale
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

    public function updateBoite($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE boite_postale SET numero =?, fk_beneficiaire_postale =?,etat=? WHERE id =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['numero'],
                $param['fk_beneficiaire_postale'],
                $param['etat'],
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

    public function affectBoite($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE boite_postale SET fk_beneficiaire_postale =?,etat=? WHERE id =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['fk_beneficiaire_postale'],
                $param['etat'],
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

    public function freeBoite($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE boite_postale SET fk_beneficiaire_postale =?,etat=? WHERE id =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                null,
                $param['etat'],
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
    public function verifNum($numero)
    {
        try
        {
            $sql = "Select u.id from boite_postale as u WHERE u.numero=:numero";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("numero"=>$numero));
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