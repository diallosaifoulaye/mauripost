<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */
class BillsController extends \app\core\BaseController
{
    private $bpModel;
    private $agenceModel;
    private $actionModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();
        $this->bpModel = $this->model('BillsModel');
        $this->actionModel = $this->model('ActionModel');
        $this->agenceModel = $this->model('AgenceModel');


        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }


    /*********Liste User*********/
    public function index()
    {

        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offres'] = $this->bpModel->OffresPostales();
        $data['agences'] = $this->agenceModel->allAgence();
        $data['benef'] = $this->bpModel->allBenef();
        $data['period'] = $this->bpModel->allperiodicite();

        $params = array('view' => 'bills/index');
        $this->view($params, $data);
    }

    public function calculmnt()
    {
        $of=explode("_",$this->utils->securite_xss($_POST['offp']));
        $data['$mntof'] = $this->bpModel->getMntOffModel($of[0]);
        $data['$nbremois'] = $this->bpModel->getNbreMoisModel($of[1]);
        $mnt=($data['$mntof']['montant']*$data['$nbremois']['nombre_mois']);
        echo $mnt;
    }

    public function getboitepostale()
    {
        $data['bp'] = $this->bpModel->getBP($this->utils->securite_xss($_POST['benef']));
        print_r(json_encode($data['bp'])) ;
    }


    public function validationBp($return)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['offres'] = $this->bpModel->OffresPostales();
        $data['agences'] = $this->agenceModel->allAgence();

        if (base64_decode($return[0]) === 'ok') {
            $params = array('view' => 'bills/index', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest9'], 'type-alert' => 'alert-success');
        } elseif (base64_decode($return[0]) === 'nok') {
            $params = array('view' => 'bills/index', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest10'], 'type-alert' => 'alert-danger');
        } else $params = array('view' => 'bills/index');

        $this->view($params, $data);
    }

    public function processingBp()
    {
        $param = [
            "button" => [
                [ROOT . "bills/detailBp/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->bpModel, "allBp", $param);
    }

    public function processingBpNew()
    {
        $param = [
            "button" => [
                [ROOT . "bills/detailBpNew/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->bpModel, "allBpNew", $param);
    }

    public function insertBpnew()
    {
        $_POST['user_creation'] = $this->userConnecter->rowid;
        $_POST['fk_agence'] = $this->userConnecter->fk_agence;
        $data['idbp'] = $this->bpModel->getIdbpModel($_POST['boite_postale']);
        unset($_POST['fk_beneficiaire_postale']);
        unset($_POST['boite_postale']);
        $_POST['fk_boite_postale']=$data['idbp']['id'];
        $insert = $this->bpModel->insertBpNew($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Ajout abonnement postale', 'beneficiaire_postale:' . $_POST['fk_beneficiaire_postale'] . ' annee:' . $_POST['annee'] , 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('ok'));
        } else {
            $this->utils->log_journal('Ajout abonnement postale', 'beneficiaire_postale:' . $_POST['fk_beneficiaire_postale'] . ' annee:' . $_POST['annee'], 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('nok'));
        }
    }

    public function insertBp()
    {
        $_POST['user_crea'] = $this->userConnecter->rowid;
        $_POST['date_crea'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->insertBp($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->bpModel->debiter_soldeAgence($this->utils->securite_xss($_POST['montant']),$this->userConnecter->fk_agence);
            $this->bpModel->crediterCarteParam(6,$this->utils->securite_xss($_POST['montant']));
            $this->utils->log_journal('Ajout abonnement postale', 'numero:' . $_POST['numero'] . ' annee:' . $_POST['annee'] . ' offre:' . $_POST['offre'] . ' agence:' . $_POST['agence'], 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('ok'));
        } else {
            $this->utils->log_journal('Ajout abonnement postale', 'numero:' . $_POST['numero'] . ' annee:' . $_POST['annee'] . ' offre:' . $_POST['offre'] . ' agence:' . $_POST['agence'], 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('nok'));
        }
    }

    public function detailBpNew($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $data['bp'] = $this->bpModel->getBpByIdStringNew(base64_decode($id[0]));
        $data['offres'] = $this->bpModel->OffresPostales();
        $data['agences'] = $this->agenceModel->allAgence();

        $params = array('view' => 'bills/bp-detail');
        $this->view($params, $data);
    }

    public function detailBp($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['bp'] = $this->bpModel->getBpByIdString(base64_decode($id[0]));
        $data['offres'] = $this->bpModel->OffresPostales();
        $data['agences'] = $this->agenceModel->allAgence();

        $params = array('view' => 'bills/bp-detail');
        $this->view($params, $data);
    }

    public function getOffreFees()
    {
        $insert = $this->actionModel->getOffreByIdString($this->utils->securite_xss($_POST['offre']));
        echo $insert->montant;
    }

    public function checkInteredBp()
    {
        $insert = @$this->bpModel->checkInteredBp($this->utils->securite_xss_array($_POST));
        echo $insert;
    }

    public function updateBp()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');
        $_POST['fk_agence'] = $this->userConnecter->fk_agence;

        $insert = $this->bpModel->updateBp($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {
            $this->utils->log_journal('Modification abonnement postale', 'id:' . base64_decode($_POST['rowid']), 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('ok'));
        } else {
            $this->utils->log_journal('Modification abonnement postale', 'id:' . base64_decode($_POST['rowid']), 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('nok'));
        }
    }

    public function cancelBp()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->cancelBp($this->utils->securite_xss_array($_POST));
        if ($insert == 1) {

            $this->bpModel->crediter_soldeAgence($this->utils->securite_xss($_POST['mt']),$this->userConnecter->fk_agence);
            $this->bpModel->debiterCarteParam(6,$this->utils->securite_xss($_POST['mt']));
            $this->utils->log_journal('Modification abonnement postale', 'id:' . base64_decode($_POST['rowid']), 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('ok'));
        } else {
            $this->utils->log_journal('Modification abonnement postale', 'id:' . base64_decode($_POST['rowid']), 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationBp/' . base64_encode('nok'));
        }
    }

    public function printRecuReservation($id)
    {
        $data['reservation'] = $this->bpModel->getReservationByIdString(base64_decode($id[0]));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //get the HTML
        ob_start();
        $imprime = __DIR__ . '/../views/bills/recu-reservation.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__ . '/../../assets/html2pdf/html2pdf.class.php';

        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuReservation.pdf', 'I');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }





    public function printRecuAbonnementNew($id)
    {
        $data['bp'] = $this->bpModel->getBpByIdStringNew(base64_decode($id[0]));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //get the HTML
        ob_start();
        $imprime = __DIR__ . '/../views/bills/recu-abonnement.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__ . '/../../assets/html2pdf/html2pdf.class.php';

        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuAbonnementPostaleN.pdf', 'I');
        }
        catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }

    }

    public function printRecuAbonnement($id)
    {
        $data['bp'] = $this->bpModel->getBpByIdString(base64_decode($id[0]));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        //get the HTML
        ob_start();
        $imprime = __DIR__ . '/../views/bills/recu-abonnement.php';

        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        require_once __DIR__ . '/../../assets/html2pdf/html2pdf.class.php';

        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times', 8);
            $html2pdf->writeHTML($content);
            ob_end_clean();
            $html2pdf->Output('RecuAbonnementPostale.pdf', 'I');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }

    public function searchabonnement()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'bills/searchabonnement');
        $this->view($params,$data);
    }

    public function reportingAbonnement()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['datedeb'] = $this->utils->securite_xss($_POST['datedeb']);
        $data['datefin'] = $this->utils->securite_xss($_POST['datefin']);
        $params = array('view' => 'bills/reportingAbonnement');
        $this->view($params, $data);
    }

    public function reportingAbonnementExcel()
    {

        $res = $this->bpModel->allBpFilteredResp($_POST);

        $csv = "PROPRIETAIRE;BOITE POSTALE;ANNEE;OFFRE;MONTANT;AGENCE;DATE\n";
        foreach($res as $bsf)
        {
            $csv .= $bsf['owner'].';'.$bsf['numero'].';'.$bsf['annee'].';'.$bsf['offre'].";".$bsf['montant'].";".$bsf['agence'].";".$bsf['date']."\n";
        }
        $nomfichier="reportingAbonnement.csv";
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=$nomfichier");
        print($csv);
    }

    public function processingReportingBp()
    {
        $deb = $_POST['4'].$_POST['5'].$_POST['6'].$_POST['7'].$_POST['8'].$_POST['9'].$_POST['10'].$_POST['11'].$_POST['12'].$_POST['13'];
        $fin = $_POST['19'].$_POST['20'].$_POST['21'].$_POST['22'].$_POST['23'].$_POST['24'].$_POST['25'].$_POST['26'].$_POST['27'].$_POST['28'];

        $param = [
            "button" => [
                [ROOT . "bills/detailBpNew/", "fa fa-search"]
            ],
            "args" => ['deb'=>$deb, 'fin'=>$fin],
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->bpModel, "allBpFiltered", $param);
    }

    public function reservations()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['itineraires'] = $this->bpModel->itineraires();

        $params = array('view' => 'bills/reservations');

        $this->view($params, $data);
    }

    public function validationReservations($return)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['itineraires'] = $this->bpModel->itineraires();

        if (base64_decode($return[0]) === 'ok') {
            $params = array('view' => 'bills/reservations', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest9'], 'type-alert' => 'alert-success');
        } elseif (base64_decode($return[0]) === 'nok') {
            $params = array('view' => 'bills/reservations', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest10'], 'type-alert' => 'alert-danger');
        } else $params = array('view' => 'bills/reservations');

        $this->view($params, $data);
    }

    public function processingReservation()
    {
        $param = [
            "button" => [
                [ROOT . "bills/detailReservation/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->bpModel, "allReservation", $param);
    }

    public function getItineraireFees()
    {
        $insert = $this->bpModel->getItineraireByIdString(\app\core\Utils::securite_xss($_POST['itineraire']));
        echo $insert->tarif;
    }

    public function getPlace()
    {
        $insert = $this->bpModel->getPlaceByIdString(\app\core\Utils::securite_xss($_POST['place']));
        echo $insert->nb_place;
    }

    public function getItinerairePlaces()
    {
        $insert = $this->bpModel->getItineraireByIdString(\app\core\Utils::securite_xss($_POST['itineraire']));
        //var_dump($insert->place_dispo);die;
        echo $insert->place_dispo;
    }

    public function insertReservation()
    {
        $_POST['user_crea'] = $this->userConnecter->rowid;
        $_POST['date_crea'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->insertReservation(\app\core\Utils::securite_xss_array($_POST));

        if ($insert > 0) {
            $this->bpModel->crediterCarteParam(7,\app\core\Utils::securite_xss($_POST['montant']));
            $this->utils->log_journal('insertReservation', 'client:' . $_POST['client'] . ' itineraire:' . $_POST['itineraire'], 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationReservations/' . base64_encode('ok'));

        } else {
            $this->utils->log_journal('insertReservation', 'client:' . $_POST['client'] . ' itineraire:' . $_POST['itineraire'], 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationReservations/' . base64_encode('nok'));
        }
    }

    public function updateReservation()
    {
        $_POST['user_crea'] = $this->userConnecter->rowid;
        $_POST['date_crea'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->updateReservation(\app\core\Utils::securite_xss_array($_POST));

        if ($insert > 0) {
            $this->utils->log_journal('updateReservation', 'id:' . base64_decode($_POST['rowid']), 'succes', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationReservations/' . base64_encode('ok'));

        } else {
            $this->utils->log_journal('updateReservation', 'id:' . base64_decode($_POST['rowid']), 'echec', 10, $this->userConnecter->rowid);
            $this->rediriger('bills', 'validationReservations/' . base64_encode('nok'));
        }
    }

    public function detailReservation($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['reservation'] = $this->bpModel->getReservationByIdString(base64_decode($id[0]));
        $data['itineraires'] = $this->bpModel->itineraires();
        $params = array('view' => 'bills/reservation-detail');
        $this->view($params, $data);
    }


    public function itineraires()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 10));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' =>'bills/itineraires');
        $this->view($params,$data);
    }

    public function getVoitureFees(){
        $data['voitures'] = $this->bpModel->voitures($_POST);
        print_r(json_encode($data['voitures'])) ;
    }

    public function getVoitureFeesM(){
        $data['voitures'] = $this->bpModel->voituresM($_POST);
        print_r(json_encode($data['voitures'])) ;
    }

    public function validationItineraires($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'bills/itineraires', 'title' =>$data['lang']['bp_gest2'], 'alert'=>$data['lang']['bp_gest9'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'bills/itineraires', 'title' =>$data['lang']['bp_gest2'], 'alert'=>$data['lang']['bp_gest10'], 'type-alert'=>'alert-danger');
        }
        else $params = array('view' =>'bills/itineraires');

        $this->view($params,$data);
    }

    public function processingItineraires()
    {
        $param = [
            "button"=>[
                [ROOT."bills/detailsItineraire/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->bpModel,"allItineraires",$param);
    }

    public function insertItineraire()
    {
        $_POST['user_crea'] = $this->userConnecter->rowid;
        $_POST['date_crea'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->insertItineraire(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal('Ajout Itineraire', 'depart:'.$_POST['lieu_depart'].' arrivee:'.$_POST['lieu_depart'], 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Ajout Itineraire', 'depart:'.$_POST['lieu_depart'].' arrivee:'.$_POST['lieu_depart'], 'echec', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('nok'));
        }
    }

    public function detailsItineraire($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['itineraire']= $this->bpModel->getItineraireByIdString(base64_decode($id[0]));

        $params = array('view' => 'bills/itineraire-detail');
        $this->view($params,$data);
    }

    public function updateItineraire()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');

        $insert = $this->bpModel->updateItineraire(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal('updateItineraire', 'id:'.base64_decode($_POST['rowid']), 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('updateItineraire', 'id:'.base64_decode($_POST['rowid']), 'echec', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('nok'));
        }
    }

    public function desactiveItineraire()
    {
        $_POST['user_modif'] = $this->userConnecter->rowid;
        $_POST['date_modif'] = date('Y-m-d H:i:s');
        $etat = base64_decode($_POST['etat']);
        $_POST['etat'] = $etat;

        if($etat == 0) $lib = 'Desactivation'; else $lib = 'Activation';

        $insert = $this->bpModel->updateItineraireState(\app\core\Utils::securite_xss_array($_POST));
        if($insert==1)
        {
            $this->utils->log_journal($lib.' Itineraire', 'id:'.base64_decode($_POST['rowid']), 'succes', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal($lib.' Itineraire', 'id:'.base64_decode($_POST['rowid']), 'echec', 1, $this->userConnecter->rowid);
            $this->rediriger('bills','validationItineraires/'.base64_encode('nok'));
        }
    }

}