<?php

class CcpController extends \app\core\BaseController
{
    public $ccpModel;
    private $agenceModel;
    private $carteModel;
    private $userConnecter;


    public function __construct()
    {
        parent::__construct();
        $this->ccpModel = $this->model('CcpModel');
        $this->agenceModel = $this->model('AgenceModel');
        $this->carteModel = $this->model('CarteModel');
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');
        $this->userConnecter = $this->getSession()->getAttribut('OBJECT_CONNECTION')[0];
    }

    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES BENEFICIARES                                        //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////

    /*********Liste Beneficiaire*********/
    public function index($arg)
    {
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $result = base64_decode($arg[0]);
        if($result > 0){
            $params = array('view' => 'ccp/acceuil', 'alert' => $data['lang']['lier_carte_compte_success'].': '.$result , 'type-alert' => 'success');
        }
        else if($result == -1){
            $params = array('view' => 'ccp/acceuil', 'alert' => $data['lang']['lier_carte_compte_error_1'] , 'type-alert' => 'danger');
        }
        else if($result == -3){
            $params = array('view' => 'ccp/acceuil', 'alert' => $data['lang']['lier_carte_compte_error_2'] , 'type-alert' => 'danger');
        }
        else if($result == -4){
            $params = array('view' => 'ccp/acceuil', 'alert' => $data['lang']['lier_carte_compte_error_3'] , 'type-alert' => 'danger');
        }
        else{
            $params = array('view' => 'ccp/acceuil');
        }



        $this->view($params,$data);
    }

    public function liercarte(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(isset($_POST['numero_serie']) && !empty($_POST['numero_serie'])){
            $tel = $this->utils->securite_xss($_POST['numero_serie']);
            if(substr($tel, 0, 1) == '+')
            {
                $tel = substr($tel, 1);
                $tel = '00'.$tel;
            }
            $data['benef']= $this->ccpModel->beneficiaireByNumeroSerie($tel);
            $params = array('view' => 'ccp/lier-carte', 'alert' => $data['lang']['numero_tel_inexistant'], 'type-alert' => 'danger');
        }
        else{
            $params = array('view' => 'ccp/lier-carte');
        }

        $this->view($params,$data);
    }

    public function comptecarte(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(isset($_POST['numero']) && !empty($_POST['numero'])){
            $numero = $this->utils->securite_xss($_POST['numero']);
            $data['benef']= $this->ccpModel->beneficiaireByNumero($numero);
            $data['typecompte']= $this->ccpModel->typeCompte();

            $data['codesms']= $this->generer_codesmsVo($data['lang']);
            $params = array('view' => 'ccp/compte-carte', 'alert' => $data['lang']['numero_tel_inexistant'], 'type-alert' => 'danger');
            $this->view($params,$data);
        }
        else{
            //$this->utils->log_journal('Ajout Utilisateur', 'Prenom:'.$prenom.' Nom:'.$nom.' Email:'.$email.' Tel:'.$telephone.' Login:'.$login, 'echec', 1, $user_creation);
            $this->rediriger('ccp','liercarte');
        }

    }


    public function generer_codesmsVo($lang){

        $found = 0;
        do{
            $code = $this->utils->random(6);
            $etat = $this->ccpModel->verifyCode($code);
            if($etat == 1){
                $found = 1;
            }
        }
        while($found == 0);
        $mail = $this->ccpModel->recup_mail($this->userConnecter->rowid);

        if($mail != '' ){
            $etatenvoi = $this->utils->envoiCodeValidationliaison($this->userConnecter->email, $this->userConnecter->prenom.' '.$this->userConnecter->nom, $code, $lang);
            $etatenvoisms = $this->utils->sendSMS('CCP', '00221774119645', 'votre code de liaison: '.$code);
            if($etatenvoi)
            {
                return $code;
            }
            else
            {
                return -1;
            }
        }
    }

