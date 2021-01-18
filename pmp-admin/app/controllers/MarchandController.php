<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */
class MarchandController extends \app\core\BaseController
{
    private $marchandModel;
    private $userModel;
    private $userConnecter;

    public function __construct()
    {
        parent::__construct();
        $this->marchandModel = $this->model('MarchandModel');
        $this->userModel = $this->model('UtilisateurModel');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    /************* default action **************/
    public function index($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Acces_module($this->userConnecter->profil, 5));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $params = array('view' => 'GestionMarchand/accueil');
        $this->view($params,$data);
    }


    /************* default action **************/
    public function marchands($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(154,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(count($arg)){
            $arg = base64_decode($arg[0]);
            if($arg == -1) {
                $type_alert='-success';
                $alert=$data['lang']['message_success_add_marchand'];
            } elseif($arg == -2){
                $type_alert='-error';
                $alert=$data['lang']['message_echec_add_marchand'];
            } elseif($arg == -3) {
                $type_alert='-success';
                $alert=$data['lang']['message_success_edit_marchand'];
            } elseif($arg == -4){
                $type_alert='-error';
                $alert=$data['lang']['message_echec_edit_marchand'];
            } elseif($arg == -5){
                $type_alert='-error';
                $alert=$data['lang']['message_echec_add_marchand'];
            }
            elseif($arg == -22){
                $type_alert='error';
                $alert=$data['lang']['message_echec_phone_marchand'];
            }
            elseif($arg == -222){
                $type_alert='error';
                $alert=$data['lang']['message_echec_email_marchand'];
            }

        }
        $params = array('view' => 'GestionMarchand/index','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params,$data);
    }


    public function addMarchand()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(154,$this->userConnecter->profil));
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $cle = $this->utils->generation_code_validation(5);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $telmobile = $this->utils->securite_xss(str_replace(" ", "", $_POST['telmobile']));
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $nom_marchand = $this->utils->securite_xss($_POST['nom_marchand']);
        $email = $this->utils->securite_xss($_POST['email']);
        $rc = $this->utils->securite_xss($_POST['rc']);
        $ninea = $this->utils->securite_xss($_POST['ninea']);
        $adresse = $this->utils->securite_xss($_POST['adresse']);
        $login = $this->utils->securite_xss($_POST['email']);
        $type = $this->utils->securite_xss($_POST['type']);
         $verifcationTel = $this->marchandModel->verifierTelMarchand($telmobile);
        if($verifcationTel == 0){

            $verifcationEmail = $this->marchandModel->verifierEmailMarchand($email);
            if($verifcationEmail == 0) {

               $idmarchand = $this->marchandModel->addMarchandModel(["nom_marchand" => $nom_marchand, "rc" => $rc, "ninea" => $ninea, "adresse" => $adresse, "email" => $email, "telmobile" => $telmobile, "user_creation" => $this->userConnecter->rowid, "cle" => $cle, "type" =>$type]);

               if ($idmarchand > 0) {
                    $libelleprofil1="caissier_".$nom_marchand;
                    $libelleprofil="admin_".$nom_marchand;
                    $profil = $this->marchandModel->addProfilMarchandModel(["user_creation" => $this->userConnecter->rowid, "fk_marchand" => $idmarchand,"libelle" => $libelleprofil, "typeprofil" => 1]);
                    $profil1 = $this->marchandModel->addProfilMarchandModel(["user_creation" => $this->userConnecter->rowid, "fk_marchand" => $idmarchand,"libelle" => $libelleprofil1, "typeprofil" => 2]);
                    if ($profil > 0) {
                        $password = $this->utils->generation_code(8);
                        $usermarchand = $this->marchandModel->addUserMarchandModel(["prenom" => $prenom, "nom" => $nom, "email" => $email, "telephone" => $telmobile, "login" => $login, "password" => sha1($password), "admin" =>1, "fk_marchand" => $idmarchand, "fk_profil" => $profil]);
                        if ($usermarchand != false) {
                            $codemarchand = $this->marchandModel->Generer_codeMarchand();
                            if($type==1){
                                    $num = $this->marchandModel->getMaxNumCaisse($idmarchand);
                                    if(count($num)>0) {
                                        $numenc = sprintf("%04d",$num[0]->numcaisse + 1);
                                    }else{
                                        $numenc='0001';
                                    }
                                    $caisse = $this->marchandModel->addCaisseMarchandModel(["numcaisse" => $numenc, "codemarchand" => $codemarchand, "fk_marchand" => $idmarchand, "user_creation" => $this->userConnecter->rowid]);
                                    if ($caisse!= false) {
                                        @$this->utils->envoiParamsMarchand($email, $prenom.' '.$nom, $nom_marchand, $login, $password, $cle, $codemarchand);
                                        $this->rediriger("marchand", "marchands/" . base64_encode(-1));
                                        exit();

                                    } else {
                                        $this->marchandModel->rollBackMarchand("marchand", ["idmarchand" => $idmarchand]);
                                        $this->rediriger("marchand", "marchands/" . base64_encode(-2));
                                    }

                                }else{
                                    @$this->utils->envoiParamsMarchand($email, $prenom.' '.$nom, $nom_marchand, $login, $password, $cle, $codemarchand);
                                    $this->rediriger("marchand", "marchands/" . base64_encode(-1));
                                    exit();
                                }

                            } else { $this->marchandModel->rollBackMarchand("marchand", ["idmarchand" => $idmarchand]);
                                     $this->rediriger("marchand", "marchands/" . base64_encode(-5));
                            }

                    } else { $this->rediriger("marchand", "marchands/" . base64_encode(-5));}

                } else  { $this->rediriger("marchand", "marchands/" . base64_encode(-2));}

               }  else { $this->rediriger("marchand", "marchands/" . base64_encode(-222));            }


        }else {$this->rediriger("marchand","marchands/".base64_encode(-22));  }


    }

