<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');

class ApiJirama {
    private $marchand;
    private $key;
    private $url;
    private $connexion;
    function __construct() {
        $this->connexion = \app\core\Connexion::getConnexion();
        $this->marchand = MARCHAND_JIRAMA ;
        $this->key = KEY_WS_JIRAMA ;
        $this->url = URL_WS_JIRAMA ;
    }

    public function getToken()
    {

        $data = [
            "marchand" => $this->marchand,
            "key" => $this->key
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.'getToken');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); // requis à partir de PHP 5.6.0
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return json_decode($result);
    }

    function callFunction($nomMethodeToCall,$data){
        $token = $this->getToken()->token;
        $headers = [
            "token:$token"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $nomMethodeToCall);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); // requis à partir de PHP 5.6.0
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        //var_dump($result); exit;
        return json_decode($result);
    }
}


class FacturierController extends \app\core\FrontendController
{
    private $utils_facturier;
    private $connexion;

    public function __construct()
    {
        $this->utils_facturier = new \app\core\UtilsFacturier();
        $this->connexion = \app\core\Connexion::getConnexion();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
    }


    public function dashbord()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/facturier/paiementDashboard'));
        $this->view($paramsview, $data);
    }


    /**********************************
     *
     * BOITE POSTALE
     */

    public function paiementpostale()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offres'] = $this->utils_facturier->OffresPostales();
        $data['agences'] = $this->utils_facturier->allAgence();
        $data['benef'] = $this->utils_facturier->allBenef();
        $data['period'] = $this->utils_facturier->allperiodicite();
        $data['last_Paiement'] = $this->utils_facturier->getLastPaiementPostal($user_creation);
        $error = base64_decode($this->utils->securite_xss($_GET['data']));

        if($error == 'ok')
        {
            $data['result_operation'] = ['alert-success', 'Paiement effectué avec succes !!'];
        } elseif ($error == 'nok') {
            $data['result_operation'] = ['alert-danger', 'Echec du paiement!!'];
        } elseif ($error == 'nokSolde'){
            $data['result_operation'] = ['alert-danger', 'Votre solde est insuffisante pour effectuer cette opération!!'];
        }

        $paramsview = array('view' => sprintf('frontend/facturier/new-paie-BP'));
        $this->view($paramsview, $data);
    }



    public function getboitepostale()
    {
        $data['bp'] = $this->utils_facturier->getBP($this->utils->securite_xss($_POST['benef']));
        print_r(json_encode($data['bp'])) ;
    }


    public function calculmnt()
    {
        $of=explode("_",$this->utils->securite_xss($_POST['offp']));
        $data['$mntof'] = $this->utils_facturier->getMntOffModel($of[0]);
        $data['$nbremois'] = $this->utils_facturier->getNbreMoisModel($of[1]);
        $mnt=($data['$mntof']['montant']*$data['$nbremois']['nombre_mois']);
        echo $mnt;
    }


    public function insertBpnew()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $fk_agence = $obj->getFk_agence();
        $service = ID_SERVICE_BP;
        $_POST['user_creation'] = $user_creation;
        $_POST['fk_agence'] = $fk_agence;
        $data['idbp'] = $this->utils_facturier->getIdbpModel($_POST['boite_postale']);
        unset($_POST['fk_beneficiaire_postale']);
        unset($_POST['boite_postale']);
        $_POST['fk_boite_postale'] = $data['idbp']['id'];
        $solde = $this->utils->getSoldeAgence($fk_agence);
        $montant = $this->utils->securite_xss($_POST['montant_paye']);
        $taux = $this->utils->getTauxDistributeur($service);
        $frais = $this->utils->calculFrais($service, $montant);
        $montant_frais_commission = ($frais * (100 - floatval($taux))/100);
        $solde_minimal = floatval($montant) + $montant_frais_commission;

        if($solde>=$solde_minimal)
        {
            $this->connexion->beginTransaction();
            $numtransact = $this->utils->Generer_numtransaction();
            $result_debit = $this->utils->debiterSoldeAgence($solde_minimal, $fk_agence, $this->connexion);
            if($result_debit == 1)
            {
                $result_AB = $this->utils->crediterAbonnementPostal($montant, $this->connexion);
                if($result_AB == 1)
                {
                    $insert = $this->utils_facturier->insertBpNew($this->utils->securite_xss_array($_POST));
                    if ($insert == 1)
                    {
                        $commentaire = 'Paiement abonnement postale';
                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_creation, $statut = 1, $commentaire , $frais, $fk_agence, $transactId = 0);
                        $this->connexion->commit();
                        $soldeapres = $this->utils->getSoldeAgence($fk_agence);
                        $operation = "DEBIT";
                        $this->utils->addMouvementCompteAgence($numtransact, $solde, $soldeapres, $solde_minimal, $fk_agence, $operation, $commentaire, $this->connexion);
                        $result_CC = $this->utils->crediterCarteCommission($montant_frais_commission, $this->connexion);
                        if($result_CC == 1)
                        {
                            $this->utils->addCommission($montant_frais_commission, $service, $fk_agence, 0, $commentaire);
                        }
                        else{
                            $this->utils->addCommission_afaire($montant_frais_commission, $service, $fk_agence, 0, $commentaire);
                        }
                        $this->utils->log_journal('Ajout abonnement postale', 'beneficiaire_postale:' .$this->utils->securite_xss($_POST['fk_beneficiaire_postale']) . ' annee:' . $this->utils->securite_xss($_POST['annee']) , 'succes', 10, $user_creation);
                        $this->rediriger('facturier', 'paiementpostale?data=' . base64_encode('ok'));
                    }
                    else {
                        $statut_log = "ECHEC";
                        $commentaire = 'Echec lors de l\'ajout d\'un nouveau abonnement';
                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_creation, $statut = 0, $commentaire , $frais, $fk_agence, $transactId = 0);
                        $this->utils->log_journal('Ajout abonnement postale', $commentaire, $statut_log, 10, $user_creation);
                        $this->connexion->rollBack();
                        $this->rediriger('facturier', 'paiementpostale?data=' . base64_encode('nok'));
                    }
                }
                else {
                    $statut_log = "ECHEC";
                    $commentaire = 'Echec lors de l\'opération: Créditer Carte Commission ou Créditer Abonnement Postal';
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_creation, $statut = 0, $commentaire , $frais, $fk_agence, $transactId = 0);
                    $this->utils->log_journal('Ajout abonnement postale', $commentaire, $statut_log, 10, $user_creation);
                    $this->connexion->rollBack();
                    $this->rediriger('facturier', 'paiementpostale?data=' . base64_encode('nok'));
                }
            }
            else {
                $statut_log = "ECHEC";
                $commentaire = 'Echec lors de l\'opération: Débiter Solde Agence';
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_creation, $statut = 0, $commentaire , $frais, $fk_agence, $transactId = 0);
                $this->utils->log_journal('Ajout abonnement postale', $commentaire, $statut_log, 10, $user_creation);
                $this->connexion->rollBack();
                $this->rediriger('facturier', 'paiementpostale?data=' . base64_encode('nok'));
            }
        }
        else {
            $statut_log = "ECHEC";
            $commentaire = 'Echec: Solde de l\'agence insuffisante !!';
            $this->utils->log_journal('Ajout abonnement postale', $commentaire, $statut_log, 10, $user_creation);
            $this->rediriger('facturier', 'paiementpostale?data=' . base64_encode('nokSolde'));
        }
    }


    public function Histo_paiement_postal(){
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/facturier/Histo_paiement_postal'));
        $this->view($paramsview, $data);
    }


    public function processingReservation()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();


        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_paiement',
            1=> 'nom_complet',
            2=> 'tel',
            3=> 'montant_paye'
        );


        $sql = "Select a.date_paiement, a.montant_paye,bp.nom_complet, bp.tel
                FROM abonnement_postale as a 
                INNER JOIN boite_postale as b ON b.id = a.fk_boite_postale
                INNER JOIN beneficiaire_postale as bp ON bp.id = b.fk_beneficiaire_postale";

        $sql.=" WHERE a.user_creation= :user_crea";

        $user = $this->connexion->prepare($sql);
        //$query=mysqli_query($conn, $sql) or die("agence-grid-data.php: get employees");

        $user->execute(
            array(
                "user_crea" => $user_crea,
            )
        );

        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.



        $sql = "Select a.date_paiement, a.montant_paye,bp.nom_complet, bp.tel
                FROM abonnement_postale as a 
                INNER JOIN boite_postale as b ON b.id = a.fk_boite_postale
                INNER JOIN beneficiaire_postale as bp ON bp.id = b.fk_beneficiaire_postale";
        $sql.=" WHERE a.user_creation= :user_crea";

        if( !empty($requestData['search']['value']) ) {

            $sql.=" WHERE ( a.date_paiement LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR bp.nom_complet LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  bp.tel LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  a.montant_paye LIKE '%".$_REQUEST['search']['value']."%' )";
        }

        $user = $this->connexion->prepare($sql);

        $user->execute(
            array(
                "user_crea" => $user_crea,
            )
        );


        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();
        //$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        /* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */

        $user = $this->connexion->prepare($sql);

        $user->execute(
            array(
                "user_crea" => $user_crea,
            )
        );

        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_paiement"]);
            $nestedData[] = $row["nom_complet"];
            $nestedData[] = $row["tel"];
            $nestedData[] = $this->utils->number_format($row["montant_paye"]);
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

    /**********************************
     *
     * jirama
     */


    public function paiementjirama()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/facturier/jirama'));
        $this->view($paramsview, $data);
    }


    /**
     *
     */
    public function paiementBill()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $fk_agence = $obj->getFk_agence();
        $num_transac = $this->utils->Generer_numtransaction();
        $vNUM_SESSION = $num_transac;
        $vnum_fact = $this->utils->securite_xss($_POST['vnum_fact']);
        $vref_client = $this->utils->securite_xss($_POST['vref_client']);
        $vdate_paie = $this->utils->securite_xss($_POST['vdate_paie']);
        $vtel_client = $this->utils->securite_xss($_POST['vtel_client']);
        $vmontant = $this->utils->securite_xss($_POST['vmontant']);
        $vtel_jirama = $this->utils->securite_xss($_POST['vtel_jirama']);
        $service = ID_SERVICE_JIRAMA;
        $solde = $this->utils->getSoldeAgence($fk_agence);
        $taux = $this->utils->getTauxDistributeur($service);
        $frais = $this->utils->calculFrais($service);
        $montant_frais_commission = ($frais * (100 - floatval($taux))/100);
        $solde_minimal = floatval($vmontant) + $montant_frais_commission;

        $data = [
            "marchand" => MARCHAND_JIRAMA,
            "key" => KEY_WS_JIRAMA,
            "vNUM_SESSION" => $vNUM_SESSION,
            "vnum_fact" => $vnum_fact,
            "vref_client" => $vref_client,
            "vdate_paie" => $vdate_paie,
            "vtel_client" => $vtel_client,
            "vmontant" => $vmontant,
            "vtel_jirama" => $vtel_jirama
        ];

        if($solde >= $solde_minimal)
        {
            $donneesRetour = $this->apiJirama->callFunction('payingBill',$data);
            $resultat = $donneesRetour->result ;
            $paiementReussi = 'PAIEMENT REUSSI';
            if ($resultat == $paiementReussi)
            {
                $this->utils->log_journal("Facture jirama " . $vnum_fact,'', 'appel réussi', 10, $this->userConnecter->rowid);
                $this->connexion->beginTransaction();
                $response = $this->utils->debiterSoldeAgence($solde_minimal, $fk_agence);
                if($response == 1)
                {
                        $result_paiement = $this->utils->saveInfosPaiement($num_transac, $vNUM_SESSION, $vnum_fact, $vref_client, $vdate_paie, $vtel_client, $vtel_jirama, $vmontant, $frais, $user_creation, $fk_agence, $statut=0 );
                        $result = $this->utils->SaveTransaction($num_transac, $service, $vmontant, 0, $user_creation, $statut,'Paiement facture jirama', $frais, $fk_agence,0);
                        $operation = "DEBIT";
                        $commentaire = "Payement JIRAMA";

                        if($result_paiement == 1 && $result == 1)
                        {
                            $result_CJ = $this->utils->crediterCarteCommissionJirama($vmontant);
                            if($result_CJ == 1)
                            {
                                $this->connexion->commit();
                                $soldeapres = $this->utils->getSoldeAgence($fk_agence);
                                $this->utils->addMouvementCompteAgence($num_transac, $solde, $soldeapres, $vmontant, $fk_agence, $operation, $commentaire);
                                $result_CC = $this->utils->crediterCarteCommission($montant_frais_commission);
                                if($result_CC  > 0)
                                {
                                    $result_addCom = $this->utils->addCommission($montant_frais_commission,$service,$fk_agence," ","Paiement facture jirama");
                                    if($result_addCom > 0)
                                    {
                                        $statut = "SUCCES";
                                        $this->utils->log_journal("Paiement facture jirama " . 'jirama','',  $data['lang']['montant'] . ":" . $vmontant . " " . $data['lang']['facture'] . ":" . $vnum_fact . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['paiement_ok'], 10, $user_creation);
                                    }
                                } else {
                                    $statut = "SUCCES";
                                    $this->utils->addCommission_afaire($montant_frais_commission,$service,$fk_agence," ", "Paiement facture jirama");
                                    $this->utils->log_journal("Paiement facture jirama " . 'jirama','',  $data['lang']['montant'] . ":" . $vmontant . " " . $data['lang']['facture'] . ":" . $vnum_fact . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['creditCommAFaire'], 10, $user_creation);
                                }
                                echo json_encode(array('code'=>1,'message'=>"Paiement effectué avec succés",'num_transac'=>base64_encode($num_transac)));

                            }
                            else {
                                $statut = "ECHEC";
                                $message = $data['lang']['message_jirama_error'];
                                $this->connexion->rollBack();
                                $this->utils->log_journal("Paiement facture jirama " . 'jirama','',  $data['lang']['montant'] . ":" . $vmontant . " " . $data['lang']['facture'] . ":" . $vnum_fact . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['creditComm'], 10, $user_creation);
                                echo json_encode(array('code'=>-1,'message'=>'ECHEC PAIMENT: '.$message));
                            }
                        }
                        else{
                            $statut = "ECHEC";
                            $message = $data['lang']['message_jirama_error'];
                            $this->connexion->rollBack();
                            $this->utils->log_journal("Paiement facture jirama " . 'jirama','',  $data['lang']['montant'] . ":" . $vmontant . " " . $data['lang']['facture'] . ":" . $vnum_fact . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['saveInfos_Paiement_&_Transac'], 10, $user_creation);
                            echo json_encode(array('code'=>-1,'message'=>'ECHEC PAIMENT: '.$message));
                        }
                } else {
                    $statut = "ECHEC";
                    $message = $data['lang']['message_jirama_error'];
                    $this->connexion->rollBack();
                    $this->utils->log_journal("Paiement facture jirama " . 'jirama','',  $data['lang']['montant'] . ":" . $vmontant . " " . $data['lang']['facture'] . ":" . $vnum_fact . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['echecDebitSolde'], 10, $user_creation);
                    echo json_encode(array('code'=>-1,'message'=>'ECHEC PAIMENT: '.$message));
                }
            } else {
                //Log: appel paiement webservice jirama successful
                $this->utils->log_journal("Facture jirama " . $vnum_fact,'', 'echec appel', 10, $user_creation);
                $codeRetour = str_replace(' ', '', $donneesRetour->result->error_code);
                $message = '';
                switch ($codeRetour) {
                    case 'P0000':
                        $message = 'facture déjà payée' ;
                        break;
                    case 'P0001':
                        $message = 'Numéro Facture vide' ;
                    case 'P0002':
                        $message = 'Net dans la base différente de montant saisi' ;
                        break;
                    case 'P0003':
                        $message = 'Insertion dans la base jirama (non effectuée par des raisons quelconques)' ;
                        break;
                    case 'P0004':
                        $message = 'Facture non trouvée' ;
                        break;
                }
                //Faudra personnaliser les messages d'erreur
                echo json_encode(array('code'=>-1,'message'=>'ECHEC PAIMENT: '.$message));
            }
        }
    }

    public function detailBill()
    {
        $vnum_fact = $this->utils->securite_xss($_POST['vnum_fact']);
        $vRefenca = $this->utils->securite_xss($_POST['vRefenca']);
        $telcli = $this->utils->securite_xss($_POST['telcli']);
        $service = ID_SERVICE_JIRAMA;

        $data = [
            "marchand" => MARCHAND_JIRAMA,
            "key" => KEY_WS_JIRAMA,
            "vnum_fact" => $vnum_fact,
            "vRefenca" => $vRefenca,
            "telcli" => str_replace("+", "00", $telcli)
        ];

        $donneesRetour = $this->apiJirama->callFunction('checkBill',$data);
        $resultat = $donneesRetour->result ;

        $montant = $donneesRetour->result->momtant_total ;
        $errorCode = $donneesRetour->errorCode ;
        $error = $donneesRetour->error ;

        $message ='' ;

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        //Check Facture  avec succés
        if(($errorCode == 0) && ($montant !=null)){
            //echo 1 ; exit;
            $data['detailFacture'] = $resultat ;
            $data['montant'] = $montant ;
            $frais = $this->utils->calculFrais($service); ;

            echo json_encode(array('code'=>1,'result'=>$resultat,'montant'=>$montant, 'frais'=>json_decode($frais))) ;
        }elseif($error != null){
            //Token invalide
            $message = $donneesRetour->message ;
            echo json_encode(array('code'=>-1,'message'=>$message));

        }else{
            // echo 3 ; exit;
            $message='WEBSERVICE';
            $codeRetour = $donneesRetour->result->num_fact ;
            switch ($codeRetour) {
                case 'N0001':
                    $message = 'numéro facture et compte encaissement vides' ;
                    break;
                case 'N0002':
                    $message = 'Compte encaissement vide' ;
                case 'N0003':
                    $message = 'numéro facture vide' ;
                    break;
                case 'N0004':
                    $message = 'Facture non trouvée' ;
                    break;
                case 'N0000':
                    $message = 'Facture déjà payer' ;
                    break;
            }
            echo json_encode(array('code'=>-2,'message'=>$message));
        }
    }

}