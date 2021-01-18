<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class BoitepostaleController extends \app\core\BaseController
{
    private $boiteModel;

    public function __construct()
    {
        parent::__construct();
        $this->boiteModel = $this->model('BoiteModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];

    }

    /*********Liste User*********/
    public function liste()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['beneficiaire'] = $this->boiteModel->getBeneficiaire();
        $params = array('view' => 'bills/listeBoite');
        $this->view($params, $data);
    }

    public function processingBaffect()
    {
        $param = [
            "button" => [
                [ROOT . "boitepostale/detailBoite/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->boiteModel, "allBoite_affect", $param);
    }

    public function processingBt_no()
    {

        $param = [
            "button" => [
                [ROOT . "boitepostale/detailBoite/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->boiteModel, "allBoite_no", $param);
    }

    public function inserBoite(){

        if($_POST['fk_beneficiaire_postale']=="")
            $_POST['etat']=0;
        else
            $_POST['etat']=1;

        $insert = $this->boiteModel->insertBt($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Ajout boite postale', 'numero:' . $_POST['numero'] , 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('boite', 'liste');
        } else {
            $this->utils->log_journal('Ajout boite postale', 'numero:' . $_POST['numero'] , 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('boite', 'liste');
        }
    }

    public function detailBoite($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['boite'] = $this->boiteModel->getBoiteById(base64_decode($id[0]));
        $data['beneficiaire'] = $this->boiteModel->getBeneficiaire();
        //var_dump($data['beneficiaire']);die;
        $params = array('view' => 'bills/boite-detail');
        $this->view($params, $data);
    }

    public function updateBoite()
    {
        if($_POST['fk_beneficiaire_postale']=="")
            $_POST['etat']=0;
        else
            $_POST['etat']=1;

        $insert = $this->boiteModel->updateBoite($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Modification boite postale', 'id:' . base64_decode($_POST['rowid']), 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('boitepostale', 'detailBoite/' . $_POST['rowid']);
        } else {
            $this->utils->log_journal('Modification boite postale', 'id:' . base64_decode($_POST['rowid']), 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('boitepostale', 'detailBoite/' . $_POST['rowid']);
        }
    }

    public function affectBoite()
    {
        if($_POST['fk_beneficiaire_postale']==""){
            $_POST['etat']=0;
            $insert = $this->boiteModel->freeBoite($this->utils->securite_xss_array($_POST));
        }
        else{
            $_POST['etat']=1;
            $insert = $this->boiteModel->affectBoite($this->utils->securite_xss_array($_POST));
        }


        if ($insert == 1) {
            $this->utils->log_journal('Affectation boite postale', 'id:' . base64_decode($_POST['rowid']), 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('boitepostale', 'detailBoite/' . $_POST['rowid']);
        } else {
            $this->utils->log_journal('Affectation boite postale', 'id:' . base64_decode($_POST['rowid']), 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('boitepostale', 'detailBoite/' . $_POST['rowid']);
        }
    }

    /******* Action verifier email ****/
    public function verifNum()
    {
        $verif = $this->boiteModel->verifNum($this->utils->securite_xss($_POST['numero']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

}