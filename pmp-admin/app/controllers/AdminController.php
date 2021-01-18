<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class AdminController extends \app\core\BaseController
{
    private $profilModel;
    private $agenceModel;
    private $moduleModel;
    private $userModel;
    private $serviceModel;
    public  $actionModel;
    public  $messageModel;
    private $typeprofilModel;
    private $virementModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();
        $this->profilModel = $this->model('ProfilModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->moduleModel = $this->model('ModuleModel');
        $this->userModel = $this->model('UtilisateurModel');
        $this->serviceModel = $this->model('ServiceModel');
        $this->actionModel = $this->model('ActionModel');
        $this->virementModel = $this->model('VirementModel');
        $this->typeprofilModel = $this->model('TypeProfilModel');
        $this->messageModel = $this->model('MessageModel');



        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    public function topup()
    {
        $CardNumber = "1111222233334444";
        $PhoneNumber = "2215373761";
        $Amount = "8000";
        $nonce = time();
        $url = 'https://197.231.130.7/';
        $key = "17c51a73-3189-4d18-a58b-be2eeac32a59";
        $timestamp = time();
        $token = "RpR-PqK_3EE1IQuBVJyKHip6POnioCm9pAQTjgOjT7WFJhGEqVAcmtjDOpfVufItDV15hJzVI7DFkQoVT0334Fce-IipUfhSI6NAu_QW4p1MKB2YUI-YGqDKnn5bKdVcBMSafTnTzPKtEjWhns-m4dbXC7cDdxVcroL3Qd2UHeVczRN8Ng_Xgk64NPse_jU1N1IOE7g2du4zx25PZ1Wkl6OkfSZpDlWgOYAJY-bhKn-ZzZPQcGqkT2Mcw2dVZNjFe3XbSPo1K7VPYWUWB9lkYg0uAaZ9kEVW7gbUngDEu5G40eeHvZfd9nitKv01KP1stYY8YUFlI-KNRFX8p3EDcRf5G1wW-dBdm1wKUZy2jNLvE6vkSfIugbW1IXf_H25YHNEiELPGa5Z9klg8QYBTemQrtJtxam0N8SUF5KLvUXvRV5ZolRV8BN77OasASipbAzBMgXsnfudnV6xo4QHe_Eu__M3zxN70rF3IkgRWWTZxzSQ6AUpZD0HMT4URrnW5CFKZNRkskXAefwt9EmP6cQ";

        $baseStringToBeSigned = $key."airtime"."africell".$nonce.$timestamp.$PhoneNumber;
        $signature = hash('sha512',$baseStringToBeSigned);
        $ch = curl_init();

        $card = array(
            "Number" => "1111222233334444",
            "ExpiryDate" => "3501",
            "Amount" => "123",
            "PIN" => "1234",
        );

        $data = array(
            "Type" => "africell",
            "PhoneNumber" => "2215373761",
            "Amount" => "8000",
            //"Card" => $card,
            //"AccountType" => "Default",
            //"AccountNumber" => "2010100001019040100",
        );

        $request = json_encode($data);
        $headers = array(
            'Authorization: Bearer '.$token,
            "Content-Type: application/json",
            "Accept: application/json",
            "Nonce: ".$nonce,
            "Timestamp: ".$timestamp,
            "Signature: ".$signature
        );
        $headers;
        curl_setopt($ch, CURLOPT_URL, $url."api/airtime/gswtest");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        echo $result;
    }

    /*********Liste User*********/
    public function index()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 1) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/accueil');
        $this->view($params,$data);
    }

    /*********Liste User*********/
    public function users()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(70,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['typeagence']= $this->agenceModel->allTypeAgence();
        $params = array('view' => 'admin/user');
        $this->view($params,$data);
    }


    /*********Liste User*********/
    public function tarifs()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(250,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/tarif');
        $this->view($params,$data);
    }

    /*************Insert User**************/
    public function inserUser()
    {

        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));;
        $profil = $this->utils->securite_xss($_POST['profil']);
        $idtype_agence = $this->utils->securite_xss($_POST['idtype_agence']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $login = $this->utils->securite_xss($_POST['login']);
        $password = $this->utils->generation_code(10);
        $user_creation = $this->userConnecter->rowid;

        $verif = $this->userModel->verifEmail($email);

        if($verif == -1){
            $insert = $this->userModel->insertUser($nom, $prenom, $email, $telephone, $profil, $agence, $login, $password, $user_creation);

            if($insert==1)
            {
                if($idtype_agence == 1){
                    $this->utils->envoiparametre($prenom.' '.$nom, $email, $login, $password);
                }
                if($idtype_agence == 3){
                    $this->utils->envoiparametreDistributeur($prenom.' '.$nom, $email, $login, $password);
                }

                $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'succes', 1, $user_creation);
                $this->rediriger('admin','validationInsert/'.base64_encode('ok'));
            }
            else
            {
                $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
                $this->rediriger('admin','validationInsert/'.base64_encode('nok'));
            }
        }
        else if($verif == 1){
            $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
            $this->rediriger('admin','validationInsert/'.base64_encode('nok2'));
        }
        else{
            $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
            $this->rediriger('admin','validationInsert/'.base64_encode('nok3'));
        }


    }

    /*************Insert User*************
    public function insertTarif()
    {
        $service = $this->utils->securite_xss($_POST['service']);
        $typecom = $this->utils->securite_xss($_POST['typecom']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $user_creation = $this->userConnecter->rowid;
        if($typecom === 'p'){
            $montant = -1;
        }
        $verif = $this->serviceModel->verifService($service);

        if($verif == -1){
            $insert = $this->serviceModel->insertTarif($service, $montant, $user_creation);
            if($insert > 0)
            {
                $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'succes', 1, $user_creation);
                $this->rediriger('admin','detailsTarif/'.base64_encode($insert));
            }
            else
            {
                $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'echec', 1, $user_creation);
                $this->rediriger('admin','tarif/'.base64_encode('nok2'));
            }
        }
        else{
            $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'echec', 1, $user_creation);
            $this->rediriger('admin','tarifs/'.base64_encode('nok1'));
        }
    }*/

    /*************Insert User**************/
    public function insertTarif()
    {
        $service = $this->utils->securite_xss($_POST['service']);
        $typecom = $this->utils->securite_xss($_POST['typecom']);
        $distributeur = 0;
        $taux = 0;
        if(isset($_POST['distributeur']) && $_POST['distributeur'] != null){
            $distributeur = $this->utils->securite_xss($_POST['distributeur']);
            //$taux = $this->utils->securite_xss($_POST['taux_commission']);
            $taux = 0;
        }

        $montant = $this->utils->securite_xss($_POST['montant']);
        $user_creation = $this->userConnecter->rowid;
        if($typecom === 'p'){
            $montant = -1;
        }
        $verif = $this->serviceModel->verifService($service);

        if($verif == -1){
            $insert = $this->serviceModel->insertTarif($service, $montant, $user_creation,intval($distributeur),intval($taux));
            if($insert > 0)
            {
                $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'succes', 1, $user_creation);
                $this->rediriger('admin','detailsTarif/'.base64_encode($insert));
            }
            else
            {
                $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'echec', 1, $user_creation);
                $this->rediriger('admin','tarif/'.base64_encode('nok2'));
            }
        }
        else{
            $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant, 'echec', 1, $user_creation);
            $this->rediriger('admin','tarifs/'.base64_encode('nok1'));
        }
    }


    public function deletePallier(){
        $idfrais = base64_decode($this->utils->securite_xss($_POST['idfrais']));
        $idtarif = base64_decode($this->utils->securite_xss($_POST['idtarif']));
        $insert = $this->serviceModel->deletePallier($idfrais, $idtarif);
        $user_creation = $this->userConnecter->rowid;
        if($insert > 0)
        {
            $this->utils->log_journal('Suppression pallier', 'Pallier:'.$idfrais.' - Service:'.$idtarif, 'succes', 1, $user_creation);
            $this->rediriger('admin','detailsTarif/'.base64_encode($idtarif).'/'.base64_encode('okp1'));
        }
        else
        {
            $this->utils->log_journal('Suppression pallier', 'Pallier:'.$idfrais.' - Service:'.$idtarif, 'echec', 1, $user_creation);
            $this->rediriger('admin','detailsTarif/'.base64_encode($idtarif).'/'.base64_encode('nokp3'));
        }

    }

    /***********Validation Insert User**********/
    public function validationInsert($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['typeagence']= $this->agenceModel->allTypeAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_success_add_user'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user'], 'type-alert'=>'alert-danger');
        }
        elseif(base64_decode($return[0])=== 'nok2'){
            $params = array('view' =>'admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user2'], 'type-alert'=>'alert-danger');
        }
        elseif(base64_decode($return[0])=== 'nok3'){
            $params = array('view' =>'admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user3'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*********detailUser********/
    public function detailUser($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();
        $data['user']= $this->userModel->getUser(base64_decode($id[0]));
        $params = array('view' => 'admin/user-detail');
        $this->view($params,$data);
    }


    /*********detailUser********/
    public function detailsTarif($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['tarif'] = $this->serviceModel->getTarif(base64_decode($id[0]));


        if($data['tarif']->frais == -1){
            $data['mtmin'] =  $this->serviceModel->getMinMontant(base64_decode($id[0]));
            $data['pallier']= $this->serviceModel->getPallierTarif(base64_decode($id[0]));
        }


        if(count($id) > 1){
            if(base64_decode($id[1])=== 'okp'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['add_pallier'], 'alert'=>$data['lang']['message_success_add_pallier'], 'type-alert'=>'alert-success');
            }
            elseif(base64_decode($id[1])=== 'okp1'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['suppression_pallier'], 'alert'=>$data['lang']['message_success_delete_pallier'], 'type-alert'=>'alert-success');
            }
            elseif(base64_decode($id[1])=== 'nokp3'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['suppression_pallier'], 'alert'=>$data['lang']['message_error_delete_pallier'], 'type-alert'=>'alert-danger');
            }
            elseif(base64_decode($id[1])=== 'nokp2'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['add_pallier'], 'alert'=>$data['lang']['message_error_add_pallier2'], 'type-alert'=>'alert-danger');
            }
            elseif(base64_decode($id[1])=== 'nokp1'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['add_pallier'], 'alert'=>$data['lang']['message_error_add_pallier1'], 'type-alert'=>'alert-danger');
            }
            elseif(base64_decode($id[1])=== 'nokp'){
                $params = array('view' =>'admin/tarif-detail', 'title' =>$data['lang']['add_pallier'], 'alert'=>$data['lang']['message_error_add_pallier'], 'type-alert'=>'alert-danger');
            }
            else{
                $params = array('view' => 'admin/tarif-detail');
            }
        }
        else{
            $params = array('view' => 'admin/tarif-detail');
        }


        //
        $this->view($params,$data);
    }

    public function getAllOthersAgence(){
        $allAg = $this->agenceModel->allAgenceWithoutSelected($this->utils->securite_xss($_POST['expediteur']));
        echo $allAg;
    }

    /*********detailUser********/
    public function getUserAgence()
    {
        $users = $this->userModel->allUserAgence($_POST['agence']);
        $soldeAgence = $this->utils->getSoldeAgence($this->utils->securite_xss($_POST['agence']));
        echo json_encode(array('user'=>$users, 'soldeagence'=>$soldeAgence));
    }

    /*************update User**************/
    public function updateUser()
    {
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);

        $email = $this->utils->securite_xss($_POST['email']);
        $emailOLD = $this->utils->securite_xss($_POST['emailOLD']);

        $telephone = $this->utils->securite_xss($_POST['phone']);
        $profil = $this->utils->securite_xss($_POST['profil']);
        $agence = $this->utils->securite_xss($_POST['agence']);
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));

        $update = $this->userModel->updateUtilisateur($nom, $prenom, $email, $telephone, $profil, $agence, $rowid, $user_modification);
        if($update==1)
        {
            if($email != $emailOLD){
                $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
                $this->utils->envoiNotifUpdateEmail($prenom.' '.$nom, $email, $emailOLD, $data['lang']);
            }
            $this->utils->log_journal('Modification Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Profil:'.$profil, 'succés', 1, $user_modification);
            $this->rediriger('admin','validationUpdate/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Profil:'.$profil, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationUpdate/'.base64_encode('nok'));
        }
    }


    /*************update User**************/
    public function updateTarif()
    {
        $service = $this->utils->securite_xss($_POST['service']);
        $typecom = $this->utils->securite_xss($_POST['typecom']);
        $montant = $this->utils->securite_xss($_POST['montant']);

        $distributeur = $this->utils->securite_xss($_POST['distributeur']);

        if($distributeur == "1") {
            $taux = $this->utils->securite_xss($_POST['taux_commission']);
        } else {
            $taux = 0;
        }

        if($typecom === 'p'){
            $montant = -1;
        }
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['idtarif']));

        $update = $this->serviceModel->updateTarif($service, $montant, $rowid, $user_modification,$distributeur,$taux);
        if($update==1)
        {
            $this->utils->log_journal('Ajout Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant.' Id:'.$rowid, 'succes', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid));
        }
        else
        {
            $this->utils->log_journal('Modification Service', 'Service:'.$service.' Type com:'.$typecom.' Montant:'.$montant.' Id:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nok'));
        }
    }

    public function addPallier(){
        $mtmin = $this->utils->securite_xss($_POST['mtmin']);
        $mtmax = $this->utils->securite_xss($_POST['mtmax']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $tva = $this->utils->securite_xss($_POST['tva']);
        $rowid = base64_decode($this->utils->securite_xss($_POST['idtarif']));
        $user_modification = $this->userConnecter->rowid;

        if($mtmin < $mtmax){

            $verif = $this->serviceModel->verifierPallier($mtmin, $rowid);
            if($verif == 0){
                $update = $this->serviceModel->addPallier($mtmin,$mtmax , $montant, $rowid, $tva);
                if($update==1)
                {
                    $this->utils->log_journal('Ajout Pallier', 'Montant min:'.$mtmin.' Montant max:'.$mtmax.' Commission:'.$montant.' Id:'.$rowid, 'succes', 1, $user_modification);
                    $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('okp'));
                }
                else
                {
                    $this->utils->log_journal('Ajout Pallier', 'Montant min:'.$mtmin.' Montant max:'.$mtmax.' Commission:'.$montant.' Id:'.$rowid, 'echec', 1, $user_modification);
                    $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nokp2'));
                }
            }
            else{
                $this->utils->log_journal('Ajout Pallier', 'Montant min:'.$mtmin.' Montant max:'.$mtmax.' Commission:'.$montant.' Id:'.$rowid, 'echec', 1, $user_modification);
                $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nokp1'));
            }

        }
        else{
            $this->utils->log_journal('Ajout Pallier', 'Montant min:'.$mtmin.' Montant max:'.$mtmax.' Commission:'.$montant.' Id:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nokp'));
        }

    }

    /***********Validation Update User**********/
    public function validationUpdate($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/user','title' =>$data['lang']['list_users'] ,'alert'=>$data['lang']['message_success_update_user'],'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/user','title' =>$data['lang']['list_users'] ,'alert'=>$data['lang']['message_error_update_user'],'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Desactiver User**************/
    public function desactiverUser()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $update = $this->userModel->deleteUtilisateur($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Désactivation Utilisateur', 'Iduser desactivé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('admin','validationdesactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Utilisateur', 'Iduser desactivé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationdesactiver/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver User**********/
    public function validationdesactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_success_delete'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_error_delete'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Activer User**************/
    public function activerUser()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $update = $this->userModel->activerUtilisateur($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Activation Utilisateur', 'Iduser activé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('admin','validationactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Activation Utilisateur', 'Iduser activé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationactiver/'.base64_encode('nok'));
        }
    }

    /*************Activer Service**************/
    public function activerTarif()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['idtarif']));
        $update = $this->serviceModel->activerTarif($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Activation Service', 'Iduser activé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid));
        }
        else
        {
            $this->utils->log_journal('Activation Service', 'Iduser activé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nok'));
        }
    }


    /*************Desactiver Service**************/
    public function desactiverTarif()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['idtarif']));
        $update = $this->serviceModel->deleteTarif($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Désactivation Service', 'Iduser desactivé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid));
        }
        else
        {
            $this->utils->log_journal('Désactivation Service', 'Iduser desactivé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','detailsTarif/'.base64_encode($rowid).'/'.base64_encode('nok'));
        }
    }
    /***********Validation Activer User**********/
    public function validationactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_activer_user'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_error_activer_user'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    public function envoiNewPass($destinataire, $email, $login, $password) {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));


        $sujet = utf8_decode($data['lang']['resetPasswordUser0']); //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete ='';
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> ".$data['lang']['mess_virem_masse1'] ." ".$destinataire.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$data['lang']['resetPasswordUser1']."<br />";
        $message.= "".$data['lang']['resetPasswordUser2']."<br />";
        $message.=  $data['lang']['identifiant']." :".$login."<br />";
        $message.=  $data['lang']['motdepasse']." :".$password."<br />";
        $message.= "<a href='". BASE_URL ."' target='_blank'>".$data['lang']['resetPasswordUser3']."</a>";
        $message.= "<br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>".$data['lang']['resetPasswordUser4']."<br /><br />".$data['lang']['resetPasswordUser5']."</td>";
        $message.= "</tr>";
        $message.= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=utf8\r";
        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:Postecash MAURIPOST <no-reply@postecash.mr>\r";
        mail($vers_mail, $sujet, $message, $entete);


    }

    /*************reset password User**************/
    public function resetPasswordUser()
    {
        $password = $this->utils->generation_code(10);
        $user_modification = $this->userConnecter->rowid;
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $email = $this->utils->securite_xss($_POST['email']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $login = base64_decode($this->utils->securite_xss($_POST['login']));

        $update = $this->userModel->resetPasswordUtilisateur($rowid, $password, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Regénération Mot de Passe Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'succes', 1, $user_modification);
            $this->envoiNewPass($prenom.' '.$nom, $email, $login, $password);
            $this->rediriger('admin','validationresetPassword/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Regénération Mot de Passe Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationresetPassword/'.base64_encode('nok'));
        }
    }

    /***********Validation Reset Password User**********/
    public function validationresetPassword($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/user', 'title' => $data['lang']['list_users'], 'alert'=>$data['lang']['message_success_regenere'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/user', 'title' => $data['lang']['list_users'], 'alert'=>$data['lang']['message_error_regenere'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }


    /***************** Liste users *********************/
    public function processingUser()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailUser/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->userModel,"allUser",$param);
    }


    /***************** Liste tqrif *********************/
    public function processingTarif()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailsTarif/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->serviceModel,"allTarifs",$param);
    }

    /******* Action verifier identifiant ****/
    public function verifLogin()
    {
        $verif = $this->userModel->verifIdentifiant($this->utils->securite_xss($_POST['identifiant']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier email ****/
    public function verifEmail()
    {
        $verif = $this->userModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }


    /******* Action verifier email ****/
    public function verifEmailAgence()
    {
        $verif = $this->agenceModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier Code ****/
    public function verifCode()
    {
        $verif = $this->agenceModel->verifCode($this->utils->securite_xss($_POST['code']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /***************** Liste action *********************/
    public function listeaction()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(71,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();
        $params = array('view' => 'admin/action');
        $this->view($params,$data);
    }
    /***************** Liste module *********************/
    public function listemodule()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(249,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->moduleModel->allModule();
        $params = array('view' => 'admin/module');
        $this->view($params,$data);
    }

    /**
     * LISTE Module
     */
    public function processingModule(){
        $param = [
            "button"=>[
                [ROOT."admin/detailModule/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->moduleModel, "allModule__", $param);
    }

    /***************** processing action *********************/
    public function processingAction()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailAction/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->actionModel,"allAction",$param);
    }

    /*********detailUser********/
    public function detailAction($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();
        $data['action']= $this->actionModel->getActionByIdString(base64_decode($id[0]));

        $params = array('view' => 'admin/action-detail');
        $this->view($params,$data);
    }
 /*********detailModule********/
    public function detailModule($id)
    {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $data['module']= $this->moduleModel->getModuleByIdString(base64_decode($id[0]));

        $params = array('view' => 'admin/module-detail');
        $this->view($params,$data);
    }




    /*************update Module**************/
    public function updatemodule()
    {

        $label = $this->utils->securite_xss($_POST['module']);
        $module = $this->utils->securite_xss($_POST['module']);
        $idmodule =$this->utils->securite_xss($_POST['idmodule']);
        $user_modification = $this->userConnecter->rowid;

        $update = $this->moduleModel->updateModule($module, $idmodule, $user_modification);

        if($update==1)
        {
            $this->utils->log_journal('Modification Module', 'Action:'.$label.' Module:'.$idmodule, 'succes', 1, $user_modification);
            $this->rediriger('admin','validationUpdateModule/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Module', 'Action:'.$label.' Module:'.$idmodule, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationUpdateModule/'.base64_encode('nok'));
        }
    }

    /***********Validation Update module**********/
    public function validationUpdateModule($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->moduleModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_success_update_module'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_error_update_module'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Desactiver module**************/
    public function desactivermodule()
    {

        $user_modification = $this->userConnecter->rowid;
        $idmodule = $this->utils->securite_xss($_POST['idmodule']);
        $etat = $this->utils->securite_xss($_POST['etat']);

        $update = $this->moduleModel->desactiveModule($idmodule, $user_modification);

        if($update==1)
        {
            if($etat==1 )
            {
                $this->utils->log_journal('Activation Module', 'Id module:'.$idmodule,'succès', 1, $user_modification);
            }
            if($etat==0 )
            {
                $this->utils->log_journal('Désactivation Module', 'Id module:'.$idmodule, 'succès', 1, $user_modification);
            }
            $this->rediriger('admin','validationdesactiverModule/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Module', 'Id action:'.$idmodule, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationdesactiverModule/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver module **********/
    public function validationdesactiverModule($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->moduleModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_success_update_module'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_error_update_module'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }
/*************Desactiver module **************/
    public function activermodule()
    {

        $user_modification = $this->userConnecter->rowid;
        $idmodule = $this->utils->securite_xss($_POST['idmodule']);
        $etat = $this->utils->securite_xss($_POST['etat']);
        $update = $this->moduleModel->activateModule($idmodule, $user_modification);

        if($update==1)
        {
            if($etat==1 )
            {
                $this->utils->log_journal('Activation Module', 'Id module:'.$idmodule,'succès', 1, $user_modification);
            }
            if($etat==0 )
            {
                $this->utils->log_journal('Désactivation Module', 'Id module:'.$idmodule, 'succès', 1, $user_modification);
            }
            $this->rediriger('admin','validationactiverModule/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Activation Module', 'Id action:'.$idmodule, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationactiverModule/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver module**********/
    public function validationactiverModule($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->moduleModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_success_update_module'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_module'], 'alert'=>$data['lang']['message_error_update_module'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Insert module **************/
    public function insertmodule()
    {


        $module = $this->utils->securite_xss($_POST['module']);
        $user_creation = $this->userConnecter->rowid;
        $insert = $this->moduleModel->insertModule($module, $user_creation);

        if($insert==1)
        {
            $this->utils->log_journal('Ajout Module', 'Action:'.$module.' Module:'.$module, 'succes', 1, $user_creation);
            $this->rediriger('admin','validationInsertModule/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Ajout Action', 'Action:'.$module.' Module:'.$module, 'echec', 1, $user_creation);
            $this->rediriger('admin','validationInsertModule/'.base64_encode('nok'));
        }
    }

    /***********Validation Insert module**********/
    public function validationInsertModule($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->moduleModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_success_add_module'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/module', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_error_add_module'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }





    /*************Insert Action**************/
    public function insertAction()
    {
        $label = $this->utils->securite_xss($_POST['action']);
        $module = $this->utils->securite_xss($_POST['module']);
        $user_creation = $this->userConnecter->rowid;
        $insert = $this->actionModel->insertAction($label, $module, $user_creation);
        if($insert==1)
        {
            $this->utils->log_journal('Ajout Action', 'Action:'.$label.' Module:'.$module, 'succes', 1, $user_creation);
            $this->rediriger('admin','validationInsertAction/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Ajout Action', 'Action:'.$label.' Module:'.$module, 'echec', 1, $user_creation);
            $this->rediriger('admin','validationInsertAction/'.base64_encode('nok'));
        }
    }

    /***********Validation Insert Action**********/
    public function validationInsertAction($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_success_add_action'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_error_add_action'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************update Action**************/
    public function updateAction()
    {
        $label = $this->utils->securite_xss($_POST['action']);
        $module = $this->utils->securite_xss($_POST['module']);
        $idaction = base64_decode($this->utils->securite_xss($_POST['idaction']));
        $user_modification = $this->userConnecter->rowid;

        $update = $this->actionModel->updateAction($label, $module, $idaction, $user_modification);

        if($update==1)
        {
            $this->utils->log_journal('Modification Action', 'Action:'.$label.' Module:'.$module, 'succes', 1, $user_modification);
            $this->rediriger('admin','validationUpdateAction/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Action', 'Action:'.$label.' Module:'.$module, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationUpdateAction/'.base64_encode('nok'));
        }
    }

    /***********Validation Update Action**********/
    public function validationUpdateAction($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_success_update_action'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_error_update_action'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Desactiver action**************/
    public function desactiveAction()
    {
        $user_modification = $this->userConnecter->rowid;
        $idaction = base64_decode($this->utils->securite_xss($_POST['idaction']));
        $etat = $this->utils->securite_xss($_POST['etat']);
        $update = $this->actionModel->desactiveAction($etat, $idaction, $user_modification);

        if($update==1)
        {
            if($etat==1 )
            {
               $this->utils->log_journal('Activation Action', 'Id action:'.$idaction, 'succès', 1, $user_modification);
            }
            if($etat==0 )
            {
                $this->utils->log_journal('Désactivation Action', 'Id action:'.$idaction, 'succès', 1, $user_modification);
            }
            $this->rediriger('admin','validationdesactiverAction/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Action', 'Id action:'.$idaction, 'echec', 1, $user_modification);
            $this->rediriger('admin','validationdesactiverAction/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver action**********/
    public function validationdesactiverAction($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_success_update_action'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/action', 'title' =>$data['lang']['list_action'], 'alert'=>$data['lang']['message_error_update_action'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /***************** Formulaire suivi action *********************/
    public function searchSuiviAction()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();
        $params = array('view' => 'admin/suivi-action-search');
        $this->view($params,$data);
    }


    /***************** Liste action *********************/
    public function suiviaction()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(131,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/suivi-action');
        $this->view($params,$data);
    }

    /***************** processing action *********************/
    public function processingSuiviAction($id)
    {
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $module = $this->utils->securite_xss($id[2]);


        $param = [
            "button"=>[],
            "args"=>["date1" => $date1, "date2" => $date2, "module" => $module],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))

        ];
        $this->processing($this->actionModel,"suiviAtion",$param);
    }

    /***************** Action show virement  *********************/
    public function showAddVirement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(162,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/addvirement');
        $this->view($params,$data);
    }

    /*************Action ajouter virement**************/
    public function ajoutVirement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant2']);
        $user_creation = $this->userConnecter->rowid;
        $add = $this->virementModel->addVirement($montant, $user_creation, $data['lang']);
        if($add==1)
        {
            $this->utils->log_journal('Ajout virement', 'Montant:'.$montant.' effectue par:'.$user_creation, 'succes', 1, $user_creation);
            $this->rediriger('admin','validationAddVirement/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Ajout virement', 'Montant:'.$montant.' effectue par:'.$user_creation, 'echec', 1, $user_creation);
            $this->rediriger('admin','validationAddVirement/'.base64_encode('nok'));
        }
    }

    /***********Validation Add Virement**********/
    public function validationAddVirement($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'admin/addvirement', 'title' =>$data['lang']['historique_virement'], 'alert'=>$data['lang']['succes_virement'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'admin/addvirement', 'title' =>$data['lang']['historique_virement'], 'alert'=>$data['lang']['error_virement'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /***************** Formulaire suivi action *********************/
    public function searchVirement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(163,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/virement-search');
        $this->view($params,$data);
    }

    /***************** Historique virement *********************/
    public function historiqueVirement()
    {
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['virement']= $this->virementModel->histoVirement($date1, $date2);
        $params = array('view' => 'admin/virement-historique');
        $this->view($params,$data);
    }

    /***************** Valider virement *********************/
    public function validerVirement()
    {
        $idvirement = base64_decode($this->utils->securite_xss($_POST['rowid']));
        $user_creation = $this->userConnecter->rowid;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $valider = $this->virementModel->validerVirement($idvirement, $user_creation);

        if($valider == 1)
        {
            $this->utils->log_journal('Validation virement', 'Idvirement:'.$idvirement.' effectue par:'.$user_creation, 'succes', 1, $user_creation);
            $this->rediriger('admin','validationVirement/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Validation virement', 'Idvirement:'.$idvirement.' effectue par:'.$user_creation, 'succes', 1, $user_creation);
            $this->rediriger('admin','validationVirement/'.base64_encode('nok'));
        }
    }

    /***********Validation Virement**********/
    public function validationVirement($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'admin/virement-search', 'title' =>$data['lang']['historique_virement'], 'alert'=>$data['lang']['succes_valider_virement'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'admin/virement-search', 'title' =>$data['lang']['historique_virement'], 'alert'=>$data['lang']['error_valider_virement'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }




    /***************** Formulaire search Transfert agence *********************/
    public function searchTransfertAgence()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(116,$this->userConnecter->profil) );

        $data['agence']= $this->agenceModel->allAgence();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/transfert-agence-search');
        $this->view($params,$data);
    }

    /******************Detail Transfert Agence******************/
    public function transfertAgenceDetail()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $codeSource = $this->utils->securite_xss($_POST['expediteur']);;
        $codeDestinataire = $this->utils->securite_xss($_POST['destinataire']);;
        $data['agenceSource'] = $this->agenceModel->agenceDetailById($codeSource);
        $data['agenceDestinataire'] = $this->agenceModel->agenceDetailById($codeDestinataire);
        $params = array('view' => 'admin/transfert-carte-agence');
        $this->view($params, $data);
    }

    /***************** Transfert Agence vers Agence *********************/
    public function transfertAgence()
    {
        $agenceSource = base64_decode($this->utils->securite_xss($_POST['source']));
        $agenceDestination = base64_decode($this->utils->securite_xss($_POST['destination']));
        $montant = $this->utils->securite_xss($_POST['montantbis']);
        $fkuser = $this->userConnecter->rowid;
        $fkagence = $this->userConnecter->fk_agence;

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $transfert = $this->agenceModel->transfertAgenceVersAgence($agenceSource, $agenceDestination, $montant, $fkuser, $fkagence);
        if($transfert == 1)
        {
            $this->utils->log_journal('Transfert agence vers agence', 'AgenceSource:'.$agenceSource.' AgenceDestination:'.$agenceDestination.' Montant:'.$montant, 'succes', 1, $fkuser);
            $this->rediriger('admin','validationTransfert/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Transfert agence vers agence', 'AgenceSource:'.$agenceSource.' AgenceDestination:'.$agenceDestination.' Montant:'.$montant, 'echec', 1, $fkuser);
            $this->rediriger('admin','validationTransfert/'.base64_encode('nok'));
        }
    }

    /***********Validation Transfert Agence**********/
    public function validationTransfert($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'admin/transfert-carte-agence-fin', 'title' =>$data['lang']['transfert_carte_agence_carte_agence'], 'alert'=>$data['lang']['transfert_carte_carte_reussie'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'admin/transfert-carte-agence-fin', 'title' =>$data['lang']['transfert_carte_agence_carte_agence'], 'alert'=>$data['lang']['error_valider_virement'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }


    /***************** Formulaire histo transaction *********************/
    public function searchTransaction()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(112,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'admin/transaction-search');
        $this->view($params,$data);
    }

    /***************** Historique Transaction *********************/
    public function historiqueTransaction()
    {
        $date1 = $this->utils->securite_xss($_POST['datedeb']);
        $date2 = $this->utils->securite_xss($_POST['datefin']);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['historique']= $this->agenceModel->searchTransaction($date1, $date2);
        
        $params = array('view' => 'admin/transaction-historique');
        $this->view($params,$data);
    }






    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                     CRUD TYPE PROFIL                                                   //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////
    /**
     * INSERTION TYPE PROFIL
     */
    public function inserTypeProfil(){

    }

    /**
     * LISTE TYPE PROFIL
     */
    public function processingTypeProfil(){

    }

    /**
     * ACTIVATION TYPE PROFIL
     */
    public function activerTypeProfil(){

    }

    /**
     * DESACTIVATION TYPE PROFIL
     */
    public function desactiverTypeProfil(){

    }

    /**
     * MODIFICATION TYPE PROFIL
     */
    public function modifierTypeProfil(){

    }



    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                     CRUD      PROFIL                                                   //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////
    /**
     * @param $id
     * PAGE CONTENANT LA LISTE DES PROFILS
     */
    public function profil($id)
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(69,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['type_profil']= $this->typeprofilModel->allTypeProfil();

        $taille = count($id);
        if($taille > 0){
            if(base64_decode($id[0])==1){
                $type_alert='success';
                $alert=$data['lang']['message_success_add_profil'];

            }
            if(base64_decode($id[0])==-1){
                $type_alert='error';
                $alert= $data['lang']['message_error_add_profil'];
            }


            if(base64_decode($id[0])==2){
                $type_alert='success';
                $alert=$data['lang']['message_success_delete'];
            }
            if(base64_decode($id[0])==-2){
                $type_alert='error';
                $alert= $data['lang']['message_error_delete'];
            }

            if(base64_decode($id[0])==4){
                $type_alert='success';
                $alert=$data['lang']['message_activer_profil'];
            }
            if(base64_decode($id[0])==-4){
                $type_alert='error';
                $alert= $data['lang']['message_error_activer_profil'];
            }

            $paramsview = array('view' => 'admin/profil','alert'=>$alert, 'type-alert'=>$type_alert );
        }
        else{
            $paramsview = array('view' => 'admin/profil');
        }

        $this->view($paramsview, $data);
    }

    /**
     * @param $id
     * PAGE CONTENANT LE DETAIL D'UN PROFIL
     */
    public function detailProfil($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['type_profil']= $this->typeprofilModel->allTypeProfil();
        $data['module']= $this->actionModel->allmodule();
        $data['actions_autorisees']= $this->actionModel->allActionsAutoriseByProfil(base64_decode($id[0]));

        $data['profil']= $this->profilModel->getProfilByIdInteger(base64_decode($id[0]));
        if(base64_decode($id[1])==1){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_profil'];
        }
        if(base64_decode($id[1])==-1){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_profil'];
        }
        if(base64_decode($id[1])==3){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_autorise_action'];
        }
        if(base64_decode($id[1])==-3){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_autorise_action'];
        }

        $params = array('view' => 'admin/profil-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }
    /**
     * INSERTION PROFIL
     */
    public function inserProfil(){

        $profil = $this->utils->securite_xss($_POST['profil']);
        $typeprofil = $this->utils->securite_xss($_POST['typeprofil']);
        $user_creation = $this->userConnecter->rowid;
        //$user_creation = 1;

        $insert = $this->profilModel->insertProfil($profil, $typeprofil,$user_creation);
        if($insert==1){
            $this->utils->log_journal('Ajout Profil', 'Profil:'.$profil.' Type Profil:'.$typeprofil, 'succes', 1, $user_creation);
        }
        if($insert==-1){
            $this->utils->log_journal('Ajout Profil', 'Profil:'.$profil.' Type Profil:'.$typeprofil, 'echec', 1, $user_creation);
        }

        $this->rediriger('admin','profil/'.base64_encode($insert));


    }

    /**
     * LISTE PROFIL
     */
    public function processingProfil(){
        $param = [
            "button"=>[
                [ROOT."admin/detailProfil/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->profilModel, "allProfil__", $param);
    }


    /**
     * INSERTION NOTIFICATION
     */
    public function inserNotif(){

        $nom = $this->utils->securite_xss($_POST['nom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $type = $this->utils->securite_xss($_POST['type']);
        $user_creation = $this->userConnecter->rowid;

        $insert = $this->profilModel->insertNotif($nom, $email,$type);
        if($insert==1){
            $this->utils->log_journal('Ajout Notification', 'Notification:'.$nom.' Type :'.$type, 'succes', 1, $user_creation);
        }
        if($insert==-1){
            $this->utils->log_journal('Ajout Notification', 'Notification:'.$nom.' Type Profil:'.$type, 'echec', 1, $user_creation);
        }

        $this->rediriger('admin','notification');


    }

    public function notification(){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $paramsview = array('view' => 'admin/notification');
        $this->view($paramsview,$data);
    }

    /**
     * LISTE Notif
     */
    public function processingNotif(){
        $param = [
            "button"=>[
                [ROOT."admin/detailNotif/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->profilModel, "allNotif__", $param);
    }


    /**
     * @param $id
     * PAGE CONTENANT LE DETAIL D'UN PROFIL
     */
    public function detailNotif($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['notif']= $this->profilModel->getNotifByIdInteger(base64_decode($id[0]));

        if(base64_decode($id[1])==1){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_profil'];
        }
        if(base64_decode($id[1])==-1){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_profil'];
        }
        if(base64_decode($id[1])==3){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_autorise_action'];
        }
        if(base64_decode($id[1])==-3){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_autorise_action'];
        }

        $params = array('view' => 'admin/notif-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }


    /**
     * MODIFICATION PROFIL
     */
    public function updateNotif(){
        $id = $this->utils->securite_xss($_POST['idrow']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $type = $this->utils->securite_xss($_POST['type']);
        $user_modification = $this->userConnecter->rowid;

        $insert = $this->profilModel->updateNotif($nom, $email, $type, $id);
        if($insert==1){
            $this->utils->log_journal('Modification Notification', 'Notification:'.$nom.' Type Notification:'.$type, 'succes', 1, $user_modification);
        }
        if($insert==-1){
            $this->utils->log_journal('Modification Notification', 'Notification:'.$nom.' Type Notification:'.$type, 'echec', 1, $user_modification);
        }

        $this->rediriger('admin','detailNotif/'.base64_encode($id));

    }


    /**
     * ACTIVATION PROFIL
     */
    public function activerProfil(){
        $id = $this->utils->securite_xss($_POST['idprofil']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->profilModel->enableProfil($id, $user_modification);
        if($insert==4)
        {
            $this->utils->log_journal('Activation Profil', 'Profil:'.$id, 'succes', 1, $user_modification);

        }
        if($insert==-4)
        {
            $this->utils->log_journal('Activation Profil', 'Profil:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','profil/'.base64_encode($insert));
    }

    /**
     * ACTIVATION NOTIFICATION
     */
    public function activerNotif(){
        $id = $this->utils->securite_xss($_POST['id']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->profilModel->enableNotif($id);
        if($insert==4)
        {
            $this->utils->log_journal('Activation Notification', 'Notification:'.$id, 'succes', 1, $user_modification);

        }
        if($insert==-4)
        {
            $this->utils->log_journal('Activation Notification', 'Notification:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','detailNotif/'.base64_encode($id));
    }

    /**
     * DESACTIVATION NOTIFICATION
     */
    public function desactiverNotif(){
        $id = $this->utils->securite_xss($_POST['id']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->profilModel->desableNotif($id);
        if($insert==4)
        {
            $this->utils->log_journal('Activation Notification', 'Notification:'.$id, 'succes', 1, $user_modification);
        }
        if($insert==-4)
        {
            $this->utils->log_journal('Activation Notification', 'Notification:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','detailNotif/'.base64_encode($id));
    }

    /**
     * DESACTIVATION PROFIL
     */
    public function desactiverProfil(){
        $id = $this->utils->securite_xss($_POST['idprofil']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->profilModel->disableProfil($id, $user_modification);
        if($insert==2){
            $this->utils->log_journal('Desactivation Profil', 'Profil:'.$id, 'succes', 1, $user_modification);
        }
        if($insert==-2){
            $this->utils->log_journal('Desactivation Profil', 'Profil:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','profil/'.base64_encode($insert));

    }

    /**
     * MODIFICATION PROFIL
     */
    public function updateProfil(){
        $id = $this->utils->securite_xss($_POST['idprofil']);
        $profil = $this->utils->securite_xss($_POST['profil']);
        $typeprofil = $this->utils->securite_xss($_POST['typeprofil']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->profilModel->updateProfil($profil, $typeprofil, $id, $user_modification);
        if($insert==1){
            $this->utils->log_journal('Modification Profil', 'Profil:'.$profil.' Type Profil:'.$typeprofil, 'succes', 1, $user_modification);
        }
        if($insert==-1){
            $this->utils->log_journal('Modification Profil', 'Profil:'.$profil.' Type Profil:'.$typeprofil, 'echec', 1, $user_modification);
        }

        $this->rediriger('admin','detailProfil/'.base64_encode($id).'/'.base64_encode($insert));

    }

    /**
     *
     */
    public function ajoutAction(){
        $user_creation = $this->userConnecter->rowid;
        $id = $this->utils->securite_xss($_POST['idprofil']);

        $lesactionscoches = $_POST['lesactions'];
        $nbre = sizeof($_POST['lesactions']);

        $result = $this->actionModel->deleteAutoriseAction($id);

        if($result){
            $i = 0;
        }
        foreach($lesactionscoches as $uneaction){
            $result1 = $this->actionModel->autoriseAction($uneaction, $id,$user_creation);
            if($result1){
                $i++;
            }
        }
        if($nbre == $i){
            $this->utils->log_journal('Ajout droit au profil', 'Profil:'.$id.' Droit:'.$lesactionscoches, 'succes', 1, $user_creation);
            $send=3;
        }
        else{
            $this->utils->log_journal('Ajout droit au profil', 'Profil:'.$id.' Type Profil:'.$lesactionscoches, 'echec', 1, $user_creation);
            $send=-3;
        }
        $this->rediriger('admin','detailProfil/'.base64_encode($id).'/'.base64_encode($send));
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                CRUD MESSAGE D'ENTETE                                                   //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function message($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(89,$this->userConnecter->profil) );

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->messageModel->allModule();

        $taille = count($id);
        if($taille > 0) {
            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_entete'];

            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_entete'];
            }


            if (base64_decode($id[0]) == 2) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_delete'];
            }
            if (base64_decode($id[0]) == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_delete'];
            }

            if (base64_decode($id[0]) == 4) {
                $type_alert = 'success';
                $alert = $data['lang']['message_activer_message'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer_message'];
            }

            $paramsview = array('view' => 'admin/message', 'alert' => $alert, 'type-alert' => $type_alert);
        }
        else{
            $paramsview = array('view' => 'admin/message');
        }
        $this->view($paramsview, $data);
    }

    /**
     * CREATION MESSAGE
     */
    public function inserMessage(){
        $expediteur = $this->utils->securite_xss($_POST['expediteur']);
        $txt_messenger = $this->utils->securite_xss($_POST['txt']);
        $module = $this->utils->securite_xss($_POST['module']);
        $user_creation = $this->userConnecter->rowid;

        $insert = $this->messageModel->insertMessage($expediteur,$module,$txt_messenger,$user_creation);

        if($insert==1){
            $this->utils->log_journal('Ajout Message entete', 'Expediteur:'.$expediteur.' Module:'.$module, 'succes', $module, $user_creation);
        }
        if($insert==-1){
            $this->utils->log_journal('Ajout Message entete', 'Expediteur:'.$expediteur.' Module:'.$module, 'echec', $module, $user_creation);
        }

        $this->rediriger('admin','message/'.base64_encode($insert));
    }

    /**
     * LISTE MESSAGE
     */
    public function processingMessage(){
        $param = [
            "button"=>[
                [ROOT."admin/detailMessage/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->messageModel, "allMessage", $param);

    }

    /**
     * @param $id
     * PAGE CONTENANT LE DETAIL D'UN PROFIL
     */
    public function detailMessage($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['module']= $this->actionModel->allmodule();
        $data['message']= $this->messageModel->getMessageByIdInteger(base64_decode($id[0]));

        if(base64_decode($id[1])==1){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_sms'];
        }
        if(base64_decode($id[1])==-1){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_sms'];
        }
        if(base64_decode($id[1])==3){
            $type_alert='success';
            $alert=$data['lang']['message_success_update_autorise_action'];
        }
        if(base64_decode($id[1])==-3){
            $type_alert='error';
            $alert= $data['lang']['message_error_update_autorise_action'];
        }

        $params = array('view' => 'admin/message-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    /**
     * ACTIVATION PROFIL
     */
    public function activerMessage(){
        $id = $this->utils->securite_xss($_POST['idmessage']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->messageModel->enableMessage($id,$user_modification);
        if($insert==4)
        {
            $this->utils->log_journal('Activation message entete', 'Message entete:'.$id, 'succes', 1, $user_modification);
        }
        if($insert==-4)
        {
            $this->utils->log_journal('Activation message entete', 'Message entete:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','message/'.base64_encode($insert));
    }

    /**
     * DESACTIVATION PROFIL
     */
    public function desactiverMessage(){
        $id = $this->utils->securite_xss($_POST['idmessage']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->messageModel->disableMessage($id, $user_modification);
        if($insert==2){
            $this->utils->log_journal('Desactivation message entete', 'Message entete:'.$id, 'succes', 1, $user_modification);
        }
        if($insert==-2){
            $this->utils->log_journal('Desactivation message entete', 'Message entete:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('admin','message/'.base64_encode($insert));

    }

    /**
     * MODIFICATION PROFIL
     */
    public function updateMessage(){
        $id = $this->utils->securite_xss($_POST['idmessage']);
        $module = $this->utils->securite_xss($_POST['module']);
        $expediteur = $this->utils->securite_xss($_POST['expediteur']);
        $txt_messenger = $this->utils->securite_xss($_POST['txt']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->messageModel->updateMessage($expediteur,$module, $txt_messenger, $id, $user_modification);
        if($insert==1){
            $this->utils->log_journal('Modification message entete', 'Message entete:'.$txt_messenger, 'succes', 1, $user_modification);
        }
        if($insert==-1){
            $this->utils->log_journal('Modification message entete', 'Message entete:'.$txt_messenger, 'echec', 1, $user_modification);
        }

        $this->rediriger('admin','detailMessage/'.base64_encode($id).'/'.base64_encode($insert));

    }


    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                               SUIVI VIREMENT                                           //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function suivivirement()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(161,$this->userConnecter->profil) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $suivivirement= $this->virementModel->soldeComptePoste();
        $data['suivi_virement']= $this->utils->nombre_format($suivivirement);
        $data['datedebut'] = $this->utils->securite_xss($_POST['datedebut']);
        $data['datefin'] = $this->utils->securite_xss($_POST['datefin']);
        $paramsview = array('view' => 'admin/suivi_virement');
        $this->view($paramsview, $data);
    }


    public function processingMvmtCompte($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(161,$this->userConnecter->profil) );
        $requestData = $_REQUEST;
        $columns = array(
            0 =>'date_transaction',
            1 => 'num_transac',
            2 => 'solde_avant',
            3 => 'solde_apres',
            4 => 'montant',

        );

        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        //var_dump(empty($date1));exit;
        if (empty($date1)==false && empty($date2)==false){
            $sql = "SELECT t.date_transaction, t.num_transac, t.solde_avant, t.solde_apres, t.montant, t.operation FROM  `releve_des_comptes` as t WHERE DATE(t.date_transaction) >='".$date1. "' AND DATE(t.date_transaction) <='".$date2."'";
            if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" AND ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_apres LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_avant LIKE '%".$requestData['search']['value']."%' )";
            }
        }else{
            $sql = "SELECT t.date_transaction, t.num_transac, t.solde_avant, t.solde_apres, t.montant, t.operation FROM  `releve_des_comptes` as t ";
            if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" WHERE ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_apres LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_avant LIKE '%".$requestData['search']['value']."%' )";
            }
        }


        //var_dump($sql);exit;
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        if ($date1!='' && $date2!=''){
            $sql = "SELECT t.date_transaction, t.num_transac, t.solde_avant, t.solde_apres, t.montant, t.operation FROM  `releve_des_comptes` as t WHERE DATE(t.date_transaction) >='".$date1. "' AND DATE(t.date_transaction) <='".$date2."'";
            if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql.=" AND ( t.num_transac LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_apres LIKE '%".$requestData['search']['value']."%' ";
                $sql.=" OR t.solde_avant LIKE '%".$requestData['search']['value']."%' )";
            }
        }else {
            $sql = "SELECT t.date_transaction, t.num_transac, t.solde_avant, t.solde_apres, t.montant, t.operation FROM  `releve_des_comptes` as t ";
            if ($requestData['search']['value'] != "") {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( t.num_transac LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR t.montant LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR t.solde_apres LIKE '%" . $requestData['search']['value'] . "%' ";
                $sql .= " OR t.solde_avant LIKE '%" . $requestData['search']['value'] . "%' )";
            }
        }

        $tabCol = ['t.date_transaction'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " DESC ";
        $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
        // var_dump($sql);exit;
        $user = $this->getConnexion()->prepare($sql);
        $user->bindParam("date1", $date1);
        $user->bindParam("date2", $date2);
        $user->execute();
        $rows = $user->fetchAll();


        $data = array();
        foreach( $rows as $row)
        {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] =$row["num_transac"];
            $nestedData[] = $this->utils->number_format($row["solde_avant"]);
            $nestedData[] = $this->utils->number_format($row["solde_apres"]);
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $row["operation"];
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



    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                     CRUD      AGENCE                                                   //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////
    public function agence_list($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(68,$this->userConnecter->profil) );
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_agence'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_agence'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_agence'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_agence'];
            }
        }
        $data['allDep'] = $this->agenceModel->getAllDepartement();
        $data['allTypAg'] = $this->agenceModel->getAllTypeAgence();
        $params = array('view' => 'admin/agence','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

 /*   public function distributeur_list($arg = null)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_distributeur'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_distributeur'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_distributeur'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_distributeur'];
            }
        }
        $data['allDep'] = $this->agenceModel->getAllDepartement();
        $params = array('view' => 'admin/distributeur','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }*/

    public function distributeur_list($arg = null)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_distributeur'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_distributeur'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_distributeur'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_distributeur'];
            }
        }
        $data['allDep'] = $this->agenceModel->getAllDepartement();
        $params = array('view' => 'admin/distributeur','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }



    public function crediter_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(113,$this->userConnecter->profil) );
        $_POST['num_transac'] = $this->agenceModel->generateNumeroTransaction();
        $data['agence']= $this->agenceModel->allAgenceByType();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $data['etape'] = base64_encode(1);
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_agence'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_agence'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_agence'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_agence'];
            }
        }

        if(isset($_POST['etape']) && base64_decode($_POST['etape']) == 2){
            $data['etape'] = base64_encode(2);
            $data['agence'] = $this->agenceModel->agenceByCode(['rowid'=>$_POST['agence']])[0];
            if(is_null($data['agence'])){
                $data['etape'] = base64_encode(1);
                $alert = $data['lang']['code_invalide'];
                $type_alert = 'danger';
            }
        }else if(isset($_POST['etape']) && base64_decode($_POST['etape']) == 3) {
            $etat = 'echec';
            if($this->getKey(true) === 1)
            {
                $soldeOp_avant = $this->agenceModel->getSoldeCompte();
                $solde_avant = $this->agenceModel->consulterSoldeAgence($_POST['agence']);
                $this->agenceModel->changeEtatKeyValidation(["code"=>$_POST['code']]);
                unset($_POST['etape']);unset($_POST['code']);
                $_POST['idUser']= $this->userConnecter->rowid;
                foreach ($_POST as $key => $item) $_POST[$key] = ($key == 'rowid') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
                if($this->agenceModel->crediterAgenceByTransactionnelle($_POST))
                {
                    $etat = 'succes';
                    $soldeOp_apres = $this->agenceModel->getSoldeCompte();
                    $solde_apres = $this->agenceModel->consulterSoldeAgence($_POST['agence']);
                    $this->utils->addMouvementCompteAgence($this->utils->securite_xss($_POST['num_transac']), $solde_avant, $solde_apres, $_POST['solde'], $_POST['agence'], 'CREDIT', 'RechargeBureau: Recharge avec succes');
                    $this->utils->addMouvementCompteOperation($this->utils->securite_xss($_POST['num_transac']), $soldeOp_avant, $soldeOp_apres, $_POST['solde'], 1, 'DEBIT', 'RechargeAgennce: Recharge avec succes');
                }else
                    $etat = 'echec' ;
                $alert = $data['lang']['agence_credit_success'];
                $type_alert = 'success';
            }
            else{
                $alert = $data['lang']['code_incorrect'];
                $type_alert = 'danger';
            }
            $this->utils->log_journal('Créditer agence',' Agence:'.$_POST['rowid'].' Montant:'.$_POST['solde'], $etat, 1, $this->userConnecter->rowid);
        }
        $params = array('view' => 'admin/crediter-agence','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function crediter_plus_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(117,$this->userConnecter->profil) );
        $_POST['num_transac'] = $this->agenceModel->generateNumeroTransaction();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        $data['etape'] = base64_encode(1);
        $taille = count($arg);
        if($taille > 0){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='success';
                $alert=$data['lang']['message_success_update_agence'];
            }elseif($arg == -2){
                $type_alert='error';
                $alert=$data['lang']['message_error_update_agence'];
            }elseif($arg == -3) {
                $type_alert='success';
                $alert=$data['lang']['message_success_add_agence'];
            }elseif($arg == -4){
                $type_alert='error';
                $alert=$data['lang']['message_error_add_agence'];
            }
        }

        if(isset($_POST['etape']) && base64_decode($_POST['etape']) == 2){

            $data['etape'] = base64_encode(1);
            $alert = 'Fichier choisi invalide'; $type_alert = 'danger';
            if(isset($_FILES['fichier']) && $_FILES['fichier']['error'] != '4' && $_FILES['fichier']['type'] === 'text/csv'){
                 $fichier = $_FILES['fichier'];
                 $nomfichier = $this->utils->getDateNow('WITH_TIME').'-'.$this->userConnecter->rowid;
                if($fichier['size'] < 1024000){
                    $fichier = $this->utils->setUploadFiles($fichier,'app/documents/rechargement-agence/',$nomfichier);
                }
                else {
                    $fichier = false;
                    $alert = $data['lang']['mess_virem_masse5'];
                }
                if($fichier != false){

                    $data['etape'] = base64_encode(2);
                    $alert = $type_alert = '';
                    $nomfichier = 'app/documents/rechargement-agence/'.$nomfichier;
                    $nameFile =$nomfichier. "." . pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
                    //echo $nameFile; die;
                    $data['agences']['traite'] = [];
                    $data['agences']['non-traite'] = [];
                    $totalSolde = 0;
                    if(($handle = fopen($nameFile, "r")) !== FALSE) {
                        while (($dataFile = fgetcsv($handle, 1000, ";")) !== FALSE) {
                            if(intval($dataFile[0]) != 0 && intval($dataFile[1]) != 0){
                                $info = $this->agenceModel->agenceDetailByCode($dataFile[0]);
                                if(isset($info->agence)){
                                    $data['agences']['traite'][] = ['rowid'=>$info->rowid,'code'=>$dataFile[0],'solde'=>$dataFile[1],'agence'=>$info->agence,'tel'=>$info->tel];
                                    $totalSolde += intval($dataFile[1]);
                                }else
                                    $data['agences']['non-traite'][] = ['rowid'=>null,'code'=>$dataFile[0],'solde'=>$dataFile[1],'agence'=>'Inconnu','tel'=>'Inconnu'];
                            }
                        }
                        fclose($handle);
                        $data['solde'] = $this->agenceModel->getSoldeCompte();
                        if($totalSolde > intval($data['solde'])) {
                            $data['etape'] = base64_encode(1);
                            $alert = $data['lang']['solde_compte_insuffisant'];
                        }else $this->getSession()->setAttributArray('agences',$data['agences']);
                    }
                    else{
                        $alert = $data['lang']['mess_virem_masse7'];
                    }
                }
                else{
                    $alert = $data['lang']['mess_virem_masse7'];
                }
            }
        }else if(isset($_POST['etape']) && base64_decode($_POST['etape']) == 3) {
            if($this->getSession()->existeAttribut('agences')){
                $dataFile = $this->getSession()->getAttribut('agences');
                $this->getSession()->destroyAttributSession('agences');
                $data['agenceNonTraite'] = $dataFile['non-traite'];
                $data['nbrLigneTraite'] = $data['montant'] = 0;
                $data['agenceTraite'] = [];
                $dataFile = $dataFile['traite'];
                for($i = 0 ; $i < count($dataFile) ; $i++)
                {
                    $soldeOp_avant = $this->agenceModel->getSoldeCompte();
                    $solde_avant = $this->agenceModel->consulterSoldeAgence($dataFile[$i]['rowid']);
                    if($this->agenceModel->crediterAgenceByTransactionnelle(['solde'=>$dataFile[$i]['solde'],'idUser'=>$this->userConnecter->rowid,'rowid'=>$dataFile[$i]['rowid']]))
                    {
                        $soldeOp_apres = $this->agenceModel->getSoldeCompte();
                        $solde_apres = $this->agenceModel->consulterSoldeAgence($dataFile[$i]['rowid']);
                        $this->utils->addMouvementCompteAgence($this->utils->securite_xss($_POST['num_transac']), $solde_avant, $solde_apres, intval($dataFile[$i]['solde']), $dataFile[$i]['rowid'], 'CREDIT', 'RechargeBureau: Recharge avec succes');
                        $this->utils->addMouvementCompteOperation($this->utils->securite_xss($_POST['num_transac']), $soldeOp_avant, $soldeOp_apres, intval($dataFile[$i]['solde']), 1, 'DEBIT', 'RechargeAgennce: Recharge avec succes');
                        $data['montant'] += intval($dataFile[$i]['solde']);
                    }
                    else
                    {
                        array_push($data['agenceNonTraite'],$dataFile[$i]);
                        unset($dataFile[$i]);
                    }
                }
                $data['agenceTraite'] = $dataFile;
                $this->utils->log_journal('Créditer plusieurs agences',' Total ligne:'.$dataFile['totLigne'].' ligne traitée : '.$data['nbrLigneTraite'].' ligne non traitée : '.(intval($dataFile['totLigne'])-intval($data['nbrLigneTraite'])).' montant : '.$data['montant'], 'succes', 1, $this->userConnecter->rowid);
            }
            $data['etape'] = base64_encode(1);
        }
        $params = array('view' => 'admin/crediter-plus-agence','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params, $data);
    }

    public function detailAgence($id)
    {
       $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->agenceModel->getAgenceByIdInteger(base64_decode($id[0]));
        $data['allService'] = $this->agenceModel->allServiceDistributeur();
        $data['allServiceByDist'] = $this->agenceModel->allServiceDistributeurId(base64_decode($id[0]));
        $data['allDep'] = $this->agenceModel->getAllDepartement();
        $data['allTypAg'] = $this->agenceModel->getAllTypeAgence();




        if(base64_decode($id[1])=== 'ok'){
            $type_alert='success';
            $alert=$data['lang']['bp_gest9'];
            $params = array('view' =>'admin/agence-detail', 'alert'=>$alert,'type-alert'=>$type_alert);
        }
        elseif(base64_decode($id[1])=== 'nok'){
            $type_alert='success';
            $alert=$data['lang']['bp_gest10'];
            $params = array('view' =>'admin/agence-detail', 'alert'=>$alert,'type-alert'=>$type_alert);
        }
        else{
            $params = array('view' => 'admin/agence-detail');
        }
        $this->view($params, $data);
    }

    public function setDataProv($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $data = $this->agenceModel->getAllProvince($id[0]);
        $result = '<select class="select3" required style="width: 100%;" id="fk_quartier" name="fk_quartier"><option selected="selected" value="">'.$data['agence']['select_region'].'</option>';
        foreach ($data as $item) {
            $select = ($item['idprovince'] == $id[1]) ? 'selected' : '';
            $result .= '<option '.$select.' value="'.$item['idprovince'].'">'.$item['province'].'</option>';
        }
        $result .= '</select>';
        echo $result;
    }

    public function setDataAgence($id)
    {
        $data = $this->agenceModel->getAllAgenceByType($id[0]);
        $result = '<select class="select3" required style="width: 100%;" id="agence" name="agence"><option selected="selected" value="">'.$data['agence']['select_agence'].'</option>';
        foreach ($data as $item) {

            $result .= '<option value="'.$item['rowid'].'">'.$item['label'].'</option>';
        }
        $result .= '</select>';
        echo $result;
    }

    public function setKey()
    {
        $datas['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['rowid'] = $this->utils->securite_xss(base64_decode($_POST['rowid']));
        $data['code'] = $this->agenceModel->setKeyValidation($data);
        if ($data['code'] != false) {

            $this->utils->envoiCodeValidationCreditercarte($this->userConnecter->email,$this->userConnecter->prenom.' '.$this->userConnecter->nom,$data['code'],$datas['lang']);

            echo 1;
        }else echo 0;
    }

    public function getKey($arg = false)
    {
        $data['code'] = $this->utils->securite_xss($_POST['code']);
        $data['rowid'] = $this->utils->securite_xss(base64_decode($_POST['rowid']));
        $data = $this->agenceModel->getKeyValidation($data);
        if($arg) return $data;
        else echo $data;
    }

    public function isValide()
    {
        foreach ($_POST as $key => $item) {
            $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $val = explode('-',$_POST[$key]);
            unset($_POST[$key]);
            $_POST[$val[1]] = $val[0];
        }
        ($this->agenceModel->isValideModel($_POST))? print json_encode(1) : print json_encode(0);
    }



    public function isValideUser()
    {
        foreach ($_POST as $key => $item) {
            $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $val = explode('-',$_POST[$key]);
            unset($_POST[$key]);
            $_POST[$val[1]] = $val[0];
        }
        ($this->userModel->isValideModel($_POST))? print json_encode(1) : print json_encode(0);
    }
    /*************Insert User**************/
   /* public function addAgence()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $_POST['user_creation'] = $this->userConnecter->rowid;
        $_POST['tel'] = str_replace(' ', '', $this->utils->securite_xss($_POST['tel']) );
        $_POST['etat'] = 1;

        if($this->agenceModel->insertAgenceModel($_POST)) $this->rediriger("admin","agence_list/".base64_encode(-3));
        else $this->rediriger("admin","agence_list/".base64_encode(-4));
    }*/


    /*************Insert User**************/
    public function addAgence()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $_POST['user_creation'] = $this->userConnecter->rowid;
        $_POST['tel'] = str_replace(' ', '', $this->utils->securite_xss($_POST['tel']) );
        $_POST['etat'] = 1;

        $result=$this->agenceModel->insertAgenceModel($this->utils->securite_xss_array($_POST));

        if($result==1) {

            $idtype_agence=$this->utils->securite_xss($_POST['idtype_agence']);
            if($idtype_agence==3){
                $this->utils->envoiCodeDistributeur($this->utils->securite_xss($_POST['label']),$this->utils->securite_xss($_POST['email']),$this->utils->securite_xss($_POST['code']),$this->utils->securite_xss($_POST['responsable']));
                $this->rediriger("admin","distributeur_list/".base64_encode(-3));
            }
            else $this->rediriger("admin","agence_list/".base64_encode(-3));
        }

        else $this->rediriger("admin","agence_list/".base64_encode(-4));
    }

    /*************Insert User**************/
    public function updateAgence()
    {
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'rowid') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $_POST['user_modification'] = $this->userConnecter->rowid;


        $email = $this->utils->securite_xss($_POST['email']);
        $emailOLD = $this->utils->securite_xss($_POST['emailOLD']);
        $responsable = $this->utils->securite_xss($_POST['responsable']);
        unset($_POST['emailOLD']);
        $res= $this->agenceModel->updateAgenceModel($this->utils->securite_xss_array($_POST));
        if($res)
        {
            if($email != $emailOLD){
                $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
                $this->utils->envoiNotifUpdateEmail($responsable, $email, $emailOLD, $data['lang']);
            }
            $this->rediriger("admin","agence_list/".base64_encode(-1));
        }
        else $this->rediriger("admin","agence_list/".base64_encode(-2));

    }

    /*************Insert User**************/
    public function addDistributeur()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $_POST['user_creation'] = $this->userConnecter->rowid;
        $_POST['tel'] = str_replace(' ', '', $this->utils->securite_xss($_POST['tel']) );
        $_POST['etat'] = 1;
        $_POST['idtype_agence'] = 3;
        //var_dump($_POST); die;
        if($this->agenceModel->insertAgenceModel($_POST)) $this->rediriger("admin","distributeur_list/".base64_encode(-3));
        else $this->rediriger("admin","distributeur_list/".base64_encode(-4));
    }

    /*************Insert User**************/
    public function updateDistributeur()
    {
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'rowid') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $_POST['user_modification'] = $this->userConnecter->rowid;

        $res= $this->agenceModel->updateAgenceModel($_POST);
        if($res) $this->rediriger("admin","distributeur_list/".base64_encode(-1));
        else $this->rediriger("admin","distributeur_list/".base64_encode(-2));

    }

    /***************** Liste users *********************/
   /* public function processingAgence()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailAgence/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->agenceModel, "getAllAgence", $param);
    }*/


    /***************** Liste users *********************/
    public function processingAgence()
    {
        $idtype_agence=$this->utils->securite_xss($_POST['idtypeagence']);

        $param = [
            "button"=>[
                [ROOT."admin/detailAgence/", "fa fa-search"]
            ],
            "args"=>[$idtype_agence],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->agenceModel, "getAllAgence", $param);
    }

    /**************SOLDE AGENCE************************/
    public function solde_agence(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(118,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['total'] = $this->agenceModel->soldeGlobalAgence();
        $params = array('view' => 'admin/solde_carte_agence');
        $this->view($params, $data);
    }

    /***************** LISTE AGENCE AVEC SOLDE *********************/
    public function processingAgence__()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailSoldeAgence/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->agenceModel, "getAllAgence__", $param);
    }


    /***************** Liste users *********************/
    public function processingDistributeur()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailAgence/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->agenceModel, "getAllDistributeur", $param);
    }


    /***************** LISTE AGENCE AVEC SOLDE *********************/
    public function processingDistributeur__()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailSoldeAgence/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->agenceModel, "getAllDistributeur__", $param);
    }


    public function detailSoldeAgence($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence'] = $this->agenceModel->getAgenceByIdInteger__(base64_decode($id[0]));
        $params = array('view' => 'admin/solde-detail');
        $this->view($params, $data);
    }

    public function exportPdfSoldeAgence(){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['solde_agences'] = $this->agenceModel->getAllSoldeAgence();
        $params = array('view' => 'admin/export_solde_agences');
        $this->view($params,$data);
    }


    /*********detailUser********/
    public function profile($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->profilModel->allProfil();
        $data['agence']= $this->agenceModel->allAgence();
        $data['user']= $this->userModel->getUser(base64_decode($id[0]));
        $params = array('view' => 'admin/profile');
        $this->view($params,$data);
    }

    /******* Action ancien password ****/
    public function verifAncienpassword()
    {
        $verif = $this->userModel->verifPassword($this->utils->securite_xss($_POST['password']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }



    /*************reset password User**************/
    public function updatePasswordUser()
    {
        $password = $this->utils->securite_xss($_POST['password']);
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->userConnecter->rowid;

        $update = $this->userModel->updatePasswordUtilisateur($rowid, $password, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Modification mot de passe', 'Prenom:'.$this->userConnecter->prenom.' Nom:'.$this->userConnecter->nom.' Login:'.$this->userConnecter->login.' Iduser'.$rowid, 'succes', 1, $user_modification);
            $this->rediriger('admin','logout');
        }
        else
        {
            $this->utils->log_journal('Modification mot de passe', 'Prenom:'.$this->userConnecter->prenom.' Nom:'.$this->userConnecter->nom.' Login:'.$this->userConnecter->login.' Iduser'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('admin','index/'.base64_encode('nok'));
        }
    }

    /******* Action verifier email ****/
    public function verifEmailN()
    {
        $verif = $this->profilModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    public function getSeuilAgence($arg = false)
    {
        $data['solde'] = $this->utils->securite_xss($_POST['solde']);
        if($data['solde']>SEUIL_AGENCE) echo 1;
        else echo -1;
    }


    public function parametrerTauxCommission()
    {
        $user_creation = $this->userConnecter->rowid;
        $distributeur = $this->utils->securite_xss(base64_decode($_POST['distributeur']));
        $service = $this->utils->securite_xss_array($_POST['service']);
        $taux = $this->utils->securite_xss_array($_POST['taux']);
        $nbre = sizeof($service);
        $result = $this->agenceModel->deleteTauxCommission($distributeur);

        if($result)
        {
            for($i=0; $i <= $nbre; $i++)
            {
                $result1 = $this->agenceModel->ajouterTauxCommission($distributeur, $service[$i], $taux[$i]);
            }
            if($nbre == $i)
            {
                $this->utils->log_journal('Paramétrage commission', 'Distribbuteur:'.$distributeur.' Service:'.$service, 'succes', 1, $user_creation);
                $this->rediriger('admin','detailAgence/'.base64_encode($distributeur).'/'.base64_encode('ok'));
            }
            else{
                $this->utils->log_journal('Paramétrage commission', 'Distribbuteur:'.$distributeur.' Service:'.$service, 'echec', 1, $user_creation);
                $this->rediriger('admin','detailAgence/'.base64_encode($distributeur).'/'.base64_encode('nok'));
            }
        }
    }

    public function detailParamTaux($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['tauxDist'] = $this->agenceModel->tauxDetail(base64_decode($id[0]));

        if(base64_decode($id[1])=== 'ok'){
            $type_alert='success';
            $alert=$data['lang']['bp_gest9'];
            $params = array('view' =>'admin/taux-detail', 'alert'=>$alert,'type-alert'=>$type_alert);
        }
        elseif(base64_decode($id[1])=== 'nok'){
            $type_alert='success';
            $alert=$data['lang']['bp_gest10'];
            $params = array('view' =>'admin/taux-detail', 'alert'=>$alert,'type-alert'=>$type_alert);
        }
        else{
            $params = array('view' =>'admin/taux-detail');
        }
        $this->view($params, $data);
    }

    /*************reset password User**************/
    public function updateTauxDistributeur()
    {
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $taux = $this->utils->securite_xss($_POST['taux']);
        $distributeur = $this->utils->securite_xss($_POST['taux']);
        $service = $this->utils->securite_xss($_POST['fk_service']);
        $user_modification = $this->userConnecter->rowid;

        $update = $this->agenceModel->updateTauxCommission($rowid, $taux);
        if($update==1)
        {
            $this->utils->log_journal('Modification Taux distributeur', 'Distribbuteur:'.$distributeur.' taux:'.$taux.' Service:'.$service, 'succes', 1, $user_modification);
            $this->rediriger('admin','detailParamTaux/'.base64_encode($rowid).'/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Taux distributeur', 'Distribbuteur:'.$distributeur.' taux:'.$taux.' Service:'.$service, 'echec', 1, $user_modification);
            $this->rediriger('admin','detailParamTaux/'.base64_encode($rowid).'/'.base64_encode('nok'));
        }
    }




}