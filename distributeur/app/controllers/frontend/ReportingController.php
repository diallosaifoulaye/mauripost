<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');
class ReportingController extends \app\core\FrontendController
{

    
    private $utils_reporting;
    private $connexion;
    private $userConnecter;
    private $utils_facturier;

    public function __construct()
    {
        $this->utils_reporting = new \app\core\UtilsReporting();
        $this->utils_facturier = new \app\core\UtilsFacturier();
        $this->connexion = \app\core\Connexion::getConnexion();
        parent::__construct('utilisateur');
        $this->userConnecter = $this->getSession()->getAttribut('objconnect');
        $this->getSession()->est_Connecter('objconnect');
    }

    public function index()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['user'] = $obj->getRowid();
        $data['nbtransac'] = $this->utils_reporting->gettransacnumber($data['user']);
        $data['nbreTransactionMensuel'] = $this->utils_reporting->nbreTransactionMensuel($data['user']);
        $data['venteCarteMensuel'] = $this->utils_reporting->venteCarteMensuel($data['user']);
        $service = 0;
        $data['courbe'] = 1;
        $data['annee'] = date('Y');
        $data['bureau'] = $obj->getRowid();
        $data['agence'] = $obj->getFk_agence();
        $data['service'] = $this->utils_reporting->allServicePar($service);
        $data['serviceRecharge'] = $this->utils_reporting->serviceRecharge();
        $data['serviceRecharge1'] = $this->utils_reporting->serviceRecharge1();
        $data['servicePaiement'] = $this->utils_facturier->servicePaiement();
        $data['transfertCartetocashMensuel'] = $this->utils_reporting->transfertCartetocashMensuel($data['user']);
        $date = date('Y-m-d');
        $data['dd']=$this->utils->joursemaine($date);


