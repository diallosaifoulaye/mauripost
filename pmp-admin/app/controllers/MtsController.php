<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */
class MtsController extends \app\core\BaseController
{
    private $model;
    private $agenceModel;
    private $actionModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->model('MtsModel');


        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste User*********/
    public function index($arg = null)
    {

        //$this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(246,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['mts_err3'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['mts_err1'];
            }elseif($arg == -5){
                $type_alert='error';
                $alert=$data['lang']['mts_err2'];
            }
        }
        $data['arg'] = $arg;

        //var_dump($data['arg']);die;


        $params = array('view' => 'mts/index','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function searchPaiement($arg = null){
        //var_dump($_POST);die;

        $res = $this->utils->getTokenMTS();
        //var_dump($res);die;
        $taux_de_change = 1;
        $frais = 0;
        $montant_xof = 0;
        $montant_a_payer = 0;

        $etat = 1;

        $json = json_decode($res);
        if ($json->Code == 1 && $json->Message != '') {
            $token = $json->Message;
            $transfertNO = $this->utils->securite_xss($_POST['transfertNO']);
            $telephone = $this->utils->securite_xss($_POST['telephone']);
            //$t = unPaidTransactionList($token, $transfertNO, $payeecode);
            //var_dump($t); die;
            $res_transac = $this->utils->getInfosTransaction($token, $transfertNO);
            $json = json_decode($res_transac);

            if(array_key_exists('resultModel', $json) && is_object($json->{'resultModel'})){

                $details_retour = $json->{'resultModel'};

                if($details_retour->{'Code'} === 1){
                    $etat = 1;

                    //$convert = json_decode(convertUSDTOXOF($details_retour->{'ReceivedCurrencyCode'}));
                    //var_dump($convert); die;
                    //if(array_key_exists($details_retour->{'ReceivedCurrencyCode'}.'_XOF', $convert)){
                        //$taux_de_change = $convert->{$details_retour->{'ReceivedCurrencyCode'}.'_XOF'}->{'val'};
                    //}
                    $montant_xof = $details_retour->{'ReceivedAmount'};
                    //$frais = getFrais($montant_xof);
                    $frais = 0;
                    $montant_a_payer = $montant_xof - $frais;

                    $_SESSION['details_retour'] = $details_retour;
                    //var_dump($_SESSION['details_retour']);die;
                    //$_SESSION['details_retour']['montant_a_payer'] = $montant_a_payer;
                    //$_SESSION['details_retour']['numeropiece'] = $montant_a_payer;
                }
                else{
                    $etat = -3;
                }
            }
            else{
                $etat = -3;
            }

        }
        else if($json->Code == 401){
            //Pas autorise
            $etat = -5;
        }
        else {
            //Pas de reponse
            $etat = -2;
        }
        //var_dump('yo');die;

        $this->rediriger("mts","index/".base64_encode($etat));

    }

    public function confirmPaiement(){
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        //echo '<pre>'; var_dump($_POST);die;
        $transfertNO = $this->utils->securite_xss($_POST['transfertNO']);
        $typepiece = $this->utils->securite_xss($_POST['typepiece']);
        $numeropiece = $this->utils->securite_xss($_POST['numeropiece']);
        $res = $this->utils->getTokenMTS();
        $taux_de_change = 1;
        $frais = 0;
        $montant_xof = 0;
        $montant_a_payer = 0;

        $json = json_decode($res);

        if ($json->Code == 1 && $json->Message != '') {
            $token = $json->Message;
            $res_transac = $this->utils->getInfosTransaction($token, $transfertNO);
            $json = json_decode($res_transac);
            //var_dump($json); die;

            if(array_key_exists('resultModel', $json) && is_object($json->{'resultModel'})){

                $details_retour = $json->{'resultModel'};

                if($details_retour->{'Code'} === 1){


                    $etat = 1;

                    /*$convert = json_decode(convertUSDTOXOF($details_retour->{'ReceivedCurrencyCode'}));
                    //var_dump($convert); die;
                    if(array_key_exists($details_retour->{'ReceivedCurrencyCode'}.'_XOF', $convert)){
                        $taux_de_change = $convert->{$details_retour->{'ReceivedCurrencyCode'}.'_XOF'}->{'val'};
                    }*/
                    $taux_de_change = 1;
                    $montant_xof = $details_retour->{'ReceivedAmount'};
                    //$frais = getFrais($montant_xof);
                    $frais = 0;
                    $montant_a_payer = $montant_xof - $frais;
                    $num_transac = $this->model->genererNumTransaction();
                    $date = date('Y-m-d H:i:s');
                    $service = 24;
                    $statut = 0;
                    $fk_agence = $this->userConnecter->fk_agence;
                    $user = $this->userConnecter->rowid;
                    $commentaire = $details_retour->{'ReceivedAmount'}.'-'.$details_retour->{'TransferNo'}.'-'.$details_retour->{'Status'}.'-'.$details_retour->{'SendDate'};
                    //$this->utils->SaveTransaction($num_transac, $date, $montant_xof, $user, $service, $statut, $frais, $montant_a_payer, $fk_agence, $commentaire, $details_retour->{'SendDate'});
                    $this->utils->SaveTransaction($num_transac, $service, $montant_xof,-1,$user, $statut, $commentaire, $frais, $fk_agence, $details_retour->{'SendDate'});

                    $res = $this->utils->getTokenMTS();
                    $json = json_decode($res);

                    if ($json->Code == 1 && $json->Message != '') {
                        $token = $json->Message;

                        $object_pay = $this->utils->payTransaction($token, $transfertNO);
                        //$object_pay = "{ 'Code': '1', 'Message': 'Success: Transaction has been Paid successfully', 'Detail': 'sp' }";
                        $json_pay = json_decode($object_pay);
                        $error_code = (int)$json_pay->{'Code'};
                        //echo $json_pay->{'Code'} ; die;
                        //$json_pay = json_decode(json_encode(array('Code' => 1)));
                        //var_dump($json_pay);die;


                        if($error_code === 0){
                            $etat = -1;
                            $msg = "Le numéro de téléphone n'est pas lié à ce code de transfert.";
                        }
                        else if($error_code === 1){
                            $statut = 1;
                            $this->model->saveTransfert($num_transac, $date, $montant_xof, $frais, $details_retour->{'SenderFirstName'}, $details_retour->{'SenderLastName'}, $details_retour->{'SenderPhone'}, $details_retour->{'SenderMiddleName'}, $details_retour->{'SenderAddress'}, $details_retour->{'SenderCity'}, $details_retour->{'SenderState'}, $details_retour->{'SenderCountry'}, $details_retour->{'ReceiverFirstName'}, $details_retour->{'ReceiverLastName'}, $details_retour->{'ReceiverMiddleName'}, $details_retour->{'ReceiverAddress'}, $details_retour->{'ReceiverPhone'}, $details_retour->{'ReceiverCity'}, $details_retour->{'ReceiverState'}, $details_retour->{'ReceiverCountry'}, $typepiece, $numeropiece, $details_retour->{'TransferNo'}, $details_retour->{'ReceivedAmount'}, $details_retour->{'SendCurrencyCode'}, $taux_de_change, $user, $statut, $fk_agence);
                            $this->model->changeStatutTransaction($num_transac, $commentaire, $statut);
                            $this->model->crediterSoldeAgence($fk_agence, $montant_xof);
                            $this->model->debiterSoldeAgence($fk_agence, $frais);
                            $_SESSION['details_retour'] = $details_retour;
                            $_SESSION['montant_xof'] = $montant_xof;
                            $_SESSION['frais'] = $frais;
                            $_SESSION['montant_a_payer'] = $montant_a_payer;
                            $_SESSION['num_transac'] = $num_transac;
                            $_SESSION['date'] = $date;
                            $_SESSION['typepiece'] = $typepiece;
                            $_SESSION['numeropiece'] = $numeropiece;

                            $etat = 0;
                        }
                        else if($error_code === 2){
                            $etat = -9;
                            $msg = "Le transfert correspondant a ete deja paye.";
                        }
                        else if($error_code === 3){
                            $etat = -8;
                            $msg = "Le transfert correspondant a ete annule.";
                        }
                        else if($error_code === 4){
                            $etat = -7;
                            $msg = "Le transfert est en attente de validation.";
                        }
                        else if($error_code === 5){
                            $etat = -6;
                            $msg = "La transaction n'est pas disponible pour le paiement.";
                        }
                        else{
                            $etat = -4;
                            $msg = "La validation du paiement a echoue.";
                        }
                    }
                    else if($json->Code == 401){
                        //Pas autorise
                        $etat = -5;
                        $msg = "Une erreur est survenue. Veuillez contacter l'administrateur de la plateforme";
                    }
                    else {
                        //Pas de reponse
                        $etat = -2;
                        $msg = "Le système est momentanément indisponible. Veuillez réessayer!";
                    }

                    //var_dump($json_pay); die;


                }
                else{
                    $etat = -3;
                    $msg = "Le code de transfert que vous avez saisi est incorrect.";
                }
            }
            else{
                $etat = -3;
                $msg = "Le code de transfert que vous avez saisi est incorrect.";
            }

        }
        else if($json->Code == 401){
            //Pas autorise
            $etat = -5;
            $msg = "Une erreur est survenue. Veuillez contacter l'administrateur de la plateforme";
        }
        else {
            //Pas de reponse
            $etat = -2;
            $msg = "Le système est momentanément indisponible. Veuillez réessayer!";
        }

        $this->rediriger("mts","retourPaiement/".base64_encode($etat));

    }


    public function retourPaiement($arg = null)
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1){
                $type_alert='error';
                $alert=$data['lang']['mts_err4'];
            }
            elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['mts_err3'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['mts_err6'];
            }elseif($arg == -4) {
                $type_alert='success';
                $alert=$data['lang']['mts_err5'];
            }elseif($arg == -5) {
                $type_alert='success';
                $alert=$data['lang']['mts_err2'];
            }elseif($arg == -6) {
                $type_alert='success';
                $alert=$data['lang']['mts_err7'];
            }elseif($arg == -7) {
                $type_alert='success';
                $alert=$data['lang']['mts_err8'];
            }elseif($arg == -8) {
                $type_alert='success';
                $alert=$data['lang']['mts_err9'];
            }elseif($arg == -9){
                $type_alert='error';
                $alert=$data['lang']['mts_err10'];
            }
        }
        $data['arg'] = $arg;

        //var_dump($data['arg']);die;


        $params = array('view' => 'mts/retourPaiement','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function printRecuMTS()
    {

        $data['mts'] = $this->utils->securite_xss_array($_POST);
        $data['mts']['agence'] = $this->userConnecter->agence;
        $data['mts']['name'] = $this->userConnecter->prenom.' '.$this->userConnecter->nom;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //get the HTML
        ob_start();
        $imprime = __DIR__ . '/../views/mts/recumts.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__ . '/../../assets/html2pdf/html2pdf.class.php';

        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuMTS.pdf', 'I');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }

    public function dailyTrans($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(247,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_dist'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_dist'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_dist'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_dist'];
            }
        }

        $params = array('view' => 'mts/dailyTrans','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function processingDailyTrans()
    {
        $deb = $_POST['4'].$_POST['5'].$_POST['6'].$_POST['7'].$_POST['8'].$_POST['9'].$_POST['10'].$_POST['11'].$_POST['12'].$_POST['13'];
        $fin = $_POST['19'].$_POST['20'].$_POST['21'].$_POST['22'].$_POST['23'].$_POST['24'].$_POST['25'].$_POST['26'].$_POST['27'].$_POST['28'];

        $param = [
            "button"=>[
                [ROOT . "mts/detailTrans/", "fa fa-search"]
            ],
            "args" => ['deb'=>$deb, 'fin'=>$fin],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        //echo '<pre>'; var_dump($deb.' '.$fin);die;
        $this->processing($this->model, "historiques", $param);
    }

    public function detailTrans($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['trans'] = $this->model->detailsHistorique(base64_decode($id[0]));

        //var_dump($data['trans']);die;

        $params = array('view' => 'mts/detailTrans');
        $this->view($params, $data);
    }

    public function histoTrans()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(248,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'mts/transaction-search');
        $this->view($params,$data);
    }

    public function historiqueTransaction()
    {
        //var_dump($_POST);die;
        $data['deb'] = $this->utils->securite_xss($_POST['datedeb']);
        $data['fin'] = $this->utils->securite_xss($_POST['datefin']);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'mts/transaction-historique');
        $this->view($params,$data);
    }

}