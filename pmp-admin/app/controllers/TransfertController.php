<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */

class TransfertController extends \app\core\BaseController
{
    public $transfertModel;
    public $reportingModel;
    public $api_gtp;
    private $userConnecter;


    public function __construct()
    {
        parent::__construct();
        $this->transfertModel = $this->model('TransfertModel');
        $this->reportingModel = $this->model('ReportingModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /***************** Fonction historique des transferts *********************/
    public function index()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 4) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/accueil');
        $this->view($params,$data);
    }

    /***************** Fonction historique des transferts *********************/
    public function transfert()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(70,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/index');
        $this->view($params,$data);
    }

    /***************** Fonction historique des transferts *********************/
    public function envoie()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(75, $this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/envoie');
        $this->view($params,$data);
    }
    /***************** Fonction historique *********************/
    public function historiqueEnvoi()
    {

        $data['agence']=$this->utils->securite_xss($_POST['agence']);
        $data['datedeb']=$this->utils->securite_xss($_POST['datedeb']);
        $data['datefin']=$this->utils->securite_xss($_POST['datefin']);


        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(76,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquenvoie');
        $this->view($params,$data);
    }


    /***************** Fonction historique des paiements *********************/
    public function historiquePaiement()
    {
        $data['agence']=$this->utils->securite_xss($_POST['agence']);
        $data['datedeb']=$this->utils->securite_xss($_POST['datedeb']);
        $data['datefin']=$this->utils->securite_xss($_POST['datefin']);

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(76,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquepaiement');
        $this->view($params,$data);
    }


    /***************** Fonction historique *********************/
    public function historiqueTransfert()
    {
        $data['agence']  = $this->utils->securite_xss($_POST['agence']);
        $data['datedeb'] = $this->utils->securite_xss($_POST['datedeb']);
        $data['datefin'] = $this->utils->securite_xss($_POST['datefin']);

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(18,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquenvoiecashtocash');
        $this->view($params,$data);
    }




    /***************** export histo transfert pdf *********************/
    public function histoTransfertPDF()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['agence'] = $this->utils->securite_xss($_POST['agence']);

        $data['recu'] = $this->transfertModel->historiqueTransfert($data['date1'], $data['date2'], $data['agence']);
        $params = array('view' => 'transfert/facturerecuJour');
        $this->view($params,$data);
    }

    public function histoTransfertExcel()
    {
        $langs =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['date1'] = $this->utils->securite_xss($_POST['date1']);
        $data['date2'] = $this->utils->securite_xss($_POST['date2']);
        $data['agence'] = $this->utils->securite_xss($_POST['agence']);

        $rows = $this->transfertModel->historiqueTransfert($data['date1'], $data['date2'], $data['agence']);

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=TransactionDuJour.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";

        echo "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0'>
              <tr align='center' valign='top'>
                <td width='11%'><strong>". $langs['date_transfert']."</strong></td>
                <td width='9%'>N°<strong>". $langs['transaction']."</strong></td>
                <td width='11%'><strong>".$langs['code']."</strong></td>
                <td width='11%'><strong>".$langs['expediteur']."</strong></td>
                <td width='11%'><strong>".$langs['destinataire']."</strong></td>
                <td width='11%'><strong>".$langs['montant']."</strong></td>
                <td width='12%'><strong>".$langs['agence']."</strong></td>
                <td width='12%'><strong>".$langs['statut']."</strong></td>
            </tr>";

        foreach($rows as $row_transact)
        {
                if($row_transact["statut"]==1)
                    $libelle = '<span class="text-success text-bold" >Retiré</span>';
                if($row_transact["statut"]==0 && $row_transact["refound"] == 0)
                    $libelle = '<span class="text-info text-bold">En cours</span>';
                if($row_transact["statut"]==3)
                    $libelle = '<span class="text-danger text-bold" >Annulé</span>';
                if($row_transact["statut"]==2 && $row_transact["refound"] == 1)
                    $libelle = '<span class="text-primary text-bold">Remboursé</span>';

            echo "<tr align='center' valign='middle'

                    <td>".$this->utils->date_jj_mm_aaaa_hh_ii_ss($row_transact["date_tranfert"])."</td>
                    <td>". $row_transact['num_transac']."</td>
                    <td align='left'>".$row_transact['code']."</td>
                    <td align='right'>".$row_transact['sender']."</td>
                    <td align='right'>".$row_transact['receiver']."</td>
                    <td align='right' style=\"text-align: right !important;\">". $this->utils->number_format($row_transact["montant"])."</td>
                    <td align='left'>". $row_transact["label"] ."</td>
                    <td align='left'>". $libelle."</td>
                </tr>";
        }
        echo "</table>";
    }


    /***************** Fonction historique *********************/
    public function historiquperiode()
    {
        $data['datedeb']= $this->utils->securite_xss($_POST['datedeb']);
        $data['datefin']= $this->utils->securite_xss($_POST['datefin']);

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(76,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiqueperiode');
        $this->view($params,$data);
    }
    /***************** Fonction historique envoi admin *********************/
    public function historiqueEnvoiadmin()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquenvoiadmin');
        $this->view($params,$data);
    }

    /***************** Fonction recaptiulative sur  un transferts *********************/
    public function recapEnvoi($id)
    {
        $num_transac=base64_decode($id[0]);
        $data['infoenvoi'] =$this->transfertModel->getInfosTransfert($num_transac);
        $data['agence'] =$this->userConnecter->agence;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'transfert/envoieRecap');
        $this->view($params,$data);
    }
    /***************** Fonction Erreur sur  un transferts *********************/
    public function erreurtransfert($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($id[0]==-1)){
            $message_transfert = 'Erreur web service: Donnees invalides';
        }
        if(base64_decode($id[0]==-2)){
            $data['$message_transfert']  = 'Solde agence insuffisant';
        }
        if(base64_decode($id[0]==-3)){
            $data['$message_transfert']  = 'Une erreur est survenue lors du debit de l\'agence, veuillez reessayer';
        }else{
            $retour = json_decode($erreur);
            $data['$message_transfert'] = $retour->{'errorMessage'};
        }
        $params = array('view' => 'transfert/erreurtransfert');
        $this->view($params,$data);
    }
    /***************** Fonction faire un transferts *********************/
    public function AjoutEnvoie()
    {
        $agencesender =$this->userConnecter->fk_agence;
        $valueSource = $this->utils->getSoldeAgence($agencesender);
        $currencyCodeSource = 'XOF';
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = $this->utils->securite_xss($_POST['code']);
        $date_tranfert = $this->utils->securite_xss($_POST['date_tranfert']);
        $nom_sender = $this->utils->securite_xss($_POST['nom']);
        $prenom_sender = $this->utils->securite_xss($_POST['prenom']);
        $type_piece_sender = $this->utils->securite_xss($_POST['typepiece']);
        $piece_sender = $this->utils->securite_xss($_POST['piece']);
        $tel_sender = $this->utils->securite_xss($_POST['tel2']);
        if(substr($tel_sender, 0, 1) == '+')
        {
            $tel_sender = substr($tel_sender, 1);
            $tel_sender = '00'.$tel_sender;
        }
        $pays_sender = $this->utils->securite_xss($_POST['pays']);
        $ville_sender = $this->utils->securite_xss($_POST['region']);
        $adresse_sender = $this->utils->securite_xss($_POST['adresse']);
        $nom_receiver = $this->utils->securite_xss($_POST['nom_dst']);
        $prenom_receiver = $this->utils->securite_xss($_POST['prenom_dst']);
        $tel_receiver = $this->utils->securite_xss($_POST['tel_dst2']);
        if(substr($tel_receiver, 0, 1) == '+')
        {
            $tel_receiver = substr($tel_receiver, 1);
            $tel_receiver = '00'.$tel_receiver;
        }
        $pays_receiver = $this->utils->securite_xss($_POST['pays_dst']);
        $ville_receiver = $this->utils->securite_xss($_POST['region_dst']);
        $adresse_receiver = $this->utils->securite_xss($_POST['adresse_dst']);
        $service = 11;
        $frais =  $this->utils->securite_xss($_POST['frais2']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $mtn_total = $montant + $frais;
        $montant_total = $mtn_total;
        $num_transac = $this->utils->Generer_numtransaction();
        $user_sender=$this->userConnecter->rowid;

        if($valueSource >= $mtn_total)
        {
            $response = $this->transfertModel->debiter_soldeAgence($mtn_total, $agencesender);
            if($response == 1) {
                $statut = 1;
                $sens = 1;
                $credit_commission = $this->utils->crediterCarteCommission($frais);
                if($credit_commission == 1)
                {
                    $this->utils->addCommission($frais, $service, 0 ,"Transfert d'argent : envoi Postecash" , $agencesender);
                }
                else
                {
                    $this->utils->addCommission_afaire($frais, $service, 0, "Transfert d'argent : envoi Postecash ", $agencesender);
                }

                $result_transfert = $this->transfertModel->saveInfosTransfert($num_transac, $code, $montant, $frais, $montant_total, $date_tranfert, $nom_sender, $prenom_sender, $type_piece_sender, $piece_sender, $tel_sender, $pays_sender, $ville_sender, $adresse_sender, $nom_receiver, $prenom_receiver, $tel_receiver, $pays_receiver, $ville_receiver, $adresse_receiver, $service,$user_sender,$agencesender,0);
                if($result_transfert > 0){

                    $message = "Bienvenu(e) Chez Postecash " . $prenom_sender . " " . $nom_sender . " vous a envoyé " . $montant . ".Code:" . $code . "Merci.";
                    $this->utils->sendSMS("Postecash", $tel_receiver, $message);

                    $this->utils->log_journal("Envoi  du code " . $code,'', 'Envoie transfert', 'transfert', $this->userConnecter->rowid);

                    $result = $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $user_sender, $statut,'Envoi Postecash  OK', $frais, $agencesender,0);

                    if($result)
                    {
                        $this->utils->save_DetailTransaction($num_transac, $agencesender, $montant, $sens);
                        $this->utils->log_journal("Envoi  d'argent " . 'transfert','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['envoi_ok'], 'transfert', $this->userConnecter->rowid);
                    }
                    else
                    {
                        $this->utils->log_journal("Envoi  d'argent " . 'transfert','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_ko'], 'transfert', $this->userConnecter->rowid);
                    }

                    $alert =$data['lang']['envoi_ok'];
                    $type_alert='text-green';
                    $this->rediriger('Transfert','recapEnvoi/'.base64_encode($num_transac));
                }
            }
            else{
                $this->rediriger('Transfert','erreurtransfert/'.base64_encode(-3));
            }
        }
        else{
            $this->rediriger('Transfert','erreurtransfert/'.base64_encode(-2));
        }

    }

