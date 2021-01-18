<?php
require_once(__DIR__ . '/../../vendor/ApiGTP/ApiBanque.php');
ini_set('display_errors', 1);

class DistributeurController extends \app\core\BaseController
{
    public $distributeurModel;
    private $agenceModel;
    private $userConnecter;
    public $api_gtp;


    public function __construct()
    {
        parent::__construct();
        $this->distributeurModel = $this->model('DistributeurModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->api_gtp = new  ApiBAnque();
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }





    /*********search code distibuteur ********/
    public function searchCodeDistributeur()
    {
        //$this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(46, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/recharge-distributeur-search');
        $this->view($params, $data);
    }

    /********* rechargement  distributeur ********/
    public function rechargeDistributeur()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $fkagence = $this->userConnecter->fk_agence;
        $code =  $this->utils->securite_xss($_POST['code']);
        $data['benef'] = $this->distributeurModel->getDistributeur($code);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($fkagence);

        $params = array('view' => 'compte/recharge-distributeur');
        $this->view($params, $data);
    }

    /*********Recharge distributeur Code Validation********/
    public function rechargeDistributeutCodeValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['code'] = base64_decode($this->utils->securite_xss($_POST['code']));
        $data['rowid'] = base64_decode($this->utils->securite_xss($_POST['rowid']));
        $data['soldeAgence'] = $this->utils->securite_xss($_POST['soldeagence']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $data['fkagence'] = $this->userConnecter->fk_agence;

        if ($data['code'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '') {

            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $recup_tel = $this->utils->recup_tel($this->userConnecter->fk_agence);
            $code_recharge = $this->utils->generateCodeRechargement($data['fkagence'], $data['fkagence'], $data['montant']);

            $message = $data['lang']['mess_recharge_espece1'] . $code_recharge . $data['lang']['mess_recharge_espece2'] . $this->utils->number_format($data['montant']) . $data['lang']['currency'].' '.$data['lang']['code_dist'].':'.$data['code'];
            @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);

            if ($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '') {

                @$this->utils->envoiCodeRechargementDist($recup_mail, $data['lang']['chef_agence'], $code_recharge, $data['montant'], $data['code'], $data['lang']);
            }

        }

        $params = array('view' => 'compte/recharge-distributeur-validation');
        $this->view($params, $data);
    }
    public function rechargeDistributeurCodeValidation2()
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['code'] = $this->utils->securite_xss($_POST['code']);
        $data['rowid'] = $this->utils->securite_xss($_POST['rowid']);
        $data['soldeAgence'] = $this->utils->securite_xss($_POST['soldeagence']);
        $data['montant'] = $this->utils->securite_xss($_POST['montant']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais']);
        $data['fkagence'] = $this->userConnecter->fk_agence;
        if ($data['code'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '') {

            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $recup_tel = $this->utils->recup_tel($this->userConnecter->fk_agence);
            $code_recharge = $this->utils->generateCodeRechargement($data['fkagence'], $data['fkagence'], $data['montant']);

            $message = $data['lang']['mess_recharge_espece1'] . $code_recharge . $data['lang']['mess_recharge_espece2'] . $this->utils->number_format($data['montant']) . $data['lang']['currency'].' '.$data['lang']['code_dist'].':'.$data['code'];
            @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);
            if ($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '') {
                @$this->utils->envoiCodeRechargementDist($recup_mail, $data['lang']['chef_agence'], $code_recharge, $data['montant'], $data['code'], $data['lang']);
            }
        }

        $params = array('view' => 'compte/recharge-distributeur-validation');
        $this->view($params, $data);
    }

    /******* Action calcul Frais Recharge ****/
    public function calculFrais()
    {
        $frais = $this->distributeurModel->calculTaxe($this->utils->securite_xss($_POST['montant']), $this->utils->securite_xss($_POST['service']));
        if ($frais > 0) echo $frais;
        else if ($frais == 0) echo 0;
        else echo -2;
    }

