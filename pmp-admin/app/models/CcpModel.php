<?php

/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */
class CcpModel extends \app\core\BaseModel
{
    ///////////////////////////////////////************************************/////////////////////////////////
    //                                                                                                        //
    //                                        GESTION DES CCP                                                 //
    //                                                                                                        //
    ///////////////////////////////////////***********************************//////////////////////////////////


    public function beneficiaireByNumeroSerie($serie){
        try{

            $sql="SELECT beneficiaire.`rowid`, beneficiaire.nom, beneficiaire.prenom, beneficiaire.cni, carte.telephone, beneficiaire.adresse,
			      beneficiaire.email, carte.`rowid` as idcarte, carte.numero_serie, carte.numero, carte.date_expiration, carte.telephone,
			      carte.date_activation , carte.statut as cartestatut
			      FROM beneficiaire 
			      INNER JOIN carte ON beneficiaire.`rowid` = carte.beneficiaire_rowid
			      WHERE carte.telephone = :numero";
            $user =  $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "numero" => strval($serie),
                )
            );
            $a = $user->fetchObject();
            //$this->pdo = NULL;
            return $a;
        }
        catch(Exception $e){
            return null;
        }
    }

    public function beneficiaireByNumero($numero){
        try{

            $sql = "SELECT beneficiaire.`rowid`, beneficiaire.nom, beneficiaire.prenom, beneficiaire.cni, carte.telephone, beneficiaire.adresse,
			        beneficiaire.email, carte.`rowid` as idcarte, carte.numero_serie, carte.numero, carte.date_expiration, carte.telephone,
			        carte.date_activation , carte.statut as cartestatut 
					FROM beneficiaire 
					INNER JOIN carte ON beneficiaire.rowid = carte.beneficiaire_rowid
					WHERE carte.telephone=:numero ";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "numero" => strval($numero),
                )
            );
            $a = $user->fetchObject();
            //$this->pdo = NULL;
            return $a;
        }
        catch(Exception $e){
            return null;
        }
    }



    public function  typeCompte(){
        try{
        $sql = "Select id_type, lib_compte
                from type_compte
                WHERE etat = :etat";
        $user =  $this->getConnexion()->prepare($sql);
        $user->execute(
            array(
                "etat" => 1,
            )
        );
        $a = $user->fetchAll(PDO::FETCH_OBJ);
        return $a;
        }
        catch(Exception $e){
            return null;
        }
    }



    public function verifyCode($code){
        try{
            $a = 0;
            $sql = "SELECT id_comptevo_carte
                    from comptevo_carte
                    WHERE code = :code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "code" => strval($code),
                )
            );
            $a = $user->rowCount();
            if($a > 0){
                return 0;
            }
            else{
                return 1;
            }
        }
        catch(Exception $e){
            return null;
        }
    }

    public function recup_mail($connecter){
        try{

            $sql = "SELECT email
                from user
                WHERE rowid = :id";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "id" => intval($connecter),
                )
            );
            $a = $user->fetchObject();
            return $a->email;


        }
        catch(Exception $e){
            return '';
        }
    }

    public function insertCompteVO($carte, $compte, $codess, $statut, $user_crea){

        try{
            $sql = "INSERT INTO comptevo_carte (carte, compte, code, satut, user_crea)
             VALUES (:carte, :compte, :code, :satut, :user_crea)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "carte"=>$carte,
                "compte" =>$compte,
                "code" => $codess,
                "satut" => $statut,
                "user_crea" => $user_crea,
            ));

        }
        catch(PDOException $Exception ){
            $res = false;
        }

        return $res;

    }

    public function insertCompteCCPCNE($compte, $rowid, $fk_typeCompte){

        try{
            $etat =1;
            $sql = "INSERT INTO compte_ccp_cne (num_compte, carte_rowid, fk_typeCompte, etat)
             VALUES (:num_compte, :carte_rowid, :fk_typeCompte, :etat)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(array(
                "num_compte"=>$compte,
                "carte_rowid" =>$rowid,
                "fk_typeCompte" => $fk_typeCompte,
                "etat" => $etat
            ));

        }
        catch(PDOException $Exception ){
            $res = false;
        }
        return $res;
    }


    public function getCarteCompteList($requestData = null)
    {
        try {
            $sql = "SELECT c.rowid, CONCAT(b.prenom, ' ', b.nom) as nom, c.telephone, t.lib_compte, m.num_compte, b.adresse, m.etat FROM carte c 
                    INNER JOIN beneficiaire b ON b.`rowid` = c.beneficiaire_rowid 
                    INNER JOIN compte_ccp_cne m ON c.rowid = m.carte_rowid 
                    INNER JOIN type_compte t ON m.fk_typeCompte = t.id_type";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( nom LIKE '%" . $requestData . "%' ";
                $sql .= " OR telephone LIKE '%" . $requestData . "%' ";
                $sql .= " OR lib_compte LIKE '%" . $requestData . "%' ";
                $sql .= " OR num_compte LIKE '%" . $requestData . "%' ";
                $sql .= " OR etat LIKE '%" . $requestData . "%' ";
                $sql .= " OR adresse LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['nom', 'telephone', 'lib_compte', 'num_compte', 'etat', 'adresse'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }
    /********Liste beneficiaires*********/
    public function getCarteCompteListCount()
    {
        try {
            $sql = "SELECT count(c.rowid) as total FROM carte c 
                    INNER JOIN beneficiaire b ON b.`rowid` = c.beneficiaire_rowid 
                    INNER JOIN compte_ccp_cne m ON c.rowid = m.carte_rowid 
                    INNER JOIN type_compte t ON m.fk_typeCompte = t.id_type";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function getVirementList($requestData = null)
    {
        try {
            $sql = "SELECT v.rowid, CONCAT(b.prenom, ' ', b.nom) as nom, c.telephone, t.lib_compte, m.num_compte, v.montant_vo, v.dd_vo FROM vo v 
                    INNER JOIN carte c ON v.carte_rowid = c.rowid 
                    INNER JOIN beneficiaire b ON c.beneficiaire_rowid = b.rowid 
                    INNER JOIN compte_ccp_cne m ON m.carte_rowid = c.rowid 
                    INNER JOIN type_compte t ON m.fk_typeCompte = t.id_type";
            if (!is_null($requestData)) {
                $sql .= " WHERE ( nom LIKE '%" . $requestData . "%' ";
                $sql .= " OR telephone LIKE '%" . $requestData . "%' ";
                $sql .= " OR lib_compte LIKE '%" . $requestData . "%' ";
                $sql .= " OR num_compte LIKE '%" . $requestData . "%' ";
                $sql .= " OR montant_vo LIKE '%" . $requestData . "%' ";
                $sql .= " OR dd_vo LIKE '%" . $requestData . "%' )";
            }
            $tabCol = ['nom', 'telephone', 'lib_compte', 'num_compte', 'montant_vo', 'dd_vo'];
            if (intval($_REQUEST['order'][0]['column']) < count($tabCol))
                $sql .= " ORDER BY " . $tabCol[$_REQUEST['order'][0]['column']] . " " . strtoupper($_REQUEST['order'][0]['dir']);
            $sql .= " LIMIT ".$_REQUEST['start']." ,".$_REQUEST['length'];
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a;
        } catch (PDOException $exception) {
            return -1;
        }
    }
    /********Liste beneficiaires*********/
    public function getVirementListCount()
    {
        try {
            $sql = "SELECT count(v.rowid) as total FROM vo v 
                    INNER JOIN carte c ON v.carte_rowid = c.rowid 
                    INNER JOIN beneficiaire b ON c.beneficiaire_rowid = b.rowid 
                    INNER JOIN compte_ccp_cne m ON m.carte_rowid = c.rowid 
                    INNER JOIN type_compte t ON m.fk_typeCompte = t.id_type";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $a = $user->fetchAll(PDO::FETCH_ASSOC);
            return $a[0]['total'];
        } catch (PDOException $exception) {
            return -1;
        }
    }

    public function historiqueVO($profil, $agence, $utilisateur, $dr, $telephone, $datedeb, $datefin){
        $cond="";
        $from="";
        $type_profil = $this->getProfils($profil);
        if($type_profil == 1)
        {
            $cond.=" AND `transaction`.fkuser=".$utilisateur;
        }
        if($type_profil==2 || $type_profil==3 ||$type_profil==4)
        {
            $cond.=" AND user.fk_agence=".$agence;
        }

        if($type_profil==6)
        {
            $from.=", region , agence ";
            $cond.=" AND  user.fk_agence = agence.rowid AND agence.region = region.idregion AND region.DR =".$dr ;
        }

        //echo $carte;
        $cdt='';
        if($telephone !=''){
            $carte = $this->getIdCarteByTelephone($telephone);
            $cdt="  AND transaction.fk_carte = :carte ";
        }

        $sql = "SELECT DISTINCT transaction.`rowid`, compte_ccp_cne.num_compte, beneficiaire.nom, beneficiaire.prenom, beneficiaire.date_nais,
		 transaction.fk_carte, transaction.montant, transaction.date_transaction, user.prenom AS pu, user.nom AS nu
		FROM `transaction`, carte, beneficiaire, compte_ccp_cne, user".$from."
		WHERE `transaction`.fk_carte = carte.numero 
        AND carte.beneficiaire_rowid = beneficiaire.rowid
        AND compte_ccp_cne.carte_rowid = carte.rowid
		AND transaction.fk_service=7  
        AND transaction.fkuser = user.rowid
		AND transaction.date_transaction >= :date1
		AND transaction.date_transaction <= :date2  ".$cdt."".$cond."";

        try{
            $stmt = $this->getConnexion()->prepare($sql);
            $stmt->bindParam("date1",  $datedeb );
            $stmt->bindParam("date2",  $datefin );
            if(!empty($carte)) $stmt->bindParam("carte",  $carte );
            $stmt->execute();
            $rs_Beneficiaire = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $rs_Beneficiaire;

        }catch(PDOException $e){
            return [];
        }
    }


    public function getProfils($profils){
        try
        {
            $sql = "SELECT type_profil FROM profil WHERE rowid='".$profils."'";
            $typprofil = $this->getConnexion()->prepare($sql);
            $typprofil->execute();
            $row_rq_typprofil= $typprofil->fetchObject();
            return $row_rq_typprofil->type_profil;
        }
        catch(PDOException $e)
        {
            return 0 ;
        }
    }


    public function getIdCarteByTelephone($telephone){
        try
        {
            $sql = "SELECT rowid FROM carte WHERE telephone='".$telephone."'";
            $typprofil = $this->getConnexion()->prepare($sql);
            $typprofil->execute();
            $row_rq_typprofil= $typprofil->fetchObject();
            return $row_rq_typprofil->rowid;
        }
        catch(PDOException $e)
        {
            return 0;
        }
    }


    public function getAllCompteByIdCarte($idcarte){
        try
        {

                $sql = "SELECT c.num_compte
				  FROM compte_ccp_cne c
                 WHERE c.carte_rowid = '".$idcarte."'";
                $typprofil = $this->getConnexion()->prepare($sql);
                $typprofil->execute();
                $row_rq_typprofil= $typprofil->fetchAll(PDO::FETCH_OBJ);

                return $row_rq_typprofil;


        }
        catch(PDOException $e)
        {
            return 0;
        }
    }

    public function insertVO($vo, $montant_vo, $duree_vo, $idcarte, $num_compte, $agence){

        try {
            $vo_ponctuel= 0;
            $vo_permanent= 0;
            if($vo==1) {$vo_permanent= 1;}
            else { $vo_ponctuel= 1;}
            $sql = "INSERT INTO vo (vo_permanent, vo_ponctuel,  montant_vo, duree_vo, carte_rowid, num_compte, sens, fk_agence)
												   VALUES (:vo_permanent, :vo_ponctuel, :montant_vo, :duree_vo, :carte_rowid, :num_compte, :sens, :fk_agence)";
            $user = $this->getConnexion()->prepare($sql);
            $res = $user->execute(
                array(
                    "vo_permanent" => $vo_permanent,
                    "vo_ponctuel" => $vo_ponctuel,
                    /*"carte_source" => $numero,*/
                    "montant_vo" => $montant_vo,
                    "duree_vo" => $duree_vo,
                    "carte_rowid" => $idcarte,
                    "num_compte" => $num_compte,
                    "sens" => 'C',
                    "fk_agence" => $agence
                )
            );

            return $res;
        }
        catch(PDOException $e){
            return -1;
        }
    }

    public function demandeVOPonctuel(){
        try{
            $sql = "SELECT DISTINCT v.montant_vo, v.dd_vo, b.rowid as idbenef, v.num_compte as numero_compte, b.nom, b.prenom, c.rowid
		FROM vo v 
		INNER JOIN carte c ON v.carte_rowid = c.rowid
		INNER JOIN beneficiaire b ON c.beneficiaire_rowid = b.rowid
		WHERE v.sens ='C' AND v.envoye =0 AND v.vo_ponctuel =1";
            $Beneficiaire = $this->getConnexion()->prepare($sql);
            $Beneficiaire->execute();
            $rs_Beneficiaire = $Beneficiaire->fetchAll(PDO::FETCH_OBJ);
            return $rs_Beneficiaire;
        }
        catch(Exception $e)
        {
            //echo $e;
            return [] ;
        }
    }


    public function verifyTransaction($code)
    {
        $a = 0;
        try{
            $sql = "SELECT reference
                FROM vo_fichier
                WHERE reference = :code";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "code" => strval($code),
                )
            );
            $a = $user->rowCount();
            if($a > 0){
                return 1;
            }
            else{
                return 0;
            }

        }
        catch(Exception $e)
        {
           // echo $e;
            return 0 ;
        }
    }

    public function updateVO($idbenef){
        try{
            $sql="UPDATE vo SET vo.envoye=1 WHERE vo.carte_rowid=".$idbenef;
            $req =  $this->getConnexion()->prepare($sql);
            return $req->execute();
        }
        catch(Exception $e)
        {
            //echo $e;
            return -1;
        }
    }


    public function ajoutFichierVO($reference){
        try{
            $sql="INSERT INTO vo_fichier(reference) VALUES (:reference)";
            $req =  $this->getConnexion()->prepare($sql);
            return $req->execute(
                array(
                    'reference' => $reference
                )
            );
        }
        catch(Exception $e)
        {
            //echo $e;
            return -1;
        }
    }

    public function existe_carte($matricule)
    {
        $nbrows = 0;
        try
        {
            $sql="SELECT b.rowid as idbenef, b.nom, b.prenom, m.num_compte, c.rowid as idcarte, c.numero, c.telephone
				  FROM compte_ccp_cne m
				  INNER JOIN carte c ON m.beneficiaire_rowid = c.beneficiaire_rowid
				  INNER JOIN beneficiaire b ON c.beneficiaire_rowid = b.rowid
				  WHERE m.num_compte = '".$matricule."' AND c.statut =1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $beneficiare = $user->fetchObject();
            $nbrows = $user->rowCount();
            if($nbrows > 0)
            {
                $numcarte = $beneficiare;
            }
            else
            {
                $numcarte ='';
            }
        }
        catch(Exception $e)
        {
            return '' ;
        }

        return $numcarte;
    }

    public function operationExiste($operation)
    {
        $nbrows = 0;
        try
        {
            $sql="SELECT num_operation  FROM operation_traitee  WHERE  num_operation = :operation";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(
                array(
                    "operation" => $operation,
                )
            );
            $beneficiare = $user->fetchObject();
            $nbrows = $user->rowCount();
            if($nbrows > 0)
            {
                $num_operation = $beneficiare->num_operation;
            }
            else
            {
                $num_operation =0;
            }
        }
        catch(Exception $e)
        {
            return 0 ;
        }

        return $num_operation;

    }

    public function verifierBenefCCP($numcompteadebiter)
    {
        try
        {
            $sql="SELECT DISTINCT b.rowid AS idbenef, b.email, b.nom, b.prenom, m.num_compte, c.rowid, c.telephone
					FROM vo v
					INNER JOIN carte c ON v.carte_rowid = c.beneficiaire_rowid
					INNER JOIN compte_ccp_cne m ON m.carte_rowid = c.beneficiaire_rowid
					INNER JOIN beneficiaire b ON c.beneficiaire_rowid = b.rowid
					WHERE v.envoye =1
					AND m.num_compte = '".$numcompteadebiter."'
					AND m.fk_typeCompte =1";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute();
            $beneficiare = $user->fetchObject();
            $nbrows = $user->rowCount();
            if($nbrows == 1)
            {
                return $beneficiare;
            }
            else
            {
                return '';
            }
        }
        catch(Exception $e)
        {
            return '' ;
        }

    }


    public function update_solde_compte($montant)
    {
        //$solde_compte = $this->get_solde_compte();
        try
        {
            $sql = "UPDATE comptes_ccp SET solde = solde - '".($montant)."'";
            $resultat = $this->getConnexion()->prepare($sql);
            return $resultat->execute();
        }
        catch(PDOException $e)
        {
            return 0;
        }

    }

    public function get_solde_compte()
    {
       // $date_envoie=date("Y-m-d H:i:s");
        try
        {
            $sql = "SELECT c.solde, c.client FROM comptes_ccp c";
            $resultat = $this->getConnexion()->prepare($sql);
            $resultat->execute();
            $rs_resultat = $resultat->fetchObject();
            $solde  = $rs_resultat->solde;
            return $solde;
        }
        catch(PDOException $e)
        {
            return 0;
        }

    }

    /***********************************************************Calcul frais*********************************************************************/
    public function Calcul_frais($serviceID, $montant=0)
    {

        if($serviceID ==12 || $serviceID ==4){
            try{
                $sql = "SELECT tarif_frais.* FROM service, tarif_frais
                        WHERE service.rowid =  tarif_frais.service
                        AND tarif_frais.montant_deb <= :mtt
                        AND tarif_frais.montant_fin >= :mtt1
                        AND  service.rowid=:serviceID";

                $service = $this->getConnexion()->prepare($sql);
                $service->execute(array(
                    "mtt" => intval($montant),
                    "mtt1" => intval($montant),
                    "serviceID" => intval($serviceID)
                ));
                $row_rq_service= $service->fetchObject();
                //$this->pdo = NULL;
                if($row_rq_service->valeur == 0.01) $return = ($montant * 0.01);
                else $return = $row_rq_service->valeur;
            }
            catch(Exception $e)
            {
                return -1;
            }

        }

        else{

            try
            {

                $query_rq_service = "SELECT frais FROM service WHERE rowid=:serviceID";
                $service = $this->getConnexion()->prepare($query_rq_service);
                $service->execute(array(
                    "serviceID" => intval($serviceID)
                ));
                $row_rq_service= $service->fetchObject();
                //$this->pdo = NULL;
                $return = $row_rq_service->frais;

            }
            catch(Exception $e)
            {
                return -2;
            }
        }
        return $return;


    }

    /**********************************************************Get comsissions******************************************************************/
    public function Get_carteCommisssion()
    {
        try
        {

            $query_rq_service = "SELECT numero_carte FROM carte_parametrable WHERE idcarte=1";
            $service = $this->getConnexion()->prepare($query_rq_service);
            $service->execute();
            $row_rq_service= $service->fetchObject();
            return $row_rq_service->numero_carte;
        }
        catch(PDOException $e)
        {
            return 0 ;
        }
    }

    /*********get Type Compte************/
    public function getTypeCompte($telephone)
    {
        try {
            $sql = "SELECT typecompte FROM carte WHERE telephone =:numero";
            $user = $this->getConnexion()->prepare($sql);
            $user->execute(array("numero" => $telephone));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) return $a->typecompte;
            else return -1;
        } catch (Exception $e) {
            return -2;
        }
    }

    public function updateVOTraiter($idbenef){
        try{
            $sql="UPDATE vo SET vo.traite=1 WHERE vo.carte_rowid=".$idbenef;
            $req =  $this->getConnexion()->prepare($sql);
            return $req->execute();
        }
        catch(Exception $e)
        {
            //echo $e;
            return -1;
        }
    }


    public function ajoutAlertCompte(){
        try{
            $sql = "SELECT message, destinataire, prenom, nom FROM mail_virement_ccp WHERE rowid = 3";
            $req =  $this->getConnexion()->prepare($sql);
            $req->execute();
            return $req->fetchObject();
        }
        catch(Exception $e)
        {
            //echo $e;
            return -1;
        }
    }


    /**********************************************************Log user******************************************************************/
    public function log_user($action, $action_object, $user, $commentaire="", $type=0)
    {

        try
        {
            $sql ="INSERT INTO action_utilisateur( action, action_object, IDUSER, commentaire, type)
		 VALUES (:action,:action_object, :IDUSER, :commentaire, :type)";
            $this->getConnexion()->prepare($sql);
            $req =  $this->getConnexion()->prepare($sql);
            $req->execute(array(
                "action" => $action,
                "action_object" => $action_object,
                "IDUSER" => $user,
                "commentaire"=>$commentaire,
                "type"=>$type,
            ));

            $log="[".date('d-m-Y H:i:s')."]:".$action.":".$action_object.":".$this->getInfoUser($user).": ".$commentaire." :".$type."";

            $this->ecrire_log($log);
        }
        catch(PDOException $e)
        {
            echo $e;
        }

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

    /**********************************************************infos user************************************************************************/
    public function getInfoUser($user)
    {

        $sql="SELECT user.nom, user.prenom,agence.label
				FROM user, agence
				WHERE user.fk_agence = agence.rowid AND user.rowid =:user  ";

        try
        {
            $stmt = $this->getConnexion()->prepare($sql);
            $stmt->bindParam("user",$user);
            $stmt->execute();
            $beneficiare = $stmt->fetchObject();
        }
        catch(PDOException $e)
        {
            return -1;
        }
        return $beneficiare->prenom." ".$beneficiare->nom.": agence ".$beneficiare->label ;

    }


    /**********************************************************Ajouter les transactions Poste*********************************************************/
    public function saveTransactionPoste($num_transac="", $ladate="", $montant=0, $statut=1, $user=1, $fk_carte=0, $commentaire="",$idagence="" )
    {
        try
        {
            $sql="INSERT INTO transaction_carte_ccp(num_transac, date_transaction, montant, statut, fkuser, fk_carte, commentaire, idagence)
					VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_carte, :commentaire, :idagence)";
            $req = $this->getConnexion()->prepare($sql);
            return $req->execute(array(
                "num_transac"=>$num_transac,
                "date_transaction"=>$ladate,
                "montant" => $montant,
                "statut"=>$statut,
                "fkuser" => $user,
                "fk_carte" => $fk_carte,
                "commentaire" => $commentaire,
                "idagence" => $idagence
            ));

        }
        catch(PDOException $e)
        {
            //echo $e;
            return -1 ;
        }

    }

}