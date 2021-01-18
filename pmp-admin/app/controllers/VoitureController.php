<?php

/**
 * Created by IntelliJ IDEA.
 * Voiture: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class VoitureController extends \app\core\BaseController
{
    private $vmodel;


    public function __construct()
    {
        parent::__construct();
        $this->vmodel = $this->model('VoitureModel');

        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste Voiture*********/
    public function liste()
    {

        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->allVoiture();
        $params = array('view' => 'bills/voiture');
        $this->view($params,$data);
    }

    /**
     * LISTE voiture
     */
    public function processingVoiture(){
        $param = [
            "button"=>[
                [ROOT."voiture/detailVoiture/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
       // var_dump($param);exit;
        $this->processing($this->vmodel, "allVoiture__", $param);
    }


    /*************Insert Voiture**************/

    public function inserVoiture()
    {

        $marque = $this->utils->securite_xss($_POST['marque']);
        $modele = $this->utils->securite_xss($_POST['modele']);
        $matricule = $this->utils->securite_xss($_POST['matricule']);
        $nb_place = $this->utils->securite_xss($_POST['nb_place']);
        $user_creation= $this->userConnecter->rowid;

        $insert = $this->vmodel->insertVoiture($marque, $modele, $matricule, $nb_place, $user_creation);

            if($insert==1)
            {

                $this->utils->log_journal('Ajout Voiture', 'Label:'.$marque.' modele:'.$modele.' nb place:'.$nb_place, 'succes', 10, $user_creation);
                $this->rediriger('voiture','validationInsert/'.base64_encode('ok'));
            }
            else
            {
                $this->utils->log_journal('Ajout Voiture', 'Label:'.$marque.' modele:'.$modele.' nb place:'.$nb_place, 'echec', 10, $user_creation);
                $this->rediriger('voiture','validationInsert/'.base64_encode('nok'));
            }
        }
       


    
    /***********Validation Insert Voiture**********/
    public function validationInsert($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->allVoiture();
        
        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/voiture', 'title' =>'Liste Voiture', 'alert'=>$data['lang']['message_success_add_voiture'], 'type-alert'=>'alert-success');
        }
        else{
            $params = array('view' =>'bills/voiture', 'title' =>'Liste Voiture', 'alert'=>$data['lang']['message_error_add_voiture'], 'type-alert'=>'alert-danger');
        }
       
        $this->view($params,$data);
    }

    /*********detailVoiture********/
    public function detailVoiture($id)
    {
       // var_dump(base64_decode($id[0]));exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->getVoitureByIdString(base64_decode($id[0]));
        //var_dump($data['voiture']);exit;
        $params = array('view' => 'bills/voiture-detail');
        $this->view($params,$data);
    }



   
    /*************update Voiture**************/
    public function updateVoiture()
    {
        $modele = $this->utils->securite_xss($_POST['modele']);
        $marque = $this->utils->securite_xss($_POST['marque']);
        $matricule = $this->utils->securite_xss($_POST['matricule']);
        $nb_place = $this->utils->securite_xss($_POST['nb_place']);



        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        //var_dump($modele." ".$marque." ".$matricule." ".$user_modification." ".$rowid);exit;

        $update = $this->vmodel->updateVoiture($marque, $modele, $matricule, $nb_place ,$user_modification,$rowid);
        //var_dump($update);exit;
        if($update==1)
        {
            $this->utils->log_journal('Modification Voiture', 'Label:'.$marque.' modele:'.$modele.' nb place:'.$nb_place, 'succés', 10, $user_modification);
            $this->rediriger('voiture','validationUpdate/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Voiture', 'Label:'.$marque.' modele:'.$modele.' nb place:'.$nb_place, 'echec', 10, $user_modification);
            $this->rediriger('voiture','validationUpdate/'.base64_encode('nok'));
        }
    }


  

    /***********Validation Update Voiture**********/
    public function validationUpdate($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->allVoiture();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_success_update_voiture'],'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_error_update_voiture'],'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Desactiver Voiture**************/
    public function desactiverVoiture()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $update = $this->vmodel->desactiverVoiture($rowid);
        if($update==2)
        {
            $this->utils->log_journal('Désactivation Voiture', 'Idvoiture desactivé:'.$rowid, 'succès', 10, $user_modification);
            $this->rediriger('voiture','validationdesactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Voiture', 'Idvoiture desactivé:'.$rowid, 'echec', 10, $user_modification);
            $this->rediriger('voiture','validationdesactiver/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver Voiture**********/
    public function validationdesactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->allVoiture();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_success_delete'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_error_delete'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Activer Voiture**************/
    public function activerVoiture()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $update = $this->vmodel->activerVoiture($rowid);
        if($update==4)
        {
            $this->utils->log_journal('Activation Voiture', 'Idvoiture activé:'.$rowid, 'succès', 10, $user_modification);
            $this->rediriger('voiture','validationactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Activation Voiture', 'Idvoiture activé:'.$rowid, 'echec', 10, $user_modification);
            $this->rediriger('voiture','validationactiver/'.base64_encode('nok'));
        }
    }

  
    /***********Validation Activer Voiture**********/
    public function validationactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['voiture']= $this->vmodel->allVoiture();
     
        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_activer_voiture'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/voiture','title' => 'Liste Voitures','alert'=>$data['lang']['message_error_activer_voiture'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }



   

}