    /******* Action verifier code rechargement ****/
    public function codeRechargement()
    {
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fk_agence = $this->utils->securite_xss($_POST['fkagence']);
        $frais = $this->distributeurModel->verifCodeRechargement($fk_agence, $code_secret);
        if ($frais == 1) echo 1;
        elseif ($frais == 0) echo 0;
        else echo -2;
    }

    /********* Recharge Distributeur Validation ********/
    public function rechargeDistributeurValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = $this->utils->securite_xss($_POST['code']);
        $rowid = $this->utils->securite_xss($_POST['rowid']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils->securite_xss($_POST['frais']);
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $codevalidation = $this->utils->securite_xss($_POST['coder']);
        $user_creation = $this->userConnecter->rowid;
        $service = ID_SERVICE_RECHARGE_DIST;
        $frais = $this->distributeurModel->calculFrais($service, $montant);
        $soldeAgence = $this->utils->getSoldeAgence($fkagence);
       

        $numtransact = $this->utils->Generer_numtransaction();
        $statut = 0;
        $commentaire = 'Recharge Distributeur';

        if ($codevalidation != '' && $code != '' && $montant > 0 && $frais >= 0 && $soldeAgence != '' && strlen($numtransact) == 15) {
            if ($soldeAgence >= $montant) {
                $codeValidation = $this->utils->rechercherCoderechargement($codevalidation, $fkagence, $fkagence, $montant);
                if ($codeValidation > 0) {
                    $soldeDistA = $this->utils->getSoldeAgence($rowid);
                    $result= $this->distributeurModel->creditersoldeAgence($montant,1, $rowid);
                   if($result==1) {
                       $soldeDistApres = $this->utils->getSoldeAgence($rowid);
                       $operation="CREDIT";
                       $this->utils->addMouvementCompteAgence($numtransact, $soldeDistA, $soldeDistApres, $montant, $rowid, $operation, $commentaire);
                       $result1=$this->agenceModel->debiter_soldeAgence($montant,$fkagence);
                       //var_dump($result1);exit;
                       if($result1==1) {
                           $statut = 1;
                           $soldeapres=$this->utils->getSoldeAgence($fkagence);
                           $operation="DEBIT";
                           $this->utils->addMouvementCompteAgence($numtransact, $soldeAgence, $soldeapres, $montant, $fkagence, $operation, $commentaire);
                           $this->utils->SaveTransaction($numtransact, $service, $montant, 0, $user_creation, $statut, $commentaire . ' ' . $commentaire, $frais, $fkagence, 0);

                           if($frais>0)
                           {
                               $crediterCarteCommission = $this->utils->crediter_carteParametrable($frais, ID_CARTE_PARAMETRABLE_COMMISSION);
                               if ($crediterCarteCommission == 1) {
                                   $observation = 'Commission Recharge Distributeur';
                                   $this->utils->addCommission($frais, $service, $rowid, $observation, $fkagence);

                               } else {
                                   $observation = 'Commission Recharge Distributeur à faire';
                                   $this->utils->addCommission_afaire($frais, $service, $rowid, $observation, $fkagence);
                               }
                           }

                           $data['benef'] = $this->distributeurModel->getDistributeur($code);
                           $messagesms = $data['lang']['mess_recharge_sms'] . $this->utils->number_format($montant) . $data['lang']['currency'].' '.$data['lang']['solde_actuel'].' : '.$this->utils->number_format($soldeDistApres). $data['lang']['currency'];
                           @$this->utils->sendSMS($data['lang']['paositra1'], $data['benef']->tel, $messagesms);
                           $this->utils->log_journal('Recharge distributeur', 'Code distributeur:' . $code . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, '', 2, $user_creation);
                           $this->rediriger('distributeur', 'validationRechargeDistributeur/' . base64_encode('ok') . '/' . base64_encode($code) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));

                       }
                       }
                       
                     
                } else {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $rowid, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                    $this->utils->log_journal('Recharge distributeur', 'Code distributeur:' . $code . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, '', 2, $user_creation);
                    $this->rediriger('distributeur', 'validationRechargeDistributeur/' . base64_encode('nok2'));
                }
            } else {
                $message = 'Solde agence insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $rowid, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                $this->utils->log_journal('Recharge distributeur', 'Code distributeur:' . $code . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, '', 2, $user_creation);
                $this->rediriger('distributeur', 'validationRechargeDistributeur/' . base64_encode('nok3'));
            }
        } else {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $rowid, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
            $this->utils->log_journal('Recharge distributeur', 'Code distributeur:' . $code . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, '', 2, $user_creation);
            $this->rediriger('distributeur', 'validationRechargeDistributeur/' . base64_encode('nok4') . '/' . base64_encode($code));
        }
    }

    /***********Validation Recharge Distributeur**********/
    public function validationRechargeDistributeur($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($return[0]) === 'ok') {
            $data['code'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' => 'compte/recharge-distributeur-fin', 'title' => $data['lang']['recharge_dist'], 'alert' => $data['lang']['message_success_rechargement_dist'], 'type-alert' => 'alert-success');
        } else if (base64_decode($return[0]) === 'nok1') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'compte/recharge-distributeur-fin', 'title' => $data['lang']['recharge_dist'], 'alert' => $message, 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok2') {
            $params = array('view' => 'compte/recharge-distributeur-fin', 'title' => $data['lang']['recharge_dist'], 'alert' => $data['lang']['chargement_erreurcode_transact_save'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok3') {
            $params = array('view' => 'compte/recharge-distributeur-fin', 'title' => $data['lang']['recharge_dist'], 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok4') {
            $params = array('view' => 'compte/recharge-distributeur-search', 'title' => $data['lang']['recharge_dist'], 'alert' => $data['lang']['message_alert'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRechargementDistributeur()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = base64_decode($this->utils->securite_xss($_POST['code']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef'] = $this->distributeurModel->getDistributeur($code);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'compte/rechargement-distributeur-facture', 'title' => $data['lang']['recharge_dist']);
        $this->view($paramsview, $data);
    }


    /*********search code distibuteur pour vente kredivola ********/
    public function venteDistributeurSearch()
    {
        //$this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(46, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/vente-carte-distributeur-search');
        $this->view($params, $data);
    }


    public function ventecarteKredivola($params)
    {
       // $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(61, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $fkagence = $this->userConnecter->fk_agence;
        $code =  $this->utils->securite_xss($_POST['code']);
        $data['benef'] = $this->distributeurModel->getDistributeur($code);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($fkagence);

        $taille = count($params);
        if ($taille > 0) {
            if ($params[0] == sha1('solde_insuffisant')) {
                $paramsview = array('view' => 'compte/vente-kredivola-dist', 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('erreur_ajout_vente')) {
                $paramsview = array('view' => 'compte/vente-kredivola-dist', 'alert' => $data['lang']['vente_carte_jula_echec'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_insuffisant')) {
                $paramsview = array('view' => 'compte/vente-kredivola-dist', 'alert' => $data['lang']['stockjula_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_epuise')) {
                $paramsview = array('view' => 'compte/vente-kredivola-dist', 'alert' => $data['lang']['stock_insuffisant'], 'type-alert' => 'danger');
            }
        } else {
            //var_dump($this->distributeurModel->retourneCommission(2000));exit;
            $paramsview = array('view' => 'compte/vente-kredivola-dist');
        }

        $this->view($paramsview, $data);
    }

    public function stockKREDIVOLA()
    {
        $montant = $this->utils->securite_xss($_POST['montant']);
        echo json_encode($this->distributeurModel->getStockJULA($this->userConnecter->fk_agence, $montant));
    }

    public function vendrecarte()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['rowid'] =  $this->utils->securite_xss($_POST['rowid']);
        $data['code'] =  $this->utils->securite_xss($_POST['code']);
        $data['montant'] = $this->utils->securite_xss($_POST['montant']);
        $data['nombre'] = $this->utils->securite_xss($_POST['nombre']);
        $data['commission'] = $this->distributeurModel->retourneCommission($data['montant']);
        $data['taux']=$this->utils->getTauxDistributeur(VENTE_CARTE_KREDIVOLA_DISTRIBUTEUR);
        $data['tauxpayer']=($data['commission']*(100-$data['taux']))/100;
        $data['montantT']=($data['montant']+$data['tauxpayer'])*$data['nombre'];
        //var_dump($data['montantT']);exit;
        $paramsview = array('view' => 'compte/vente-kredivola-distrecap');
        $this->view($paramsview, $data);


    }
    public function vendrecartevalide(){

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant']);
        $nombre = $this->utils->securite_xss($_POST['nombre']);
        $commission = $this->utils->securite_xss($_POST['tauxcom']);
        $montant_ttc=$this->utils->securite_xss($_POST['mnttot']);
        $rowid =  $this->utils->securite_xss($_POST['rowid']);
        $code =  $this->utils->securite_xss($_POST['code']);
        $soldeagence = $this->distributeurModel->soldeAgence($this->userConnecter->fk_agence);
        $pdo = $this->utils->getPDO();

        if ($soldeagence >= $montant_ttc) {

            $stock = $this->distributeurModel->getStockJULA($this->userConnecter->fk_agence, $montant,$pdo);

            if ($stock >= $nombre) {

                $result = 0;
                $resultat = $this->distributeurModel->vendreCarteJULA($montant, $commission, $nombre, $this->userConnecter->fk_agence, $this->userConnecter->rowid, $rowid, $this->utils);

                if (count($resultat)>0){
                    $result = $resultat['num_transac'] ;
                }else{
                    $result =  $resultat;
                }
                if ($result > 0){

                    $this->rediriger("distributeur", "recuVenteKredivolaDistributeur/" . base64_encode($result));
                }
                else if ($result == -2) {

                    $this->rediriger("distributeur", "ventecarteKredivola/" . sha1('stock_epuise'));
                }
                else {
                    $this->rediriger("distributeur", "ventecarteKredivola/" . sha1('erreur_ajout_vente'));
                }

            }
            else {

                $this->rediriger("distributeur", "ventecarteKredivola/" . sha1('stock_insuffisant'));
            }
        }
        else {

            $this->rediriger("distributeur", "ventecarteKredivola/" . sha1('solde_insuffisant'));
        }

    }

    /************************** Recu Recharge Espece **************/
    public function recuVenteKredivolaDistributeur()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $code = base64_decode($this->utils->securite_xss($_POST['code']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef'] = $this->distributeurModel->getDistributeur($code);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'compte/rechargement-distributeur-facture', 'title' => $data['lang']['recharge_dist']);
        $this->view($paramsview, $data);
    }



    public function carteKredivola($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(36, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['cartes'] = $this->distributeurModel->cartesVente(base64_decode($id[0]));

        if (intval(base64_decode($id[0])) > 0) {
            $type_alert = 'success';
            $alert = $data['lang']['carte_vendues'];
        } else {
            $type_alert = 'error';
            $alert = $data['lang']['echec_vente_carte'];
        }

        $paramsview = array('view' => 'compte/carte-kredivola-vendu','alert' => $alert,'type-alert' => $type_alert);
        $this->view($paramsview, $data);
    }

    /******* Action verifier premier appro ****/
    public function getPremierAppro()
    {
       // var_dump($this->utils->securite_xss($_POST['agence']));exit;
        $verif = $this->distributeurModel->getPremierAppro(base64_decode($this->utils->securite_xss($_POST['agence'])));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

}



