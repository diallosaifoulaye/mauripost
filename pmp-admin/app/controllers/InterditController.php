<?php

class InterditController extends \app\core\BaseController
{
    public $interdit;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();
        $this->interdit = $this->model('InterditModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    public function index()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(270,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'interdit/liste');
        $this->view($params, $data);
    }


    public function processingListInterdit()
    {
        $param = [
            "button" => [[ROOT . "interdit/alertDetails/", "fa fa-search"]],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->interdit, "getHistoriqueListInterdit", $param);
    }

    public function alerte_instance($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(271,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($id[0]) == -1) {
            $type_alert = 'success';
            $alert = $data['lang']['bp_gest9'];
        }
        if (base64_decode($id[0]) == -2) {
            $type_alert = 'error';
            $alert = $data['lang']['bp_gest10'];
        }
        $params = array('view' => 'interdit/instance', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingListInterditInstance()
    {
        $param = [
            "button" => [
                [ROOT . "interdit/alertDetails/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->interdit, "getHistoriqueListInterditInstance", $param);
    }

    public function updateAlerteInstance($param)
    {

        $result = $this->interdit->updateAlerteInstanceById([base64_decode($param[1]), $this->userConnecter->rowid, base64_decode($param[0])]);

        if ($result !== false) $this->rediriger('interdit', 'alerte_instance/' . base64_encode(-1));
        else $this->rediriger('interdit', 'alerte_instance/' . base64_encode(-2));
    }

    public function alerte_confirme()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(272,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'interdit/confirme');
        $this->view($params, $data);
    }

    public function processingListInterditConfirme()
    {
        $param = [
            "button" => [
                [ROOT . "interdit/alertDetails/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->interdit, "getHistoriqueListInterditConfirme", $param);
    }

    public function alerte_levee()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(273,$this->userConnecter->profil) );

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'interdit/levee');
        $this->view($params, $data);
    }

    public function processingListInterditLevee()
    {
        $param = [
            "button" => [
                [ROOT . "interdit/alertDetails/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->interdit, "getHistoriqueListInterditLevee", $param);
    }

    public function alertDetails($id)
    {
        $data['alert'] = 'detail_alerte_instance';
        $data['infoalert'] = $this->interdit->getInfosalertByID(base64_decode($id[0]));

        $data['alert'] = ($data['infoalert'][0]->confirmer == 0) ? 'detail_alerte_levee' : (($data['infoalert'][0]->confirmer == 1) ? 'detail_alerte_confirme' : 'detail_alerte_instance');

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'interdit/alertDetails');
        $this->view($params, $data);
    }

    public function parametrage()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(274,$this->userConnecter->profil) );

        $data['parametrage'] = $this->interdit->getParametrageInterdits();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'interdit/parametrage');
        $this->view($params, $data);
    }

    public function updateParametrage()
    {
        $param['prenom'] = $this->utils->securite_xss($_POST['prenom']);
        $param['prenom1'] = $this->utils->securite_xss($_POST['prenom1']);
        $param['nom'] = $this->utils->securite_xss($_POST['nom']);
        $param['date_nais'] = $this->utils->securite_xss($_POST['date_nais']);
        $param = array_map(function ($one){ return $one != 1 ? 0 : 1; }, $param);

        $this->interdit->updateParametrageInterdits($param);
        $this->rediriger('interdit', 'parametrage');
    }

}