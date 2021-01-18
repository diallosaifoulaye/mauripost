<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class OffreController extends \app\core\BaseController
{
    private $offreModel;


    public function __construct()
    {
        parent::__construct();
        $this->offreModel = $this->model('OffresModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];

    }



    public function listeOffres()
    {

        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' =>'bills/offres');

        $this->view($params,$data);
    }

    public function validationListeOffres($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'bills/offres', 'title' =>$data['lang']['bp_gest2'], 'alert'=>$data['lang']['bp_gest9'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'bills/offres', 'title' =>$data['lang']['bp_gest2'], 'alert'=>$data['lang']['bp_gest10'], 'type-alert'=>'alert-danger');
        }
        else $params = array('view' =>'bills/offres');

        $this->view($params,$data);
    }

    public function processingOffres()
    {
        $param = [
            "button"=>[
                [ROOT."offre/detailsOffre/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->offreModel,"allOffres",$param);
    }

    public function insertOffre()
    {
        $_POST['user_crea'] = $this->userConnecter->rowid;
        $_POST['date_crea'] = date('Y-m-d H:i:s');

        $insert = $this->offreModel->insertOffre(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal('Ajout offre postale', 'libelle:'.$_POST['libelle'].' montant:'.$_POST['montant'], 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Ajout offre postale', 'libelle:'.$_POST['libelle'].' montant:'.$_POST['montant'], 'echec', 1, $this->userConnecter->rowider_creation);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('nok'));
        }
    }

    public function detailsOffre($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offre']= $this->offreModel->getOffreByIdString(base64_decode($id[0]));

        $params = array('view' => 'bills/offre-detail');
        $this->view($params,$data);
    }

    public function updateOffre()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');

        $insert = $this->offreModel->updateOffre(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal('Update offre postale', 'id:'.base64_decode($_POST['rowid']), 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Update offre postale', 'id:'.base64_decode($_POST['rowid']), 'echec', 1, $this->userConnecter->rowid);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('nok'));
        }
    }

    public function desactiveOffre()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');
        $etat = base64_decode($_POST['etat']);
        $_POST['etat'] = $etat;

        if($etat == 0) $lib = 'Desactivation'; else $lib = 'Activation';

        $insert = $this->offreModel->updateOffreState(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal($lib.' offre postale', 'id:'.base64_decode($_POST['rowid']), 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal($lib.' offre postale', 'id:'.base64_decode($_POST['rowid']), 'echec', 1, $this->userConnecter->rowid);
            $this->rediriger('offre','validationListeOffres/'.base64_encode('nok'));
        }
    }
}