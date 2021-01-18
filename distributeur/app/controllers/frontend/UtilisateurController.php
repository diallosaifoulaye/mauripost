<?php

/**
 * Created by IntelliJ IDEA.
 * User: Madiop
 * Date: 15/02/2017
 * Time: 21:11
 */


class UtilisateurController extends \app\core\FrontendController
{

    private $utils_utilisateur;
    private $connexion;
    private $userConnecter;


    public function __construct()
    {

        $this->utils_utilisateur = new \app\core\UtilsUtilisateur();
        $this->connexion = \app\core\Connexion::getConnexion();
        parent::__construct('utilisateur');
        $this->getSession()->est_Connecter('objconnect');
        $this->obj = $this->getSession()->getAttribut('objconnect');
        $this->userConnecter = $this->getSession()->getAttribut('objconnect');



    }


    /**********************************************************************************************************************************************/
    /*
     *  Afficher la view des formulaires d'ajout
     */
    public function ajoutform()
    {

        $paramsview = array('view' => 'backend/utilisateurform');
        $this->view($paramsview,$this->oneElement('profil', '',''));
    }

    public function modifierform($id)
    {
        $data['profil'] = $this->oneElement('profil', '','');
        $data['usermodif'] = $this->detailElement($id[0]);
        $paramsview = array('view' =>sprintf('backend/%smodform','utilisateur'), 'title' => sprintf('Modifier %s ','utilisateur') );
        $this->view($paramsview, $data);
    }

    /*********Liste User*********/
    public function users()
    {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();
        $params = array('view' => 'frontend/admin/user');
        $this->view($params,$data);
    }