    /******* Action verifier email ****/
    public function verifEmail()
    {
        $verif = $this->marchandModel->verifierEmailMarchand($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==0) echo -2;
        else echo -1;
    }

    /******* Action verifier email ****/
    public function verifTel()
    {
        $tel="+".$this->utils->securite_xss(str_replace(" ", "", $_POST['telmobile']));
        //var_dump($tel);exit;
        $verif = $this->marchandModel->verifierTelMarchand($tel);
        if($verif==1) echo 1;
        elseif($verif==0) echo -2;
        else echo -1;
    }



    /************* default action **************/
    public function detailsMarchand($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(156,$this->userConnecter->profil));
        if(is_null($arg)) $this->rediriger("marchand","");
        $arg2 = -1;
        $data['lier'] = 0;

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        //$arg = base64_decode($arg[0]);
        if(count($arg) == 2){
            $arg2 = base64_decode($arg[1]);
            $data['lier'] = 1;
            if($arg2 == 1) {
                $type_alert='-success';
                $alert=$data['lang']['message_success_associate_marchand'];
            } else{
                $type_alert='-error';
                $alert=$data['lang']['message_echec_associate_marchand'];
            }
            $params = array('view' => 'GestionMarchand/marchand-detail' ,'alert'=>$alert,'type-alert'=>$type_alert);
        }
        else{
            $params = array('view' => 'GestionMarchand/marchand-detail');
        }


        $data['marchand'] =  $this->marchandModel->getDetailsMarchand(base64_decode($arg[0]));
//        echo"<pre>";var_dump($data['marchand']);exit();


