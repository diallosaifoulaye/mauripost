<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class GamswitchController extends \app\core\BaseController
{
    private $userModel;
    private $userConnecter;
    private $transaction;
    private $date ;
    private $idcompte_gamswitch;
    private $idcompte_commission ;
    private $service ;


    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('UtilisateurModel');
        $this->gamswitchModel = $this->model('GamswitchModel');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
        $this->date = date('Y-m-d H:i:s');
        $this->idcompte_gamswitch = 7;
        $this->idcompte_commission = 1;
        $this->service = 37;


    }

    /************* default action **************/
    public function index($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil,32));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $datedeb = date('Y-m-d');
        $datefin = date('Y-m-d');

        //var_dump($datefin);die;
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        $data['reporting'] = $this->gamswitchModel->transactionJour($datedeb, $datefin,  $type_profil, $this->userConnecter->rowid, $this->userConnecter->fk_agence);

        $params = array('view' => 'gamswitch/index');
        $this->view($params,$data);
    }
    public function facture_jour()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date2']);
        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);
        $data['recu'] = $this->gamswitchModel->transactionJour($date1, $date2,  $type_profil, $this->userConnecter->rowid, $this->userConnecter->fk_agence);
        // var_dump($data['recu']);die;
        $params = array('view' => 'gamswitch/facture_jour');
        $this->view($params,$data);
    }
    public function detailTransact($id)
    {
       // var_dump($id);die;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $num_transac = (int)base64_decode($this->utils->securite_xss($id[0]));
        $data['detail'] = $this->gamswitchModel->detailTransasction($num_transac);
       // var_dump($data['detail']);die;
        $params = array('view' => 'gamswitch/transactiondetails');
        $this->view($params,$data);
    }
    /***************** recu duplicata *********************/
    public function factureduplicata($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $num_transac = $this->utils->securite_xss($_POST['num_transac']);
        $data['recu'] = $this->gamswitchModel->detailTransasction($num_transac);
        $params = array('view' => 'gamswitch/duplicata');
        $this->view($params,$data);
    }
    /************* Liste Transaction **************/
    public function histoGamswitch()
    {
        $datedeb = date('Y-m-d');
        $datefin = date('Y-m-d');


        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        $data['reporting'] = $this->gamswitchModel->transactionJour($datedeb, $datefin,  $type_profil, $this->userConnecter->rowid, $this->userConnecter->fk_agence);
        $params = array('view' => 'gamswitch/index');
        //var_dump($data['reporting']);die;
        $this->view($params,$data);
    }
    public function paiement($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 33));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));


        $params = array('view' => 'gamswitch/new-purchase');
        $this->view($params,$data);
    }
    public function custumerValidation($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 33));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $frais = '';
        $customerName = '';


        $compteur = $this->utils->securite_xss($_POST['compteur']);
        $customerMobile = ($this->utils->securite_xss($_POST['mobile']));
        $customerEmail = trim($this->utils->securite_xss($_POST['email']));
        $amount =intval(trim($this->utils->securite_xss($_POST['montant'])));
      // var_dump(intval($amount));
       // var_dump($customerMobile);die;
        $_SESSION['numero_compteur']=$compteur;
        $_SESSION['mobile']='+'.$customerMobile;
        $_SESSION['mobile']=str_replace(' ','',$_SESSION['mobile']);
        $_SESSION['email']=$customerEmail;
        $_SESSION['montant']=$amount;
        $mob=str_replace('+','',$customerMobile);
       // $token=$this->gamswitchModel->generate_token();
       // var_dump($token);die;
       // var_dump(($_SESSION['mobile']));

        $result = $this->gamswitchModel->customer_validation($compteur,$mob,$amount)  ;

        $data = json_decode($result);
       // $customerId = "07000000013";
       // $customerMobile = "00221775373761";
       // $amount = 8000;

       // echo generate_token();

        /*echo register();
        {"Username":"Numherit","FirstName":"Numherit","LastName":"Numherit","Password":"ToubaM28@1927","PhoneNumber":"00221775373761","Email":"bocar@numherit.com"}
        $response = '{"HashKey":"17c51a73-3189-4d18-a58b-be2eeac32a59","SecurityKey":"oxTu3o4mC4JXmTAT9VbWdPoi5QckxIIOO+63Rmeb2h0VFPFAk3BMEtWrF3JNe5V1IHbMyBevDBKr/i3AfmYbf56KehiXhIPb8PQwUvnBcv7HnDupoORJqqb1dMQArUHj9V0H12bOChGzsuYHPOA2xBezK3C6RidyzWnSkMzFJ/c=AQAB"}';*/

        //echo $data=$this->gamswitchModel->payment($customerId,$customerMobile,$amount);
       // $response='{"nawec":{"ErrorText":"","CustomerName":"Mr. Ted Smith","Meter":"07000000013","Tokens":"PIN: 4445 0263 3933 9781 9655","VAT":"9.82","Amount":"R 79.95","AmountKWH":"58.5","Fee":"4.00","ReceiptNumber":"87906-70","ExpiryDate":"Thu Aug 10 00:00:00 BST 2017"},"responseCode":0,"responseDescription":"Approved"}';*/

       // echo $data =$this->gamswitchModel->customer_validation($customerId,$customerMobile,$amount) ;
        //$response='{"nawec":{"ErrorText":null,"CustomerName":"TED SMITH","Meter":null,"Tokens":null,"VAT":null,"Amount":null,"AmountKWH":null,"Fee":"4.00","ReceiptNumber":null,"ExpiryDate":null},"responseCode":0,"responseDescription":"Approved"}';*/

        //echo last_token_reprint($customerId,$customerMobile,$amount) ;

       //var_dump($data);die;
       if(is_object($data))
        {
            $code = $data->{'ResponseCode'};
           // var_dump($code);
            if($code == 0   )
            {
                $customerName = $data->{'nawec'}->{'CustomerName'};
                $frais = $data->{'nawec'}->{'Fee'}*100;
                echo $code.'@'.$customerName.'@'.$frais;
                $_SESSION['frais']=$frais;
                $_SESSION['customerName']=$customerName;
            }
            else
                echo $data->{'responseDescription'}.'@'.$data->{'nawec'}->{'ErrorText'};
        }
        else
            echo 111;


       // $params = array('view' => 'gamswitch/new-purchase');
       // $this->view($params,$data);
    }
    public function gestionCompense($montant, $frais, $agence_connecte, $compteur, $utilisateur, $code, $order_number,$type_agence,$numero_transaction)
    {
        /*******************debut traitement compense*******************/
        $type_agence=$this->gamswitchModel->getTypeAgence($this->userConnecter->fk_agence);
        $debit_agence = $this->gamswitchModel->debiter_soldeAgence($montant, $frais, $agence_connecte,$type_agence);

        if($debit_agence == 1)
        {
            //$frais = $this->getFrais();
            $ladate = date('Y-m-d H:i:s');
            //$numero_transaction = $this->Generer_numtransaction();
            $numcarteWoyofal =$this->gamswitchModel->GetcarteGamswitch();
            $json =$this->gamswitchModel->crediter_compteParametrable($montant, $this->idcompte_gamswitch );
            if($json==1)
            {
                $statut = 1;
               $this->gamswitchModel->SaveTransaction_carte($this->service,$montant,$numcarteWoyofal,$utilisateur, $statut,$numero_transaction, $frais, $agence_connecte);
                $this->utils->log_journal("Achat code Gamswitch : ".$ladate," Code :".$code." - montant :".$montant." - compteur :".$compteur,$utilisateur,"Achat code OK",7);
                /******debut paiement commission en transferant dans la carte commission**********/
                if($frais != 0)
                {
                    //$numcartecomm = $this->Get_carteCommisssion();//Recuperation du numero de la carte commission

                    $numtransactioncom =$this->gamswitchModel->Generer_numtransaction();
                    //$debit_agence_com = @debiter_soldeAgence($frais, $agence_connecte);
                    $debit_agence_com = 1;
                    if($debit_agence_com == 1)
                    {
                        if($type_agence==3)
                            $mont_frais=$frais-300;
                        else
                            $mont_frais=$frais;
                        $jsoncom =$this->gamswitchModel->crediter_compteParametrable($mont_frais, $this->idcompte_commission);

                        if ($jsoncom==1)
                        {
                            //Distribution des commissions dans la table transaction_commission
                            $result =$this->gamswitchModel->addCommission($mont_frais,$this->service,$agence_connecte,$numcartecomm,$numtransactioncom);
                        }
                        else
                        {
                           $this->gamswitchModel->addCommission_afaire($mont_frais,$this->service,$agence_connecte,$numcartecomm, $observations="");
                        }
                    }
                    else
                    {
                       $this->gamswitchModel->addCommission_afaire($mont_frais,$this->service,$agence_connecte,$numcartecomm, 'Erreur debit soldeagence');
                    }
                }
                /*************************fin traitement commission*******************************/
            }
            else
            {
                $statut = 0;
                $errorCode = $json;

                //SaveTransaction($this->service,$montant,$numcarteWoyofal,$utilisateur,0, $statut, $numero_transaction, $errorCode,$ladate)
               $this->gamswitchModel->SaveTransaction_carte($this->service,$montant,$numcarteWoyofal,$utilisateur, $statut,$numero_transaction, $frais, $agence_connecte);
            }
        }
        else
        {
            $statut = 0;
            // echo 'bocar3';
           $this->gamswitchModel->SaveTransaction_carte($this->service,$montant,$numcarteWoyofal,$utilisateur, $statut,$numero_transaction, $frais, $agence_connecte);
        }
        /*******************fin traitement compense*******************/
    }

    public function customerPayment()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 33));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $compteur = $this->utils->securite_xss($_POST['compteur']);
        $customerMobile = ($this->utils->securite_xss($_POST['mobile']));
        $customerEmail = trim($this->utils->securite_xss($_POST['email']));
        $amount = intval(trim($this->utils->securite_xss($_POST['montant'])));
        $frais = intval(trim($this->utils->securite_xss($_POST['frais'])));
        $mt = $amount + $frais;
        $client = trim($this->utils->securite_xss($_POST['client']));

        $num_transact = $this->gamswitchModel->generateNumeroTransaction();

        $numero_transaction = $this->utils->Generer_numtransactions();
        //echo "<pre>"; var_dump($_POST);
        $utilisateur = $this->userConnecter->rowid;
        $agence_connecte = $this->userConnecter->fk_agence;
        $_SESSION['numero_compteur']=$compteur;
        $_SESSION['mobile']='+'.$customerMobile;
        $_SESSION['mobile']=str_replace(' ','',$_SESSION['mobile']);
        $_SESSION['email']=$customerEmail;
        $_SESSION['montant']=$amount;
        $appel = 0;
        $mob=str_replace(' ','',$customerMobile);
       // var_dump($mob);
       // echo "<pre>"; var_dump($compteur);
       // echo "<pre>"; var_dump($customerMobile);
        //echo "<pre>"; var_dump($amount);
        $result = $this->gamswitchModel->payment($compteur,$mob,$amount);
