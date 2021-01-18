<?php

/**
 * Created by IntelliJ IDEA.
 * User: AL MOURCHID
 * Date: 20/08/2017
 * Time: 09:11
 */


class ReportingController extends \app\core\BaseController
{
    private $utilisateurModel;
    private $reportingModel;
    private $userConnecter;
    public $error;

    public function __construct()
    {
        parent::__construct();
        $this->utilisateurModel = $this->model('UtilisateurModel');
        $this->reportingModel = $this->model('ReportingModel');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];

    }
    

    public function index()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 3));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/accueil');
        $this->view($params,$data);
    }


    public function reportingdujour()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(32,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/index');
        $this->view($params,$data);
    }

    public function reportingdujourdebug()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(32,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/indexdebug');
        $this->view($params,$data);
    }

    
    /***************** transaction du jour *********************/
    public function processingUser($id)
    {
        $requestData = $_REQUEST;
        $columns = array(
            0 =>'date_transaction',
            1 => 'num_transac',
            2 => 'fk_carte',
            3 => 'label',
            4 => 'montant',
            5 => 'commission',
            6 => 'montant',
            7 => 'statut',
            8 => 'prenom',
            9 => 'label',
            10 => 'rowid',
        );
        
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $next = '';
        $where = '';
            
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        if($this->userConnecter->admin == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

        // getting total number records without any search
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction,  service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service = service.rowid 
           
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction,  service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
           
            AND transaction.fk_service = service.rowid 
           
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where;

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            //$sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) 
        {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';
            
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] =$row["num_transac"];
            //$nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];


            /*$nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);*/


            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($commission)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant_ttc)."</p>";


            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href=".WEBROOT."reporting/detailTransac/".base64_encode($row["num_transac"])."><i class='fa fa-search'></i></a>";
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"=> intval( $requestData['draw'] ),   
            "recordsTotal"=> intval( $totalData ),  // total number of records
            "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"=> $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }


    /***************** transaction du jour *********************/
    public function processingByDateAndCompte($id)
    {

        $requestData = $_REQUEST;
        $columns = array(
            0 =>'date_transaction',
            1 => 'num_transac',
            //2 => 'fk_carte',
            3 => 'label',
            4 => 'montant',
            5 => 'commission',
            6 => 'montant',
            7 => 'statut',
            8 => 'prenom',
            9 => 'label',
            10 => 'rowid',
        );

        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $carte = $this->utils->securite_xss($id[2]);
        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($carte != '') {
            $where = "  AND carte.telephone ='00" . $carte . "'";
            if ($this->userConnecter->admin == 1 || $this->userConnecter->type_profil == 1) {

                $next.="INNER JOIN region ON agence.province = region.idregion";
            } else if ($this->userConnecter->type_profil == 2) {

                $next.="INNER JOIN region ON agence.province = region.idregion AND transaction.fk_agence=" . $this->userConnecter->fk_agence;
            } else if ($this->userConnecter->type_profil == 4) {
                $where .= " AND transaction.fk_agence=" . $this->userConnecter->fk_agence;
            } else if ($this->userConnecter->type_profil == 3) {
                $where .= " AND transaction.fkuser=" . $this->userConnecter->rowid;
            }
        }else{
            if ($this->userConnecter->admin == 1 || $this->userConnecter->type_profil == 1) {

                $next.="INNER JOIN region ON agence.province = region.idregion";
            } else if ($this->userConnecter->type_profil == 2) {

                $next.="INNER JOIN region ON agence.province = region.idregion AND transaction.fk_agence=" . $this->userConnecter->fk_agence;

            } else if ($this->userConnecter->type_profil == 4) {
                $where .= " AND transaction.fk_agence=" . $this->userConnecter->fk_agence;
            } else if ($this->userConnecter->type_profil == 3) {
                $where .= " AND transaction.fkuser=" . $this->userConnecter->rowid;
            }
        }
            $sql = "SELECT DISTINCT (transaction.rowid), transaction.num_transac,  transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction 
            LEFT JOIN carte ON transaction.fk_carte = carte.rowid
            INNER JOIN service ON transaction.fk_service = service.rowid
            INNER JOIN user ON transaction.fkuser = user.rowid
            INNER JOIN agence ON transaction.fk_agence = agence.rowid
            ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND  transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
           
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            ".$where;


        if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' )";
            //$sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%'

        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT DISTINCT (transaction.rowid), transaction.num_transac, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction 
            LEFT JOIN carte ON transaction.fk_carte = carte.rowid
            INNER JOIN service ON transaction.fk_service = service.rowid
            INNER JOIN user ON transaction.fkuser = user.rowid
            INNER JOIN agence ON transaction.fk_agence = agence.rowid
            ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND  transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
           
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            ".$where;

        if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' )";
            //$sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();


        $data = array();
        foreach( $rows as $row)
        {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] =$row["num_transac"];
            //$nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];


            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($commission)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant_ttc)."</p>";


            /*$nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc)*/;




            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href=".WEBROOT."reporting/detailTransac/".base64_encode($row["num_transac"])."><i class='fa fa-search'></i></a>";
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"=> intval( $requestData['draw'] ),
            "recordsTotal"=> intval( $totalData ),  // total number of records
            "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"=> $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    /***************** transaction du jour *********************/
    public function processingUserTest($id)
    {

        $requestData = $_REQUEST;
        $columns = array(
            0 =>'date_transaction',
            1 => 'num_transac',
            2 => 'fk_carte',
            3 => 'label',
            4 => 'montant',
            5 => 'commission',
            6 => 'montant',
            7 => 'statut',
            8 => 'prenom',
            9 => 'label',
            10 => 'rowid',
        );

        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }


        // getting total number records without any search
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where;

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' )";
            $sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row)
        {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] =$row["num_transac"];
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);
            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href=".WEBROOT."reporting/detailTransac/".base64_encode($row["num_transac"])."><i class='fa fa-search'></i></a>";
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"=> intval( $requestData['draw'] ),
            "recordsTotal"=> intval( $totalData ),  // total number of records
            "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"=> $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }


    /***************** transaction detail *********************/
    public function detailTransac($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $num_transac = base64_decode($this->utils->securite_xss($id[0])); 
        $data['detail'] = $this->reportingModel->detailTransasction($num_transac);
        $params = array('view' => 'reporting/detailstransac');
        $this->view($params,$data);
    }

    /***************** recu duplicata *********************/
    public function factureduplicata($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $num_transac = $this->utils->securite_xss($_POST['num_transac']);
        $data['recu'] = $this->reportingModel->dupliquerRecu($num_transac);
        $params = array('view' => 'reporting/duplicata');
        $this->view($params,$data);
    }

    /************************recu jour******************************/
    public function  transactionJour($date1, $date2)
    {
            $next = '';
            $where = '';

            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac,  transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where."";
            
            try
            {
                $user = $this->getConnexion()->prepare($sql);
                $user->bindParam("date1",  $date1 );
                $user->bindParam("date2",  $date2 );
                $user->execute();
                $rows = $user->fetchAll();
                $totalData = $user->rowCount(); 
                return $rows;
            }
            catch(Exception $e)
            {
                  echo 'Error: -99'; die;
            }
    }

    /***************** recu jour *********************/
    public function facturejour()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date1'], $_POST['date2']);

        $data['recu'] = $this->transactionJour($date1, $date2);
        $params = array('view' => 'reporting/facturerecuJour');
        $this->view($params,$data);
    }

    public function facturejourExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $rows = $this->transactionJour($date1, $date2);
        $total=0;
        $totalttc=0;
        $nb=0;
        $com=0;


        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=TransactionDuJour.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";
        

        echo "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0'>
              <tr align='center' valign='top'>
                <td width='11%'><strong>". $langs['date']."</strong></td>
                <td width='9%'><strong>". $langs['numero']."</strong></td>
                <td width='11%'><strong>".$langs['produit']."</strong></td>
                <td width='11%'><strong>".$langs['montant_sans_ttc']."</strong></td>
                <td width='11%'><strong>".$langs['frais']."</strong></td>
                <td width='11%'><strong>".$langs['montant_ttc']."</strong></td>
                <td width='11%'><strong>".$langs['effectuer_par']."</strong></td>
                <td width='12%'><strong>".$langs['agence']."</strong></td>
            </tr>";


            foreach($rows as $row_transact)
            {
                $montant_ttc=$row_transact['montant']+$row_transact['commission'];

                echo "<tr align='center' valign='middle'>
                    <td>".$this->utils->date_fr4($row_transact['date_transaction'])."</td>
                    <td>". $row_transact['num_transac']."</td>
                    <td align='left'>". $row_transact['label']."</td>
                    <td align='right'>". $this->utils->number_format($row_transact['montant'])."</td>
                    <td align='right'>". $this->utils->number_format($row_transact['commission'])."</td>
                    <td align='right'>". $this->utils->number_format($montant_ttc)."</td>
                    <td align='left'>". $row_transact['prenom']." ".$row_transact['nom']."</td>
                    <td align='left'>". $row_transact['nom_agence']."</td>
                </tr>";

                $total+= $row_transact['montant'];
                $nb+= 1;
                $com+= $row_transact['commission'];
                $totalttc+= $montant_ttc;
            }
            
            echo "</table>
            <br/>
            <table width='40%' border='1' align='center' cellpadding='10' cellspacing='2' class='table_form'>
                <tr class='txt_form1'>
                    <td width='20%' align='center' valign='top'>Montant Total </td>
                    <td width='21%' align='center' valign='top'>Total Commission </td>
                    <td width='24%' align='center' valign='top'>Montant Total TTC </td>
                    <td width='29%' align='center' valign='top'>Nombre de Transactions </td>
                </tr>
                <tr>
                    <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($total)."</strong></td>
                    <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($com)."</strong></td>
                    <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($totalttc)."</strong></td>
                    <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $nb."</strong></td>
                </tr>
            </table>";



    }

    /***************** reporting date search *********************/
    public function reportingsearchdate()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(33,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/reporting');
        $this->view($params,$data);
    }

    /***************** reporting date *********************/
    public function reportingdate()
    {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $numserie = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['numserie'])));
        $data['datedeb'] = $date1;
        $data['datefin'] = $date2;
        $data['numserie'] = $numserie;
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        $data['reporting'] = $this->reportingModel->transactionByDateAndOrUmSerie($date1, $date2, $numserie, $this->userConnecter->rowid, $this->userConnecter->fk_agence, $type_profil, $this->userConnecter->admin);
        //$data['reporting'] = $this->reportingModel->transactionDate($date1, $date2, $numserie);
       //echo "<pre>"; var_dump($data['reporting']); die;
        $params = array('view' => 'reporting/reportingdate');
        $this->view($params,$data);
    }

     /***************** transaction du jour *********************/
    public function processingdate($id)
    {
        // storing  request (ie, get/post) global array to a variable  
        $requestData= $_REQUEST;


        $columns = array( 
        // datatable column index  => database column name
                0 =>'date_transaction',
                1 => 'num_transac',
                2 => 'fk_carte',
                3 => 'label',
                4 => 'montant',
                5 => 'commission',
                6 => 'montant',
                7 => 'statut',
                8 => 'prenom',
                9 => 'label',
                10 => 'rowid',
        );

            //getting total number records without any search
            $date1 = $this->utils->securite_xss($id[0]);
            $date2 = $this->utils->securite_xss($id[1]);
            $numserie = $this->utils->securite_xss($id[2]);


            $next = '';
            $where = '';
                
            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

        if($numserie != ''){
                $where="  AND carte.telephone =:carte";
            }
            try {

                $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence " . $next . "
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid " . $where;

                $user = $this->getConnexion()->prepare($sql);
                $user->bindParam("date1", $date1);
                $user->bindParam("date2", $date2);
                if ($numserie != '') $user->bindParam("carte", $numserie);
                $user->execute();
                $rows = $user->fetchAll();
                $totalData = $user->rowCount();
                $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

                $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence " . $next . "
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid " . $where;


                if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " AND ( transaction.num_transac LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR transaction.montant LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR service.label LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR transaction.date_transaction LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR user.prenom LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR user.nom LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR agence.label LIKE '%" . $requestData['search']['value'] . "%' ";
                    $sql .= " OR service.label LIKE '%" . $requestData['search']['value'] . "%' )";
                    $sql .= " OR carte.telephone LIKE '%" . $requestData['search']['value'] . "%' )";

                }

                echo $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'] . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";

                $user = $this->getConnexion()->prepare($sql);
                $user->bindParam("date1", $date1);
                $user->bindParam("date2", $date2);
                if ($numserie != '') $user->bindParam("carte", $numserie);
                $user->execute();
                $rows = $user->fetchAll();
            }
            catch (PDOException $e){
                echo -99;
            }
        //var_dump($rows);
        $data = array();
        foreach( $rows as $row) {  //preparing an array

            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';
            
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];
            /*$nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);*/


            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($commission)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant_ttc)."</p>";




            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href='detailTransact/".base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";
            
            $data[] = $nestedData;
        }


        $json_data = array(
                    "draw"=> intval( $requestData['draw'] ),   
                    "recordsTotal"=> intval( $totalData ),  // total number of records
                    "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"=> $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }

    public function  transactionDate($date1, $date2, $carte)
    {
            $next = '';
            $where = '';
                
            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

        if($carte != ''){
                $where="  AND carte.telephone ='".$carte."'";
            }
            
            $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction, service, user, agence, carte ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.fk_carte = carte.rowid
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where;

            
            try
            {
                $user = $this->getConnexion()->prepare($sql);
                $user->execute();
                $rows = $user->fetchAll();
                $totalData = $user->rowCount(); 
                return $rows;
            }
            catch(Exception $e)
            {
                  echo 'Error: -99'; die;
            }
            
    }

     /***************** transaction detail *********************/
    public function detailTransact($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $num_transac = (int)base64_decode($this->utils->securite_xss($id[0]));
        $data['detail'] = $this->reportingModel->detailTransasction($num_transac);
        $params = array('view' => 'reporting/detailstransact');
        $this->view($params,$data);
    }

    /***************** recu jour *********************/
    public function facturedate()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $carte = $this->utils->securite_xss($_POST['numserie']);
        if($carte[0] === '+')
            $carte = str_replace('+', '00', $carte);
        $data['date'] = $this->utils->securite_xss($_GET['date2']);
        $data['recu'] = $this->transactionDate($date1, $date2, $carte);
        
        $params = array('view' => 'reporting/facturerecuDate');
        $this->view($params,$data);
    }

    /***************** reporting produit search *********************/
    public function reportingsearchproduit()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(34,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['service'] = $this->reportingModel->allService();
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'reporting/searchproduit');
        $this->view($params,$data);
    }

    /***************** reporting date *********************/
    public function reportingproduit()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $datedeb = $this->utils->securite_xss($_POST['datedeb']);
        $datefin = $this->utils->securite_xss($_POST['datefin']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        $data['datedeb'] = $datedeb;
        $data['datefin'] = $datefin;
        $data['produit'] = $produit;
        $data['agence'] =  $agence;
        $data['reporting'] = $this->reportingModel->transactionByProduitDate($datedeb, $datefin, $produit, $this->userConnecter->rowid, $this->userConnecter->fk_agence, $type_profil, $agence, $this->userConnecter->admin);

        $params = array('view' => 'reporting/reportingproduit');
        $this->view($params,$data);
    }

     /***************** transaction du jour *********************/
    /*public function processingproduit($id)
    {
        // storing  request (ie, get/post) global array to a variable  
        $requestData= $_REQUEST;

        $columns = array( 
        // datatable column index  => database column name
                0 =>'date_transaction',
                1 => 'num_transac',
                2 => 'fk_carte',
                3 => 'label',
                4 => 'montant',
                5 => 'commission',
                6 => 'montant',
                7 => 'statut',
                8 => 'prenom',
                9 => 'label',
                10 => 'rowid',
        );

            //getting total number records without any search
            $date1 = $this->utils->securite_xss($id[0]);
            $date2 = $this->utils->securite_xss($id[1]);
            $produit = $this->utils->securite_xss($id[2]);
            $agency = $this->utils->securite_xss($id[3]);
            $next = '';
            $where = '';
                
            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
            if($this->userConnecter->admin == 1 || $type_profil==1){
                $next = '';
                $where = '';
            }
            else if($type_profil==2 || $type_profil==4)
            {
                $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
            }
            else if($type_profil==6)
            {
                $next.=", region";
                $where.=" agence.province = region.idregion";
            }
            else{
                $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
            }

            if($agency > 0)
            {
                $where="  AND transaction.fk_agence=:agence";
            }


            $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            
            FROM transaction, service, user, agence, carte ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service =:produit
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where."";
            
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->bindParam("produit",  $produit ); 
            if($agency > 0) $user->bindParam("agence",  $agency );      
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount(); 
            $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

            $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence,carte.telephone
            FROM transaction, service, user, agence,carte ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service =:produit
            AND transaction.fk_service != 6
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence = agence.rowid ".$where."";


        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->bindParam("produit",  $produit ); 
        if($agency > 0) $user->bindParam("agence",  $agency ); 
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  //preparing an array

            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';
            
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];

            $nestedData[] = $row["label"];
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($commission)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant_ttc)."</p>";
            $nestedData[] = "<p class='text-green'>".$statut."</p>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href='detailTransact/".base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";
            
            $data[] = $nestedData;
        }


        $json_data = array(
                    "draw"=> intval( $requestData['draw'] ),   
                    "recordsTotal"=> intval( $totalData ),  // total number of records
                    "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"=> $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }*/


    public function processingproduit($id)
    {
        // storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;

        $columns = array(
            // datatable column index  => database column name
            0 =>'date_transaction',
            1 => 'num_transac',
            2 => 'fk_carte',
            3 => 'label',
            4 => 'montant',
            5 => 'commission',
            6 => 'montant',
            7 => 'statut',
            8 => 'prenom',
            9 => 'label',
            10 => 'rowid',
        );

        //getting total number records without any search
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $produit = $this->utils->securite_xss($id[2]);
        $agency = $this->utils->securite_xss($id[3]);
        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=" INNER JOIN region ON agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

        if($agency > 0)
        {
            $where="  AND transaction.fk_agence=:agence";
        }


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
                transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
                FROM transaction
                INNER JOIN service ON transaction.fk_service = service.rowid
                INNER JOIN user ON transaction.fkuser = user.rowid
                INNER JOIN agence ON transaction.fk_agence = agence.rowid
                LEFT JOIN carte ON transaction.fk_carte = carte.rowid ".$next."
                WHERE transaction.statut = 1
                AND service.etat = 1 
                AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
                AND DATE(transaction.date_transaction) >=:date1 
                AND DATE(transaction.date_transaction) <=:date2 
                AND transaction.fk_service =:produit
                AND transaction.fk_service != 6
                AND transaction.fk_service != 0 ".$where."";

        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->bindParam("produit",  $produit );
        if($agency > 0) $user->bindParam("agence",  $agency );
        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, 
                transaction.statut, service.label, transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
                FROM transaction
                INNER JOIN service ON transaction.fk_service = service.rowid
                INNER JOIN user ON transaction.fkuser = user.rowid
                INNER JOIN agence ON transaction.fk_agence = agence.rowid
                LEFT JOIN carte ON transaction.fk_carte = carte.rowid ".$next."
                WHERE transaction.statut = 1
                AND service.etat = 1 
                AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
                AND DATE(transaction.date_transaction) >=:date1 
                AND DATE(transaction.date_transaction) <=:date2 
                AND transaction.fk_service =:produit
                AND transaction.fk_service != 6
                AND transaction.fk_service != 0 ".$where."";


        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->bindParam("produit",  $produit );
        if($agency > 0) $user->bindParam("agence",  $agency );
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  //preparing an array

            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $montant_ttc =  $montant + $commission;
            if($row["statut"]==1) $statut='succès';

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            /*$nestedData[] = $this->utils->truncate_carte($row["telephone"]);*/
            $nestedData[] = $row["label"];
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($commission)."</p>";
            $nestedData[] = "<p style='text-align: right !important;'>".$this->utils->number_format($montant_ttc)."</p>";
            $nestedData[] = "<p class='text-green'>".$statut."</p>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href='detailTransact/".base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }


        $json_data = array(
            "draw"=> intval( $requestData['draw'] ),
            "recordsTotal"=> intval( $totalData ),  // total number of records
            "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"=> $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format
    }

    
    /***************** facture produit *********************/
    public function  transactionProduit($date1, $date2, $agence, $produit)
    {
            $next = '';
            $where = '';
                
        $next = '';
        $where = '';
                
            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.="INNER JOIN region ON agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

        if($agence > 0){
                $where="  AND transaction.fk_agence =:agence";
            }

           $sql = "SELECT DISTINCT (transaction.rowid), transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction 
            LEFT JOIN carte ON transaction.fk_carte = carte.rowid
            INNER JOIN service ON transaction.fk_service = service.rowid
            INNER JOIN user ON transaction.fkuser = user.rowid
            INNER JOIN agence ON transaction.fk_agence = agence.rowid
            ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.fk_service =:service
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2
            ".$where;
            
            try
            {
                $user = $this->getConnexion()->prepare($sql);
                $user->bindParam("date1",  $date1 );
                $user->bindParam("date2",  $date2 );
                if($agence > 0) $user->bindParam("agence",  $agence); 
                $user->bindParam("service",  $produit); 
                $user->execute();
                $rows = $user->fetchAll();
                $totalData = $user->rowCount(); 
                return $rows;
            }
            catch(Exception $e)
            {
                 echo 'Error: -99'; die;
            }
    }

    /***************** recu jour *********************/
    public function factureproduit()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $data['date'] = $this->utils->securite_xss($_POST['date1'],$_POST['date2']);
        $data['recu'] = $this->transactionProduit($date1, $date2, $agence, $produit);
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        $data['recu'] = $this->reportingModel->transactionByProduitDate($date1, $date2, $produit, $this->userConnecter->rowid, $this->userConnecter->fk_agence, $type_profil, $agence, $this->userConnecter->admin);

        $params = array('view' => 'reporting/facturerecuProduit');
        $this->view($params,$data);
    }

    public function factureproduitExcel()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        $rows = $this->reportingModel->transactionByProduitDate($date1, $date2, $produit, $this->userConnecter->rowid, $this->userConnecter->fk_agence, $type_profil, $agence, $this->userConnecter->admin);

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=transactionProduit.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";

        echo
        "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0'>
          <tr align='center' valign='top'>
                <td width='11%'><strong>". $data['lang']['date']."</strong></td>
        <td width='9%'><strong>".$data['lang']['numero']."</strong></td>
        
        <td width='11%'><strong>".$data['lang']['produit']."</strong></td>
        <td width='11%'><strong>".$data['lang']['montant_sans_ttc']."</strong></td>
        <td width='11%'><strong>".$data['lang']['frais']."</strong></td>
        <td width='11%'><strong>".$data['lang']['montant_ttc']."</strong></td>
        <td width='11%'><strong>".$data['lang']['effectuer_par']."</strong></td>
        <td width='12%'><strong>".$data['lang']['agence']."</strong></td>
        </tr>";

        $total=0;
        $totalttc=0;
        $nb=0;
        $com=0;

        foreach($rows as $row_transact)
        {
            $montant_ttc=$row_transact->montant+$row_transact->commission;

            echo
            "<tr align='center' valign='middle'>
                <td>".$this->utils->date_fr4($row_transact->date_transaction)."</td>
                <td>".$row_transact->num_transac."</td>
                <td align='right'>". $row_transact->label."</td>
                <td align='right'>". $this->utils->number_format($row_transact->montant)."</td>
                <td align='right'>". $this->utils->number_format($row_transact->commission)."</td>
                <td align='right'>". $this->utils->number_format($montant_ttc)."</td>
                <td align='left'>".$row_transact->prenom.' '.$row_transact->nom."</td>
                <td align='left'>".$row_transact->nom_agence."</td>
            </tr>";
            $total+= $row_transact->montant;
            $nb+= 1;
            $com+= $row_transact->commission;
            $totalttc+= $montant_ttc;
        }

        echo
        "</table>
        <br/>
        <table width='40%' border='1' align='center' cellpadding='10' cellspacing='2' class='table_form'>
            <tr class='txt_form1'>
                <td width='20%' align='center' valign='top'>Montant Total </td>
                <td width='21%' align='center' valign='top'>Total Commission </td>
                <td width='24%' align='center' valign='top'>Montant Total TTC </td>
                <td width='29%' align='center' valign='top'>Nombre de Transactions </td>
            </tr>
            <tr>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($total)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($com)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($totalttc)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $nb."</strong></td>
            </tr>
        </table>";
    }

    /***************** reporting produit search *********************/
    public function searchcourbe()
    {        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(261,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['service'] = $this->reportingModel->allService();
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'reporting/searchcourbe');
        $this->view($params,$data);
    }

    /***************** recu jour *********************/
    public function courbeevolution()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $annee = $this->utils->securite_xss($_POST['annee']);
        $service = $this->utils->securite_xss($_POST['service']);
        $bureau = $this->utils->securite_xss($_POST['bureau']);
        $courbe = $this->utils->securite_xss($_POST['courbe']);
        $data['courbe'] = $this->utils->securite_xss($_POST['courbe']);
        $data['annee'] = $this->utils->securite_xss($_POST['annee']);
        $data['bureau'] = $this->utils->securite_xss($_POST['bureau']);
        $data['service'] = $this->reportingModel->allServicePar($service);
        $params = array('view' => 'reporting/evolution');
        $this->view($params,$data);
    }

    /***************** reporting detail rechargement search *********************/
    public function detailsearchrecharge()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(262,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/detailrechargementsearch');
        $this->view($params,$data);
    }

    /***************** reporting detail rechargement *********************/
    public function detailrechargement()
    {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $params = array('view' => 'reporting/detailrechargement');
        $this->view($params,$data);
    }

    /***************** transaction du jour *********************/
    public function processingdetailrecharge($id)
    {
        // storing  request (ie, get/post) global array to a variable  
        $requestData= $_REQUEST;

        $columns = array( 
        // datatable column index  => database column name
                0 =>'date_transaction',
                1 => 'num_transac',
                2 => 'fk_agence',
                3 => 'montant',
                4 => 'commission',
                5 => 'rowid',
                6 => 'fk_carte',
        );

            //getting total number records without any search
            $date1 = $this->utils->securite_xss($id[0]);
            $date2 = $this->utils->securite_xss($id[1]);
            $next = '';
            $where = '';
                
            $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $next = '';
            $where = '';
        }
        else if($type_profil==2 || $type_profil==4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($type_profil==6)
        {
            $next.=", region";
            $where.=" agence.province = region.idregion";
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }

            $sql = "SELECT DISTINCT t.num_transac, t.fk_carte, c.telephone, t.montant, t.commission, t.fk_agence, t.date_transaction, t.fk_service, a.label
            FROM transaction t 
            LEFT JOIN carte c ON t.fk_carte = c.rowid
            LEFT JOIN agence a ON t.fk_agence = a.rowid
            WHERE DATE(t.date_transaction) >=:date1
            AND DATE(t.date_transaction) <=:date2
            AND t.fk_service = 12
            AND t.statut = 1
            AND a.idtype_agence <> 3";
            
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount(); 
            $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

            $sql = "SELECT DISTINCT t.num_transac, t.fk_carte, c.telephone, t.montant, t.commission, t.fk_agence, t.date_transaction, t.fk_service, a.label
            FROM transaction t 
            LEFT JOIN carte c ON t.fk_carte = c.rowid
            LEFT JOIN agence a ON t.fk_agence = a.rowid
            WHERE DATE(t.date_transaction) >=:date1
            AND DATE(t.date_transaction) <=:date2
            AND t.fk_service = 12
            AND t.statut = 1
            AND a.idtype_agence <> 3";

            if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" AND ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.commission LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR c.telephone LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.date_transaction LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.fk_service LIKE '%".$requestData['search']['value']."%' )";
                
            }

        
        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];
            $commission =  $row["commission"];
            $benef = $this->utils->nomBeneficiareParCarteBis($row["fk_carte"]);
            
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $benef->prenom." ".$benef->prenom1." ".$benef->nom;
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            
            $data[] = $nestedData;
        }

        $json_data = array(
                    "draw"=> intval( $requestData['draw'] ),   
                    "recordsTotal"=> intval( $totalData ),  // total number of records
                    "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"=> $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }

    /***************** recu jour *********************/
    public function printdetaildrecharge()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->reportingModel->detailRechargementParDate($date1, $date2);
        $params = array('view' => 'reporting/printdetaildrecharge');
        $this->view($params,$data);
    }

    public function printdetaildrechargeExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);

        $rows = $this->reportingModel->detailRechargementParDate($date1, $date2);
        $total = 0;
        $total_com = 0;

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=DétailsRechargementParDate.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";

        echo "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0' style='font-size:12px'>
          <tr align='center' valign='top' >
          <th style='font-weight:bold'>". $langs['date']."</th>
            <th style='font-weight:bold'>". $langs['numero']."</th>
            <th style='font-weight:bold'>". $langs['agence']."</th>
            <th style='font-weight:bold'>". $langs['montant_sans_ttc']."</th>
            <th style='font-weight:bold'>". $langs['commission']."</th>
            <th style='font-weight:bold'>". $langs['nom_beneficiaire']."</th>
            <th style='font-weight:bold'>". $langs['carte_num']."</th>
        </tr>";
        foreach($rows as $row_transact)
        {
            $total = $total + $row_transact['montant'];
            $total_com = $total_com + $row_transact['commission'];
            $benef = $this->utils->nomBeneficiareParCarteBis($row_transact['fk_carte']);

            echo "<tr align='center' valign='middle'>
                <td>".$this->utils->date_fr4($row_transact['date_transaction'])."</td>
                <td>".$row_transact['num_transac']."</td>
                <td align='right'>". $row_transact['label']."</td>
                <td align='right'>". $this->utils->number_format($row_transact['montant'])."</td>
                <td align='right'>". $this->utils->number_format($row_transact['commission'])."</td>
                <td align='right'>". $benef->prenom." ".$benef->prenom1." ".$benef->nom."</td>
                <td align='right'>". $this->utils->truncate_carte($benef->telephone)."</td>
            </tr>";
        }
        echo "<tfoot>
            <tr style='font-weight: bold'>
                <td colspan='2' align='right'>TOTAL NET: </td>
                <td colspan='2' align='left'>".$this->utils->number_format($total)." F CFA </td>
                <td colspan='1' align='right'>TOTAL TTC: </td>
                <td colspan='2' align='left'>". $this->utils->number_format($total+$total_com)." F CFA </td>
            </tr>
            </tfoot>
            </table>";

    }

    /***************** reporting detail rechargement search *********************/
    public function detailsearchretrait()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(263,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/detailretraitsearch');
        $this->view($params,$data);
    }

    /***************** reporting detail retrait *********************/
    public function detailretrait()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $params = array('view' => 'reporting/detailretrait');
        $this->view($params,$data);
    }

    /***************** detail retrait *********************/
    public function processingdetailretrait($id)
    {
        // storing  request (ie, get/post) global array to a variable  
        $requestData= $_REQUEST;

        $columns = array( 
        // datatable column index  => database column name
                0 =>'date_receiver',
                1 => 'num_transac',
                2 => 'fk_agence',
                3 => 'montant',
                4 => 'rowid',
                5 => 'rowid',
        );

            //getting total number records without any search
            $date1 = $this->utils->securite_xss($id[0]);
            $date2 = $this->utils->securite_xss($id[1]);
            $next = '';
            $where = '';
                
            $sql = "SELECT t.num_transac, t.montant, t.date_tranfert, t.tel_sender, t.prenom_sender, t.nom_sender, t.prenom_receiver, t.nom_receiver, t.tel_receiver, t.date_receiver, t.user_receiver, a.label
            FROM tranfert t JOIN user u ON t.user_receiver = u.rowid JOIN agence a ON a.rowid = u.fk_agence
            WHERE DATE(t.date_receiver) >=:date1 AND DATE(t.date_receiver) <=:date2 AND t.statut = 1 AND t.fk_service IN (11,20)";
            
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount(); 
            $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

           $sql = "SELECT t.num_transac, t.montant, t.date_tranfert, t.tel_sender, t.prenom_sender, t.nom_sender, t.prenom_receiver, t.nom_receiver, t.tel_receiver, t.date_receiver, t.user_receiver, a.label
            FROM tranfert t JOIN user u ON t.user_receiver = u.rowid JOIN agence a ON a.rowid = u.fk_agence
            WHERE DATE(t.date_receiver) >=:date1 AND DATE(t.date_receiver) <=:date2 AND t.statut = 1 AND t.fk_service IN (11,20)";

            if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" AND ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.date_receiver LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.prenom_sender LIKE '%".$requestData['search']['value']."%' )";
                
            }

        
        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  //preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_receiver"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($row["montant"]);
            $nestedData[] = $row["prenom_sender"].' '.$row["nom_sender"];
            $nestedData[] = $row["prenom_receiver"].' '.$row["nom_receiver"];
            $data[] = $nestedData;
        }

        $json_data = array(
                    "draw"=> intval( $requestData['draw'] ),   
                    "recordsTotal"=> intval( $totalData ),  // total number of records
                    "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"=> $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }

    /***************** recu jour *********************/
    public function printdetaildretrait()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->reportingModel->detailRetraitParDate($date1, $date2);
        $params = array('view' => 'reporting/printdetaildretrait');
        $this->view($params,$data);
    }
    public function printdetaildretraitExcel()
    {
        $langs = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $rows = $this->reportingModel->detailRetraitParDate($date1, $date2);

        $total = 0;
        $total_com = 0;

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=DetailsRetraitTransfertParDate.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";//ENCODAGE UTF-8

        echo
        "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0' style='font-size:12px'>
          <tr align='center' valign='top' >
          <th style='font-weight:bold'>". $langs['date_paiement']."</th>
            <th style='font-weight:bold'>". $langs['numero_transaction']."</th>
            <th style='font-weight:bold'>". $langs['bureau_payeur']."</th>
            <th style='font-weight:bold'>". $langs['montant_sans_ttc']."</th>
            <th style='font-weight:bold'>". $langs['nom_envoyeur']."</th>
            <th style='font-weight:bold'>". $langs['nom_beneficiaire']."</th>
        </tr>";
        foreach($rows as $row_transact)
        {
            $total = $total + $row_transact['montant'];
            $total_com = $total_com + $row_transact['frais'];

            echo
            "<tr align='center' valign='middle'>
                <td>". $this->utils->date_fr4($row_transact['date_receiver'])."</td>
                <td>". $row_transact['num_transac']."</td>
                <td align='right'>".$row_transact['label']."</td>
                <td align='right'>".$this->utils->number_format($row_transact['montant'])."</td>
                <td align='right'>".$row_transact['prenom_sender'].' '.$row_transact['nom_sender']."</td>
                <td align='right'>".$row_transact["prenom_receiver"].' '.$row_transact["nom_receiver"]."</td>
            </tr>";
            }
        echo "<tfoot>
        <tr style='font-weight: bold'>
            <td colspan='2' align='right'>TOTAL NET: </td>
            <td colspan='1' align='left'>". $this->utils->number_format($total)." ".$langs['currency']."</td>
            <td colspan='2' align='right'>TOTAL TTC: </td>
            <td colspan='1' align='left'>". $this->utils->number_format($total+$total_com)." ".$langs['currency']."</td>
        </tr>
        </tfoot>
        </table>";


    }

    /***************** reporting detail titulaire search *********************/
    public function detailsearchtitulaire()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(264,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'reporting/detailtitulairesearch');
        $this->view($params,$data);
    }

    /***************** reporting detail titulaire *********************/
    public function detailtitulaire()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $params = array('view' => 'reporting/detailtitulaire');
        $this->view($params,$data);
    }

    /***************** detail titulaire *********************/
    public function processingdetailtitulaire($id)
    {
        // storing  request (ie, get/post) global array to a variable  
        $requestData= $_REQUEST;

        $columns = array( 
        // datatable column index  => database column name
                0 =>'date_transaction',
                1 => 'num_transac',
                2 => 'fk_agence',
                3 => 'montant',
                4 => 'rowid',
        );

            //getting total number records without any search
            $date1 = $this->utils->securite_xss($id[0]);
            $date2 = $this->utils->securite_xss($id[1]);

            $next = '';
            $where = '';
                
            $sql = "SELECT t.num_transac, t.montant, t.date_transaction, a.label, t.fk_carte 
            FROM transaction t 
            JOIN agence a ON t.fk_agence = a.rowid 
            JOIN carte c ON t.fk_carte = c.rowid
            WHERE t.fk_service IN (10,17) AND t.statut = 1 AND DATE(t.date_transaction) >=:date1 AND DATE(t.date_transaction) <=:date2";
            
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount(); 
            $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

           $sql = "SELECT t.num_transac, t.montant, t.date_transaction, a.label, t.fk_carte 
                  FROM transaction t 
                  JOIN agence a ON t.fk_agence = a.rowid 
                  JOIN carte c ON t.fk_carte = c.rowid
                  WHERE t.fk_service IN (10,17) AND t.statut = 1 AND DATE(t.date_transaction) >=:date1 AND DATE(t.date_transaction) <=:date2";

            if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" AND ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.date_transaction LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.fk_carte LIKE '%".$requestData['search']['value']."%' )";
                
            }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1",  $date1 );
        $user->bindParam("date2",  $date2 );
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  //preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($row["montant"]);
            $nestedData[] = $this->utils->nomBeneficiareParCarte($row["fk_carte"]);
            $data[] = $nestedData;
        }

        $json_data = array(
                    "draw"=> intval( $requestData['draw'] ),   
                    "recordsTotal"=> intval( $totalData ),  // total number of records
                    "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"=> $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }

    /***************** recu jour *********************/
    public function printdetaildtitulaire()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->reportingModel->detailRetraitTitulaireParDate($date1, $date2);
        $params = array('view' => 'reporting/printdetaildtitulaire');
        $this->view($params,$data);
    }

    public function printdetaildTitulaireExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $total = 0;

        $rows = $this->reportingModel->detailRetraitTitulaireParDate($date1, $date2);

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=DetailsRetraitTitulaire.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";

        echo
        "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0' style='font-size:12px'>
              <tr align='center' valign='top' >
                    
                      <th style='font-weight:bold'>". $langs['date_paiement']."</th>
            <th style='font-weight:bold'>". $langs['numero_transaction']."</th>
            <th style='font-weight:bold'>". $langs['bureau_payeur']."</th>
            <th style='font-weight:bold'>". $langs['montant_sans_ttc']."</th>
            <th style='font-weight:bold'>". $langs['nom_beneficiaire']."</th>
            </tr>";

            foreach($rows as $row_transact)
            {
                $total = $total + $row_transact['montant'];
                echo
                "<tr align='center' valign='middle'>
                    <td>".$this->utils->date_fr4($row_transact['date_transaction'])."</td>
                    <td>".$row_transact['num_transac']."</td>
                    <td align='right'>".$row_transact['label']."</td>
                    <td align='right'>".$this->utils->number_format($row_transact['montant'])."</td>
                    <td align='right'>".$this->utils->nomBeneficiareParCarte($row_transact["fk_carte"])."</td>
                </tr>";
            }
            echo
            "<tfoot>
            <tr style=''font-weight: bold'>
                <td colspan='3' align='right'>TOTAL TTC: </td>
                <td colspan='2' align='left'>". $this->utils->number_format($total)." ".$langs['currency']." </td>
            </tr>
            </tfoot>
            </table>";

    }

    /***************** reporting produit search *********************/
    public function bordereausearch()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(265,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'reporting/bordereausearch');
        $this->view($params,$data);
    }

    /***************** bordereau de rechargement*********************/
    public function bordereaurecharge()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        if($date1 != '' && $date2 != '' && $agence > 0)
        {
            $data['date1'] = $this->utils->securite_xss($_POST['datedeb']);
            $data['date2'] = $this->utils->securite_xss($_POST['datefin']);
            $data['recu'] = $this->reportingModel->bordereauRechargement($date1, $date2, $agence);
            $params = array('view' => 'reporting/bordereaurechargement');
            $this->view($params,$data);
        }
        else
        {
            $data['recu'] = 0;
            $params = array('view' => 'reporting/bordereaurechargement');
            $this->view($params,$data);
        }   
        
    }

    /***************** recu jour *********************/
    public function printbordereaurecharge()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->reportingModel->bordereauRechargement($date1, $date2, $agence);
        $params = array('view' => 'reporting/printbordereaurecharge');
        $this->view($params,$data);
    }

    public function printbordereaurechargeExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);

        $rows = $this->reportingModel->bordereauRechargement($date1, $date2, $agence);
        $nombre_total = 0;
        $montant_total = 0;
        $commission_total = 0;
        $annulation_total = 0;

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=bordereauRechargement.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";

        echo
        "<table width='642' align='center' cellpadding='10' cellspacing='0' border='1' style='font-size:12px'>
              <thead>
                <tr>
                  <td width='19%' rowspan='2' align='center'  valign='middle' nowrap='nowrap' class='txt_form1'><strong>".$langs['date_transac']."</strong></td>
        <td colspan='3' align='center'  valign='top' nowrap='nowrap'><strong>". $langs['RECHARGEMENT']."</strong></td>
        <td width='25%' align='center'  valign='top' nowrap='nowrap'><strong>". $langs['ANNULATION']."</strong></td>
        </tr>
        <tr>
            <td width='11%' align='center'  valign='top' nowrap='nowrap'><strong>". $langs['Nombre_de_transactions']."</strong></td>
            <td width='23%' align='right'  valign='top' nowrap='nowrap'><strong>".$langs['Montant_Recharge_(Ar)']."</strong></td>
            <td width='22%' align='right'  valign='top' nowrap='nowrap'><strong>".$langs['commission_transac']."</strong></td>
            <td align='center'  valign='top' nowrap='nowrap'><strong>". $langs['(Montant)']."</strong></td>
        </tr>
        </thead>
        <tbody>";


        foreach($rows as $row_rs_resultat)
        {
            $date_transaction = $row_rs_resultat['datet'];
            $nombre = $row_rs_resultat['nombre'];
            $montant = $row_rs_resultat['montant'];
            $commission = $row_rs_resultat['commission'];
            $annulation = 0;

            $nombre_total+= $nombre;
            $montant_total+=$montant;
            $commission_total+= $commission;

            echo
            "<tr>
                <td align='center' valign='middle' class='textNormal'>".$this->utils->date_fr2($date_transaction)."</td>
                <td align='center' valign='middle' class='textNormal'>".$this->utils->number_format($nombre)."</td>
                <td align='right' valign='middle'  class='textNormal'>".$this->utils->number_format($montant)."</td>
                <td align='right' valign='middle'  class='textNormal'>".$this->utils->number_format($commission)."</td>
                <td align='center' valign='middle' class='textNormal'>".$annulation."</td>
            </tr>";
        }

        echo
        "</tbody>
        <tfoot>
        <tr>
            <td  align='right'  valign='middle' nowrap='nowrap'><strong>".$langs['TOTAL']."  : </strong></td>
            <td  align='center' valign='middle' nowrap='nowrap'><strong>".$this->utils->number_format($nombre_total)."</strong></td>
            <td  align='right'  valign='middle' nowrap='nowrap'><strong>".$this->utils->number_format($montant_total)."</strong></td>
            <td  align='right'  valign='middle' nowrap='nowrap'><strong>".$this->utils->number_format($commission_total)."</strong></td>
            <td  align='center' valign='middle' nowrap='nowrap'><strong>".$annulation_total."</strong></td>
        </tr>
        </tfoot>
        </table>";

    }

    /***************** reporting produit search *********************/
    public function bordereauretraitsearch()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(266,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'reporting/bordereauretraitsearch');
        $this->view($params,$data);
    }

    /***************** bordereau de retrait*********************/
    public function bordereauretrait()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        if($date1 != '' && $date2 != '' && $agence > 0)
        {
            $data['date1'] = $this->utils->securite_xss($_POST['datedeb']);
            $data['date2'] = $this->utils->securite_xss($_POST['datefin']);
            $data['agence'] = $this->utils->securite_xss($_POST['agence']);
            $data['dates'] = $this->reportingModel->getDatesBetween($date1, $date2);
            $data['transact'] = sizeof($date1);
            $params = array('view' => 'reporting/bordereauretrait');
            $this->view($params,$data);
        }
        else
        {
            $data['recu'] = 0;
            $params = array('view' => 'reporting/bordereauretrait');
            $this->view($params,$data);
        }   
        
    }

    /***************** recu jour *********************/
    public function printbordereauretrait()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['agence'] = $this->utils->securite_xss($_POST['agence']);
        $data['dates'] = $this->reportingModel->getDatesBetween($date1, $date2);
        $data['transact'] = sizeof($date1);
        $params = array('view' => 'reporting/printbordereauretrait');
        $this->view($params,$data);
    }
    public function printbordereauretraitExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $dates = $this->reportingModel->getDatesBetween($date1, $date2);

        $nombre_retrait_total = 0;
        $montant_retrait_total = 0;
        $montant_total = 0;
        $nombre_total = 0;
        $commision_total = 0;
        //var_dump($_POST);die();


        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=bordereauRetrait.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";//ENCODAGE UTF-8

        //DEB
        echo "<table width='80%' border='1' align='center' cellpadding='5' cellspacing='0' style='font-size:16px'>
            <tr>
                <td width='14%' rowspan='2' align='center' valign='middle'><strong>".$langs['date']."</strong></td>
                <td colspan='3' align='center' valign='middle'><strong>". $langs['RETRAIT_TIERS']."</strong></td>
                <td colspan='2' align='center' valign='middle' nowrap='nowrap' bgcolor='#CCCCCC'><strong>". $langs['RETRAIT_TITULAIRE(CASHOUT)']."</strong></td>
                <td width='23%' align='center' valign='middle'><strong>".$langs['ANNULATION']."</strong></td>
            </tr>
            <tr>
                <td width='8%' align='center'  valign='top' nowrap='nowrap'><strong>".$langs['Nombre']."</strong></td>
                <td width='14%' align='right'  valign='top' nowrap='nowrap'><strong>". $langs['Montant_(FCFA)']."</strong></td>
                <td width='17%' align='right'  valign='top' nowrap='nowrap'><strong>". $langs['Commission_(FCFA)']."</strong></td>
                <td width='8%' align='center'  valign='top' nowrap='nowrap' bgcolor='#CCCCCC'><strong>". $langs['Nombre']."</strong></td>
                <td width='16%' align='right'  valign='top' nowrap='nowrap' bgcolor='#CCCCCC'><strong>". $langs['Montant_(FCFA)']."</strong></td>
                <td align='center'  valign='top' nowrap='nowrap'><strong>". $langs['(Montant)']."</strong></td>
            </tr>";

            for($i = 0; $i < sizeof($dates); $i++)
            {
                $date_transaction = $dates[$i];
                $nombre_retrait = $this->utils->nombreRetraitTiers($date_transaction, $agence);
                $nombre_retrait_total+=$nombre_retrait;
                $montant_retrait = $this->utils->montantRetraitTiers($date_transaction, $agence);
                $montant_retrait_total+=$montant_retrait;
                $commision = 300 * $nombre_retrait;
                $commision_total+=$commision;
                $nombre = $this->utils->nombreRetraitTitulaire($date_transaction, $agence);
                $nombre_total+=$nombre;
                $montant = $this->utils->montantRetraitTitulaire($date_transaction, $agence);
                $montant_total+=$montant;
                if($nombre_retrait > 0 || $nombre > 0){
                echo
                "<tr>
                    <td width='14%' align='center' valign='middle'>".$this->utils->date_fr2($date_transaction)."</td>
                    <td align='center' valign='top'>".$this->utils->number_format($nombre_retrait)."</td>
                    <td align='right' valign='top'>".$this->utils->number_format($montant_retrait)."</td>
                    <td align='right' valign='top'>".$this->utils->number_format($commision)."</td>
                    <td align='center' valign='top' bgcolor='#CCCCCC'>".$this->utils->number_format($nombre)."</td>
                    <td align='right' valign='top' bgcolor='#CCCCCC'>".$this->utils->number_format($montant)."</td>
                    <td align='center' valign='middle'>0</td>
                </tr>";
                }
            }
            echo
            "<tr>
                <td align='right'  valign='middle' nowrap='nowrap'><strong>".$langs['TOTAL'].":</strong></td>
                <td align='center' valign='middle'><strong>". $this->utils->number_format($nombre_retrait_total)."</strong></td>
                <td align='right'  valign='middle'><strong>". $this->utils->number_format($montant_retrait_total)."</strong></td>
                <td align='right'  valign='middle'><strong>". $this->utils->number_format($commision_total)."</strong></td>
                <td align='center' valign='middle' bgcolor='#CCCCCC'><strong>". $this->utils->number_format($nombre_total)."</strong></td>
                <td align='right'  valign='middle' bgcolor='#CCCCCC'><strong>". $this->utils->number_format($montant_total)."</strong></td>
                <td align='center' valign='middle'><strong>0</strong></td>
            </tr>
        </table>";
    }

    /***************** reporting produit search *********************/
    public function dashboardsearch()
    {        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(267,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['service'] = $this->reportingModel->allService();
        $params = array('view' => 'reporting/dashboardsearch');
        $this->view($params,$data);
    }

    /***************** bordereau de retrait*********************/
    public function dashboard()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        if($date1 != '' && $date2 != '' && $produit > 0)
        {
            $data['date1'] = $this->utils->securite_xss($_POST['datedeb']);
            $data['date2'] = $this->utils->securite_xss($_POST['datefin']);
            $data['produit'] = $this->utils->securite_xss($_POST['produit']);
            $data['allAgences'] = $this->reportingModel->allAgence();
            $allAgences = $this->reportingModel->allAgence();
            $data['transact'] = sizeof($allAgences);
            $params = array('view' => 'reporting/dashboard');
            $this->view($params,$data);
        }
        else
        {
            $data['recu'] = 0;
            $params = array('view' => 'reporting/dashboard');
            $this->view($params,$data);
        }   
        
    }

    /***************** recu jour *********************/
    public function printdashboard()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['produit'] = $this->utils->securite_xss($_POST['produit']);
        $data['allAgences'] = $this->reportingModel->allAgence();
        $allAgences = $this->reportingModel->allAgence();
        $data['transact'] = sizeof($allAgences);
        $params = array('view' => 'reporting/printdashboard');
        $this->view($params,$data);
    }

    public function printdashboardExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $allAgences = $this->reportingModel->allAgence();
        $montant_total = 0;
        $nombre_total = 0;
        $commision_total = 0;

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=dashboard.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";//ENCODAGE UTF-8

        echo "<table width='80%' border='1' align='center' cellpadding='5' cellspacing='0' style='font-size:16px'>
        <tr>
            <td width='14%' rowspan='2' align='center' valign='middle'><strong><span class='txt_form1'>".$langs['Bureaux']."</span></strong></td>
            <td colspan='3' align='center' valign='middle'><strong><span class='txt_form1'>". $this->utils->getNomService($produit)."</span></strong></td>
            <td width='23%' align='center' valign='middle'><strong><span class='txt_form1'>". $langs['ANNULATION']."</span></strong></td>
        </tr>
        <tr>
            <td width='8%' align='center'  valign='top' nowrap='nowrap'><strong>". $langs['Nombre']."</strong></td>
            <td width='14%' align='right'  valign='top' nowrap='nowrap'><strong>". $langs['Montant_(MRU)']."</strong></td>
            <td width='17%' align='right'  valign='top' nowrap='nowrap'><strong>". $langs['Commission_(MRU)']."</strong></td>
            <td align='center'  valign='top' nowrap='nowrap'><strong>". $langs['(Montant)']."</strong></td>
        </tr>";


        foreach($allAgences as $row_rs_resultat)
        {
            $idagence = $row_rs_resultat['rowid'];
            $label = $row_rs_resultat['agence'];

            $nombre = $this->utils->nbretableauBordParDate($date1, $date2, $produit, $idagence);
            $montant = $this->utils->mttableauBordParDate($date1, $date2, $produit, $idagence);

            if($produit==20) $commision = 300 * $nombre;
            else $commision = $this->utils->commissiontableauBordParDate($date1, $date2, $produit, $idagence);

            $montant_total+= $montant;
            $nombre_total+= $nombre;
            if($produit==20) $commision_total = 300 * $nombre_total;

            else $commision_total+= $commision;
            if ($nombre > 0) {


            echo "<tr>
                <td width='14%' align='left' valign='middle'>". $label."</td>
                <td align='center' valign='middle'>".$this->utils->number_format($nombre)."</td>
                <td align='right' valign='middle'>". $this->utils->number_format($montant)."</td>
                <td align='right' valign='middle'>". $this->utils->number_format($commision)."</td>
                <td align='center' valign='middle'>0</td>
            </tr>";

        }}
        echo "<tr>
            <td align='right' valign='middle'><strong>". $langs['TOTAL'].":</strong></td>
            <td align='center' valign='middle'><strong>". $this->utils->number_format($nombre_total)."</strong></td>
            <td align='right' valign='middle'><strong>". $this->utils->number_format($montant_total)."</strong></td>
            <td align='right' valign='middle'><strong>". $this->utils->number_format($commision_total)."</strong></td>
            <td align='center' valign='middle'><strong>0</strong></td>
        </tr>
        </table>";

    }

    /***************** reporting produit search *********************/
    public function tableaudeboard()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(35,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['allAgence'] = $this->reportingModel->allAgence();
        $data['typeAgence'] = $this->reportingModel->getTypeAgence1();
        $data['allService'] = $this->reportingModel->allService();
        $data['nbreTransactionMensuel'] = $this->reportingModel->nbreTransactionMensuel();
        $data['nbreCarteMensuel'] = $this->reportingModel->nbreCarteMensuel();
        $data['montantPaimentMensuel'] = $this->reportingModel->montantPaimentMensuel();
        $data['transfertCarteAcarteMensuel'] = $this->reportingModel->transfertCarteAcarteMensuel();
        $data['transfertCartetocashMensuel'] = $this->reportingModel->transfertCartetocashMensuel();
        $data['venteCarteMensuel'] = $this->reportingModel->venteCarteMensuel();
        $data['serviceRecharge'] = $this->reportingModel->serviceRecharge();
        $params = array('view' => 'reporting/tableaudeboard');
        $this->view($params,$data);
    }

    /***************** recu jour *********************/
    public function req1()
    {
        header("Content-Type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        echo "<list>";
        if(isset($_POST["region"])){
            $departement = (isset($_POST["region"])) ? htmlentities($_POST["region"]) : NULL;
            if ($departement) {
                $sql = "SELECT rowid, label   FROM agence where idtype_agence=:etat AND etat =1";
                $groupes = $this->getConnexion()->prepare($sql);
                $groupes->bindParam("etat",  $departement );
                $groupes->execute();
                $groupes->setFetchMode(PDO::FETCH_ASSOC);

                if($groupes->rowCount() > 0){
                    echo '<item id="" name="'.$data['lang']['select_agence'].'" selected="selected" />';
                    foreach($groupes as $g) {
                        echo "<item id=\"" . $g["rowid"] . "\" name=\"" . $g["label"] . "\" />";
                    }
                }
                else{
                    echo '<item id="" name="'.$data['lang']['no_dep'].'" />';
                }
            }
        }
        echo '</list>';
    }

    /***************** commission par produit *********************/

    public function reportingSearchCommission()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(276,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['service'] = $this->reportingModel->allService();
        $params = array('view' => 'reporting/reportingSearchCommission');
        $this->view($params,$data);
    }

    public function commissionParProduit()
    {
        $datedeb = $this->utils->securite_xss($_POST['datedeb']);
        $datefin = $this->utils->securite_xss($_POST['datefin']);
        $produit = $this->utils->securite_xss($_POST['produit']);

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(276,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['commissionParProduit'] = $this->reportingModel->commissionParProduits($produit, $datedeb, $datefin);



        $params = array('view' => 'reporting/commissionParProduit');
        $this->view($params,$data);
    }




}