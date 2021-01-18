<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


class OffresModel extends \app\core\BaseModel
{
    public function  allOffres($requestData = null)
    {
        try{
            $sql = "Select rowid, libelle, montant from offres_postales";
            if(!is_null($requestData)){
                $sql.=" WHERE ( libelle LIKE '%".$requestData."%' ";
                $sql.=" OR montant LIKE  '%".$requestData."%' )";
            }
            $tabCol = ['libelle', 'montant'];
            if(intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql.=" ORDER BY ".$tabCol[$_REQUEST['order'][0]['column']]." ".strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return $Exception;
        }
    }

    public function  allOffresCount($requestData = null)
    {
        try {
            $sql = "Select COUNT(rowid) as total from offres_postales";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( libelle LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant LIKE  '%" . $requestData . "%' )";
            }
            $tabCol = ['libelle', 'montant'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function insertOffre($param)
    {
        try
        {
            $sql = "INSERT INTO offres_postales(libelle, montant, description, conditions, user_crea, date_crea) 
                    VALUES (:libelle, :montant, :description, :conditions, :user_crea, :date_crea)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "libelle"=>$param['libelle'],
                "montant"=>$param['montant'],
                "description"=>$param['description'],
                "conditions"=>$param['conditions'],

                "user_crea"=>$param['user_crea'],
                "date_crea"=>$param['date_crea']
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception )
        {
            var_dump($Exception);die;
            return -2;
        }

    }

    public function getOffreByIdString($id){
        try{
            $sql = "Select a.rowid, a.libelle, a.montant, a.description, a.conditions, a.user_crea, a.date_crea, a.user_modif, a.date_modif, a.etat
                from offres_postales a
                WHERE a.rowid = :id";
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

    public function updateOffre($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE offres_postales 
                    SET libelle = :libelle, montant = :typeprofil, description = :des, conditions = :cond, user_modif= :user_modification, date_modif = :date_modification 
                    WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "libelle" =>$param['libelle'],
                "typeprofil" =>$param['montant'],
                "des" =>$param['description'],
                "cond" =>$param['conditions'],
                "user_modification" =>$param['user_modif'],
                "date_modification" =>$param['date_modif'],
                "id" => $id
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }

    }

    public function updateOffreState($param)
    {
        try
        {
            //var_dump($param);die;
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE offres_postales SET etat=?, user_modif=?, date_modif=? WHERE rowid=?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                $param['etat'],
                $param['user_modif'],
                $param['date_modif'],
                $id
            ));
            $this->closeConnexion();
            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }

    }

}