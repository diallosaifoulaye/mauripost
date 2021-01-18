<?php
require_once(__DIR__ . '/../../vendor/ApiGTP/ApiBanque.php');
ini_set('display_errors', 1);

class CompteController extends \app\core\BaseController
{
    public $compteModel;
    private $agenceModel;
    private $carteModel;
    private $userConnecter;
    public $api_gtp;


    public function __construct()
    {
        parent::__construct();
        $this->compteModel = $this->model('CompteModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->carteModel = $this->model('CarteModel');
        $this->api_gtp = new  ApiBAnque();
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES BENEFICIARES                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    /*********Liste Beneficiaire*********/
    public function index()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Acces_module($this->userConnecter->profil, 2));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/accueil');
        $this->view($params, $data);
    }

    /*********Liste Beneficiaire*********/
    public function beneficiaires()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(37, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/beneficiaire');
        $this->view($params, $data);
    }

    /***************** processing Beneficiaires *********************/
    public function processingBenef()
    {
        $param = [
            "button" => [
                [ROOT . "compte/detailBenef/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "allBenef", $param);
    }

    /*********detail Beneficiaire********/
    public function detailBenef($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['benef'] = $this->compteModel->getBeneficiaireById(base64_decode($id[0]));

        if (base64_decode($id[1]) == 1) {
            $type_alert = 'success';
            $alert = $data['lang']['message_success_update_beneficiaire'];
        }
        if (base64_decode($id[1]) == -1) {
            $type_alert = 'error';
            $alert = $data['lang']['message_error_update_beneficiaire'];
        }

        $params = array('view' => 'compte/detail-beneficiaire', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);

    }

    public function updateBenef($id)
    {
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $prenom2 = $this->utils->securite_xss($_POST['prenom1']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $datenaissance = $this->utils->securite_xss($_POST['datenaissance']);
        $cni = $this->utils->securite_xss($_POST['cni']);
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['tel'])));;
        $email = $this->utils->securite_xss($_POST['email']);
        $adresse = $this->utils->securite_xss($_POST['adresse']);

        $commentaire = $this->utils->securite_xss($_POST['comment']);
        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['idBenef']);

        $update = $this->compteModel->updateBeneficiaire($prenom, $prenom2, $nom, $datenaissance, $cni, $telephone, $email, $adresse, $commentaire, $user_modification, $rowid);
        if ($update == 1) {
            $this->utils->log_journal('Modification Beneficiaire', 'Prenom:' . $prenom . ' Nom:' . $nom . ' Email:' . $email . ' Tel:' . $telephone, 'succés', 1, $user_modification);

        } else {
            $this->utils->log_journal('Modification Beneficiaire', 'Prenom:' . $prenom . ' Nom:' . $nom . ' Email:' . $email . ' Tel:' . $telephone, 'echec', 1, $user_modification);

        }
        $this->rediriger('compte', 'detailBenef/' . base64_encode($rowid) . '/' . base64_encode($update));
    }


    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION DES COMPTES                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////
    /*********detail Beneficiaire********/
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
        $user_creation = $this->userConnecter->rowid;
        $agence = $this->userConnecter->fk_agence;
        $response = $this->api_numherit->creerCompte($username, $token, $nom, $prenom, $adresse, $email, $password, $dateNaissance, $telephone, $user_creation, $agence, $sexe, $typePiece, $numPiece, $dateDeliv);

