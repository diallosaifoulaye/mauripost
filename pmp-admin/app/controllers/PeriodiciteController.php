<?php

/**
 * Created by IntelliJ IDEA.
 * Periodicite: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class PeriodiciteController extends \app\core\BaseController
{
    private $pmodel;


    public function __construct()
    {
        parent::__construct();
        $this->pmodel = $this->model('PeriodiciteModel');

        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste Periodicite*********/
    public function liste()
    {


        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->allPeriodicite();
        $params = array('view' => 'bills/periodicite');
        $this->view($params,$data);
    }

    /**
     * LISTE periodicite
     */
    public function processingPeriodicite(){
        $param = [
            "button"=>[
                [ROOT."periodicite/detailPeriodicite/", "fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
       // var_dump($param);exit;
        $this->processing($this->pmodel, "allPeriodicite__", $param);
    }


    /*************Insert Periodicite**************/

    public function inserPeriodicite()
    {

        $label = $this->utils->securite_xss($_POST['label']);
        $nombre_mois = $this->utils->securite_xss($_POST['nombre_mois']);
        $user_creation= $this->userConnecter->rowid;

        $insert = $this->pmodel->insertPeriodicite($label, $nombre_mois);

            if($insert==1)
            {

                $this->utils->log_journal('Ajout Periodicite', 'Label:'.$label.' Nombre mois:'.$nombre_mois, 'succes', 10, $user_creation);
                $this->rediriger('periodicite','validationInsert/'.base64_encode('ok'));
            }
            else
            {
                $this->utils->log_journal('Ajout Periodicite', 'Label:'.$label.' Nombre mois:'.$nombre_mois, 'echec', 10, $user_creation);
                $this->rediriger('periodicite','validationInsert/'.base64_encode('nok'));
            }
        }
       


    
    /***********Validation Insert Periodicite**********/
    public function validationInsert($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->allPeriodicite();
        
        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/periodicite', 'title' =>'Liste Periodicite', 'alert'=>$data['lang']['message_success_add_period'], 'type-alert'=>'alert-success');
        }
        else{
            $params = array('view' =>'bills/periodicite', 'title' =>'Liste Periodicite', 'alert'=>$data['lang']['message_error_add_period'], 'type-alert'=>'alert-danger');
        }
       
        $this->view($params,$data);
    }

    /*********detailPeriodicite********/
    public function detailPeriodicite($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->getPeriodiciteByIdString(base64_decode($id[0]));
        //var_dump($data['periodicite']);exit;
        $params = array('view' => 'bills/periodicite-detail');
        $this->view($params,$data);
    }



   
    /*************update Periodicite**************/
    public function updatePeriodicite()
    {
        $nombre_mois = $this->utils->securite_xss($_POST['nombre_mois']);
        $label = $this->utils->securite_xss($_POST['label']);
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);

        $update = $this->pmodel->updatePeriodicite($label,$nombre_mois,$rowid);
        if($update==1)
        {
            $this->utils->log_journal('Modification Periodicite', 'Label:'.$label.' Nombre mois:'.$nombre_mois, 'succés', 10, $user_modification);
            $this->rediriger('periodicite','validationUpdate/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Periodicite', 'Label:'.$label.' Nombre mois:'.$nombre_mois, 'echec', 10, $user_modification);
            $this->rediriger('periodicite','validationUpdate/'.base64_encode('nok'));
        }
    }


  

    /***********Validation Update Periodicite**********/
    public function validationUpdate($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->allPeriodicite();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_success_update_period'],'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_error_update_period'],'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Desactiver Periodicite**************/
    public function desactiverPeriodicite()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $update = $this->pmodel->desactiverPeriodicite($rowid);
        if($update==2)
        {
            $this->utils->log_journal('Désactivation Periodicite', 'Idperiodicite desactivé:'.$rowid, 'succès', 10, $user_modification);
            $this->rediriger('periodicite','validationdesactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Periodicite', 'Idperiodicite desactivé:'.$rowid, 'echec', 10, $user_modification);
            $this->rediriger('periodicite','validationdesactiver/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver Periodicite**********/
    public function validationdesactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->allPeriodicite();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_success_delete'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_error_delete'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Activer Periodicite**************/
    public function activerPeriodicite()
    {
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $update = $this->pmodel->activerPeriodicite($rowid);
        if($update==4)
        {
            $this->utils->log_journal('Activation Periodicite', 'Idperiodicite activé:'.$rowid, 'succès', 10, $user_modification);
            $this->rediriger('periodicite','validationactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Activation Periodicite', 'Idperiodicite activé:'.$rowid, 'echec', 10, $user_modification);
            $this->rediriger('periodicite','validationactiver/'.base64_encode('nok'));
        }
    }

  

    /***********Validation Activer Periodicite**********/
    public function validationactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['periodicite']= $this->pmodel->allPeriodicite();
     
        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_activer_period'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'bills/periodicite','title' => 'Liste Periodicites','alert'=>$data['lang']['message_error_activer_period'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }



   

}