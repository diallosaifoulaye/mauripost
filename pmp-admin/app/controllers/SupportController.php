<?php
require_once (__DIR__.'/../../vendor/ApiGTP/ApiBanque.php');
/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */
class SupportController extends \app\core\BaseController
{
    private $supportModel;
    public $transfertModel;
    public $compteModel;
    private $agenceModel;
    private $userConnecter;
    public $api_gtp;

    public function __construct()
    {
        parent::__construct();
        $this->supportModel = $this->model('SupportModel');
        $this->transfertModel = $this->model('TransfertModel');
        $this->compteModel = $this->model('CompteModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
        $this->api_gtp = new  ApiBAnque();
    }

    /************* default action **************/
    public function index($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 8));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'GestionSupport/accueil');
        $this->view($params,$data);
    }

    /************* default action **************/
    public function support($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(154,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'GestionSupport/index');
        $this->view($params,$data);
    }
    /*********search Recharge Espece Carte********/
    public function searchRechargeEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/recharge-espece-carte-search');
        $this->view($params,$data);
    }
    /*********Recharge Espece Carte Beneficiaire********/
    public function rechargeEspeceCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );
        $fkagence = $this->userConnecter->fk_agence;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));
        $data['benef']= $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['regions']= $this->supportModel->getAllRegion();


        $params = array('view' => 'GestionSupport/recharge-espece-carte');
        $this->view($params,$data);
    }


    /*********Recharge Espece Carte Beneficiaire********/
    public function getSoldeAgence()
    {
        echo $this->utils->getSoldeAgence($this->utils->securite_xss($_POST['agence']));
    }


    /*********Recharge Espece Carte Beneficiaire********/
    public function rechargeEspeceCodeValidation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeAgence'] = $this->utils->securite_xss($_POST['soldeagence']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $data['fk_agence'] = $this->utils->securite_xss($_POST['fk_agence']);
        $data['fk_user'] = $this->utils->securite_xss($_POST['fk_user']);
        $data['fk_user_support'] = $this->userConnecter->rowid;

        if($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fk_agence'] != '' && $data['fk_user'] != '' && $data['fk_user_support'] != '')
        {
            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $recup_tel = $this->utils->recup_tel($this->userConnecter->fk_agence);
            $code_recharge = $this->utils->generateCodeRechargement($this->userConnecter->fk_agence);
            if($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '')
            {
                $this->utils->envoiCodeRechargement($recup_mail,'Chef d\'agence Call Center',$code_recharge, $data['lang']);
                $this->utils->envoiCodeRechargement($this->userConnecter->email, $this->userConnecter->prenom.' '.$this->userConnecter->nom, $code_recharge, $data['lang']);
            }


            $message = $data['lang']['supp_recharge_mess1'] .$code_recharge . $data['lang']['supp_recharge_mess2'] . $this->utils->number_format($data['montant'])." Ar";
            @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);
            @$this->utils->sendSMS($data['lang']['paositra1'], $this->userConnecter->telephone, $message);
            if(DEBUG === TRUE){
                $this->utils->envoiCodeRechargement('papa.ngom@numherit.com','Papa NGOM',$code_recharge, $data['lang']);
            }
        }

        $params = array('view' => 'GestionSupport/recharge-espece-code-validation');
        $this->view($params,$data);
    }
    /******* Action verifier code rechargement ****/
    public function codeRechargement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fk_agence = $this->utils->securite_xss($_POST['fkagence']);
        $frais = $this->compteModel->verifCodeRechargement($this->userConnecter->fk_agence, $code_secret);
        if($frais == 1) echo 1;
        elseif($frais == 0) echo 0;
        else echo -2;
    }

    /********* Recharge Espece Carte Beneficiaire Validation ********/
    public function rechargeEspeceValidation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils->securite_xss($_POST['frais']);
        $fkagencesupport = $this->userConnecter->fk_agence;
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $fkuser= $this->utils->securite_xss($_POST['fkuser']);
        $fkusersupport= $this->utils->securite_xss($_POST['fkusersupport']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $user_creation = $this->userConnecter->rowid;
        $service = 12;

        $json = $this->supportModel->calculFraisab($service,$montant);
        $result = json_decode($json);
        if(is_object($result) && array_key_exists('frais', $result)){
            $frais = $result->frais;
        }
        $numtransact = $this->utils->Generer_numtransaction();
        $statut=0;
        $commentaire='Recharge Espece';

        $soldeAgence = $this->supportModel->getoldeAgence($fkagence);
        if($codevalidation != '' && $telephone != '' && $montant > 0 && $frais > 0 && $soldeAgence != '' && strlen($numtransact)==15)
        {
            if($soldeAgence >= ($montant+$frais))
            {
                $codeValidation = $this->utils->rechercherCoderechargement($codevalidation,$this->userConnecter->fk_agence);
                if($codeValidation  > 0)
                {
                    $typecompte = $this->compteModel->getTypeCompte($telephone);
                    if($typecompte == 0)
                    {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->utils->getToken($userId);
                        $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $fkuser, $fkagence);
                        $decode_response = json_decode($response);
                        if($decode_response->{'statusCode'}==000)
                        {
                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};
                            $this->utils->changeStatutCoderechargement($codeValidation);
                            $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            $crediterCarteCommission = $this->utils->crediter_carteParametrable($frais, 1);
                            if($crediterCarteCommission == 1)
                            {
                                $debiterAgenceCommission = $this->agenceModel->debiter_soldeAgence($frais, $fkagence);
                                if($debiterAgenceCommission==1)
                                {
                                    $observation = 'Commission Recharge Espece';
                                    $this->utils->addCommission($frais, $service, $fkcarte, $observation, $fkagence);
                                }
                                else
                                {
                                    $observation = 'Commission Recharge Espece à faire';
                                    $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                                }
                            }
                            else
                            {
                                $observation = 'Commission Recharge Espece à faire';
                                $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                            }
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $fkuser);
                            $this->rediriger('support','validationRechargeCompte/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact));
                        }
                        else
                        {
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $fkuser);
                            $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                        }
                    }
                    else if($typecompte == 1)
                    {
                        $numcarte = $this->compteModel->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $statut = 0;
                        $json= $this->api_gtp->LoadCard($numtransact,$numeroserie, $last4digitclient,$montant,'XOF','RechargementEspece');
                        $return = json_decode("$json");
                        $response = $return->{'ResponseData'};
                        if($response != NULL && is_object($response))
                        {
                            if(array_key_exists('ErrorNumber', $response))
                            {
                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                                @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                                $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $errorNumber.'-'.$message, 2, $user_creation);
                                $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                            }
                            else
                            {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if($transactionId > 0)
                                {
                                    $statut = 1;
                                    $this->utils->changeStatutCoderechargement($codeValidation);
                                    $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactionId, $fkusersupport, $fkagencesupport);
                                    @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                                    $crediterCarteCommission = $this->utils->crediter_carteParametrable($frais, 1);
                                    if($crediterCarteCommission == 1)
                                    {
                                        $debiterAgenceCommission = $this->agenceModel->debiter_soldeAgence($frais, $fkagence);
                                        if($debiterAgenceCommission==1)
                                        {
                                            $observation = 'Commission Recharge Espece';
                                            $this->utils->addCommission($frais, $service, $fkcarte, $observation, $fkagence);
                                        }
                                        else
                                        {
                                            $observation = 'Commission Recharge Espece à faire';
                                            $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                                        }
                                    }
                                    else
                                    {
                                        $observation = 'Commission Recharge Espece à faire';
                                        $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                                    }
                                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                                    $this->rediriger('support','validationRechargeCompte/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact));
                                }
                            }
                        }
                        else
                        {
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                            $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                        }
                    }
                }
                else
                {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                    @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                    $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok2'));
                }
            }
            else
            {
                $message = 'Solde agence insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok3'));
            }
        }
        else
        {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
            $this->utils->log_journal('Recharge compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
            $this->rediriger('support','validationRechargeCompte/'.base64_encode('nok4').'/'.base64_encode($telephone));
        }
    }

    /***********Validation Recharge Compte**********/
    public function validationRechargeCompte($return)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0]) === 'ok')
        {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' =>'GestionSupport/recharge-espece-carte-fin', 'title' =>$data['lang']['rechargement_par_espece'], 'alert'=>$data['lang']['message_success_rechargement_espece'], 'type-alert'=>'alert-success');
        }
        else if(base64_decode($return[0]) === 'nok1')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'GestionSupport/recharge-espece-carte-fin', 'title' =>$data['lang']['rechargement_par_espece'], 'alert'=>$message, 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok2')
        {
            $params = array('view' =>'GestionSupport/recharge-espece-carte-fin', 'title' =>$data['lang']['rechargement_par_espece'], 'alert'=>$data['lang']['chargement_erreurcode_transact_save'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok3')
        {
            $params = array('view' =>'GestionSupport/recharge-espece-carte-fin', 'title' =>$data['lang']['rechargement_par_espece'], 'alert'=>$data['lang']['solde_agence_insuffisant'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok4')
        {
            $params = array('view' =>'GestionSupport/recharge-espece-carte-search', 'title' =>$data['lang']['rechargement_par_espece'], 'alert'=>$data['lang']['message_alert'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRechargementEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(171,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef']= $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'GestionSupport/rechargement-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece'] );
        $this->view($paramsview,$data);
    }

    /*********search Retrait Espece Carte********/
    public function searchRetraitEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/retrait-espece-carte-search');
        $this->view($params,$data);
    }

    public function soldeAgence(){
        $agence = $this->utils->securite_xss($_POST['agence']);
        echo json_encode($this->utils->getSoldeAgence($agence));
    }
    /*********Retrait Espece Carte Beneficiaire********/
    public function retraitEspeceCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['regions'] = $this->supportModel->getAllRegion();
        if($this->userConnecter->idtype_agence == 3) $data['frais'] = $this->compteModel->calculFraisab(17); else $data['frais'] = $this->compteModel->calculFraisab(10);

        $typecompte = $this->compteModel->getTypeCompte($telephone);
        if($typecompte == 0)
        {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->soldeCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if($decode_response->{'statusCode'}==000)
            {
                $data['soldeCarte'] = $decode_response->{'statusMessage'};
            }
            else $data['soldeCarte'] = 0;
        }
        else if($typecompte == 1)
        {
            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $solde = $this->api_gtp->ConsulterSolde($numeroserie,'6325145878');
            $json = json_decode("$solde");
            $responseData = $json->{'ResponseData'};
            if($responseData != NULL && is_object($responseData))
            {
                if(array_key_exists('ErrorNumber', $responseData))
                {
                    $message = $responseData->{'ErrorNumber'};
                    $data['soldeCarte'] = 0;
                }
                else
                {
                    $data['soldeCarte'] = $responseData->{'Balance'};
                    $currencyCode = $responseData->{'CurrencyCode'};
                }
            }
            else $data['soldeCarte'] = 0;
        }

        $params = array('view' => 'GestionSupport/retrait-espece-carte');
        $this->view($params,$data);
    }

    /*********Retrait Espece Code Validation********/
    public function retraitEspeceCodeValidation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $data['fkagence'] = $this->utils->securite_xss($_POST['fk_agence']);;
        $data['fkuser'] = $this->utils->securite_xss($_POST['fk_user']);


        if($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != ''  && $data['fkuser'] != '' )
        {
            $code_retrait = $this->utils->generateCodeRetrait($data['fkcarte'], $data['montant']);
            if($data['telephone'] != -1 &&  -2 && $code_retrait != '' && strlen($code_retrait) == 10)
            {
                $message="Merci de donner le code suivant: ".$code_retrait;
                $this->utils->sendSMS('EdkCash',$data['telephone'], $message);
            }

            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $this->utils->envoiCodeRetrait($recup_mail, $data['lang']['chef_agence'], $code_retrait);
            @$this->utils->envoiCodeRetrait($this->userConnecter->email, $this->userConnecter->prenom.' '.$this->userConnecter->nom, $code_retrait);
        }

        $params = array('view' => 'GestionSupport/retrait-espece-code-validation');
        $this->view($params,$data);
    }

    /******* Action verifier code retrait ****/
    public function codeRetrait()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $frais = $this->compteModel->verifCodeRetrait($fkcarte, $code_secret);
        if($frais == 1) echo 1;
        elseif($frais == 0) echo 0;
        else echo -2;
    }

    /********* Retrait Espece Carte Beneficiaire Validation ********/
    public function retraitEspeceValidation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $montant = $this->utils->securite_xss($_POST['montant']);

        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $fkuser = $this->utils->securite_xss($_POST['fkuser']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $cni = $this->utils->securite_xss($_POST['cni']);
        $typecompte = $this->compteModel->getTypeCompte($telephone);
        $soldecarte = 0;
        if($typecompte == 0)
        {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->soldeCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if($decode_response->{'statusCode'}==000)
            {
                $soldecarte = $decode_response->{'statusMessage'};
            }
            else $soldecarte = 0;
        }
        else if($typecompte == 1)
        {
            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $solde = $this->api_gtp->ConsulterSolde($numeroserie,'6325145878');
            $json = json_decode("$solde");
            $responseData = $json->{'ResponseData'};
            if($responseData != NULL && is_object($responseData))
            {
                if(array_key_exists('ErrorNumber', $responseData))
                {
                    $message = $responseData->{'ErrorNumber'};
                    $soldecarte = 0;
                }
                else
                {
                    $soldecarte = $responseData->{'Balance'};
                    $currencyCode = $responseData->{'CurrencyCode'};
                }
            }
            else
                $soldecarte = 0;
        }

        $fkusersupport= $this->userConnecter->rowid;
        $fkagencesupport= $this->userConnecter->fk_agence;

        $service = 10;
        $json = $this->supportModel->calculFraisab($service,$montant);
        $result = json_decode($json);
        if(is_object($result) && array_key_exists('frais', $result)){
            $frais = $result->frais;
        }
        $numtransact = $this->utils->Generer_numtransaction();
        $statut=0;
        $commentaire='Cashout';

        if($this->userConnecter->idtype_agence == 3)
        {
            $frais = $this->compteModel->calculFraisab(17);
            $montant_total = $montant + $frais;
            $montant_agence = $montant + ($frais * 0.65);
            $montant_commission = $frais*0.35;
            $commentaire='Cashout Partenaire';
            $service = 17;
        }
        else
        {
            $frais = $this->compteModel->calculFraisab(10);
            $montant_total = $montant;
            $montant_agence = $montant;
        }
        if($codevalidation != '' && $telephone != '' && $montant > 0 && $frais >= 0 && strlen($numtransact)==15)
        {
            if($soldecarte > $montant_total)
            {
                $codeValidation = $this->utils->rechercherCodeRetrait($codevalidation, $montant);
                if($codeValidation  > 0)
                {
                    $typecompte = $this->compteModel->getTypeCompte($telephone);
                    if($typecompte == 0)
                    {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->utils->getToken($userId);
                        $response = $this->api_numherit->debiterCompte($username, $token, $telephone, $montant_total, $service, $fkuser, $fkagence);
                        $decode_response = json_decode($response);
                        if($decode_response->{'statusCode'}==000)
                        {
                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};
                            $this->utils->validerCodeRetrait($codeValidation, $cni);
                            $this->agenceModel->crediter_soldeAgence($montant_agence, $fkagence);
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            if($this->userConnecter->idtype_agence==3)
                            {
                                $crediterCarteCommission = $this->utils->crediter_carteParametrable($montant_commission, 1);
                                if($crediterCarteCommission == 1)
                                {
                                    $observation = 'Commission Retrait Espece';
                                    $this->utils->addCommission($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                }
                                else
                                {
                                    $observation = 'Commission Retrait Espece à faire';
                                    $this->utils->addCommission_afaire($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                }
                            }
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $fkuser);
                            $this->rediriger('support','validationRetraitCompte/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact));
                        }
                        else
                        {
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $decode_response->{'statusMessage'}, 2, $fkuser);
                            $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                        }
                    }
                    else if($typecompte == 1)
                    {
                        $numcarte = $this->compteModel->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $statut = 0;
                        $json= $this->api_gtp->UnLoadCard($numtransact,$numeroserie, $last4digitclient,$montant_total,'XOF','RetraitEspece');
                        $return = json_decode("$json");
                        $response = $return->{'ResponseData'};
                        if($response != NULL && is_object($response))
                        {
                            if(array_key_exists('ErrorNumber', $response))
                            {
                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                                @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                                $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $errorNumber.'-'.$message, 2, $fkuser);
                                $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                            }
                            else
                            {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if($transactionId > 0)
                                {
                                    $statut = 1;
                                    $this->utils->validerCodeRetrait($codeValidation, $cni);
                                    $this->agenceModel->crediter_soldeAgence($montant_agence, $fkagence);
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactionId, $fkusersupport, $fkagencesupport);
                                    @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                                    if($this->userConnecter->idtype_agence==3)
                                    {
                                        $crediterCarteCommission = $this->utils->crediter_carteParametrable($montant_commission, 1);
                                        if($crediterCarteCommission == 1)
                                        {
                                            $observation = 'Commission Retrait Espece';
                                            $this->utils->addCommission($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                        }
                                        else
                                        {
                                            $observation = 'Commission Retrait Espece à faire';
                                            $this->utils->addCommission_afaire($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                        }
                                    }
                                    $this->utils->log_journal('Retrait support', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                                    $this->rediriger('support','validationRetraitCompte/'.base64_encode('ok').'/'.base64_encode($telephone).'/'.base64_encode($montant).'/'.base64_encode($frais).'/'.base64_encode($numtransact));
                                }
                            }
                        }
                        else
                        {
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                            $this->utils->log_journal('Retrait support', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                            $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok1').'/'.base64_encode($message));
                        }
                    }
                }
                else
                {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser , $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                    @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                    $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok2'));
                }
            }
            else
            {
                $message = 'Solde compte insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser, $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
                @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
                $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
                $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok3'));
            }
        }
        else
        {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte,$fkuser, $statut, $commentaire.' '.$message, $frais, $fkagence, $transactId, $fkusersupport, $fkagencesupport);
            @$this->utils->SaveTransactionSupport($numtransact, $service, $montant,$fkagencesupport, $fkusersupport, $statut);
            $this->utils->log_journal('Retrait compte', 'Téléphone compte:'.$telephone.' Montant:'.$montant.' Frais:'.$frais.' Numtransact:'.$numtransact, $message, 2, $fkuser);
            $this->rediriger('support','validationRetraitCompte/'.base64_encode('nok4').'/'.base64_encode($telephone));
        }
    }

    /***********Validation retrait Compte**********/
    public function validationRetraitCompte($return)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0]) === 'ok')
        {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' =>'GestionSupport/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['message_success_retrait_carte'], 'type-alert'=>'alert-success');
        }
        else if(base64_decode($return[0]) === 'nok1')
        {
            $message = base64_decode($return[1]);
            $params = array('view' =>'GestionSupport/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$message, 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok2')
        {
            $params = array('view' =>'GestionSupport/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['chargement_erreurcode_transact_save'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok3')
        {
            $params = array('view' =>'GestionSupport/retrait-espece-carte-fin', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['solde_insuffisant'], 'type-alert'=>'alert-danger');
        }
        else if(base64_decode($return[0]) === 'nok4')
        {
            $params = array('view' =>'GestionSupport/retrait-espece-carte-search', 'title' =>$data['lang']['retrait_carte'], 'alert'=>$data['lang']['message_alert'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /************************** Recu Retrait Espece **************/
    public function recuRetraitEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(172,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef']= $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'Gestionupport/retrait-carte-espece-facture', 'title' => $data['lang']['retrait_carte'] );
        $this->view($paramsview,$data);
    }


    public function getAllRgionByPays(){
        $pays = $this->utils->securite_xss($_POST['pays']);
        echo json_encode($this->utils->allRegionByPays($pays));
    }


    /**************************************************************************************************************/
    /********************************Transfert Teliman Support************************************************************/
    public function envoie()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(174,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['regions'] =  $this->supportModel->getAllRegion();
        $params = array('view' => 'GestionSupport/envoie');
        $this->view($params,$data);
    }

    public function allAgenceByRegion(){
        $region = $this->utils->securite_xss($_POST['region']);
        echo $this->supportModel->getAllAgenceByRegion($region);
    }

    public function allUserByAgent(){
        $agence = $this->utils->securite_xss($_POST['agence']);
        echo $this->supportModel->getAllUsersByAgence($agence);
    }
    
    public function allUserByAgentAndSoldeAgence(){
        $agence = $this->utils->securite_xss($_POST['agence']);
        echo $this->supportModel->getAllUsersByAgenceAndSoldeAgence($agence);
    }

    public function allUserByAgentAndSoldeAgence2(){
        $agence = $this->utils->securite_xss($_POST['agence']);
        echo $this->supportModel->getAllUsersByAgenceAndSoldeAgence2($agence);
    }

    /***************** Fonction faire un transferts *********************/
    public function AjoutEnvoie()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(174,$this->userConnecter->profil) );
        $agence = $this->utils->securite_xss($_POST['agence']);
        $user_sender = $this->utils->securite_xss($_POST['agent']);
        $user_support_sender = $this->userConnecter->rowid;
        $agence_support_sender = $this->userConnecter->fk_agence;
        $valueSource = $this->utils->getSoldeAgence($agence);
        $currencyCodeSource = 'XOF';
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = $this->utils->securite_xss($_POST['code']);
        $date_tranfert = $this->utils->securite_xss($_POST['date_tranfert']);
        $nom_sender = $this->utils->securite_xss($_POST['nom']);
        $prenom_sender = $this->utils->securite_xss($_POST['prenom']);
        $type_piece_sender = $this->utils->securite_xss($_POST['typepiece']);
        $piece_sender = $this->utils->securite_xss($_POST['piece']);
        $tel_sender = $this->utils->securite_xss($_POST['tel2']);
        $tel_sender = trim(str_replace("+", "00", $tel_sender));
        $pays_sender = $this->utils->securite_xss($_POST['pays']);
        $ville_sender = $this->utils->securite_xss($_POST['region']);
        $adresse_sender = $this->utils->securite_xss($_POST['adresse']);
        $nom_receiver = $this->utils->securite_xss($_POST['nom_dst']);
        $prenom_receiver = $this->utils->securite_xss($_POST['prenom_dst']);
        $tel_receiver = $this->utils->securite_xss($_POST['tel_dst2']);
        $tel_receiver = trim(str_replace("+", "00", $tel_receiver));
        $pays_receiver = $this->utils->securite_xss($_POST['pays_dst']);
        $ville_receiver = $this->utils->securite_xss($_POST['region_dst']);
        $adresse_receiver = $this->utils->securite_xss($_POST['adresse_dst']);
        $service = 11;
        $frais =  $this->utils->securite_xss($_POST['frais2']);
        $montant = $this->utils->securite_xss($_POST['montant']);

        $mtn_total = $montant + $frais;
        $montant_total = $mtn_total;
        $num_transac = $this->utils->generation_numTransaction();

        if($valueSource >= $mtn_total){

            $response = $this->transfertModel->debiter_soldeAgence($mtn_total, $agence);
            if($response == 1) {
                $statut = 1;
                $sens = 1;
                $credit_commission = $this->utils->crediterCarteCommission($frais);
                if($credit_commission == 1)
                {
                    $this->utils->addCommission($frais,$service,$agence," ","envoi d'argent");
                }
                else
                {
                    $this->utils->addCommission_afaire($frais,$service,$agence," ", "envoi d'argent");
                }

                $result_transfert = $this->supportModel->saveInfosTransfert($num_transac, $code, $montant, $frais, $montant_total, $date_tranfert, $nom_sender, $prenom_sender, $type_piece_sender, $piece_sender, $tel_sender, $pays_sender, $ville_sender, $adresse_sender, $nom_receiver, $prenom_receiver, $tel_receiver, $pays_receiver, $ville_receiver, $adresse_receiver, $service,$user_sender,$agence,0, $user_support_sender);
                if($result_transfert > 0){

                    $message = $data['lang']['mess_remborsement1'] . $prenom_sender . " " . $nom_sender . $data['lang']['mess_autre3'] . $montant . $data['lang']['code'].":" . $code . $data['lang']['mess_transfert4'];
                    $this->utils->sendSMS($data['lang']['paositra1'], $tel_receiver, $message);
                    $this->utils->log_journal("Envoi  du code " . $code,'', 'Envoie transfert', 'transfert', $this->userConnecter->rowid);

                    $result = $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $user_sender, $statut,'Envoi d\'argent OK', $frais, $agence,0, $user_support_sender,$agence_support_sender);
                    @$this->utils->SaveTransactionSupport($num_transac, $service, $montant,$agence_support_sender, $user_support_sender, $statut);
                    if ($result) {
                        $this->utils->save_DetailTransaction($num_transac, $agence, $montant, $sens);
                        $this->utils->log_journal("Envoi  d'argent " . 'transfert','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['envoi_ok'], 'transfert', $this->userConnecter->rowid);
                    } else {


                        $this->utils->log_journal("Envoi  d'argent " . 'transfert','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_ko'], 'transfert', $this->userConnecter->rowid);
                    }

                    $alert =$data['lang']['envoi_ok'];
                    $type_alert='text-green';
                    $this->rediriger('support','recapEnvoi/'.base64_encode($num_transac));

                }

            }
            else{
                $this->rediriger('support','erreurtransfert/'.base64_encode(-3));
            }

        }

        else{
            $this->rediriger('support','erreurtransfert/'.base64_encode(-2));
        }

    }

    /***************** Fonction recaptiulative sur  un transferts *********************/
    public function recapEnvoi($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(174,$this->userConnecter->profil) );
        $num_transac=base64_decode($id[0]);
        $data['infoenvoi'] =$this->transfertModel->getInfosTransfert($num_transac);
        $data['agence'] =$this->userConnecter->agence;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'GestionSupport/envoieRecap');
        $this->view($params,$data);
    }
    /***************** Fonction Erreur sur  un transferts *********************/
    public function erreurtransfert($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(174,$this->userConnecter->profil) );
        $res = base64_decode($id[0]);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if($res == "-1"){
            $message_transfert = 'Erreur web service: Donnees invalides';
        }
        else if($res == "-2"){
            $data['message_transfert']  = $data['lang']['solde_insuffisant_agence_source'];
        }
        else if($res == "-3"){
            $data['message_transfert']  = $data['lang']['error_debit_agence_source'];
        }else{
            $data['message_transfert'] = $data['lang']['error_survenue'];
        }
        $params = array('view' => 'GestionSupport/erreurtransfert');
        $this->view($params,$data);
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function getFrais()
    {
        $service =$this->utils->securite_xss($_POST['service']);
        $montant =$this->utils->securite_xss($_POST['mt']);
        echo  $this->transfertModel->calculFraisab($service,$montant);
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



    /***************** Fonction paiement d'un transferts *********************/
    public function paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(175,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/paiement');
        $this->view($params,$data);
    }
    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function confirm_paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(175,$this->userConnecter->profil) );
        $code=$this->utils->securite_xss($_POST['code']);
        $tel=$this->utils->securite_xss($_POST['tel']);
        $tel = trim(str_replace("+", "00", $tel));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['regions'] =$this->supportModel->getAllRegion();
        $data['infoenvoi'] =$this->transfertModel->getInfosTransfertByCode($code,$tel);
        if($data['infoenvoi']==-1){
            $params = array('view' =>'GestionSupport/paiement', 'title' =>$data['lang']['new_paiement'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }
        else{
            $start = $data['infoenvoi']->date_tranfert;
            $end = date('Y-m-d');
            $dateDiff = $this->dateDiff($start, $end);
            $params = array('view' => 'GestionSupport/confirm_paiement');
            $this->view($params,$data);
        }
    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function valider_paiement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(175,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(isset($_POST['tel_sender']) && isset($_POST['prenom_receiver']) && isset($_POST['nom_receiver']) &&
            isset($_POST['numtransaction']) && isset($_POST['montant']) && isset($_POST['idtransfert'])
            && isset($_POST['date_reception']) && isset($_POST['udid']) && isset($_POST['agence']) && isset($_POST['agent']))
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
            $fkagence =  $this->utils->securite_xss($_POST['agence']);
            $fkuser =  $this->utils->securite_xss($_POST['agent']);

            $fkusersupport =$this->userConnecter->rowid;
            $fkagencesupport =$this->userConnecter->fk_agence;
            $playerID =  $this->utils->securite_xss($_POST['udid']);

            $statut=1;
            $sens = 1;

            $res = $this->transfertModel->crediter_soldeAgence($montant, $fkagence);
            if($res == 1){
                $res = $this->supportModel->updateInfosPaiementA($fkuser, $date_receive, $type_piece, $piece, $idtransfert, $date_delivrance, $date_expiration, $fkusersupport, $fkagencesupport, $fkagence);

                if($res == 1){
                    $message =  $data['lang']['mess_transfert1'] . $montant . $data['lang']['mess_transfert2'] . $prenom_receiver . " " . $nom_receiver . $data['lang']['mess_transfert3'] . $code . $data['lang']['mess_transfert4'];
                    $this->utils->sendSMS("POSTCASH", $tel_sender, $message);

                    $this->utils->log_journal("Paiement  du code " . 'transfert','', 'Retrai transfert', 'transfert', $fkuser);
                    $id = base64_encode($numero_transaction);

                    $this->rediriger('support', 'paiement_recap/'.$id);
                }
                else{
                    $this->transfertModel->debiter_soldeAgence($montant, $fkagence);
                    $this->rediriger('support', 'paiement_recap/'.base64_encode('-1'));
                }
            }
            else{
                $this->rediriger('support', 'paiement_recap/'.base64_encode('-2'));
            }
        }
        else{
            $this->rediriger('support', 'paiement_recap/'.base64_encode('-3'));
        }
    }

    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function paiement_recap($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(175,$this->userConnecter->profil) );
        $numtransaction  = base64_decode($id[0]);
        if($numtransaction == '-1' || $numtransaction == '-2' || $numtransaction == '-3'){
            $data['infoenvoie'] = '-1';
        }
        else{
            $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        }

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/paiement_recap');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu *********************/
    public function impressionRecu()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(175,$this->userConnecter->profil) );
        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $currencyCode = 'XOF';
        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/GestionSupport/recu-transfert-cash.php';
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
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(178,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/historiquereception');
        $this->view($params,$data);
    }
    /***************** Fonction historique envoi admin *********************/
    public function historiqueReceptionadmin()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(178,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/historiquereceptionadmin');
        $this->view($params,$data);
    }

    /***************** Fonction paiement d'un transferts *********************/
    public function envoidataRecep($id)
    {
        $admin= $this->utils->securite_xss($id[0]);
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

        if($admin!=1){
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
            $nestedData[] = $row["libelle"];
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
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/remboursement');
        $this->view($params,$data);
    }

    /***************** Fonction Pour valider  un remboursement *********************/
    public function infoRemboursement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $code=$this->utils->securite_xss($_POST['code']);
        $tel=$this->utils->securite_xss($_POST['tel']);
        $tel = trim(str_replace("+", "00", $tel));

        $data['infoenvoi'] =$this->supportModel->getInfosTransfertByCode1($code,$tel);

        if($data['infoenvoi']==-1){
            $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
            $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
            $params = array('view' =>'GestionSupport/remboursement', 'title' =>$data['lang']['remboursement'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }
        else{
            $data['regions'] =$this->supportModel->getAllRegion();
            $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
            $params = array('view' => 'GestionSupport/remboursementInfo');
            $this->view($params,$data);

        }

    }

    /***************** Fonction Pour valider  un remboursement *********************/
    public function validerRemboursement()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $service = 28;
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais2 =  $this->transfertModel->calculFrais($service, $montant);
        $code = $this->utils->securite_xss(base64_decode($_POST['code']));
        $tel = $this->utils->securite_xss(base64_decode($_POST['tel']));
        $idtransfert = $this->utils->securite_xss( base64_decode($_POST['idtransfert']));
        $num_transac = $this->utils->generation_numTransaction();
        $fkagencesupport = $this->userConnecter->fk_agence;
        $fkusersupport = $this->userConnecter->rowid;
        $fk_agence = $this->utils->securite_xss($_POST['fk_agence']);
        $fk_user = $this->utils->securite_xss($_POST['fk_user']);
        $data['infoenvoi'] = $this->supportModel->getInfosTransfertByCode1($code,$tel);
        $nom_sender = $this->utils->securite_xss($data['infoenvoi']->nom_sender);
        $prenom_sender = $this->utils->securite_xss($data['infoenvoi']->prenom_sender);

        $response = $this->transfertModel->crediter_soldeAgence($montant, $fk_agence);
        if($response == 1){
            $statut = 1;
            $sens = 1;
            //commission non rembourser
            $etat = $this->supportModel->update_statut_remboursement($idtransfert, $fk_agence, $fk_user, $fkusersupport);

            $message = $data['lang']['mess_remborsement1']. $prenom_sender . " " . $nom_sender . $data['lang']['mess_remborsement2'] . $montant . $data['lang']['code']. ":" . $code . $data['lang']['mess_transfert4'];
            $this->utils->sendSMS("EdkCash", $tel, $message);
            $this->utils->log_journal("remboursement  du code " . $code,'', 'remboursement code', 'Remboursement', $this->userConnecter->rowid);
            $result = $this->utils->SaveTransaction($num_transac, $service, $montant, 0, $fk_user, $statut,'Remboursement d\'argent OK', $frais2, $fk_agence,0, $fkusersupport,$fkagencesupport);

            if ($result) {
                $savedet1 =  $this->utils->save_DetailTransaction($num_transac, $fk_agence, $montant, $sens);
            } else {

                $this->utils->log_journal("remboursement code " . 'remboursement code','',  $data['lang']['montant'] . ":" . $montant . " " . $data['lang']['code'] . ":" . $code . " " . $data['lang']['statut'] . ": " . $statut . " " . $data['lang']['numero_transaction'] . ":" . $num_transac, $data['lang']['comment']. ":" . $data['lang']['save_transact_KO'], 'Remboursement', $fk_user);

            }
            if($etat == 1){
                $id = base64_encode($this->utils->securite_xss($data['infoenvoi']->num_transac));
                $this->rediriger('support', 'remboursement_recap/'.$id);
            }
            else{
                $this->rediriger('support','erreurrembourse/'.base64_encode(-2));
            }
        }
        else{
            $this->rediriger('support','erreurrembourse/'.base64_encode(-3));
        }


    }
    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function remboursement_recap($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $numtransaction  = base64_decode($id[0]);
        $data['infoenvoi'] =$this->supportModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/remboursementRecap');
        $this->view($params,$data);
    }
    /***************** Fonction Erreur remboursemnt *********************/
    public function erreurrembourse($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $res = base64_decode($id[0]);

        if($res == "-1"){
            $message_transfert = 'Erreur web service: Donnees invalides';
        }
        else if($res == "-3"){
            $data['message_transfert']  = $data['lang']['solde_insuffisant_agence_source'];
        }
        else{
            $data['message_transfert'] = $data['lang']['error_survenue'];
        }
        $params = array('view' => 'GestionSupport/rembourserror');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu remboursement  *********************/
    public function impressionRecuRemboursement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(176,$this->userConnecter->profil) );
        $numtransaction  = $this->utils->securite_xss($_POST['transac']);
        $data['infoenvoie'] =  $this->transfertModel->getInfosTransfert($numtransaction);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/GestionSupport/recu-remboursement-cash.php';
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

    public function historiqueGestionCarte(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(173,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/histo-carte');
        $this->view($params,$data);
    }

    public function historiqueTeliman()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(178, $this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['admin'] =  $this->userConnecter->admin;
        $params = array('view' => 'GestionSupport/histo-teliman');
        $this->view($params,$data);
    }

    public function processingHistoCarte(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(173,$this->userConnecter->profil) );
        if($this->userConnecter->admin == 1 || $this->userConnecter->profil == 62){

            $param = [
                "button"=>[
                    [ROOT."support/detailHistoCarte/","fa fa-search"]
                ],
                "args"=>null,
                "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
            ];
            $this->processing($this->supportModel, "getHistoCarte", $param);
        }
        else{

            $param = [
                "button"=>[
                    [ROOT."compsupportte/detailHistoCarte/","fa fa-search"]
                ],
                "args"=>[$this->userConnecter->rowid],
                "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
            ];
            //var_dump($param);die;
            $this->processing($this->supportModel, "getHistoCarteByUserSupport", $param);
        }

    }


    public function processingHistoTeliman(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(178,$this->userConnecter->profil) );
        if($this->userConnecter->admin == 1 || $this->userConnecter->profil == 62){
            $param = [
                "button"=>[
                    //[ROOT."support/detailHistoTeliman/","fa fa-search"]
                ],
                "args"=>null,
                "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
            ];
            $this->processing($this->supportModel, "getHistoTeliman", $param);
        }
        else{
            $param = [
                "button"=>[
                    //[ROOT."support/detailHistoTeliman/","fa fa-search"]
                ],
                "args"=>[$this->userConnecter->rowid],
                "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
            ];
            $this->processing($this->supportModel, "getHistoTelimanByUserSupport", $param);
        }

    }

    public function annulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/annulation');
        $this->view($params,$data);
    }

    /***************** Fonction Pour valider  un remboursement *********************/
    public function infoAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant']);
        $code = $this->utils->securite_xss($_POST['code']);
        $tel=$this->utils->securite_xss($_POST['tel']);
        $tel = trim(str_replace("+", "00", $tel));


        $data['infoenvoi'] =$this->supportModel->getInfosTransfertByMontant($montant,$tel, $code);

        if($data['infoenvoi']==-1){
            $params = array('view' =>'GestionSupport/annulation', 'title' =>$data['lang']['ANNULATION'], 'alert'=>$data['lang']['erreur_code_telephone'], 'type-alert'=>'alert-danger');
            $this->view($params,$data);
        }
        else{
            $data['frais'] =  $this->transfertModel->calculFrais($service=31, $montant);
            $data['regions'] =$this->supportModel->getAllRegion();

            $params = array('view' => 'GestionSupport/annulationInfo');
            $this->view($params,$data);
        }
    }


    /***************** Fonction Pour valider  une annulation *********************/
    public function validerAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
        $service = 31;
        $montant = $this->utils->securite_xss($_POST['montant']);
        $code = $this->utils->securite_xss(base64_decode($_POST['code']));
        $tel = $this->utils->securite_xss(base64_decode($_POST['tel']));
        $idtransfert = $this->utils->securite_xss( base64_decode($_POST['idtransfert']));
        $num_transac = $this->utils->generation_numTransaction();
        $fkagencesupport = $this->userConnecter->fk_agence;
        $fkusersupport = $this->userConnecter->rowid;
        $fk_agence = $this->utils->securite_xss($_POST['fk_agence']);
        $fk_user = $this->utils->securite_xss($_POST['fk_user']);

        $data['infoenvoi'] = $this->supportModel->getInfosTransfertByCodeIdMontant($code, $tel, $idtransfert, $montant);

        if($data['infoenvoi'] != -1){
            $frais = $this->transfertModel->calculFrais($service, $data['infoenvoi']->montant);
            $mt_ttc = (intval($data['infoenvoi']->montant) + intval($data['infoenvoi']->frais)) - intval($frais);
            $nom_sender = $this->utils->securite_xss($data['infoenvoi']->nom_sender);
            $prenom_sender = $this->utils->securite_xss($data['infoenvoi']->prenom_sender);
            $response = $this->transfertModel->crediter_soldeAgence($mt_ttc, $fk_agence);
            if($response == 1){
                $statut = 1;
                $sens = 1;
                //commission non rembourser
                $etat = $this->supportModel->update_statut_annulation($idtransfert, $fk_agence, $fk_user, $fkusersupport);

                if($etat == 1){
                    $this->utils->log_journal("annulation  du code " . $code,'', 'annulation code', 'Annulation', $fk_user);
                    $this->utils->SaveTransaction($num_transac, $service, $mt_ttc, 0, $fk_user, $statut,'Annulation d\'argent OK', $frais, $fk_agence,0, $fkusersupport,$fkagencesupport);
                    $id = base64_encode($this->utils->securite_xss($data['infoenvoi']->num_transac));
                    $this->rediriger('support', 'annulation_recap/'.$id);
                }
                else{
                    $statut = 0;
                    $this->utils->log_journal("annulation  du code " . $code,'', 'annulation code echec', 'Annulation', $fk_user);
                    $this->utils->SaveTransaction($num_transac, $service, $mt_ttc, 0, $fk_user, $statut,'Annulation d\'argent KO', $frais, $fk_agence,0, $fkusersupport,$fkagencesupport);

                    $this->rediriger('support','erreurannulation/'.base64_encode(-2));
                }
            }
            else{
                $this->rediriger('support','erreurannulation/'.base64_encode(-3));
            }
        }
        else{
            $this->rediriger('support','erreurannulation/'.base64_encode(-1));
        }

    }
    /***************** Fonction de confirmation paiement d'un transferts *********************/
    public function annulation_recap($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
        $numtransaction  = base64_decode($id[0]);
        $data['infoenvoi'] =$this->supportModel->getInfosTransfert($numtransaction);
        $data['frais'] =$this->transfertModel->calculFrais($service=31, $data['infoenvoi']->montant);

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'GestionSupport/annulationRecap');
        $this->view($params,$data);
    }
    /***************** Fonction Erreur remboursemnt *********************/
    public function erreurannulation($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
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
        $params = array('view' => 'GestionSupport/annulationerror');
        $this->view($params,$data);
    }

    /***************** Fonction d'impression des recu remboursement  *********************/
    public function impressionRecuAnnulation()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(177,$this->userConnecter->profil) );
        $numtransaction  = $this->utils->securite_xss($_POST['transac']); 
        $data['infoenvoie'] =  $this->supportModel->getInfosTransfert($numtransaction);
        $data['frais'] =$this->transfertModel->calculFrais($service=31, $data['infoenvoi']->montant);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['effectuerpar'] = $this->userConnecter->prenom." ".$this->userConnecter->nom ;
        $data['agence'] = $this->userConnecter->agence;
        $currencyCode = 'XOF';

        //get the HTML
        ob_start();
        $imprime = __DIR__.'/../views/GestionSupport/recu-annulation-teliman.php';
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


}