<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


require_once __DIR__.'/Utilisateur.class.php';

class BillsModel extends \app\core\BaseModel
{

    /***************crediter solde agence***************/
    function crediter_soldeAgence($montant, $row_agence)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE agence SET solde=solde+:soldes WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$row_agence));
            if($Result1 > 0){
                return  1;
            }
            else return -1;
        }catch(PDOException $e) {
            return -2;
        }
    }

    /***************debiter solde agence***************/
    function debiter_soldeAgence($montant, $row_agence)
    {
        try
        {
            $req = $this->getConnexion()->prepare("UPDATE agence SET solde=solde-:soldes WHERE rowid=:agence");
            $Result1 = $req->execute(array("soldes"=>$montant, "agence"=>$row_agence));
            if($Result1 > 0)
            {
                return  1;
            }
            else return -1;
        }
        catch(PDOException $e)
        {
            return -2;
        }
    }

    public function crediterCarteParam($id,$montant){
        try{
            $sql = "UPDATE carte_parametrable SET solde = solde + ".$montant." WHERE idcarte = '".$id."' ";

            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    public function debiterCarteParam($id,$montant){
        try{
            $sql = "UPDATE carte_parametrable SET solde = solde - ".$montant." WHERE idcarte = '".$id."' ";

            $dbh = $this->getConnexion()->prepare($sql);

            $a = $dbh->execute();
            if($a > 0 ) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    public function  allBp($requestData = null)
    {
        try
        {
            $sql = "Select a.rowid, a.owner, a.numero, a.annee, o.libelle as offre, a.montant, ag.label as agence
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid ";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( a.numero LIKE '%".$requestData."%' ";
                $sql.=" OR a.owner LIKE '%".$requestData."%' ";
                $sql.=" OR a.annee LIKE '%".$requestData."%' ";
                $sql.=" OR  o.libelle LIKE '%".$requestData."%' ";
                $sql.=" OR  a.montant LIKE '%".$requestData."%' ";
                $sql.=" OR ag.label LIKE '%".$requestData."%' )";
            }
            $tabCol = ['a.owner','a.numero', 'a.annee', 'o.libelle', 'ag.label'];
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

    public function  allBpFilteredResp($requestData)
    {
        //var_dump($requestData);die;
        $deb = $requestData['deb'];
        $fin = $requestData['fin'];

        try
        {
            $sql = "Select a.rowid, a.owner, a.numero, a.annee, o.libelle as offre, a.montant, ag.label as agence, a.date_crea as date
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid 
                WHERE  DATE(a.date_crea) BETWEEN '".$deb."' AND '".$fin."'";

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

    public function  allBpFiltered($requestData = null)
    {
        //var_dump($requestData);die;
        $deb = $requestData['deb'];
        $fin = $requestData['fin'];

        if(!is_null($requestData['deb'])){ $requestData = null; }

        try
        {
            /*$sql = "Select a.rowid, a.owner, a.numero, a.annee, o.libelle as offre, a.montant, ag.label as agence, a.date_crea as date
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid 
                WHERE  DATE(a.date_crea) BETWEEN '".$deb."' AND '".$fin."'";*/


            $sql = "Select a.id as rowid,b.nom_complet as benef, a.annee, a.montant_paye ,p.label as period, bp.numero as bp, o.libelle
                from abonnement_postale as a
                LEFT OUTER JOIN boite_postale as bp
                ON a.fk_boite_postale  = bp.id
                LEFT OUTER JOIN beneficiaire_postale as b
                ON b.id = bp.fk_beneficiaire_postale
                LEFT OUTER JOIN periodicite_postale as p
                ON p.rowid = a.fk_periodicite_postale
                LEFT OUTER JOIN offres_postales as o
                ON o.rowid = a.fk_offre_postale
                WHERE  DATE(a.date_paiement) BETWEEN '".$deb."' AND '".$fin."'";


            if(!is_null($requestData)) {
                $sql.=" WHERE (b.nom_complet LIKE '%".$requestData."%' ";
                $sql.=" OR a.annee LIKE '%".$requestData."%' ";
                $sql.=" OR a.montant_paye LIKE '%".$requestData."%' ";
                $sql.=" OR a.p.label LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR o.libelle LIKE '%".$requestData."%' )";
            }


           /* if(!is_null($requestData)) {
                $sql.=" WHERE ( a.numero LIKE '%".$requestData."%' ";
                $sql.=" OR a.owner LIKE '%".$requestData."%' ";
                $sql.=" OR a.annee LIKE '%".$requestData."%' ";
                $sql.=" OR  o.libelle LIKE '%".$requestData."%' ";
                $sql.=" OR  a.montant LIKE '%".$requestData."%' ";
                $sql.=" OR ag.label LIKE '%".$requestData."%' )";
            }*/


            $tabCol = ['b.nom_complet','a.annee', 'a.montant_paye', 'p.label', 'bp.numero', 'o.libelle'];

           /* $tabCol = ['a.owner','a.numero', 'a.annee', 'o.libelle', 'ag.label'];*/

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


    /*public function  allBpNew($requestData = null)
    {
        try
        {
            $sql = "Select a.id as rowid,b.nom_complet as benef, a.annee, a.montant_paye ,p.label as period, bp.numero as bp, o.libelle
                from abonnement_postale as a
                LEFT OUTER JOIN boite_postale as bp
                ON a.fk_boite_postale  = bp.id
                LEFT OUTER JOIN beneficiaire_postale as b
                ON b.id = bp.fk_beneficiaire_postale
                LEFT OUTER JOIN periodicite_postale as p
                ON p.rowid = a.fk_periodicite_postale
                LEFT OUTER JOIN offres_postales as o
                ON o.rowid = a.fk_offre_postale";
            if(!is_null($requestData)) {
                $sql.=" WHERE (b.nom_complet LIKE '%".$requestData."%' ";
                $sql.=" OR a.annee LIKE '%".$requestData."%' ";
                $sql.=" OR a.montant_paye LIKE '%".$requestData."%' ";
                $sql.=" OR a.p.label LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR o.libelle LIKE '%".$requestData."%' )";
            }

            $tabCol = ['b.nom_complet','a.annee', 'a.montant_paye', 'p.label', 'bp.numero', 'o.libelle'];
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
    }*/














    public function  allBpFilteredCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT (a.rowid) as total
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid  
                WHERE  a.date_crea BETWEEN ? AND ?";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array($requestData['deb'],$requestData['fin']));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function  allBpCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT (a.rowid) as total
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid ";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function  OffresPostales()
    {
        try
        {
            $sql = "Select *
                from offres_postales
                WHERE etat = 1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();

            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  checkInteredBp($param)
    {
        //var_dump('ji');die;
        try
        {
            $sql = "Select COUNT(rowid) as id
                from abonnement_postales
                WHERE numero=? AND annee=? AND offre=?";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    $param['bp'],
                    $param['annee'],
                    $param['offre']
                )
            );

            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a->id;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  insertBp($param)
    {
        try
        {
            $montant = intval($param['montant']);
            $sql = "INSERT INTO abonnement_postales(owner, numero, annee, offre, agence, montant, user_crea, date_crea) VALUES (:owner, :numero, :annee, :offre, :agence, :montant, :user_creation, :date_creation)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "owner"=>$param['owner'],
                "numero"=>$param['numero'],
                "annee"=>$param['annee'],"offre"=>$param['offre'],
                "agence"=>$param['agence'],
                "montant"=>$montant,
                "user_creation"=>$param['user_crea'],
                "date_creation"=>$param['date_crea'])
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
    public function insertBpNew($param)
    {


        try
        {
            $montant_paye = intval($param['montant_paye']);
            $sql = "INSERT INTO abonnement_postale(annee, fk_offre_postale, fk_periodicite_postale, montant_paye, user_creation, fk_agence, fk_boite_postale) VALUES (:annee, :fk_offre_postale, :fk_periodicite_postale, :montant_paye, :user_creation, :fk_agence,:fk_boite_postale)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "annee"=>$param['annee'],
                "fk_offre_postale"=>$param['fk_offre_postale'],
                "fk_periodicite_postale"=>$param['fk_periodicite_postale'],
                "montant_paye"=>$montant_paye,
                "user_creation"=>$param['user_creation'],
                "fk_agence"=>$param['fk_agence'],
                "fk_boite_postale"=>$param['fk_boite_postale'])
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


    public function  allBpNew($requestData = null)
    {
        try
        {
            $sql = "Select a.id as rowid,b.nom_complet as benef, a.annee, a.montant_paye ,p.label as period, bp.numero as bp, o.libelle
                from abonnement_postale as a
                LEFT OUTER JOIN boite_postale as bp
                ON a.fk_boite_postale  = bp.id
                LEFT OUTER JOIN beneficiaire_postale as b
                ON b.id = bp.fk_beneficiaire_postale
                LEFT OUTER JOIN periodicite_postale as p
                ON p.rowid = a.fk_periodicite_postale
                LEFT OUTER JOIN offres_postales as o
                ON o.rowid = a.fk_offre_postale";
            if(!is_null($requestData)) {
                $sql.=" WHERE (b.nom_complet LIKE '%".$requestData."%' ";
                $sql.=" OR a.annee LIKE '%".$requestData."%' ";
                $sql.=" OR a.montant_paye LIKE '%".$requestData."%' ";
                $sql.=" OR a.p.label LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR bp.numero LIKE '%".$requestData."%' ";
                $sql.=" OR o.libelle LIKE '%".$requestData."%' )";
            }

            $tabCol = ['b.nom_complet','a.annee', 'a.montant_paye', 'p.label', 'bp.numero', 'o.libelle'];
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

    public function  allBpNewCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT (a.id) as total
                from abonnement_postale as a
                LEFT OUTER JOIN boite_postale as bp
                ON a.fk_boite_postale  = bp.id
                LEFT OUTER JOIN beneficiaire_postale as b
                ON b.id = bp.fk_beneficiaire_postale
                LEFT OUTER JOIN periodicite_postale as p
                ON p.rowid = a.fk_periodicite_postale
                LEFT OUTER JOIN offres_postales as o
                ON o.rowid = a.fk_offre_postale";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getBpByIdStringNew($id){
        try{
            $sql = "Select a.*, b.nom_complet as benef, a.annee, a.montant_paye ,p.label as period, bp.numero as bp, o.libelle as offre,ag.label as agence
                from abonnement_postale as a
                LEFT OUTER JOIN boite_postale as bp
                ON a.fk_boite_postale  = bp.id
                LEFT OUTER JOIN beneficiaire_postale as b
                ON b.id = bp.fk_beneficiaire_postale
                LEFT OUTER JOIN periodicite_postale as p
                ON p.rowid = a.fk_periodicite_postale
                LEFT OUTER JOIN offres_postales as o
                ON o.rowid = a.fk_offre_postale
                LEFT OUTER JOIN agence as ag
                ON ag.rowid = a.fk_agence
                WHERE a.id =".$id;

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchObject();
            $this->closeConnexion();
            return $a;
        }
        catch(PDOException $Exception ){
            return -2;
        }
    }

    public function updateBpNew($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $montant_old = intval($param['montant_old']);
            $montant = intval($param['montant']);
            $sql = "UPDATE abonnement_postales SET owner =?, numero =?, annee =?, offre =?, agence =?, montant =?, user_modif=?, date_modif =? WHERE rowid =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['owner'],
                $param['numero'],
                $param['annee'],
                $param['offre'],
                $param['agence'],
                $montant,
                $param['user_modif'],
                $param['date_modif'],
                $id
            ]);
            $this->closeConnexion();

            $this->crediter_soldeAgence($montant_old, $param['fk_agence']);
            $this->debiterCarteParam(6, $montant_old);

            $this->debiter_soldeAgence($montant, $param['fk_agence']);
            $this->crediterCarteParam(6, $montant);

            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }

    }

    public function getBpByIdString($id){
        try{
            $sql = "Select a.*, o.libelle , o.montant as mt_offre, ag.label 
                from abonnement_postales as a
                LEFT OUTER JOIN offres_postales as o
                ON a.offre = o.rowid
                LEFT OUTER JOIN agence as ag
                ON a.agence = ag.rowid 
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

    public function updateBp($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $montant_old = intval($param['montant_old']);
            $montant = intval($param['montant']);
            $sql = "UPDATE abonnement_postales SET owner =?, numero =?, annee =?, offre =?, agence =?, montant =?, user_modif=?, date_modif =? WHERE rowid =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['owner'],
                $param['numero'],
                $param['annee'],
                $param['offre'],
                $param['agence'],
                $montant,
                $param['user_modif'],
                $param['date_modif'],
                $id
            ]);
            $this->closeConnexion();

            $this->crediter_soldeAgence($montant_old, $param['fk_agence']);
            $this->debiterCarteParam(6, $montant_old);

            $this->debiter_soldeAgence($montant, $param['fk_agence']);
            $this->crediterCarteParam(6, $montant);

            if($res==1) return 1;
            else return -1;
        }
        catch(PDOException $Exception ){
            return -2;
        }

    }

    public function cancelBp($param)
    {
        try
        {
            $id = base64_decode($param['rowid']);
            $etat = base64_decode($param['etat']);
            $sql = "UPDATE abonnement_postales SET etat =?, user_modif=?, date_modif =? WHERE rowid =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $etat,
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

    public function  itineraires()
    {
        try
        {
            $sql = "Select i.rowid, CONCAT(i.lieu_depart,' ==> ',i.lieu_arrivee) as itineraire, i.date_trajet as date, 
                    CONCAT(i.heure_depart,' ==> ',i.heure_arrivee) as trajet, place_dispo, i.tarif
                from itineraire_pv i
                WHERE statut = 1 AND place_dispo > 0";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();

            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }
    }

    public function  voitures($param)
    {
        try
        {
            $sql = "Select v.rowid, CONCAT(v.marque,' -- ',v.modele,' -- ',v.matricule) as voiture  
                from transpost_voiture as v 
                WHERE v.rowid NOT IN ( select i.fk_voiture from itineraire_pv as i where ((i.heure_depart >= :heureDepart AND i.heure_depart <= :heureFin) OR (i.heure_arrivee >= :heureDepart AND i.heure_arrivee <= :heureFin)) AND date(i.date_trajet) = :date_voyage AND i.etat = :etat )";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("heureDepart" =>$param['heureDepart'],"heureFin" =>$param['heureFin'],"date_voyage" =>$param['date_voyage'],"etat" =>1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }

    }

    public function  voituresM($param)
    {
        try
        {
            $sql = "Select v.rowid, CONCAT(v.marque,' -- ',v.modele,' -- ',v.matricule) as voiture  
                from transpost_voiture as v 
                WHERE v.rowid NOT IN ( select i.fk_voiture from itineraire_pv as i where ((i.heure_depart >= :heureDepart AND i.heure_depart <= :heureFin) OR (i.heure_arrivee >= :heureDepart AND i.heure_arrivee <= :heureFin)) AND date(i.date_trajet) = :date_voyage AND v.nb_place = :reserve AND i.etat = :etat )";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("heureDepart" =>$param['heureDepart'],"heureFin" =>$param['heureFin'],"date_voyage" =>$param['date_voyage'],"reserve" =>$param['reserve'],"etat" =>1));
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnexion();
            return $a;
        }
        catch (PDOException $exception)
        {
            return -1;
        }


    }

    public function getPlaceByIdString($id){
        try{
            $sql = "Select *
                from transpost_voiture 
                WHERE rowid = :id";
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

    public function  allReservation($requestData = null)
    {
        try
        {
            $sql = "Select r.rowid, r.client, CONCAT(i.lieu_depart,' ==> ',i.lieu_arrivee) as itineraire, i.date_trajet as date, 
                    CONCAT(i.heure_depart,' ==> ',i.heure_arrivee) as trajet,
                    r.montant as montant 
                from reservation_pv as r
                LEFT OUTER JOIN itineraire_pv as i
                ON r.itineraire = i.rowid";
            if(!is_null($requestData)) {
                $sql.=" WHERE ( i.lieu_depart LIKE '%".$requestData."%' ";
                $sql.=" OR i.lieu_arrivee LIKE '%".$requestData."%' ";
                $sql.=" OR  i.date_trajet LIKE '%".$requestData."%' ";
                $sql.=" OR  i.heure_depart LIKE '%".$requestData."%' ";
                $sql.=" OR  i.heure_arrivee LIKE '%".$requestData."%' ";
                $sql.=" OR  r.client LIKE '%".$requestData."%' ";
                $sql.=" OR i.tarif LIKE '%".$requestData."%' )";
            }
            $tabCol = ['client','itineraire', 'date', 'trajet', 'montant'];
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

    public function  allReservationCount($requestData = null)
    {
        try
        {
            $sql = "Select COUNT(r.rowid) as total
                from reservation_pv as r
                LEFT OUTER JOIN itineraire_pv as i
                ON r.itineraire = i.rowid";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function insertReservation($param)
    {
        try
        {
            //var_dump($param);die;
            $montant = intval($param['montant']);
            $sql = "INSERT INTO reservation_pv(client, itineraire, nb_place, montant, user_crea, date_crea,tel_client,mail_client) 
                    VALUES (:client, :itineraire, :nb_place, :montant, :user_crea, :date_crea,:tel_client,:mail_client)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "client"=>$param['client'],
                "itineraire"=>$param['itineraire'],
                "nb_place"=>$param['nb_place'],
                "montant"=>$montant,
                "tel_client"=>$param['tel_client'],
                "mail_client"=>$param['mail_client'],
                "user_crea"=>$param['user_crea'],
                "date_crea"=>$param['date_crea']
            ));


            if($res==1) {
                $sql = "UPDATE itineraire_pv SET place_dispo = place_dispo - ".$param['nb_place']." WHERE rowid=?";
                $user = $this->getConnexion()->prepare($sql);
                $res = $user->execute(array(
                    $param['itineraire']
                ));

                $this->closeConnexion();

                $this->debiter_soldeAgence($montant, $param['fk_agence']);
                $this->crediterCarteParam(7,$montant);

                if($res==1) return 1;
                else return -1;
            }
            else return -1;
        }
        catch(PDOException $Exception )
        {
            var_dump($Exception);die;
            return -2;
        }

    }

    public function updateReservation($param)
    {
        try
        {
            //var_dump($param);die;
            $id = base64_decode($param['rowid']);
            $montant = intval($param['montant']);
            $montant_old = intval($param['montant_old']);
            $sql = "UPDATE reservation_pv SET client=?, itineraire=?, nb_place=?, montant=?, user_modif=?, date_modif=?, tel_client=?, mail_client=? WHERE rowid=?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                $param['client'],
                $param['itineraire'],
                $param['nb_place'],
                $montant,
                $param['user_crea'],
                $param['date_crea'],
                $param['tel_client'],
                $param['mail_client'],
                $id
            ));


            if($res==1) {

                    $sql = "UPDATE itineraire_pv SET place_dispo = place_dispo - ".$param['nb_place']." WHERE rowid=?";
                    $sql1 = "UPDATE itineraire_pv SET place_dispo = place_dispo + ".$param['old_place']." WHERE rowid='".$param['old_it']."' ";

                $user = $this->getConnexion()->prepare($sql);
                $res = $user->execute(array(
                    $param['itineraire']
                ));

                if(isset($sql1))
                {
                    $user = $this->getConnexion()->prepare($sql1);
                    $res = $user->execute();
                }

                $this->closeConnexion();

                $this->crediter_soldeAgence($montant_old, $param['fk_agence']);
                $this->debiterCarteParam(7,$montant_old);

                $this->debiter_soldeAgence($montant, $param['fk_agence']);
                $this->crediterCarteParam(7,$montant);

                if($res==1) return 1;
                else return -1;
            }
            else return -1;
        }
        catch(PDOException $Exception )
        {
            var_dump($Exception);die;
            return -2;
        }

    }

    public function getReservationByIdString($id){
        try{
            $sql = "Select r.*, CONCAT(i.lieu_depart,' ==> ',i.lieu_arrivee) as itineraire, i.date_trajet as date, 
                    CONCAT(i.heure_depart,' ==> ',i.heure_arrivee) as trajet, i.tarif, i.rowid as itnier, i.place_dispo
                from reservation_pv as r
                LEFT OUTER JOIN itineraire_pv as i
                ON r.itineraire = i.rowid
                WHERE r.rowid=:id";
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

    /************AllBénéficiaire************/
    public function  allBenef(){
        $sql = "SELECT id, nom_complet FROM beneficiaire_postale";
        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        }
        catch(Exception $e)
        {
            return -2;
        }

    }

    /************ All Periodicité ************/
    public function allperiodicite()
    {
        try {
            $sql = "SELECT rowid,label FROM periodicite_postale WHERE etat =:etat";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1));
            $a = $user->fetchAll();
            $this->closeConnexion();
            return $a;
        } catch (Exception $e) {
            return -2;
        }
    }

    /************ All Boite Postale ************/
    public function getBP($fk_beneficiaire_postale)
    {
        try
        {
            $sql = "SELECT numero FROM boite_postale WHERE etat =:etat AND fk_beneficiaire_postale =:fk_beneficiaire_postale";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("etat" => 1, "fk_beneficiaire_postale" => $fk_beneficiaire_postale));
            $a = $user->fetchObject();
            $rows = $user->rowCount();
            if($rows > 0) return $a;
            else return -1;
        }
        catch (PDOException $e)
        {
            return -2;
        }
    }

    /************ Montant offre ************/
    public function getMntOffModel($id)
    {

            $sql = "SELECT montant FROM offres_postales WHERE rowid =".$id;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetch();
            $this->closeConnexion();
            return $a;
    }

    /************  Nombre de mois ************/
    public function getNbreMoisModel($id)
    {
            $sql = "SELECT nombre_mois FROM periodicite_postale WHERE rowid =".$id;
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetch();
            $this->closeConnexion();
            return $a;
    }

    /************  ID Boite Postale ************/
    public function getIdbpModel($num)
    {
            $sql = "SELECT id FROM boite_postale WHERE numero =:num";

            $user = $this->getConnexion()->prepare($sql);
            $user->execute( array(

                    "num" => strval($num),
                )
            );
            //var_dump($sql);exit;
            $a = $user->fetch();
            $this->closeConnexion();
            return $a;
    }

    public function  allItineraires($requestData = null)
    {
        try{
            $sql = "Select rowid, CONCAT(lieu_depart,' ==> ',lieu_arrivee) as itineraire, date_trajet as date, CONCAT(heure_depart,' ==> ',heure_arrivee) as trajet,
                    heure_convocation, place, tarif as montant 
                    from itineraire_pv";
            if(!is_null($requestData)){
                $sql.=" WHERE ( lieu_depart LIKE '%".$requestData."%' ";
                $sql.=" OR lieu_arrivee LIKE  '%".$requestData."%' ";
                $sql.=" OR date_trajet LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_depart LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_arrivee LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_convocation LIKE  '%".$requestData."%' ";
                $sql.=" OR place LIKE  '%".$requestData."%' ";
                $sql.=" OR tarif LIKE  '%".$requestData."%' )";
            }
            $tabCol = ['itineraire', 'trajet', 'heure_convocation', 'place', 'place_dispo', 'montant'];
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

    public function  allItinerairesCount($requestData = null)
    {
        try {
            $sql = "Select COUNT(rowid) as total from itineraire_pv";
            if(!is_null($requestData)){
                $sql.=" WHERE ( lieu_depart LIKE '%".$requestData."%' ";
                $sql.=" OR lieu_arrivee LIKE  '%".$requestData."%' ";
                $sql.=" OR date_trajet LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_depart LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_arrivee LIKE  '%".$requestData."%' ";
                $sql.=" OR heure_convocation LIKE  '%".$requestData."%' ";
                $sql.=" OR place LIKE  '%".$requestData."%' ";
                $sql.=" OR tarif LIKE  '%".$requestData."%' )";
            }
            $tabCol = ['itineraire', 'trajet', 'heure_convocation', 'place', 'place_dispo', 'montant'];
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

    public function insertItineraire($param)
    {
        try
        {
            $sql = "INSERT INTO itineraire_pv(lieu_depart, lieu_arrivee, date_trajet, heure_depart, heure_arrivee, heure_convocation, place, place_dispo, tarif,fk_voiture, user_crea, date_crea) 
                    VALUES (:lieu_depart, :lieu_arrivee, :date_trajet, :heure_depart, :heure_arrivee, :heure_convocation, :place, :place_dispo, :tarif,:fk_voiture, :user_crea, :date_crea)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "lieu_depart"=>$param['lieu_depart'],
                "lieu_arrivee"=>$param['lieu_arrivee'],
                "date_trajet"=>$param['from'],
                "heure_depart"=>$param['timepicker1'],
                "heure_arrivee"=>$param['timepicker2'],
                "heure_convocation"=>$param['timepicker3'],
                "place"=>$param['place'],
                "place_dispo"=>$param['place'],
                "tarif"=>$param['montant'],
                "fk_voiture"=>$param['fk_voiture'],
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

    public function getItineraireByIdString($id){
        try{
            $sql = "Select i.*, CONCAT(v.marque,' -- ',v.modele,' -- ',v.matricule) as voiture
                from itineraire_pv as i
                LEFT OUTER JOIN transpost_voiture as v
                ON i.fk_voiture = v.rowid
                WHERE i.rowid = :id";
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

    public function updateItineraire($param)
    {
        try
        {   $dispo=$param['place']-$param['reserve'];
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE itineraire_pv SET lieu_depart=?,lieu_arrivee=?, date_trajet=?, heure_depart=?,heure_arrivee=?,
                    heure_convocation=?, place=?, place_dispo=?, tarif=?, fk_voiture=?, user_modif=?, date_modif =? WHERE rowid =?";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute([
                $param['lieu_depart'],
                $param['lieu_arrivee'],
                $param['from'],
                $param['timepicker1'],
                $param['timepicker2'],
                $param['timepicker3'],
                $param['place'],
                $dispo,
                $param['montant'],
                $param['fk_voiture'],
                $param['user_modif'],
                $param['date_modif'],
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

    public function updateItineraireState($param)
    {
        try
        {
            //var_dump($param);die;
            $id = base64_decode($param['rowid']);
            $sql = "UPDATE itineraire_pv SET statut=?, user_modif=?, date_modif=? WHERE rowid=?";
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