<?php
require_once (__DIR__.'/../../vendor/ApiGTP/ApiBanque.php');

class JulaController extends \app\core\BaseController
{
    public $julaModel;
    private $agenceModel;
    private $carteModel;
    private $userConnecter;
    public $api_gtp;


    public function __construct()
    {
        parent::__construct();
        $this->julaModel = $this->model('JulaModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->carteModel = $this->model('CarteModel');
        $this->api_gtp = new  ApiBAnque();
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES BENEFICIARES                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    /*********Liste Beneficiaire*********/
    public function index()
    {
       // var_dump('odd'); die();

        //$this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(110,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'jula/jula');
        $this->view($params, $data);
    }



    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             LIER CARTE A COMPTE                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function indexlcac()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(36,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'jula/liercac-search');
        $this->view($params,$data);
    }



    /*********Recharge Jula Carte Beneficiaire********/
    public function rechargeJulaCarte()
    {
        $fkagence = $this->userConnecter->fk_agence;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));
        $data['benef']= $this->julaModel->beneficiaireByTelephone1($telephone);
        $data['soldeAgence']= $this->utils->getSoldeAgence($fkagence);

        $params = array('view' => 'jula/recharge-jula-carte');
        $this->view($params,$data);
    }

    /********* Recharge Jula Carte Beneficiaire Validation ********/
    public function rechargeJulaValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $code =  $this->utils->trimUltime($this->utils->securite_xss($_POST['code']));
        $fkcarte = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $soldeAgence = $this->utils->securite_xss($_POST['soldeagence']);
        $fkagence = $this->userConnecter->fk_agence;
        $user_creation = $this->userConnecter->rowid;
        $service = 4;
        $statut=0;
        $commentaire='Recharge Owo';

        if($code != '' && $telephone != '' && $soldeAgence != '' && $fkcarte != '' && $user_creation != '' && $fkagence != '')
        {
            require_once (__DIR__.'/../../vendor/ApiGTP/lib/nusoap.php');
            //On recupere le montant de la carte JULA
            $s = new nusoap_client(URL_WS_JULA, true);
            $cle = sha1($code."20"."6bdcaf4bb572c48f62e93d462a62c06978c5df92");
            $parameters = array("code_carte"=>$code,"idmarchand"=>20,"cle_hachage"=>$cle);
            $ResulatRenvoye= $s->call('ConsultationSolde',$parameters);
            $error = $s->getError();
            if($error)
            {
                $msg = $data['lang']['erreur_webservice_jula'];
                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
            }
            else
            {
                if($ResulatRenvoye==104)
                {
                    $msg = $data['lang']['erreur_webservice_jula'];
                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                }
                else if($ResulatRenvoye==103)
                {
                    $msg = $data['lang']['code_jula_incorrect'];
                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                }
                else
                {
                    $parameterss = array();
                    $ResulatRenvoyer= $s->call('ConsulterSoldeCompteJula',$parameterss);
                    $errors = $s->getError();
                    if($errors)
                    {
                        $msg = $data['lang']['erreur_webservice_jula'];
                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                        $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                    }
                    else
                    {
                        if($ResulatRenvoyer!='001' && $ResulatRenvoyer!='002' && $ResulatRenvoyer!='003')
                        {
                            if($ResulatRenvoyer >= $ResulatRenvoye)
                            {
                                if($soldeAgence >= $ResulatRenvoye)
                                {
                                    $cle2 = sha1($code.$ResulatRenvoye."20"."6bdcaf4bb572c48f62e93d462a62c06978c5df92");
                                    $parameters = array("code_carte"=>$code,"montant"=>$ResulatRenvoye,"idmarchand"=>20,"cle_hachage"=>$cle2,"num_transaction"=>"EdkCashSn".date("YmdHis"));
                                    $ResulatRenvoye2= $s->call('debiterCarte',$parameters);
                                    $error = $s->getError();
                                    if($error)
                                    {
                                        $msg = $data['lang']['erreur_webservice_jula'];
                                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                        $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                    }
                                    else
                                    {
                                        if($ResulatRenvoye2==105)
                                        {
                                            $msg = $data['lang']['erreur_webservice_jula'];
                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                        }
                                        if($ResulatRenvoye2==104)
                                        {
                                            $msg = $data['lang']['erreur_webservice_jula'];
                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                        }
                                        if($ResulatRenvoye2==103)
                                        {
                                            $msg = $data['lang']['code_jula_incorrect'];
                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                        }
                                        if($ResulatRenvoye2==102)
                                        {
                                            $msg = $data['lang']['erreur_webservice_jula'];
                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                        }
                                        if($ResulatRenvoye2==101)
                                        {
                                            $montant = $ResulatRenvoye;
                                            $frais = $this->julaModel->calculFraisab($service, $montant);
                                            $typecompte = $this->julaModel->getTypeCompte($telephone);
                                            $numtransact = $this->utils->Generer_numtransaction();
                                            if($typecompte == 0)
                                            {
                                                $username = 'Numherit';
                                                $userId = 1;
                                                $token = $this->utils->getToken($userId);
                                                $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);
                                                $decode_response = json_decode($response);
                                                if($decode_response->{'statusCode'}==000)
                                                {
                                                    $statut = 1;
                                                    $message = $decode_response->{'statusMessage'};
                                                    $transactId = $decode_response->{'NumTransaction'};
                                                    $this->utils->debiter_compteJula($montant);
                                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire.' '.$message.' '.$code, $frais, $fkagence, $transactId);
                                                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact).'/'.base64_encode($code));
                                                }
                                                else
                                                {
                                                    $message = $decode_response->{'statusMessage'};
                                                    $transactId = 0;
                                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire.' '.$message.' '.$code, $frais, $fkagence, $transactId);
                                                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok1').'/'.base64_encode($message));
                                                }
                                            }
                                            else if($typecompte == 1)
                                            {
                                                $numcarte = $this->julaModel->getNumCarte($telephone);
                                                $numeroserie = $this->utils->returnCustomerId($numcarte);
                                                $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                                                $statut = 0;
                                                $json= $this->api_gtp->LoadCard($numtransact,$numeroserie, $last4digitclient,$montant,'Ar','RechargementEspece');
                                                $return = json_decode("$json");
                                                $response = $return->{'ResponseData'};
                                                if($response != NULL && is_object($response))
                                                {
                                                    if(array_key_exists('ErrorNumber', $response))
                                                    {
                                                        $errorNumber = $response->{'ErrorNumber'};
                                                        $message = $response->{'ErrorMessage'};
                                                        $transactId = 0;
                                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire.' '.$message.' '.$code, $frais, $fkagence, $transactId);
                                                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact.' code:'.$code, $errorNumber.'-'.$message, 2, $user_creation);
                                                        $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok1').'/'.base64_encode($message));
                                                    }
                                                    else
                                                    {
                                                        $transactionId = $response->{'TransactionID'};
                                                        $message = 'Succes';
                                                        if($transactionId > 0)
                                                        {
                                                            $statut = 1;
                                                            $this->utils->debiter_compteJula($montant);
                                                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire.' '.$message.' '.$code, $frais, $fkagence, $transactionId);
                                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $user_creation);
                                                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact).'/'.base64_encode($code));
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    $message = 'Response GTP not object';
                                                    $transactId = 0;
                                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire.' '.$message.' '.$code, $frais, $fkagence, $transactId);
                                                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact.' code:'.$code, $message, 2, $user_creation);
                                                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok1').'/'.base64_encode($message));
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $msg = $data['lang']['solde_agence_insuffisant'];
                                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                    $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                                }
                            }
                            else
                            {
                                $msg = $data['lang']['compte_solde_jula'];
                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                                $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                            }
                        }
                        else
                        {
                            $msg = $data['lang']['erreur_solde_jula'];
                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:'.$telephone.' code:'.$code, $msg, 2, $user_creation);
                            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok2').'/'.base64_encode($msg));
                        }
                    }
                }
            }
        }
        else
        {
            $msg ='Paramétres envoyés incorrect';
            $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Code:'.$code, $msg, 2, $user_creation);
            $this->rediriger('compte','validationRechargeJula/'.base64_encode('nok1').'/'.base64_encode($msg));
        }
    }

    /***********Validation retrait Compte**********/
    public function validationRechargeJula($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0]) === 'ok')
        {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);
            $data['code'] = base64_decode($return[5]);

            $params = array('view' =>'jula/recharge-jula-carte-fin', 'title' =>$data['lang']['rechargement_par_jula'], 'alert'=>$data['lang']['message_success_rechargement_jula'], 'type-alert'=>'alert-success');
        }
        else if(base64_decode($return[0]) === 'nok1')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'jula/recharge-jula-carte-fin', 'title' =>$data['lang']['rechargement_par_jula'], 'alert'=>$message, 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok2')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'jula/recharge-jula-carte-fin', 'title' =>$data['lang']['rechargement_par_jula'], 'alert'=>$message, 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /************************** Recu Recharge Jula **************/
    public function recuRechargementJula()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef']= $this->julaModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $data['code'] = $this->utils->securite_xss($_POST['code']);
        $paramsview = array('view' => 'jula/rechargement-carte-jula-facture', 'title' => $data['lang']['rechargement_par_jula'] );
        $this->view($paramsview,$data);
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION DES STOCKS                                         //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function reception($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(59,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(!is_null($arg)){

            $data['num_debut'] = base64_decode($arg[1]);
            $data['num_fin'] = base64_decode($arg[2]);
            //$data['montant'] = base64_decode($arg[3]);
            $arg = base64_decode($arg[0]);
            if($arg == -1)
            {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_reception'];
            }
            elseif($arg === '002'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_requete'];
            }
            elseif($arg === '003'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_Connection'];
            }
            elseif($arg === '004'){
                $type_alert='error';
                $alert=$data['lang']['message_error_montant'];
            }
            elseif($arg === '006'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_discontinu'];
            }
            elseif($arg === '007'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_indisponible'];
            }
            elseif($arg === '104'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Cle_incorrecte'];
            }
            elseif($arg === '105'){
                $type_alert='error';
                $alert=$data['lang']['message_error_IDmarchand_incorrect'];
            }
            elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_reception'];
            }
        }

        require_once (__DIR__.'/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = sha1(ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $parameters = array("idmarchand"=>ID_WS_JULA,"idpartenaire"=>IDPARTENAIRE_WS_JULA,"cle_hachage"=>$cle);
        $ResulatRenvoye= $s->call('ReturnTypeCarte',$parameters);
        $data['typecarte'] = json_decode($ResulatRenvoye);

        $data['ref'] = $this->julaModel->__getReference();
        $params = array('view' => 'jula/nouvelle-reception','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function addLotCarte()
    {
        $numdebut = $_POST['num_debut'];
        $numfin = $_POST['num_fin'];
        $montant = $_POST['montant'];
        $statut = 0;
        $etat = 3;

        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $_POST['user_add'] = $this->userConnecter->rowid;
        $_POST['idagence'] = $this->userConnecter->fk_agence;
        if ($numdebut != '' && $numfin != '' && $montant != '') {

        }

        require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = SHA1( ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $parameters = array("idpartenaire" => IDPARTENAIRE_WS_JULA, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle, "num_debut" =>$numdebut, "num_fin" =>$numfin, "statut" =>$statut);
        $parame = array("idpartenaire" => IDPARTENAIRE_WS_JULA, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle, "num_debut" =>$numdebut, "num_fin" =>$numfin, "statut" =>$etat);
        $ResulatRenvoye = $s->call('verifierLot', $parameters);

        $error = $s->getError();
        if ($error) {
            $this->rediriger("jula","reception/".base64_encode(-2)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));

        }
        else {

            $decode =  json_decode($ResulatRenvoye);

            $montant_retourne = $decode->{'montant'};
            $code = $decode->{'result'};
            if($code == '001' && $montant_retourne == $montant){
                $result = $this->julaModel->insertReception($_POST);

                if($result){

                    $ResulatRenvoyes = $s->call('updateLotCarte', $parame);
                    $decodes = json_decode($ResulatRenvoyes);

                    $code = $decodes->{'result'};
                    if ($code ==="001"){
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '002')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '003')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '006')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '007')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '104')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                    if($code == '105')
                    {
                        $result = $code;
                        $this->rediriger("jula","historique_recep/".base64_encode($result));
                    }
                }

            }
            else{
                if($code == '001' && $montant_retourne != $montant){
                    $result = '004';
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }

                if($code == '002')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
                if($code == '003')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
                if($code == '006')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
                if($code == '007')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
                if($code == '104')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
                if($code == '105')
                {
                    $result = $code;
                    $this->rediriger("jula","reception/".base64_encode($result)."/".base64_encode($numdebut)."/".base64_encode($numfin)."/".base64_encode($montant));
                }
            }
        }
    }

    public function historique_recep($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(58,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(!is_null($arg)){
            $arg = base64_decode($arg[0]);
            //var_dump($arg);die;
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_reception'];
            }
            elseif($arg === '001'){
                $type_alert='success';
                $alert=$data['lang']['message_success_Lot_disponible'];
            }
            elseif($arg === '002'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_requete'];
            }
            elseif($arg === '003'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_Connection'];
            }
            elseif($arg === '004'){
                $type_alert='error';
                $alert=$data['lang']['message_error_montant'];
            }
            elseif($arg === '006'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_discontinu'];
            }
            elseif($arg === '007'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_indisponible'];
            }
            elseif($arg === '104'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Cle_incorrecte'];
            }
            elseif($arg === '105'){
                $type_alert='error';
                $alert=$data['lang']['message_error_IDmarchand_incorrect'];
            }

        }
        $params = array('view' => 'jula/histo-reception','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function historique_dist($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(56,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(!is_null($arg)){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_dist'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_dist'];
            }elseif($arg == -3){
                $type_alert='error';
                $alert=$data['lang']['message_error_webservice'];
            }elseif($arg === '006'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_discontinu'];
            }elseif($arg === '007'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Lot_indisponible'];
            }elseif($arg === '002'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_requete'];
            }elseif($arg === '003'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Erreur_Connection'];
            }elseif($arg === '104'){
                $type_alert='error';
                $alert=$data['lang']['message_error_Cle_incorrecte'];
            }elseif($arg === '105'){
                $type_alert='error';
                $alert=$data['lang']['message_error_IDmarchand_incorrect'];
            }
        }
        $params = array('view' => 'jula/histo-distribution','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function distribution($arg = null)
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(57,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(!is_null($arg)){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_dist'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_dist'];
            }
        }
        $data['agences'] = $this->agenceModel->allAgence();

        $data['lots'] = $this->julaModel->getLotReception();


        $data['ref'] = $this->julaModel->__getReference('lotcarte_jula');

        $params = array('view' => 'jula/nouvelle-distribution','alert'=>$alert,'type-alert'=>$type_alert);

        $this->view($params, $data);
    }

    public function updateDistribution()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = base64_decode($_POST[$key]);
        $rowid = $_POST['idlotcarte'];
        unset($_POST['idlotcarte']);
        $result = $this->julaModel->updateDistribution($_POST, $rowid);
        $this->rediriger("jula","historique_dist/".base64_encode($result));
    }

    public function detailHistoRecep($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['detailRecep'] = $this->julaModel->getHistoriqueReception(['idlotcarte_recu'=>base64_decode($arg[0])])[0];
        $params = array('view' => 'jula/reception-detail');
        $this->view($params, $data);
    }

    public function detailHistoDist($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['detailDist'] = $this->julaModel->getHistoriqueDistribution(['idlotcarte'=>base64_decode($arg[0])])[0];
        $data['nbrCarteVendu'] = count($this->julaModel->getCartesSaleByIntevale(['debut'=>$data['detailDist']['num_debut'],'fin'=>$data['detailDist']['num_fin'],'idagence'=>$data['detailDist']['idagence']]));
        if($data['nbrCarteVendu'] === 0){
            $data['agences'] = $this->agenceModel->allAgence();
            $data['typeCarte'] = $this->julaModel->getTypeCarte();
        }
        $params = array('view' => 'jula/distribution-detail');
        $this->view($params, $data);
    }

    public function distribuerLotCarte()
    {
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'idLot' || $key == 'agence_retour' || $key == 'idlotcarte') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $_POST['user_add'] = $this->userConnecter->rowid;
        $_POST['date_add'] = date('Y-m-d H:i:s');
        $statut = 2;
        $numfin = $_POST['num_fin'];
        $numdebut = $_POST['num_debut'];

        require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = SHA1( ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $parameters = array("idpartenaire" => IDPARTENAIRE_WS_JULA, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle, "num_debut" =>$numdebut, "num_fin" =>$numfin, "statut" =>$statut);
        $ResulatRenvoye = $s->call('updateLotCarte', $parameters);
        $error = $s->getError();
        if ($error) {
            $this->rediriger("jula","historique_dist/".base64_encode(-3));
        }
        else
        {
            $decode =  json_decode($ResulatRenvoye);

            $code = $decode->{'result'};
            if($code == '001'){
                $result = $this->julaModel->insertDistribution($_POST);
                if($result) $this->rediriger("jula","historique_dist/".base64_encode($result));
                else $this->rediriger("jula","historique_dist/".base64_encode(-1));
            }
            else $this->rediriger("jula","historique_dist/".base64_encode($code));
        }


    }

    public function disponibilite($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(63,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(!is_null($arg)){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_agence'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_agence'];
            }
        }
        $params = array('view' => 'jula/disponibilite-carte','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function retour_old($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(65,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(count($_POST) == 0){
            if(!is_null($arg)) {
                $arg = base64_decode($arg[0]);
                if($arg == -1) {
                    $type_alert='success';
                    $alert=$data['lang']['message_success_retour_carte_jula'];
                }elseif($arg == -2){
                    $type_alert='error';
                    $alert=$data['lang']['message_error_retour_carte_jula'];
                }
            }
            $data['agences'] = $this->agenceModel->allAgenceWithLotJula();
            //echo 2;die;
        }else {
            foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $_POST['lotCarte'] = explode('-',$_POST['lotCarte']);
            $data['idlotcarte'] = $_POST['lotCarte'][0];
            $_POST['debut'] = $_POST['lotCarte'][1];
            $_POST['fin'] = $_POST['lotCarte'][2];
            $data['idagence'] = $_POST['idagence'];
            unset($_POST['lotCarte']);
            $carteSale = $this->julaModel->getCartesSaleAndReturnByIntevale(['debut'=>$_POST['debut'],'fin'=>$_POST['fin'],'idagence'=>$_POST['idagence']]);
            $data['lotRestant'] = $this->getFreeInterval(['debut'=>$_POST['debut'],'fin'=>$_POST['fin']],$carteSale);
            $this->getSession()->setAttributArray('lotRestant',$data['lotRestant']);
            $data['reference'] = $_POST['reference'];
            $data['date_reception'] = $_POST['date_reception'];
        }
        $params = array('view' => 'jula/retour-lot-carte','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }


    public function retour($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(65,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(count($_POST) == 0){
            if(!is_null($arg)) {
                $arg = base64_decode($arg[0]);
                if($arg == -1) {
                    $type_alert='success';
                    $alert=$data['lang']['message_success_retour_carte_jula'];
                }elseif($arg == -2){
                    $type_alert='error';
                    $alert=$data['lang']['message_error_retour_carte_jula'];
                }
            }
            $data['agences'] = $this->agenceModel->allAgenceWithLotJula();
            //echo 2;die;
        }else {
            foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $_POST['lotCarte'] = explode('-',$_POST['lotCarte']);
            $data['idlotcarte'] = $_POST['lotCarte'][0];
            $_POST['debut'] = $_POST['lotCarte'][1];
            $_POST['fin'] = $_POST['lotCarte'][2];
            $data['idagence'] = $_POST['idagence'];
            unset($_POST['lotCarte']);
            $carteSale = $this->julaModel->getCartesSaleAndReturnByIntevale(['debut'=>$_POST['debut'],'fin'=>$_POST['fin'],'idagence'=>$_POST['idagence']]);
            $data['lotRestant'] = $this->getFreeInterval(['debut'=>$_POST['debut'],'fin'=>$_POST['fin']],$carteSale);
            $this->getSession()->setAttributArray('lotRestant',$data['lotRestant']);
            $data['reference'] = $_POST['reference'];
            $data['date_reception'] = $_POST['date_reception'];
        }
        $params = array('view' => 'jula/retour-lot-carte','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function retournerLot()
    {

        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'idLot' || $key == 'agence_retour' || $key == 'idlotcarte') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $idlotcarte = $_POST['idlotcarte'];
        unset($_POST['idlotcarte']);
        $lot = $this->getSession()->getAttribut('lotRestant')[$_POST['idLot']];

        require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
        //On recupere le montant de la carte JULA
        $s = new nusoap_client(URL_WS_JULA, true);
        $cle = SHA1( ID_WS_JULA.IDPARTENAIRE_WS_JULA.KEY_WS_JULA);
        $parameters = array("idpartenaire" => IDPARTENAIRE_WS_JULA, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle, "num_debut" =>$_POST['num_debut'], "num_fin" =>$_POST['num_fin'], "statut" =>3);
        $ResulatRenvoye = $s->call('updateLotCarte', $parameters);
        $error = $s->getError();

        if ($error) {
            $this->rediriger("jula","retour/".base64_encode(-2));
        }
        else
        {
            $decode =  json_decode($ResulatRenvoye);

            $code = $decode->{'result'};
            if($code == '001'){
                if((intval($_POST['num_debut']) < intval($_POST['num_fin'])) && (intval($_POST['num_debut']) <= intval($lot['debut'])) && (intval($_POST['num_fin']) <= intval($lot['fin']))){
                    unset($_POST['idLot']);
                    $_POST['num_reference'] = $this->julaModel->__getReference();
                    $_POST['date_reception'] = $this->utils->getDateNow();
                    $_POST['user_add'] = $this->userConnecter->rowid;
                    $_POST['idagence'] = $this->userConnecter->fk_agence;
                    $_POST['montant'] = $this->julaModel->getMontantCarteLOt($idlotcarte);
                    $current_stock = $this->julaModel->getCurrentStock($idlotcarte);


                    $result = $this->julaModel->insertReceptionRetour($_POST);
                    if($result === -1){
                        $retour = (intval($_POST['num_fin']) - intval($_POST['num_debut']))+1;
                        $stock = $current_stock - $retour;
                        $this->julaModel->updateDistribution(['stock'=>$stock, 'carte_retour'=>$retour], $idlotcarte);
                    }
                    $this->rediriger("jula","retour/".base64_encode($result));
                }
                else{
                    $this->rediriger("jula","retour/".base64_encode(-2));
                }
            }
            else {
                $this->rediriger("jula", "retour/" . base64_encode(-2));
            }
        }
    }

    public function getFreeInterval($interval = ['debut'=>null,'fin'=>null], array $data)
    {
        asort($data);
        $current = null;
        $result = [];
        foreach ($data as $oneData) {
            if($oneData > $interval['debut']) {
                array_push($result,['idlot'=>null,'debut'=>$interval['debut'],'fin'=>str_pad((intval($oneData) - 1), 13, "0", STR_PAD_LEFT),'stock'=>((intval(str_pad((intval($oneData) - 1), 13, "0", STR_PAD_LEFT)) - intval($interval['debut'])) +1)]);
                $result[(count($result) - 1)]['idlot'] = (count($result) - 1);
                $interval['debut'] = str_pad((intval($oneData) + 1), 13, "0", STR_PAD_LEFT);
            }else
                $interval['debut'] = str_pad((intval($interval['debut']) + 1), 13, "0", STR_PAD_LEFT);
        }
        if(intval($result[(count($result)-1)]['fin']) < intval($interval['fin'])) {
            array_push($result,['idlot'=>null,'debut'=>$interval['debut'],'fin'=>$interval['fin'],'stock'=>((intval($interval['fin']) - intval($interval['debut']))+1)]);
            $result[(count($result) - 1)]['idlot'] = (count($result) - 1);
        }
        return $result;
    }

    public function getLotCarteByAgence()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $data = $this->julaModel->getLotByAgence($_POST);
        $result = '<option disabled selected="selected">'.$data['lang']['select_lot'].'</option>';
        foreach($data as $lot)
            $result .= '<option value="'.$lot['idlotcarte'].'-'.$lot['num_debut'].'-'.$lot['num_fin'].'">'.$lot['num_debut'].' ==> '.$lot['num_fin'].' ==> '.$this->utils->nombre_format($lot['stock']).'</option>';
        echo $result;
    }

    public function processingHistoRecep()
    {
        $param = [
            "button"=>[
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->julaModel, "getHistoriqueReception", $param);
    }

    public function processingHistoDist()
    {
        $param = [
            "button"=>[],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->julaModel, "getHistoriqueDistribution", $param);
    }

    public function processingDispoCarte()
    {
        $param = [
            "button"=>[],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->julaModel, "getDisponibiliteCartes", $param);
    }



}