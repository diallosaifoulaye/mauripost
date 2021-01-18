<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');


require_once __DIR__.'/../../../vendor/API_Numherit/ApiComptes.php';


class CarteController extends \app\core\FrontendController
{

    
    private $utils_carte;
    private $connexion;
    private $userConnecter;
    private $api_gtp;
    private  $api_numherit;

    public function __construct()
    {
        $this->utils_carte = new \app\core\UtilsCarte();
        $this->connexion = \app\core\Connexion::getConnexion();
        $this->api_numherit = new \vendor\API_Numherit\ApiComptes();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
        $this->userConnecter = $this->getSession()->getAttribut('objconnect');
    }

    public function GestionCarte()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/cartes/gestion-des-cartes'));
        $this->view($paramsview, $data);
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION DES CARTES                                         //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function createCarte(){
        $this->getSession()->getAttribut('objconnect');
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profession'] = $this->utils->professions();
        $data['typepiece'] = $this->utils->typepiece();
        $data['pays'] = $this->utils->listePays();
        $data['nationalites'] = $this->utils->nationalites();
        $data['regions'] = $this->utils->allRegionByPays();
        $paramsview = array('view' => 'frontend/cartes/new-carte');
        $this->view($paramsview, $data);

    }

    public function inserCarte()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $prenom = trim($this->utils->securite_xss($_POST['prenom']));
        $prenom1 = trim($this->utils->securite_xss($_POST['prenom1']));
        $nom = trim($this->utils->securite_xss($_POST['nom']));
        $sexe = trim($this->utils->securite_xss($_POST['sexe']));
        $datenais = trim($this->utils->securite_xss($_POST['from']));
        $email = trim($this->utils->securite_xss($_POST['email']));
        $profession = trim($this->utils->securite_xss($_POST['profession']));
        $adresse = trim($this->utils->securite_xss($_POST['adresse']));
        $adresse1 = trim($this->utils->securite_xss($_POST['adresse1']));
        $adresse2 = trim($this->utils->securite_xss($_POST['adresse2']));
        $typepiece = trim($this->utils->securite_xss($_POST['typepiece']));
        $piece = trim($this->utils->securite_xss($_POST['piece']));
        $datedelivrancepiece = trim($this->utils->securite_xss($_POST['datedelivrancepiece']));
        $pays = trim($this->utils->securite_xss($_POST['pays']));
        $nationalite = trim($this->utils->securite_xss($_POST['nationalite']));
        $region = trim($this->utils->securite_xss($_POST['region']));
        $departement = trim($this->utils->securite_xss($_POST['departement']));
        $codepostal = trim($this->utils->securite_xss($_POST['codepostal']));
        $telfixe = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['telfixe'])));
        $typecarte = trim($this->utils->securite_xss($_POST['typecarte']));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['telephone'])));
        $embossage = trim($this->utils->securite_xss($_POST['embossage']));
        $numeroserie = trim($this->utils->securite_xss($_POST['numeroserie']));
        if ($embossage == '') {
            $embossage = substr($prenom, 0, 15);
        }
        $dateexpirationcarte = trim($this->utils->securite_xss($_POST['dateexpirationcarte']));
        $commentaire = trim($this->utils->securite_xss($_POST['commentaire']));
        $user_creation = $this->userConnecter->rowid;
        $agence = $this->userConnecter->fk_agence;
        $prenomuser = $this->userConnecter->prenom;
        $nomuser = $this->userConnecter->nom;
        $idtransaction = $this->utils->Generer_numtransaction();


        if ($this->utils_carte->verifierNumSerie($numeroserie) === 1) {

            $ResultatEnroller = $this->utils_carte->enrollerCarte($prenom,
                $prenom1,
                $nom,
                $sexe,
                $datenais,
                $email,
                $profession,
                $adresse,
                $adresse1,
                $adresse2,
                $typepiece,
                $piece,
                $datedelivrancepiece,
                $pays,
                $nationalite,
                $region,
                $departement,
                $codepostal,
                $telfixe,
                $typecarte,
                $telephone,
                $embossage,
                $numeroserie,
                $dateexpirationcarte,
                $commentaire,
                $user_creation,
                $agence,
                $prenomuser,
                $nomuser,
                $idtransaction);

            $ResultatEnroller = json_decode($ResultatEnroller);
            $errorCode = $ResultatEnroller->errorCode;
            $errorMessage = $ResultatEnroller->errorMessage;
            if ($errorCode === 1) {
                $this->utils_carte->venteCarte($numeroserie, $agence);
            }

            $this->rediriger('carte', 'resultInsertCarte/' . base64_encode($errorCode). '/' .base64_encode($errorMessage) . '/' . base64_encode($telephone));
        } else {
            $errorMessage = $data['lang']['agency_non_authaurize_to_active'];
            $this->rediriger('carte', 'resultInsertCarte/' . base64_encode(22) . '/' .base64_encode($errorMessage) . '/' . base64_encode($telephone));
        }
    }

    public function resultInsertCarte($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $resultat = base64_decode($id[0]);
        $message = base64_decode($id[1]);

        if ($resultat == 1) {
            $tel = base64_decode($id[2]);
            $data['benef'] = $this->utils_carte->beneficiaireByNumeroTel($tel);

            $type_alert = 'success';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-success', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 2 || $resultat == 3) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 4) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 5) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 6) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);

        } else if ($resultat == 99) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 22) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 33) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 44) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 55) {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else {
            $type_alert = 'error';
            $alert = $message;
            $params = array('view' => 'frontend/cartes/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        }
    }



    public function createCompte($id){

        $this->getSession()->getAttribut('objconnect');
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($id[0]) == 'bon') {
            $type_alert = 'alert-success';
            $alert = 'Compte créé avec succès';
        } else {
            $type_alert = 'alert-danger';
            $alert = base64_decode($id[0]);
        }
        $data['typepiece'] = $this->utils->typepiece();
        $paramsview = array('view' => 'frontend/cartes/new-compte','alert' => $alert, 'type-alert' => $type_alert);
        $this->view($paramsview, $data);

    }

    public function inserCompte()
    {
        $username = "Numherit";
        $userId = 1;
        $token = $this->utils->getToken($userId);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $adresse = $this->utils->securite_xss($_POST['adresse']);
        $email = $this->utils->securite_xss($_POST['email']);
        $password = $this->utils->generation_code(12);
        $dateNaissance = $this->utils->securite_xss($_POST['datenaiss']);
        $typePiece = $this->utils->securite_xss($_POST['typepiece']);
        $numPiece = $this->utils->securite_xss($_POST['piece']);
        $dateDeliv = $this->utils->securite_xss($_POST['datedelivrancepiece']);
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $sexe = $this->utils->securite_xss($_POST['sexe']);
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $agence = $obj->getFk_agence();
        $response = $this->api_numherit->creerCompte($username, $token, $nom, $prenom, $adresse, $email, $password, $dateNaissance, $telephone, $user_creation, $agence, $sexe, $typePiece, $numPiece, $dateDeliv);
        $tab = json_decode($response);
        if (is_object($tab)) {
            $num_transac = $this->utils->generation_numTransaction();
            if ($tab->{'statusCode'} == '000')
            {
                $idcompte = $this->utils->getCarteTelephone($telephone);
                $this->utils->SaveTransaction($num_transac, $service = ID_SERVICE_CREATION_COMPTE, $montant = 0, $idcompte, $user_creation, $statut = 1, 'SUCCESS: CREATION COMPTE', $frais = 0, $agence, 0);
                $true = 'bon';
                $this->utils->log_journal('Creation compte', 'Client:' . $nom . ' ' . $prenom .'N° téléphone du client: '.$telephone .' Agence:' . $agence, 'succes', 2, $user_creation);

                $pictures = array($_FILES['recto'],$_FILES['verso'],$_FILES['photo']);
                $fichiers = array();
                for($i=0;$i<sizeof($pictures);$i++)
                {
                    $nomfichier = $telephone.'-'.($i+1);
                    $nomfichierWithExtension = $nomfichier.".". \pathinfo($pictures[$i]['name'], PATHINFO_EXTENSION);
                    $this->utils->setUploadFiles($pictures[$i],__DIR__.'/../../../../pmp-admin/assets/img_users/',$nomfichier);
                    array_push($fichiers,$nomfichierWithExtension);
                }
                $this->utils_carte->updateCarte($fichiers, $telephone);

                $this->rediriger('carte', 'beneficiaire/' . base64_encode($true));
            }
            else {
                $this->utils->SaveTransaction($num_transac, $service = ID_SERVICE_CREATION_COMPTE, $montant = 0, 0, $user_creation, $statut = 0, 'ERROR: CREATION COMPTE :'.$tab->{'statusMessage'}, $frais = 0, $agence, 0);
                $this->utils->log_journal('Creation compte', 'Client:' . $nom . ' ' . $prenom . 'N° téléphone du client: '.$telephone . ' Agence:' . $agence, 'echec : '.$tab->{'statusMessage'}, 2, $user_creation);
                $this->rediriger('carte', 'createCompte/' . base64_encode($tab->{'statusMessage'}));
            }
        }
    }


    public function testNumeroTelephone()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        echo $this->utils_carte->verifyBeneficiaire('telephone', trim(str_replace("+", "00", $this->utils->securite_xss($_POST['telephone']))), 'carte');
    }
    public function test()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $var = $this->utils->securite_xss($_POST['var']);

        switch ($var) {
            case 'email':
                echo $this->utils_carte->verifyBeneficiaire('email', $this->utils->securite_xss($_POST['email']), 'beneficiaire');
                break;
            case 'piece':
                echo $this->utils_carte->verifyBeneficiaire('cni', $this->utils->securite_xss($_POST['piece']), 'beneficiaire');
                break;
            case 'codeben':
                echo $this->utils_carte->verifyBeneficiaire('code', $this->utils->securite_xss($_POST['codeben']), 'carte');
                break;
            case 'numeroserie':
                echo $this->utils_carte->verifyBeneficiaire('numero_serie', $this->utils->securite_xss($_POST['numeroserie']), 'carte');
                break;
            case 'numero':
                echo $this->utils_carte->verifyBeneficiaire('numero', $this->utils->securite_xss($_POST['numero']), 'carte');
                break;
            case 'telephone':
                echo $this->utils_carte->verifyBeneficiaire('telephone', trim(str_replace(" ", "00", $this->utils->securite_xss($_POST['telephone']))), 'carte');
                break;
            case 'serie':
                echo $this->utils_carte->verifyBeneficiaire('numero_serie', $this->utils->securite_xss($_POST['serie']), 'carte');
                break;
            case 'tel':
                echo $this->utils_carte->verifyBeneficiaire('telephone', $this->utils->securite_xss($_POST['tel']), 'carte');
                break;
        }
    }

    /***************  Veifier beneficiare **************************/
    public function test2()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $var = $this->utils->securite_xss($_POST['var']);
        switch ($var) {
            case 'email':
                echo $this->utils_carte->verifyBeneficiaire2('email', $this->utils->securite_xss($_POST['email']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'beneficiaire');
                break;
            case 'piece':
                echo $this->utils_carte->verifyBeneficiaire2('cni', $this->utils->securite_xss($_POST['piece']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'beneficiaire');
                break;
            case 'codeben':
                echo $this->utils_carte->verifyBeneficiaire2('code', $this->utils->securite_xss($_POST['codeben']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'numeroserie':
                echo $this->utils_carte->verifyBeneficiaire2('numero_serie', $this->utils->securite_xss($_POST['numeroserie']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'numero':
                echo $this->utils_carte->verifyBeneficiaire2('numero', $this->utils->securite_xss($_POST['numero']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'telephone':
                echo $this->utils_carte->verifyBeneficiaire2('telephone', trim(str_replace(" ", "00", $this->utils->securite_xss($_POST['telephone']))), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'serie':
                echo $this->utils_carte->verifyBeneficiaire2('numero_serie', $this->utils->securite_xss($_POST['serie']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'tel':
                echo $this->utils_carte->verifyBeneficiaire2('telephone', $this->utils->securite_xss($_POST['tel']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
        }
    }


    public function region()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $this->utils_carte->departement($this->utils->securite_xss($_POST['region']));
    }

    public function getDepByRegion()
    {
        $dep = $this->utils_carte->getDepartementByIdRegions($_POST['region']);
        echo $dep;
    }

    public function getRegionByPays()
    {
        $dep = $this->utils_carte->getRegionsByIdPays($this->utils->securite_xss($_POST['pays']));
        echo $dep;
    }

    public function enrollerCarte(
                                    $prenom,
                                    $prenom1,
                                    $nom,
                                    $sexe,
                                    $datenais,
                                    $email,
                                    $profession,
                                    $adresse,
                                    $adresse1,
                                    $adresse2,
                                    $typepiece,
                                    $piece,
                                    $datedelivrancepiece,
                                    $pays,
                                    $nationalite,
                                    $region,
                                    $departement,
                                    $codepostal,
                                    $telfixe,
                                    $typecarte,
                                    $telephone,
                                    $embossage,
                                    $numeroserie,
                                    $dateexpirationcarte,
                                    $commentaire,
                                    $user_creation,
                                    $agence,
                                    $prenomuser,
                                    $nomuser,
                                    $idtransaction
    ){

        $date = date('Y-m-d H:i:s');
        $regionenrollement = $this->utils_carte->regionourEnrollement($region);
        $paysenrollement = $this->utils_carte->paysPourEnrollement($pays);
        $typecnienrollement = $this->utils_carte->cniPourEnrollement($typepiece);
        $lastName = $this->utils->trimUltime($nom);
        $firstName = $this->utils->trimUltime($prenom);
        $datenaisInsert = $datenais;
        $datenais = $this->utils_carte->dateFormatEnrollement($datenais);
        if($prenom1 != '')
            $middleName = $this->utils->trimUltime($prenom1);
        else
            $middleName = '';

        if($adresse1 != '')
            $adresse1 = $this->utils->trimUltime($adresse1);
        else
            $adresse1 = '';

        if($adresse2 != '')
            $adresse2 = $this->utils->trimUltime($adresse2);
        else
            $adresse2 = '';
        $cni = $this->utils->trimUltime($piece);
        if($email != '')
            $email = $this->utils->trimUltime($email);
        else
            $email = '';

        if($embossage != '')
            $preferedName = $this->utils->trimUltime($embossage);
        else
            $preferedName = $firstName.$lastName;

        if($codepostal != '')
            $codepostal = $this->utils->trimUltime($codepostal);
        else
            $codepostal = $agence;
        $numeroserie=str_pad((intval($numeroserie)), 10, "0", STR_PAD_LEFT);

        $mobileNumber = $this->utils->trimUltime($telephone);
        $adresse = $this->utils->trimUltime($adresse);


        $SubCompany = 31732;
        $CustomerSource = 'SKYE';


        $donnees = $numeroserie . "-" . $firstName . "-" . $middleName . "-" . $lastName . "-" . $preferedName . "-" . $adresse . "-" . $adresse1 . "-" . $adresse2 . "-" . $regionenrollement . "-" . $paysenrollement . "-" . $regionenrollement . "-" . $codepostal . "-" . $datenais . "-" . $typecnienrollement . "-" . $cni . "-" . $mobileNumber . "-" . $email;
        $numeroacarte = $this->utils_carte->CarteExiste($numeroserie);
        $idlotcarte = $this->utils_carte->lotDistribution($agence, $numeroserie);

        $idlotristour=$this->utils_carte->lotDistribution2($agence, $numeroserie);
        if($idlotristour>0){
            $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, Carte retourne','',$user_creation,$prenomuser,$nomuser);
            return 99;
        }
        if ($numeroacarte == -1) {
            if ($this->utils_carte->verifierBeneficiaire($numeroserie, $telephone) == 1) {
                $errorCodes = -1;
                $card = $this->api_gtp->RegistrationCard($numeroserie, $idtransaction, $firstName, $middleName, $lastName, $preferedName,
                    $adresse, $adresse1, $adresse2, $regionenrollement, $paysenrollement, $regionenrollement,
                    $codepostal, $datenais, $typecnienrollement, $cni, $mobileNumber, $email, $CustomerSource, $SubCompany);


                $montant = 0;
                if ($typecarte == 1) {
                    $montant = 3000;
                    $commision = 2000;
                    $service = 18;
                }
                if ($typecarte == 2) {
                    $montant = 6000;
                    $commision = 4000;
                    $service = 19;
                }

                $json = json_decode($card);

                if(is_object($json))
                {
                    if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $errorCodes = $json->{'ResponseData'}->{'ErrorNumber'};
                        $errorMessage = $json->{'ResponseData'}->{'ErrorMessage'};
                        $this->utils_carte->insertTransaction($idtransaction, $montant, 1, $service, $numeroserie, $errorCodes.' '.$errorMessage, $commision,'0000ECHEC',$user_creation,$agence,$date);
                        $this->utils->log_journal('Enrollement beneficiaire', $donnees,'Enrollement beneficiaire echoue ' . ': errorCodes-'.$errorCodes.' : errorMessage-'.$errorMessage,'',$user_creation,$prenomuser,$nomuser);
                        return $card;
                    }
                    elseif(isset($json->{'ResponseData'}->{'RegistrationCustomerID'}))//if(isset($json->{'ResponseData'}->{'ErrorNumber'}))
                    {
                        $cardId = $json->{'ResponseData'}->{'RegistrationCustomerID'}.$json->{'ResponseData'}->{'RegistrationLast4Digits'};
                        $this->utils_carte->updateLotDistribution($idlotcarte);

                        $result1 = $this->utils_carte->insertBeneficiaire(
                            $prenom, $prenom1, $nom, $sexe, $datenaisInsert, $email, $profession, $adresse,
                            $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                            $nationalite, $region,$departement, $codepostal, $telfixe,
                            $commentaire, $errorCodes, $errorMessage= 'ok',$user_creation,$agence,$date);
                        $lastCarte=0;

                        if ($result1 > 0) {
                            $result2 = $this->utils_carte->insertCarte($result1, $cardId, $typecarte, $telephone, $embossage, $numeroserie,$dateexpirationcarte,$user_creation,$agence,$date);
                            if ($result2) {
                                $lastCarte=$this->utils_carte->lastInsertCarte($user_creation,$agence);
                                $this->utils_carte->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                                $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire réussi','',$user_creation,$prenomuser,$nomuser);
                                return 1;
                            }
                            else {
                                $this->utils_carte->insertCarte($result1, $cardId, $typecarte, $telephone, $embossage, $numeroserie, $dateexpirationcarte,$user_creation,$agence,$date);
                                $this->utils_carte->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                                $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire reussi mais elle n\'a pas ete enregistre dans la table carte','',$user_creation,$prenomuser,$nomuser);
                                return 2; //Erreur insertion carte
                            }
                        }
                        else {
                            $this->utils_carte->insertEchecBenef($prenom, $prenom1, $nom, $sexe, $datenaisInsert, $email, $profession, $adresse,
                                $adresse1, $adresse2, $typepiece, $piece, $datedelivrancepiece, $pays,
                                $nationalite, $region,$departement, $codepostal, $telfixe,
                                $commentaire, $errorCodes, $errorMessage= 'Erreur insertion beneficiaire',$user_creation,$agence,$date);
                            $this->utils_carte->insertTransaction($idtransaction, $montant, 1, $service, $lastCarte, $errorCodes, $commision,$json->{'TransactionID'},$user_creation,$agence,$date);
                            $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire reussi mais elle n\'a pas ete enregistre dans la table beneficiare et ni celle de carte','',$user_creation,$prenomuser,$nomuser);
                            return 3; //Erreur insertion beneficiaire
                        }
                    }else{
                        $this->utils_carte->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision);
                        $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                        return 4; //Erreur web service
                    }
                }
                else{
                    $this->utils_carte->insertTransaction($idtransaction, $montant, 0, $service, $numeroserie, $errorCodes, $commision);
                    $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue' . ': retour non objet','',$user_creation,$prenomuser,$nomuser);
                    return 4; //Erreur web service
                }
            }
            else {
                $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, le numero de telephone ou le numero de serie existe deja dans la base','',$user_creation,$prenomuser,$nomuser);
                return 5;
            }
        }
        else {
            $this->utils->log_journal('Enrollement beneficiaire', $donnees, 'Enrollement beneficiaire echoue, le numero de serie existe deja dans la base','',$user_creation,$prenomuser,$nomuser);
            return 6; //Erreur numero de carte
        }

    }

    /*********Liste Beneficiaire*********/
    public function beneficiaire($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($id[0]) == 'bon') {
            $type_alert = 'alert-success';
            $alert = 'Compte créé avec succès';
        } else {
            $type_alert = 'alert-danger';
            $alert = base64_decode($id[0]);
        }
        $params = array('view' => 'frontend/cartes/beneficiaire','alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params,$data);
    }

    /***************** processing Beneficiaires *********************/
    public function processingBenef()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $prenomuser = $obj->getPrenom();
        $nomuser = $obj->getNom();
        $agence = $obj->getFk_agence();
        $fkprofil = $obj->getFk_profil();
        $DR = $obj->getDR();
        $requestData= $_REQUEST;

        $columns = array( 
        // datatable column index  => database column name
                0 =>'prenom',
                1 => 'nom',
                2 => 'cni',
                3 => 'numero',
                4 => 'numero_serie',
                5 => 'adresse',
                6 => 'idbenef'
        );

            $next = '';
            $where = '';
            $type_profil=$this->utils->typeProfil($fkprofil);

            if($type_profil==1)
            {
                $where.=" AND beneficiaire.user_creation=".$user_creation;
            }
            if($type_profil==2 ||$type_profil==3 || $type_profil==4)
            {
                $where.=" AND beneficiaire.fk_agence=".$agence;
            }

            if($type_profil==6)
            {
                $next.=" LEFT OUTER JOIN agence ";
                $next.=" ON beneficiaire.fk_agence=agence.rowid";
                $next.=" LEFT OUTER JOIN region";
                $next.=" ON agence.region=region.idregion";
                $where.=" AND region.DR=".$DR;

            }

        // getting total number records without any search
        $sql = "SELECT beneficiaire.`rowid` as idbenef, beneficiaire.nom, beneficiaire.prenom,beneficiaire.prenom1, beneficiaire.cni,
                 beneficiaire.date_delivrance, beneficiaire.adresse, beneficiaire.telephone_fixe, beneficiaire.email,
                 beneficiaire.date_nais, beneficiaire.statut as statutbenef, carte.numero, carte.numero_serie,  carte.statut as statutcarte, carte.typecompte";
        $sql.=" FROM beneficiaire, carte";
        $sql.=" WHERE beneficiaire.`rowid` = carte.beneficiaire_rowid AND beneficiaire.user_creation = :rowid";
        $sql.= $next;
        $sql.=" AND carte.etat = :etat";
        $sql.= $where;

        $user = $this->connexion->prepare($sql);
        $user->execute(
                array(
                        "etat" => 1,
                        "rowid" => $user_creation
                )
        );


        $rows = $user->fetchAll();
        $totalData = $user->rowCount(); 
        //$totalData = 0;
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

        $sql = "SELECT beneficiaire.`rowid` as idbenef, beneficiaire.nom, beneficiaire.prenom, beneficiaire.prenom1, beneficiaire.cni,
                     beneficiaire.date_delivrance, beneficiaire.adresse, beneficiaire.telephone_fixe, beneficiaire.email,
                     beneficiaire.date_nais, beneficiaire.statut as statutbenef, carte.numero, carte.numero_serie, carte.statut as statutcarte, carte.typecompte";
        $sql.=" FROM beneficiaire, carte";
        $sql.=" WHERE beneficiaire.`rowid` = carte.beneficiaire_rowid AND beneficiaire.user_creation = :rowid";
        $sql.= $next;
        $sql.=" AND 1=1";
        $sql.=" AND carte.etat = :etat";
        $sql.= $where;

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( beneficiaire.nom LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.prenom LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.prenom1 LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.cni LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.date_delivrance LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.adresse LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.telephone_fixe LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.email LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.matricule LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR beneficiaire.date_nais LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR carte.numero LIKE '".$requestData['search']['value']."%' )";
        }
        $user = $this->connexion->prepare($sql);
        $user->execute(
                    array(
                        "etat" => 1,
                        "rowid" => $user_creation
                    )
                );
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount(); 

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->connexion->prepare($sql);
        $user->execute(
                    array(
                        "etat" => 1,
                        "rowid" => $user_creation
                    )
                );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array(); 
            if($row["prenom1"] != ''){
                $nestedData[] = $row["prenom"].' '.$row["prenom1"];
            }
            else{
                $nestedData[] = $row["prenom"];
            }

            $nestedData[] = $row["nom"];
            $nestedData[] = $row["email"];
            $nestedData[] = $row["adresse"];
            if($row['statutbenef'] == 1){
                $nestedData[] = '<span class="text-success">Activé</span>';
            }
            else{
                $nestedData[] =  '<span class="text-danger">Désactivé</span>';
            }

            if($row["typecompte"] == 1) $nestedData[] = "CARTE"; else $nestedData[] = "COMPTE";

            $nestedData[] = "<a  href='".ROOT."carte/detailBenef/".base64_encode($row["idbenef"])."' ><i class='fa fa-search'></i></a>";
            
            $data[] = $nestedData;
        }

        $json_data = array(
                    "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval( $totalData ),  // total number of records
                    "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $data   // total data array
                    );

        echo json_encode($json_data);  // send data as json format
    }

    /*********detail Beneficiaire********/
    public function detailBenef($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['benef']= $this->utils_carte->getBeneficiaireById(base64_decode($id[0]));

        $params = array('view' => 'frontend/cartes/detail-beneficiaire');
        $this->view($params,$data);
    }

    /************Jula************/

    public function ventecartejula($params)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $taille = count($params);
        if ($taille > 0) {
            if ($params[0] == sha1('solde_insuffisant')) {
                $paramsview = array('view' => 'frontend/cartes/jula', 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('erreur_ajout_vente')) {
                $paramsview = array('view' => 'frontend/cartes/jula', 'alert' => $data['lang']['vente_carte_jula_echec'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_insuffisant')) {
                $paramsview = array('view' => 'frontend/cartes/jula', 'alert' => $data['lang']['stockjula_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_epuise')) {
                $paramsview = array('view' => 'frontend/cartes/jula', 'alert' => $data['lang']['stockjula_insuffisant'], 'type-alert' => 'danger');
            }
        } else {
            $paramsview = array('view' => 'frontend/cartes/jula');
        }
        $this->view($paramsview, $data);
    }

    public function stockJULA()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $montant = $this->utils->securite_xss($_POST['montant']);
        echo json_encode($this->utils_carte->getStockJULA(1, $montant));
    }

    public function cartejula($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['cartes'] = $this->utils_carte->cartesVente(base64_decode($id[0]));

        if (intval(base64_decode($id[0])) > 0) {
            $type_alert = 'success';
            $alert = $data['lang']['carte_vendues'];
        } else {
            $type_alert = 'error';
            $alert = $data['lang']['echec_vente_carte'];
        }

        $paramsview = array('view' => 'frontend/cartes/jula','alert' => $alert,'type-alert' => $type_alert);
        $this->view($paramsview, $data);
    }

    public function vendrecarte()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $agence = $obj->getFk_agence();
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant']);
        $nombre = $this->utils->securite_xss($_POST['nombre']);
        $commission = $this->utils_carte->commissionJULA($montant);
        $soldeagence = $this->utils_carte->soldeAgence(1);
        $montant_ttc = intval(intval(intval($montant) + intval($commission)) * $nombre);


        if ($soldeagence >= $montant_ttc)
        {
            $stock = $this->utils_carte->getStockJULA(1, $montant);
            if ($stock >= $nombre)
            {
                $result = 0;
                $resultat = $this->utils_carte->vendreCarteJULA($montant, $commission, $nombre, 1, $user_creation);

                if(count($resultat)>0)
                {
                    $result = $resultat['num_transac'] ;
                }else{
                    $result =  $resultat;
                }
                if ($result > 0)
                {
                    $this->rediriger("carte", "cartejula/" . base64_encode($result));
                }
                else if ($result == -2)
                {
                    $this->rediriger("carte", "ventecartejula/" . sha1('stock_epuise'));
                }
                else {
                    $this->rediriger("carte", "ventecartejula/" . sha1('erreur_ajout_vente'));
                }
            }
            else {
                $this->rediriger("carte", "ventecartejula/" . sha1('stock_insuffisant'));
            }
        }
        else {
            $this->rediriger("carte", "ventecartejula/" . sha1('solde_insuffisant'));
        }
    }

}