    /*************Insert User**************/
    public function inserUser()
    {

        $obj = $this->getSession()->getAttribut('objconnect');
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $telephone = trim(str_replace("+", "00",$this->utils->securite_xss($_POST['phone'])));;
        $profil = $this->utils->securite_xss($_POST['profil']);
        $cni = $this->utils->securite_xss($_POST['cni']);
        $idtype_agence = 3;
        $agence = $obj->getFk_agence();
        $login = $this->utils->securite_xss($_POST['login']);
        $password = $this->utils->generation_code(10);
        $user_creation = $obj->getRowid();

        $verif = $this->utils_utilisateur->verifEmail($email);

        if($verif == -1){
            $insert = $this->utils_utilisateur->insertUser($nom, $cni,$prenom, $email, $telephone, $profil, $agence, $login, $password, $user_creation);

            if($insert==1)
            {
                if($idtype_agence == 1){
                    $this->utils->envoiparametre($prenom.' '.$nom, $email, $login, $password);
                }
                if($idtype_agence == 3){
                    $this->utils->envoiparametreDistributeur($prenom.' '.$nom, $email, $login, $password);
                }

                $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'succes', 1, $user_creation);
                $this->rediriger('utilisateur','validationInsert/'.base64_encode('ok'));
            }
            else
            {
                $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
                $this->rediriger('utilisateur','validationInsert/'.base64_encode('nok'));
            }
        }
        else if($verif == 1){
            $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
            $this->rediriger('utilisateur','validationInsert/'.base64_encode('nok2'));
        }
        else{
            $this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
            $this->rediriger('utilisateur','validationInsert/'.base64_encode('nok3'));
        }


    }


    /***********Validation Insert User**********/
    public function validationInsert($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();
        //$data['agence']= $this->agenceModel->allAgence();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'frontend/admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_success_add_user'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'frontend/admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user'], 'type-alert'=>'alert-danger');
        }
        elseif(base64_decode($return[0])=== 'nok2'){
            $params = array('view' =>'frontend/admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user2'], 'type-alert'=>'alert-danger');
        }
        elseif(base64_decode($return[0])=== 'nok3'){
            $params = array('view' =>'frontend/admin/user', 'title' =>$data['lang']['list_users'], 'alert'=>$data['lang']['message_error_add_user3'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************update User**************/
    public function updateUser()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $nom = $this->utils->securite_xss($_POST['nom']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $email = $this->utils->securite_xss($_POST['email']);
        $telephone = $this->utils->securite_xss($_POST['phone']);
        $profil = $this->utils->securite_xss($_POST['profil']);
        $cni = $this->utils->securite_xss($_POST['cni']);
        $agence = $obj->getFk_agence();
        $user_modification = $obj->getRowid();
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));

        $update = $this->utils_utilisateur->updateUtilisateur($nom, $cni,$prenom, $email, $telephone, $profil, $agence, $rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Modification Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Profil:'.$profil, 'succés', 1, $user_modification);
            $this->rediriger('utilisateur','validationUpdate/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Modification Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Profil:'.$profil, 'echec', 1, $user_modification);
            $this->rediriger('utilisateur','validationUpdate/'.base64_encode('nok'));
        }
    }


    /***********Validation Update User**********/
    public function validationUpdate($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();


        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'frontend/admin/user','title' =>$data['lang']['list_users'] ,'alert'=>$data['lang']['message_success_update_user'],'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'frontend/admin/user','title' =>$data['lang']['list_users'] ,'alert'=>$data['lang']['message_error_update_user'],'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }


    /*********detailUser********/
    public function detailUser($id)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();
        $data['user']= $this->utils_utilisateur->getUser(base64_decode($id[0]));
        $params = array('view' => 'frontend/admin/user-detail');
        $this->view($params,$data);
    }

    public function setDataAgence($id)
    {
        $data = $this->utils_utilisateur->getAllAgenceByType($id[0]);
        $result = '<select class="select3" required style="width: 100%;" id="agence" name="agence"><option selected="selected" value="">'.$data['agence']['select_agence'].'</option>';
        foreach ($data as $item) {

            $result .= '<option value="'.$item['rowid'].'">'.$item['label'].'</option>';
        }
        $result .= '</select>';
        echo $result;
    }
    /******* Action verifier identifiant ****/
    public function verifLogin()
    {
        $verif = $this->utils_utilisateur->verifIdentifiant($this->utils->securite_xss($_POST['identifiant']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /******* Action verifier email ****/
    public function verifEmail()
    {
        $verif = $this->utils_utilisateur->verifEmail($this->utils->securite_xss($_POST['email']));
        if($verif==1) echo 1;
        elseif($verif==-2) echo -2;
        else echo -1;
    }

    /*************Desactiver User**************/
    public function desactiverUser()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_modification = $obj->getRowid();
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $update = $this->utils_utilisateur->deleteUtilisateur($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Désactivation Utilisateur', 'Iduser desactivé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('utilisateur','validationdesactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Désactivation Utilisateur', 'Iduser desactivé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('utilisateur','validationdesactiver/'.base64_encode('nok'));
        }
    }

    /***********Validation Desactiver User**********/
    public function validationdesactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'frontend/admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_success_delete'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'frontend/admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_error_delete'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }

    /*************Activer User**************/
    public function activerUser()
    {
        $obj = $this->getSession()->getAttribut('objconnect');

        $user_modification = $obj->getRowid();
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $update = $this->utils_utilisateur->activerUtilisateur($rowid, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Activation Utilisateur', 'Iduser activé:'.$rowid, 'succès', 1, $user_modification);
            $this->rediriger('utilisateur','validationactiver/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Activation Utilisateur', 'Iduser activé:'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('utilisateur','validationactiver/'.base64_encode('nok'));
        }
    }
    /***********Validation Activer User**********/
    public function validationactiver($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'frontend/admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_activer_user'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'frontend/admin/user','title' => $data['lang']['list_users'],'alert'=>$data['lang']['message_error_activer_user'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }


    public function envoiNewPass($destinataire, $email, $login, $password) {

        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));


        $sujet = $data['lang']['resetPasswordUser0']; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete ='';
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> ".$data['lang']['mess_virem_masse1'] ." ".$destinataire.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$data['lang']['resetPasswordUser1']."<br />";
        $message.= "".$data['lang']['resetPasswordUser2']."<br />";
        $message.=  $data['lang']['identifiant']." :".$login."<br />";
        $message.=  $data['lang']['motdepasse']." :".$password."<br />";
        $message.= "<a href='". BASE_URL2 ."' target='_blank'>".$data['lang']['resetPasswordUser3']."</a>";
        $message.= "<br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>".$data['lang']['resetPasswordUser4']."<br /><br />".$data['lang']['resetPasswordUser5']."</td>";
        $message.= "</tr>";
        $message.= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=utf8\r";
        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:Paositra <no-reply@paositra.mg>\r";
        mail($vers_mail, $sujet, $message, $entete);


    }

    /*************reset password User**************/
    public function resetPasswordUser()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $password = $this->utils->generation_code(10);
        $user_modification = $obj->getRowid();
        $rowid = base64_decode($this->utils->securite_xss($_POST['iduser']));
        $email = $this->utils->securite_xss($_POST['email']);
        $prenom = $this->utils->securite_xss($_POST['prenom']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $login = base64_decode($this->utils->securite_xss($_POST['login']));

        $update = $this->utils_utilisateur->resetPasswordUtilisateur($rowid, $password, $user_modification);
        if($update==1)
        {
            $this->utils->log_journal('Regénération Mot de Passe Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'succes', 1, $user_modification);
            $this->envoiNewPass($prenom.' '.$nom, $email, $login, $password);
            $this->rediriger('utilisateur','validationresetPassword/'.base64_encode('ok'));
        }
        else
        {
            $this->utils->log_journal('Regénération Mot de Passe Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Login:'.$login.' Iduser'.$rowid, 'echec', 1, $user_modification);
            $this->rediriger('utilisateur','validationresetPassword/'.base64_encode('nok'));
        }
    }

    /***********Validation Reset Password User**********/
    public function validationresetPassword($return)
    {
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $data['profil']= $this->utils_utilisateur->allProfil();

        if(base64_decode($return[0])=== 'ok'){
            $params = array('view' =>'frontend/admin/user', 'title' => $data['lang']['list_users'], 'alert'=>$data['lang']['message_success_regenere'], 'type-alert'=>'alert-success');
        }
        elseif(base64_decode($return[0])=== 'nok'){
            $params = array('view' =>'frontend/admin/user', 'title' => $data['lang']['list_users'], 'alert'=>$data['lang']['message_error_regenere'], 'type-alert'=>'alert-danger');
        }
        $this->view($params,$data);
    }


    /***************** Liste users *********************/
   /* public function processingUser()
    {
        $param = [
            "button"=>[
                [ROOT."admin/detailUser/","fa fa-search"]
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->utils_utilisateur,"allUser",$param);
    }*/

    public function processingUser()
    {

        $obj = $this->getSession()->getAttribut('objconnect');
        $user_crea = $obj->getRowid();
        $agence = $obj->getFk_agence();

        $requestData= $_REQUEST;
        $columns = array(
            // datatable column index  => database column name
            0=> 'prenom',
            1=> 'nom',
            2=> 'email',
            3=> 'telephone',
            4=> 'profil',
            5=> 'agence'

        );

        $sql = "Select u.rowid, u.nom, u.prenom, u.email, u.telephone, p.label as profil, u.etat
                from user as u 
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid where u.fk_agence=".$agence;


        $user = $this->connexion->prepare($sql);

        $user->execute();
        $rows = $user->fetchAll();
        $totalData = $user->rowCount();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


        $sql = "Select u.rowid, u.nom, u.prenom, u.email, u.telephone, p.label as profil, u.etat
                from user as u
                LEFT OUTER JOIN profil as p
                ON u.fk_profil = p.rowid
                LEFT OUTER JOIN agence as a
                ON u.fk_agence = a.rowid where u.fk_agence=".$agence;


        if(!empty($requestData['search']['value']) ) {
            $etat = (strtolower($requestData) == 'activer' ) ? 1 : ((strtolower($requestData) == 'desactiver') ? 0 : null);
            $sql.=" AND ( u.prenom LIKE '%".$requestData."%' ";
            $sql.=" OR u.nom LIKE '%".$requestData."%' ";
            $sql.=" OR  u.email LIKE '%".$requestData."%' ";
            $sql.=" OR u.telephone LIKE '%".$requestData."%' ";
            $sql.=" OR p.label LIKE '%".$requestData."%' ";
            if($etat !== null) $sql.=" OR u.etat = ".$etat;
            $sql.=" OR a.label LIKE  '%".$requestData."%' )";
        }

        $user = $this->connexion->prepare($sql);
        $user->execute();
        $rows = $user->fetchAll();
        $totalFiltered = $user->rowCount();
        //$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

        $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        /* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */

        $user = $this->connexion->prepare($sql);
        $user->execute();
        $rows = $user->fetchAll();
        $data = array();
        foreach( $rows as $row) {  // preparing an array
            $nestedData=array();
            if ($row["etat"]==1) $statut='Activé'; else $statut='Désactivé';
            $nestedData[] = $row["prenom"];
            $nestedData[] = $row["nom"];
            $nestedData[] = $row["email"];
            $nestedData[] = $row["telephone"];
            $nestedData[] = $row["profil"];
            $nestedData[] = $statut;
            $nestedData[] = "<a  href='".ROOT."utilisateur/detailUser/".base64_encode($row["rowid"])."'><i class='fa fa-search'></i></a>";
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

    public function checkCNI(){
        if($this->utils->securite_xss($_POST['_type'] == "2")){
            $id = str_replace(' ','',$_POST['id']);
            $id = base64_decode($id);
            $verif = $this->utils_utilisateur->verifCNIUpdate($this->utils->securite_xss($_POST['cni']),$id);
            if($verif==1) echo 1;
            elseif($verif==-2) echo -2;
            else echo -1;
        } else {
            $verif = $this->utils_utilisateur->verifCNI($this->utils->securite_xss($_POST['cni']));

            if($verif==1) echo 1; //exist
            elseif($verif==-2) echo -2;
            else echo -1;
        }

        
    }




}