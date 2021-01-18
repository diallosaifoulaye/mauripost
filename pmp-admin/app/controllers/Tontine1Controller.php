<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class Tontine1Controller extends \app\core\BaseController
{


    public  $messageModel;
    public  $collecteurModel;
    public  $clientModel;
    public  $tontineModel;
    public  $versementModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();

        $this->messageModel = $this->model('MessageModel');
        $this->collecteurModel = $this->model('CollecteurModel','t_collecteur');
        $this->clientModel = $this->model('ClientModel','t_client');
        $this->tontineModel = $this->model('TontineModel','t_tontine');
        $this->versementModel = $this->model('VersementModel','t_versement');

        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********** Accueil *********/
    public function index()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 9) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'tontine/accueil');
        $this->view($params,$data);
    }

    /******************************************************************************** /
    /****************************** GESTION COLLECTEUR *******************************/
    /****************************************************************************** /


    /******************* Liste Collecteurs *******************/
    public function collecteur_list($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_collecteur'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_collecteur'];
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
                $alert = $data['lang']['message_activer_collecteur'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
            if (base64_decode($id[0]) == 3) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_regenere'];
            }
            if (base64_decode($id[0]) == -3) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_regenere'];
            }

        }
        $params = array('view' => 'tontine/collecteur','alert'=>$alert, 'type-alert'=>$type_alert );
        $data['agence']= $this->collecteurModel->allAgence();
        //echo '<pre>'; var_dump($data['agence']);die();

        $this->view($params,$data);
    }

    public function processingCollecteur(){
        $param = [
            "button"=>[
                [ROOT."tontine1/detailCollecteur/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->collecteurModel, "collecteur", $param);
    }

    public function insertCollecteur()
    {
        // var_dump($_POST);exit;
        unset($_POST['valider']);
        $date_creation = date('Y-m-d H:i:s');
        $user_creation = $this->userConnecter->rowid;

        $data = $this->utils->securite_xss_array($_POST);

        $data['user_creation'] = $user_creation ;
        $data['date_creation'] = $date_creation ;
        $password = $this->utils->generation_code(10);
        $data['password'] = sha1('NUMH'.$password);
        $data['telephone'] = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['telephone'])));

        $email = $data['email'];
        $prenom = $data['prenom'];
        $nom = $data['nom'];
        //$login = $data['login'];
        $telephone = $data['telephone'];

        $verif = $this->collecteurModel->verifEmail($email);

        //echo '<pre>'; var_dump($data); die();
        if ($verif == -1){

            $insert = $this->collecteurModel->insert($data);
            if($insert>0){
                $insert = 1 ;
                $this->utils->envoiparametreAuCollecteur($prenom.' '.$nom, $email, $telephone, $password);
                $this->utils->log_journal('Ajout Collecteur', 'Collecteur:'.$_POST['libelle'], 'succes', 1, $user_creation);
            }
            else{
                $insert = -1 ;
                $this->utils->log_journal('Ajout Collecteur', 'Collecteur:'.$_POST['libelle'], 'echec', 1, $user_creation);
            }

            $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));
        }else{
            $insert  = -2;
            $this->utils->log_journal('Ajout Collecteur', 'Collecteur:'.$_POST['libelle'], 'echec', 1, $user_creation);
            $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));
        }

    }

    public function detailCollecteur($id){

        //$data['id'] = base64_decode($id[0]);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence']= $this->collecteurModel->allAgence();
        $data['collecteur'] = $this->collecteurModel->getCollecteurById(base64_decode($id[0]));
        $data['tel_bis'] = trim(str_replace("00", "+",$data['collecteur']['telephone']));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if(isset($id[1])){
                if (base64_decode($id[1]) == 1) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update'];
                }
                if (base64_decode($id[1]) == -1) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }
        }


        }
        $params = array('view' => 'tontine/collecteur-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    public function activeCollecteur(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->collecteurModel->activeCollecteur($id);
        if($insert>0){
            $insert = 4 ;
            $this->utils->log_journal('Activation Collecteur', 'Collecteur:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -4 ;
            $this->utils->log_journal('Activation Collecteur', 'Collecteur:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));

    }

    public function desactiveCollecteur(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->collecteurModel->disableCollecteur($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Desactivation Collecteur', 'Collecteur:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Desactivation Collecteur', 'Collecteur:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));

    }

    public function updateCollecteur()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;

        $_POST['telephone'] = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['telephone'])));

        $insert = $this->collecteurModel->updateCollecteur($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification Collecteur', 'Collecteur:'.$_POST['libelle'], 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification Collecteur', 'Collecteur:'.$_POST['libelle'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine1','detailCollecteur/'.base64_encode($id).'/'.base64_encode($insert));

    }

    /******* Action verifier email ****/
    public function verifEmail()
    {
        $verif = $this->collecteurModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier identifiant ****/
    public function verifLogin()
    {
        $verif = $this->collecteurModel->verifIdentifiant($this->utils->securite_xss($_POST['identifiant']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier email ****/
    public function verifTel()
    {
        $verif = $this->collecteurModel->verifTelephone($this->utils->securite_xss($_POST['telephone']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /*************reset password User**************/
    public function resetPasswordCollect()
    {
        //var_dump($_POST); exit ;
        $password = $this->utils->generation_code(10);
        //var_dump($password);die();
        $user_modification = $this->userConnecter->rowid;

        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $email = $this->utils->securite_xss($_POST['email']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $login = $this->utils->securite_xss($_POST['login']);

        $_POST['password'] = sha1('NUMH'.$password);
        unset( $_POST['nom']);
        unset( $_POST['prenom']);
        unset( $_POST['delete']);
        unset( $_POST['login']);
        unset( $_POST['email']);

        $insert = $this->collecteurModel->resetPasswordCollecteur();
        if($insert>0){
            $insert = 3;
            $this->utils->log_journal('Regénération Mot de Passe Collecteur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'succes', 1, $user_modification);
            $this->utils->envoiNewPassCollecteur($prenom.' '.$nom, $email, $login, $password);
            $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));
        }
        else
        {
            $insert = -3;
            $this->utils->log_journal('Regénération Mot de Passe Collecteur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('tontine1','collecteur_list/'.base64_encode($insert));
        }
    }

    /******************* Transactions du Collecteur *******************/
    public function processingTransaction($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing3($this->collecteurModel, "transactionsDuCollecteur", $param);
    }

    public function processing3($model, $method , $param = [])
    {
//        "  LIMIT ".$requestData['start']." ,".$requestData['length']."   "
        extract($param);
        $requestData = $_REQUEST;
        $count = $method.'Count';
        $tempData = (!is_null($args)) ? $model->$method($args) : $model->$method();
        $totalData = (!is_null($args)) ? $model->$count($args) : $model->$count();
        $rows = (!empty($requestData['search']['value'])) ? ((!is_null($args)) ? $model->$method($args,$requestData['search']['value']) : $model->$method($requestData['search']['value'])) : $tempData;
        $totalFiltered = $totalData;
        $data = [];
        foreach ($rows as $item) {
            $dataId = $item['rowid'];
            unset($item['rowid']);


            if(isset($item['date_debut'])) $item['date_debut'] = $this->utils->date_mois_en_lettre($item["date_debut"]);
            if(isset($item['date_c'])) $item['date_c'] = $this->utils->date_mois_en_lettre($item["date_c"]);
            if(isset($item['date_avec_heure'])) $item['date_avec_heure'] = $this->utils->date_heure_mois_en_trois_lettre($item["date_avec_heure"]);

            $arg = (isset($item['solde'])) ? 'solde' : ((isset($item['stock'])) ? 'stock' : false);
            if($arg != false) $item[$arg] = $this->utils->number_format($item[$arg]);
            if(isset($item['numero'])) $item['numero'] = $this->utils->truncate_carte($item['numero']);
            if(isset($item['telephone'])) $item['telephone'] = $this->utils->truncate_carte($item['telephone']);

            //Gestion Transaction
            $arg = (isset($item['statut'])) ? 'statut' : ((isset($item['statut'])) ? 'statut' : false);
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success">'.$lang['succes_transaction'].'</span>' : '<span class="text-danger">'.$lang['echec_transaction'].'</span>';

            $arg = (isset($item['typecompte'])) ? 'typecompte' : false;
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? $lang['carte'] : $lang['comptes'];

            $temp = array_values($item);

            for($i = 0 ; $i < count($button) ; $i++){
                $href = "<a style='margin-left: 50%;' href='".$button[$i][0].base64_encode($dataId)."'><i class='".$button[$i][1]."'></i></a>";
                array_push($temp,$href);
            }
            $data[] = $temp;
        }
        $json_data = array(
            "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    /******************* Enrollements du Collecteur *******************/
    public function processingClientsEnrolles($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing1($this->collecteurModel, "enrollementsDuCollecteur", $param);
    }

    /******************* Equipements du Collecteur *******************/
    public function processingEquipementsCollecteur($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing4($this->collecteurModel, "equipementsDuCollecteur", $param);
    }

    public function processing4($model, $method , $param = [])
    {
//        "  LIMIT ".$requestData['start']." ,".$requestData['length']."   "
        extract($param);
        $requestData = $_REQUEST;
        $count = $method.'Count';
        $tempData = (!is_null($args)) ? $model->$method($args) : $model->$method();
        $totalData = (!is_null($args)) ? $model->$count($args) : $model->$count();
        $rows = (!empty($requestData['search']['value'])) ? ((!is_null($args)) ? $model->$method($args,$requestData['search']['value']) : $model->$method($requestData['search']['value'])) : $tempData;
        $totalFiltered = $totalData;
        $data = [];
        foreach ($rows as $item) {
            $dataId = $item['rowid'];
            unset($item['rowid']);


            if(isset($item['date_debut'])) $item['date_debut'] = $this->utils->date_jj_mm_aaaa($item["date_debut"]);
            if(isset($item['date_avec_heure'])) $item['date_avec_heure'] = $this->utils->date_heure_mois_en_trois_lettre($item["date_avec_heure"]);

            $arg = (isset($item['solde'])) ? 'solde' : ((isset($item['stock'])) ? 'stock' : false);
            if($arg != false) $item[$arg] = $this->utils->number_format($item[$arg]);
            if(isset($item['numero'])) $item['numero'] = $this->utils->truncate_carte($item['numero']);
            if(isset($item['telephone'])) $item['telephone'] = $this->utils->truncate_carte($item['telephone']);

            //Gestion Affectation
            $arg = (isset($item['statut'])) ? 'statut' : ((isset($item['statut'])) ? 'statut' : false);
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success">'.$lang['equip_affecte'].'</span>' : '<span class="text-danger">'.$lang['equip_desaffecte'].'</span>';

            $arg = (isset($item['typecompte'])) ? 'typecompte' : false;
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? $lang['carte'] : $lang['comptes'];

            $temp = array_values($item);

            for($i = 0 ; $i < count($button) ; $i++){
                $href = "<a style='margin-left: 50%;' href='".$button[$i][0].base64_encode($dataId)."'><i class='".$button[$i][1]."'></i></a>";
                array_push($temp,$href);
            }
            $data[] = $temp;
        }
        $json_data = array(
            "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    /******************************************************************************** /
    /****************************** GESTION CLIENT *******************************/
    /****************************************************************************** /


    /******************* Liste clients *******************/
    public function client_list($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_client'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_client'];
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
                $alert = $data['lang']['message_activer_client'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
            if (base64_decode($id[0]) == 5) {
                $type_alert = 'success';
                $alert = $data['lang']['message_ouvrir_tontine'];
            }
            if (base64_decode($id[0]) == -5) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_ouvrir_tontine'];
            }
        }
        $params = array('view' => 'tontine/client','alert'=>$alert, 'type-alert'=>$type_alert );
        $data['agence']= $this->clientModel->allAgence();
        //echo '<pre>'; var_dump($data['agence']);die();

        $this->view($params,$data);
    }

    public function processingClient() {
        $param = [
            "button"=>[
                [ROOT."tontine1/detailClient/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->clientModel, "client", $param);
    }

    public function insertClient()
    {
       // var_dump($_POST);exit;
        unset($_POST['valider']);
        $date_creation = date('Y-m-d H:i:s');
        $user_creation = $this->userConnecter->rowid;

        $data = $this->utils->securite_xss_array($_POST);

        $data['user_creation'] = $user_creation ;
        $data['date_creation'] = $date_creation ;
        $data['telephone'] = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['telephone'])));

        //echo '<pre>'; var_dump($data); die();

        $insert = $this->clientModel->insert($data);
        $idClient = $insert ;
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Ajout client', 'client:'.$_POST['libelle'], 'succes', 1, $user_creation);
            }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout client', 'client:'.$_POST['libelle'], 'echec', 1, $user_creation);
            }

        $this->rediriger('tontine1','detailClient/'.base64_encode($idClient));

    }

    public function detailClient($id){

       // var_dump(base64_decode($id[0])); ;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence']= $this->clientModel->allAgence();
        $data['offre']= $this->tontineModel->allOffres();
        $data['client']= $this->clientModel->getClientById(base64_decode($id[0]));
        $data['tontines'] = $this->clientModel->souscriptionsByClient(base64_decode($id[0]));
        $data['nbTontines'] = $this->clientModel->nbTontinesByClient(base64_decode($id[0]));
        $data['transactionsTontines'] = $this->clientModel->transactionsDuClientTable(base64_decode($id[0]));
        //var_dump($data['transactionsTontines']);exit;
        //echo base64_decode($id[0]) ;
        // var_dump($data['transactionsTontines']); exit;
        // echo '<pre>' ;
        //var_dump($data['tontines']); exit;
        // $data['nb_tontine'] = $this->clientModel->souscriptionsDuClientCount(base64_decode($id[0]));
        //$data['client']= $this->clientModel->getClient1ById(base64_decode($id[0]));

        //echo '<pre>'; var_dump($data['client']);die();
        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if(isset($id[1])){
                if (base64_decode($id[0]) == 1) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update'];
                }
                if (base64_decode($id[0]) == -1) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }
            }

        }

        $params = array('view' => 'tontine/client-detaill', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
}

    public function detailClientt($id){





        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['agence']= $this->clientModel->allAgence();
        $data['offre']= $this->tontineModel->allOffres();
        $data['client']= $this->clientModel->getClientById(base64_decode($id[0]));
        $data['tontines'] = $this->clientModel->souscriptionsByClient(base64_decode($id[0]));
        $data['nbTontines'] = $this->clientModel->nbTontinesByClient(base64_decode($id[0]));
        $data['transactionsTontines'] = $this->clientModel->transactionsDuClientTable(base64_decode($id[0]));
        //var_dump($data['transactionsTontines']);exit;
        //echo base64_decode($id[0]) ;
       // var_dump($data['transactionsTontines']); exit;
        // echo '<pre>' ;
        //var_dump($data['tontines']); exit;
        // $data['nb_tontine'] = $this->clientModel->souscriptionsDuClientCount(base64_decode($id[0]));
        //$data['client']= $this->clientModel->getClient1ById(base64_decode($id[0]));

        //echo '<pre>'; var_dump($data['client']);die();
        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if(isset($id[1])){
                if (base64_decode($id[0]) == 1) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update'];
                }
                if (base64_decode($id[0]) == -1) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }
            }

        }

        $params = array('view' => 'tontine/client-detaill', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }


    public function activeClient(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->clientModel->activeClient($id);
        if($insert>0){
            $insert = 4 ;
            $this->utils->log_journal('Activation client', 'client:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -4 ;
            $this->utils->log_journal('Activation client', 'client:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','client_list/'.base64_encode($insert));

    }

    public function desactiveClient(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->clientModel->disableClient($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Desactivation client', 'client:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Desactivation client', 'client:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','client_list/'.base64_encode($insert));

    }

    public function updateClient()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->clientModel->updateClient($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification client', 'client:'.$_POST['libelle'], 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification client', 'client:'.$_POST['libelle'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine1','detailClient/'.base64_encode($id).'/'.base64_encode($insert));

    }

    /******* Action verifier email ****/
    public function verifEmailClient()
    {
        $verif = $this->clientModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier email ****/
    public function verifTelClient()
    {
        $verif = $this->clientModel->verifTelephone($this->utils->securite_xss($_POST['telephone']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /*************Ouvrir tontine **************/
    public function ouvrirTontine()
    {
        //var_dump($_POST); exit ;
        $id = $this->utils->securite_xss($_POST['rowid']);
        $date_creation = date('Y-m-d H:i:s');
        $user_creation = $this->userConnecter->rowid;

        $_POST['user_creation'] = $user_creation ;
        $_POST['date_creation'] = $date_creation ;
        unset($_POST['delete']);
        unset($_POST['rowid']);
        $_POST = $this->utils->securite_xss_array($_POST);

        $insert = $this->clientModel->demarrerTontine($_POST);
        if($insert>0){
            $insert = 5;
            $this->utils->log_journal('Ouverture Tontine', 'Tontine:'.$id, 'succes', 1, $user_creation);

            $this->rediriger('tontine1','client_list/'.base64_encode($insert));
        }
        else
        {
            $insert = -5;
            $this->utils->log_journal('Ouverture Tontine', 'Tontine:'.$id, 'echec', 1, $user_creation);
            $this->rediriger('tontine1','client_list/'.base64_encode($insert));
        }
    }

    public function processingTransactionClient($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing3($this->clientModel, "transactionsDuClient", $param);
    }


    public function processingTransactionClientBV($id){

        $param = [
            "button"=>[
                [ROOT."tontine1/detailTrasaction/", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing3($this->clientModel, "transactionsDuClientBV", $param);
    }

    /******************* Processing des souscriptions du client aux tontines lancées *******************/
    public function processingSouscriptions($customerid){

        $param = [
            "button"=>[
                [ROOT."tontine1/detailTontine/", "fa fa-search"]
            ],
            "args"=>[$customerid[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing1($this->clientModel, "souscriptionsDuClient", $param);
    }


    /******************************************************************************** /
    /****************************** GESTION TONTINE *******************************/
    /****************************************************************************** /


    /******************* Liste tontines *******************/
    public function tontine_list($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_tontine'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_tontine'];
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
                $alert = $data['lang']['message_activer_tontine'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
            if (base64_decode($id[0]) == 6) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_fermeture'];
            }
            if (base64_decode($id[0]) == -6) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_fermeture'];
            }
            if (base64_decode($id[0]) == 8) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_abandon'];
            }
            if (base64_decode($id[0]) == -8) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_abandon'];
            }

        }

        $params = array('view' => 'tontine/tontine','alert'=>$alert, 'type-alert'=>$type_alert );
        $data['offre']= $this->tontineModel->allOffres();
        $data['client']= $this->tontineModel->allClients();
        //echo '<pre>'; var_dump($data['agence']);die();

        $this->view($params,$data);
    }

    public function processingTontine($id){
        $param = [
            "button"=>[
                [ROOT."tontine1/detailTontine/", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing1($this->tontineModel, "tontine", $param);
    }

    public function insertTontine()
    {
       //echo '<pre>';
       //var_dump($_POST);exit;
        $date_adhesion = $_POST['datedeb'] ;
        $mois_c = $_POST['nb_mise_c'] ;
        $mt_adhesion = $_POST['mt_adhesion'] ;

        unset($_POST['valider']);
        unset($_POST['m_adhesion']);
        unset($_POST['datedeb']);
        unset($_POST['nb_mise_c']);
        unset($_POST['m_misfe_c']);
        unset($_POST['mt_adhesion']);
        $date_creation = date('Y-m-d H:i:s');
        $user_creation = $this->userConnecter->rowid;


        $data = $this->utils->securite_xss_array($_POST);

        $data['user_creation'] = $user_creation ;
        $data['date_creation'] = $date_creation ;
        $data['date_adhesion'] = $date_adhesion ;
        $data['mois_c'] = $mois_c ;
        $data['mois_r'] = $data['periode'] - $mois_c ;
        $data['montant_encours'] = $mt_adhesion ;

        //echo '<pre>'; var_dump($data); die();

        $insert = $this->tontineModel->insert($data);


        //Insertion de(s) transaction
        if ($insert){

            $data1 = array() ;


            //DETAIL TRANSACTION
            //$data1['penalite'] = $data['penalite'] ;
            $data1['nb_cotisation'] = $mois_c ;
            $data1['montant_cotise'] = $mois_c*$data['mise'] ;
            $data1['num_cotisation'] = $mois_c  ;

            $data1['montant'] = $_POST['mise'] ;
            $data1['fk_tontine'] = $insert ;
            $data1['fk_client'] = $_POST['fk_client'] ;
            $data1['fk_agence'] = $this->userConnecter->fk_agence ;

            $date_creation = date('Y-m-d H:i:s');
            $data1['date_transaction'] = $date_creation ;
            $data1['commentaire'] = "Adhesion tontine" ;
            $insertTransaction = $this->tontineModel->insertTrasanction($data1);

            if($insertTransaction > 0){

                $this->utils->log_journal('Ajout Cotisation', 'tontine :'.$insert, 'succes', 1, $user_creation);
            }
            else{
                $insertTransaction = -1 ;
                $this->utils->log_journal('Ajout Cotisation', 'tontine :'.$insert, 'echec', 1, $user_creation);
            }


        }


        if(($insert>0) && ($insertTransaction>0)){
           // $insert = 1 ;
            $this->utils->log_journal('Ajout Tontine', 'tontine :'.$_POST['libelle'], 'succes', 1, $user_creation);
        }
        else{
            //$insert = -1 ;
            $this->utils->log_journal('Ajout Tontine', 'tontine :'.$_POST['libelle'], 'echec', 1, $user_creation);
        }

        $this->rediriger('tontine1','detailClient/'.base64_encode($data['fk_client']));

    }

    public function insertTCotisation()
    {
        $data = $this->utils->securite_xss_array($_POST);
     //echo '<pre>' ;
     //var_dump($data); exit;

        $data1 = [] ;
        $data1['montant'] = $data['mtc'] ;
        $data1['fk_tontine'] = $data['fk_tontine'] ;
        $data1['fk_client'] = $data['fk_client'] ;
        $data1['fk_agence'] = $this->userConnecter->fk_agence ;
        $nbCotisationEncours = $data['mois_c'] ;
        $dureeT= $data['duree_total'] ;

        $date_creation = date('Y-m-d H:i:s');
        $data1['date_transaction'] = $date_creation ;

        //DETAIL TRANSACTION
        $data1['penalite'] = $data['penalite'] ;
        $data1['nb_cotisation'] = $data['nb_c'] + $data['mp'] ;
        $data1['montant_cotise'] = $data['mtc'] + $data1['penalite'] ;
        $data1['num_cotisation'] = $data['mois_c'] + $data['nb_c'] + $data['mp'] ;

        $user_creation = $this->userConnecter->rowid;
        $insert = $this->tontineModel->insertTrasanction($data1);
//$insert = 1;
        $insert1 = -1 ;
        if ($insert>0){
            $data2 =[];
            $data2['statut'] = 1 ;
            $data2['montant_encours'] = $data['mtc'];
            $data2['penalite'] = $data['penalite'];
            $data2['mois_c'] = $data['nb_c'] + $data['mp'];
            $data2['mois_r'] = $data['nbCotisationRestant'];
            if ($dureeT == $nbCotisationEncours + $data2['mois_c'])
                $data2['statut'] = 2 ;
            $insert1 = $this->tontineModel->updateTontineC($data2['montant_encours'],$data2['mois_c'],$data1['fk_tontine'],$data2['mois_r'],$data2['statut'], $data2['penalite']);
        }


        if(($insert>0) && ($insert1>0)){
            $insert = 1 ;
            $this->utils->log_journal('Ajout Cotisation', 'tontine :'.$_POST['fk_tontine'], 'succes', 1, $user_creation);
        }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout Cotisation', 'tontine :'.$_POST['fk_tontine'], 'echec', 1, $user_creation);
        }

        $this->rediriger('tontine1','detailClient/'.base64_encode($data1['fk_client']));

    }

    public function detailTontine($id){
       // var_dump(base64_decode($id[0])) ;
        //var_dump($this->tontineModel->cotisationsTontine(base64_decode($id[0])));exit;

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offre']= $this->tontineModel->allOffres();
        $data['client']= $this->tontineModel->allClients();
        $data['tontine']= $this->tontineModel->getTontineByIdBis(base64_decode($id[0]));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if(isset($id[1])){
                if(base64_decode($id[1])==1){
                    $type_alert='success';
                    $alert=$data['lang']['message_success_update'];
                }
                if(base64_decode($id[1])==-1){
                    $type_alert='error';
                    $alert= $data['lang']['message_error_update'];
                }
            }

        }



        $params = array('view' => 'tontine/tontine-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    public function cloturerTontine(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->tontineModel->closeTontine($id);
        if($insert>0){
            $insert = 6 ;
            $this->utils->log_journal('Fermeture Tontine', 'Tontine:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -6 ;
            $this->utils->log_journal('Fermeture Tontine', 'Tontine:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','tontine_list/'.base64_encode($insert));

    }

    public function abandonnerTontine(){
        $id = $this->utils->securite_xss($_POST['rowid']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->tontineModel->abortTontine($id);
        if($insert>0){
            $insert = 8 ;
            $this->utils->log_journal('Abandon Tontine', 'Tontine :'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -8 ;
            $this->utils->log_journal('Abandon Tontine', 'Tontine :'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','tontine_list/'.base64_encode($insert));

    }

    public function updateTontine()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->tontineModel->updateTontine($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification Tontine', 'Tontine:'.$_POST['libelle'], 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification Tontine', 'Tontine:'.$_POST['libelle'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine1','detailTontine/'.base64_encode($id).'/'.base64_encode($insert));

    }

    public function processing1($model, $method , $param = [])
    {
//        "  LIMIT ".$requestData['start']." ,".$requestData['length']."   "
        extract($param);
        $requestData = $_REQUEST;
        $count = $method.'Count';
        $tempData = (!is_null($args)) ? $model->$method($args) : $model->$method();
        $totalData = (!is_null($args)) ? $model->$count($args) : $model->$count();
        $rows = (!empty($requestData['search']['value'])) ? ((!is_null($args)) ? $model->$method($args,$requestData['search']['value']) : $model->$method($requestData['search']['value'])) : $tempData;
        $totalFiltered = $totalData;
        $data = [];
        foreach ($rows as $item) {
            $dataId = $item['rowid'];
            unset($item['rowid']);


            if(isset($item['date_debut'])) $item['date_debut'] = $this->utils->date_jj_mm_aaaa($item["date_debut"]);;
            if(isset($item['date_avec_heure'])) $item['date_avec_heure'] = $this->utils->date_heure_mois_en_trois_lettre($item["date_avec_heure"]);

            $arg = (isset($item['solde'])) ? 'solde' : ((isset($item['stock'])) ? 'stock' : false);
            if($arg != false) $item[$arg] = $this->utils->number_format($item[$arg]);
            if(isset($item['numero'])) $item['numero'] = $this->utils->truncate_carte($item['numero']);
            if(isset($item['telephone'])) $item['telephone'] ;

            //Gestion tontine
            $arg = (isset($item['statut']) ? 'statut' : false);
            if ($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success" >'.$lang['encours'].'</span>' : (($item[$arg] == 2)? '<span class="text-danger">'.$lang['cloture'].'</span>' : '<span class="text-warning">'.$lang['abandon'].'</span>');

            /*$arg = (isset($item['statut'])) ? 'statut' : ((isset($item['statut'])) ? 'statut' : false);
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success">'.$lang['encours'].'</span>' : '<span class="text-danger">'.$lang['cloture'].'</span>';*/

            $arg = (isset($item['typecompte'])) ? 'typecompte' : false;
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? $lang['carte'] : $lang['comptes'];

            $temp = array_values($item);

            for($i = 0 ; $i < count($button) ; $i++){
                $href = "<a style='margin-left: 50%;' href='".$button[$i][0].base64_encode($dataId)."'><i class='".$button[$i][1]."'></i></a>";
                array_push($temp,$href);
            }
            $data[] = $temp;
        }
        $json_data = array(
            "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    /******************* Processing des cotisations pour une tontine *******************/
    public function processingCotisations($id){
       // var_dump($id);exit;

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing1($this->tontineModel, "cotisationsTontine", $param);
    }


    /******************************************************************************** /
    /****************************** GESTION RETRAIT *******************************/
    /****************************************************************************** /

    /******************* Liste retraits *******************/
    public function retrait_list($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_save_retrait'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_save_retrait'];
            }
            if (base64_decode($id[0]) == 2) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_update_retrait'];
            }
            if (base64_decode($id[0]) == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_update_retrait'];
            }
            if (base64_decode($id[0]) == 3) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_archivage_retrait'];
            }
            if (base64_decode($id[0]) == -3) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_archivage_retrait'];
            }
        }

        $params = array('view' => 'tontine/retrait','alert'=>$alert, 'type-alert'=>$type_alert );
        $data['client']= $this->tontineModel->allClientsAPayer();
        $data['tontine']= $this->tontineModel->getTontinesWithCustomers();

        //echo '<pre>'; var_dump($data['tontine']);die();

        $this->view($params,$data);
    }

    public function processingRetrait(){
        $param = [
            "button"=>[
                [ROOT."tontine1/detailRetrait/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing2($this->tontineModel, "retrait", $param);
    }

    public function processing2($model, $method , $param = [])
    {
//        "  LIMIT ".$requestData['start']." ,".$requestData['length']."   "
        extract($param);
        $requestData = $_REQUEST;
        $count = $method.'Count';
        $tempData = (!is_null($args)) ? $model->$method($args) : $model->$method();
        $totalData = (!is_null($args)) ? $model->$count($args) : $model->$count();
        $rows = (!empty($requestData['search']['value'])) ? ((!is_null($args)) ? $model->$method($args,$requestData['search']['value']) : $model->$method($requestData['search']['value'])) : $tempData;
        $totalFiltered = $totalData;
        $data = [];
        foreach ($rows as $item) {
            $dataId = $item['rowid'];
            unset($item['rowid']);


            if(isset($item['date_debut'])) $item['date_debut'] = $this->utils->date_jj_mm_aaaa($item["date_debut"]);;

            $arg = (isset($item['solde'])) ? 'solde' : ((isset($item['stock'])) ? 'stock' : false);
            if($arg != false) $item[$arg] = $this->utils->number_format($item[$arg]);
            if(isset($item['numero'])) $item['numero'] = $this->utils->truncate_carte($item['numero']);
            if(isset($item['telephone'])) $item['telephone'] = $this->utils->truncate_carte($item['telephone']);

            //Gestion Retrait
            $arg = (isset($item['retrait'])) ? 'retrait' : ((isset($item['retrait'])) ? 'retrait' : false);
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success">'.$lang['retrait_effectif'].'</span>' : '<span class="text-danger">'.$lang['retrait_non_effectif'].'</span>';

            $arg = (isset($item['typecompte'])) ? 'typecompte' : false;
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? $lang['carte'] : $lang['comptes'];

            $temp = array_values($item);

            for($i = 0 ; $i < count($button) ; $i++){
                $href = "<a style='margin-left: 50%;' href='".$button[$i][0].base64_encode($dataId)."'><i class='".$button[$i][1]."'></i></a>";
                array_push($temp,$href);
            }
            $data[] = $temp;
        }
        $json_data = array(
            "draw"            => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    public function saveRetrait()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        $user_modification = $this->userConnecter->rowid;

        unset($_POST['valider']);
        $_POST = $this->utils->securite_xss_array($_POST);

        $insert = $this->tontineModel->enregRetrait($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Sauvegarde Retrait', 'Retrait:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -1 ;
            $this->utils->log_journal('Sauvegarde Retrait', 'Retrait:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','retrait_list/'.base64_encode($insert));

    }


    public function saveRetrait1()
    {

        //echo '<pre>';
        //var_dump($_POST); exit;
        $_POST = $this->utils->securite_xss_array($_POST);
        $id = $this->utils->securite_xss($_POST['rowid']);
        $user_modification = $this->userConnecter->rowid;
        $nature = $_POST['nature'] ;

        $data = [];
        $date_creation = date('Y-m-d H:i:s');
        $data['date_retrait'] = $date_creation;

        if ($nature == 'abdandon'){
            $data['montant_retrait'] = $_POST['montant'] ;
            $data['statut'] = 3 ;
            $data['retrait'] = 1 ;
            $data['com_bv'] = $_POST['com'] ;
            $_POST = $data ;
        }

        if ($nature == 'retrait'){
            $data['montant_retrait'] = $_POST['montant_t'] ;
            $data['montant_bv'] = $_POST['bv'] ;
            $data['com_bv'] = $_POST['com'] ;
            $data['retrait'] = 1 ;
            $data['statut'] = 2 ;
            $_POST = $data ;
        }

        //var_dump($_POST); exit;

        $insert = $this->tontineModel->updateTontine($id);



        //$_POST['statut'] = $this->utils->securite_xss($_POST['valider']) ;

        // var_dump($_POST); exit;

       // unset($_POST['valider']);


       // $insert = $this->tontineModel->enregRetrait($id);
        if($insert>0){
            $insert = 3 ;
            $this->utils->log_journal('Sauvegarde Retrait', 'Retrait:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -3 ;
            $this->utils->log_journal('Sauvegarde Retrait', 'Retrait:'.$id, 'echec', 1, $user_modification);
        }



        $this->rediriger('tontine1','detailRetrait/'.base64_encode($id).'/'.base64_encode($insert));

    }

    public function detailRetrait($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offre']= $this->tontineModel->allOffres();
        $data['client']= $this->tontineModel->allClients();
        $data['tontine']= $this->tontineModel->getTontinesWithCustomers();
        $data['retrait']= $this->tontineModel->getRetraitById(base64_decode($id[0]));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if(isset($id[1])){
                if (base64_decode($id[1]) == 2) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update'];
                }
                if (base64_decode($id[1]) == -2) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }

                if (base64_decode($id[1]) == 3) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_save_retrait'];
                }
                if (base64_decode($id[1]) == -3) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_save_retrait'];
                }
            }
        }



        $params = array('view' => 'tontine/retrait-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    public function updateRetrait()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->tontineModel->updateRetrait($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Modification Retrait', 'Retrait:'.$_POST['libelle'], 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Modification Retrait', 'Retrait:'.$_POST['libelle'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine1','detailRetrait/'.base64_encode($id).'/'.base64_encode($insert));

    }

    public function getInfosClientTontine()
    {
        $idclient = $_POST['idclient'];
        $data = $this->tontineModel->getDonneesClients($idclient);
        echo json_encode($data);
    }

    public function archivageRetrait()
    {
        $id = $this->utils->securite_xss($_POST['rowid']);
        $user_modification = $this->userConnecter->rowid;

        unset($_POST['valider']);
        $_POST = $this->utils->securite_xss_array($_POST);

        $insert = $this->tontineModel->archiverRetrait($id);
        if($insert>0){
            $insert = 3 ;
            $this->utils->log_journal('Archivage Retrait', 'Archivage:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -3 ;
            $this->utils->log_journal('Archivage Retrait', 'Archivage:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine1','retrait_list/'.base64_encode($insert));

    }

    public function processingRetraitsClient($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing2($this->tontineModel, "retraitsDuClient", $param);
    }

    /***************** recu retrait *********************/
    public function recuRetrait()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //var_dump($_POST);exit;

        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date2']);

        //$data['recu'] = $this->tontineModel->recu($date1, $date2);

        $idtontine = $this->utils->securite_xss($_POST['rowid']);
        //$idclient = $this->utils->securite_xss($_POST['fk_client']);

        $data['recu'] = $this->tontineModel->recu($idtontine);
        $data['recu']['agent'] =  $this->tontineModel->getAgent($this->userConnecter->rowid);
        //var_dump($data['recu']);die();

        $params = array('view' => 'tontine/retraitTontine');
        $this->view($params,$data);
    }




    public function recuTransaction()
    {


        //var_dump($_POST);exit;

//echo '<pre>';

        $idTransaction = $this->utils->securite_xss($_POST['idTransaction']);
        //$idclient = $this->utils->securite_xss($_POST['fk_client']);

        $data['recu'] = $this->tontineModel->recuTransaction($idTransaction);
        $data['recu']['agent'] =  $this->tontineModel->getAgent($this->userConnecter->rowid);
        //var_dump($data['recu']);die();

        $params = array('view' => 'tontine/retraitTransaction');
        $this->view($params,$data);
    }
    /******************************************************************************** /
    /****************************** GESTION VERSEMENTS *******************************/
    /****************************************************************************** /


    /******************* Liste Versements *******************/
    public function versement_list($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {


            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_collecteur'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_collecteur'];
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
                $alert = $data['lang']['message_activer_collecteur'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
            if (base64_decode($id[0]) == 3) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_regenere'];
            }
            if (base64_decode($id[0]) == -3) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_regenere'];
            }
        }

        $params = array('view' => 'tontine/versement','alert'=>$alert, 'type-alert'=>$type_alert );
        $data['tontinier']= $this->versementModel->getCollecteurs();
        $data['agence']= $this->versementModel->allAgence();
        //echo '<pre>'; var_dump($data['agence']);die();

        $this->view($params,$data);
    }

    public function processingVersement(){
        $param = [
            "button"=>[
                [ROOT."tontine1/detailVersement/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->versementModel, "versement", $param);
    }

    public function detailVersement($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['tontinier']= $this->versementModel->getCollecteurs();
        $data['agence']= $this->versementModel->allAgence();
        $data['versement']= $this->versementModel->getVersementById(base64_decode($id[0]));

        $alert = '' ;
        $type_alert='';


        if ($id!= null) {

            if(isset($id[1])){
                if (base64_decode($id[1]) == 1) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update'];
                }
                if (base64_decode($id[1]) == -1) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }
            }


        }

        $params = array('view' => 'tontine/versement-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    /***************** recu retrait *********************/
    public function imprimerVersement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        /*$date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);*/
        $data['date'] = $this->utils->securite_xss($_POST['date2']);
        //$data['recu'] = $this->versementModel->recu($date1, $date2);

        $idversement = $this->utils->securite_xss($_POST['rowid']);
        $data['etat'] = $this->versementModel->imprimer($idversement);
        //var_dump($data['etat']);die();

        $params = array('view' => 'tontine/etatVersement');
        $this->view($params,$data);
    }






}