<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */

date_default_timezone_set('Indian/Antananarivo');
require_once __DIR__.'/../../core/ApiGTP/ApiBAnque.php';

class AccueilController extends \app\core\FrontendController
{

    private $connexion;
    private $api_gtp;

    public function __construct()
    {
        $this->connexion = new \app\core\Connexion();
        $this->api_gtp = new ApiBAnque();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    public function index()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = ($this->getSession()->getAttribut('objconnect'));
        $agence = $obj->getFk_agence();



        $solde_carte =  $this->infsoCarteDist($agence);

        $numeroserie = $solde_carte->num_carte;
        $solde =$this->utils->getSoldeAgence($agence);
        $json = json_decode("$solde");
        $responseData = $json->{'ResponseData'};
        if($responseData != NULL && is_object($responseData))
        {
            if(array_key_exists('ErrorNumber', $responseData))
            {
                $message = $responseData->{'ErrorNumber'};
                $data['soldeCarte'] = 0;
            }
            else
            {
                $data['soldeCarte'] = $responseData->{'Balance'};
            }
        }
        else $data['soldeCarte'] = 0;

        $paramsview = array('view' => sprintf('frontend/dashboard'));
        $this->view($paramsview, $data);
    }

    public function index1()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/dashboard'));
        $this->view($paramsview, $data);
    }

    public function dashbord()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/mydashboard'));
        $this->view($paramsview, $data);
    }

    public function appelfond()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = ($this->getSession()->getAttribut('objconnect'));
        $agence = $obj->getFk_agence();
        $solde_carte =  $this->infsoCarteDist($agence);
        $numeroserie = $solde_carte->num_carte;
        $solde = $this->api_gtp->ConsulterSolde($numeroserie,'6325145878');
        $json = json_decode("$solde");
        $responseData = $json->{'ResponseData'};
        if($responseData != NULL && is_object($responseData))
        {
            if(array_key_exists('ErrorNumber', $responseData))
            {
                $message = $responseData->{'ErrorNumber'};
                $data['soldeCarte'] = 0;
            }
            else
            {
                $data['soldeCarte'] = $responseData->{'Balance'};
            }
        }
        else $data['soldeCarte'] = 0;

        $paramsview = array('view' => sprintf('frontend/appeldefonds'));
        $this->view($paramsview, $data);
    }


    public function monsolde()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();

        $sql = "SELECT solde FROM agence WHERE rowid= :id";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->bindValue('id', filter_var($agence, FILTER_SANITIZE_NUMBER_INT));
            $stmt->execute();
            $user = $stmt->fetchObject();
            $this->connexion->closeConnexion();

            if($user->solde != null || $user->solde >= 0) $data['solde'] = $this->utils->nombre_form($user->solde);
            else $data['solde'] = $data['lang']['solde_indisponible'];

        } catch (Exception $e) {
            $data['solde'] = $data['lang']['solde_indisponible'];
        }

        $paramsview = array('view' => sprintf('frontend/monsolde'));
        $this->view($paramsview, $data);
    }


    public function Histo_soldeAgence()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/historiquesolde'));
        $this->view($paramsview, $data);
    }

    public function listSolde()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $userid = $obj->getRowid();
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $fk_agence = $obj->getFk_agence();
        $duplicata = $data['lang']['duplicata'];


        $requestData= $_REQUEST;
        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_transaction',
            1=> 'num_transac',
            2=> 'solde_avant',
            3=> 'montant',
            4=> 'solde_apres',
            5=> 'operation',
            6=> 'commentaire'
        );



        $sql = "SELECT DISTINCT date_transaction,num_transac,solde_avant,montant,solde_apres,operation,commentaire
            FROM releve_solde_agence
            WHERE fk_agence= :fk_agence";


        $user = $this->connexion->getConnexion()->prepare($sql);

        $user->execute(
            array(
                "fk_agence" => $fk_agence,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "SELECT DISTINCT date_transaction,num_transac,solde_avant,montant,solde_apres,operation,commentaire
            FROM releve_solde_agence
            WHERE fk_agence= :fk_agence";
        if( !empty($requestData['search']['value']) ) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND (num_transac LIKE '%" . $_REQUEST['search']['value'] . "%'";
            $sql .= " OR date_transaction LIKE '%" . $_REQUEST['search']['value'] . "%' ";
            $sql .= " OR montant LIKE '%" . $_REQUEST['search']['value'] . "%' ";
            $sql .= " OR commentaire LIKE '%" . $_REQUEST['search']['value'] . "%' ";
            $sql .= " OR solde_avant LIKE '%" . $_REQUEST['search']['value'] . "%' ";
            $sql .= " OR solde_apres LIKE '%" . $_REQUEST['search']['value'] . "%' ";
            $sql .= " OR operation LIKE '%" . $_REQUEST['search']['value'] . "%' )";
        }
        $user = $this->connexion->getConnexion()->prepare($sql);

        $user->execute(
            array(
                "fk_agence" => $fk_agence,
            )
        );

        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();
        //$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

            $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

        $user = $this->connexion->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "fk_agence" => $fk_agence,
                )
            );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            $nestedData[] = $this->utils->date_fr4($row["date_transaction"]);
            $nestedData[] = $row["num_transac"];
            $nestedData[] = $this->utils->number_format($row["solde_avant"]);
            $nestedData[] = $this->utils->number_format($row["montant"]);
            $nestedData[] = $this->utils->number_format($row["solde_apres"]);
            $nestedData[] = $row["operation"];
            $nestedData[] = $row["commentaire"];
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

    public function infsoCarteDist($agence)
    {
        $sql = "SELECT * FROM agence WHERE rowid=:agence";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("agence" => $agence));
            $result = $stmt->fetchObject();
            return $result;

        } catch (\PDOException $e) {
            return -1;
        }
    }

    /**************Privates********************************************/
    private function saveAppelDeFond($numero, $libelle_operation, $utilisateur, $agence, $resultat_operation, $num_transac, $solde_avant = 0, $solde_apres = 0, $statut = 0, $montant = 0)
    {
        $date_envoie = date("Y-m-d H:i:s");
        $query_insert = "INSERT INTO operations_agence( numero,libelle_operation,date_operation,utilisateur,agence,resultat_operation,num_transac, solde_avant, solde_apres, statut, montant) ";
        $query_insert .= " VALUES ( :numero,:libelle_operation,:date_operation,:utilisateur,:agence,:resultat_operation,:num_transac ,:solde_avant ,:solde_apres ,:statut ,:montant)";
        try {
            $rs_insert = $this->connexion->getConnexion()->prepare($query_insert);
            $rs_insert->bindParam(':numero', $numero);
            $rs_insert->bindParam(':libelle_operation', $libelle_operation);
            $rs_insert->bindParam(':date_operation', $date_envoie);
            $rs_insert->bindParam(':utilisateur', $utilisateur);
            $rs_insert->bindParam(':agence', $agence);
            $rs_insert->bindParam(':resultat_operation', $resultat_operation);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':solde_avant', $solde_avant);
            $rs_insert->bindParam(':solde_apres', $solde_apres);
            $rs_insert->bindParam(':statut', $statut);
            $rs_insert->bindParam(':montant', $montant);
            $result = $rs_insert->execute();

            $this->connexion->closeConnexion();
            if ($result > 0) return 1;
            else return 0;

        } catch (PDOException $e) {
            return -1;
        }
    }

    private function appelDeFond($agence, $user, $montant, $solde_agence)
    {
        $sql = "SELECT idcard FROM agence WHERE rowid=:agence";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("agence" => $agence));
            $result = $stmt->fetchObject();
            $num_carte = $result->idcard;
            $this->connexion->closeConnexion();

            $customerId = $this->utils->returnCustomerId($num_carte);
            $last4Digits = $this->utils->returnLast4Digits($num_carte);
            $num_transac = $this->utils->Generer_numtransaction();
            $currencyCode = 'XOF';
            $referenceMemo = 'Transfer de fond distributeur';
            $solde_apres = $solde_agence - $montant;

            $api = new ApiBAnque();

            $fn_call = $api->LoadCard($num_transac, $customerId, $last4Digits, $montant, $currencyCode, $referenceMemo);
            $json = json_decode("$fn_call");
            $response = $json->{'ResponseData'};
            if ($response != NULL && is_object($response)) {
                if (array_key_exists('ErrorNumber', $response)) {
                    $errorNumber = $response->{'ErrorNumber'};
                    $errorMessage = $response->{'ErrorMessage'};

                    $statut = 0;
                    $this->saveAppelDeFond($num_carte, 'Appel de fonds', $user, $agence, 'ERREURE REPONSE : ErrorNumber-' . $errorNumber . ' ErrorMessage-' . $errorMessage, $num_transac, $solde_agence, $solde_agence, $statut, $montant);

                    $this->utils->log_user('Appel de fonds échoué', "numero carte-" . $num_carte . " Agence-" . $agence . " montant-" . $montant . "", $agence, 'ERREURE REPONSE : ErrorNumber-' . $errorNumber . ' ErrorMessage-' . $errorMessage, 0);
                    return -2;
                } else {
                    $transactionId = $response->{'TransactionID'};
                    if ($transactionId > 0) {
                        $debitAgence = $this->utils->debiterSoldeAgence($montant, $agence);

                        if ($debitAgence == true) {
                            $statut = 1;
                            $this->saveAppelDeFond($num_carte, 'Appel de fonds', $user, $agence, 'SUCCES', $num_transac, $solde_agence, $solde_apres, $statut, $montant);

                            $this->utils->log_user('Appel de fonds reussi', "numero carte-" . $num_carte . " Agence-" . $agence . " montant-" . $montant . "", $agence, 'SUCCES', 0);
                            return 1;
                        }
                    } else {
                        $statut = 0;
                        $this->saveAppelDeFond($num_carte, 'Appel de fonds', $user, $agence, 'TransactionID innexistant dans la REPONSE', $num_transac, $solde_agence, $solde_agence, $statut, $montant);

                        $this->utils->log_user('Appel de fonds en instance', "numero carte-" . $num_carte . " Agence-" . $agence . " montant-" . $montant . "", $agence, 'TransactionID innexistant dans la REPONSE', 0);
                        return -3;
                    }
                }
            } else {
                $statut = 0;
                $this->saveAppelDeFond($num_carte, 'Appel de fonds', $user, $agence, 'REPONSE WS NON OBJET', $num_transac, $solde_agence, $solde_agence, $statut, $montant);

                $this->utils->log_user('Appel de fonds échoué', "numero carte-" . $num_carte . " Agence-" . $agence . " montant-" . $montant . "", $agence, 'REPONSE WS NON OBJET', 0);
                return -2;
            }
        } catch (Exception $e) {
            return -1;
        }
    }

    /**************Privates********************************************/

    public function appelfondpost()
    {
        $montant = $this->utils->securite_xss($_POST['amount']);
        $solde_compte = $this->utils->securite_xss(base64_decode($_POST['solde_compte']));
        $obj = ($this->getSession()->getAttribut('objconnect'));
        $agence = $obj->getFk_agence();
        $user = $obj->getRowid();
        $appelDeFond = $this->appelDeFond($agence, $user, $montant, $solde_compte);
        $sd = $solde_compte - $montant;
        
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $data['sd'] = base64_encode($sd);
        $data['xs'] = base64_encode($appelDeFond);

        $paramsview = array('view' => sprintf('frontend/appeldefondssuite'));
        $this->view($paramsview, $data);
    }

    public function myusers(){

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/myusers'));
        $this->view($paramsview, $data);
    }

    public function userlist(){
        $requestData= $_REQUEST;


        $columns = array(
// datatable column index  => database column name
            0=> 'nom',
            1=> 'login',
            2=> 'email',
            3=> 'tel',
            4=> '',
        );

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));


        $obj = ($this->getSession()->getAttribut('objconnect'));
        $agence = $obj->getFk_agence();
