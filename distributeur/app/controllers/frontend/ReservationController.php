<?php
/**
 * Created by PhpStorm.
 * User: developpeur3
 * Date: 23/08/2017
 * Time: 08:33
 */

date_default_timezone_set('Indian/Antananarivo');
class ReservationController extends \app\core\FrontendController
{

    private $utils_reservation;
    private $connexion;

    public function __construct()
    {
        $this->utils_reservation = new \app\core\UtilsReservation();
        $this->connexion = \app\core\Connexion::getConnexion();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
        $this->obj = $this->getSession()->getAttribut('objconnect');

    }

    public function index()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/facturier/index'));
        $this->view($paramsview, $data);
    }



    /**********************************
     *
     * RESERVATION TRANSPOST
     */

    public function reservationtranspost()
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['itineraires'] = $this->utils_reservation->itineraires();
        $data['reservation'] = $this->utils_reservation->getReservationByUserCreaString($user_creation);
         $paramsview = array('view' => 'frontend/reservation/transpost');
        $this->view($paramsview,$data);

    }

    public function getItineraireFees()
    {
        $insert = $this->utils_reservation->getItineraireByIdString($this->utils->securite_xss($_POST['itineraire']));
          echo $insert->tarif;
    }

    public function getPlace()
    {
        $insert = $this->utils_reservation->getPlaceByIdString($this->utils->securite_xss($_POST['place']));
        echo $insert->nb_place;
    }

    public function getItinerairePlaces()
    {
        $insert = $this->utils_reservation->getItineraireByIdString($this->utils->securite_xss($_POST['itineraire']));
        echo $insert->place_dispo;
    }
    public function Histo_reservations()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/reservation/Histo_reservation'));
        $this->view($paramsview, $data);
    }
    public function processingReservation()
    {
      $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();

        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'date_crea',
            1=> 'lieu_depart',
            2=> 'lieu_arrivee',
            3=> 'date_trajet',
            4=> 'heure_depart',
            5=> 'heure_arrivee',
            6=> 'client',
            7=> 'tarif',
            8=> 'montant'
        );


        $sql = "Select r.rowid, r.date_crea, r.client, CONCAT(i.lieu_depart,' ==> ',i.lieu_arrivee) as itineraire, i.date_trajet as date, 
                    CONCAT(i.heure_depart,' ==> ',i.heure_arrivee) as trajet, r.montant as montant 
                from reservation_pv as r
                LEFT OUTER JOIN itineraire_pv as i
                ON r.itineraire = i.rowid  ";

         $sql.=" WHERE r.user_crea = :user_crea";

        $user = $this->connexion->prepare($sql);
        //$query=mysqli_query($conn, $sql) or die("agence-grid-data.php: get employees");

        $user->execute(
            array(
                "user_crea" => $user_crea,
            )
        );
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "Select r.rowid, r.date_crea, r.client, CONCAT(i.lieu_depart,' ==> ',i.lieu_arrivee) as itineraire, i.date_trajet as date, 
                    CONCAT(i.heure_depart,' ==> ',i.heure_arrivee) as trajet, r.montant as montant 
                from reservation_pv as r
                LEFT OUTER JOIN itineraire_pv as i
                ON r.itineraire = i.rowid  ";
        $sql.=" WHERE r.user_crea = :user_crea";
        if( !empty($requestData['search']['value']) ) {

            $sql.="  AND ( i.lieu_depart LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR i.lieu_arrivee LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  i.date_trajet LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  i.heure_depart LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  i.heure_arrivee LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR  r.client LIKE '%".$_REQUEST['search']['value']."%' ";
            $sql.=" OR i.tarif LIKE '%".$_REQUEST['search']['value']."%' )";;
        }

        $user = $this->connexion->prepare($sql);

            $user->execute(
                array(
                    "user_crea" => $user_crea,
                )
            );

        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();
        //$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        /* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */

        $user = $this->connexion->prepare($sql);
        $user->execute(
            array(
                "user_crea" => $user_crea,
            )
        );
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            $nestedData[] = $row["date_crea"];
            $nestedData[] = $row["client"];
            $nestedData[] = $row["itineraire"];
            $nestedData[] = $row["date"];
            $nestedData[] = $row["trajet"];
            $nestedData[] = $row["montant"];
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ),// total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );

        // echo 1;
        echo json_encode($json_data);  // send data as json format

    }


    public function insertReservation()
    {

        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();
        $_POST['user_crea'] = $user_crea;
        $_POST['date_crea'] = date('Y-m-d H:i:s');
        $fk_agence = $obj->getFk_agence();
        $service = ID_SERVICE_TRANSPOST;
        $idtrans = ID_CARTE_TRANSPOST;
        $solde = $this->utils->getSoldeAgence($fk_agence);

        $montant = $this->utils->securite_xss($_POST['montant']);

        $taux = $this->utils->getTauxDistributeur($service);

        $frais = $this->utils->calculFrais($service);

        $montant_frais_commission = ($frais * (100 - floatval($taux)) / 100);

        $solde_minimal = $montant + $montant_frais_commission;


        if ($solde >= $solde_minimal)
        {
            $this->connexion->beginTransaction();
            $result_debit = $this->utils->debiterSoldeAgence($solde_minimal, $fk_agence, $this->connexion);
            if ($result_debit == 1)
            {
                $result1 = $this->utils->crediterCarteCommission($montant_frais_commission, $this->connexion);
                $result2 = $this->utils->crediter_carteParametrable($this->connexion, $this->utils->securite_xss($_POST['montant']), $idtrans);
                if ($result1 == 1 && $result2 == 1)
                {
                    $insert = $this->utils_reservation->insertReservation($this->utils->securite_xss_array($_POST));
                    $numtransact = $this->utils->Generer_numtransaction();
                    if ($insert == 1)
                    {
                        $commentaire = 'Sauvegarde de la transaction';
                        $result_ST = $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_crea, $statut = 1, $commentaire, $frais, $fk_agence, $transactId = 0);
                        if ($result_ST == 1)
                        {
                            $this->connexion->commit();
                            $soldeapres = $this->utils->getSoldeAgence($fk_agence);
                            $operation = "DEBIT";
                            $commentaire = "Reservation Transpost";
                            $result_SR = $this->utils->addMouvementCompteAgence($numtransact, $solde, $soldeapres, $solde_minimal, $fk_agence, $operation, $commentaire, $this->connexion);
                            if ($result_SR)
                            {
                                $this->utils->log_journal('insertReservation', 'client:' . $this->utils->securite_xss($_POST['client']) . ' itineraire:' . $this->utils->securite_xss($_POST['itineraire']), 'succes', 10, $user_crea);
                                $this->rediriger('reservation', 'validationReservations/' . base64_encode('ok'));
                            }
                        }
                    }
                    else {
                        $statut_log="ECHEC";
                        $commentaire = 'Echec de la Sauvegarde de la transaction';
                        $this->utils->SaveTransaction($numtransact, $service, $montant, $fkcarte = 0, $user_crea, $statut = 0, $commentaire, $frais, $fk_agence, $transactId = 0);
                        $this->utils->log_journal('insertReservation', $commentaire, $statut_log, 10, $user_crea);
                        $this->connexion->rollBack();
                        $this->rediriger('reservation', 'validationReservations/' . base64_encode('nok'));

                    }
                }
                else {
                    $statut_log = "ECHEC";
                    $commentaire = 'Echec lors de l\'opération: Créditer Carte Commission ou Créditer Reservation Transpost';
                    $this->utils->log_journal('insertReservation', $commentaire, $statut_log, 10, $user_crea);
                    $this->connexion->rollBack();
                    $this->rediriger('reservation', 'validationReservations?data=' . base64_encode('nok'));
                }
            }
            else {
                $statut_log = "ECHEC";
                $commentaire = 'Echec lors de l\'opération: Débiter Solde Agence';
                $this->utils->log_journal('insertReservation', $commentaire, $statut_log, 10, $user_crea);
                $this->connexion->rollBack();
                $this->rediriger('reservation', 'validationReservations?data=' . base64_encode('nok'));
            }

        } else {
            $statut_log = "ECHEC";
            $commentaire = 'Echec: Solde de l\'agence insuffisante !!';
            $this->utils->log_journal('insertReservation', $commentaire, $statut_log, 10, $user_crea);
            $this->rediriger('reservation', 'validationReservations?data=' . base64_encode('nokSolde'));
        }
    }

    public function validationReservations($return)
    {
        $data['lang'] = $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_creation = $obj->getRowid();
        $data['itineraires'] = $this->utils_reservation->itineraires();
        $data['reservation'] = $this->utils_reservation->getReservationByUserCreaString($user_creation);

        if (base64_decode($return[0]) === 'ok') {
            $params = array('view' => 'frontend/reservation/transpost', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest9'], 'type-alert' => 'alert-success');
        } elseif (base64_decode($return[0]) === 'nok') {
            $params = array('view' => 'frontend/reservation/transpost', 'title' => $data['lang']['bp_gest2'], 'alert' => $data['lang']['bp_gest10'], 'type-alert' => 'alert-danger');
        }

        $this->view($params, $data);
    }


}