        $paramsview = array('view' => sprintf('frontend/reporting/index'));
        $this->view($paramsview, $data);
    }

    public function dashboard()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['user'] = $obj->getFk_agence();
        $data['nbtransac'] = $this->utils_reporting->gettransacnumber1($data['user']);
        $data['nbreTransactionMensuel'] = $this->utils_reporting->nbreTransactionMensuel1($data['user']);
        $data['venteCarteMensuel'] = $this->utils_reporting->venteCarteMensuel1($data['user']);
        $service = 0;
        $data['courbe'] = 1;
        $data['annee'] = date('Y');
        $data['bureau'] = $obj->getFk_agence();
        $data['service'] = $this->utils_reporting->allServicePar($service);
        $data['serviceRecharge'] = $this->utils_reporting->serviceRecharge();
        $data['serviceRecharge1'] = $this->utils_reporting->serviceRecharge1();
        $data['transfertCartetocashMensuel'] = $this->utils_reporting->transfertCartetocashMensuel1($data['user']);
        $date = date('Y-m-d');
        $data['dd']=$this->utils->joursemaine($date);

        $paramsview = array('view' => sprintf('frontend/reporting/dashboard1'));
        $this->view($paramsview, $data);
    }

    public function reportingdujour()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $paramsview = array('view' => 'frontend/reporting/transactionjr');
        $this->view($paramsview, $data);
    }

    /***************** transaction du jour *********************/
    public function processingUser()
    {
        $obj = $this->getSession()->getAttribut('objconnect');

        $profily = $obj->getFk_profil();
        $agence = $obj->getFk_agence();
        $rowid = $obj->getRowid();

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

        $date1 = date('Y-m-d');
        $date2 = date('Y-m-d');
        $next = '';
        $where = '';
        //$data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $duplicata = 'Duplicata';



        $type_profil = $this->utils->typeProfil($profily);
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$rowid;
        }else{
            $next = '';
            $where = '';
        }

        // getting total number records without any search
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, 
            transaction.statut, service.label, transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2
            AND transaction.fk_agence=$agence
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND user.fk_agence = agence.rowid ".$where;

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%' )";

        }

        $user = $this->connexion->prepare($sql);
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
            AND transaction.fk_agence=$agence
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND user.fk_agence = agence.rowid ".$where;

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( transaction.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR transaction.date_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.prenom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR user.nom LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR agence.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR service.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR carte.telephone LIKE '%".$requestData['search']['value']."%' )";

        }

        $user = $this->connexion->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,));
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->connexion->prepare($sql);
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
            $nestedData[] = "<a  href=".ROOT."reporting/detailTransac/".base64_encode($row["num_transac"])."' title='".$duplicata."' target='new'><input name='duplicata' type='button' class='btn btn-info btn-rounded' value='".$duplicata."'  /></a>";
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

    /***************** Duplicata *********************/
    public function detailTransac($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtransac = base64_decode($return[0]);
        $data['recu'] = $this->utils_reporting->dupliquerRecu($numtransac);
        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recu-reportingn.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('Recuduplicata.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }
    }

    /***************** recu jour *********************/
    public function facturejour()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->transactionJour($date1, $date2);
        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recuJour.php';


        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('L', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('recuJour.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }

    }

    /************************recu jour******************************/
    public function  transactionJour($date1, $date2)
    {
        $next = '';
        $where = '';
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence=$obj->getFk_agence();

        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$obj->getRowid();
        }else{
            $next = '';
            $where = '';
        }

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2
            AND transaction.fk_agence=$agence
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND user.fk_agence = agence.rowid ".$where."";

        try
        {
            $user = $this->connexion->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount();
            return $rows;
        }
        catch(Exception $e)
        {
            return -2;
            exit();
        }
    }

    /***************** reporting date search *********************/
    public function reportingsearchdate()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/reporting/reporting'));
        $this->view($paramsview, $data);
    }

    /***************** reporting date *********************/
    public function reportingdate()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $numserie = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['numserie'])));
        $data['datedeb'] = $date1;
        $data['datefin'] = $date2;
        $data['numserie'] = $numserie;
        $type_profil = $this->utils->typeProfil($obj->getType_profil());
        $data['reporting'] = $this->utils_reporting->transactionByDateAndOrUmSerie($date1, $date2, $numserie, $obj->getRowid(), $obj->getFk_agence(), $type_profil, $obj->getAdmin());

        $paramsview = array('view' => sprintf('frontend/reporting/reportingdate'));
        $this->view($paramsview, $data);

    }

    /***************** transaction par date *********************/
    public function processingdate($id)
    {
        $obj = $this->getSession()->getAttribut('objconnect');


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
        $numserie = "00".$this->utils->securite_xss($id[2]);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $duplicata = $data['lang']['duplicata'];
        $agence=$obj->getFk_agence();

        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$obj->getRowid();
        }else{
            $next = '';
            $where = '';
        }

        if($numserie != '00')
        {
            $where.="  AND carte.telephone =:carte";
        }else{
            $where.="";
        }


            $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence, carte.telephone
            FROM transaction, carte, service, user, agence " . $next . "
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid
            AND transaction.fk_agence=$agence
            AND user.fk_agence = agence.rowid " . $where;


            $user = $this->connexion->prepare($sql);
            if ($numserie != '00'){
                $user->execute(array("date1" => $date1,"date2" => $date2,"carte" => $numserie,));
            }else{
                $user->execute(array("date1" => $date1,"date2" => $date2));

            }
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
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence=$agence
            AND user.fk_agence = agence.rowid " . $where;


            if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " AND ( transaction.num_transac LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR transaction.montant LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR service.label LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR transaction.date_transaction LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR user.prenom LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR user.nom LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR agence.label LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR service.label LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR carte.telephone LIKE '%" . $requestData['search']['value'] . "%' )";

            }

            $user = $this->connexion->prepare($sql);
        if ($numserie != '00'){
            $user->execute(array("date1" => $date1,"date2" => $date2,"carte" => $numserie,));
        }else{
            $user->execute(array("date1" => $date1,"date2" => $date2));

        }
            $rows = $user->fetchAll();
            $totalFiltered = $user->rowCount();

            $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

            $user = $this->connexion->prepare($sql);
        if ($numserie != '00'){
            $user->execute(array("date1" => $date1,"date2" => $date2,"carte" => $numserie,));
        }else{
            $user->execute(array("date1" => $date1,"date2" => $date2));

        }
            $rows = $user->fetchAll();
            $data = array();

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
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);
            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[
                ] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href=".ROOT."reporting/detailTransacDate/".base64_encode($row["num_transac"])."' title='".$duplicata."' target='new'><input name='duplicata' type='button' class='btn btn-info btn-rounded' value='".$duplicata."'  /></a>";

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
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence=$obj->getFk_agence();

        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$obj->getRowid();
        }else{
            $next = '';
            $where = '';
        }

        if($carte != ''){

            $where.="  AND carte.telephone ='".$carte."'";
        }else{
            $where.="";
        }

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction, service, user, agence, carte ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.fk_carte = carte.rowid
            AND DATE(transaction.date_transaction) >='".$date1."'
            AND DATE(transaction.date_transaction) <='".$date2."'
            AND transaction.fk_agence=$agence
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND user.fk_agence = agence.rowid ".$where;

            try
            {
                $user = $this->connexion->prepare($sql);
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
    public function detailTransacDate($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtransac = base64_decode($return[0]);
        $data['recu'] = $this->utils_reporting->dupliquerRecu($numtransac);
        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recu-reportingn.php';

        include("$imprime");
        $content = ob_get_clean();

        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('Recuduplicata.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }
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
        $data['date1'] = $date1;
        $data['date2'] = $date2;
        $data['recu'] = $this->transactionDate($date1, $date2, $carte);

        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recuDate.php';


        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('L', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('recuDate.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }
    }

    /***************** reporting produit search *********************/
    public function reportingsearchproduit()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['service'] = $this->utils_reporting->allService();
        $params = array('view' => 'frontend/reporting/searchproduit');
        $this->view($params,$data);
    }

    /***************** reporting date *********************/
    public function reportingproduit()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $datedeb = $this->utils->securite_xss($_POST['datedeb']);
        $datefin = $this->utils->securite_xss($_POST['datefin']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        $paramsview = array('view' => sprintf('frontend/reporting/reportingproduit'));
        $this->view($paramsview, $data);

    }

    /***************** transaction du jour *********************/
    public function processingproduit($id)
    {
        // storing  request (ie, get/post) global array to a variable
        $requestData = $_REQUEST;

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
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $duplicata = $data['lang']['duplicata'];
        //getting total number records without any search
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $produit = $this->utils->securite_xss($id[2]);
        $agence=$obj->getFk_agence();
        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$obj->getRowid();
        }else{
            $next = '';
            $where = '';
        }


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence,carte.telephone
            FROM transaction, service, user, agence, carte ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_service =:produit
            AND transaction.fk_service != 0 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence=$agence
            AND user.fk_agence = agence.rowid ".$where."";

        //var_dump($sql);exit;

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


        $user = $this->connexion->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,"produit" => $produit,));
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
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence=$agence
            AND user.fk_agence = agence.rowid ".$where."";


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


        $user = $this->connexion->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,"produit" => $produit,));
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->connexion->prepare($sql);
        $user->execute(array("date1" => $date1,"date2" => $date2,"produit" => $produit,));

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
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);
            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = $row["nom_agence"];
            $nestedData[] = "<a  href=".ROOT."reporting/detailTransacProduit/".base64_encode($row["num_transac"])."' title='".$duplicata."' target='new'><input name='duplicata' type='button' class='btn btn-info btn-rounded' value='".$duplicata."'  /></a>";

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
    public function  transactionProduit($date1, $date2, $produit)
    {

        $next = '';
        $where = '';
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence=$obj->getFk_agence();

        $type_profil = $this->utils->typeProfil($obj->getFk_profil());
        if($obj->getAdmin() == 1 || $type_profil == 1){
            $next = '';
            $where = '';
        }
        else if ($type_profil == 3)
        {
            $where.=" AND transaction.fkuser=".$obj->getRowid();
        }else{
            $next = '';
            $where = '';
        }

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, carte.telephone, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom, agence.label as nom_agence
            FROM transaction, carte, service, user, agence ".$next."
            WHERE transaction.statut = 1
            AND service.etat = 1 
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND transaction.fk_carte = carte.rowid
            AND transaction.fk_service =:service
            AND transaction.fk_service != 0 
            AND transaction.fk_service = service.rowid 
            AND transaction.fkuser = user.rowid 
            AND transaction.fk_agence=$agence
            AND user.fk_agence = agence.rowid ".$where;

        try
        {
            $user = $this->connexion->prepare($sql);
            $user->execute(array("date1" => $date1,"date2" => $date2,"service" => $produit,));
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
    public function detailTransacProduit($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtransac = base64_decode($return[0]);
        $data['recu'] = $this->utils_reporting->dupliquerRecu($numtransac);
        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recu-reportingn.php';

        include("$imprime");
        $content = ob_get_clean();

        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('Recuduplicata.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }
    }

    /***************** recu jour *********************/
    public function factureproduit()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $produit = $this->utils->securite_xss($_POST['produit']);
        $data['date'] = $this->utils->securite_xss($_POST['date2']);
        $data['recu'] = $this->transactionProduit($date1, $date2, $produit);
        $currencyCode = 'Ar';
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/reporting/recuProduit.php';


        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('L', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('recuDate.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            return -2;
            exit;
        }
    }


    /***************** Commision totale du distributeur *********************/

    public function commisionTotaleDistributeur()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $distributeur=$obj->getFk_agence();
        $data['commission'] = $this->utils_reporting->getTauxDistributeurService($distributeur);

        $paramsview = array('view' => 'frontend/reporting/commissionTotaleDistributeur');
        $this->view($paramsview, $data);
    }


}