    /***************** Fonction paiement d'un transferts *********************/
    public function getFrais()
    {
        $service =$this->utils->securite_xss($_POST['service']);
        $montant =$this->utils->securite_xss($_POST['montant']);
        echo $this->transfertModel->calculFraisab($service,$montant);;
    }

    public function getFraisPapa()
    {
        $_POST['service'] = 11;
        $_POST['montant'] = 2000;
        $service =$this->utils->securite_xss($_POST['service']);
        $montant =$this->utils->securite_xss($_POST['montant']);
        echo $this->transfertModel->calculFraisab($service,$montant);
    }
    /***************** Fonction historique des envois carte vers cash *********************/
    public function envoidata($id)
    {
       // var_dump($_POST);exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $admin= $this->userConnecter->admin;
        // / storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_tranfert',
            1=> 'num_transac',
            2=> 'code',
            3=> 'sender',
            4=> 'receiver',
            5=> 'montant',
            6=> 'commission',
            7=> 'statut',
            8=> 'label',
            9=> 'idtransfert'
        );
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $where = '';
        }
        elseif($type_profil==2 || $type_profil==4)
        {
            $where.=" AND u.agencesender=".$this->userConnecter->fk_agence;
        }

        else{
            $where.=" AND u.user_sender=".$this->userConnecter->rowid;
        }
        if(isset($_POST)){
            if($_POST['agence'] != '' && $_POST['agence'] > 0){
                $where .= " AND b.fk_agence = ".$this->utils->securite_xss($_POST['agence']);
            }
            if($_POST['datedeb'] != ''){
                $where .= " AND DATE(u.date_tranfert) >= '".$this->utils->securite_xss($_POST['datedeb'])."'";
            }
            if($_POST['datefin'] != ''){
                $where .= " AND DATE(u.date_tranfert) <= '".$this->utils->securite_xss($_POST['datefin'])."'";
            }
        }

        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac, u.frais,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant,u.statut, a.label,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u LEFT JOIN user as b ON u.user_sender = b.rowid LEFT JOIN agence as a ON b.fk_agence = a.rowid  LEFT JOIN service s ON u.fk_service = s.rowid WHERE u.statut = 0";

        //   $sql.=" FROM tranfert as u LEFT JOIN agence as a ON u.agencesender = a.rowid WHERE fk_service = 11 ";
        $sql .= $where;

        $user = $this->getConnexion()->prepare($sql);

        $user->execute();
        $rows = $user->fetchAll();

        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac, u.frais,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant,u.statut, a.label,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u LEFT JOIN user as b ON u.user_sender = b.rowid LEFT JOIN agence as a ON b.fk_agence = a.rowid  LEFT JOIN service s ON u.fk_service = s.rowid WHERE u.statut = 0";

        // $sql.=" FROM tranfert as u LEFT JOIN agence as a ON u.agencesender = a.rowid WHERE fk_service = 11 ";
        $sql .= $where;


        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_tranfert LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.nom_sender LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.nom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' )";
        }

        $user = $this->getConnexion()->prepare($sql);
        if($admin!=1){
            $user->execute();
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if($row["statut"]==1)
                $libelle = '<span class="text-success text-bold" >Retiré</span>';
            if($row["statut"]==0 && $row["refound"] == 0)
                $libelle = '<span class="text-info text-bold">En cours</span>';
            if($row["statut"]==3)
                $libelle = '<span class="text-danger text-bold" >Annulé</span>';
            if($row["statut"]==2 && $row["refound"] == 1)
                $libelle = '<span class="text-primary text-bold">Remboursé</span>';

            $nestedData[] = $this->utils->date_jj_mm_aaaa_hh_ii_ss($row["date_tranfert"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["sender"];
            $nestedData[] = $row["receiver"];
            $nestedData[] = $this->utils->nombre_form($row["montant"]);
            $nestedData[] =  $this->utils->nombre_form($row["frais"]);
            $nestedData[] = $row["label"];
            $nestedData[] = $libelle;

            $nestedData[] = "<a  href='". ROOT.'transfert/recapEnvoi/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }



        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        // echo 1;
        echo json_encode($json_data);  // send data as json format
    }


    /***************** Fonction historique des paiements carte vers cash ou cash vers cash *********************/
    public function paiementdata($id)
    {
        $admin= $this->userConnecter->admin;
        // / storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_receiver',
            1=> 'num_transac',
            2=> 'code',
            3=> 'sender',
            4=> 'receiver',
            5=> 'montant',
            6=> 'commission',
            7=> 'service',
            8=> 'label',
            9=> 'idtransfert'
        );
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $where = '';
        }
        elseif($type_profil==2 || $type_profil==4)
        {
            $where.=" AND u.agencereceiver=".$this->userConnecter->fk_agence;
        }
        else{
            $where.=" AND u.user_receiver=".$this->userConnecter->rowid;
        }

        if(isset($_POST)){
            if($_POST['agence'] != '' && $_POST['agence'] > 0){
                $where .= " AND b.fk_agence = ".$this->utils->securite_xss($_POST['agence']);
            }
            if($_POST['datedeb'] != ''){
                $where .= " AND DATE(u.date_receiver) >= '".$this->utils->securite_xss($_POST['datedeb'])."'";
            }
            if($_POST['datefin'] != ''){
                $where .= " AND DATE(u.date_receiver) <= '".$this->utils->securite_xss($_POST['datefin'])."'";
            }
        }
       
        // getting total number records without any search
        $sql = "SELECT u.date_receiver,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant, a.label, CONCAT(b.prenom, ' ', b.nom) as agent, u.frais, u.idtransfert, s.label as service";
        $sql.=" FROM tranfert as u LEFT JOIN user as b ON u.user_receiver = b.rowid LEFT JOIN agence as a ON b.fk_agence = a.rowid  LEFT JOIN service s ON u.fk_service = s.rowid WHERE u.statut = 1";
        $sql .= $where;


        $user = $this->getConnexion()->prepare($sql);

        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        // getting total number records without any search
        $sql = "SELECT u.date_receiver,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant, a.label, CONCAT(b.prenom, ' ', b.nom) as agent, u.frais,u.idtransfert, s.label as service";
        $sql.=" FROM tranfert as u LEFT JOIN user as b ON u.user_receiver = b.rowid LEFT JOIN agence as a ON b.fk_agence = a.rowid  LEFT JOIN service s ON u.fk_service = s.rowid WHERE u.statut = 1";
        $sql .= $where;


        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_receiver LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.nom_sender LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.nom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.frais LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR s.label LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' )";
        }

        $user = $this->getConnexion()->prepare($sql);
        if($admin!=1){
            $user->execute();
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();


            $nestedData[] = $this->utils->date_jj_mm_aaaa_hh_ii_ss($row["date_receiver"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["sender"];
            $nestedData[] = $row["receiver"];
            $nestedData[] = $this->utils->nombre_form($row["montant"]);
            $nestedData[] = $this->utils->nombre_form($row["frais"]);
            $nestedData[] = $row["service"];
            $nestedData[] = $row["label"];

            $nestedData[] = "<a  href='". ROOT.'transfert/recapEnvoi/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }



        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format
    }


    public function paiementpdf(){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $data['datedeb'] = $date1;
        $data['datefin'] = $date2;


        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1){
            $where = '';
        }
        elseif($type_profil==2 || $type_profil==4)
        {
            $where.=" AND u.agencereceiver=".$this->userConnecter->fk_agence;
        }
        else{
            $where.=" AND u.user_receiver=".$this->userConnecter->rowid;
        }

        if(isset($_POST)){
            if($_POST['agence'] != '' && $_POST['agence'] > 0){
                $where .= " AND b.fk_agence = ".$this->utils->securite_xss($_POST['agence']);
            }
            if($_POST['datedeb'] != ''){
                $where .= " AND DATE(u.date_receiver) >= '".$this->utils->securite_xss($_POST['datedeb'])."'";
            }
            if($_POST['datefin'] != ''){
                $where .= " AND DATE(u.date_receiver) <= '".$this->utils->securite_xss($_POST['datefin'])."'";
            }
        }
        // getting total number records without any search
        $sql = "SELECT u.date_receiver,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant, a.label, CONCAT(b.prenom, ' ', b.nom) as agent, u.idtransfert, s.label as nom_service";
        $sql.=" FROM tranfert as u LEFT JOIN user as b ON u.user_receiver = b.rowid LEFT JOIN agence as a ON b.fk_agence = a.rowid LEFT JOIN service s ON u.fk_service = s.rowid  WHERE u.statut = 1";
        $sql .= $where;


        $user = $this->getConnexion()->prepare($sql);

        $user->execute();
        $data['paiement'] = $user->fetchAll();

        $params = array('view' => 'transfert/rapportpaiementpdf');
        $this->view($params,$data);
    }

    /***************** Fonction historique des envois cash vers cash *********************/
    public function envoidatacashtocash($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $admin= $this->userConnecter->admin;
        // / storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_tranfert',
            1=> 'num_transac',
            2=> 'code',
            3=> 'sender',
            4=> 'receiver',
            5=> 'montant',
            6=> 'statut',
            7=> 'label',
            8=> 'idtransfert'
        );
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        if($this->userConnecter->admin == 1 || $type_profil==1 || $this->userConnecter->profil == 62){
            $where = '';
        }
        elseif($type_profil==2 || $type_profil==4)
        {
            $where.=" AND u.agencesender=".$this->userConnecter->fk_agence;
        }

        else{
            $where.=" AND u.user_sender=".$this->userConnecter->rowid;
        }
        if(isset($_POST)){
            if($_POST['agence'] != '' && $_POST['agence'] > 0){
                $where .= " AND u.agencesender = ".$this->utils->securite_xss($_POST['agence']);
            }
            if($_POST['datedeb'] != ''){
                $where .= " AND DATE(u.date_tranfert) >= '".$this->utils->securite_xss($_POST['datedeb'])."'";
            }
            if($_POST['datefin'] != ''){
                $where .= " AND DATE(u.date_tranfert) <= '".$this->utils->securite_xss($_POST['datefin'])."'";
            }
        }


        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant,u.statut, a.label,u.refound,u.idtransfert";

        $sql.=" FROM tranfert as u LEFT JOIN agence as a ON u.agencesender = a.rowid WHERE 1 ";
        $sql .= $where;

        $user = $this->getConnexion()->prepare($sql);

        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver, ' ', u.nom_receiver) as receiver,u.montant,u.statut, a.label,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u LEFT JOIN agence as a ON u.agencesender = a.rowid WHERE 1 ";
        $sql .= $where;


        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_tranfert LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR a.label LIKE '%".$requestData['search']['value']."%' )";
        }

        $user = $this->getConnexion()->prepare($sql);
        if($admin!=1){
            $user->execute();
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if($row["statut"]==1)
                $libelle = '<span class="text-success text-bold" >Retiré</span>';
            if($row["statut"]==0 && $row["refound"] == 0)
                $libelle = '<span class="text-info text-bold">En cours</span>';
            if($row["statut"]==3)
                $libelle = '<span class="text-danger text-bold" >Annulé</span>';
            if($row["statut"]==2 && $row["refound"] == 1)
                $libelle = '<span class="text-primary text-bold">Remboursé</span>';

            $nestedData[] = $this->utils->date_jj_mm_aaaa_hh_ii_ss($row["date_tranfert"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["sender"];
            $nestedData[] = $row["receiver"];
            $nestedData[] = '<p style="text-align: right !important;">'.$this->utils->number_format($row["montant"]).'</p>';
            $nestedData[] = $row["label"];
            $nestedData[] = $libelle;

            $nestedData[] = "<a  href='". ROOT.'transfert/recapEnvoi/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }



        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );


        echo json_encode($json_data);  // send data as json format
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function envoidataParam($id)
    {

        $admin= $this->userConnecter->admin;
        // / storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_tranfert',
            1=> 'num_transac',
            2=> 'code',
            3=> 'sender',
            4=> 'receiver',
            5=> 'montant',
            6=> 'statut',
            8=> 'idtransfert'
        );


        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver,u.montant,u.statut,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u WHERE 1 ";

        if($admin!=1 && $this->userConnecter->profil != 62){
            $sql.=" AND  u.user_sender = :user AND fk_service <> 28";
        }

        if($id[0]!='')
        {
            $sql.="  AND u.date_tranfert>='".$this->utils->securite_xss($id[0].'00:00:00')."'";
        }

        if($id[1]!='')
        {
            $sql.="  AND u.date_tranfert <='".$this->utils->securite_xss($id[1].'23:59:59')."'";
        }

        $user = $this->getConnexion()->prepare($sql);

        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,CONCAT(u.prenom_sender, ' ', u.nom_sender) as sender, CONCAT(u.prenom_receiver,u.montant,u.statut,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u";
        $sql.=" WHERE 1=1";
        if( $admin!=1){
            $sql.=" AND fk_service <> 28 AND u.user_sender = :user";
        }

        if($id[0]!='')
        {
            $sql.="  AND u.date_tranfert>='".$this->utils->securite_xss($id[0].'00:00:00')."'";
        }

        if($id[1]!='')
        {
            $sql.="  AND u.date_tranfert <='".$this->utils->securite_xss($id[1].'23:59:59')."'";
        }
        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_tranfert LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '%".$requestData['search']['value']."%' )";
        }


        $user = $this->getConnexion()->prepare($sql);
        if($admin!=1){
            $user->execute(
                array(
                    "user" => $this->userConnecter->rowid,
                )
            );
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if($row["statut"]==1)
                $libelle = '<span class="text-success text-bold" >'.$data['lang']['transfert_retire'].'</span>';
            if($row["statut"]==0 && $row["refound"] == 0)
                $libelle = '<span class="text-info text-bold">'.$data['lang']['transfert_encours'].'</span>';
            if($row["statut"]==2)
                $libelle = '<span class="text-danger text-bold" >'.$data['lang']['transfert_annule'].'</span>';
            if($row["statut"]==0 && $row["refound"] == 1)
                $libelle = '<span class="text-primary text-bold">'.$data['lang']['transfert_rembourse'].'</span>';

            $nestedData[] = $this->utils->date_jj_mm_aaaa_hh_ii_ss($row["date_tranfert"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["sender"];
            $nestedData[] = $row["receiver"];
            $nestedData[] = $row["montant"];
            $nestedData[] = $libelle;

            $nestedData[] = "<a  href='". ROOT.'transfert/recapEnvoi/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format
    }
    /***************** Fonction mise à jour d'un transferts *********************/
    public function UpdatePiaement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/envoie');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu *********************/
    public function impressionRecuEnvoie()
    {

        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        // var_dump($data['infoenvoie']);die;
        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/transfert/recu-envoi-cash.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../assets/html2pdf/html2pdf.class.php';

        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuTransfertCash.pdf', 'I');
        }
        catch (HTML2PDF_exception $e)
        {
            echo $e;
            exit;
        }
    }




    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                    PAIEMENT                                                            //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    function dateDiff($start, $end) {
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $diff = $end_ts - $start_ts;
        return round($diff / 86400);
    }

    public function searchHistoriquePaiement(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(268,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'transfert/search-historique-paiement');
        $this->view($params,$data);
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(73,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/paiement');
        $this->view($params,$data);
    }
    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function confirm_paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(73,$this->userConnecter->profil) );
        $code=$this->utils->securite_xss($_POST['code']);
        $tel=$this->utils->securite_xss($_POST['tel']);
        if(substr($tel, 0, 1) == '+')
        {
            $tel = substr($tel, 1);
            $tel = '00'.$tel;
        }
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['infoenvoi'] =$this->transfertModel->getInfosTransfertByCode($code,$tel);

        if($data['infoenvoi']==-1){
            $params = array('view' =>'transfert/paiement', 'title' =>$data['lang']['new_paiement'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }
        else{
            $start = $data['infoenvoi']->date_tranfert;
            $end = date('Y-m-d');
            $dateDiff = $this->dateDiff($start, $end);

            $params = array('view' => 'transfert/confirm_paiement');
            $this->view($params,$data);
        }

    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function valider_paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(73,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        /***********************Debut recup solde carte agence*************************/
        $agence = $this->userConnecter->fk_agence;
        /***********************Fin recup solde carte agence*************************/
        $id = -1;
        if(isset($_POST['tel_sender']) && isset($_POST['prenom_receiver']) && isset($_POST['nom_receiver']) &&
            isset($_POST['numtransaction']) && isset($_POST['montant']) && isset($_POST['idtransfert'])
            && isset($_POST['date_reception']) && isset($_POST['udid']))
        {
            $montant =  $this->utils->securite_xss($_POST['montant']);
            $numero_transaction =  $this->utils->securite_xss($_POST['numtransaction']);
            $mtn_total=$montant;

            $date_receive =  $this->utils->securite_xss($_POST['date_reception']);
            $idtransfert =  $this->utils->securite_xss( base64_decode($_POST['idtransfert']));

            $type_piece =  $this->utils->securite_xss($_POST['typepiece']);
            $piece =  $this->utils->securite_xss($_POST['piece']);
            $date_delivrance =  $this->utils->securite_xss($_POST['datedeb']);
            $date_expiration =  $this->utils->securite_xss($_POST['datefin']);

            $prenom_receiver =  $this->utils->securite_xss($_POST['prenom_receiver']);
            $nom_receiver =  $this->utils->securite_xss($_POST['nom_receiver']);
            $tel_sender =  $this->utils->securite_xss($_POST['tel_sender']);
            $code =  $this->utils->securite_xss($_POST['code']);
            $playerID =  $this->utils->securite_xss($_POST['udid']);

            $statut=1;
            $sens = 1;

            $this->transfertModel->crediter_soldeAgence($montant, intval($agence));

            $this->transfertModel->updateInfosPaiementA($this->userConnecter->rowid, $date_receive, $type_piece, $piece, $idtransfert, $date_delivrance, $date_expiration, $this->userConnecter->fk_agence);

            $message = $data['lang']['mess_transfert1']." " . $montant . " ".$data['lang']['mess_transfert2'] . $prenom_receiver . " " . $nom_receiver . $data['lang']['mess_transfert3'] . $code . $data['lang']['mess_transfert4'];
            $this->utils->sendSMS($data['lang']['paositra'], $tel_sender, $message);

            $this->utils->log_journal('Paiement du code transfert: '.$code,'', 'Retrai transfert, bureau payeur'.$agence, 'transfert', $this->userConnecter->rowid);
            $id = base64_encode($numero_transaction);

        }
        $this->rediriger('transfert', 'paiement_recap/'.$id);


    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function paiement_recap($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(73,$this->userConnecter->profil) );
        $numtransaction  = base64_decode($id[0]);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/paiement_recap');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu *********************/
    public function impressionRecu()
    {
        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/transfert/recu-paiement-cash.php';
        include("$imprime");
        $content = ob_get_clean();
        // convert in PDF
        require_once __DIR__.'/../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuTransfertCash.pdf', 'I');
        }
        catch (HTML2PDF_exception $e)
        {
            echo $e;
            exit;
        }

    }

    /***************** Fonction historique *********************/
    public function historiqueReception()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquereception');
        $this->view($params,$data);
    }

    /***************** Fonction historique envoi admin *********************/
    public function historiqueReceptionadmin()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/historiquereceptionadmin');
        $this->view($params,$data);
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function envoidataRecep($id)
    {
        $admin= $this->utils->securite_xss($id[0]);
        // / storing  request (ie, get/post) global array to a variable
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_tranfert',
            1=> 'num_transac',
            2=> 'code',
            3=> 'prenom_sender',
            4=> 'prenom_receiver',
            5=> 'montant',
            6=> 'statut',
            7=> 'idtransfert'
        );


        // getting total number records without any search
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,u.prenom_sender,u.prenom_receiver,u.montant,u.statut,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u";

        if($admin!=1 && $this->userConnecter->profil != 62){
            $sql.=" WHERE u.user_sender = :user AND fk_service <> 28";
        }
        $user = $this->getConnexion()->prepare($sql);

        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,u.prenom_sender,u.prenom_receiver,u.montant,u.statut,u.refound,u.idtransfert";
        $sql.=" FROM tranfert as u";
        $sql.=" WHERE 1=1";
        if( $admin!=1){
            $sql.=" AND fk_service <> 28 AND u.user_sender = :user";
        }
        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_tranfert LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_receiver LIKE '".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '".$requestData['search']['value']."%' )";
        }

        $user = $this->getConnexion()->prepare($sql);
        if($admin!=1){
            $user->execute(
                array(
                    "user" => $this->userConnecter->rowid,
                )
            );
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $this->userConnecter->rowid,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if($row["statut"]==1)
                $libelle = 'Valide';
            if($row["statut"]==0 && $row["refound"] == 0)
                $libelle = 'En cours';
            if($row["statut"]==2)
                $libelle = 'Annule';
            if($row["statut"]==0 && $row["refound"] == 1)
                $libelle = 'Rembourse';

            $nestedData[] = $row["date_tranfert"];
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["prenom_sender"];
            $nestedData[] = $row["prenom_receiver"];
            $nestedData[] = $row["montant"];
            $nestedData[] = $libelle;

            $nestedData[] = "<a  href='". ROOT.'transfert/recapEnvoi/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format
    }


    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                    REMBOURSEMENT                                                            //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function remboursement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(170,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/remboursement');
        $this->view($params,$data);
    }
    /***************** Fonction Pour valider  un remboursement *********************/
    public function infoRemboursement()
    {
        $agence = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0]->fk_agence;

        $code=$this->utils->securite_xss($_POST['code']);
        $tel=str_replace('+', '00', $this->utils->securite_xss($_POST['tel']));

        $data['infoenvoi'] =$this->transfertModel->getInfosTransfertByCode1($code,$tel,intval($agence));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(!$data['infoenvoi']){
            $params = array('view' =>'transfert/remboursement', 'title' =>$data['lang']['new_paiement'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }else{

            $params = array('view' => 'transfert/remboursementInfo');
            $this->view($params,$data);

        }


    }
    /***************** Fonction Pour valider  un remboursement *********************/
    public function validerRemboursement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $agence = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0]->fk_agence;
        $service = 28;
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais   =  $this->utils->securite_xss($_POST['frais']);
        $frais2 =  $this->transfertModel->calculFrais($service, $montant);
        $code = $this->utils->securite_xss(base64_decode($_POST['code']));
        $tel = $this->utils->securite_xss(base64_decode($_POST['tel']));
        $mtn_total=$montant + $frais2;
        $date_receive = $this->utils->securite_xss($_POST['date_reception']);
        $idtransfert = $this->utils->securite_xss( base64_decode($_POST['idtransfert']));
        $num_transac = $this->utils->generation_numTransaction();
        $date_tranfert = date('Y-m-d H:i:s');
        $agence =$this->userConnecter->fk_agence;
        $userembourse = $this->userConnecter->rowid;

        $data['infoenvoi'] = $this->transfertModel->getInfosTransfertByCode1($code,$tel,$agence);

        $nom_sender = $this->utils->securite_xss($data['infoenvoi']->nom_sender);
        $prenom_sender = $this->utils->securite_xss($data['infoenvoi']->prenom_sender);
        $type_piece_sender = $this->utils->securite_xss($data['infoenvoi']->type_piece_sender);
        $piece_sender = $this->utils->securite_xss($data['infoenvoi']->cin_sender);
        $tel_sender = $tel;
        $pays_sender = $this->utils->securite_xss($data['infoenvoi']->pays_sender);
        $ville_sender = $this->utils->securite_xss($data['infoenvoi']->ville_sender);
        $adresse_sender = $this->utils->securite_xss($data['infoenvoi']->adresse_sender);
        $nom_receiver = $this->utils->securite_xss($data['infoenvoi']->nom_receiver);
        $prenom_receiver = $this->utils->securite_xss($data['infoenvoi']->prenom_receiver);
        $tel_receiver = $this->utils->securite_xss($data['infoenvoi']->tel_receiver);
        $pays_receiver = $this->utils->securite_xss($data['infoenvoi']->pays_receiver);
        $ville_receiver = $this->utils->securite_xss($data['infoenvoi']->ville_receiver);
        $adresse_receiver = $this->utils->securite_xss($data['infoenvoi']->adresse_receiver);


        $this->getConnexion()->beginTransaction() ;

        $response = $this->transfertModel->crediter_soldeAgence($montant, $agence);
        if($response == 1){
            $statut = 1;
            $sens = 1;
            $this->transfertModel->update_statut_remboursement($idtransfert);

            //$result_transfert = $this->transfertModel->saveInfosTransfert($num_transac, $code, $montant, $frais2, $mtn_total, $date_tranfert, $nom_sender, $prenom_sender, $type_piece_sender, $piece_sender, $tel_sender, $pays_sender, $ville_sender, $adresse_sender, $nom_receiver, $prenom_receiver, $tel_receiver, $pays_receiver, $ville_receiver, $adresse_receiver, $service,$userembourse,$agence,1,2);
            $resultat_annule =  $this->transfertModel->update_statut_transfert($idtransfert,2);


            $message = $data['lang']['mess_remborsement1'] . $prenom_sender . " " . $nom_sender . $data['lang']['mess_remborsement2'] . $montant . $data['lang']['code'].":" . $code . $data['lang']['mess_transfert4'];


            $this->utils->sendSMS($data['lang']['paositra'], $tel, $message);
            $this->utils->log_journal("remboursement  du code " . $code,'', 'remboursement code', 'Remboursement', $this->userConnecter->rowid);
            $result = $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $userembourse, $statut,'Remboursement postecash OK', $frais2, $agence,0);

            $savedet1 =  $this->utils->save_DetailTransaction($num_transac, $agence, $montant, $sens);

            if (($result > 0) /*&& ($result_transfert > 0)*/ && ($resultat_annule > 0) && ($savedet1 > 0)) {
                $this->connexion->commit() ;
                $this->utils->log_journal("remboursement code " . 'remboursement code','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_OK'], 'Remboursement', $this->userConnecter->rowid);
                $id = base64_encode($num_transac);
                $this->rediriger('transfert', 'remboursement_recap/'.$id);

            } else {

                $this->connexion->rollBack() ;
                $this->utils->log_journal("remboursement code " . 'remboursement code','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_KO'], 'Remboursement', $this->userConnecter->rowid);
                $this->rediriger('Transfert','erreurrembourse/'.base64_encode(-3));
            }
        }
        else{
            $this->connexion->rollBack() ;
            $this->rediriger('Transfert','erreurrembourse/'.base64_encode(-3));
        }
    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function remboursement_recap($id)
    {
        $numtransaction  = base64_decode($id[0]);
        $data['infoenvoi'] =$this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/remboursementRecap');
        $this->view($params,$data);
    }

    /***************** Fonction Erreur remboursemnt *********************/
    public function erreurrembourse($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($id[0]==-1)){
            $data['message_transfert'] = 'Erreur web service: Donnees invalides';
        }
        if(base64_decode($id[0]==-2)){
            $data['message_transfert']  = $data['lang']['solde_agence_insuffisant'];
        }
        if(base64_decode($id[0]==-3)){
            $data['message_transfert']  = $data['lang']['error_debit_agence_source'];
        }else{
            $retour = json_decode($erreur);
            $data['message_transfert'] = $retour->{'errorMessage'};
        }
        $params = array('view' => 'transfert/rembourserror');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu remboursement  *********************/
    public function impressionRecuRemboursement()
    {
        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/transfert/recu-remboursement-cash.php';
        include("$imprime");
        $content = ob_get_clean();
        // convert in PDF
        require_once __DIR__.'/../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuRemboursementCash.pdf', 'I');
        }
        catch (HTML2PDF_exception $e)
        {
            echo $e;
            exit;
        }
    }

    public function annulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/annulation');
        $this->view($params,$data);
    }

    /***************** Fonction Pour valider  un remboursement *********************/
    public function infoAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant']);
        $code = $this->utils->securite_xss($_POST['code']);
        $tel=$this->utils->securite_xss($_POST['tel']);
        $tel = trim(str_replace("+", "00", $tel));

        $data['infoenvoi'] = $this->transfertModel->getInfosTransfertByMontant($montant, $tel, $this->userConnecter->fk_agence, $this->userConnecter->rowid, $code);

        if($data['infoenvoi']==-1){
            $params = array('view' =>'transfert/annulation', 'title' =>$data['lang']['ANNULATION'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }
        else{
            $data['frais'] =  $this->transfertModel->calculFrais($service=31, $montant);
            $params = array('view' => 'transfert/annulationInfo');
            $this->view($params,$data);
        }

    }

    /***************** Fonction Pour valider  une annulation *********************/
    public function validerAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );

        $service = 31;
        $montant = $this->utils->securite_xss($_POST['montant']);
        $code = $this->utils->securite_xss(base64_decode($_POST['code']));
        $tel = $this->utils->securite_xss(base64_decode($_POST['tel']));
        $idtransfert = $this->utils->securite_xss( base64_decode($_POST['idtransfert']));
        $num_transac = $this->utils->generation_numTransaction();
        $fk_agence = $this->userConnecter->fk_agence;
        $fk_user = $this->userConnecter->rowid;

        $data['infoenvoi'] = $this->transfertModel->getInfosTransfertByCodeIdMontant($code, $tel, $idtransfert, $montant, $fk_agence, $fk_user);

        if($data['infoenvoi'] != -1){
            $frais = $this->transfertModel->calculFrais($service, $data['infoenvoi']->montant);
            $mt_ttc = (intval($data['infoenvoi']->montant) + intval($data['infoenvoi']->frais)) - intval($frais);
            $response = $this->transfertModel->crediter_soldeAgence($mt_ttc, $fk_agence);
            if($response == 1){
                $statut = 1;
                $sens = 1;
                //commission non rembourser
                $etat = $this->transfertModel->update_statut_annulation($data['infoenvoi']->idtransfert, $fk_agence, $fk_user);

                if($etat == 1){
                    $this->utils->log_journal("annulation  du code " . $code,'', 'annulation code', 'Annulation', $fk_user);
                    $this->utils->SaveTransaction($num_transac, $service, $mt_ttc, 0, $fk_user, $statut,'Annulation transfert d\'argent OK', $frais, $fk_agence,0);
                    $id = base64_encode($this->utils->securite_xss($data['infoenvoi']->num_transac));
                    $this->rediriger('transfert', 'annulation_recap/'.$id);
                }
                else{
                    $statut = 0;
                    $this->utils->log_journal("annulation  du code " . $code,'', 'annulation code echec', 'Annulation', $fk_user);
                    $this->utils->SaveTransaction($num_transac, $service, $mt_ttc, 0, $fk_user, $statut,'Annulation transfert d\'argent KO', $frais, $fk_agence,0);

                    $this->rediriger('transfert','erreurannulation/'.base64_encode(-2));
                }
            }
            else{
                $this->rediriger('transfert','erreurannulation/'.base64_encode(-3));
            }
        }
        else{
            $this->rediriger('transfert','erreurannulation/'.base64_encode(-1));
        }

    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function annulation_recap($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );
        $numtransaction  = base64_decode($id[0]);

        $data['infoenvoi'] =$this->transfertModel->getInfosTransfert($numtransaction);
        $data['frais'] =$this->transfertModel->calculFrais($service=31, $data['infoenvoi']->montant);

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'transfert/annulationRecap');
        $this->view($params,$data);
    }

    /***************** Fonction Erreur remboursemnt *********************/
    public function erreurannulation($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $res = base64_decode($id[0]);

        if($res == "-1"){
            $data['message_transfert'] = $data['lang']['annulation_impossible'];
        }
        else if($res == "-2"){
            $data['message_transfert']  = $data['lang']['annulation_impossible'];
        }
        else if($res == "-3"){
            $data['message_transfert']  = $data['lang']['solde_insuffisant_agence_source'];
        }
        else{

            $data['message_transfert'] = $data['lang']['error_survenue'];
        }
        $params = array('view' => 'transfert/annulationerror');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu remboursement  *********************/
    public function impressionRecuAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(95,$this->userConnecter->profil) );
        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['frais'] =$this->transfertModel->calculFrais($service=31, $data['infoenvoi']->montant);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/transfert/recu-annulation-teliman.php';
        include("$imprime");
        $content = ob_get_clean();
        // convert in PDF
        require_once __DIR__.'/../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuAnnulationTeliman.pdf', 'I');
        }
        catch (HTML2PDF_exception $e)
        {
            echo $e;
            exit;
        }
    }

    public function getAllRgionByPays(){
        $pays = $this->utils->securite_xss($_POST['pays']);
        echo json_encode($this->utils->allRegionByPays($pays));
    }



    /******************** HISTORIQUE ENVOI *********************/

    public function searchHistoriqueEnvoi(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(76,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'transfert/search-historique-envoi');
        $this->view($params,$data);
    }  /******************** HISTORIQUE TRANSFERT *********************/

    public function searchHistoriqueTransfert(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(18,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->reportingModel->allAgence();
        $params = array('view' => 'transfert/search-historique-transfert');
        $this->view($params,$data);
    }


}