        $tab = json_decode($response);
        if (is_object($tab)) {
            if ($tab->{'statusCode'} == '000') {
                @$num_transac = $this->utils->generation_numTransaction();
                @$idcompte = $this->utils->getCarteTelephone($telephone);
                @$this->utils->SaveTransaction($num_transac, $service = ID_SERVICE_CREATION_COMPTE, $montant = 0, $idcompte, $user_creation, $statut = 0, 'SUCCESS: CREATION COMPTE', $frais = 0, $agence, 0);
                $true = 'bon';
                $this->utils->log_journal('Creation compte', 'Client:' . $nom . ' ' . $prenom . ' Agence:' . $agence, 'succes', 1, $user_creation);
                $this->rediriger('compte', 'compte/' . base64_encode($true));
            } else {
                $this->utils->log_journal('Creation compte', 'Client:' . $nom . ' ' . $prenom . ' Agence:' . $agence, 'echec', 1, $user_creation);
                $this->rediriger('compte', 'compte/' . base64_encode($tab->{'statusMessage'}));
            }
        }
    }

    /**
     * @param $id
     * PAGE CONTENANT LA LISTE DES PROFILS
     */
    public function compte($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($id[0]) == 'bon') {
            $type_alert = 'success';
            $alert = 'Compte créé avec succès';
        } else {
            $type_alert = 'error';
            $alert = base64_decode($id[0]);
        }
        $paramsview = array('view' => 'compte/beneficiaire', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($paramsview, $data);
    }

    public function createCompte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(9, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['typepiece'] = $this->utils->typepiece();
        $paramsview = array('view' => 'compte/compte');
        $this->view($paramsview, $data);
    }


    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION DES CARTES                                         //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function createCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(9, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profession'] = $this->utils->professions();
        $data['typepiece'] = $this->utils->typepiece();
        $data['pays'] = $this->utils->listePays();
        $data['nationalites'] = $this->utils->nationalites();
        $data['regions'] = $this->utils->allRegionByPays();
        $paramsview = array('view' => 'compte/new-carte');
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

        if ($this->carteModel->verifierNumSerie($numeroserie) === 1) {

            $ResultatEnroller = $this->carteModel->enrollerCarte($prenom,
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

            if ($ResultatEnroller === 1) {
                $this->carteModel->venteCarte($numeroserie, $agence);
            }

            $this->rediriger('compte', 'resultInsertCarte/' . base64_encode($ResultatEnroller) . '/' . base64_encode($telephone));
        } else {
            $this->rediriger('compte', 'resultInsertCarte/' . base64_encode(22) . '/' . base64_encode($telephone));
        }
    }

    public function resultInsertCarte($id)
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $resultat = base64_decode($id[0]);

        if ($resultat == 1) {
            $tel = base64_decode($id[1]);
            $data['benef'] = $this->carteModel->beneficiaireByNumeroTel($tel);

            $type_alert = 'success';
            $alert = $data['lang']['message_enrollement'];
            $params = array('view' => 'compte/new-carte-success', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 2 || $resultat == 3) {
            $type_alert = 'error';
            $alert = $data['lang']['message_enrollement_1'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 4) {
            $type_alert = 'error';
            $alert = $data['lang']['message_enrollement_2'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 5) {
            $type_alert = 'error';
            $alert = $data['lang']['message_enrollement_4'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 6) {
            $type_alert = 'error';
            $alert = $data['lang']['message_enrollement_4'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);

        } else if ($resultat == 99) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_retournee'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 22) {
            $type_alert = 'error';
            $alert = $data['lang']['agency_non_authaurize_to_active'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 33) {
            $type_alert = 'error';
            $alert = $data['lang']['type_carte_incorrect'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 44) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_deja_vendue'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 55) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_non_auth_vente'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else {
            $type_alert = 'error';
            $alert = $data['lang']['erreur_enrolement'];
            $params = array('view' => 'compte/new-carte-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        }
    }

    public function carteActive()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(38, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/hist-carte-actif');
        $this->view($params, $data);
    }

    public function carteInactive()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(39, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/hist-carte-inactif');
        $this->view($params, $data);
    }

    /***************** processing Carte Active *********************/
    public function processingCarteActive()
    {
        $requestData = $_REQUEST;


        $columns = array(
// datatable column index  => database column name
            0 => 'numero',
            1 => 'telephone',
            2 => 'prenom',
            3 => 'date_activation',
            4 => 'date_expiration',
        );


        $next = '';
        $where = '';
        //$type_profil = $this->utils->typeProfil($_SESSION['profil']);

        if ($this->userConnecter->type_profil == 1 || $this->userConnecter->type_profil==3) {
            $where .= " AND beneficiaire.user_creation=" . $this->userConnecter->rowid;
        }
        if ($this->userConnecter->type_profil == 4) {
            $where .= " AND beneficiaire.fk_agence=" . $this->userConnecter->fk_agence;
        }

        if ($this->userConnecter->type_profil == 1 || $this->userConnecter->type_profil==2) {
            $next .= " LEFT OUTER JOIN agence ";
            $next .= " ON beneficiaire.fk_agence=agence.rowid";
            $next .= " LEFT OUTER JOIN region";
            $next .= " ON agence.region=region.idregion";

        }

// getting total number records without any search
        $sql = "SELECT carte.rowid, carte.numero, carte.telephone, carte.date_activation, carte.date_expiration, carte.statut, beneficiaire.prenom, beneficiaire.nom";
        $sql .= " FROM carte";
        $sql .= " LEFT OUTER JOIN beneficiaire";
        $sql .= " ON carte.beneficiaire_rowid = beneficiaire.`rowid`";
        $sql .= $next;
        $sql .= " WHERE carte.etat = :etat";
        $sql .= " AND carte.statut = :statut";
        $sql .= $where;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(1),
            )
        );

        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT carte.rowid, carte.numero, carte.telephone, carte.date_activation, carte.date_expiration, carte.statut, beneficiaire.prenom, beneficiaire.nom";
        $sql .= " FROM carte";
        $sql .= " LEFT OUTER JOIN beneficiaire";
        $sql .= " ON carte.beneficiaire_rowid = beneficiaire.`rowid`";
        $sql .= $next;
        $sql .= " WHERE 1=1";
        $sql .= " AND carte.etat = :etat";
        $sql .= " AND carte.statut = :statut";
        $sql .= $where;

        if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( beneficiaire.nom LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR beneficiaire.prenom LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.numero LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.telephone LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.date_activation LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.date_expiration LIKE '" . $requestData['search']['value'] . "%' )";
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(1),
            )
        );
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'] . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(1),
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach ($rows as $row) {  // preparing an array
            $nestedData = array();
            $nestedData[] = $this->utils->truncate_carte($row["numero"]);
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["prenom"] . ' ' . $row["nom"];
            $nestedData[] = $this->utils->date_fr4($row["date_activation"]);
            $nestedData[] = $this->utils->date_fr2($row["date_expiration"]);

            $data[] = $nestedData;
        }


        $json_data = array(
            "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format

    }

    /***************** processing Carte Inactive *********************/
    public function processingCarteInactive()
    {
        $requestData = $_REQUEST;


        $columns = array(
            0 => 'numero',
            1 => 'telephone',
            2 => 'prenom',
            3 => 'date_activation',
            4 => 'date_expiration',
        );


        $next = '';
        $where = '';
        $type_profil = $this->utils->typeProfil($_SESSION['profil']);

        if ($type_profil == 1) {
            $where .= " AND beneficiaire.user_creation=" . $this->userConnecter->rowid;
        }
        if ($type_profil == 2 || $type_profil == 3 || $type_profil == 4) {
            $where .= " AND beneficiaire.fk_agence=" . $this->userConnecter->fk_agence;
        }

        if ($type_profil == 6) {
            $next .= " LEFT OUTER JOIN agence ";
            $next .= " ON beneficiaire.fk_agence=agence.rowid";
            $next .= " LEFT OUTER JOIN region";
            $next .= " ON agence.region=region.idregion";

        }

        // getting total number records without any search
        $sql = "SELECT carte.rowid, carte.numero, carte.telephone, carte.date_activation, carte.date_expiration, carte.statut, beneficiaire.prenom, beneficiaire.nom";
        $sql .= " FROM carte";
        $sql .= " LEFT OUTER JOIN beneficiaire";
        $sql .= " ON carte.beneficiaire_rowid = beneficiaire.`rowid`";
        $sql .= $next;
        $sql .= " WHERE carte.etat = :etat";
        $sql .= " AND carte.statut = :statut";
        $sql .= $where;

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(0),
            )
        );


        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT carte.rowid, carte.numero, carte.telephone, carte.date_activation, carte.date_expiration, carte.statut, beneficiaire.prenom, beneficiaire.nom";
        $sql .= " FROM carte";
        $sql .= " LEFT OUTER JOIN beneficiaire";
        $sql .= " ON carte.beneficiaire_rowid = beneficiaire.`rowid`";
        $sql .= $next;
        $sql .= " WHERE 1=1";
        $sql .= " AND carte.etat = :etat";
        $sql .= " AND carte.statut = :statut";
        $sql .= $where;

        if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( beneficiaire.nom LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR beneficiaire.prenom LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.numero LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.telephone LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.date_activation LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR carte.date_expiration LIKE '" . $requestData['search']['value'] . "%' )";
        }
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(0),
            )
        );
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'] . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";

        $user = $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
                "statut" => intval(0),
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach ($rows as $row) {  // preparing an array
            $nestedData = array();
            $nestedData[] = $this->utils->truncate_carte($row["numero"]);
            $nestedData[] = $this->utils->truncate_carte($row["telephone"]);
            $nestedData[] = $row["prenom"] . ' ' . $row["nom"];
            $nestedData[] = $this->utils->date_fr4($row["date_activation"]);
            $nestedData[] = $this->utils->date_fr2($row["date_expiration"]);

            $data[] = $nestedData;
        }


        $json_data = array(
            "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format

    }

    /***************  Veifier beneficiare **************************/
    public function test()
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $var = $this->utils->securite_xss($_POST['var']);
        switch ($var) {
            case 'email':
                echo $this->carteModel->verifyBeneficiaire('email', $this->utils->securite_xss($_POST['email']), 'beneficiaire');
                break;
            case 'piece':
                echo $this->carteModel->verifyBeneficiaire('cni', $this->utils->securite_xss($_POST['piece']), 'beneficiaire');
                break;
            case 'codeben':
                echo $this->carteModel->verifyBeneficiaire('code', $this->utils->securite_xss($_POST['codeben']), 'carte');
                break;
            case 'numeroserie':
                echo $this->carteModel->verifyBeneficiaire('numero_serie', $this->utils->securite_xss($_POST['numeroserie']), 'carte');
                break;
            case 'numero':
                echo $this->carteModel->verifyBeneficiaire('numero', $this->utils->securite_xss($_POST['numero']), 'carte');
                break;
            case 'telephone':
                echo $this->carteModel->verifyBeneficiaire('telephone', trim(str_replace(" ", "00", $this->utils->securite_xss($_POST['telephone']))), 'carte');
                break;
            case 'serie':
                echo $this->carteModel->verifyBeneficiaire('numero_serie', $this->utils->securite_xss($_POST['serie']), 'carte');
                break;
            case 'tel':
                echo $this->carteModel->verifyBeneficiaire('telephone', $this->utils->securite_xss($_POST['tel']), 'carte');
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
                echo $this->carteModel->verifyBeneficiaire2('email', $this->utils->securite_xss($_POST['email']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'beneficiaire');
                break;
            case 'piece':
                echo $this->carteModel->verifyBeneficiaire2('cni', $this->utils->securite_xss($_POST['piece']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'beneficiaire');
                break;
            case 'codeben':
                echo $this->carteModel->verifyBeneficiaire2('code', $this->utils->securite_xss($_POST['codeben']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'numeroserie':
                echo $this->carteModel->verifyBeneficiaire2('numero_serie', $this->utils->securite_xss($_POST['numeroserie']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'numero':
                echo $this->carteModel->verifyBeneficiaire2('numero', $this->utils->securite_xss($_POST['numero']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'telephone':
                echo $this->carteModel->verifyBeneficiaire2('telephone', trim(str_replace(" ", "00", $this->utils->securite_xss($_POST['telephone']))), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'serie':
                echo $this->carteModel->verifyBeneficiaire2('numero_serie', $this->utils->securite_xss($_POST['serie']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
            case 'tel':
                echo $this->carteModel->verifyBeneficiaire2('telephone', $this->utils->securite_xss($_POST['tel']), base64_decode($this->utils->securite_xss($_POST['idbenef'])), 'carte');
                break;
        }
    }


    public function region()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $this->carteModel->departement($this->utils->securite_xss($_POST['region']));
    }

    public function getDepByRegion()
    {
        $dep = $this->carteModel->getDepartementByIdRegions($_POST['region']);
        echo $dep;
    }

    public function getRegionByPays()
    {
        $dep = $this->carteModel->getRegionsByIdPays($this->utils->securite_xss($_POST['pays']));
        echo $dep;
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             LIER CARTE A COMPTE                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function indexlcac()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(99, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/liercac-search');
        $this->view($params, $data);
    }


    public function searchBeneficiaire()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profession'] = $this->utils->professions();
        $data['typepiece'] = $this->utils->typepiece();
        $data['pays'] = $this->utils->listePays();
        $data['nationalites'] = $this->utils->nationalites();
        $data['regions'] = $this->utils->allRegion();
        $tel = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['tel'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone2($tel);
        $params = array('view' => 'compte/liercac');
        $this->view($params, $data);
    }

    public function updateCoTCarte()
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
        $telfixe = trim($this->utils->securite_xss($_POST['telfixe']));
        $typecarte = trim($this->utils->securite_xss($_POST['typecarte']));
        $telephone = trim($this->utils->securite_xss($_POST['telephone']));
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
        $idBenef = trim(base64_decode($this->utils->securite_xss($_POST['idBenef'])));
        $idcarte = trim(base64_decode($this->utils->securite_xss($_POST['idcarte'])));

        if ($sexe === 'Masculin')
            $sexe = 'M';
        if ($sexe === 'Féminin')
            $sexe = 'F';

        if ($this->carteModel->verifierNumSerie($numeroserie) === 1) {

            $ResultatEnroller = $this->carteModel->lierCarteACompte($prenom,
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
                $idtransaction, $idBenef, $idcarte);
            if ($ResultatEnroller === 1) {
                $this->carteModel->venteCarte($numeroserie, $agence);
            }
            $this->rediriger('compte', 'resultLierCarteToCompte/' . base64_encode($ResultatEnroller) . '/' . base64_encode($telephone));
        } else {
            $this->rediriger('compte', 'resultLierCarteToCompte/' . base64_encode(55) . '/' . base64_encode($telephone));
        }
    }

    public function resultLierCarteToCompte($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $resultat = base64_decode($id[0]);

        if ($resultat == 1) {
            $tel = base64_decode($id[1]);
            $data['benef'] = $this->carteModel->beneficiaireByNumeroTel($tel);

            $type_alert = 'success';
            $alert = $data['lang']['message_lcac_enrollement'];
            $params = array('view' => 'compte/lcac-success', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 2 || $resultat == 3) {
            $type_alert = 'error';
            $alert = $data['lang']['message_lcac_enrollement_1'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 4) {
            $type_alert = 'error';
            $alert = $data['lang']['message_lcac_enrollement_2'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 6) {
            $type_alert = 'error';
            $alert = $data['lang']['message_lcac_enrollement_4'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);

        } else if ($resultat == 99) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_retournee'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 22) {
            $type_alert = 'error';
            $alert = $data['lang']['agency_non_authaurize_to_active'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 33) {
            $type_alert = 'error';
            $alert = $data['lang']['type_carte_incorrect'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 44) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_deja_vendue'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else if ($resultat == 55) {
            $type_alert = 'error';
            $alert = $data['lang']['carte_non_auth_vente'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        } else {
            $type_alert = 'error';
            $alert = $data['lang']['erreur_enrolement'];
            $params = array('view' => 'compte/lcac-error', 'alert' => $alert, 'type-alert' => $type_alert);
            $this->view($params, $data);
        }
    }


    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION OPERATION DES CARTES                               //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    /*********search Beneficiaire********/
    public function searchCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(40, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/search-carte');
        $this->view($params, $data);
    }

    /*********detail Beneficiaire********/
    public function detailBeneficiaire()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone($telephone);
        $params = array('view' => 'compte/search-carte');
        $this->view($params, $data);
    }

    /*********search Activer Beneficiaire********/
    public function searchActiveCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(42, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/active-carte-search');
        $this->view($params, $data);
    }

    /*********Activer Beneficiaire********/
    public function activerCarte()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $params = array('view' => 'compte/active-carte');
        $this->view($params, $data);
    }

    /********* Activer Beneficiaire Validation ********/
    public function activerCarteValidation()
    {
        $typecompte = $this->utils->securite_xss($_POST['typecompte']);
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $user_creation = $this->userConnecter->rowid;
        $fkagence = $this->userConnecter->fk_agence;
        $num_transac = $this->compteModel->generateNumeroTransactionOperationcarte();
        if ($typecompte == 0) {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->activerCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if ($decode_response->{'statusCode'} == 000) {
                $this->compteModel->saveOperation_carte($telephone, $decode_response->{'statusMessage'}, $decode_response->{'statusCode'}, $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, $decode_response->{'statusMessage'}, 2, $user_creation);
                $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('ok') . '/' . base64_encode($telephone));
            } else {
                $this->compteModel->saveOperation_carte($telephone, $decode_response->{'statusMessage'}, $decode_response->{'statusCode'}, $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, $decode_response->{'statusMessage'}, 2, $user_creation);
                $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('nok') . '/' . base64_encode($telephone));
            }

        } else if ($typecompte == 1) {

            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $last4digitBenef = $this->utils->returnLast4Digits($numcarte);
            $num_transact = $this->compteModel->generateNumeroTransactionOperationcarte();
            $reactive = $this->api_gtp->ReactivateCard($num_transact, $numeroserie, $last4digitBenef, $telephone);
            $jsonSource = json_decode("$reactive");


            $responseData = $jsonSource->{'ResponseData'};
            if ($responseData != NULL && is_object($responseData)) {
                if (array_key_exists('ErrorNumber', $responseData)) {
                    $errorNumber = $responseData->{'ErrorNumber'};
                    $errorMessage = $responseData->{'ErrorMessage'};
                    $this->compteModel->saveOperation_carte($telephone, $errorMessage, $errorNumber, $num_transac, $user_creation, $fkagence);
                    $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, $errorNumber . '-' . $errorMessage, 2, $user_creation);
                    $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('nok') . '/' . base64_encode($telephone));
                } else {
                    $value = $responseData->{'Success'};
                    if ($value == 'true') {
                        $statut = 1;
                        $enable = $this->compteModel->enableCarte($telephone, $user_creation);
                        if ($enable == 1) {
                            $this->compteModel->saveOperation_carte($telephone, $value . '-' . $enable . '-statut:' . $statut, $num_transac, $user_creation, $fkagence);
                            $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, $value, 2, 0 , $user_creation);
                            $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('ok') . '/' . base64_encode($telephone));
                        } else {
                            $statut = 0;
                            $this->compteModel->saveOperation_carte($telephone, $value . ' - action nok sur BD mais ok avec GTP - statut:' . $statut, 0 , $num_transac, $user_creation, $fkagence);
                            $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, $value, 2, $user_creation);
                            $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('nok') . '/' . base64_encode($telephone));
                        }
                    }
                }
            } else {
                $this->compteModel->saveOperation_carte($telephone, 'Response non object', 'non obj', $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Activation compte', 'Téléphone lié au compte:' . $telephone, '', 2, $user_creation);
                $this->rediriger('compte', 'validationActivationCompte/' . base64_encode('nok1') . '/' . base64_encode($telephone));
            }

        }
    }

    /***********Validation Activation Compte**********/
    public function validationActivationCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($return[1]);
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);

        if (base64_decode($return[0]) === 'ok') {
            $params = array('view' => 'compte/active-carte', 'title' => $data['lang']['activation_carte'], 'alert' => $data['lang']['message_activer_carte'], 'type-alert' => 'alert-success');
        } elseif (base64_decode($return[0]) === 'nok' || base64_decode($return[0]) === 'nok1') {
            $params = array('view' => 'compte/active-carte', 'title' => $data['lang']['activation_carte'], 'alert' => $data['lang']['message_erreur_activer_carte'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /*********search desactiver Beneficiaire********/
    public function searchDesactiveCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(43, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/desactive-carte-search');
        $this->view($params, $data);
    }

    /*********desactiver Beneficiaire********/
    public function desactiverCarte()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $params = array('view' => 'compte/desactive-carte');
        $this->view($params, $data);
    }

    /********* Desactiver Beneficiaire Validation ********/
    public function desactiverCarteValidation()
    {

        $typecompte = $this->utils->securite_xss($_POST['typecompte']);
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $user_creation = $this->userConnecter->rowid;
        $fkagence = $this->userConnecter->fk_agence;
        $num_transac = $this->compteModel->generateNumeroTransactionOperationcarte();
        if ($typecompte == 0) {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->desactiverCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if ($decode_response->{'statusCode'} == 000) {
                $this->compteModel->saveOperation_carte($telephone, $decode_response->{'statusMessage'}, $decode_response->{'statusCode'}, $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, $decode_response->{'statusMessage'}, 2, $user_creation);
                $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('ok') . '/' . base64_encode($telephone));
            } else {
                $this->compteModel->saveOperation_carte($telephone, $decode_response->{'statusMessage'}, $decode_response->{'statusCode'}, $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, $decode_response->{'statusMessage'}, 2, $user_creation);
                $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('nok') . '/' . base64_encode($telephone));
            }
        } else if ($typecompte == 1) {
            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $last4digitBenef = $this->utils->returnLast4Digits($numcarte);
            $num_transact = $this->compteModel->generateNumeroTransactionOperationcarte();
            $deseactive = $this->api_gtp->DesactiveCard($num_transact, $numeroserie, $last4digitBenef, $telephone);
            //var_dump($deseactive); die;
            $jsonSource = json_decode($deseactive);
            $responseData = $jsonSource->{'ResponseData'};
            if ($responseData != NULL && is_object($responseData)) {
                if (array_key_exists('ErrorNumber', $responseData)){
                    $errorNumber = $responseData->{'ErrorNumber'};
                    $errorMessage = $responseData->{'ErrorMessage'};
                    $this->compteModel->saveOperation_carte($telephone, $errorMessage, $errorNumber, $num_transac, $user_creation, $fkagence);
                    $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, $errorNumber . '-' . $errorMessage, 2, $user_creation);
                    $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('nok2') . '/' . base64_encode($telephone));
                } else {
                    $value = $responseData->{'Success'};
                    if ($value == true) {
                        $statut = 0;
                        $deseable = $this->compteModel->deseableCarte($telephone, $user_creation);
                        if ($deseable == 1) {
                            $this->compteModel->saveOperation_carte($telephone, $value . '-' . $deseable . '-statut:' . $statut, 0 , $num_transac, $user_creation, $fkagence);
                            $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, $value, 2, $user_creation);
                            $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('ok') . '/' . base64_encode($telephone));
                        } else {
                            $statut = 1;
                            $this->compteModel->saveOperation_carte($telephone, $value . ' - action nok sur BD mais ok avec GTP - statut:' . $statut, 0 , $num_transac, $user_creation, $fkagence);
                            $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, $value, 2, $user_creation);
                            $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('nok') . '/' . base64_encode($telephone));
                        }
                    }
                }
            } else {
                $this->compteModel->saveOperation_carte($telephone, 'Response non object', 'non obj', $num_transac, $user_creation, $fkagence);
                $this->utils->log_journal('Desactivation compte', 'Téléphone lié au compte:' . $telephone, '', 2, $user_creation);
                $this->rediriger('compte', 'validationDesactivationCompte/' . base64_encode('nok1') . '/' . base64_encode($telephone));
            }

        }
    }

    /***********Validation Desactivation Compte**********/
    public function validationDesactivationCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($return[1]);
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);

        if (base64_decode($return[0]) === 'ok') {
            $params = array('view' => 'compte/desactive-carte', 'title' => $data['lang']['activation_carte'], 'alert' => $data['lang']['message_desactiver_carte'], 'type-alert' => 'alert-success');
        } elseif (base64_decode($return[0]) === 'nok' || base64_decode($return[0]) === 'nok1' || base64_decode($return[0]) === 'nok2') {
            $params = array('view' => 'compte/desactive-carte', 'title' => $data['lang']['activation_carte'], 'alert' => $data['lang']['message_erreur_desactiver_carte'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /*********search Solde Carte********/
    public function searchSoldeCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(44, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/solde-carte-search');
        $this->view($params, $data);
    }

    /*********Solde carte Beneficiaire********/
    public function soldeCarte()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $typecompte = $this->compteModel->getTypeCompte($telephone);
        if ($typecompte == 0) {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->soldeCompte($username, $token, $telephone);

            $decode_response = json_decode($response);
            if ($decode_response->{'statusCode'} == 000) {
                $data['soldeCarte'] = $decode_response->{'statusMessage'};
            } else $data['soldeCarte'] = 0;
        } else if ($typecompte == 1) {
            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $solde = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
            $json = json_decode($solde);
            $responseData = $json->{'ResponseData'};
            if ($responseData != NULL && is_object($responseData)) {
                if (array_key_exists('ErrorNumber', $responseData)) {
                    $message = $responseData->{'ErrorNumber'};
                    $data['soldeCarte'] = 0;
                } else {
                    $data['soldeCarte'] = (int)$responseData->{'Balance'};
                    $currencyCode = $responseData->{'CurrencyCode'};
                }
            } else $data['soldeCarte'] = 0;
        }
        $params = array('view' => 'compte/solde-carte');
        $this->view($params, $data);
    }

    /*********search Recharge Espece Carte********/
    public function searchRechargeEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(46, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/recharge-espece-carte-search');
        $this->view($params, $data);
    }

    /*********Recharge Espece Carte Beneficiaire********/
    public function rechargeEspeceCarte()
    {
        $fkagence = $this->userConnecter->fk_agence;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($fkagence);

        $params = array('view' => 'compte/recharge-espece-carte');
        $this->view($params, $data);
    }

    /*********Recharge Espece Code Validation********/
    public function rechargeEspeceCodeValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeAgence'] = $this->utils->securite_xss($_POST['soldeagence']);
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $data['fkagence'] = $this->userConnecter->fk_agence;
        if ($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '') {

            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $recup_tel = $this->utils->recup_tel($this->userConnecter->fk_agence);
            $code_recharge = $this->utils->generateCodeRechargement($data['fkagence']);

            $message = $data['lang']['mess_recharge_espece1'] . $code_recharge . $data['lang']['mess_recharge_espece2'] . $this->utils->number_format($data['montant']) . $data['lang']['currency'];
             @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);
             //@$this->utils->envoiCodeRechargement($this->userConnecter->email, $this->userConnecter->prenom . ' ' . $this->userConnecter->nom, $code_recharge, $data['lang']);

             /*****BALDE*****/
             @$this->utils->envoiCodeRechargement('alioubalde@numherit.com', 'Aliou' . ' ' . 'BALDE', $code_recharge, $data['lang']);
             /****BALDE*****/

            if ($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '') {

                @$this->utils->envoiCodeRechargement($recup_mail, $data['lang']['chef_agence'], $code_recharge, $data['lang']);

            }

            /* if (DEBUG === TRUE) {
                 @$this->utils->envoiCodeRechargement('ibrahima.fall@numherit.com', '', $code_recharge, $data['lang']);
             }*/
        }

        $params = array('view' => 'compte/recharge-espece-code-validation');
        $this->view($params, $data);
    }
    public function rechargeEspeceCodeValidation2()
    {

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = $this->utils->securite_xss($_POST['telephone']);
        $data['fkcarte'] = $this->utils->securite_xss($_POST['fkcarte']);
        $data['soldeAgence'] = $this->utils->securite_xss($_POST['soldeagence']);
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['montant'] = $this->utils->securite_xss($_POST['montant']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais']);
        $data['fkagence'] = $this->userConnecter->fk_agence;
        //var_dump($data['telephone']." ".$data['montant']." ".$data['frais']."" .$data['fkagence']);exit;
        if ($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '') {

            $recup_mail = $this->utils->recup_mail($this->userConnecter->fk_agence);
            $recup_tel = $this->utils->recup_tel($this->userConnecter->fk_agence);
            $code_recharge = $this->utils->generateCodeRechargement($data['fkagence']);

            $message = $data['lang']['mess_recharge_espece1'] . $code_recharge . $data['lang']['mess_recharge_espece2'] . $this->utils->number_format($data['montant']) . $data['lang']['currency'];
             @$this->utils->sendSMS($data['lang']['paositra1'], $recup_tel, $message);
             //@$this->utils->envoiCodeRechargement($recup_mail, $this->userConnecter->prenom . ' ' . $this->userConnecter->nom, $code_recharge, $data['lang']);
            if ($recup_mail != -1 && $recup_mail != -2 && $code_recharge != '') {

                @$this->utils->envoiCodeRechargement($recup_mail, $data['lang']['chef_agence'], $code_recharge, $data['lang']);

                /**********BALDE***********/
                @$this->utils->envoiCodeRechargement('alioubalde@numherit.com', $data['lang']['chef_agence'], $code_recharge, $data['lang']);
                /*********BALDE***********/
            }

           /* if (DEBUG === TRUE) {
                @$this->utils->envoiCodeRechargement('alioubalde@numherit.com', '', $code_recharge, $data['lang']);
            }*/
        }

        $params = array('view' => 'compte/recharge-espece-code-validation');
        $this->view($params, $data);
    }

    /******* Action calcul Frais Recharge ****/
    public function calculFrais()
    {
        $frais = $this->compteModel->calculTaxe($this->utils->securite_xss($_POST['montant']), $this->utils->securite_xss($_POST['service']));
        if ($frais > 0) echo $frais;
        else if ($frais == 0) echo 0;
        else echo -2;
    }

    /******* Action verifier code rechargement ****/
    public function codeRechargement()
    {
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fk_agence = $this->utils->securite_xss($_POST['fkagence']);
        $frais = $this->compteModel->verifCodeRechargement($fk_agence, $code_secret);
        if ($frais == 1) echo 1;
        elseif ($frais == 0) echo 0;
        else echo -2;
    }

    /********* Recharge Espece Carte Beneficiaire Validation ********/
    public function rechargeEspeceValidation()
    {
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $soldeAgence = $this->utils->securite_xss($_POST['soldeagence']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils->securite_xss($_POST['frais']);
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $user_creation = $this->userConnecter->rowid;
        $service = ID_SERVICE_RECHARGE_ESPECE;
        $frais = $this->compteModel->calculFrais($service, $montant);

        $numtransact = $this->utils->Generer_numtransaction();
        $statut = 0;
        $commentaire = 'Recharge Espece';

        if ($codevalidation != '' && $telephone != '' && $montant > 0 && $frais > 0 && $soldeAgence != '' && strlen($numtransact) == 15) {
            //if ($soldeAgence >= ($montant + $frais)) {
            if ($soldeAgence >= $montant) {
                $codeValidation = $this->utils->rechercherCoderechargement($codevalidation, $fkagence);
                if ($codeValidation > 0) {
                    $typecompte = $this->compteModel->getTypeCompte($telephone);
                    if ($typecompte == 0) {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->utils->getToken($userId);
                        $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                        $jjs = json_decode($soldeavant);
                        $soldeavant = $jjs->{'statusMessage'};

                        $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);

                        $decode_response = json_decode($response);
                        if ($decode_response->{'statusCode'} == 000) {
                            $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                            $jjs = json_decode($soldeapres);
                            $soldeapres = $jjs->{'statusMessage'};
                            @$this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, $operation = "CREDIT", $commentaire = "RECHARGEMENTESPECE");

                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};
                            $this->utils->changeStatutCoderechargement($codeValidation);
                            $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $crediterCarteCommission = $this->utils->crediter_carteParametrable($frais, 1);
                            if ($crediterCarteCommission == 1) {
                                $observation = 'Commission Recharge Espece';
                                $this->utils->addCommission($frais, $service, $fkcarte, $observation, $fkagence);

                            } else {
                                $observation = 'Commission Recharge Espece à faire';
                                $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                            }
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                            $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                        } else {
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                            $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    } else if ($typecompte == 1) {
                        $numcarte = $this->compteModel->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);

                        $statut = 0;
                        $json = $this->api_gtp->LoadCard($numtransact, $numeroserie, $last4digitclient, $montant, 'XOF', 'RechargementEspece');
                        $return = json_decode($json);
                        $response = $return->{'ResponseData'};
                        if ($response != NULL && is_object($response)) {
                            if (array_key_exists('ErrorNumber', $response)) {
                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $errorNumber . '-' . $message, 2, $user_creation);
                                $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                            } else {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if ($transactionId > 0) {
                                    $statut = 1;
                                    $this->utils->changeStatutCoderechargement($codeValidation);
                                    $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactionId);
                                    $crediterCarteCommission = $this->utils->crediter_carteParametrable($frais, 1);
                                    if ($crediterCarteCommission == 1) {
                                        $observation = 'Commission Recharge Espece';
                                        $this->utils->addCommission($frais, $service, $fkcarte, $observation, $fkagence);


                                    } else {
                                        $observation = 'Commission Recharge Espece à faire';
                                        $this->utils->addCommission_afaire($frais, $service, $fkcarte, $observation, $fkagence);
                                    }
                                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                    $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                                }
                            }
                        } else {
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                            $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    }
                } else {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                    $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                    $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok2'));
                }
            } else {
                $message = 'Solde agence insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok3'));
            }
        } else {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
            $this->rediriger('compte', 'validationRechargeCompte/' . base64_encode('nok4') . '/' . base64_encode($telephone));
        }
    }

    /***********Validation Recharge Compte**********/
    public function validationRechargeCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($return[0]) === 'ok') {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' => 'compte/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['message_success_rechargement_espece'], 'type-alert' => 'alert-success');
        } else if (base64_decode($return[0]) === 'nok1') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'compte/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $message, 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok2') {
            $params = array('view' => 'compte/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['chargement_erreurcode_transact_save'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok3') {
            $params = array('view' => 'compte/recharge-espece-carte-fin', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok4') {
            $params = array('view' => 'compte/recharge-espece-carte-search', 'title' => $data['lang']['rechargement_par_espece'], 'alert' => $data['lang']['message_alert'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /************************** Recu Recharge Espece **************/
    public function recuRechargementEspece()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'compte/rechargement-carte-espece-facture', 'title' => $data['lang']['rechargement_par_espece']);
        $this->view($paramsview, $data);
    }

    /*********search Retrait Espece Carte********/
    public function searchRetraitEspece()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(47, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/retrait-espece-carte-search');
        $this->view($params, $data);
    }

    /*********Retrait Espece Carte Beneficiaire********/
    public function retraitEspeceCarte()
    {
        $fkagence = $this->userConnecter->fk_agence;
        $data['typeagence'] = $this->userConnecter->idtype_agence;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($fkagence);

        $typecompte = $this->compteModel->getTypeCompte($telephone);
        if ($typecompte == 0) {
            $username = 'Numherit';
            $userId = 1;
            $token = $this->utils->getToken($userId);
            $response = $this->api_numherit->soldeCompte($username, $token, $telephone);
            $decode_response = json_decode($response);
            if ($decode_response->{'statusCode'} == 000) {
                $data['soldeCarte'] = $decode_response->{'statusMessage'};
            } else $data['soldeCarte'] = 0;
        } else if ($typecompte == 1) {
            $numcarte = $this->compteModel->getNumCarte($telephone);
            $numeroserie = $this->utils->returnCustomerId($numcarte);
            $solde = $this->api_gtp->ConsulterSolde($numeroserie, '6325145878');
            $json = json_decode("$solde");
            $responseData = $json->{'ResponseData'};
            if ($responseData != NULL && is_object($responseData)) {
                if (array_key_exists('ErrorNumber', $responseData)) {
                    $message = $responseData->{'ErrorNumber'};
                    $data['soldeCarte'] = 0;
                } else {
                    $data['soldeCarte'] = $responseData->{'Balance'};
                    $currencyCode = $responseData->{'CurrencyCode'};
                }
            } else $data['soldeCarte'] = 0;
        }

        $params = array('view' => 'compte/retrait-espece-carte');
        $this->view($params, $data);
    }

    /*********Retrait Espece Code Validation********/
    public function retraitEspeceCodeValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['telephone'] = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $data['fkcarte'] = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $data['soldeCarte'] = $this->utils->securite_xss($_POST['soldecarte']);
        $data['typeagence'] = $this->utils->securite_xss($_POST['typeagence']);
        $data['montant'] = $this->utils->securite_xss($_POST['montantbis']);
        $data['frais'] = $this->utils->securite_xss($_POST['frais2']);
        $data['fkagence'] = $this->userConnecter->fk_agence;
        if ($data['telephone'] != '' && $data['montant'] != '' && $data['frais'] != '' && $data['fkagence'] != '') {
            $code_retrait = $this->utils->generateCodeRetrait($data['fkcarte'], $data['montant']);
            if ($data['telephone'] != -1 && $data['telephone'] != -2 && $code_retrait != '' && strlen($code_retrait) == 10)
            {
                $message = $data['lang']['mess_retrait_espece1'] . $code_retrait . $data['lang']['mess_retrait_espece2'];
                // $this->utils->sendSMS($data['lang']['paositra1'], $data['telephone'], $message);

                $recup_mail = $this->utils->recup_mailBenef($data['telephone']);

                @$this->utils->envoiCodeRetrait($recup_mail, $this->userConnecter->prenom . ' ' . $this->userConnecter->nom, $code_retrait);

                /*********BALDE*************/
                @$this->utils->envoiCodeRetrait('alioubalde@numherit.com', $this->userConnecter->prenom .' '. $this->userConnecter->nom, $code_retrait);
                /*********BALDE*************/

                /*if (DEBUG === TRUE) {
                    $this->utils->envoiCodeRetrait('papa.ngom@numherit.com', 'Papa NGOM', $code_retrait);
                }*/
            }
        }
        $params = array('view' => 'compte/retrait-espece-code-validation');
        $this->view($params, $data);
    }

    /******* Action verifier code retrait ****/
    public function codeRetrait()
    {
        $code_secret = $this->utils->securite_xss($_POST['codesecret']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $frais = $this->compteModel->verifCodeRetrait($fkcarte, $code_secret);
        if ($frais == 1) echo 1;
        elseif ($frais == 0) echo 0;
        else echo -2;
    }

    /********* Retrait Espece Carte Beneficiaire Validation ********/
    public function retraitEspeceValidation()
    {
        $telephone = $this->utils->securite_xss($_POST['telephone']);
        $fkcarte = $this->utils->securite_xss($_POST['fkcarte']);
        $montant = $this->utils->securite_xss($_POST['montant']);
        $frais = $this->utils->securite_xss($_POST['frais']);
        $fkagence = $this->utils->securite_xss($_POST['fkagence']);
        $typeagence = $this->utils->securite_xss($_POST['typeagence']);
        $codevalidation = $this->utils->securite_xss($_POST['code']);
        $soldecarte = $this->utils->securite_xss($_POST['soldecarte']);
        $cni = $this->utils->securite_xss($_POST['cni']);
        $user_creation = $this->userConnecter->rowid;
        $service = ID_SERVICE_CASHOUT;
        $json = $this->compteModel->calculFraisab($service, $montant);
        $result = json_decode($json);
        if (is_object($result) && array_key_exists('frais', $result)) {
            $frais = $result->frais;
        }
        $numtransact = $this->utils->Generer_numtransaction();
        $statut = 0;
        $commentaire = 'Cashout';
        if ($typeagence == 3) {
            $montant_total = $montant + $frais;
            $montant_agence = $montant + ($frais * 0.65);
            $montant_commission = $frais * 0.35;
            $commentaire = 'Cashout Partenaire';
            $service = ID_SERVICE_CASHOUTPARTENAIRE;
        } else {
            $montant_total = $montant;
            $montant_agence = $montant;
        }
        if ($codevalidation != '' && $telephone != '' && $montant > 0 && $frais >= 0 && strlen($numtransact) == 15) {
            if ($soldecarte >= $montant_total) {
                $codeValidation = $this->utils->rechercherCodeRetrait($codevalidation, $montant);
                if ($codeValidation > 0) {
                    $typecompte = $this->compteModel->getTypeCompte($telephone);
                    if ($typecompte == 0) {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->utils->getToken($userId);
                        $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                        $jjs = json_decode($soldeavant);
                        $soldeavant = $jjs->{'statusMessage'};
                        $response = $this->api_numherit->debiterCompte($username, $token, $telephone, $montant_total, $service, $user_creation, $fkagence);
                        $decode_response = json_decode($response);
                        if ($decode_response->{'statusCode'} == 000) {
                            $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                            $jjs = json_decode($soldeapres);
                            $soldeapres = $jjs->{'statusMessage'};
                            @$this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, $operation = "DEBIT", $commentaire = "RETRAITESPECE");

                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};
                            $this->utils->validerCodeRetrait($codeValidation, $cni);
                            $this->agenceModel->crediter_soldeAgence($montant_agence, $fkagence);
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);

                            if ($typeagence == 3) {
                                $crediterCarteCommission = $this->utils->crediter_carteParametrable($montant_commission, 1);
                                if ($crediterCarteCommission == 1) {
                                    $observation = 'Commission Retrait Espece';
                                    $this->utils->addCommission($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                } else {
                                    $observation = 'Commission Retrait Espece à faire';
                                    $this->utils->addCommission_afaire($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                }
                            }
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                            $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                        } else {
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' - ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                            $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    } else if ($typecompte == 1) {
                        $numcarte = $this->compteModel->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $statut = 0;
                        $json = $this->api_gtp->UnLoadCard($numtransact, $numeroserie, $last4digitclient, $montant_total, 'XOF', 'RetraitEspece');
                        $return = json_decode("$json");
                        $response = $return->{'ResponseData'};
                        if ($response != NULL && is_object($response)) {
                            if (array_key_exists('ErrorNumber', $response)) {
                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                                $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $errorNumber . '-' . $message, 2, $user_creation);
                                $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                            } else {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if ($transactionId > 0) {
                                    $statut = 1;
                                    $this->utils->validerCodeRetrait($codeValidation, $cni);
                                    $this->agenceModel->crediter_soldeAgence($montant_agence, $fkagence);
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactionId);
                                    if ($typeagence == 3) {
                                        $crediterCarteCommission = $this->utils->crediter_carteParametrable($montant_commission, 1);
                                        if ($crediterCarteCommission == 1) {
                                            $observation = 'Commission Retrait Espece';
                                            $this->utils->addCommission($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                        } else {
                                            $observation = 'Commission Retrait Espece à faire';
                                            $this->utils->addCommission_afaire($montant_commission, $service, $fkcarte, $observation, $fkagence);
                                        }
                                    }
                                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                    $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact));
                                }
                            }
                        } else {
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                            $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok1') . '/' . base64_encode($message));
                        }
                    }
                } else {
                    $message = 'Code de validation incorrect';
                    $transactId = 0;
                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                    $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                    $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok2'));
                }
            } else {
                $message = 'Solde compte insuffisant';
                $transactId = 0;
                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
                $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok3'));
            }
        } else {
            $message = 'Paramétres renseignés incorrects';
            $transactId = 0;
            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message, $frais, $fkagence, $transactId);
            $this->utils->log_journal('Retrait compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
            $this->rediriger('compte', 'validationRetraitCompte/' . base64_encode('nok4') . '/' . base64_encode($telephone));
        }
    }

    /***********Validation retrait Compte**********/
    public function validationRetraitCompte($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($return[0]) === 'ok') {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);

            $params = array('view' => 'compte/retrait-espece-carte-fin', 'title' => $data['lang']['retrait_carte'], 'alert' => $data['lang']['message_success_retrait_carte'], 'type-alert' => 'alert-success');
        } else if (base64_decode($return[0]) === 'nok1') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'compte/retrait-espece-carte-fin', 'title' => $data['lang']['retrait_carte'], 'alert' => $message, 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok2') {
            $params = array('view' => 'compte/retrait-espece-carte-fin', 'title' => $data['lang']['retrait_carte'], 'alert' => $data['lang']['chargement_erreurcode_transact_save'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok3') {
            $params = array('view' => 'compte/retrait-espece-carte-fin', 'title' => $data['lang']['retrait_carte'], 'alert' => $data['lang']['solde_insuffisant'], 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok4') {
            $params = array('view' => 'compte/retrait-espece-carte-search', 'title' => $data['lang']['retrait_carte'], 'alert' => $data['lang']['message_alert'], 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /************************** Recu Retrait Espece **************/
    public function recuRetraitEspece()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $paramsview = array('view' => 'compte/retrait-carte-espece-facture', 'title' => $data['lang']['retrait_carte']);
        $this->view($paramsview, $data);
    }

    /*********search Recharge Jula Carte********/
    public function searchRechargeJula()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(45, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/recharge-jula-carte-search');
        $this->view($params, $data);
    }

    /*********Recharge Jula Carte Beneficiaire********/
    public function rechargeJulaCarte()
    {
        $fkagence = $this->userConnecter->fk_agence;
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['soldeAgence'] = $this->utils->getSoldeAgence($fkagence);

        $params = array('view' => 'compte/recharge-jula-carte');
        $this->view($params, $data);
    }

    /********* Recharge Jula Carte Beneficiaire Validation ********/
    public function rechargeJulaValidation()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $code = $this->utils->trimUltime($this->utils->securite_xss($_POST['code']));
        $fkcarte = base64_decode($this->utils->securite_xss($_POST['fkcarte']));
        $soldeAgence = $this->utils->securite_xss($_POST['soldeagence']);
        $fkagence = $this->userConnecter->fk_agence;
        $user_creation = $this->userConnecter->rowid;
        $service = ID_SERVICE_RECHARGE_KREDIVOLA;
        $statut = 0;
        $commentaire = 'Recharge JULA';

        if ($code != '' && $telephone != '' && $soldeAgence != '' && $fkcarte != '' && $user_creation != '' && $fkagence != '') {
            require_once(__DIR__ . '/../../vendor/ApiGTP/lib/nusoap.php');
            //On recupere le montant de la carte JULA
            $s = new nusoap_client(URL_WS_JULA, true);
            $cle = sha1($code . ID_WS_JULA . KEY_WS_JULA);
            $parameters = array("code_carte" => $code, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle);
            $ResulatRenvoye = $s->call('ConsultationSolde', $parameters);
            $error = $s->getError();
            if ($error) {
                $msg = $data['lang']['erreur_webservice_jula'];
                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
            } else {
                if ($ResulatRenvoye == 104) {
                    $msg = $data['lang']['erreur_webservice_jula'];
                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                    $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                } else if ($ResulatRenvoye == 103) {
                    $msg = $data['lang']['code_jula_incorrect'];
                    $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                    $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                } else {

                    if ($soldeAgence >= $ResulatRenvoye) {
                        $cle2 = sha1($code . $ResulatRenvoye . ID_WS_JULA . KEY_WS_JULA);
                        $parameters = array("code_carte" => $code, "montant" => $ResulatRenvoye, "idmarchand" => ID_WS_JULA, "cle_hachage" => $cle2, "num_transaction" => "MauriPost" . date("YmdHis"));
                        $ResulatRenvoye2 = $s->call('debiterCarte', $parameters);
                        $error = $s->getError();
                        if ($error) {
                            $msg = $data['lang']['erreur_webservice_jula'];
                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                            $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                        } else {
                            if ($ResulatRenvoye2 == 105) {
                                $msg = $data['lang']['erreur_webservice_jula'];
                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                            }
                            if ($ResulatRenvoye2 == 104) {
                                $msg = $data['lang']['erreur_webservice_jula'];
                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                            }
                            if ($ResulatRenvoye2 == 103) {
                                $msg = $data['lang']['code_jula_incorrect'];
                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                            }
                            if ($ResulatRenvoye2 == 102) {
                                $msg = $data['lang']['erreur_webservice_jula'];
                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                            }
                            if ($ResulatRenvoye2 == 101) {
                                $montant = $ResulatRenvoye;
                                $frais = $this->compteModel->calculFraisab($service, $montant);
                                $typecompte = $this->compteModel->getTypeCompte($telephone);
                                $numtransact = $this->utils->Generer_numtransaction();
                                if ($typecompte == 0) {
                                    $username = 'Numherit';
                                    $userId = 1;
                                    $token = $this->utils->getToken($userId);
                                    $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                                    $jjs = json_decode($soldeavant);
                                    $soldeavant = $jjs->{'statusMessage'};
                                    $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $user_creation, $fkagence);

                                    $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                                    $jjs = json_decode($soldeapres);
                                    $soldeapres = $jjs->{'statusMessage'};
                                    @$this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, $operation = "CREDIT", $commentaire = "RECHARGEMENTJULAAGENCE");

                                    $decode_response = json_decode($response);
                                    if ($decode_response->{'statusCode'} == 000) {
                                        $statut = 1;
                                        $message = $decode_response->{'statusMessage'};
                                        $transactId = $decode_response->{'NumTransaction'};
                                        $this->utils->debiter_compteJula($montant);
                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message . ' ' . $code, $frais, $fkagence, $transactId);
                                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                        $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact) . '/' . base64_encode($code));
                                    } else {
                                        $message = $decode_response->{'statusMessage'};
                                        $transactId = 0;
                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message . ' ' . $code, $frais, $fkagence, $transactId);
                                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $user_creation);
                                        $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok1') . '/' . base64_encode($message));
                                    }
                                } else if ($typecompte == 1) {
                                    $numcarte = $this->compteModel->getNumCarte($telephone);
                                    $numeroserie = $this->utils->returnCustomerId($numcarte);
                                    $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                                    $statut = 0;
                                    $json = $this->api_gtp->LoadCard($numtransact, $numeroserie, $last4digitclient, $montant, 'XOF', 'RechargementEspece');
                                    $return = json_decode("$json");
                                    $response = $return->{'ResponseData'};
                                    if ($response != NULL && is_object($response)) {
                                        if (array_key_exists('ErrorNumber', $response)) {
                                            $errorNumber = $response->{'ErrorNumber'};
                                            $message = $response->{'ErrorMessage'};
                                            $transactId = 0;
                                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message . ' ' . $code, $frais, $fkagence, $transactId);
                                            $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact . ' code:' . $code, $errorNumber . '-' . $message, 2, $user_creation);
                                            $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok1') . '/' . base64_encode($message));
                                        } else {
                                            $transactionId = $response->{'TransactionID'};
                                            $message = 'Succes';
                                            if ($transactionId > 0) {
                                                $statut = 1;
                                                $this->utils->debiter_compteJula($montant);
                                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message . ' ' . $code, $frais, $fkagence, $transactionId);
                                                $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $user_creation);
                                                $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('ok') . '/' . base64_encode($telephone) . '/' . base64_encode($montant) . '/' . base64_encode($frais) . '/' . base64_encode($numtransact) . '/' . base64_encode($code));
                                            }
                                        }
                                    } else {
                                        $message = 'Response GTP not object';
                                        $transactId = 0;
                                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $user_creation, $statut, $commentaire . ' ' . $message . ' ' . $code, $frais, $fkagence, $transactId);
                                        $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact . ' code:' . $code, $message, 2, $user_creation);
                                        $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok1') . '/' . base64_encode($message));
                                    }
                                }
                            }
                        }
                    } else {
                        $msg = $data['lang']['solde_agence_insuffisant'];
                        $this->utils->log_journal('Recharge compte par jula', 'Téléphone compte:' . $telephone . ' code:' . $code, $msg, 2, $user_creation);
                        $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok2') . '/' . base64_encode($msg));
                    }
                }
            }
        } else {
            $msg = 'Paramétres envoyés incorrect';
            $this->utils->log_journal('Recharge compte', 'Téléphone compte:' . $telephone . ' Code:' . $code, $msg, 2, $user_creation);
            $this->rediriger('compte', 'validationRechargeJula/' . base64_encode('nok1') . '/' . base64_encode($msg));
        }
    }

    /***********Validation retrait Compte**********/
    public function validationRechargeJula($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (base64_decode($return[0]) === 'ok') {
            $data['telephone'] = base64_decode($return[1]);
            $data['montant'] = base64_decode($return[2]);
            $data['frais'] = base64_decode($return[3]);
            $data['numtransact'] = base64_decode($return[4]);
            $data['code'] = base64_decode($return[5]);

            $params = array('view' => 'compte/recharge-jula-carte-fin', 'title' => $data['lang']['rechargement_par_jula'], 'alert' => $data['lang']['message_success_rechargement_jula'], 'type-alert' => 'alert-success');
        } else if (base64_decode($return[0]) === 'nok1') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'compte/recharge-jula-carte-fin', 'title' => $data['lang']['rechargement_par_jula'], 'alert' => $message, 'type-alert' => 'alert-danger');
        } else if (base64_decode($return[0]) === 'nok2') {
            $message = base64_decode($return[1]);
            $params = array('view' => 'compte/recharge-jula-carte-fin', 'title' => $data['lang']['rechargement_par_jula'], 'alert' => $message, 'type-alert' => 'alert-danger');
        }
        $this->view($params, $data);
    }

    /************************** Recu Recharge Jula **************/
    public function recuRechargementJula()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $telephone = base64_decode($this->utils->securite_xss($_POST['telephone']));
        $numtransac = $this->utils->securite_xss($_POST['numtransact']);
        $data['benef'] = $this->compteModel->beneficiaireByTelephone1($telephone);
        $data['transaction'] = $this->utils->transactionByNum($numtransac);
        $data['code'] = $this->utils->securite_xss($_POST['code']);
        $paramsview = array('view' => 'compte/rechargement-carte-jula-facture', 'title' => $data['lang']['rechargement_par_jula']);
        $this->view($paramsview, $data);
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                             GESTION DES STOCKS                                         //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    public function envoi_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(251, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';

        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_envoi_caveau'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_envoi_caveau'];
            }
        }

        $data['typeCarte'] = $this->compteModel->getTypeCarte();
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/nouvel-envoi-caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_envoie_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(252, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_reception'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_reception'];
            }
        }
        $params = array('view' => 'compte/historique_envoie_caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingHistoEnvCaveau()
    {
        $param = [
            "button" => [
                [ROOT . "compte/recuEnvoiCaveau/", "fa fa-print"]
            ],
            "attribut" => ['target="_blank"'],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueEnvCaveau", $param);
    }

    public function recuEnvoiCaveau($arg)
    {
        $data = $this->compteModel->getLot(["lc.idlotcarte"=>base64_decode($arg[0])]);
        if(!(count($data) > 0))
            $this->rediriger("compte", "historique_envoie_caveau");
        $data[0]['titre'] = 'Bon de livraison de cartes MauriPost';
        $this->exportToPdf("app/views/compte/tpl-recu-carte", $data[0]);
    }

    public function validEnvoiCaveau()
    {
        $num_debut = $this->utils->securite_xss($_POST['num_debut']);
        $num_fin = $this->utils->securite_xss($_POST['num_fin']);
        $num_reference = $this->utils->securite_xss($_POST['num_reference']);
        $typecarte = $this->utils->securite_xss($_POST['idtypecarte']);

        if ($num_debut <= $num_fin) {
            $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte,0,'NUMHERIT');
            if ($verifLot) {
                $typelot = 0;
                $expediteur = 'NUMHERIT';
                $destinataire = 'CAVEAU';
                $stockencours = $this->utils->getlastStock($typelot, $expediteur);
                if ($stockencours >= 0) {
                    $stock = (intval($num_fin) - intval($num_debut))+ 1;
                    $idlot = $this->compteModel->addLot($num_reference, $num_debut, $num_fin, NUMHERIT_ID, CAVEAU_ID, $stock, $stockencours, $expediteur, $destinataire, $typelot, $typecarte, 0);

                    if ($idlot > 0) {
                        $this->compteModel->saveCarte($num_debut, $num_fin, $idlot, $niveau = 'NUMHERIT', $typelot, $typecarte, NUMHERIT_ID);
                        $this->utils->sendNotification(1, $num_debut, $num_fin, $typecarte, $expediteur, "Caveau");
                        @$this->utils->log_journal('Nouvel envoi de Carte à Caveau', "lot :" . $num_debut . "-" . $num_fin, 'Nouvelle envoi carte', 2, $this->userConnecter->rowid);
                        $this->rediriger("compte", "historique_envoie_caveau/" . base64_encode(-1));
                    } else $this->rediriger("compte", "envoi_caveau/" . base64_encode(-2));
                } else $this->rediriger("compte", "envoi_caveau/" . base64_encode(-2));
            } else $this->rediriger("compte", "envoi_caveau/" . base64_encode(-2));
        } else $this->rediriger("compte", "envoi_caveau/" . base64_encode(-2));
    }

    public function reception_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(253, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';

        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_reception_caveau'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_reception_caveau'];
            }
        }

        $data['typeCarte'] = $this->compteModel->getTypeCarte();
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/nouvel-reception-caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_reception_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(254, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_reception'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_reception'];
            }
        }
        $params = array('view' => 'compte/historique_reception_caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingHistoReceptCaveau()
    {
        $param = [
            "button" => [
                [ROOT . "compte/recuReceptionCaveau/", "fa fa-print"]
            ],
            "attribut" => ['target="_blank"'],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueReceptionCaveau", $param);
    }

    public function recuReceptionCaveau($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data = $this->compteModel->getLot(["lc.idlotcarte"=>base64_decode($arg[0])]);
        if(!(count($data) > 0))
            $this->rediriger("compte", "historique_reception_caveau");
        $data[0]['titre'] =  $data['lang']['title_bon_recept'];
        $this->exportToPdf("app/views/compte/tpl-recu-carte", $data[0]);
    }

    public function validReceptionCaveau()
    {
        $num_debut = $this->utils->securite_xss($_POST['num_debut']);
        $num_fin = $this->utils->securite_xss($_POST['num_fin']);
        $num_reference = $this->utils->securite_xss($_POST['num_reference']);
        $typecarte = $this->utils->securite_xss($_POST['typecarte']);
        if ($num_debut <= $num_fin) {
            $typelotStockEnCours = 1;
            $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte,$typelotStockEnCours,'CAVEAU');
            if($verifLot == false)
                $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte,2,'AGENCE', 2);

            $prevInfo = $this->utils->getInfoPrev($num_debut, $num_fin, $typecarte);
            if ($verifLot && is_array($prevInfo) && count($prevInfo) > 0) {
                $idLot = $prevInfo['idlot'];
                $expediteur = $prevInfo['niveau'];
                $expId = $prevInfo['idagence'];
                $typelot = 1;
                $destinataire = 'CAVEAU';
                $stockencours = $this->utils->getlastStock($typelotStockEnCours, $expediteur);

                if ($stockencours >= 0) {
                    $stock = (intval($num_fin) - intval($num_debut))+ 1;
                    $idlot = $this->compteModel->addLot($num_reference, $num_debut, $num_fin, $expId, CAVEAU_ID, $stock, $stockencours, $expediteur, $destinataire, $typelot, $typecarte, 0);

                    if ($idlot > 0) {
                        $this->compteModel->soustraireStockLotCarte(["stock"=>$stock, "rowid"=>$idLot]);
                        $this->compteModel->moveCarte($num_debut, $num_fin, $destinataire, $typelot, CAVEAU_ID, $idlot);
                        $this->utils->sendNotification(2, $num_debut, $num_fin, $typecarte, $expediteur, $destinataire);
                        @$this->utils->log_journal('Nouvelle reception de Carte à Caveau', "lot :" . $num_debut . "-" . $num_fin, 'Nouvelle reception carte', 2, $this->userConnecter->rowid);
                        $this->rediriger("compte", "historique_reception_caveau/" . base64_encode(-1));
                    } else $this->rediriger("compte", "reception_caveau/" . base64_encode(-2));
                } else $this->rediriger("compte", "reception_caveau/" . base64_encode(-2));
            } else $this->rediriger("compte", "reception_caveau/" . base64_encode(-2));
        } else $this->rediriger("compte", "reception_caveau/" . base64_encode(-2));
    }

    public function distribution_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(255, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_dist'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_dist'];
            }
        }
        $data['agences'] = $this->agenceModel->allAgence();
        $data['typeCarte'] = $this->compteModel->getTypeCarte();
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/distribution_caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_distribution_caveau($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(256, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_dist'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_dist'];
            }
        }
        $params = array('view' => 'compte/historique_distribution_caveau', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingHistodistCaveau()
    {
        $param = [
            "button" => [
                [ROOT . "compte/recuDistributionCaveau/", "fa fa-print"]
            ],
            "attribut" => ['target="_blank"'],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueDistributionCaveau", $param);
    }

    public function recuDistributionCaveau($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data = $this->compteModel->getLot(["lc.idlotcarte"=>base64_decode($arg[0])]);
        if(!(count($data) > 0))
            $this->rediriger("compte", "historique_distribution_caveau");
        $data[0]['titre'] = $data['lang']['title_bon_distrib'];
        $this->exportToPdf("app/views/compte/tpl-recu-carte", $data[0]);
    }

    public function validEnvoiAgence()
    {
        $num_debut = $this->utils->securite_xss($_POST['num_debut']);
        $num_fin = $this->utils->securite_xss($_POST['num_fin']);
        $num_reference = $this->utils->securite_xss($_POST['num_reference']);
        $typecarte = $this->utils->securite_xss($_POST['idtypecarte']);
        $idagence = $this->utils->securite_xss($_POST['idagence']);
        if ($num_debut <= $num_fin) {
            $typelot = 1;
            $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte, $typelot,'CAVEAU', 2);
            $prevInfo = $this->utils->getInfoPrev($num_debut, $num_fin, $typecarte);
            if ($verifLot && is_array($prevInfo) && count($prevInfo) > 0) {
                $typelot = 0;
                $idLot = $prevInfo['idlot'];
                $expediteur = $prevInfo['niveau'];
                $destinataire = 'AGENCE';
                $stockencours = $this->utils->getlastStock($typelot, $expediteur);

                if ($stockencours >= 0) {
                    $stock = (intval($num_fin) - intval($num_debut)) + 1;
                    $idlot = $this->compteModel->addLot($num_reference, $num_debut, $num_fin, CAVEAU_ID, $idagence, $stock, $stockencours, $expediteur, $destinataire, $typelot, $typecarte, 0);
                    $idLot = $prevInfo['idlot'];

                    if ($idlot > 0) {
                        $this->compteModel->soustraireStockLotCarte(["stock"=>$stock, "rowid"=>$idLot]);
                        $this->compteModel->moveCarte($num_debut, $num_fin, null, $typelot, null, $idlot);
                        $this->utils->sendNotification(3, $num_debut, $num_fin, $typecarte, $expediteur, $destinataire);
                        @$this->utils->log_journal('Nouvelle distribution de Carte à Agence', "lot :" . $num_debut . "-" . $num_fin, 'Nouvelle distribution carte', 2, $this->userConnecter->rowid);
                        $this->rediriger("compte", "historique_distribution_caveau/" . base64_encode(-1));
                    } else $this->rediriger("compte", "distribution_caveau/" . base64_encode(-2));
                } else $this->rediriger("compte", "distribution_caveau/" . base64_encode(-2));
            } else $this->rediriger("compte", "distribution_caveau/" . base64_encode(-2));
        } else $this->rediriger("compte", "distribution_caveau/" . base64_encode(-2));
    }

    public function reception_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(257, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';

        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_reception_caveau'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_reception_caveau'];
            }
            elseif ($arg == -3) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_reception_agence'];
            }
        }


        $data['agences'] = $this->agenceModel->allAgence();
        $data['idagence'] = $this->userConnecter->fk_agence;

        $data['typeCarte'] = $this->compteModel->getTypeCarte();
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/nouvel-reception-agence', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_reception_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(258, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_reception'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_reception'];
            }
        }
        $params = array('view' => 'compte/historique_reception_agence', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingHistoReceptAgence()
    {
        $param = [
            "button" => [
                [ROOT . "compte/recuReceptionAgence/", "fa fa-print"]
            ],
            "attribut" => ['target="_blank"'],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueReceptionAgence", $param);
    }

    public function recuReceptionAgence($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data = $this->compteModel->getLot(["lc.idlotcarte"=>base64_decode($arg[0])]);
        if(!(count($data) > 0))
            $this->rediriger("compte", "historique_reception_agence");
        $data[0]['titre'] = $data['lang']['title_bon_recept'];
        $this->exportToPdf("app/views/compte/tpl-recu-carte", $data[0]);
    }

    public function validReceptionAgence()
    {
        $num_debut = $this->utils->securite_xss($_POST['num_debut']);
        $num_fin = $this->utils->securite_xss($_POST['num_fin']);
        $num_reference = $this->utils->securite_xss($_POST['num_reference']);
        $typecarte = $this->utils->securite_xss($_POST['idtypecarte']);
        $agence_connect = $this->utils->securite_xss($_POST['agence_connect']);

        if ($num_debut <= $num_fin) {
            $typelot = 0;
            $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte, $typelot,'CAVEAU', 2);
            $prevInfo = $this->utils->getInfoPrev($num_debut, $num_fin, $typecarte);
            $agence_id = $this->utils->retourAgence($prevInfo["idlot"]);
            $agence_distribue=$agence_id["idagencedest"];

            if(intval($agence_connect) === intval($agence_distribue)){

                if ($verifLot && is_array($prevInfo) && count($prevInfo) > 0) {
                    $idLot = $prevInfo['idlot'];
                    //var_dump($idLot);die();
                    $lotInfo = $this->compteModel->getLot(["lc.idlotcarte"=>$idLot])[0];
                    $idagence = $lotInfo['idagencedest'];
                    $typelot = 1;
                    $expediteur = $prevInfo['niveau'];
                    $destinataire = 'AGENCE';
                    $stockencours = $this->utils->getlastStock($typelot, $expediteur);

                    if ($stockencours >= 0) {
                        $stock = (intval($num_fin) - intval($num_debut)) + 1;
                        $idlot = $this->compteModel->addLot($num_reference, $num_debut, $num_fin, CAVEAU_ID, $idagence, $stock, $stockencours, $expediteur, $destinataire, $typelot, $typecarte, 0);
                        if ($idlot > 0) {
                            $this->compteModel->soustraireStockLotCarte(["stock"=>$stock, "rowid"=>$idLot]);
                            $this->compteModel->moveCarte($num_debut, $num_fin, $destinataire, $typelot, $idagence, $idlot);
                            $this->utils->sendNotification(4, $num_debut, $num_fin, $typecarte, $expediteur, $destinataire);
                            @$this->utils->log_journal('Nouvelle reception de Carte à Agence', "lot :" . $num_debut . "-" . $num_fin, 'Nouvelle reception carte', 2, $this->userConnecter->rowid);
                            $this->rediriger("compte", "historique_reception_agence/" . base64_encode(-1));
                        } else $this->rediriger("compte", "reception_agence/" . base64_encode(-2));
                    } else $this->rediriger("compte", "reception_agence/" . base64_encode(-2));
                }
                else{
                    $this->rediriger("compte", "reception_agence/" . base64_encode(-2));

                }

            }
            else{
                $this->rediriger("compte", "reception_agence/" . base64_encode(-3));

            }

        }
        else
        {
            $this->rediriger("compte", "reception_agence/" . base64_encode(-2));

        }
    }

    public function retour_lot_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(259, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';

        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_retour_lot'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_retour_lot'];
            }
        }

        $data['lots'] = $this->compteModel->getLotReception('CAVEAU', CAVEAU_ID);
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/retour_lot_agence', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_retour_lot_agence($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(260, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_retour_lot'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_retour_lot'];
            }
        }
        $params = array('view' => 'compte/historique_retour_lot_agence', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function processingHistoRetourLotAgence()
    {
        $param = [
            "button" => [
                [ROOT . "compte/recuRetourLotAgence/", "fa fa-print"]
            ],
            "attribut" => ['target="_blank"'],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueRetourLotAgence", $param);
    }

    public function recuRetourLotAgence($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data = $this->compteModel->getLot(["lc.idlotcarte"=>base64_decode($arg[0])]);
        if(!(count($data) > 0))
            $this->rediriger("compte", "historique_retour_lot_agence");
        $data[0]['titre'] = 'Bon de retour de lot de cartes MauriPost'; $data['lang']['title_bon_retour'];
        $this->exportToPdf("app/views/compte/tpl-recu-carte", $data[0]);
    }

    public function validRetourLotAgence()
    {
        $num_debut = intval($this->utils->securite_xss($_POST['num_debut']));
        $num_fin = intval($this->utils->securite_xss($_POST['num_fin']));
        $num_reference = $this->utils->securite_xss($_POST['num_reference']);
        $idLot = $this->utils->securite_xss($_POST['idlot']);
        if ($num_debut <= $num_fin) {
            $lot = $this->compteModel->getLot(["idlotcarte"=>$idLot]);
            if (count($lot) === 1 && intval($lot[0]['num_debut']) <= $num_debut && $num_fin <= intval($lot[0]['num_fin'])) {
                $carteSale = $this->compteModel->carteSaleInLot($num_debut, $num_fin);
                $carteSale = array_map(function ($one){
                    return intval($one['num_serie']);
                }, $carteSale);
                $lotretour = $this->utils->getIntervaleRetour([$num_debut, $num_fin], $carteSale);
                $idagence = $lot[0]['idagencedest'];
                $typecarte = $lot[0]['idtypecarte'];
                $typelot = 2;
                $expediteur = 'AGENCE';
                $destinataire = 'CAVEAU';
                $verifLot = $this->utils->verifValidLot($num_debut, $num_fin, $typecarte, 1,'AGENCE', 2);
                if ($verifLot) {
                    foreach ($lotretour as $item) {
                        $stockencours = $this->utils->getlastStock($typelot, $expediteur);
                        if ($stockencours >= 0) {
                            $stock = (intval($item[1]) - intval($item[0])) + 1;
                            $item[0] = str_pad($item[0], 10, "0", STR_PAD_LEFT);
                            $item[1] = str_pad($item[1], 10, "0", STR_PAD_LEFT);
                            $addlot = $this->compteModel->addLot($num_reference, $item[0], $item[1], $idagence, CAVEAU_ID, $stock, $stockencours, $expediteur, $destinataire, $typelot, $typecarte, 0);
                            if ($addlot > 0) {
                                $this->compteModel->soustraireStockLotCarte(["stock"=>$stock, "rowid"=>$idLot]);
                                $this->compteModel->moveCarte($item[0], $item[1], null, $typelot, null, $addlot);
                                $this->utils->sendNotification(5, $item[0], $item[1], $typecarte, $expediteur, $destinataire);
                                @$this->utils->log_journal('Nouvel retour de Carte à Caveau', "lot :" . $item[0] . "-" . $item[1], 'Nouvel retour carte', 2, $this->userConnecter->rowid);
                                $num_reference = $this->compteModel->__getReference();
                            }
                        }
                    }
                    $this->rediriger("compte", "historique_retour_lot_agence/" . base64_encode(-1));
                }else $this->rediriger("compte", "retour_lot_agence/" . base64_encode(-2));
            } else $this->rediriger("compte", "retour_lot_agence/" . base64_encode(-2));
        } else $this->rediriger("compte", "retour_lot_agence/" . base64_encode(-2));
    }

    public function reception($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(110, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_reception'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_reception'];
            }
        }
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/nouvelle-reception', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function addLotCarte()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $_POST['user_add'] = $this->userConnecter->rowid;
        $result = $this->compteModel->insertReception($_POST);
        $this->rediriger("compte", "historique_recep/" . base64_encode($result));
    }

    public function historique_recep($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(111, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_reception'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_reception'];
            }
        }
        $params = array('view' => 'compte/histo-reception', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function historique_dist($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(54, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_dist'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_dist'];
            }
        }
        $params = array('view' => 'compte/histo-distribution', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function distribution($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(53, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_add_dist'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_add_dist'];
            }
        }
        $data['agences'] = $this->agenceModel->allAgence();
        $data['lots'] = [];//$this->compteModel->getLotReception();
        $data['typeCarte'] = $this->compteModel->getTypeCarte();
        $data['ref'] = $this->compteModel->__getReference();
        $params = array('view' => 'compte/nouvelle-distribution', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function updateDistribution()
    {
        foreach ($_POST as $key => $item) $_POST[$key] = base64_decode($_POST[$key]);
        $rowid = $_POST['idlotcarte'];
        unset($_POST['idlotcarte']);
        $result = $this->compteModel->updateDistribution($_POST, $rowid);
        $this->rediriger("compte", "historique_dist/" . base64_encode($result));
    }

    public function detailHistoRecep($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['detailRecep'] = $this->compteModel->getHistoriqueReception(['idlotcarte_recu' => base64_decode($arg[0])])[0];
        $params = array('view' => 'compte/reception-detail');
        $this->view($params, $data);
    }

    public function detailHistoDist($arg)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['detailDist'] = $this->compteModel->getHistoriqueDistribution(['idlotcarte' => base64_decode($arg[0])])[0];
        $data['nbrCarteVendu'] = count($this->compteModel->getCartesSaleByIntevale(['debut' => $data['detailDist']['num_debut'], 'fin' => $data['detailDist']['num_fin'], 'idagence' => $data['detailDist']['idagence']]));
        if ($data['nbrCarteVendu'] === 0) {
            $data['agences'] = $this->agenceModel->allAgence();
            $data['typeCarte'] = $this->compteModel->getTypeCarte();
        }
        $params = array('view' => 'compte/distribution-detail');
        $this->view($params, $data);
    }

    public function distribuerLotCarte()
    {
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'idLot' || $key == 'agence_retour' || $key == 'idlotcarte') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $_POST['user_add'] = $this->userConnecter->rowid;
        $result = $this->compteModel->insertDistribution($_POST);
        $this->rediriger("compte", "historique_dist/" . base64_encode($result));
    }

    public function disponibilite($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(55, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (!is_null($arg)) {
            $arg = base64_decode($arg[0]);
            if ($arg == -1) {
                $type_alert = 'success';
                $alert = $data['lang']['message_success_update_agence'];
            } elseif ($arg == -2) {
                $type_alert = 'error';
                $alert = $data['lang']['message_error_update_agence'];
            }
        }
        $params = array('view' => 'compte/disponibilite-carte', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function retour($arg = null)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(109, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if (count($_POST) == 0) {
            if (!is_null($arg)) {
                $arg = base64_decode($arg[0]);
                if ($arg == -1) {
                    $type_alert = 'success';
                    $alert = $data['lang']['message_success_update_agence'];
                } elseif ($arg == -2) {
                    $type_alert = 'error';
                    $alert = $data['lang']['message_error_update_agence'];
                }
            }
            $data['agences'] = $this->agenceModel->allAgenceWithLot();
        } else {
            foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $_POST['lotCarte'] = explode('-', $_POST['lotCarte']);
            $data['idlotcarte'] = $_POST['lotCarte'][0];
            $_POST['debut'] = $_POST['lotCarte'][1];
            $_POST['fin'] = $_POST['lotCarte'][2];
            $data['idagence'] = $_POST['idagence'];
            unset($_POST['lotCarte']);
            $carteSale = $this->compteModel->getCartesSaleByIntevale(['debut' => $_POST['debut'], 'fin' => $_POST['fin'], 'idagence' => $_POST['idagence']]);
            $data['lotRestant'] = $this->getFreeInterval(['debut' => $_POST['debut'], 'fin' => $_POST['fin']], $carteSale);
            $this->getSession()->setAttributArray('lotRestant', $data['lotRestant']);
            $data['reference'] = $_POST['reference'];
            $data['date_reception'] = $_POST['date_reception'];
        }
        $params = array('view' => 'compte/retour-lot-carte', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);
    }

    public function retournerLot()
    {
        foreach ($_POST as $key => $item)
            $_POST[$key] = ($key == 'idLot' || $key == 'agence_retour' || $key == 'idlotcarte') ? base64_decode($_POST[$key]) : $this->utils->securite_xss($_POST[$key]);
        $idlotcarte = $_POST['idlotcarte'];
        unset($_POST['idlotcarte']);

        $lot = $this->getSession()->getAttribut('lotRestant')[$_POST['idLot']];
        if ((intval($_POST['num_debut']) < intval($_POST['num_fin'])) && (intval($_POST['num_debut']) <= intval($lot['debut'])) && (intval($_POST['num_fin']) <= intval($lot['fin']))) {
            unset($_POST['idLot']);
            $_POST['num_reference'] = $this->compteModel->__getReference();
            $_POST['date_reception'] = $this->utils->getDateNow();
            $_POST['user_add'] = $this->userConnecter->rowid;
            $result = $this->compteModel->insertReceptionRetour($_POST);
            if ($result === -1) {
                $stock = (intval($_POST['num_fin']) - intval($_POST['num_debut'])) + 1;
                $this->compteModel->updateDistribution(['carte_retour' => $stock], $idlotcarte);
            }

            $this->rediriger("compte", "retour/" . base64_encode($result));
        } else {
            $this->rediriger("compte", "retour/" . base64_encode(-2));
        }
    }

    public function getFreeInterval($interval = ['debut' => null, 'fin' => null], array $data)
    {
        asort($data);
        $current = null;
        $result = [];
        foreach ($data as $oneData) {
            if ($oneData > $interval['debut']) {
                array_push($result, ['idlot' => null, 'debut' => $interval['debut'], 'fin' => str_pad((intval($oneData) - 1), 10, "0", STR_PAD_LEFT), 'stock' => ((intval(str_pad((intval($oneData) - 1), 10, "0", STR_PAD_LEFT)) - intval($interval['debut'])) + 1)]);
                $result[(count($result) - 1)]['idlot'] = (count($result) - 1);
                $interval['debut'] = str_pad((intval($oneData) + 1), 10, "0", STR_PAD_LEFT);
            } else
                $interval['debut'] = str_pad((intval($interval['debut']) + 1), 10, "0", STR_PAD_LEFT);
        }
        if (intval($result[(count($result) - 1)]['fin']) < intval($interval['fin'])) {
            array_push($result, ['idlot' => null, 'debut' => $interval['debut'], 'fin' => $interval['fin'], 'stock' => ((intval($interval['fin']) - intval($interval['debut'])) + 1)]);
            $result[(count($result) - 1)]['idlot'] = (count($result) - 1);
        }
        return $result;
    }

    public function getLotCarteByAgence()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        foreach ($_POST as $key => $item) $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
        $data = $this->compteModel->getLotByAgence($_POST);
        $result = '<option disabled selected="selected">' . $data['lang']['select_lot'] . '</option>';
        foreach ($data as $lot)
            $result .= '<option value="' . $lot['idlotcarte'] . '-' . $lot['num_debut'] . '-' . $lot['num_fin'] . '">' . $lot['num_debut'] . ' ==> ' . $lot['num_fin'] . ' ==> ' . $this->utils->nombre_format($lot['stock']) . '</option>';
        echo $result;
    }

    public function processingHistoRecep()
    {
        $param = [
            "button" => [
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueReception", $param);
    }

    public function processingHistoDist()
    {
        $param = [
            "button" => [],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getHistoriqueDistribution", $param);
    }

    public function processingDispoCarte()
    {
        $param = [
            "button" => [],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "getDisponibiliteCartes", $param);
    }

    /*********search Historique carte********/
    public function searchHistoriqueCarte()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(49, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/search-historique-carte');
        $this->view($params, $data);
    }

    /***************** Historique Transaction *********************/
    public function historiqueTransaction()
    {
        $data['phone'] = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/historiquecarte');
        $this->view($params, $data);
    }

    /***************** transaction du jour *********************/
    public function processingcarte($id)
    {


        // storing  request (ie, get/post) global array to a variable
        $requestData = $_REQUEST;

        $columns = array(
            // datatable column index  => database column name
            0 => 'date_transaction',
            1 => 'montant',
            2 => 'commentaire',
        );

        //getting total number records without any search
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $phone = trim(str_replace("+", "00", $this->utils->securite_xss($id[2])));


        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        if($this->userConnecter->admin == 1 || $this->userConnecter->type_profil == 1){
            $next.=", region";
            $where.=" AND agence.province = region.idregion";
        }
        else if($this->userConnecter->type_profil == 2)
        {
            $next.=", region";
            $where.=" AND agence.province = region.idregion AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($this->userConnecter->type_profil == 4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, 
			transaction.commentaire,  transaction.fk_service,
            transaction.date_transaction 
            FROM transaction, carte, agence " . $next . "
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND carte.telephone =:tel
            AND transaction.fk_carte = carte.rowid " . $where . "";


        if ($requestData['search']['value']!="") {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( transaction.date_transaction LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR transaction.montant LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR transaction.date_transaction LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR  transaction.commentaire LIKE '" . $requestData['search']['value'] . "%' )";

        }

        $tabCol = ['b.prenom', 'b.nom', 'b.email', 'b.adresse', 'b.etat'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'];// . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";
        $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("tel" => $phone, "date1" => $date1, "date2" => $date2));
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;
        $data = array();


        foreach ($rows as $row) {  //preparing an array

            $nestedData = array();

            $montant = $row["montant"];
            $commission = $row["commission"];
            $montant_ttc = $montant + $commission;

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $this->utils->number_format($montant);
            $nestedData[] = $this->utils->number_format($commission);
            $nestedData[] = $this->utils->number_format($montant_ttc);

            $commentaire = null;
            switch ($row["fk_service"]) {
                case 4:
                    $commentaire = 'RECHARGE CARTE JULA';
                    break;
                case 5:
                    $commentaire = 'TRANSFERT CARTE A CARTE';
                    break;
                case 9:
                    $commentaire = 'PAIEMENT MARCHAND';
                    break;
                case 10:
                    $commentaire = 'CASH OUT';
                    break;
                case 11:
                    $commentaire = 'TRANSFERT CASHOUT';
                    break;
                case 12:
                    $commentaire = 'RECHARGE ESPECE';
                    break;
                case 14:
                    $commentaire = 'TAXE';
                    break;
                case 16:
                    $commentaire = 'TRANFERT CARTE A COMPTE';
                    break;
                case 15:
                    $commentaire = 'COMMISSION';
                    break;
                case 17:
                    $commentaire = 'CASHOUT PARTENAIRE';
                    break;
                case 18:
                    $commentaire = 'Vente De carte EDKcash Particulier';
                    break;
                case 19:
                    $commentaire = 'VENT CARTE EDKcash COMMERCANT';
                    break;
                case 20:
                    $commentaire = 'TRANSFERT CARTE TO CASH';
                    break;
                case 21:
                    $commentaire = 'ACHAT CODE NEOSURF';
                    break;
                case 22:
                    $commentaire = 'RECHARGE NEOSURF';
                    break;
                case 23:
                    $commentaire = 'PAIMENT API';
                    break;
                case 26:
                    $commentaire = 'ACHAT CREDIT TELEPHONIQUE';
                    break;
                case 25:
                    $commentaire = 'TRANSFERT CARTE TO CASHPART';
                    break;
                case 28:
                    $commentaire = 'REFOUND TRANSFERT';
                    break;
            }
            $nestedData[] = $commentaire;

            $data[] = $nestedData;
        }


        $json_data = array(
            "draw" => intval($requestData['draw']),
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format*/
    }

    public function ventecartejula($params)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(61, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $taille = count($params);
        if ($taille > 0) {
            if ($params[0] == sha1('solde_insuffisant')) {
                $paramsview = array('view' => 'compte/vente-jula-carte', 'alert' => $data['lang']['solde_agence_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('erreur_ajout_vente')) {
                $paramsview = array('view' => 'compte/vente-jula-carte', 'alert' => $data['lang']['vente_carte_jula_echec'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_insuffisant')) {
                $paramsview = array('view' => 'compte/vente-jula-carte', 'alert' => $data['lang']['stockjula_insuffisant'], 'type-alert' => 'danger');
            }
            if ($params[0] == sha1('stock_epuise')) {
                $paramsview = array('view' => 'compte/vente-jula-carte', 'alert' => $data['lang']['stock_insuffisant'], 'type-alert' => 'danger');
            }
        } else {
            $paramsview = array('view' => 'compte/vente-jula-carte');
        }

        $this->view($paramsview, $data);
    }

    public function stockJULA()
    {
        $montant = $this->utils->securite_xss($_POST['montant']);
        echo json_encode($this->compteModel->getStockJULA($this->userConnecter->fk_agence, $montant));
    }

    public function vendrecarte()
    {

        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(61, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $montant = $this->utils->securite_xss($_POST['montant']);
        $nombre = $this->utils->securite_xss($_POST['nombre']);
        $commission = $this->compteModel->commissionJULA($montant);
        $soldeagence = $this->compteModel->soldeAgence($this->userConnecter->fk_agence);
        $montant_ttc = intval(intval(intval($montant) + intval($commission)) * $nombre);

        $pdo = $this->utils->getPDO();

        if ($soldeagence >= $montant_ttc) {

            $stock = $this->compteModel->getStockJULA($this->userConnecter->fk_agence, $montant,$pdo);

            if ($stock >= $nombre) {

                $result = 0;
                $resultat = $this->compteModel->vendreCarteJULA($montant, $commission, $nombre, $this->userConnecter->fk_agence, $this->userConnecter->rowid,$this->utils);

                if (count($resultat)>0){
                    $result = $resultat['num_transac'] ;
                }else{
                    $result =  $resultat;
                }
                if ($result > 0){

                    $this->rediriger("compte", "cartejula/" . base64_encode($result));
                }
                else if ($result == -2) {

                    $this->rediriger("compte", "ventecartejula/" . sha1('stock_epuise'));
                }
                else {
                    $this->rediriger("compte", "ventecartejula/" . sha1('erreur_ajout_vente'));
                }

            }
            else {

                $this->rediriger("compte", "ventecartejula/" . sha1('stock_insuffisant'));
            }
        }
        else {

            $this->rediriger("compte", "ventecartejula/" . sha1('solde_insuffisant'));
        }
    }



    public function cartejula($id)
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(61, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['cartes'] = $this->compteModel->cartesVente(base64_decode($id[0]));

        if (intval(base64_decode($id[0])) > 0) {
            $type_alert = 'success';
            $alert = $data['lang']['carte_vendues'];
        } else {
            $type_alert = 'error';
            $alert = $data['lang']['echec_vente_carte'];
        }

        $paramsview = array('view' => 'compte/carte-jula-vendu','alert' => $alert,'type-alert' => $type_alert);
        $this->view($paramsview, $data);
    }

    public function virementdemasse()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(179, $this->userConnecter->profil));
        $data['etape'] = base64_encode(1);

        $fkagence = $this->userConnecter->fk_agence;
        $alert = '';
        $type_alert = '';
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if (isset($_POST['etape']) && base64_decode($_POST['etape']) == 2) {


            $mimes = array('application/vnd.ms-excel', 'text/csv');

            $alert = $data['lang']['mess_virem_masse4'];

            $type_alert = 'danger';
            if (isset($_FILES['fichier'], $_POST['etape']) && $_FILES['fichier']['error'] != '4' && in_array($_FILES['fichier']['type'], $mimes)) {
                $fichier = $_FILES['fichier'];
                $nomfichier = $this->utils->getDateNow('WITH_TIME') . '-' . $this->userConnecter->rowid;
                if ($fichier['size'] < 1024000) {

                    $fichier = $this->utils->setUploadFiles($fichier, 'app/documents/virement_de_masse/', $nomfichier);
                } else {
                    $fichier = false;

                    $alert = $data['lang']['mess_virem_masse5'];
                }
                if ($fichier != false) {
                    $nomfichier = 'app/documents/virement_de_masse/' . $nomfichier;
                    $nameFile = $nomfichier . "." . pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);

                    $data['comptes']['traite'] = [];
                    $data['comptes']['non-traite'] = [];
                    $data['comptes']['non-trouve'] = [];
                    $totalSolde = 0;
                    $totalSoldeNonTrouve = 0;

                    if (($handle = fopen($nameFile, "r")) !== FALSE) {
                        while (($dataFile = fgetcsv($handle, 1000, ";")) !== FALSE){

                            $montant = (int)str_replace(' ', '', $dataFile[1]);
                            $telephone = str_replace(' ', '', $dataFile[0]);
                            $result = substr($telephone, 0, 2);
                            if ($result !== '00') {
                                $telephone = '00' . $telephone;
                            }

                            $info = $this->compteModel->beneficiaireByTelephone($telephone);

                            if ($info !== -1 && $info !== -2) {
                                $data['comptes']['traite'][] = ['prenom' => $info->prenom, 'nom' => $info->nom, 'telephone' => $info->telephone, 'montant' => $montant, 'typecompte' => $info->typecompte, 'idcarte' => $info->idcarte];
                                $totalSolde += $montant;
                            } else {
                                $data['comptes']['non-trouve'][] = ['prenom' => 'Inconnu', 'nom' => 'Inconnu', 'telephone' => $telephone, 'montant' => $montant, 'agence' => 'Inconnu', 'tel' => 'Inconnu'];
                                $totalSoldeNonTrouve += $montant;
                            }
                        }
                        fclose($handle);
                        $solde_agence = $this->agenceModel->consulterSoldeAgence($fkagence);
                        if ($totalSolde > (int)$solde_agence) {
                            $alert = $data['lang']['mess_virem_masse6'];
                        } else {
                            $data['comptes']['montant_total'] = $totalSolde;
                            $data['comptes']['montant_total_not_found'] = $totalSoldeNonTrouve;
                            $this->getSession()->setAttributArray('comptes', $data['comptes']);
                            $this->rediriger('compte', 'virementdemassesuite');
                        }
                    } else {
                        $alert = $data['lang']['mess_virem_masse7'];
                    }
                }
            }
        }


        $params = array('view' => 'compte/virement_de_masse', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);

    }

    public function virementdemassesuite()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';
        if ($this->getSession()->existeAttribut('comptes')) {
            $dataFile = $this->getSession()->getAttribut('comptes');

            $data['compteNonTraite'] = $dataFile['non-trouve'];
            $data['compteTraite'] = $dataFile['traite'];
            $data['nbrLigneNonTrouve'] = count($data['compteNonTraite']);
            $data['nbrLigneTraite'] = count($data['compteTraite']);
            $data['montant'] = $dataFile['montant_total'];
            $data['montant_total_not_found'] = $dataFile['montant_total_not_found'];
        } else {
            $this->rediriger('compte', 'virementdemasse');
        }

        $this->getSession()->setAttributArray('comptes', $data['compteTraite']);
        $params = array('view' => 'compte/virement_de_masse_suite', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);

    }

    public function virementdemassefin()
    {

        $service = ID_SERVICE_VIREMENT_MASSE;
        $fkagence = $this->userConnecter->fk_agence;
        $commentaire = "VIREMENT DE MASSE";
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $type_alert = $alert = '';

        $data['traite'] = [];
        $data['non-traite'] = [];
        $totalSolde = 0;
        $totalSoldeNonTraitee = 0;


        if ($this->getSession()->existeAttribut('comptes')) {
            $dataFile = $this->getSession()->getAttribut('comptes');
            $montant_total = $dataFile['montant_total'];

            $taille = count($dataFile);
            $solde_agence = $this->agenceModel->consulterSoldeAgence($fkagence);

            if ($montant_total <= $solde_agence) {

                for ($i = 0; $i < $taille; $i++) {
                    $numtransact = $this->utils->Generer_numtransaction();
                    $fkcarte = $dataFile[$i]['idcarte'];
                    $telephone = $dataFile[$i]['telephone'];
                    $montant = $dataFile[$i]['montant'];
                    $prenom = $dataFile[$i]['prenom'];
                    $nom = $dataFile[$i]['nom'];
                    $typecompte = $dataFile[$i]['typecompte'];

                    if ($typecompte == 0) {
                        $username = 'Numherit';
                        $userId = 1;
                        $token = $this->utils->getToken($userId);
                        $soldeavant = $this->api_numherit->soldeCompte($username, $token, $telephone);
                        $jjs = json_decode($soldeavant);
                        $soldeavant = $jjs->{'statusMessage'};
                        $response = $this->api_numherit->crediterCompte($username, $token, $telephone, $montant, $service, $this->userConnecter->rowid, $fkagence);
                        $decode_response = json_decode($response);

                        if ($decode_response->{'statusCode'} == 000) {
                            $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                            $soldeapres = $this->api_numherit->soldeCompte($username, $token, $telephone);
                            $jjs = json_decode($soldeapres);
                            $soldeapres = $jjs->{'statusMessage'};
                            @$this->utils->addMouvementCompteClient($numtransact, $soldeavant, $soldeapres, $montant, $telephone, $operation = "CREDIT", $commentaire = "VIREMENT DE MASSE");
                            $message = $data['lang']['mess_virem_masse1'] . $prenom . " " . $nom . $data['lang']['mess_virem_masse2'] . $this->utils->nombre_form($montant) . $data['lang']['mess_virem_masse3'];
                            $statut = 1;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = $decode_response->{'NumTransaction'};

                            @$this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $this->userConnecter->rowid, $statut, $commentaire . ' ' . $message . ' ' . $telephone . ' ' . $montant, $frais = 0, $fkagence, $transactId);
                            @$this->utils->log_journal('Virement de masse', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $this->userConnecter->rowid);

                            $data['traite'][] = ['prenom' => $prenom, 'nom' => $nom, 'telephone' => $telephone, 'montant' => $montant, 'typecompte' => $typecompte, 'idcarte' => $fkcarte];
                            $totalSolde += $montant;


                        } else {

                            $data['non-traite'][] = ['prenom' => $prenom, 'nom' => $nom, 'telephone' => $telephone, 'montant' => $montant, 'comment' => $decode_response->{'statusMessage'}];
                            $totalSoldeNonTraitee += $montant;
                            $message = $decode_response->{'statusMessage'};
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $this->userConnecter->rowid, $statut = 0, $commentaire . ' ' . $message . ' ' . $telephone . ' ' . $montant, $frais = 0, $fkagence, $transactId);
                            $this->utils->log_journal('Virement de masse', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $decode_response->{'statusMessage'}, 2, $this->userConnecter->rowid);
                        }
                    } else if ($typecompte == 1) {
                        $numcarte = $this->compteModel->getNumCarte($telephone);
                        $numeroserie = $this->utils->returnCustomerId($numcarte);
                        $last4digitclient = $this->utils->returnLast4Digits($numcarte);
                        $statut = 0;
                        $json = $this->api_gtp->LoadCard($numtransact, $numeroserie, $last4digitclient, $montant, 'XOF', 'VIREMENTDEMASSE');
                        $return = json_decode("$json");
                        $response = $return->{'ResponseData'};
                        if ($response != NULL && is_object($response)) {
                            if (array_key_exists('ErrorNumber', $response)) {
                                $data['non-traite'][] = ['prenom' => 'Inconnu', 'nom' => 'Inconnu', 'telephone' => $telephone, 'montant' => $montant, 'agence' => 'Inconnu', 'tel' => 'Inconnu'];
                                $totalSoldeNonTraitee += $montant;

                                $errorNumber = $response->{'ErrorNumber'};
                                $message = $response->{'ErrorMessage'};
                                $transactId = 0;
                                $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $this->userConnecter->rowid, $statut, $commentaire . ' ' . $message . ' ' . $telephone . ' ' . $montant, $frais = 0, $fkagence, $transactId);
                                $this->utils->log_journal('Virement de masse', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $errorNumber . '-' . $message, 2, $this->userConnecter->rowid);

                            } else {
                                $transactionId = $response->{'TransactionID'};
                                $message = 'Succes';
                                if ($transactionId > 0) {
                                    $data['traite'][] = ['prenom' => $prenom, 'nom' => $nom, 'telephone' => $telephone, 'montant' => $montant, 'typecompte' => $typecompte, 'idcarte' => $fkcarte];
                                    $totalSolde += $montant;
                                    $statut = 1;
                                    $this->agenceModel->debiter_soldeAgence($montant, $fkagence);
                                    $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $this->userConnecter->rowid, $statut, $commentaire . ' ' . $message . ' ' . $telephone . ' ' . $montant, $frais = 0, $fkagence, $transactionId);
                                    $this->utils->log_journal('Virement de masse', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $this->userConnecter->rowid);
                                }
                            }
                        } else {
                            $data['non-traite'][] = ['prenom' => 'Inconnu', 'nom' => 'Inconnu', 'telephone' => $telephone, 'montant' => $montant, 'agence' => 'Inconnu', 'tel' => 'Inconnu'];
                            $totalSoldeNonTraitee += $montant;
                            $message = 'Response GTP not object';
                            $transactId = 0;
                            $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte, $this->userConnecter->rowid, $statut, $commentaire . ' ' . $message . ' ' . $telephone . ' ' . $montant, $frais = 0, $fkagence, $transactId);
                            $this->utils->log_journal('Virement de masse', 'Téléphone compte:' . $telephone . ' Montant:' . $montant . ' Frais:' . $frais . ' Numtransact:' . $numtransact, $message, 2, $this->userConnecter->rowid);
                        }
                    }
                }
                $this->utils->log_journal('Virement de masse', ' Total ligne:' . $taille . ' ligne traitée : ' . count($data['comptes']['traite']) . ' ligne non traitée : ' . count($data['comptes']['non-traite']) . ' montant : ' . $data['montant'], 'succes', 1, $this->userConnecter->rowid);
                $data['montant'] = $totalSolde;
                $params = array('view' => 'compte/virement_de_masse_fin', 'alert' => $alert, 'type-alert' => $type_alert);
                $this->view($params, $data);

            } else {
                $this->rediriger('compte', 'virementdemasse');
            }
        } else {
            $this->rediriger('compte', 'virementdemasse');
        }
    }

    public function isValide()
    {
        foreach ($_POST as $key => $item) {
            $_POST[$key] = $this->utils->securite_xss($_POST[$key]);
            $val = explode('-', $_POST[$key]);
            unset($_POST[$key]);
            $_POST[$val[1]] = $val[0];
        }
        ($this->agenceModel->isValideModel($_POST)) ? print json_encode(1) : print json_encode(0);
    }

    /*********search Releve carte********/
    public function searchReleveCarte()
    {
        //$this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(49, $this->userConnecter->profil));

        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/search-releve-carte');
        $this->view($params, $data);
    }

    /***************** Historique Releve Carte *********************/
    public function releveCarte()
    {
        $phone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['phone'])));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/relevecarte');
        $this->view($params, $data);
    }

    /***************** processing Releve Carte *********************/
    public function processingReleveCarte($id)
    {
        $requestData = $_REQUEST;

        $columns = array(
            // datatable column index  => database column name
            0 => 'num_transac',
            1 => 'date_transaction',
            2 => 'solde_avant',
            3 => 'solde_apres',
            4 => 'montant',
        );

        //getting total number records without any search
        $date1 = $this->utils->securite_xss($id[0]);
        $date2 = $this->utils->securite_xss($id[1]);
        $phone = trim(str_replace("+", "00", $this->utils->securite_xss($id[2])));


        $sql = "SELECT DISTINCT t.id, t.num_transac, t.date_transaction, t.solde_avant, t.solde_apres, t.montant, t.operation, t.commentaire
            FROM releve_comptes_client t
            WHERE t.idcompte =:tel
            AND t.num_transac IS NOT NULL AND t.num_transac != ''
            AND DATE(t.date_transaction) >=:date1 
            AND DATE(t.date_transaction) <=:date2 ";

//var_dump($sql);exit;
        if ($requestData['search']['value']!="") {

            $sql .= " AND ( t.date_transaction LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR t.solde_avant LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR t.solde_apres LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR t.montant LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR t.operation LIKE '" . $requestData['search']['value'] . "%' ";
            $sql .= " OR  t.commentaire LIKE '" . $requestData['search']['value'] . "%' )";
        }

        $tabCol = ['t.solde_avant', 't.solde_apres', 't.montant', 't.operation', 't.commentaire'];
        if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
            $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
        $sql .= " LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        $user = $this->getConnexion()->prepare($sql);
        $user->execute(array("tel" => $phone, "date1" => $date1, "date2" => $date2));
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;
        $data = array();

        foreach ($rows as $row) {  //preparing an array

            $nestedData = array();

            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = '<span style="text-align: right !important;">'.$this->utils->number_format($row["solde_avant"]).'</span>';
            $nestedData[] = '<span style="text-align: right !important;">'.$this->utils->number_format($row["montant"]).'</span>';
            $nestedData[] = '<span style="text-align: right !important;">'.$this->utils->number_format($row["solde_apres"]).'</span>';
            $nestedData[] = $row["operation"];
            $nestedData[] = $row["commentaire"];

            $data[] = $nestedData;
        }

        $json_data = array(
            "draw" => intval($requestData['draw']),
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );

        echo json_encode($json_data);  // send data as json format*/
    }


    /***************** recu jour *********************/
    public function printreleve()
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $tel = $this->utils->securite_xss($_POST['tel']);

        $data['tel'] = $tel;
        $data['date1'] = $date1;
        $data['date2'] = $date2;
        $data['releve'] = $this->compteModel->releveAll($date1, $date2, $tel);


        // var_dump($data['releve']); die();

        $params = array('view' => 'compte/relevepdf');
        $this->view($params,$data);
    }


    /***************** recu jour *********************/
    public function facturejour()
    {


        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date1'], $_POST['date2']);
        $carte = $this->utils->securite_xss($_POST['numserie']);

        $nomcomplet = $this->userConnecter->prenom ." ". $this->userConnecter->nom;
        $agence = $this->userConnecter->agence ;
        $data['nomComplet']= $nomcomplet;
        $data['agence']= $agence;

        if($carte[0] === '+')
            $carte = str_replace('+', '00', $carte);
            $data['recu'] = $this->facturejourPdf($date1, $date2, $carte);
        //echo "<pre>"; var_dump($data['recu']);die();


        $params = array('view' => 'compte/facturerecu');
        $this->view($params,$data);
    }

    public function facturejourExcel()
    {


        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $date1 = $this->utils->securite_xss($_POST['date1']);
        $date2 = $this->utils->securite_xss($_POST['date2']);
        $data['date'] = $this->utils->securite_xss($_POST['date1'], $_POST['date2']);
        $carte = $this->utils->securite_xss($_POST['numserie']);


        $nomcomplet = $this->userConnecter->prenom ." ". $this->userConnecter->nom;
        $agence = $this->userConnecter->agence ;
        $data['nomComplet']= $nomcomplet;
        $data['agence']= $agence;

        if($carte[0] === '+')
            $carte = str_replace('+', '00', $carte);
        $rows = $this->facturejourPdf($date1, $date2, $carte);

        //echo "<pre>"; var_dump($data['recu']);die();


        $total=0;
        $totalttc=0;
        $nb=0;
        $com=0;

        /*echo "<pre>";
        print_r($rows);die();*/

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=historiqueTransacCompte.xls");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo "\xEF\xBB\xBF";


        echo "<table width='642' border='1' align='center' cellpadding='20' cellspacing='0'>
              <tr align='center' valign='top'>
                <td width='11%'><strong>". $data['lang']['date']."</strong></td>
                <td width='9%'><strong>". $data['lang']['numero']."</strong></td>
                <td width='11%'><strong>".$data['lang']['montant_sans_ttc']."</strong></td>
                <td width='11%'><strong>".$data['lang']['frais']."</strong></td>
                <td width='11%'><strong>".$data['lang']['montant_ttc']."</strong></td>
                <td width='11%'><strong>".$data['lang']['effectuer_par']."</strong></td>
                <td width='12%'><strong>".$data['lang']['agence']."</strong></td>
            </tr>";


        foreach($rows as $row_transact)
        {
            $montant_ttc=$row_transact['montant']+$row_transact['commission'];

            echo "<tr align='center' valign='middle'>
                <td>".$this->utils->date_fr4($row_transact['date_transaction'])."</td>
                <td>". $row_transact['num_transac']."</td>
                <td align='right'>". $this->utils->number_format($row_transact['montant'])."</td>
                <td align='right'>". $this->utils->number_format($row_transact['commission'])."</td>
                <td align='right'>". $this->utils->number_format($montant_ttc)."</td>
                <td align='left'>". $nomcomplet ."</td>
                <td align='left'>". $agence."</td>
            </tr>";

            $total+= $row_transact['montant'];
            $nb+= 1;
            $com+= $row_transact['commission'];
            $totalttc+= $montant_ttc;
        }

        echo "</table>
        <br/>
        <table width='40%' border='1' align='center' cellpadding='10' cellspacing='2' class='table_form'>
            <tr class='txt_form1'>
                <td width='20%' align='center' valign='top'>Montant Total </td>
                <td width='21%' align='center' valign='top'>Total Commission </td>
                <td width='24%' align='center' valign='top'>Montant Total TTC </td>
                <td width='29%' align='center' valign='top'>Nombre de Transactions </td>
            </tr>
            <tr>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($total)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($com)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $this->utils->number_format($totalttc)."</strong></td>
                <td align='center' valign='top' bgcolor='#FFFFFF'><strong>". $nb."</strong></td>
            </tr>
        </table>";
    }





    /*********Liste Compte et attente de validation *********/

    public function compteenattente()
    {
        $this->utils->Restreindre($this->userConnecter->admin, $this->utils->Est_autoriser(37, $this->userConnecter->profil));
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'compte/compteenattente');
        $this->view($params, $data);
    }

    /***************** processing compteenattente *********************/
    public function processingCompteEnAttente()
    {
        $param = [
            "button" => [
                [ROOT . "compte/detailCompteEE/", "fa fa-search"]
            ],
            "args" => null,
            "lang" => $this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->compteModel, "allBenefCEE", $param);
    }

    /*********detail Beneficiaire********/
    public function detailCompteEE($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['benef'] = $this->compteModel->getBeneficiaireByIdEE(base64_decode($id[0]));

        if (base64_decode($id[1]) == 1) {
            $type_alert = 'success';
            $alert = $data['lang']['message_success_update_CompteEE'];
        }
        if (base64_decode($id[1]) == -1) {
            $type_alert = 'error';
            $alert = $data['lang']['message_error_update_CompteEE'];
        }

        $params = array('view' => 'compte/detail-CompteEE', 'alert' => $alert, 'type-alert' => $type_alert);
        $this->view($params, $data);

    }

    public function validerCompteEE($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);

        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['telephone'])));
        $email = $this->utils->securite_xss($_POST['email']);

        $update = $this->compteModel->UpdateCarte($rowid, $user_modification);
        if ($update == 1) {

            $subjet = "Validation Creation Compte";
            $contenue = "Une demande de validation de compte vient d'etre effectuée. De numéro de téléphone : ".$telephone.". MAURIPOST Money VOUS REMERCIE.";
            $nom_client =  " ";
            $this->utils->sendMailAlert(MAIL_VALIDATION, $contenue, $subjet, $nom_client);
            $this->utils->log_journal('Validation Compte en Attente','idbenef:'.$rowid.' Telephone : '.$telephone, 'succés', 2, $user_modification);

        } else {
            $this->utils->log_journal('Validation Compte en Attente','idbenef:'.$rowid.' Telephone : '.$telephone, 'echec', 2, $user_modification);

        }
        $this->rediriger('compte', 'detailCompteEE/' . base64_encode($rowid) . '/' . base64_encode($update));
    }


    public function RejeterCompteEE($id)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $user_modification = $this->userConnecter->rowid;
        $rowid = $this->utils->securite_xss($_POST['rowid']);

        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $telephone = trim(str_replace("+", "00", $this->utils->securite_xss($_POST['telephone'])));;
        $email = $this->utils->securite_xss($_POST['email']);

        $update = $this->compteModel->UpdateCarteRejet($rowid, $user_modification);
        if ($update == 1) {
            $message = 'Rejet Creation Compte . Paositra Money VOUS REMERCIE.';
            $this->utils->sendSMS($data['lang']['paositra1'],$telephone,$message);
            $this->utils->envoiMailER($email, $prenom, $nom, $telephone);
            $this->utils->log_journal('Rejet Compte en Attente','idbenef:'.$rowid, 'succés', 1, $user_modification);

        } else {
            $this->utils->log_journal('Rejet Compte en Attente','idbenef:'.$rowid, 'echec', 1, $user_modification);

        }
        $this->rediriger('compte', 'detailCompteEE/' . base64_encode($rowid) . '/' . base64_encode($update));
    }




    public function facturejourPdf($date1, $date2, $phone)
    {



        $next = '';
        $where = '';

        $type_profil = $this->utils->typeProfil($this->userConnecter->profil);

        if($this->userConnecter->admin == 1 || $this->userConnecter->type_profil == 1){
            $next.=", region";
            $where.=" AND agence.province = region.idregion";
        }
        else if($this->userConnecter->type_profil == 2)
        {
            $next.=", region";
            $where.=" AND agence.province = region.idregion AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else if($this->userConnecter->type_profil == 4)
        {
            $where.=" AND transaction.fk_agence=".$this->userConnecter->fk_agence;
        }
        else{
            $where.=" AND transaction.fkuser=".$this->userConnecter->rowid;
        }


        $sql = "SELECT DISTINCT transaction.rowid, transaction.num_transac, transaction.fk_carte, transaction.montant, transaction.commission, transaction.statut, 
			transaction.commentaire,  transaction.fk_service,
            transaction.date_transaction 
            FROM transaction, carte,user, agence " . $next . "
            WHERE transaction.statut = 1
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND DATE(transaction.date_transaction) >=:date1 
            AND DATE(transaction.date_transaction) <=:date2 
            AND carte.telephone =:tel
            AND transaction.fk_carte = carte.rowid " . $where;

        try
        {
            $user = $this->getConnexion()->prepare($sql);
            $user->bindParam("date1",  $date1 );
            $user->bindParam("date2",  $date2 );
            $user->bindParam("tel",  $phone );
            $user->execute();
            $rows = $user->fetchAll();
            $totalData = $user->rowCount();
            return $rows;
        }
        catch(Exception $e)
        {
            echo 'Error: -99'; die;
        }
    }

}