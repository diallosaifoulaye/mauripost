<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class AbonnementController extends \app\core\BaseController
{
    private $abonneModel;


    public function __construct()
    {
        parent::__construct();
        $this->abonneModel = $this->model('AbonneModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];

    }

    /*********Liste User*********/
    public function liste()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'bills/listeAbonne');
        $this->view($params, $data);
    }

    public function processingAb()
    {

        $param = [
            "button" => [
                [ROOT . "abonnement/detailAbonne/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->abonneModel, "allAboone", $param);
    }

    public function inserAbonne(){

        $insert = $this->abonneModel->insertBp($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Ajout abonné postale', 'téléphone:' . $_POST['tel'] , 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('abonnement', 'liste');
        } else {
            $this->utils->log_journal('Ajout abonnement postale', 'téléphone:' . $_POST['tel'] , 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('abonnement', 'liste');
        }
    }

    public function detailAbonne($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['abonne'] = $this->abonneModel->getAbooneById(base64_decode($id[0]));
        $params = array('view' => 'bills/abonne-detail');
        $this->view($params, $data);
    }

    public function updateAbonne()
    {
        $insert = $this->abonneModel->updateAbonne($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Modification abonné postale', 'id:' . $_POST['rowid'], 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('abonnement', 'detailAbonne/' . $_POST['rowid']);
        } else {
            $this->utils->log_journal('Modification abonné postale', 'id:' . $_POST['rowid'], 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('abonnement', 'detailAbonne/' . $_POST['rowid']);
        }
    }

    /******* Action verifier email ****/
    public function verifEmail()
    {
        $verif = $this->abonneModel->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

}