        $this->view($params,$data);
    }

    /************* default action **************/
    public function updateMarchand()
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(157,$this->userConnecter->profil));
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'idmarchand') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        if($this->marchandModel->updateMarchandModel($_POST)) $this->rediriger("marchand","marchands/".base64_encode(-3));
        else $this->rediriger("marchand","marchands/".base64_encode(-4));
    }

    /************* Liste Marchand **************/
    public function processingMarchand()
    {
        $param = [
            "button"=>[
                [ROOT."marchand/detailsMarchand/", "fa fa-search"]
            ],
            "args"=>[],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->marchandModel, "getMarchand", $param);
    }

    public function associerMarchand()
    {
        //$this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(157,$this->userConnecter->profil));
        $tel = str_replace('+', '00', $this->utils->securite_xss($_POST['tel']));
        //$marchand = base64_decode($this->utils->securite_xss($_POST['marchand']));

        echo $this->marchandModel->particulierMarchandModel($tel);

    }

    public function confirmAssoicierMarchand()
    {
        //$this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(157,$this->userConnecter->profil));
        //$carte = str_replace('+', '00', $this->utils->securite_xss($_POST['tel']));
        $idmarchand = base64_decode($this->utils->securite_xss($_POST['idmarchand']));
        $idcarte = base64_decode($this->utils->securite_xss($_POST['idcarte']));
        $idcarte = str_replace('+', '00', $idcarte);
        $res = $this->marchandModel->associerMarchandModel($idmarchand, trim('00'.$idcarte));
        $this->rediriger("marchand","detailsMarchand/".base64_encode($idmarchand)."/".base64_encode($res));

    }

    //********** APPEL DE FOND *************//

    public function listeAppelsFond($return=null)
    {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['marchand']= $this->marchandModel->getMarchands();


        if (isset($_POST["datedebut"]) && isset($_POST["datefin"]) && isset($_POST['fk_marchand'])) {
            $data['datedebut'] =$this->utils->securite_xss($_POST['datedebut']);
            $data['datefin']  = $this->utils->securite_xss($_POST['datefin']);
            $data['fk_marchand']  = $this->utils->securite_xss($_POST['fk_marchand']);

        }else{

            $data['datedebut'] = date('Y-m-d');
            $data['datefin']  = date('Y-m-d');
            $data['fk_marchand']  = 0;
        }



        $data['appelfond1']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],0);
        $data['appelfond2']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],1);
        $data['appelfond3']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],2);
        $params = array('view' => 'GestionMarchand/ListeAppelDeFond');
        $this->view($params,$data);
    }

    /***************** Valider Appel de fond *********************/
    public function validerAppelFond()
    {
        $data['datedebut']=date('Y-m-d');
        $data['datefin']=date('Y-m-d');
        $id_user_marchand=base64_decode($this->utils->securite_xss($_POST['id_usermarchand']));
        $rowid = base64_decode($this->utils->securite_xss($_POST['rowid']));
        $id_marchand=base64_decode($this->utils->securite_xss($_POST['fk_marchand']));
        $user_validation = $this->userConnecter->rowid;
        $date_validation = $date = date('Y-m-d H:i:s');
        $montant=base64_decode($this->utils->securite_xss($_POST['montant']));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $numtrans = $this->utils->Generer_numtransaction();
        $this->getConnexion()->beginTransaction() ;
        $soldeavant = $this->marchandModel->consulterSoldeMarchand($id_marchand);
        var_dump($soldeavant." montant : ".$montant);exit;
        if($soldeavant>$montant){
            $result = $this->marchandModel->updateSoldeMarchand($id_marchand, $montant);
            if ($result > 0) {
                $soldeapres = $this->marchandModel->consulterSoldeMarchand($id_marchand);
                $date = date('Y-m-d H:i:s');
                $status_transaction = 1;
                $commentaire = "Appel de fonds succes";
                $result1 = $this->marchandModel->insertReleve(["num_transac" => $numtrans, "solde_avant" => $soldeavant, "solde_apres" => $soldeapres, "montant" => $montant, "date_transaction" => $date, "fk_user_marchand" => $id_user_marchand, "marchand" => $id_marchand, "operation" => 'DEBIT']);

                $result2 = $this->marchandModel->insertTransactionCompte(["num_transaction" => $numtrans, "date_transaction" => $date, "montant" => $montant, "statut" => $status_transaction, "fk_marchand" => $id_marchand, "commentaire" => "Appel de fonds succes"]);

                if ($result1 > 0 && $result2 > 0) {
                    /*
                 *  Mise a jour table appel de fond
                 * */
                    $etat = 1;
                    $result3 = $this->marchandModel->validateAppel($etat, $date_validation, $rowid, $user_validation);

                    if ($result3 > 0) {
                        $result4 = $this->marchandModel->crediter_soldeCompte($montant);
                        $operation = "CREDIT";
                        $soldeComptesAv = $this->marchandModel->soldeComptePoste();
                        $soldeComptesAp = $soldeComptesAv - $montant;
                        $row = 1;
                        $this->utils->addMouvementCompteOperation($numtrans, $soldeComptesAv, $soldeComptesAp, $montant, $row, $operation, $commentaire);

                        if ($result4 > 0) {
                            $subjet = "Information sur votre appel de fonds ";
                            $nom_marchand = $this->marchandModel->getMarchandInfo($id_marchand);
                            $email = $nom_marchand[0]['email'];
                            $contenue = "Votre appel de fonds du montant de : " . $montant . " Ar a étè validé. Votre nouveau solde est de " . $nom_marchand[0]['solde'] . " Ar";
                            $this->utils->sendMailAlert($email, $contenue, $subjet, $nom_marchand[0]['nom_marchand']);
                            $this->connexion->commit();
                            $this->utils->log_journal('Validation appel de fond', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'succes', 1, $user_validation);
                            $this->rediriger('marchand', 'validationApp/' . base64_encode('ok'));
                            exit;
                        } else {
                            $this->connexion->rollBack();
                            $this->utils->log_journal('Créditation Compte', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'echec', 1, $user_validation);
                            $this->rediriger('marchand', 'validationApp/' . base64_encode('nok'));
                        }
                    } else {
                        $this->connexion->rollBack();
                        $this->utils->log_journal('Validation appel de fond', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'echec', 1, $user_validation);
                        $this->rediriger('marchand', 'validationApp/' . base64_encode('nok'));
                    }
                } else {
                    $this->connexion->rollBack();
                    $this->utils->log_journal('Validation appel de fond', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'echec', 1, $user_validation);
                    $this->rediriger('marchand', 'validationApp/' . base64_encode('nok'));
                }
            } else {
                $this->connexion->rollBack();
                $this->utils->log_journal('Validation appel de fond', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'echec', 1, $user_validation);
                $this->rediriger('marchand', 'validationApp/' . base64_encode('nok'));
            }
        }else{
            $this->connexion->rollBack();
            $this->utils->log_journal('Validation appel de fond', 'idappel:' . $rowid . ' effectue par:' . $user_validation, 'echec', 1, $user_validation);
            $this->rediriger('marchand', 'validationApp/' . base64_encode('nok1'));
        }
    }

    /***********Validation Appel de fond**********/
    public function validationApp($return)
    {
        //var_dump(base64_decode($return[0]));exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['datedebut']=date('Y-m-d');
        $data['datefin']=date('Y-m-d');
        $data['fk_marchand']  = 0;
        $data['appelfond1']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],0);
        $data['appelfond2']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],1);
        $data['appelfond3']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],2);
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'GestionMarchand/ListeAppelDeFond', 'title' =>$data['lang']['listeAppelsFond'], 'alert'=>$data['lang']['succes_valider_appel'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'GestionMarchand/ListeAppelDeFond', 'title' =>$data['lang']['listeAppelsFond'], 'alert'=>$data['lang']['error_valider_appel'], 'type-alert'=>'alert-danger');
        }
        elseif(base64_decode($return[0])=== 'nok1')
        {
            $params = array('view' =>'GestionMarchand/ListeAppelDeFond', 'title' =>$data['lang']['listeAppelsFond'], 'alert'=>$data['lang']['error_valider_appel1'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /************* Autoriser Appel de fond **************/
    public function autoriserAppelFond()
    {

        $user_autoriz = $this->userConnecter->rowid;
        $date_autoriz = date('Y-m-d H:i:s');
        $rowid =  base64_decode($this->utils->securite_xss($_POST['rowid1']));
        $result3 = $this->marchandModel->validateAppel(2,$date_autoriz,$rowid,$user_autoriz);
        if ($result3 == 1) {
            $this->utils->log_journal('Autorisation appel de fond', 'Id a:' . $rowid, 'succès', 10, $user_autoriz);
            $this->rediriger('marchand', 'validationAutoriserAppelFond/' . base64_encode('ok'));
        } else {
            $this->utils->log_journal('Autorisation appel de fond', 'Id a:' . $rowid, 'succès', 10, $user_autoriz);
            $this->rediriger('marchand', 'validationAutoriserAppelFond/' . base64_encode('nok'));
        }

    }


    /***********Validation Autoriser Appel de fond**********/
    public function validationAutoriserAppelFond($return)
    {
        //var_dump(base64_decode($return[0]));exit;

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['datedebut']=date('Y-m-d');
        $data['datefin']=date('Y-m-d');
        $data['fk_marchand']  = 0;
        $data['appelfond1']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],0);
        $data['appelfond2']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],1);
        $data['appelfond3']= $this->marchandModel->AppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin'],2);
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'GestionMarchand/ListeAppelDeFond', 'title' =>$data['lang']['listeAppelsFond'], 'alert'=>$data['lang']['succes_valider_appel'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'GestionMarchand/ListeAppelDeFond', 'title' =>$data['lang']['listeAppelsFond'], 'alert'=>$data['lang']['error_valider_appel'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    //********* Regénération clé secrete ****************//
    public function regenerecle()
    {
        $cle = $this->utils->generation_code_validation(5);
        $id_marchand=base64_decode($this->utils->securite_xss($_POST['idmarchand']));
        $nom_marchand = $this->marchandModel->getMarchandInfo($id_marchand);
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $result=$this->marchandModel->updateCleMarchand($id_marchand,$cle);
       //var_dump($id_marchand);exit;
        if($result) {

            $subjet = "Regénération de clé ";
            $email = $nom_marchand[0]['email'];
            $contenue = "Votre clé secréte a été regénéré : " . $cle;
            $this->utils->sendMailAlert($email, $contenue, $subjet, $nom_marchand[0]['nom_marchand']);
            $recup_tel=$nom_marchand[0]['telmobile'];
            @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $contenue);
            $this->rediriger('marchand','validationRegeneration/'.base64_encode('ok'));

        }else $this->rediriger('marchand','validationRegeneration/'.base64_encode('nok'));
    }


    /***********Validation Appel de fond**********/
    public function validationRegeneration($return)
    {
        //var_dump(base64_decode($return[0]));exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'GestionMarchand/index', 'title' =>$data['lang']['genecle'], 'alert'=>$data['lang']['succes_genecle'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'GestionMarchand/index', 'title' =>$data['lang']['genecle'], 'alert'=>$data['lang']['error_genecle'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }
    /************* Liste User du Marchand **************/
    public function processingUser($id)
    {
        $id=base64_decode($id[0]);
        //var_dump($id);exit;
        $param = [
            "button"=>[
                [ROOT."marchand/detailsUserMarchand/", "fa fa-search"]
            ],
            "args"=>[$id],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->marchandModel, "getUserMarchand", $param);
    }
    /************* Liste caisse du Marchand **************/
    public function processingCaisse($id)
    {
        $id=base64_decode($id[0]);
        $param = [
            "button"=>[
                [ROOT."marchand/detailsCaisseMarchand/", "fa fa-search"]
            ],
            "args"=>[$id],
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->marchandModel, "getCaisseMarchand", $param);
    }

    /**
     * @return mixed
     */
    public function detailsUserMarchand($id)
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['marchand'] = $this->marchandModel->getUserMarchandId(base64_decode($id[0]));
        $data['profil'] = $this->marchandModel->getProfil();
        //var_dump($data['profil']);exit;
        $params = array('view' => 'GestionMarchand/usermarchand-detail');
        $this->view($params, $data);

    }


    public function detailsCaisseMarchand($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['marchand'] = $this->marchandModel->getCaisseMarchandId(base64_decode($id[0]));
        $params = array('view' => 'GestionMarchand/caissemarchand-detail');
        $this->view($params, $data);

    }


    public function regenerePwdMarchand()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $pass = $this->utils->generation_code(8);
        $id_user=base64_decode($this->utils->securite_xss($_POST['iduser']));
        //var_dump($id_user);exit;
        $nom_marchand = $this->marchandModel->getUserMarchandInfo($id_user);
        $result=$this->marchandModel->updatePwdMarchand($id_user,sha1($pass));
        //var_dump($nom_marchand." ".$result);exit;
        if($result>0){
           // var_dump($nom_marchand[0]['email']);exit;
            $url="https://numherit-preprod.com/postecashv3/partenaire";
            $subjet = "Regénération mot de passe";
            $email = $nom_marchand[0]['email'];
            $contenue = "veuillez vous connecter sur votre compte en cliquant sur ce <a href='" . $url . "'>lien</a> <br> login: " .$nom_marchand[0]['login']. "<br> mot de passe: " . $pass;
            $nom_client = $nom_marchand[0]['prenom'] . " " . $nom_marchand[0]['nom'] ;
            $this->utils->sendMailAlert($email, $contenue, $subjet, $nom_client);
            $this->rediriger('marchand','validationRegenerationPwd/'.base64_encode('ok'));

        }else $this->rediriger('marchand','validationRegenerationPwd/'.base64_encode('nok'));
    }



    public function regenerecodeMarchand()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $codemarchand = $this->marchandModel->Generer_codeMarchand();
        $id=base64_decode($this->utils->securite_xss($_POST['rowid']));
        $idmarchand=base64_decode($this->utils->securite_xss($_POST['idmarchand']));
        //var_dump($id_user);exit;
        $nom_marchand = $this->marchandModel->getCaisseMarchandInfo($id);
        $result=$this->marchandModel->updateCodeMarchand($id,$codemarchand);
      //  var_dump($nom_marchand." ".$result);exit;
        if($result>0){
            // var_dump($nom_marchand[0]['email']);exit;
            //$url="https://numherit-preprod.com/postecashv3/partenaire";
            $subjet = "Regénération code marchant";
            $email = $nom_marchand[0]['email'];
            $contenue = "Votre code marchand a été regénéré :  " . $codemarchand . " pour la caisse n° : " .$nom_marchand[0]['numcaisse'];
            $this->utils->sendMailAlert($email, $contenue, $subjet, $nom_marchand[0]['nom_marchand']);
            $this->rediriger("marchand","usmarchands/".base64_encode(-2));

        }else $this->rediriger("marchand","usmarchands/".base64_encode(-1));
    }

    /***********Validation Appel de fond**********/
    public function validationRegenerationPwd($return)
    {
        //var_dump(base64_decode($return[0]));exit;
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($return[0])=== 'ok')
        {
            $params = array('view' =>'GestionMarchand/index', 'title' =>$data['lang']['regenere_pass'], 'alert'=>$data['lang']['message_success_regenere'], 'type-alert'=>'-success');
        }
        elseif(base64_decode($return[0])=== 'nok')
        {
            $params = array('view' =>'GestionMarchand/index', 'title' =>$data['lang']['regenere_pass'], 'alert'=>$data['lang']['message_error_regenere'], 'type-alert'=>'-danger');
        }
        $this->view($params,$data);
    }


    public function updateUserMarchand()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(157,$this->userConnecter->profil));
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'iduser') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        if($this->marchandModel->updateUserMarchandModel($_POST)) $this->rediriger("marchand","usmarchands/".base64_encode(-3));
        else $this->rediriger("marchand","usmarchands/".base64_encode(-4));

    }


    /************* default action **************/
    public function usmarchands($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(154,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if(count($arg)){
            $arg = base64_decode($arg[0]);
            if($arg == -3) {
                $type_alert='-success';
                $alert=$data['lang']['message_success_edit_usermarchand'];
            } elseif($arg == -4) {
                $type_alert = '-error';
                $alert = $data['lang']['message_echec_edit_usermarchand'];
            } elseif($arg == -6) {
                $type_alert = '-success';
                $alert = $data['lang']['message_success_edit_caissemarchand'];
            } elseif($arg == -7) {
                $type_alert = '-error';
                $alert = $data['lang']['message_echec_edit_caissemarchand'];
            }elseif($arg == -1){
                $type_alert='-error';
                $alert=$data['lang']['message_echec_gmarchand'];
            }
            elseif($arg == -2){
                $type_alert='-success';
                $alert=$data['lang']['message_success_gmarchand'];
            }

        }
        $params = array('view' => 'GestionMarchand/index','alert'=>$alert,'type-alert'=>$type_alert);
        $this->view($params,$data);
    }

    public function updateCaisseMarchand()
    {

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(157,$this->userConnecter->profil));
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'rowid') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        if($this->marchandModel->updateCaisseMarchandModel($_POST)) $this->rediriger("marchand","usmarchands/".base64_encode(-6));
        else $this->rediriger("marchand","usmarchands/".base64_encode(-7));


    }


        public function histo_appel_fond(){
            $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
            $data['marchand']= $this->marchandModel->getMarchands();


            if (isset($_POST["datedebut"]) && isset($_POST["datefin"]) && isset($_POST['fk_marchand'])) {
                //var_dump($_POST["datedebut"]);die;
                $data['datedebut'] =$this->utils->securite_xss($_POST['datedebut']);
                $data['datefin']  = $this->utils->securite_xss($_POST['datefin']);
                $data['fk_marchand']  = $this->utils->securite_xss($_POST['fk_marchand']);

            }else{

                $data['datedebut'] = "";
                $data['datefin']  = "";
                $data['fk_marchand']  = "";
                //var_dump($data['datedebut']);exit;
            }
            $data['histo_appel_fond']= $this->marchandModel->getHistoAppelFond($data['fk_marchand'],$data['datedebut'],$data['datefin']);

           // var_dump($data['histo_appel_fond']);die();
            $params = array('view' => 'GestionMarchand/histo_appel_fond');
            $this->view($params,$data);
        }

    /***************** transaction du jour *********************/
    public function processing_histo_appel_fond()
    {
        //var_dump($_POST);die();
        $requestData = $_REQUEST;
        $columns = array(
            0 =>'date_transaction',
            1 => 'num_transaction',
            2 => 'montant',
            3 => 'statut',
            4 => 'nom_marchand',
            5 => 'rowid',
        );
        $where = '';

        if($_POST["datedebut"] != "" && $_POST["datefin"] != "" && $_POST["fk_marchand"] != ""){
            $date1 = $this->utils->securite_xss($_POST["datedebut"]);
            $date2 = $this->utils->securite_xss($_POST["datefin"]);
            $marchand = $this->utils->securite_xss($_POST["fk_marchand"]);
            $where.=" where t.fk_marchand=".$marchand." AND Date(t.date_transaction)>='".$date1."' AND Date(t.date_transaction)<='".$date2."'";

        }elseif ($_POST["datedebut"] != "" && $_POST["datefin"] != "") {
            $date1 = $this->utils->securite_xss($_POST["datedebut"]);
            $date2 = $this->utils->securite_xss($_POST["datefin"]);
            $where.=" where Date(t.date_transaction)>='".$date1."' AND Date(t.date_transaction)<='".$date2."'";

        }elseif ($_POST["fk_marchand"] != "") {
            $marchand = $this->utils->securite_xss($_POST["fk_marchand"]);
            $where.=" where t.fk_marchand='".$marchand."'";
        }

        // getting total number records without any search
        $sql = "SELECT t.rowid, t.date_transaction, t.num_transaction, t.montant, t.statut ,m.`nom_marchand`  FROM `marchand` as m INNER JOIN `transaction_compte_marchand` as t ON m.`idmarchand`= t.fk_marchand ".$where;

        if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( t.num_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR m.`nom_marchand` LIKE '%".$requestData['search']['value']."%' )";
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT t.rowid, t.date_transaction, t.num_transaction, t.montant, t.statut ,m.`nom_marchand`  FROM `marchand` as m INNER JOIN `transaction_compte_marchand` as t ON m.`idmarchand`= t.fk_marchand ".$where;

        if( $requestData['search']['value']!="" ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( t.num_transaction LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR t.montant LIKE '%".$requestData['search']['value']."%' ";
            $sql.=" OR m.`nom_marchand` LIKE '%".$requestData['search']['value']."%' )";

        }

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $user = $this->getConnexion()->prepare($sql);
        $user->execute();
        $rows = $user->fetchAll();


        $data = array();
        foreach( $rows as $row)
        {  //preparing an array
            $nestedData=array();
            $montant = $row["montant"];
            if($row["statut"]==1) $statut='succès';

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] =$row["num_transaction"];
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = "<span class='text-green'>".$statut."</span>";
            $nestedData[] = $row["nom_marchand"];
            //$nestedData[] = "<a  href=".WEBROOT."histo_appel_fond/detailTransac/".base64_encode($row["num_transaction"])."><i class='fa fa-search'></i></a>";
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"=> intval( $requestData['draw'] ),
            "recordsTotal"=> intval( $totalData ),  // total number of records
            "recordsFiltered"=> intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"=> $data   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }
}