//var_dump($result);die;
        $result = html_entity_decode($result);
        $data = json_decode($result);
       // var_dump($data);die;
        if(is_object($data))
        {
            $code = $data->{'responseCode'};
            if($code == 0){
                $token = $data->{'nawec'}->{'Tokens'};
                $tab = explode(':',$token);
               // var_dump($token) ;
                $customerPIN = $tab[1];
               // $customerPIN = $tab[1];
            }
            else
                echo $message=   $data->{'responseDescription'}.'@'.$data->{'nawec'}->{'ErrorText'};

            //$message='OK';

            $save = $this->gamswitchModel->saveTransaction($compteur,$token,$numero_transaction,$mob,$amount,$frais,1,$num_transact,$client,$client,$this->userConnecter->fk_agence,$this->userConnecter->rowid);
            $this->gestionCompense($amount, $frais, $this->userConnecter->fk_agence, $compteur, $this->userConnecter->rowid, $customerPIN, $num_transact,$type_agence,$numero_transaction);
            echo  $code.'@'.$num_transact.'@'.$message.'@'.$customerPIN.'@'.$num_transact;
        }


    }
    public function validatePurchase()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 33));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $num_transac = $_GET['ref'];

        $data['rslt'] = $this->gamswitchModel->getTransaction($num_transac);
       // var_dump($data['rslt']);die;
        $params = array('view' => 'gamswitch/validate_purchase');
        $this->view($params,$data);

    }


    public function achatCredit()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 33));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        // $customerId = "07000000013";
       // $PhoneNumber = "00221775373761";
        $PhoneNumber = "2202244096";
        $type="comium";
        $Amount = 8000;

        $data['rslt'] = $this->gamswitchModel->buyCredit($type,$PhoneNumber,$Amount);
         var_dump($data['rslt']);die;
        //$params = array('view' => 'gamswitch/validate_purchase');
       // $this->view($params,$data);

    }

}