    public function validationcompteacarte(){
        $carte = $this->utils->securite_xss($_POST['numero1']);
        $compte = $this->utils->securite_xss($_POST['matricule']);
        $rowid = $this->utils->securite_xss($_POST['idbenef']);
        $email = $this->utils->securite_xss($_POST['email']);
        $nom = $this->utils->securite_xss($_POST['nom']);
        $fk_typeCompte = $this->utils->securite_xss($_POST['type']);
        $codesms = $this->utils->securite_xss($_POST['codesms']);
        $code_gen = $this->utils->securite_xss($_POST['default_code']);
        $statut = 1;
        $user_admin = $this->userConnecter->rowid;
        if($compte != '' && $rowid != '' && $fk_typeCompte != '' && $codesms != '')
        {
            if($codesms === $code_gen)
            {
                $result = $this->ccpModel->insertCompteVO($rowid, $compte, $codesms, $statut, $user_admin);
                //var_dump($result); die;
                if($result > 0)
                {
                    //log_user('Nouvelle liaison de Compte Vo a Carte ',"numero carte-".$carte." montant-".$montant."",$_SESSION['rowid']);
                    $result2 = $this->ccpModel->insertCompteCCPCNE($compte, $rowid, $fk_typeCompte);
                    //var_dump($result); die;
                    if($result2>0)
                    {
                        $this->utils->envoiMailLierCarteCompte($email, $nom, $carte, $compte);
                        $this->rediriger('ccp','index/'.base64_encode($compte));
                    }
                    else
                    {
                        $this->rediriger('ccp','index/'.base64_encode(-1));
                    }

                }
                else{
                    $this->rediriger('ccp','index/'.base64_encode(-1));
                }
            }
            else
            {
                $this->rediriger('ccp','index/'.base64_encode(-3));
            }

        }
        else{
            $this->rediriger('ccp','index/'.base64_encode(-4));
        }
    }

