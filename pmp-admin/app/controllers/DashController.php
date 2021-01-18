<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class DashController extends \app\core\BaseController
{


    public  $dashModel;


    public function __construct()
    {
        parent::__construct();


        $this->messageModel = $this->model('MessageModel');
        $this->equipementModel = $this->model('EquipementModel');
        $this->offreModel = $this->model('OffreModel','t_offres');
        $this->collecteurModel = $this->model('CollecteurModel','t_collecteur');
        $this->clientModel = $this->model('ClientModel','t_client');
        $this->tontineModel = $this->model('TontineModel','t_tontine');

        $this->dashModel = $this->model('DashModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste User*********/
    public function index()
    {
        //A changer accés modéle dashbord
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 9) );


        $data['montantCollecte'] = $this->collecteurModel->getCollectToDay();
        $data['montantVerse'] = $this->collecteurModel->getVersementToDay();

        $data['clients'] = $this->clientModel->nbreClientsActifs();
        $data['tontines'] = $this->tontineModel->nbreTontinesEnCours();
        $data['retraits'] = $this->tontineModel->nbreDeRetraits();
        $data['collectEncours'] = $this->collecteurModel->nbreCollecteursEnExercice();
        $data['equipements'] = $this->equipementModel->nbreEquipements();
        $data['offres'] = $this->offreModel->allOffreCount();

        $data['nbreTransactionMensuel'] = $this->dashModel->nbreTransactionMensuel();

        $data['collecteurs'] = $this->collecteurModel->allCollecteurs();
        $data['les_clients'] = $this->clientModel->allClients();
        //echo '<pre>'; var_dump($data['nbreTransactionMensuel']); die();

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'dash/accueil');
        $this->view($params,$data);
    }

    public function filteReporting(){

        // Nettoyage des données
        $this->utils->securite_xss_array($_POST);

        $datedeb = $_POST['datedeb'];
        $datefin = $_POST['datefin'];
        $collecteur = $_POST['collecteur'];
        $client = $_POST['client'];

        $data['datedeb'] = $datedeb ;
        $data['datefin'] = $datefin ;
        $data['collecteur'] = $collecteur ;
        $data['client'] = $client ;

        $data['montantCollecte'] = $this->collecteurModel->getCollectToDay();
        $data['montantVerse'] = $this->collecteurModel->getVersementToDay();

        $data['clients'] = $this->clientModel->nbreClientsActifs();
        $data['tontines'] = $this->tontineModel->nbreTontinesEnCours();
        $data['retraits'] = $this->tontineModel->nbreDeRetraits();
        $data['collectEncours'] = $this->collecteurModel->nbreCollecteursEnExercice();
        $data['equipements'] = $this->equipementModel->nbreEquipements();
        $data['offres'] = $this->offreModel->allOffreCount();

        $data['nbreTransactionMensuel'] = $this->dashModel->nbreTransactionMensuel($datedeb,$datefin,$client,$collecteur);

        $data['collecteurs'] = $this->collecteurModel->allCollecteurs();
        $data['les_clients'] = $this->clientModel->allClients();
        //var_dump($data['collecteurs']); die();

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'dash/accueil');
        $this->view($params,$data);

    }





}