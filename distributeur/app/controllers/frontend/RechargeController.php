<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');

require_once __DIR__.'/../../../vendor/ApiGTP/ApiBanque.php';
require_once __DIR__.'/../../../vendor/API_Numherit/ApiComptes.php';

class RechargeController extends \app\core\FrontendController
{
    private $utils_recharge;
    private $connexion;
    private $api_gtp;
    private  $api_numherit;

    public function __construct()
    {
        $this->utils_recharge = new \app\core\UtilsRecharge();
        $this->connexion = \app\core\Connexion::getConnexion();
        $this->api_gtp = new ApiBAnque();
        $this->api_numherit = new \vendor\API_Numherit\ApiComptes();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
    }

    /****************************TOKEN PARTENAIRE*****************************************/
    public function getToken($id)
    {
        try {
            $sql = "SELECT token FROM authToken WHERE userId =:userId";
            $user = $this->connexion->prepare($sql);
            $user->execute(array("userId" => $id,));
            $a = $user->fetch();
            return $a['token'];
        } catch (\PDOException $e) {
            return -1;
        }
    }

    public function index()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/rechargementretrait/rechargementretrait'));
        $this->view($paramsview, $data);
    }

    public function index1()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/rechargementretrait/rechargementretrait'));
        $this->view($paramsview, $data);
    }

    /***************************************
     ******** RECHARGEMENT *****************
     ***************************************/
    public function rechargement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['transaction'] = $this->utils_recharge->transactionByrowid($user_creation);
        $fkcarte = $data['transaction']->fk_carte;
        $data['benef'] = $this->utils_recharge->beneficiaireBycarte($fkcarte);
        $paramsview = array('view' => sprintf('frontend/rechargementretrait/rechargement'));
        $this->view($paramsview, $data);
    }

    public function Histo_rechargeEspeceCarte()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/rechargementretrait/histo-recharge-espece'));
        $this->view($paramsview, $data);
    }

    public function listrecharge()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $num = $obj->getRowid();
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $duplicata = $data['lang']['duplicata'];

        $requestData= $_REQUEST;

        $columns = array(
        // datatable column index  => database column name
            0=> 'date_transaction',
            1=> 'num_transac',
            2=> 'fk_carte',
            3=> 'commission',
            4=> 'service',
            5=> 'montant'
        );

        // getting total number records without any search
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label as service, 
            transaction.date_transaction, user.prenom, user.nom,carte.telephone
            FROM transaction, service, user,carte
            WHERE transaction.statut = 1
            AND service.etat = 1
            AND transaction.fkuser = :num  
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_service = ".ID_SERVICE_RECHARGE_ESPECE."
            AND transaction.fk_carte= carte.rowid
            AND transaction.fk_service = service.rowid
            AND transaction.fkuser = user.rowid";
        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "num" => $num,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label as service, 
            transaction.date_transaction, user.prenom, user.nom,carte.telephone
            FROM transaction, service, user,carte
            WHERE transaction.statut = 1
            AND service.etat = 1
            AND transaction.fkuser = :num  
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_service = ".ID_SERVICE_RECHARGE_ESPECE."
            AND transaction.fk_service = service.rowid
            AND transaction.fk_carte= carte.rowid
            AND transaction.fkuser = user.rowid";
        if( !empty($requestData['search']['value']) ) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND (transaction.num_transac LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.date_transaction LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.montant LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.commission LIKE '%".$_REQUEST['search']['value']. "%' )";
        }

        $user = $this->connexion->prepare($sql);

        $user->execute(
            array(
                "num" => $num,
            )
        );
        //var_dump($sql);exit;
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();
        //$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        /* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */
//var_dump($sql);exit;
        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "num" => $num,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($row["montant"]);
            $nestedData[] = $this->utils->number_format($row["commission"]);
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = "<a href='".ROOT."recharge/recuRechargementEspece1/".base64_encode($row["num_transac"])."/".base64_encode($row['fk_carte'])." ' title='".$duplicata."' target='new'>
                    <input name='duplicata' type='button' class='btn btn-info btn-rounded' value='".$duplicata."'  />
                    </a>

                    ";

            $data[] = $nestedData;
        }


        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        echo json_encode($json_data);
    }

    public function rechargeEspeceCarte()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $user_creation = $obj->getRowid();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $telephone = trim(str_replace(" ", "",$telephone));
        $data['benef'] = $this->utils_recharge->beneficiaireByTelephone1($telephone);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($agence);
        $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte');
        $this->view($params, $data);
    }

    /******* Action calcul Frais Recharge ****/
    public function calculFrais()
    {
        $mtt = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils_recharge->calculTaxe($this->utils->securite_xss($_POST['montant']));
        if($frais == 0.01){
            $f = $mtt*$frais;
            echo $f;
        }
        else if($frais == 0){
            echo 0;
        }
        else{
            echo $frais;
        }
    }

    /******* Action calcul Frais Retrait ****/
    public function calculFraisRetrait()
    {
        $mtt = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils_recharge->calculFrais(ID_SERVICE_CASHOUTPARTENAIRE, $mtt);
        if($frais >= 0) echo $frais;
        else echo 0;
    }

    /******* Action verifier code rechargement ****/
    public function codeRechargement()
    {
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fk_agence = $this->utils->securite_xss($_POST['fkagence']);
        $frais = $this->utils_recharge->verifCodeRechargement($fk_agence, $code_secret);
        if($frais == 1) echo 1;
        elseif($frais == 0) echo 0;
        else echo -2;
    }

    /*********Recharge Espece Code Validation********/
    public function rechargeEspeceCodeValidation()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeAgence'] = $this->utils->getSoldeAgence($agence);
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['fkagence'] = $obj->getFk_agence();
        if($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '' )
        {
            $recup_mail = $this->utils->recup_mail($data['fkagence']);
            $recup_tel = $this->utils->recup_tel($data['fkagence']);
            $code_recharge = $this->utils->generateCodeRechargement($data['fkagence']);
            if($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '')
            {
                $message = $data['lang']['mess_recharge_espece1'] . $code_recharge . $data['lang']['mess_recharge_espece2'] . $this->utils->number_format($data['montant']) . $data['lang']['currency'].' '.$data['lang']['tel'].':'.$data['telephone'];
               @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);
                $this->utils->envoiCodeRechargement($recup_mail,'' , $code_recharge, $data['montant'], $data['telephone'], $data['lang']);
            }
        }

        $this->rediriger('recharge', 'validationRechargeEspeceCodeValidation/'  . base64_encode($data['telephone']) . '/' . base64_encode($data['soldeAgence']) .'/' . base64_encode($data['montant']). '/' . base64_encode($data['frais']). '/' . base64_encode($data['fkcarte']));

    }

    public function validationRechargeEspeceCodeValidation($return)
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($return[0]);
        $data['soldeAgence'] = base64_decode($return[1]);
        $data['montant'] = base64_decode($return[2]);
        $data['frais'] = base64_decode($return[3]);
        $data['fkcarte'] = base64_decode($return[4]);
        $data['fkagence'] = $obj->getFk_agence();
       // var_dump($data['telephone']."soldeAgence".$data['soldeAgence']."soldecarte".$data['soldeCarte']."montant".$data['montant']."frais".$data['frais']);exit();
        $params = array('view' => 'frontend/rechargementretrait/recharge-espece-code-validation');
        $this->view($params, $data);
    }
    /********* Recharge Espece Carte Beneficiaire Validation ********/
    public function rechargeEspeceValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $montant = $this->utils->securite_xss($_POST['montant']);
       // var_dump($montant);exit;
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $service = ID_SERVICE_RECHARGE_ESPECE;
        $frais = $this->utils_recharge->calculFrais($service, $montant);
        $numtransact = $this->utils->Generer_numtransaction();
        $statut = 0;
        $commentaire = 'RECHARGE ESPECE';

        if($codevalidation != '' && $telephone != '' && $montant > 0 && $frais > 0  && strlen($numtransact) == 15)
        {
            $soldeAgence = $this->utils->getSoldeAgence($agence);

            $tauxCommision = $this->utils->getTauxDistributeur($service, $fkagence) / 100;

            $comAgence = ((int)$frais)*$tauxCommision;
            $part_paositra = round($frais - $comAgence);
            $montant_total = round($montant+($frais - $comAgence));

            if($soldeAgence >= $montant_total)
            {
                $codeValidation = $this->utils->rechercherCoderechargement($codevalidation, $fkagence);
                if($codeValidation > 0)
                {
                    $typecompte = $this->utils_recharge->getTypeCompte($telephone);
                    if($typecompte == 0)
                    {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->getToken($userId);
                        $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                        $jjs = json_decode($soldeavant);
                        $soldeavant = $jjs->{'statusMessage'};
                        $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);
                        $decode_response = json_decode($response);
                        if($decode_response->{'statusCode'} == 000)
                        {
                            $this->connexion->beginTransaction();
                            $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                            $jjs = json_decode($soldeapres);
                            $soldeapres = $jjs->{'statusMessage'};
                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};
                            $save = $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $date_op = date('Y-m-d H:i:s');
                            $this->utils->saveDetailsTranscation($numtransact, $fkcarte, $montant, 1, $date_op);
                            $change = $this->utils->changeStatutCoderechargement($codeValidation);
                            $debit_Agence = $this->utils->debiterSoldeAgence($montant_total, $fkagence, $this->connexion);

                            if($save==1 && $change==1 && $debit_Agence==1)
                            {
                                $crediterCarteCommission = $this->utils->crediter_carteParametrable($this->connexion, $part_paositra, ID_CARTE_PARAMETRABLE);
                                if($crediterCarteCommission == 1)
                                {
                                    $this->connexion->commit();
                                    $soldeAgenceApres = $this->utils->getSoldeAgence($agence);
                                    $this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, "CREDIT", "RECHARGEMENTESPECE", $this->connexion);
                                    $this->utils->addMouvementCompteAgence($numtransact, $soldeAgence, $soldeAgenceApres, $montant_total, $agence, "DEBIT", "RECHARGEMENT ESPECE", $this->connexion);
                                    $observation = 'Commission Recharge Espece';
                                    $this->utils->addCommission($part_paositra, $service, $fkagence, $fkcarte , $observation );
                                    $messagesms = $data['lang']['mess_recharge_sms'] . $this->utils->number_format($montant) . $data['lang']['currency'].' '.$data['lang']['solde_actuel'].' : '.$this->utils->number_format($soldeapres). $data['lang']['currency'];
                                    $this->utils->sendSMS($data['lang']['paositra1'], $telephone, $messagesms);
                                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                    $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));

                                } else {
                                    $this->connexion->commit();
                                    $observation = 'Commission Recharge Espece à faire';
                                    $this->utils->addCommission_afaire($part_paositra, $service, $fkagence, $fkcarte , $observation);
                                }
                            }
                            else{
                                $statut = 0;
                                $this->connexion->rollBack();
                                $message = 'Contacter administrateur, erreur dans le traitement de la req';
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                $this->api_numherit->debiterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);
                                $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok4') . '/' . base64_encode($telephone));
                            }
                        } else {
                            $statut = 0;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                            $this->api_numherit->debiterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);
                            $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    }
                    else if ($typecompte == 1)
                    {
                        $numcarte = $this->utils_recharge->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $solde = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
                        $json = json_decode($solde);
                        $responseData = $json->{'ResponseData'};
                        if ($responseData != NULL && is_object($responseData))
                        {
                            if (array_key_exists('ErrorNumber', $responseData))
                            {
                                $soldeavant = 0;
                            }
                            else {
                                $soldeavant = (int)$responseData->{'Balance'};
                            }
                        } else $soldeavant = 0;

                        $statut = 0;
                        $json = $this->api_gtp->LoadCard($numtransact, $numeroserie, $last4digitclient, $montant, 'XOF', 'RechargementEspece');
                        $return = json_decode($json);
                        $response = $return->{'ResponseData'};
                        if ($response != NULL && is_object($response))
                        {
                            if (array_key_exists('ErrorNumber', $response))
                            {
                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $errorNumber . '-' . $message, 2, $user_creation);
                                $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                            }
                            else {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if ($transactionId > 0)
                                {
                                    $this->connexion->beginTransaction();
                                    $debitAgence = $this->utils->debiterSoldeAgence($montant_total, $fkagence, $this->connexion);
                                    if($debitAgence == 1)
                                    {
                                        $this->connexion->commit();
                                        $statut = 1;
                                        $soldes = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
                                        $json = json_decode($soldes);
                                        $responseData = $json->{'ResponseData'};
                                        if ($responseData != NULL && is_object($responseData))
                                        {
                                            if (array_key_exists('ErrorNumber', $responseData))
                                            {
                                                $soldeapres = 0;
                                            }
                                            else {
                                                $soldeapres = (int)$responseData->{'Balance'};
                                            }
                                        } else $soldeapres = 0;

                                        $this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, "CREDIT", $commentaire, $this->connexion);
                                        $this->utils->changeStatutCoderechargement($codeValidation);
                                        $soldeAgenceApres = $this->utils->getSoldeAgence($agence);
                                        $this->utils->addMouvementCompteAgence($numtransact, $soldeAgence, $soldeAgenceApres, $montant_total, $agence, "DEBIT", $commentaire, $this->connexion);
                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactionId);
                                        $crediterCarteCommission = $this->utils->crediter_carteParametrable($this->connexion, $part_paositra, ID_CARTE_PARAMETRABLE);
                                        if($crediterCarteCommission == 1)
                                        {
                                            $observation = 'Commission Recharge Espece';
                                            $this->utils->addCommission($part_paositra, $service, $fkagence, $observation,$fkcarte );
                                        }
                                        else {
                                            $observation = 'Commission Recharge Espece à faire';
                                            $this->utils->addCommission_afaire($part_paositra, $service, $fkagence, $observation, $fkcarte);
                                        }
                                        $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                        $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                                    }
                                    else{
                                        $transactionId = 0;
                                        $this->connexion->rollBack();
                                        $this->api_gtp->UnLoadCard($numtransact, $numeroserie, $last4digitclient, $montant, 'XOF', 'RetraitEspece');
                                        $message = 'echec debit solde agence';
                                        $statut = 0;
                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactionId);
                                        $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                    }
                                }
                            }
                        } else {
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                            $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    }
                } else {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                    $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok2'));
                }
            } else {
                $message = 'Solde agence insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok3'));
            }
        } else {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
            $this->rediriger('recharge', 'validationRechargeCompte/' . base64_encode('nok4') . '/' . base64_encode($telephone));
        }
    }

    public function validationRechargeCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($return[0]) === 'ok') {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['message_success_rechargement_espece'], 'type-alert' => 'alert-success');
        } else if (base64_decode($return[0]) === 'nok1') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $message, 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok2') {
            $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['chargement_erreurcode_transact_save'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok3') {
            $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok4') {
            $params = array('view' => 'frontend/rechargementretrait/recharge-espece-carte-search', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['message_alert'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRechargementEspece()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef']= $this->utils_recharge->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);

        $paramsview = array('view' => 'frontend/rechargementretrait/rechargement-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece'] );
        $this->view($paramsview,$data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRechargementEspece1($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtransac = base64_decode($return[0]);
        $carte = base64_decode($return[1]);
        $data['benef']= $this->utils_recharge->beneficiaireBycarte($carte);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'frontend/rechargementretrait/rechargement-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece'] );
        $this->view($paramsview,$data);
    }

    /*********************************************
     *************** RETRAIT *********************
     *********************************************/

    /*********search Retrait Espece Carte********/
    public function searchRetraitEspece()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['transaction'] = $this->utils_recharge->transactionByrowid1($user_creation);
        $fkcarte = $data['transaction']->fk_carte;
        $data['benef'] = $this->utils_recharge->beneficiaireBycarte($fkcarte);
        $params = array('view' => 'frontend/rechargementretrait/retrait-espece-carte-search');
        $this->view($params,$data);
    }

    public function Histo_retraitEspeceCarte()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/rechargementretrait/histo-retrait-espece'));
        $this->view($paramsview, $data);
    }

    public function listretrait()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $num = $obj->getRowid();
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $duplicata = $data['lang']['duplicata'];

        $requestData= $_REQUEST;

        $columns = array(
            // datatable column index  => database column name
            0=> 'date_transaction',
            1=> 'num_transac',
            2=> 'fk_carte',
            3=> 'commission',
            4=> 'service',
            5=> 'montant'
        );

        // getting total number records without any search
        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom,carte.telephone
            FROM transaction, service, user,carte
            WHERE transaction.statut = 1
            AND service.etat = 1
            AND transaction.fkuser = :num  
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_service = ".ID_SERVICE_CASHOUTPARTENAIRE."
            AND transaction.fk_service = service.rowid
            AND transaction.fk_carte=carte.rowid
            AND transaction.fkuser = user.rowid";
        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "num" => $num,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, service.label, 
            transaction.date_transaction, user.prenom, user.nom,carte.telephone
            FROM transaction, service, user,carte
            WHERE transaction.statut = 1
            AND service.etat = 1
            AND transaction.fkuser = :num  
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_service = ".ID_SERVICE_CASHOUTPARTENAIRE."
            AND transaction.fk_service = service.rowid
            AND transaction.fk_carte=carte.rowid
            AND transaction.fkuser = user.rowid";
        if( !empty($requestData['search']['value']) ) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND (transaction.num_transac LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.date_transaction LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.montant LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql.=" OR transaction.commission LIKE '%".$_REQUEST['search']['value']. "%' )";
        }

        $user = $this->connexion->prepare($sql);

        $user->execute(
            array(
                "num" => $num,
            )
        );
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "num" => $num,
            )
        );
        $rows = $user->fetchAll();
        $data = array();

        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["label"];
            $nestedData[] = $this->utils->number_format($row["montant"]);
            $nestedData[] = $this->utils->number_format($row["commission"]);
            $nestedData[] = $row["prenom"].' '.$row["nom"];
            $nestedData[] = "<a href='".ROOT."recharge/recuRetraitEspece1/".base64_encode($row["num_transac"])."/".base64_encode($row['fk_carte'])." ' title='".$duplicata."' target='new'>
                    <input name='duplicata' type='button' class='btn btn-info btn-rounded' value='".$duplicata."'  />
                    </a>

                    ";

            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        echo json_encode($json_data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRetraitEspece1($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtransac = base64_decode($return[0]);
        $carte = base64_decode($return[1]);
        $data['benef']= $this->utils_recharge->beneficiaireByTelephone2($carte);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'frontend/rechargementretrait/retrait-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece'] );
        $this->view($paramsview,$data);
    }

    /*********Recharge Espece Carte Beneficiaire********/
    public function retraitEspeceCarte()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $data['typeagence'] = $obj->getidtype_agence();

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));
        $telephone = trim(str_replace(" ", "",$telephone));
        $data['benef']= $this->utils_recharge->beneficiaireByTelephone1($telephone);
        $data['soldeAgence']= $this->utils->getSoldeAgence($agence);

        $typecompte = $this->utils_recharge->getTypeCompte($telephone);
        if ($typecompte == 0) {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->getToken($userId);
            $response = $this->api_numherit->soldeCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if ($decode_response->{'statusCode'} == 000) {
                $data['soldeCarte'] = $decode_response->{'statusMessage'};
            } else $data['soldeCarte'] = 0;
        } else if ($typecompte == 1) {

            $numcarte = $this->utils_recharge->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $solde = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
            $json = json_decode("$solde");
            $responseData = $json->{'ResponseData'};
            if ($responseData != NULL && is_object($responseData)) {
                if (array_key_exists('ErrorNumber', $responseData)) {
                    $message = $responseData->{'ErrorNumber'};
                    $data['soldeCarte'] = 0;
                } else {
                    $data['soldeCarte'] = $responseData->{'Balance'};
                    $currencyCode = $responseData->{'CurrencyCode'};
                }
            } else $data['soldeCarte'] = 0;
        }
        $params = array('view' => 'frontend/rechargementretrait/retrait-espece-carte');
        $this->view($params,$data);
    }

    /*********Retrait Espece Code Validation********/
    public function retraitEspeceCodeValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['typeagence'] = $this->utils->securite_xss($_POST['typeagence']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['fkagence'] = $obj->getFk_agence();
        if($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '')
        {
            $code_retrait = $this->utils->generateCodeRetrait($data['fkcarte'], $data['montant']);
            if ($data['telephone'] != -1 && $data['telephone'] != -2 && $code_retrait != '' && strlen($code_retrait) == 10)
            {
                $recup_mail = $this->utils->recup_mailBenef($data['telephone']);
                $montant=$this->utils->nombre_format($data['montant']).$data['lang']['currency'];
                $this->utils->envoiCodeRetrait($recup_mail, $obj->getPrenom().' '.$obj->getNom(), $code_retrait, $montant);
                $message = $data['lang']['retraitss'].  $this->utils->nombre_format($data['montant']) . $data['lang']['currency'].' ' .$data['lang']['mess_retrait_espece1'] . $code_retrait . $data['lang']['mess_retrait_espece2'];
                $this->utils->sendSMS($data['lang']['paositra1'], $data['telephone'], $message);
            }

        }
        $this->rediriger('Recharge', 'retraitEspeceCodeValidationSuite/' . base64_encode(($data['telephone'])).'/'.base64_encode(($data['fkcarte'])).'/'.base64_encode(($data['montant'])).'/'.base64_encode(($data['frais'])));
        //$params = array('view' => 'frontend/rechargementretrait/retrait-espece-code-validation');
        //$this->view($params, $data);
    }
    public function retraitEspeceCodeValidationSuite($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $data['telephone'] = base64_decode($return[0]);
        $data['fkcarte'] = base64_decode($return[1]);
        $data['montant'] = base64_decode($return[2]);
        $data['frais'] = base64_decode($return[3]);
        $obj = $this->getSession()->getAttribut('objconnect');
        $data['fkagence'] = $obj->getFk_agence();

        $params = array('view' => 'frontend/rechargementretrait/retrait-espece-code-validation');
        $this->view($params, $data);
    }

    /******* Action verifier code retrait ****/
    public function codeRetrait()
    {
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $frais = $this->utils_recharge->verifCodeRetrait($fkcarte, $code_secret);
        if($frais == 1) echo 1;
        elseif($frais == 0) echo 0;
        else echo -2;
    }

    /******* Action verifier cni ****/
    public function cniCheck()
    {
        $cni = $this->utils->securite_xss($_POST['cni']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $resultat = $this->utils_recharge->verifCNI($fkcarte, $cni);
        if($resultat == 1) echo 1;
        elseif($resultat == 0) echo 0;
        else echo -2;
    }

     /********* Retrait Espece Carte Beneficiaire Validation ********/
    public function retraitEspeceValidation()
    {
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $cni = $this->utils->securite_xss($_POST['cni']);


        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $service = ID_SERVICE_CASHOUTPARTENAIRE;
        $frais = $this->utils_recharge->calculFrais($service, $montant);
        $numtransact = $this->utils->Generer_numtransaction();
        $statut = 0;
        $montant_total = $montant + $frais;
        $part_commission = ( $frais * $this->utils->getTauxDistributeur($service, $fkagence) ) / 100;
        $montant_commission = $frais - $part_commission;
        $montant_agence = (int)($montant + $part_commission);
        $commentaire = 'Cashout Distributeur';
        if($codevalidation != '' && $telephone != '' && $montant > 0 && $frais >= 0 && strlen($numtransact) == 15)
        {
            $codeValidation = $this->utils->rechercherCodeRetrait($codevalidation, $montant);
            $cniValidation = $this->utils->verifCNI($fkcarte, $cni);
            if($codeValidation > 0 && $cniValidation > 0)
            {
                    $typecompte = $this->utils_recharge->getTypeCompte($telephone);
                    if ($typecompte == 0)
                    {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->getToken($userId);
                        $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                        $jjs = json_decode($soldeavant);
                        $soldeavant = $jjs->{'statusMessage'};

                        if($soldeavant >= $montant_total)
                        {
                            $response = $this->api_numherit->debiterCompte($username, $token, $telephone, $montant_total, $service, $user_creation, $fkagence);
                            $decode_response = json_decode($response);
                            if($decode_response->{'statusCode'} == 000)
                            {
                                $this->utils->validerCodeRetrait($codeValidation, $cni);
                                $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                                $jjs = json_decode($soldeapres);
                                $soldeapres = $jjs->{'statusMessage'};
                                $soldeAvant = $this->utils->getSoldeAgence($fkagence);
                                $this->connexion->beginTransaction();
                                $crediterAgence = $this->utils->crediter_soldeAgence($montant_agence, $fkagence, $this->connexion);
                                if($crediterAgence == 1)
                                {
                                    $statut = 1;
                                    $message = $decode_response->{'statusMessage'};
                                    $transactId = $decode_response->{'NumTransaction'};
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                    $date_op = date('Y-m-d H:i:s');
                                    $this->utils->saveDetailsTranscation($numtransact, $fkcarte, $montant, 0, $date_op);
                                    $crediterCarteCommission = $this->utils->crediter_carteParametrable($this->connexion, $montant_commission, ID_CARTE_PARAMETRABLE);
                                    if($crediterCarteCommission == 1)
                                    {
                                        $this->connexion->commit();
                                        $soldeApres = $this->utils->getSoldeAgence($fkagence);
                                        $this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant_total, $telephone, "DEBIT", "CASHOUTDISTRIBUTEUR", $this->connexion);
                                        $this->utils->addMouvementCompteAgence($numtransact, $soldeAvant, $soldeApres, $montant_agence, $fkagence, "CREDIT", "CASHOUTDISTRIBUTEUR", $this->connexion);
                                        $observation = 'Commission Retrait Espece';
                                        $this->utils->addCommission($montant_commission, $service, $fkagence,$fkcarte , $observation);
                                    }
                                    else {
                                        $this->connexion->commit();
                                        $observation = 'Commission Retrait Espece à faire';
                                        $this->utils->addCommission_afaire($montant_commission, $service, $fkagence, $fkcarte , $observation);
                                    }

                                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                    $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                                }
                                else{
                                    $this->api_numherit->crediterCompte($username, $token, $telephone, $montant_total, $service, $user_creation, $fkagence);
                                    $this->connexion->rollBack();
                                    $message = 'Echec rechargement, merci de contacter votre admin';
                                    $message1 = 'Echec rechargement : defaut de credit agence';
                                    $transactId = 0;
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' - ' . $message1, $frais, $fkagence, $transactId);
                                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message1, 2, $user_creation);
                                    $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                                }
                            } else {
                                $message = $decode_response->{'statusMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' - ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                            }
                        }
                        else{
                            $message = 'Solde compte insuffisant';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                            $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok3'));
                        }
                    }
                    else if ($typecompte == 1) {
                        $numcarte = $this->utils_recharge->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $soldes = $this->api_gtp->ConsulterSolde($numeroserie,'6325145878');
                        $json = json_decode($soldes);
                        $responseData = $json->{'ResponseData'};
                        if ($responseData != NULL && is_object($responseData))
                        {
                            if (array_key_exists('ErrorNumber', $responseData))
                            {
                                $soldeavant = 0;
                            }
                            else {
                                $soldeavant = (int)$responseData->{'Balance'};
                            }
                        } else $soldeavant = 0;

                        if($soldeavant >= $montant_total)
                        {
                            $statut = 0;
                            $json = $this->api_gtp->UnLoadCard($numtransact, $numeroserie, $last4digitclient, $montant_total, 'XOF', 'CashoutDistributeur');
                            $return = json_decode("$json");
                            $response = $return->{'ResponseData'};
                            if($response != NULL && is_object($response))
                            {
                                if(array_key_exists('ErrorNumber', $response))
                                {
                                    $errorNumber = $response->{'ErrorNumber'};
                                    $message = $response->{'ErrorMessage'};
                                    $transactId = 0;
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $errorNumber . '-' . $message, 2, $user_creation);
                                    $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                                }
                                else {
                                    $transactionId = $response->{'TransactionID'};
                                    $message = 'Succes';
                                    if ($transactionId > 0)
                                    {
                                        $this->connexion->beginTransaction();
                                        $soldeAgence = $this->utils->getSoldeAgence($fkagence);
                                        $crediterAgence = $this->utils->crediter_soldeAgence($montant_agence, $fkagence, $this->connexion);
                                        if($crediterAgence == 1)
                                        {
                                            $this->connexion->commit();
                                            $statut = 1;
                                            $this->utils->validerCodeRetrait($codeValidation, $cni);
                                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactionId);
                                            $soldess  = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
                                            $json = json_decode($soldess);
                                            $responseData = $json->{'ResponseData'};
                                            if ($responseData != NULL && is_object($responseData))
                                            {
                                                if (array_key_exists('ErrorNumber', $responseData))
                                                {
                                                    $soldeapres = 0;
                                                }
                                                else {
                                                    $soldeapres = (int)$responseData->{'Balance'};
                                                }
                                            } else $soldeapres = 0;

                                            $this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant_total, $telephone, "DEBIT", $commentaire, $this->connexion);
                                            $soldeApres = $this->utils->getSoldeAgence($fkagence);
                                            $this->utils->addMouvementCompteAgence($numtransact, $soldeAgence, $soldeApres, $montant_agence, $fkagence, "CREDIT", $commentaire, $this->connexion);
                                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                            $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                                        }
                                        else{
                                            $this->api_gtp->LoadCard($numtransact, $numeroserie, $last4digitclient, $montant_total, 'XOF', 'RecrediterCarte');
                                            $this->connexion->rollBack();
                                            $message = 'Echec retrait: contacter votre admin';
                                            $message1 = 'Echec retrait: defaut de crediter le bureau';
                                            $transactId = 0;
                                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message1, $frais, $fkagence, $transactId);
                                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message1, 2, $user_creation);
                                            $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                                        }
                                    }
                                }
                            } else {
                                $message = 'Response GTP not object';
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                            }
                        }
                        else{
                            $message = 'Solde compte insuffisant';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                            $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok3'));
                        }
                    }
            } else {
                if($codeValidation < 0) {
                    $message = 'Code de validation incorrect !';
                }elseif($cniValidation < 0){
                    $message = 'CNI incorrect';
                } else {
                    $message = 'Code de validation et le numéro de CNI sont incorrects';
                }
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                    $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok2'));
            }
        } else {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
            $this->rediriger('Recharge', 'validationRetraitCompte/' . base64_encode('nok4') . '/' . base64_encode($telephone));
        }
    }

    /***********Validation retrait Compte**********/
    public function validationRetraitCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        if(base64_decode($return[0]) === 'ok')
        {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' =>'frontend/rechargementretrait/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['message_success_retrait_carte'], 'type-alert'=>'alert-success');
        }
        else if(base64_decode($return[0]) === 'nok1')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'frontend/rechargementretrait/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$message, 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok2')
        {
            $params = array('view' =>'frontend/rechargementretrait/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['chargement_erreurcode_transact_save'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok3')
        {
            $params = array('view' =>'frontend/rechargementretrait/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['solde_insuffisant'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok4')
        {
            $params = array('view' =>'frontend/rechargementretrait/retrait-espece-carte-search', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['message_alert'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRetraitEspece()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef']= $this->utils_recharge->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'frontend/rechargementretrait/retrait-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece'] );
        $this->view($paramsview,$data);
    }

}