    public function listecarte(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'ccp/carte-compte-list');
        $this->view($params,$data);
    }


    public function processingListVO()
    {
        $param = [
            "button"=>[
                /*[ROOT."compte/detailBenef/","fa fa-search"]*/
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->ccpModel, "getCarteCompteList", $param);
    }


    public function listevo(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'ccp/virement-list');
        $this->view($params,$data);
    }


    public function processingVO()
    {
        $param = [
            "button"=>[
                /*[ROOT."compte/detailBenef/","fa fa-search"]*/
            ],
            "args"=>null,
            "lang"=>$this->lang->getLangFile($this->getSession()->getAttribut('lang'))
        ];
        $this->processing($this->ccpModel, "getVirementList", $param);
    }

    public function vo(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'ccp/vo');
        $this->view($params,$data);
    }

    public function historiquevo(){

        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        $params = array('view' => 'ccp/histo-vo');
        
        if(isset($_POST['telephone']) && isset($_POST['datedebut']) && isset($_POST['datefin']) ){
            $telephone = $this->utils->securite_xss($_POST['telephone']);
            if(substr($telephone, 0, 1) == '+')
            {
                $telephone = substr($telephone, 1);
                $telephone = '00'.$telephone;
            }
            $datedeb = $this->utils->securite_xss($_POST['datedebut']);
            $datefin = $this->utils->securite_xss($_POST['datefin']);
            $data['histo'] = $this->ccpModel->historiqueVO($this->userConnecter->profil, $this->userConnecter->fk_agence, $this->userConnecter->rowid, $this->userConnecter->fk_DR, $telephone, $datedeb, $datefin);
            
        }
        
        $this->view($params,$data);
        
    }

    public function demandevo(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $telephone = $this->utils->securite_xss($_POST['numero_serie']);
        if(substr($telephone, 0, 1) == '+')
        {
            $telephone = substr($telephone, 1);
            $telephone = '00'.$telephone;
        }
        $data['carte'] = $this->ccpModel->getIdCarteByTelephone($telephone);
        $data['comptes'] = $this->ccpModel->getAllCompteByIdCarte($data['carte']);
        $params = array('view' => 'ccp/vo-demande');
        $this->view($params,$data);

    }

    public function validationvo(){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));

        $vo = $this->utils->securite_xss($_POST['vo']);
        $compte = $this->utils->securite_xss($_POST['compte']);
        $vo_mt = $this->utils->securite_xss($_POST['vo_mt']);
        $idcarte = base64_decode($this->utils->securite_xss($_POST['idcarte']));
        if($vo == 0){
            $vo_duree = $this->utils->securite_xss($_POST['vo_duree']);
        }
        else{
            $vo_duree = 0;
        }


        $res = $this->ccpModel->insertVO($vo, $vo_mt, $vo_duree, $idcarte, $compte, $this->userConnecter->fk_agence);
        //var_dump($res); die;
        if($res == 1){
            $this->rediriger('ccp','etatvo/'.base64_encode(1));
        }
        else{
            $this->rediriger('ccp','etatvo/'.base64_encode(0));
        }

    }

    public function etatvo($id){
        $this->utils->Restreindre($this->userConnecter->admin,$this->utils->Est_autoriser(37,$this->userConnecter->profil));
        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));
        if(base64_decode($id[0]) == 1){
            $params = array('view' => 'ccp/after_vo', 'alert' => $data['lang']['vo_ok'], 'type-alert' => 'success');
        }
        else{
            $params = array('view' => 'ccp/after_vo', 'alert' => $data['lang']['vo_ko'], 'type-alert' => 'danger');
        }
        $this->view($params,$data);
    }

    public function codeRefVO()
    {
        $found = 1;

        do{
            $code = $this->utils->generateur(10);
            $etat = $this->ccpModel->verifyTransaction($code);
            if($etat == 0){
                $found = 0;
            }
        }
        while($found == 1);
        return $code;
    }
    
    public function demandeVOPonctuel(){

        $reference = $this->codeRefVO();
        $allbenef = $this->ccpModel->demandeVOPonctuel();
        $csv = '';
        if(count($allbenef) > 0){
            $csv.= "Numero Compte;Nom;Prenom;Montant;Date Demande\n";
            foreach ($allbenef as $benef){
                $etat = $this->ccpModel->updateVO($benef->rowid);
                if($etat == 1){
                    $csv .= $benef->numero_compte. ';'.$benef->nom.';'.$benef->prenom.
                        ';'.$this->utils->nombre_form($benef->montant_vo).';'.$benef->dd_vo."\n";
                }
            }
            $res = $this->ccpModel->ajoutFichierVO($reference);
            if($res == 1){
                 $fichierChemin =__DIR__."/../../ged_ccp/demande/";
               // $fichierChemin ="";
                $nomfichier = $reference;
                $resultCreationfichier = $this->creationFichierVOPonctuel($fichierChemin, $nomfichier, 'csv', $csv, $droit="");
                print($csv);

                /****************Envoie du ficher par ftp*****************/
                $destination="/ccp/non_traites/";
                $urlfichier = "https://pmp-benin.com/ged_ccp/demande/";
                $upload = $this->envoi_ftp($nomfichier.'.csv',$fichierChemin, $destination);
                $this->Mail_VO($fichierChemin.$nomfichier.'.csv');
            }
            else{
                echo -2;
            }

        }
        else{
            echo -1;
        }
    }

    public function creationFichierVOPonctuel($fichierChemin, $nomfichier, $extension, $contenu, $droit=""){
        //$fichierCheminComplet = $_SERVER["DOCUMENT_ROOT"].$fichierChemin."/".$fichierNom;
        $fichierCheminComplet = $fichierChemin."/".$nomfichier;

        if($extension!=""){
            $fichierCheminComplet = $fichierCheminComplet.".".$extension;
        }

        // création du fichier sur le serveur
        $leFichier = fopen($fichierCheminComplet, "wb");
        fwrite($leFichier,$contenu);
        fclose($leFichier);

        // la permission
        if($droit==""){
            $droit="0777";
        }

        // on vérifie que le fichier a bien été créé
        $t_infoCreation['fichierCreer'] = false;
        if(file_exists($fichierCheminComplet)==true){
            $t_infoCreation['fichierCreer'] = true;
        }

        // on applique les permission au fichier créé
        $retour = chmod($fichierCheminComplet,intval($droit,8));
        $t_infoCreation['permissionAppliquer'] = $retour;

        return $t_infoCreation;
    }


    public function envoi_ftp($fichier="default.txt", $source, $destination)
    {
        //$ftp_server='128.65.193.142';
        //$ftp_user_name='lfda_compte_ccp';
        //$ftp_user_pass='P@ssword2011';
        $ftp_server='83.166.140.29';
        $ftp_user_name='nxlh_compte_ccp';
        $ftp_user_pass='yKOTBYN2TMFq';

        // Mise en place d'une connexion basique
        $conn_id = ftp_connect($ftp_server);
        $file = 'somefile.txt';
        $remote_file = 'readme.txt';

        // Mise en place d'une connexion basique
        $conn_id = ftp_connect($ftp_server);

        // Identification avec un nom d'utilisateur et un mot de passe
        $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
        if ((!$conn_id) || (!$login_result))
        {
            return -1;
        }
        else
        {
            if (ftp_put($conn_id,  $destination."".$fichier, $source."".$fichier, FTP_ASCII))
            {
                ftp_chmod ( $conn_id , 0777 ,  $destination."".$fichier );
                return 1;
            }
            else
            {
                return -2;
            }
        }
        // Fermeture du flux FTP
        ftp_close($conn_id);
    }


    public function Mail_VO($file_name)
    {
        //envoi de mail
        $subject = "Generation VO ponctuels "; //Sujet du mail
        //$de_mail = "";
        //$vers_nom = $prenom.' '.$nom;
        //$vers_mail = $destinataire;
        $message= "Merci de trouver en piece jointe la liste des demandes de virements ponctuels effectuees.";
        $boundary = "_".md5 (uniqid (rand()));
        //$file_name='./contrat/Contrat_'.$donnees['reference_contrat'].'.pdf' ;
        $attached_file = file_get_contents($file_name); //file name ie: ./image.jpg
        $attached_file = chunk_split(base64_encode($attached_file));
        $attached = "\n\n". "--" .$boundary . "\nContent-Type: application; name=\"$file_name\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$file_name\"\r\n\n".$attached_file . "--" . $boundary . "--";
        $headers ="From: <no-reply@numherit.com> \r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $body = "--". $boundary ."\nContent-Type: application/pdf; charset=ISO-8859-1\r\n\n".$message . $attached;
        @mail("bocar.sy@numherit.com",$subject,$body,$headers);
        @mail("papa.ngom@numherit.com",$subject,$body,$headers);
        @mail("pape.ndiaye@numherit.com",$subject,$body,$headers);
    }


    public function accordVOPonctuel(){
        $somme_total_crediter = 0;
        $contenu = "";
        $vo_echec = $this->lire_or_repCCP_PAYER($lcl2='',$tab2[0]='', $somme_total_crediter, $contenu);
        /*********************************************************************************************************
         ********************* RECUPERATION DU FICHIER A TRAITER ET DEPLACEMENT APRES TRAITEMENT ******************/
        $distant = "/ccp/traites/";
        //$local2 = "ged_ccp/accord/";
        $local2 = __DIR__."/../../ged_ccp/accord/";
        //$local = "/postecash/traites/";

        $ftp_server='83.166.140.29';
        $ftp_user_name='nxlh_compte_ccp';
        $ftp_user_pass='yKOTBYN2TMFq';

        $conn_id = ftp_connect($ftp_server);
        $return =0;
        //IDENTIFICATION FTP
        $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
        //VERIFICATION DE LA CONNEXION
        if((!$conn_id) || (!$login_result))
        {
            $subject = "Connexion FTP echec VO ponctuels "; //Sujet du mail
            $message= "Merci de trouver en piece jointe la liste des demandes de virements ponctuels effectuees.";
            $headers = "Content-type: text/html; charset=utf8\r\n";
           // $headers .= "To: Admin<$vers_mail> \r\n";
            $headers .= "From:Admin <no-reply@numherit.com>\r\n";
            @mail("bocar.sy@numherit.com",$subject,$message,$headers);
            @mail("papa.ngom@numherit.com",$subject,$message,$headers);
            @mail("pape.ndiaye@numherit.com",$subject,$message,$headers);
            //echo "La connexion FTP a échoué !<br>";
            //echo "Tentative de connexion au serveur $ftp_server pour l'utilisateur $ftp_user_name";

        }
        else
        {
            $liste_fichiers = ftp_nlist($conn_id, $distant);
            //var_dump($liste_fichiers);
            $nb_fichiers = sizeof($liste_fichiers);

            for($indice=0;$indice<$nb_fichiers; $indice++)
            {
                $dt='';
                $lcl='';
                $fichier=$liste_fichiers[$indice];
                //foreach($liste_fichiers as $fichier)
                if(($fichier != $distant.".") && ($fichier != $distant.".."))
                {
                    $tab = explode('/', $fichier);
                    $fch = $tab[3];
                    $tab2 = explode('.', $fch);
                    $fc = $tab2[0].'.csv';//postecash/non_traites/test_envoi.csv
                    $dt = $distant.$fch;
                    $lcl = $local2.$fch;
                    $lcl2 = $local2.$fc;
                    $upload = ftp_get($conn_id, $lcl2, $dt, FTP_ASCII);

                    if (!$upload)
                    {
                        echo "<hr />Le chargement FTP a échoué!";
                    }
                    else
                    {
                        echo "<hr />Le chargement FTP est ok";
                        $vo_echec = $this->lire_or_repCCP_PAYER($lcl2,$tab2[0], $somme_total_crediter, $contenu);
                        if($vo_echec == -1)
                        {
                            ftp_rename($conn_id, $dt, $lcl);
                            //rename($lcl, $lcl2);

                        }
                        else{
                            echo 'Liaison ou repCCP paye OK';
                        }
                        $contenu_fichier = file_get_contents($vo_echec);
                        $nbLignes = substr_count($contenu_fichier, "\n");
                        if($nbLignes > 1){
                            $this->mail_vo_echec($vo_echec);

                        }
                        else{
                            echo 'Aucune ligne dans le fichier';
                        }

                        //rename($lcl, $lcl2);//deplacement des fichiers traites
                        ftp_rename($conn_id, $dt, $lcl);
                    }
                }
                else{
                    echo 'Aucun fichier';
                }

            }

        }
        ftp_close($conn_id);
    }


    public function lire_or_repCCP_PAYER($fichier,$nom, $somme_total_credite, $contenu)
    {

        $msg="";
        //$fichier = $lcl2;
        //$nom = $tab2[0];
        $vo_echec =  __DIR__."/../../ged_ccp/echec/";;
        $csv = '';
        $csv.= "Numero Operation;Numero Compte;Montant;Date Demande;Commentaire\n";
        $nbligne=0;
        $nbligneaexe=0;
        $montantaexe=0;
        $nblignenonexe=0;
        $montantnonexe=0;
        $montant_total = 0;
        $i= 0;
        //$fic=fopen($fichier,"r");
        $fic=fopen(__DIR__."/../../ged_ccp/accord/KhgpQX812e.csv","r");
        while($tab=fgetcsv($fic,4096,';'))
        {
            var_dump($tab); die;
            $operation = $tab[0];
            $libelle_operation = $tab[1];
            //$date = $tab[2];
            //$prop_compte = $tab[3];
            //$num_compte_crediter = $tab[4];
            //$nom_prenom = $tab[5];
            $num_compte_debiter = $tab[6];
            $num_compte_debiter=str_pad($num_compte_debiter, 12, "0", STR_PAD_LEFT);
            $num_compte_debiter=trim($num_compte_debiter,"'");
            //$num_compte_debiter1 = $tab[7];
            $sens = $tab[8];
            $montant=$tab[9];
            //$reference=$tab[10];
            $reqid = $this->utils->generation_numTransaction();
            $infoscarte = $this->ccpModel->existe_carte($num_compte_debiter);

            if($infoscarte != ''){
                if($this->ccpModel->operationExiste($operation)==1)
                {
                    $msg="vo deja traite";
                    $statut=0;
                    /******************save transaction***************************************************/
                    $ladate = date('Y-m-d H:i:s');
                    $this->utils->SaveTransaction(7,$montant,$infoscarte->idcarte,0,0, $statut, $reqid, $msg,$ladate, 0,0);
                    $this->utils->saveDetailsTranscation($reqid, $infoscarte->idcarte, $montant, 1, $ladate);
                    /******************************************************************************************/
                }
                else
                {
                    if($sens=="C")
                    {

                        $benef = $this->ccpModel->verifierBenefCCP($num_compte_debiter);

                        if($benef != ''){
                            $nbligneaexe++;
                            $montantaexe=$montantaexe+$montant;
                            if($this->ccpModel->get_solde_compte() >= $montant)
                            {
                                /**************************************Appel du webservice*************************/
                                $statut=0;
                                $service = 7;
                                $statut_operation=1;
                                $currency="XOF";
                                //$memo='Opération de virement ccp';
                                //$memo=getServiceName(7);
                                $frais = $this->ccpModel->Calcul_frais(7,$montant);
                                $montant_sansfrais = $montant - $frais;
                                $carte_commission = $this->ccpModel->Get_carteCommisssion() ;

                                $typecompte = $this->ccpModel->getTypeCompte($infoscarte->telephone);
                                $ladate = date('Y-m-d H:i:s');
                                if($typecompte == 0) {

                                    $username = 'Numherit';
                                    $userId = 1;
                                    $token = $this->utils->getToken($userId);
                                    $response = $this->api_numherit->crediterCompte($username, $token, $infoscarte->telephone, $montant_sansfrais, $service, $user_creation=0, $fkagence=0);
                                    $decode_response = json_decode($response);
                                    if($decode_response->{'statusCode'}==000)
                                    {
                                        $transactionId = $decode_response->{'NumTransaction'};
                                        $this->ccpModel->SaveOperation($operation, $libelle_operation,$statut_operation);
                                        $res = $this->ccpModel->updateVOTraiter($infoscarte->idcarte);
                                        if($res == 1){
                                            $this->ccpModel->update_solde_compte($montant);
                                            $this->envoiMailVirement($nom, $num_compte_debiter, $montant_sansfrais);
                                            $message = 'Votre carte EdkCash vient d etre credite de '.number_format($montant_sansfrais)." F CFA par le débit de votre compte CCP. Merci de votre confiance";
                                            //$this->sendSMSOrange('EdkCash', $phone, $message);

                                            /**************************************fin*************************/
                                            $contenu.=$infoscarte->telephone.";".$reqid.";".intval($montant).";".intval($statut).";".$msg."\n";
                                            $somme_total_credite=$somme_total_credite+$montant;

                                        }
                                        else{
                                            //echo 'rrrr';
                                            $this->ccpModel->SaveOperation($operation, $libelle_operation,$statut_operation=0);
                                        }


                                        $msg = 'Virement effectue';
                                        $msg1 = 'Debiter solde agence OK';
                                        $msg1 = 'Crediter compte client OK';
                                        $this->utils->SaveTransaction($reqid, $montant_sansfrais, $montant,$infoscarte->idcarte,0, $statut, 'Suuces', $frais, 0, 0);
                                        $this->ccpModel->saveTransactionPoste($reqid, $ladate, $montant_sansfrais, 1, 9586, $infoscarte->idcarte, $msg1, 288);
                                        $this->utils->saveDetailsTranscation($reqid, $infoscarte->idcarte, $montant_sansfrais, '1', $ladate);
                                        $this->ccpModel->log_user('Virement ccp', 'numéro-'.$infoscarte->idcarte.' montant-'.$montant_sansfrais, 9586, 'Virement ccp', 2);
                                        /******************************************************************************************/
                                    }
                                    else{
                                        $msg1 = 'Crediter compte client KO';
                                        $this->utils->SaveTransaction($reqid, $montant_sansfrais, $montant,$infoscarte->idcarte,0, 0, 'Suuces', $frais, 0, 0);
                                        $this->ccpModel->saveTransactionPoste($reqid, $ladate, $montant_sansfrais, 1, 9586, $infoscarte->idcarte, $msg1, 288);
                                        $this->utils->saveDetailsTranscation($reqid, $infoscarte->idcarte, $montant_sansfrais, '1', $ladate);
                                        $this->ccpModel->log_user('Virement ccp', 'numéro-'.$infoscarte->idcarte.' montant-'.$montant_sansfrais, 9586, 'Virement ccp', 2);
                                        /******************************************************************************************/
                                    }
                                        echo 'TTTTT';
                                }

                            }
                            else{
                                echo 'YYYYY';
                            }

                        }
                        else
                        {
                            echo "no money";
                            $this->alerte_compte();
                            return -1;
                        }
                    }
                    else{
                        echo  $msg="Ce beneficiaire n'est pas dans la base ou n'a pas fait de demande de vo";
                        $statut=0;
                        $pct = 0;
                        $csv.= $tab[0].';'.$tab[6].';'.$tab[9].';'.$tab[2].';'.$msg."\n";
                        $montantnonexe = $montantnonexe + $montant;
                        $nblignenonexe++;
                    }

                }
                $nbligne++;
                $montant_total=$montant_total + $montant;
            }
            else{
                echo 'Carte lie a compte introuvable';
                $msg="Cette carte n'est pas dans la base";
                $statut=0;
                $montantnonexe = $montantnonexe + $montant;
                $nblignenonexe++;
                $csv.= $tab[0].';'.$tab[6].';'.$tab[9].';'.$tab[2].';'.$msg."\n";
            }

        }
        $i++;
        $nom = $nom."_echec_vo";
        $chemin = __DIR__.'/../../ged_ccp/echec/';
        $rslt = $this->ccpModel->creerFichier($chemin, $nom, 'csv', $csv);
        if($rslt['fichierCreer'] = true)
            $vo_echec = $chemin.$nom.'.csv';
        return $vo_echec;
    }



    /*******************************************************************************************************************************************
     *********************************************************** MAIL DE NOTIFICATION DE L'ETAT DU COMPTE **************************************/
    public function alerte_compte()
    {
        $compte = $this->ccpModel->ajoutAlertCompte();

        $sujet = "Situation compte";
        $vers_nom = $compte->prenom.' '.$compte->nom;
        $vers_mail = $compte->destinataire;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Cher ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>".$compte->message."<br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "</table>";
        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:EdkCash <no-reply@edkcash.com>\r\n";
        mail($vers_mail, $sujet, $message, $entete);
    }

    //**********************************************************envoi mail virement************************************************************
    public function envoiMailVirement($nom, $compte, $montant)
    {
        $sujet = "Virement CCP"; //Sujet du mail
        $de_mail = "no-reply@edkcash.com";
        $vers_nom = $nom;
        //$vers_mail = $destinataire;
        $vers_mail = 'bocar.sy@numherit.com';
        $message = "<table width='100%' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Bonjour ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>";
        $message.= "Votre carte EdkCash vient d'etre crétidé de ".number_format($montant)." F CFA par le débit de votre compte CCP n° ".$compte." .<br />";
        $message.= "Merci de votre confiance <br />";
        $message.= "Equipe EdkCash <br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "</table>";
        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom<$vers_mail> \r\n";
        $entete .= "From:EdkCash <no-reply@edkcash.com>\r\n";
        mail($vers_mail, $sujet, $message, $entete);
        mail('thierno.gaye@numherit.com', $sujet, $message, $entete);

    }

    //*********************************************************envoi mail etat solde**********************************************************
    public function envoiMailSolde($destinataire, $nom)
    {
        $sujet = "Liaison carte avec compte"; //Sujet du mail
        $de_mail = "no-reply@edkcash.com";
        $vers_nom = $nom;
        //$vers_mail = $destinataire;
        $vers_mail = 'bocar.sy@numherit.com';

        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Bonjour ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>";
        $message.= "Le solde de votre carte est insuffisant.<br />";
        $message.= "Vous ne pouvez pas éffectuer un virement.<br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>Veuillez recharger votre carte EdkCash.</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>Bonne réception.</td>";
        $message .= "<div align='left'><b>Date  : </b>".date('d-m-Y H:i:s')."</div>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "</tr>";

        $message.= "</table>";
        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom<$vers_mail> \r\n";
        $entete .= "From:EdkCash <no-reply@edkcash.com>\r\n";
        mail($vers_mail, $sujet, $message, $entete);
    }

    /******************************************************************************************************************************************
    /************************************************ FONCTION POUR ENVOYER UN MAIL DE NOTIFICATION VIREMENT ECHOUES *******************************/
    public function mail_vo_echec($file_name)
    {
        $subject = "VO echec   "; //Sujet du mail
        //$de_mail = "";
        //$vers_nom = $prenom.' '.$nom;
       // $vers_mail = $destinataire;

        $message= "Merci de trouver en piece jointe la liste des virements non reussis.";
        $message.= "Merci de prendre les dispositions necessaires pour l'execution de ces virements.";

        $boundary = "_".md5 (uniqid (rand()));

        $attached_file = file_get_contents($file_name); //file name ie: ./image.jpg
        $attached_file = chunk_split(base64_encode($attached_file));

        $attached = "\n\n". "--" .$boundary . "\nContent-Type: application; name=\"$file_name\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$file_name\"\r\n\n".$attached_file . "--" . $boundary . "--";

        $headers ="From: no-reply@numherit.com \r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $body = "--". $boundary ."\nContent-Type: application/pdf; charset=ISO-8859-1\r\n\n".$message . $attached;

        @mail("bocar.sy@numherit.com",$subject,$body,$headers);
    }


    /**************Fichier log txt********************************************/
    public function ecrire_log($errtxt)
    {

        if (!file_exists("../log/".date('W')))
        {
            mkdir ("../log/".date('W'),0777);

        }


        $fp = fopen("../log/".date('W')."/".date("d_m_Y")."".".txt",'a+'); // ouvrir le fichier ou le créer
        fseek($fp,SEEK_END); // poser le point de lecture à la fin du fichier
        $nouvel_ligne=$errtxt."\r\n"; // ajouter un retour à la ligne au fichier
        fputs($fp,$nouvel_ligne); // ecrire ce texte
        fclose($fp); //fermer le fichier
    }

}