// getting total number records without any search
        $sql = "SELECT CONCAT(prenom,' ', nom) as username, email, telephone, rowid, login, etat FROM user WHERE fk_agence=:user ";
        $user = $this->connexion->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $agence,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

        $sql = "SELECT CONCAT(prenom,' ', nom) as username, email, telephone, rowid, login, etat FROM user WHERE fk_agence=:user ";

        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql.=" AND ( CONCAT(prenom,' ', nom) LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR login LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR email LIKE '".$requestData['search']['value']."%' ";
            $sql.=" OR telephone LIKE '".$requestData['search']['value']."%' )";
        }

        $user = $this->connexion->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "user" => $agence,
            )
        );
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        $edit = $data['lang']['edit'];
        $delete = $data['lang']['delete'];
        $active = $data['lang']['active'];
        $this->connexion->closeConnexion();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();

            $nestedData[] = $row["username"];
            $nestedData[] = $row["login"];
            $nestedData[] = $row["email"];
            $nestedData[] = $row["telephone"];
            if($row["etat"] == 1){
                $nestedData[] = "<a href='".ROOT."accueil/edituser/".base64_encode($row["rowid"])." ' title='".$edit."'>
		                        <input type='button' class='btn btn-info btn-rounded' value='".$edit."'  />
	                        </a>
	                          |
		                        <input type='button' class='btn btn-warning btn-rounded' id='delete' name='delete' value='".$delete."' onClick='showModal(".$row["rowid"].");' />
	                        ";
            }
            else{
                $nestedData[] = "<a href='".ROOT."accueil/edituser/".base64_encode($row["rowid"])." ' title='".$edit."'>
		                        <input type='button' class='btn btn-info btn-rounded' value='".$edit."'  />
	                        </a>
	                          |
		                        <input type='button' class='btn btn-success btn-rounded' id='active' name='active' value='".$active."' onClick='showModalActive(".$row["rowid"].");' />
	                        ";
            }


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

    public function newuser(){
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/newuser'));
        $this->view($paramsview, $data);
    }

    public function checklogin(){
        $login = $this->utils->securite_xss($_POST['mdp']);

        $sql = "SELECT rowid FROM user WHERE login='" . $login. "' ";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetchObject();
            $this->connexion->closeConnexion();

            if($user->rowid != null || $user->rowid > 0) echo -2;
            else echo 1;

        } catch (Exception $e) {
            echo -1;
        }
    }

    public function checkemail(){
        $login = $this->utils->securite_xss($_POST['mdp']);

        $sql = "SELECT rowid FROM user WHERE email='" . $login. "' ";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetchObject();
            $this->connexion->closeConnexion();

            if($user->rowid != null || $user->rowid > 0) echo -2;
            else echo 1;

        } catch (Exception $e) {
            echo -1;
        }
    }

    public function adduser(){

        $login = $this->utils->securite_xss($_POST['login']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $tel = $this->utils->securite_xss($_POST['tel']);
        $sup = $this->utils->securite_xss($_POST['sup']);
        $admin = $this->utils->securite_xss($_POST['admin']);
        $date_crea = date('Y-m-d H:i:s');

        $obj = $this->getSession()->getAttribut('objconnect');
        $agence = $obj->getFk_agence();
        $user = $obj->getRowid();
        $new_pass = $this->utils->generation_code();
        $pass = md5($new_pass.'AZVERTI@RE2015');

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $sql = "INSERT INTO `user` (nom, prenom, email, telephone, login, password, fk_profil, fk_agence, connect, etat, user_crea, date_crea, superviseur, admin)
							     VALUES (:nom, :prenom, :email, :telephone, :login, :password, :fk_profil, :fk_agence, :connect, :etat, :user_crea, :date_crea, :superviseur, :admin)";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array(
                "nom" => $nom,
                "prenom" => $prenom,
                "email" => $email,
                "telephone" => $tel,
                "login" => $login,
                "password" => $pass,
                "fk_profil" => 5,
                "fk_agence" => $agence,
                "connect" => 0,
                "etat" => 1,
                "user_crea" => $user,
                "date_crea" => $date_crea,
                "superviseur" => $sup,
                "admin" => $admin
            ));
            $this->connexion->closeConnexion();
            $data['err'] = 1;

            $sujet = $data['lang']['new_user'];

            $msg =  $data['lang']['new_user1'].' '.$prenom.' '.$nom.', <br/>'.$data['lang']['new_user2'].'<br/>'.$data['lang']['new_user3'];

            $vers_mail = $email;
            $message = "<table width='550px' border='0'>";
            $message .= "<tr>";
            $message .= "</tr>";
            $message .= "<tr>";
            $message .= "<td align='left' valign='top'><p>" . $msg . "<br /><br /><b>".$data['lang']['new_user4']." : ".$data['lang']['new_user5']." <br/> ".$data['lang']['login']." : ".$login." <br/>".$data['lang']['pass']." : ".$new_pass." <br/> </b>"  ;

            $message .= "</p></td>";
            $message .= "</tr>";

            $message .= "</table>";
            $entete = "Content-type: text/html; charset=utf8\r\n";
            $entete .= "From: POSTECASH <no-reply@postecash.com>\r\n";
            mail($vers_mail, $sujet, $message, $entete);

        }
        catch (Exception $e) {
            $data['err'] = -1;
        }

        $paramsview = array('view' => sprintf('frontend/myusers'));
        $this->view($paramsview, $data);

    }

    public function saveedituser(){

        $user = $this->utils->securite_xss(base64_decode($_POST['user']));
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $tel = $this->utils->securite_xss($_POST['tel']);
        $sup = $this->utils->securite_xss($_POST['sup']); if($sup == 'on') $sup = 1;
        $admin = $this->utils->securite_xss($_POST['admin']); if($admin == 'on') $admin = 1;
        $date_crea = date('Y-m-d H:i:s');

        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $sql = "UPDATE `user` SET nom=:nom, prenom=:prenom, email=:email, telephone=:telephone, user_modif=:user_crea, date_modif=:date_crea, superviseur=:superviseur, admin=:admin WHERE rowid=:user";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array(
                "nom" => $nom,
                "prenom" => $prenom,
                "email" => $email,
                "telephone" => $tel,
                "user_crea" => $user_crea,
                "date_crea" => $date_crea,
                "superviseur" => $sup,
                "admin" => $admin,
                "user" => $user
            ));
            $this->connexion->closeConnexion();
            $data['err'] = 2;

            $sujet = $data['lang']['edit_user'];

            $msg =  $data['lang']['new_user1'].' '.$prenom.' '.$nom.', <br/>'.$data['lang']['edit_user1'].'<br/>'.$data['lang']['new_user3'];

            $vers_mail = $email;
            $message = "<table width='550px' border='0'>";
            $message .= "<tr>";
            $message .= "</tr>";
            $message .= "<tr>";
            $message .= "<td align='left' valign='top'><p>" . $msg . "<br /><br /><b>".$data['lang']['new_user4']." : ".$data['lang']['new_user5']." <br/> </b>"  ;

            $message .= "</p></td>";
            $message .= "</tr>";

            $message .= "</table>";
            $entete = "Content-type: text/html; charset=utf8\r\n";
            $entete .= "From: POSTECASH <no-reply@postecash.com>\r\n";
            mail($vers_mail, $sujet, $message, $entete);

        }
        catch (Exception $e) {
            $data['err'] = -1;
        }

        $paramsview = array('view' => sprintf('frontend/myusers'));
        $this->view($paramsview, $data);

    }

    public function deleteuser(){

        $delete = $this->utils->securite_xss($_POST['deleteuser']);
        $date_crea = date('Y-m-d H:i:s');

        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $sql = "UPDATE `user` SET etat=0, user_modif=:user_crea, date_modif=:date_crea WHERE rowid=:user";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array(
                "user_crea" => $user_crea,
                "date_crea" => $date_crea,
                "user" => $delete));
            $this->connexion->closeConnexion();
            $data['err'] = 3;
        }
        catch (Exception $e) {
            $data['err'] = -1;
        }

        $paramsview = array('view' => sprintf('frontend/myusers'));
        $this->view($paramsview, $data);

    }

    public function activeuser(){

        $delete = $this->utils->securite_xss($_POST['activeuser']);
        $date_crea = date('Y-m-d H:i:s');

        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();

        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

        $sql = "UPDATE `user` SET etat=1, user_modif=:user_crea, date_modif=:date_crea WHERE rowid=:user";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array(
                "user_crea" => $user_crea,
                "date_crea" => $date_crea,
                "user" => $delete));
            $this->connexion->closeConnexion();
            $data['err'] = 4;
        }
        catch (Exception $e) {
            $data['err'] = -1;
        }

        $paramsview = array('view' => sprintf('frontend/myusers'));
        $this->view($paramsview, $data);

    }

    public function edituser($id){

        $user = $this->utils->securite_xss(base64_decode($id[0]));

        $sql = "SELECT rowid,nom, prenom, email, telephone, login, superviseur, admin FROM `user` WHERE rowid=:user";

        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("user" => $user));
            $retour = $stmt->fetchAll();

            $this->element->setObject($retour[0]);
            $retour = $this->element->getObject();
            $data['user'] = $retour;

            $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));

            $paramsview = array('view' => sprintf('frontend/edituser'));
            $this->view($paramsview, $data);

        } catch (Exception $e) {
            echo -1;
        }

    }
}