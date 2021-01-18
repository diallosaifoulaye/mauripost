<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');
require_once __DIR__.'/../../core/ApiGTP/ApiBAnque.php';
class TransfertController extends \app\core\FrontendController
{
    private $utils_transfert;
    private $utils_recharge;
    private $connexion;
    private $userConnecter;
    private $obj;

    public function __construct()
    {
        $this->utils_transfert = new \app\core\UtilsTransfert();
        $this->utils_recharge = new \app\core\UtilsRecharge();
        $this->connexion = \app\core\Connexion::getConnexion();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
        $this->obj = $this->getSession()->getAttribut('objconnect');

    }

    public function index()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/transfert/transfert'));
        $this->view($paramsview, $data);
    }

    public function index1()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/transfert/transfert'));
        $this->view($paramsview, $data);
    }

    /**********************************
     ******* ENVOI TRANSFERT ******
     ********************************/
    public function envoi()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/transfert/envoi'));
        $this->view($paramsview, $data);
    }


    /***************** Fonction faire un transferts *********************/
    public function AjoutEnvoie()
    {
        $agencesender =$this->obj->getFk_agence();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = $this->utils_transfert->genererCodeTransfert();
        $date_tranfert = date('Y-m-d H:i:s');
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
        $service = ID_SERVICE_TRANSFERT;
        $frais =  $this->utils->securite_xss($_POST['frais2']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $com = $this->utils->getTauxDistributeur($service);
        $fraiscom = ( $frais * (100 - $com) ) / 100;
        $mtn_total = $montant + $fraiscom;
        $montant_total = $mtn_total;
        $num_transac = $this->utils->generation_numTransaction();
        $user_sender = $this->obj->getRowid();

        $valueSource = $this->utils->getSoldeAgence($agencesender);
        if($valueSource >= $mtn_total)
        {
            $this->connexion->beginTransaction();
            $response = $this->utils->debiterSoldeAgence($mtn_total, $agencesender, $this->connexion);
            if($response == 1)
            {
                $statut = 0;
                $result_transfert = $this->utils_transfert->saveInfosTransfert($num_transac, $code, $montant, $frais, $montant_total, $date_tranfert, $nom_sender, $prenom_sender, $type_piece_sender, $piece_sender, $tel_sender, $pays_sender, $ville_sender, $adresse_sender, $nom_receiver, $prenom_receiver, $tel_receiver, $pays_receiver, $ville_receiver, $adresse_receiver, $service,$user_sender,$agencesender,0, $statut, $this->connexion);
                if($result_transfert > 0)
                {
                    $this->connexion->commit();
                    $soldeapres = $this->utils->getSoldeAgence($agencesender);
                    $this->utils->addMouvementCompteAgence($num_transac,$valueSource,$soldeapres,$montant_total,$agencesender,"DEBIT","envoi Cash Paositra", $this->connexion);
                    $result = $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $user_sender, $statut,'Envoi cash OK', $frais, $agencesender,0);
                    if($result == 1)
                    {
                        $message = $data['lang']['mess_remborsement1']. $prenom_sender." ".$nom_sender . $data['lang']['mess_autre3']. $montant .' Ar '. $data['lang']['code'] .': '. $code . " Merci.";
                        $this->utils->sendSMS("Paositra", $tel_receiver, $message);
                        $this->utils->log_journal("Envoi  cash " . 'transfert','',  $data['lang']['montant'] . ":" . $montant_total . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['envoi_ok'], 3, $this->obj->getRowid());
                        $credit_commission = $this->utils->crediterCarteCommission($fraiscom, $this->connexion);
                        if($credit_commission == 1) $this->utils->addCommission($fraiscom, $service, $agencesender, 0, "Envoi Cash Paositra");
                        else $this->utils->addCommission_afaire($fraiscom, $service, $agencesender, 0, "Envoi Cash Paositra");
                    }
                    else {
                        $this->utils->log_journal("Envoi  d'argent " . 'transfert','',  $data['lang']['montant'] . ":" . $montant_total . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_ko'], 3, $this->obj->getRowid());
                    }
                    $this->rediriger('Transfert','recapEnvoi/'.base64_encode($num_transac));
                }
                else{
                    $statut = 0;
                    $this->connexion->rollBack();
                    $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $user_sender, $statut,'Envoi cash NOK', $frais, $agencesender,0);
                    $this->rediriger('Transfert','erreurtransfert/'.base64_encode(-3));
                }
            }else{
                $statut = 0;
                $this->connexion->rollBack();
                $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $user_sender, $statut,'Envoi cash NOK', $frais, $agencesender,0);
                $this->rediriger('Transfert','erreurtransfert/'.base64_encode(-4));
            }
        }
        else{
            $this->rediriger('Transfert','erreurtransfert/'.base64_encode(-2));
        }
    }

    /***************** Fonction recaptiulative sur  un transferts *********************/
    public function recapEnvoi($id)
    {
        $num_transac=base64_decode($id[0]);
        $data['infoenvoi'] =$this->utils_transfert->getInfosTransfert($num_transac);
        if($id[1] == 'p')
        {
            $data['agence'] = $this->utils_transfert->getInfosAgenceReceiver($num_transac);
            $data['p'] = 1;
        }
        else $data['agence'] = $this->utils_transfert->getInfosAgenceSender($num_transac);

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/transfert/envoieRecap'));
        $this->view($paramsview, $data);
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
        if(base64_decode($id[0]==-4)){
            $data['$message_transfert']  = 'Erreur debit compte';
        }
        if(base64_decode($id[0]==-3)){
            //$data['$message_transfert']  = 'Une erreur est survenue lors du debit de l\'agence, veuillez reessayer';
            $data['$message_transfert']  = 'Une erreur est survenue lors de la transaction, veuillez reessayer';
        }else{
            $retour = json_decode($erreur);
            $data['$message_transfert'] = $retour->{'errorMessage'};
        }

        $paramsview = array('view' => sprintf('frontend/transfert/erreurtransfert'));
        $this->view($paramsview, $data);
    }

    /***************** Fonction d'impression des recu *********************/
    public function impressionRecuEnvoie()
    {

        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->utils_transfert->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(isset($_POST['paiement']) && $this->utils->securite_xss($_POST['paiement']) ==='ok')
        {
            $data['effectuerpar'] = $this->utils_transfert->getInfosAgentReceiver($numtransaction);
            $data['agence'] = $this->utils_transfert->getInfosAgenceReceiver($numtransaction);
            $data['titre'] = $data['lang']['recu_reception_pc'];
        }
        else
        {
            $data['effectuerpar'] = $this->utils_transfert->getInfosAgentSender($numtransaction);
            $data['agence'] = $this->utils_transfert->getInfosAgenceSender($numtransaction);
            $data['titre'] = $data['lang']['TITRE_RECU_ENVOI'];
        }

        //get the HTML
        ob_start();
        $imprime =__DIR__.'/../../views/frontend/transfert/recu-envoi-cash.php';


        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
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
           return -2;
        }
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function getFrais()
    {
        $service =$this->utils->securite_xss($_POST['service']);
        $montant =$this->utils->securite_xss($_POST['montant']);
        echo $this->utils_transfert->calculFraisab($service,$montant);;
    }

    public function detailsenvoi($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['envoi'] = $this->utils_transfert->getDetailsEnvoi($id[0], $user_creation);
        $paramsview = array('view' => sprintf('frontend/transfert/details-envoi'));
        $this->view($paramsview, $data);
    }

    public function imprimeRecuEnvoi($id){
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['agence'] = $obj->getFk_agence();
        $data['effectuerpar'] = $obj->getPrenom()." ".$obj->getNom();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $user_creation = $obj->getRowid();
        $data['envoi'] = $this->utils_transfert->getDetailsEnvoi($id[0], $user_creation);
        ob_start();
        $imprime = __DIR__.'/../../views/frontend/transfert/recu-envoi-cash.php';
        include("$imprime");
        $content = ob_get_clean();
        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuEnvoiCash.pdf', 'I');
        }
        catch (HTML2PDF_exception $e)
        {
            return -2;
        }
    }

    /**********************************
     ******* PAIEMENT TRANSFERT ******
     ********************************/
    public function paiement($return)
    {
            if(base64_decode($return[0])=='nok')
            {
                $data['return']=-1;
            }
            elseif (base64_decode($return[0])=='nok1')
            {
                $data['return']=-3;
            }
            $data['lang'] = $this->lang->getLangFile($this->session->getAttribut('lang'));
            $obj = $this->getSession()->getAttribut('objconnect');
            $user_creation = $obj->getRowid();
            $data['transfert'] = $this->utils_transfert->getLastPaiement($user_creation);
            $paramsview = array('view' => sprintf('frontend/transfert/search-paiement'));
            $this->view($paramsview, $data);
    }

    /**********************************
     ******* PAIEMENT TRANSFERT ******
     ********************************/
    public function detailpaiement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));
        $code = trim($this->utils->securite_xss($_POST['code']));
        $data['typepiece'] = $this->utils->typepiece();

        $data['transfert'] = $this->utils_transfert->detailTransfert($telephone, $code, $user_creation);
        if( $data['transfert']==-1 || $data['transfert']==-2)
        {
            $this->rediriger('transfert','paiement/'.base64_encode('nok'));
        }
        elseif ($data['transfert']==-3)
        {
            $this->rediriger('transfert','paiement/'.base64_encode('nok1'));
        }
        else
        {
            $paramsview = array('view' => sprintf('frontend/transfert/paiement'));
            $this->view($paramsview, $data);
        }
    }

    /*********Validation Paiement Transfert ********/
    public function paiementValidation()
    {
        $code1 = $this->utils->securite_xss($_POST['code1']);
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['ben_phone'])));
        $date_delivrance = $this->utils->securite_xss($_POST['datedelpiece_benef']);
        $type_piece = $this->utils->securite_xss($_POST['typepiece_benef']);
        $piece = trim(str_replace("_", "",$this->utils->securite_xss($_POST['numpiece_benef'])));
        $date_expiration = $this->utils->securite_xss($_POST['datefinpiece_benef']);
        $idtransfert = $this->utils->securite_xss($_POST['idtransfert']);
        $date_receive = date('Y-m-d H:i:s');
        $user_creation = $this->obj->getRowid();
        $agence = $this->obj->getFk_agence();
        $commentaire = 'Paiement Transfert';
        $envoi = $this->utils_transfert->detailTransfert($telephone,$code1,$user_creation );

        if($envoi != -1 && $envoi != -2)
        {
            $this->connexion->beginTransaction();
            $update=$this->utils_transfert->updateInfosPaiement($user_creation, $agence, $date_receive, $type_piece, $piece, $idtransfert, $date_delivrance, $date_expiration, $envoi->fk_service, $envoi->frais, $this->connexion);
            if($update ==1 )
            {
                $soldeAgence =$this->utils->getSoldeAgence($agence);
                $crediter = $this->utils->crediter_soldeAgence($envoi->montant, $agence, $this->connexion);
                if($crediter == 1)
                {
                    $this->connexion->commit();
                    $soldeAgenceApres=$this->utils->getSoldeAgence($agence);
                    $this->utils->addMouvementCompteAgence($envoi->num_transac, $soldeAgence, $soldeAgenceApres, $envoi->montant, $agence, 'CREDIT', $commentaire, $this->connexion);
                    $this->utils->log_journal('Paiement', 'Code validation:' . $envoi->code .'Téléphone beneficiaire:' . $telephone . ' Montant:' . $envoi->montant . ' Frais:' . $envoi->frais . ' Numtransact:' . $envoi->numtransact, 'SUCCESS', 3, $user_creation);
                    $this->rediriger('transfert','validationTransfert/'.base64_encode('ok').'/'.base64_encode($envoi->num_transac));
                }
                else{
                    $this->connexion->rollBack();
                    $this->rediriger('transfert','validationTransfert/'.base64_encode('nok2'));
                }
            }
            else
            {
                $this->connexion->rollBack();
                $this->rediriger('transfert','validationTransfert/'.base64_encode('nok2'));
            }
        }
        else
        {
            $this->rediriger('transfert','validationTransfert/'.base64_encode('nok1'));
        }
    }

    /***********Validation Transfert Paiement**********/
    public function validationTransfert($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0]) === 'ok')
        {
            $data['numtransact'] = base64_decode($return[1]);
            $params = array('view' =>'frontend/transfert/paiement-fin', 'title' =>$data['lang']['paiement_transfert'], 'alert'=>$data['lang']['message_success_paiement_transfert'], 'type-alert'=>'alert-success');
        }
        else if(base64_decode($return[0]) === 'nok1')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'frontend/transfert/paiement-fin', 'title' =>$data['lang']['paiement_transfert'], 'alert'=>$data['lang']['message_echec_paiement_transfert'] , 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok2')
        {
            $params = array('view' =>'frontend/transfert/paiement-fin', 'title' =>$data['lang']['paiement_transfert'], 'alert'=>$data['lang']['message_echec_paiement_transfert'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu *********************/
    public function impressionRecu()
    {
        $numtransaction  = $this->utils->securite_xss($_POST['numtransact']);
        $obj = $this->getSession()->getAttribut('objconnect');

        $data['infoenvoie'] =  $this->utils_transfert->getInfosTransfert($numtransaction);
        $data['agence'] = $obj->getFk_agence();
        $data['effectuerpar'] = $obj->getPrenom()." ".$obj->getNom();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../../views/frontend/transfert/recu-transfert-cash.php';
        include("$imprime");
        $content = ob_get_clean();
        // convert in PDF
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
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
           return -2;
            exit;
        }
    }

    /***************** Fonction historique *********************/
    public function historiqueReception()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['user']= $obj->getRowid();
        $params = array('view' => 'frontend/transfert/historiquereception');
        $this->view($params,$data);
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function envoidataRecep($id)
    {
        $user1= $this->utils->securite_xss($id[0]);
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
        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,u.prenom_sender,u.nom_sender,u.prenom_receiver,u.nom_receiver,u.montant,u.statut,u.idtransfert";
        $sql.=" FROM tranfert as u";

        if($admin!=1){
            $sql.=" WHERE u.user_receiver = :user AND fk_service <> 28";
        }
        $user = $this->connexion->prepare($sql);
        //$query=mysqli_query($conn, $sql) or die("agence-grid-data.php: get employees");

        $user->execute(
            array(
                "user" => $user1,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT u.date_tranfert,u.num_transac,u.num_transac,u.code,u.prenom_sender,u.nom_sender,u.prenom_receiver,u.nom_receiver,u.montant,u.statut,u.idtransfert";
        $sql.=" FROM tranfert as u";
        $sql.=" WHERE 1=1";
        if( $admin!=1){
            $sql.=" AND fk_service <> 28 AND u.user_receiver = :user";
        }
        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( u.date_tranfert LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.num_transac LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR u.code LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_sender LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.prenom_receiver LIKE '%".$requestData['search']['value']."%' ";

            $sql.=" OR u.montant LIKE '%".$requestData['search']['value']."%' )";
        }
        $user = $this->connexion->prepare($sql);
        if($admin!=1){
            $user->execute(
                array(
                    "user" => $user1,
                )
            );
        }else{
            $user->execute();
        }
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "user" => $user1,
            )
        );
        $rows = $user->fetchAll();

        \app\core\Connexion::closeConnexion();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if($row["statut"]==1)
                $libelle = '<span style="color:red;">rétiré</span>';
            if($row["statut"]==0)
                $libelle = '<span style="color:green;">En cours</span>';

            $nestedData[] = $row["date_tranfert"];
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $row["code"];
            $nestedData[] = $row["prenom_sender"].' '.$row["nom_sender"];
            $nestedData[] = $row["prenom_receiver"].' '.$row["nom_receiver"];
            $nestedData[] = $row["montant"];
            $nestedData[] = $libelle;

            $nestedData[] = "<a  href='". ROOT.'transfert/paiementrecap/'.base64_encode($row["num_transac"])."'><i class='fa fa-search'></i></a>";

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

    /***************** Fonction recaptiulative sur  un transferts *********************/
    public function paiementrecap($id)
    {
        $num_transac=base64_decode($id[0]);
        $data['infoenvoi'] =$this->utils_transfert->getInfosTransfert($num_transac);
        $obj = $this->getSession()->getAttribut('objconnect');
        $user = $obj->getRowid();
        $data['agence'] = $obj->getLabel();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'frontend/transfert/paiementrecap');
        $this->view($params,$data);
    }

    public function getAllRgionByPays(){
        $pays = $this->utils->securite_xss($_POST['pays']);
        echo json_encode($this->utils->allRegionByPays($pays));
    }
}