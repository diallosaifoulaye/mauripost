<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class TontineController extends \app\core\BaseController
{


    public  $messageModel;
    public  $equipementModel;
    public  $offreModel;
    private  $collecteurModel;
    private  $clientModel;
    private  $tontineModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();

        $this->messageModel = $this->model('MessageModel');
        $this->equipementModel = $this->model('EquipementModel');
        $this->offreModel = $this->model('OffreModel','t_offres');
        $this->collecteurModel = $this->model('CollecteurModel','t_collecteur');
        $this->clientModel = $this->model('ClientModel','t_client');
        $this->tontineModel = $this->model('TontineModel','t_tontine');

        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste User*********/
    public function index()
    {

        //var_dump($data['montantVerse'] = $this->collecteurModel->getVersementToDay()); exit ;
        $data['montantCollecte'] = $this->collecteurModel->getCollectToDay();
        $data['montantVerse'] = $this->collecteurModel->getVersementToDay();

        $data['clients'] = $this->clientModel->nbreClientsActifs();
        $data['tontines'] = $this->tontineModel->nbreTontinesEnCours();
        $data['collectEncours'] = $this->collecteurModel->nbreCollecteursEnExercice();
        $data['equipements'] = $this->equipementModel->nbreEquipements();
       // var_dump( $data['equipements']); exit ;

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 9) );
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'tontine/accueil');
        $this->view($params,$data);
    }

    // Retrait
    public function searchTontinesClient(){
       //echo '<pre>';
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(192,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));
        $data['client'] = count($this->clientModel->getClientByTel($telephone)) ? $this->clientModel->getClientByTel($telephone)[0] : NULL;
        //var_dump($data['client']); echo 'GGGGGG'; exit;

        if ($data['client'] != NULL)
            $data['tontinesClients'] =  $this->clientModel->tontinesByClient($data['client']['rowid']);
            //$data['tontinesClients'] =  $this->clientModel->souscriptionsByClient($data['client']['rowid']);

       //var_dump( $data['tontinesClients']); exit;


        $params = array('view' => 'tontine/result-detail');
        $this->view($params,$data);

    }

    public function retrait(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(192,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'tontine/retrait-tontine-search');
        $this->view($params,$data);

    }

    /*Début Gestion des types d'équipement*/

    public function listEquipements($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_el'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_aff'];
            }

        }
        $data['types'] = $this->equipementModel->getTypes();
        $params = array('view' => 'tontine/type-equipement', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

    }

    public function inserType(){
        //var_dump($_POST); exit;
        unset($_POST['valider']);
        $user_creation = $this->userConnecter->rowid;
        $data = $this->utils->securite_xss_array($_POST);
        $insert = $this->equipementModel->insertType($data);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Ajout Type équipement', 'équipement:'.$_POST['libelle'], 'succes', 1, $user_creation);
        }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout équipement', 'équipement:'.$_POST['libelle'], 'echec', 1, $user_creation);
        }

        $this->rediriger('tontine','listEquipements/'.base64_encode($insert));


    }

    public function detailType($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['type']= $this->equipementModel->getTypeById(base64_decode($id[0]));

       // var_dump('MMM'.base64_decode($id[1])); exit;
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

                if (base64_decode($id[1]) == 4) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_activer_element'];
                }
                if (base64_decode($id[1]) == -4) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }

                if (base64_decode($id[1]) == 5) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_desactiver_element'];
                }
                if (base64_decode($id[1]) == -5) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update'];
                }
            }

        }
        $params = array('view' => 'tontine/type-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }


    public function desactiverType(){
        $id = intval($this->utils->securite_xss($_POST['idtype']));
        $action = $this->utils->securite_xss($_POST['action']);
        $_POST['etat'] = intval($this->utils->securite_xss($_POST['etat'])) ;
        unset($_POST['idtype']);
        unset($_POST['action']);
        if($action=='update')  unset($_POST['etat']);
       // var_dump($_POST); exit ;
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->equipementModel->updateType($id);

        if($action=='activer'){

            if(($insert>0)){
                $insert = 4 ;
                $this->utils->log_journal('Activation Type', 'Type:'.$id, 'succes', 1, $user_modification);
            }else
            {
                $insert = -4 ;
                $this->utils->log_journal('Activation Type', 'Type:'.$id, 'echec', 1, $user_modification);
            }
        }


        if($action=='desactiver'){
            if(($insert>0)){
                $insert = 5 ;
                $this->utils->log_journal('Desactivation Type', 'Type:'.$id, 'succes', 1, $user_modification);
            }else
            {
                $insert = -5 ;
                $this->utils->log_journal('Desactivation Type', 'Type:'.$id, 'echec', 1, $user_modification);
            }
        }


        if($action=='update'){
            if(($insert>0)){
                $insert = 1 ;
                $this->utils->log_journal('Update Type', 'Type:'.$id, 'succes', 1, $user_modification);
            }
            else
            {
                $insert = -1 ;
                $this->utils->log_journal('Update Type', 'Type:'.$id, 'echec', 1, $user_modification);
            }
        }


        $this->rediriger('tontine','detailType/'.base64_encode($id).'/'.base64_encode($insert));

    }



    public function processingType(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailType/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->equipementModel, "getAllTypes", $param);
    }




    /*Fin Gestion des types d'équipement*/

    /*Débuts
     Gestion des versements*/

    public function insertVersement(){

        $nombre = count($_POST['lesids']) ;
        $id = intval($_POST['fk_collecteur']) ;
        if ($nombre > 0){
            $_POST = $this->utils->securite_xss_array($_POST);
            $data = array();
            $user_creation = $this->userConnecter->rowid;
            $agence = $this->userConnecter->fk_agence;
            $data['fk_collecteur'] = $id;
            $data['fk_agence'] = $agence ;
            $data['user_crea'] = $user_creation;
            $data['etat'] = 1 ;
            $insert = -1 ;
            $insertEtat = 0 ;
            $updateEtat = 0 ;
            try{
                $this->getConnexion()->beginTransaction() ;

                for($i=0; $i<$nombre;$i++){
                    $data['date_versement'] = $_POST['date'.$i];
                    $data['montant_verse'] = intval($_POST['montant_verse'.$i]);
                    $data['montant_collect'] = intval($_POST['montant_collect'.$i]);
                    $data['manquant'] = intval($_POST['montant_collect'.$i]-$_POST['montant_verse'.$i]);
                    $data['date_creation'] = date("Y-m-d H:i:s");
                    $insertEtat = $this->collecteurModel->insertVersement($data) ;
                    $updateEtat = $this->collecteurModel->updateTransaction($id,$data['date_versement']) ;

                }

                $updateCollecteur = $this->collecteurModel->resetCaisseCollecteur($id) ;

                if(($insertEtat > 0) && ($updateEtat > 0) && ($updateCollecteur > 0)){
                    $this->connexion->commit() ;
                    $insert = 1 ;
                }

                if($insert>0){
                    $insert = 1 ;
                    $this->utils->log_journal('Ajout Versement', 'Versement:'.$id, 'succes', 1, $user_creation);
                }
                else{
                    $insert = -1 ;
                    $this->utils->log_journal('Ajout Versement', 'Versement:'.$id, 'echec', 1, $user_creation);
                }
                $this->rediriger('tontine1','detailVersement/'.base64_encode($insertEtat).'/'.base64_encode($insert));
            }catch(Exception $e){
                $this->getConnexion()->rollBack() ;
                //var_dump($e->getMessage()); exit ;
                $this->rediriger('tontine','detailCollect/'.base64_encode($insert));
            }

        }else{
            $insert=5 ;
            $this->rediriger('tontine','detailCollect/'.base64_encode($id).'/'.base64_encode($insert));
        }





    }

    /**
     * @param $id
     */
    public function collectEncours($id){

        //var_dump($this->collecteurModel->getCollectEncours()); exit;


        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        try{
            $data['collect'] = $this->collecteurModel->getCollectEncours1();
        }catch (Exception $e){
           $e->getMessage();
        }

       // var_dump($data['collect']); exit;
       // error_reporting(E_ALL);exit;
        $params = array('view' => 'tontine/collect-encours');
        $this->view($params,$data);

    }


    public function detailCollect($id){

        $valeur = ($id!= null) ? $id[0] : null ;


        $dates = $this->collecteurModel->getDateCollectByIdByTontine(base64_decode($valeur));
        $data['collecteur']= $this->collecteurModel->getCollecteurById(base64_decode($valeur));
        foreach ($dates as $item => $date){
            $data['tontines'.$date['date']]= $this->collecteurModel->getCollectByIdByTontine(base64_decode($valeur),$date['date']);

        }
       // var_dump($data['tontines']);exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['collects']= $this->collecteurModel->getCollectById(base64_decode($valeur));

        $alert = '' ;
        $type_alert='';
        if (count($id) > 1 ){
            if (base64_decode($id[1]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_update'];
            }
            if (base64_decode($id[1]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_update'];
            }
            if (base64_decode($id[1]) == 5) {
                $type_alert = 'warning';
                $alert = $data['lang']['message_error_non_versement'];
            }

        }
        $params = array('view' => 'tontine/collect-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

    }

    public function processingCollectEncours(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailCollect/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->collecteurModel, "getCollectEncours", $param);
    }


    /*Débuts
      Gestion des affectations de matériel*/
    public function insertAffectation(){

        $date = strtotime($_POST['datedeb']);
        $date_debut = date('y-m-d:H:i:s', $date);
        unset($_POST['valider']);
        unset($_POST['datedeb']);
        $user_creation = $this->userConnecter->rowid;
        $data = $this->utils->securite_xss_array($_POST);
        $data['date_debut'] = $date_debut ;
        $data['user_creation'] = $user_creation ;
        $insert = $this->equipementModel->insertAffectation($data);

        $idmateriel = $_POST['fk_materiel'] ;
        $materiel= $this->equipementModel->getEquipementById($idmateriel);
        if ($materiel['uiid']!= null){
            $resultat= $this->equipementModel->updateCollecteur($_POST['fk_collecteur'],$materiel['uiid']);
        }
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Ajout Affectation', 'Affectation:'.$_POST['fk_materiel'], 'succes', 1, $user_creation);
        }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout Affectation', 'Affectation:'.$_POST['fk_materiel'], 'echec', 1, $user_creation);
        }

        $this->rediriger('tontine','affectationsEncours/'.base64_encode($insert));


    }

    public function  historiqueAffectation(){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'tontine/affectation-historique');
        $this->view($params,$data);
    }

    public function affectationsEncours($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['collecteurs'] = $this->collecteurModel->getCollecteurs();
        $data['type']= $this->equipementModel->getTypes();
        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_aff'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_aff'];
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
                $alert = $data['lang']['message_activer_offre'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
        }
        $params = array('view' => 'tontine/affectation-encours','alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

    }

    public function getEquipementByType(){
        $id = $this->utils->securite_xss($_POST['type']);
        $data = $this->equipementModel->getEquipementByType($id);
        echo json_encode($data)  ;
    }


    public function getOffreById(){
        $id = $this->utils->securite_xss($_POST['offre']);
        $data = $this->offreModel->getOffreByIdJson($id);
        echo json_encode($data)  ;
    }

    public function getTontineById(){
        $id = $this->utils->securite_xss($_POST['tontine']);
        $data = $this->tontineModel->getTontineById($id);
        echo json_encode($data)  ;
    }


    public function estAffecter(){
        $type = $this->utils->securite_xss($_POST['type']);
        $collecteur = $this->utils->securite_xss($_POST['collecteur']);
        $data = $this->equipementModel->isTypeaffecte($type,$collecteur);
        echo json_encode($data)  ;
    }

    public function hasUiid(){
        $type = $this->utils->securite_xss($_POST['type']);
        $data = $this->equipementModel->hasUiid($type);

        echo json_encode(intval($data))  ;
    }

    public function desaffectaterAffectation(){
        $id = $this->utils->securite_xss($_POST['id']);
        unset($_POST['id']);
        unset($_POST['reset']);

        $date_fin = date("Y-m-d H:i:s");     ;
        $_POST['date_fin'] = $date_fin;

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->equipementModel->disaffecter($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Desactivation Affectation', 'Affectation:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Desactivation Affectation', 'Affectation:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine','affectationsEncours/'.base64_encode($insert));

    }



    public function updateAffectation(){

        $id = intval($this->utils->securite_xss($_POST['rowid']));
        $_POST['fk_materiel'] = intval($this->utils->securite_xss($_POST['fk_materiel']));
        $_POST['fk_collecteur'] = intval($this->utils->securite_xss($_POST['fk_collecteur']));
        $_POST['date_debut'] = date('y-m-d:H:i:s',strtotime($_POST['datedeb']));

        unset($_POST['datedeb']);
        unset($_POST['update']);
        unset($_POST['rowid']);
        //unset($_POST['type']);

        $user_modification = $this->userConnecter->rowid;

        $insert = $this->equipementModel->updateAffectation($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification Affectation', 'Affectation:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification Affectation', 'Affectation:'.$id, 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine','detailAffectation/'.base64_encode($id).'/'.base64_encode($insert));

    }


    public function detailAffectation($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['affectation']= $this->equipementModel->getAffectationById(base64_decode($id[0]));
        //var_dump($data['affectation']); die();
        $alert = '' ;
        $type_alert='';
        $type = $data['affectation']['typeid'];
        $data['collecteurs'] = $this->collecteurModel->getCollecteurs();
        $data['type']= $this->equipementModel->getTypes();
        $data['materiel_type']= $this->equipementModel->getEquipementByType($type);
        if ($id!= null){
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

        $params = array('view' => 'tontine/affectation-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

     }



    public function processingAffectationEncours(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailAffectation/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->equipementModel, "affectationEncours", $param);
    }


    public function processingAffectationHistorique(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailAffectation/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->equipementModel, "affectationHistorique", $param);
    }



    /*Fin
      Gestion des affectations de matériel*/


    /*Débuts
    Gestion des offres*/
    public function offres($id){
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $alert = '' ;
        $type_alert='';
        if ($id!= null){
            if(base64_decode($id[0])==1){
                $type_alert='success';
                $alert=$data['lang']['message_success_add_offre'];

            }
            if(base64_decode($id[0])==-1){
                $type_alert='error';
                $alert= $data['lang']['message_error_add_offre'];
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
                $alert=$data['lang']['message_activer_offre'];
            }
            if(base64_decode($id[0])==-4){
                $type_alert='error';
                $alert= $data['lang']['message_error_activer'];
            }
        }



        $params = array('view' => 'tontine/offre','alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

    }

    public function inserOffre(){
        unset($_POST['valider']);
        $user_creation = $this->userConnecter->rowid;
        $data = $this->utils->securite_xss_array($_POST);
        $insert = $this->offreModel->insert($data);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Ajout Offre', 'Offre:'.$_POST['libelle'], 'succes', 1, $user_creation);
        }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout Offre', 'Offre:'.$_POST['libelle'], 'echec', 1, $user_creation);
        }

        $this->rediriger('tontine','offres/'.base64_encode($insert));


    }

    public function detailOffre($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offre']= $this->offreModel->getOffreById(base64_decode($id[0]));

        $alert = '' ;
        $type_alert='';
        //echo 'LLL'.var_dump($id); exit;
        if ($id!= null) {
            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_update'];
            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_update'];
            }
        }
        $params = array('view' => 'tontine/offre-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }

    /**
     * DESACTIVATION Equipement
     */
    public function activeOffre(){
        $id = $this->utils->securite_xss($_POST['idoffre']);
        unset($_POST['idoffre']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->offreModel->activeOffre($id);
        if($insert>0){
            $insert = 4 ;
            $this->utils->log_journal('Desactivation Offre', 'Offre:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -4 ;
            $this->utils->log_journal('Desactivation Offre', 'Offre:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine','offres/'.base64_encode($insert));

    }

    public function desactiverOffre(){
        $id = $this->utils->securite_xss($_POST['idoffre']);
        unset($_POST['idoffre']);
        unset($_POST['reset']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->offreModel->disableOffre($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Desactivation Offre', 'Offre:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Desactivation Offre', 'Offre:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine','offres/'.base64_encode($insert));

    }


    public function updateOffre(){
        $id = $this->utils->securite_xss($_POST['idoffre']);
        $_POST['libelle'] = $this->utils->securite_xss($_POST['libelle']);
        $_POST['duree'] = $this->utils->securite_xss($_POST['duree']);
        $_POST['versement'] = $this->utils->securite_xss($_POST['versement']);
        $_POST['frais'] = $this->utils->securite_xss($_POST['frais']);
        $_POST['cagnotte'] = $this->utils->securite_xss($_POST['cagnotte']);
        unset($_POST['idoffre']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->offreModel->updateOffre($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification Offre', 'Offre:'.$_POST['libelle'], 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification Offre', 'Offre:'.$_POST['libelle'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine','detailOffre/'.base64_encode($id).'/'.base64_encode($insert));

    }




    public function processingOffre(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailOffre/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->offreModel, "allOffre", $param);
    }




    /*Débuts
    Gestion des équipements*/
    public function equipements($id){


        $data['type']= $this->equipementModel->getTypes();
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {

            if (base64_decode($id[0]) == 1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_equipement'];

            }
            if (base64_decode($id[0]) == -1) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_equipement'];
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
                $alert = $data['lang']['message_activer_equipement'];
            }
            if (base64_decode($id[0]) == -4) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_activer'];
            }
            if (base64_decode($id[0]) == -7) {
                $type_alert = 'error';
                $alert = $data['lang']['ref_exit'];
            }

        }
        $params = array('view' => 'tontine/equipement','alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);

    }

    /******* Action verifier uuid ****/
    public function verifUuid()
    {
        $verif = $this->equipementModel->verifUuid($this->utils->securite_xss($_POST['uiid']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier reference ****/
    public function verifReference()
    {
        $verif = $this->equipementModel->verifReference($this->utils->securite_xss($_POST['reference']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    public function inserEquipement(){
        unset($_POST['valider']);
        $user_creation = $this->userConnecter->rowid;
        $data = $this->utils->securite_xss_array($_POST);

        $verif = -1 ;
        $uuid = $data['uiid'];
        if ($uuid != null)
             $verif = $this->equipementModel->verifUuid($uuid);


        if ($verif == -1){

            $insert = $this->equipementModel->insert($data);
           // echo 'FFF'.$insert; exit;
            if($insert>0){
                $insert = 1 ;
                $this->utils->log_journal('Ajout Materiel', 'Materiel:'.$_POST['libelle'].' Type Profil:'.$_POST['type'], 'succes', 1, $user_creation);
            }
            else{
                $insert = -1 ;
                $this->utils->log_journal('Ajout Materiel', 'Materiel:'.$_POST['libelle'].' Type Profil:'.$_POST['type'], 'echec', 1, $user_creation);
            }

            //echo 'dfg'.$insert ; exit;
            $this->rediriger('tontine','equipements/'.base64_encode($insert));

        }else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout Materiel', 'Materiel:'.$_POST['libelle'].' Type Profil:'.$_POST['type'], 'echec', 1, $user_creation);
            $this->rediriger('tontine','equipements/'.base64_encode($insert));
        }


       /* $insert = $this->equipementModel->insert($data);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Ajout Materiel', 'Materiel:'.$_POST['libelle'].' Type Profil:'.$_POST['type'], 'succes', 1, $user_creation);
        }
        else{
            $insert = -1 ;
            $this->utils->log_journal('Ajout Materiel', 'Materiel:'.$_POST['libelle'].' Type Profil:'.$_POST['type'], 'echec', 1, $user_creation);
        }

        //echo 'dfg'.$insert ; exit;
        $this->rediriger('tontine','equipements/'.base64_encode($insert));*/


    }


    /**
     * DESACTIVATION Equipement
     */
    public function desactiverEquipement(){
        $id = $this->utils->securite_xss($_POST['idequipement']);
        unset($_POST['idequipement']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->equipementModel->disableEquipement($id);
        if($insert>0){
            $insert = 2 ;
            $this->utils->log_journal('Desactivation Equipement', 'Equipement:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -2 ;
            $this->utils->log_journal('Desactivation Equipement', 'Equipement:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine','equipements/'.base64_encode($insert));

    }

    /**
     * DESACTIVATION Equipement
     */
    public function activeEquipement(){
        $id = $this->utils->securite_xss($_POST['idequipement']);
        unset($_POST['idequipement']);
        $user_modification = $this->userConnecter->rowid;
        $insert = $this->equipementModel->activeEquipement($id);
        if($insert>0){
            $insert = 4 ;
            $this->utils->log_journal('Desactivation Equipement', 'Equipement:'.$id, 'succes', 1, $user_modification);
        }else
        {
            $insert = -4 ;
            $this->utils->log_journal('Desactivation Equipement', 'Equipement:'.$id, 'echec', 1, $user_modification);
        }
        $this->rediriger('tontine','equipements/'.base64_encode($insert));

    }

    public function detailEquipement($id){

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['type']= $this->equipementModel->getTypes();
        $data['equipement']= $this->equipementModel->getEquipementById(base64_decode($id[0]));
        //echo '<pre>';var_dump($data['equipement']); die();

        $alert = '' ;
        $type_alert='';
        if ($id!= null) {
            if (isset($id[1])){
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

        $params = array('view' => 'tontine/equipement-detail', 'alert'=>$alert, 'type-alert'=>$type_alert );
        $this->view($params,$data);
    }


    public function updateEquipement(){
        $id = $this->utils->securite_xss($_POST['idequipement']);
        $_POST['type'] = $this->utils->securite_xss($_POST['type']);
        $_POST['reference'] = $this->utils->securite_xss($_POST['reference']);
        $_POST['libelle'] = $this->utils->securite_xss($_POST['libelle']);
        unset($_POST['idequipement']);
        unset($_POST['update']);

        $user_modification = $this->userConnecter->rowid;
        $insert = $this->equipementModel->updateEquipement($id);
        if($insert>0){
            $insert = 1 ;
            $this->utils->log_journal('Modification Equipement', 'Equipement:'.$_POST['libelle'].' Type Equipement:'.$_POST['type'], 'succes', 1, $user_modification);
        }else
        {
            $insert=-1 ;
            $this->utils->log_journal('Modification Equipement', 'Equipement:'.$_POST['libelle'].' Type Equipement:'.$_POST['type'], 'echec', 1, $user_modification);
        }

        $this->rediriger('tontine','detailEquipement/'.base64_encode($id).'/'.base64_encode($insert));

    }


    public function processingEquipement(){
        $param = [
            "button"=>[
                [ROOT."tontine/detailEquipement/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processing($this->equipementModel, "allEquipement", $param);
    }

    /*Fin
        Gestion des équipements*/


    /******************************************************************************** /
    /****************************** CLIENTS SOUSCRITS A UNE OFFRE *******************************/
    /**************************************** **************************************/

    public function processingClientsSouscrits($id){

        $param = [
            "button"=>[
                [ROOT."#", "fa fa-search"]
            ],
            "args"=>[$id[0]],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];

        $this->processingClients($this->clientModel, "clientSouscrit", $param);
    }

    public function processingClients($model, $method , $param = [])
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

            $arg = (isset($item['solde'])) ? 'solde' : ((isset($item['stock'])) ? 'stock' : false);
            if($arg != false) $item[$arg] = $this->utils->number_format($item[$arg]);
            if(isset($item['numero'])) $item['numero'] = $this->utils->truncate_carte($item['numero']);
            if(isset($item['telephone'])) $item['telephone'] ;
            $arg = (isset($item['etat'])) ? 'etat' : ((isset($item['statut'])) ? 'statut' : false);
            if($arg != false) $item[$arg] = ($item[$arg] == 1) ? '<span class="text-success">'.$lang['activ'].'</span>' : '<span class="text-danger">'.$lang['desactiv'].'</span>';
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



}