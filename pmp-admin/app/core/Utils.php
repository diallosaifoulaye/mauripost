<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 17/08/2017
 * Time: 11:03
 */

namespace app\core;


class Utils
{
    private $pdo;
    //public $lang;

    function __construct()
    {

        //$this->lang = (new Lang())->getLangFile($this->getSession()->getAttribut('lang'));;
        $connexion = new BaseModel();
        $this->pdo = $connexion->getConnexion();
    }

    public function getPDO(){
        try {
            if ($this->pdo === null)
                return $this->pdo = $this->getConnexion() ;
            else return $this->pdo ;
        } catch (\PDOException $ex) {
            echo 'Aucune connexion à une base de donnée !';
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    public static function validateMail($params)
    {
        return filter_var(filter_var($params, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
    }


    /**
     * @param $length
     * @return array
     */
    public static function getGeneratePassword($length = 8)
    {
        // on declare une chaine de caractÃ¨res
        $chaine = "abcdefghijklmnopqrstuvwxyz@ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        //nombre de caractÃ¨res dans le mot de passe
        $pass = "";
        //on fait une boucle
        for ($u = 1; $u <= $length; $u++) {
            //on compte le nombre de caractÃ¨res prÃ©sents dans notre chaine
            $nb = \strlen($chaine);
            // on choisie un nombre au hasard entre 0 et le nombre de caractÃ¨res de la chaine
            $nb = \mt_rand(0, ($nb - 1));
            // on ajoute la lettre a la valeur de $pass
            $pass .= $chaine[$nb];
        }
        // on retourne le rÃ©sultat :
        return ["pass"=>$pass,"crypt"=>self::getPassCrypt($pass)];
    }



    /**
     * @param $pass
     * @return bool|null|string
     */
    public static function getPassCrypt($pass)
    {
        $timeTarget = 0.05; // 50 millisecondes
        $cost = 8;
        $passHasher = null;
        do {
            $cost++;
            $start = \microtime(true);
            $passHasher = \password_hash($pass, PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = \microtime(true);
        } while (($end - $start) < $timeTarget);
        return $passHasher;
    }

    /**********************************************************************************************************************************************/
    function verifierSerie($num_debut, $num_fin, $typelot = null, $niveau = null)
    {
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {
                $insertSQL = "SELECT id FROM carte_stock WHERE CONVERT(num_serie, SIGNED INTEGER) BETWEEN :debut AND :fin ";

                if($typelot !== null) $insertSQL .= " AND typelot = :typelot";
                if($niveau !== null) $insertSQL .= " AND niveau = :niveau";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('debut', intval($num_debut));
                $insertlotcarte->bindValue('fin', intval($num_fin));
                if($typelot !== null) $insertlotcarte->bindValue('typelot', $typelot);
                if($niveau !== null) $insertlotcarte->bindValue('niveau', $niveau);

                $insertlotcarte->execute();
                return ($insertlotcarte->rowCount() == 0) ? 1 : -1;
            } else return -2;
        }
        catch (\PDOException $e)
        {
            return -2;
        }
    }

    /**********************************************************************************************************************************************/

    function getInfoPrev($num_debut, $num_fin, $typecarte)
    {
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {
                $insertSQL = "SELECT MAX(id) as `id`, `num_serie`, `idlot`, `niveau`, `typelot`, `typecarte`, `idagence`, `etatvente`, `datevente`, `agence_vendeur`, `commentaire`, `statut` FROM `carte_stock` WHERE (CONVERT(`num_serie`, SIGNED INTEGER) BETWEEN :num_debut AND :num_fin) AND typecarte = :idtypecarte ";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('num_debut', intval($num_debut));
                $insertlotcarte->bindValue('num_fin', intval($num_fin));
                $insertlotcarte->bindValue('idtypecarte', intval($typecarte));

                $insertlotcarte->execute();
                $rst = $insertlotcarte->rowCount();
                return $insertlotcarte->fetchAll(\PDO::FETCH_ASSOC)[0];

            } else return false;
        }
        catch (\PDOException $e) {
            return false;
        }
    }

    function retourAgence($idlot)
    {
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {
                $insertSQL = "SELECT idagencedest FROM `lotcarte` WHERE idlotcarte =:idlot ";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('idlot', intval($idlot));
                $insertlotcarte->execute();

                return $insertlotcarte->fetchAll(\PDO::FETCH_ASSOC)[0];

            } else return false;
        }
        catch (\PDOException $e) {
            return false;
        }
    }


    function verifValidLot($num_debut, $num_fin, $typecarte, $typelot = 0, $niveau = 'NUMHERIT', $level=1)
    {
        try
        {
            //$level =1;
            $dbh = $this->pdo;
            if (is_object($dbh)) {

                $insertSQL = "SELECT COUNT(id) as nbr FROM `carte_stock` WHERE (CONVERT(`num_serie`, SIGNED INTEGER) BETWEEN :num_debut AND :num_fin) AND typelot = :typelot AND typecarte = :idtypecarte  AND niveau = :niveau";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('num_debut', intval($num_debut));
                $insertlotcarte->bindValue('num_fin', intval($num_fin));
                $insertlotcarte->bindValue('idtypecarte', intval($typecarte));
                $insertlotcarte->bindValue('typelot', intval($typelot));
                $insertlotcarte->bindValue('niveau', $niveau);
                $insertlotcarte->execute();
                $rst = intval($insertlotcarte->fetchAll(\PDO::FETCH_ASSOC)[0]['nbr']);
                //var_dump($rst);die();

                if ($level == 1) return ($rst == 0) ? true : false;
                else return (((intval($num_fin) - intval($num_debut)) + 1) === intval($rst)) ? true : false;
            } else return false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    function verifChevauchementLot($num_debut, $num_fin, $typecarte, $typelot = 1, $exp = 'NUMHERIT', $dest = 'CAVEAU')
    {
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {

                $insertSQL = "SELECT * FROM `lotcarte` WHERE CONVERT(`num_debut`, SIGNED INTEGER) <= :num_debut1 AND CONVERT(`num_fin`, SIGNED INTEGER) >= :num_debut2 AND typelot = :typelot AND idtypecarte = :idtypecarte AND expediteur = :expediteur  AND destinataire = :destinataire ";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('num_debut1', intval($num_debut));
                $insertlotcarte->bindValue('num_debut2', intval($num_debut));
                $insertlotcarte->bindValue('idtypecarte', intval($typecarte));
                $insertlotcarte->bindValue(':typelot', intval($typelot));
                $insertlotcarte->bindValue('expediteur', $exp);
                $insertlotcarte->bindValue('destinataire', $dest);

                $insertlotcarte->execute();
                $test1 = $insertlotcarte->rowCount();
                $insertSQL = "SELECT * FROM `lotcarte` WHERE CONVERT(`num_debut`, SIGNED INTEGER) <= :num_fin1 AND CONVERT(`num_fin`, SIGNED INTEGER) >= :num_fin2 AND typelot = :typelot AND idtypecarte = :idtypecarte AND expediteur = :expediteur  AND destinataire = :destinataire ";

                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('num_fin1', intval($num_fin));
                $insertlotcarte->bindValue('num_fin2', intval($num_fin));
                $insertlotcarte->bindValue('idtypecarte', intval($typecarte));
                $insertlotcarte->bindValue(':typelot', intval($typelot));
                $insertlotcarte->bindValue('expediteur', $exp);
                $insertlotcarte->bindValue('destinataire', $dest);

                $insertlotcarte->execute();
                $test2 = $insertlotcarte->rowCount();

                return $test1 == 0 && $test2 == 0 ? true : false;
            } else return false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**********************************************************************************************************************************************/
    function getlastStock($typelot, $expediteur)
    {
        $insertSQL = "SELECT MAX(stock_apres) as stock_apres FROM lotcarte WHERE typelot = :typelot AND expediteur = :expediteur";
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {
                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('typelot', $typelot);
                $insertlotcarte->bindValue('expediteur', $expediteur);
                $insertlotcarte->execute();
                $object = $insertlotcarte->fetchObject();
                if ($object->stock_apres != NULL) {
                    return intval($object->stock_apres);
                } else {
                    return 0;
                }
            } else {
                return -2;
            }
        } catch (\PDOException $e) {
            return -2;
        }
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $string
     * @return string
     */
    public function securite_xss($data)
    {
        //return $string;
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        $data = htmlspecialchars($data);
        $data = strip_tags($data);
        return $data;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_numero($id_length = 6)
    {
        // add any character / digit
        $alfa = '1234567890';
        $token = '';
        for ($i = 0; $i < $id_length; $i++) {

            // generate randomly within given character/digits
            @$token .= $alfa[rand(1, strlen($alfa))];

        }
        return $token;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_code_validation($id_length = 10)
    {
        $string = "";
        $chaine = "0123456789";
        \srand((double)\microtime() * 1000000);
        for ($i = 0; $i < $id_length; $i++)
            $string .= $chaine[\rand() % \strlen($chaine)];
        return $string;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_code($id_length = 10)
    {
        // add any character / digit
        $alfa = 'abcdefghijklmnopqrstuvwxyz@@@@1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ@@@@0987654321';
        $token = '';
        for ($i = 1; $i < $id_length; $i++) {

            // generate randomly within given character/digits
            @$token .= $alfa[rand(1, strlen($alfa))];

        }
        return $token;
    }

    public function generateur($length = 10)
    {
        $random = "";
        srand((double)microtime() * 1000000);
        $data = "AbcDE123IJKLMN67QRSTUVWXYZaBCdefghijklmn123opq45rs67tuv89wxyz0FGH45OP89";
        for ($i = 0; $i < $length; $i++)
            $random .= substr($data, (rand() % (strlen($data))), 1);
        return $random;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param int $id_length
     * @return string
     */
    public function generation_numTransaction($id_length = 12)
    {
        // add any character / digit
        $alfa = '0987654321';
        $token = '';
        for ($i = 1; $i < $id_length; $i++) {

            // generate randomly within given character/digits
            @$token .= $alfa[rand(1, strlen($alfa))];

        }
        return $token;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj moisenlettre aaaa a hh:ii:ss
     */
    public function mois_en_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Janvier';
                    break;
                case '02':
                    $mm = 'Février';
                    break;
                case '03':
                    $mm = 'Mars';
                    break;
                case '04':
                    $mm = 'Avril';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juillet';
                    break;
                case '08':
                    $mm = 'Août';
                    break;
                case '09':
                    $mm = 'Septembre';
                    break;
                case '10':
                    $mm = "Octobre";
                    break;
                case '11':
                    $mm = 'Novembre';
                    break;
                case '12':
                    $mm = 'Décembre';
                    break;
            }
            $date_fr = $mm . '-' . $aa;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj moisenlettre aaaa
     */
    public function date_mois_en_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Janvier';
                    break;
                case '02':
                    $mm = 'Février';
                    break;
                case '03':
                    $mm = 'Mars';
                    break;
                case '04':
                    $mm = 'Avril';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juillet';
                    break;
                case '08':
                    $mm = 'Août';
                    break;
                case '09':
                    $mm = 'Septembre';
                    break;
                case '10':
                    $mm = "Octobre";
                    break;
                case '11':
                    $mm = 'Novembre';
                    break;
                case '12':
                    $mm = 'Décembre';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj moisentroislettre aaaa a hh:ii:ss
     */
    public function date_heure_mois_en_trois_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Jan';
                    break;
                case '02':
                    $mm = 'Fév';
                    break;
                case '03':
                    $mm = 'Mar';
                    break;
                case '04':
                    $mm = 'Avr';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juil';
                    break;
                case '08':
                    $mm = 'Aoû';
                    break;
                case '09':
                    $mm = 'Sep';
                    break;
                case '10':
                    $mm = "Oct";
                    break;
                case '11':
                    $mm = 'Nov';
                    break;
                case '12':
                    $mm = 'Déc';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa . ' à ' . $hh . ':' . $ii . ':' . $ss;
        }
        return $date_fr;
    }


    public function heure_minute_seconde($date)
    {
        $date_fr = '';
        if ($date != '') {
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);

            ///////////////
            $date_fr = $hh . ':' . $ii . ':' . $ss;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj moisentroislettre aaaa
     */
    public function date_mois_en_trois_lettre($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case '01':
                    $mm = 'Jan';
                    break;
                case '02':
                    $mm = 'Fév';
                    break;
                case '03':
                    $mm = 'Mar';
                    break;
                case '04':
                    $mm = 'Avr';
                    break;
                case '05':
                    $mm = 'Mai';
                    break;
                case '06':
                    $mm = 'Juin';
                    break;
                case '07':
                    $mm = 'Juil';
                    break;
                case '08':
                    $mm = 'Aoû';
                    break;
                case '09':
                    $mm = 'Sep';
                    break;
                case '10':
                    $mm = "Oct";
                    break;
                case '11':
                    $mm = 'Nov';
                    break;
                case '12':
                    $mm = 'Déc';
                    break;
            }
            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa;
        }
        return $date_fr;
    }
    /**********************************************************************************************************************************************/

    /**
     * @param $date au format aaaa/mm/jj hh:ii:ss
     * @return string au format  jj/mm/aaaa a hh:ii:ss
     */
    public function date_jj_mm_aaaa_hh_ii_ss($date)
    {
        $date_fr = '';
        if ($date != '') {
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            ////////////////
            $date_fr = $jj . ' ' . $mm . ' ' . $aa . ' à ' . $hh . ':' . $ii . ':' . $ss;
        }
        return $date_fr;
    }
    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj/mm/aaaa
     */
    public function date_jj_mm_aaaa($date)
    {
        $date_fr = '';
        if ($date != '') {

            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            ////////////////
            $date_fr = $jj . '-' . $mm . '-' . $aa;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $date au format aaaa/mm/jj
     * @return string au format  jj/mm/aaaa
     */
    public function date_aaaa_mm_jj($date)
    {
        $date_eng = '';
        if ($date != '') {

            $aa = substr($date, 6, 4);
            $mm = substr($date, 3, 2);
            $jj = substr($date, 0, 2);

            ////////////////
            $date_fr = $aa . '-' . $mm . '-' . $jj;
        }
        return $date_fr;
    }

    /**********************************************************************************************************************************************/
    /**
     * @param $number
     * @return string au format 1 234 567,89
     */
    public function number_format($number)
    {
        return number_format($number, 0, ',', ' ');
    }

    function nombre_form($nombre)
    {
        return @number_format($nombre, 0, ',', ' ');
    }
    /**********************************************************************************************************************************************/

    /**********************************************************************************************************************************************/
    /**
     * @param $number
     * @return string au format 1 234 567,89
     */
    public function genererCodeTontine()
    {
        try {
            $found = 0;
            while ($found == 0) {
                $code_carte = 'TNE-' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                $totalRows = R::count('sttonine', 'code=?', array($code_carte));
                if ($totalRows == 0) {
                    $found = 1;
                    break;
                }
            }
            return $code_carte;
        } catch (\Exception $e) {
            return -1;
        }
    }
    /**********************************************************************************************************************************************/

    /*****************envoi de mail param connection***************************/
    public function envoiInviation($destinataire, $nom="", $invitepar="")
    {
        $entete = '';
        $sujet = "Invitation e-tontine "; //Sujet du mail

        $vers_nom = $nom;
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $vers_nom . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>";
        $message .= "Une invitation de tontine de la part de " . $invitepar . " vous a été envoyée dans la plateforme e-tontine que vous pourrez acc&eacute;der en suivant le lien ci dessous  :<br />";
        $message .= "Si vous n'avez pas encore de compte. Merci d'en créer un pour vous connecter<br />";
        $message .= "<br />";
        $message .= "<a href='http://numherit-labs.com/sunutontine/public/' target='new'>Cliquer sur ce lien pour se connecter &aacute; votre compte . </a>";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos param&egrave;tres  sont confidentiels .</td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "</tr>";

        $message .= "</table>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom<$vers_mail> \r\n";
        $entete .= "From:SUNUTONTINE <no-reply@mauripost.mr>\r\n";
        mail($vers_mail, $sujet, $message, $entete);

    }

    public function envoiparametre($destinataire, $email="", $login="", $password="")
    {

        $sujet = "Création compte PMP"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre compte d'accès à la plateforme MAURIPOST vient d'être créé.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "Identifiant :" . $login . "<br />";
        $message .= "Mot de passe :" . $password . "<br />";
        //$message .= "<a href='http://pmp-burkina.com/' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<a href='" . BASE_URL . "' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.<br /><br />Equipe MAURIPOST</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r";
        mail($vers_mail, $sujet, $message, $entete);


    }


    public function envoiparametreDistributeur($destinataire, $email="", $login="", $password="")
    {

        $sujet = "Création compte distributeur"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre compte d'accès à la plateforme distributeur MAURIPOST vient d'être créé.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "Identifiant :" . $login . "<br />";
        $message .= "Mot de passe :" . $password . "<br />";
        $message .= "<a href='" . BASE_URL . "' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.<br /><br />Equipe MAURIPOST</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r";
        return mail($vers_mail, $sujet, $message, $entete);


    }

    public function envoiparametreAuCollecteur($destinataire, $email="", $tel="", $password="")
    {

        $sujet = "Création compte MAURIPOST"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<br />";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre compte d'accès à la plateforme MAURIPOST vient d'être créé.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "<br />";
        $message .= "Identifiant : " . $tel . "<br />";
        $message .= "Mot de passe : " . $password . "<br />";
        $message .= "<br />";

        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.<br /><br /><br />Equipe MAURIPOST</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r";
        mail($vers_mail, $sujet, $message, $entete);


    }

    public static function envoiNewPassCollecteur($destinataire, $email="", $login="", $password="")
    {
        $sujet = "Régénération mot de passe compte MAURIPOST"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Chér(e) " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre mot de passe d'accès  à la plateforme MAURIPOST vient d'être regénéré.<br />";
        $message .= "Vous pourrez désormais vous connecter à l'application avec les paramètres suivants :<br />";
        $message .= "Login :" . $login . "<br />";
        $message .= "Mot de passe :" . $password . "<br />";
        $message .= "<a href='" . BASE_URL . "' target='_blank'>Cliquer sur ce lien pour accéder á votre compte.</a>";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Nous vous rappelons que vos paramètres de connexion sont confidentiels.</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr\r\n";
        mail('', $sujet, $message, $entete);

    }

    public function envoiCodeValidationCreditercarte($dest, $nom="", $code="", $langue="")
    {

        $de_nom = $langue['plateforme_postecash'];
        $de_mail = "no-reply@mauripost.mr";
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = $langue['rechargement_carte_agence'];
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_5'] . "</div></br>";
        $message .= "<div align='left'>" . $langue['envoi_mail_code_rechargement_2'] . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_3'] . "</b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_4'] . " : </b>" . $this->getDateNow('WITH_TIME') . "</div>";

        $entete = '';
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        return mail($dest, $sujet, $message, $entete);
    }

    public function envoiCodeValidationliaison($dest, $nom="", $code="", $langue="")
    {
        $de_nom = 'plateforme MAURIPOST';
        $de_mail = "no-reply@mauripost.mr";

        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = $langue['laison_carte_compte'];
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $langue['message_send_1'] . "</div></br>";
        $message .= "<div align='left'>" . $langue['message_send_2'] . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_3'] . "</b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['date'] . " : </b>" . date('d-m-Y H:i:s') . "</div>";

        $entete = '';
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoie = mail($dest, $sujet, $message, $entete);
        return $envoie;
    }

    function envoiMailLierCarteCompte($destinataire, $nom="", $carte="", $compte="", $langue="")
    {


        $sujet = $langue['laison_carte_compte']; //Sujet du mail
        $de_mail = "no-reply@mauripost.mr";
        $vers_nom = $nom;
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td>" . $langue['message_send_3'] . $vers_nom . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>";
        $message .= $langue['message_send_4'] . $carte . $langue['message_send_4'] . $compte . ".<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>" . $langue['message_send_5'] . "</td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>" . $langue['message_send_6'] . "</td>";
        $message .= "<div align='left'><b>" . $langue['date'] . " : </b>" . date('d-m-Y H:i:s') . "</div>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "</tr>";

        $message .= "</table>";
        $entete = "";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom<$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        mail($vers_mail, $sujet, $message, $entete);

    }
    /*****************Acceder a un module***************************/

    //Activer un module
    function Acces_module($profil, $module)
    {
        $checked = "  ";
        try {
            $query = "SELECT affectation_droit.valide, action.module
			FROM affectation_droit, action 
			WHERE affectation_droit.action=action.rowid 
			AND affectation_droit.profil=:profil
			AND action.module=:module";
            $result = $this->pdo->prepare($query);
            $result->bindParam("profil", $profil);
            $result->bindParam("module", $module);
            $result->execute();
            $rs_execute = $result->fetchObject();
            $totalRows = $result->rowCount();
            if ($totalRows > 0) {
                return 1;
            } else {
                return -1;
            }
            return 1;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    //restreindre lq cess  ala page
    public function Restreindre($useradmin, $valide)
    {
        if ($useradmin != 1 && $valide != 1) {
            $MM_restrictGoTo = ROOT . 'accueil/accueil';
            header("Location: " . $MM_restrictGoTo);
        }
    }

    /************** Permet de verifier  si le user est autoisé a voir cet action ********************************************/
    public function Est_autoriser($action, $profil)
    {

        $checked = "  ";
        try {
            $query = "SELECT valide FROM affectation_droit WHERE action=:action AND profil=:profil AND valide = 1";

            $result = $this->pdo->prepare($query);
            $result->bindParam("action", $action);
            $result->bindParam("profil", $profil);
            $result->execute();
            $rs_execute = $result->fetchObject();
            $totalRows = $result->rowCount();
            if ($totalRows > 0) {
                return 1;
            } else {
                return -1;
            }
        } catch (\PDOException $e) {
            return -2;
        }
    }

    /***********insert journal************/
    public function insertJournal($action, $object, $commentaires, $type, $iduser)
    {

        $date = date('Y-m-d H:i:s');
        try {
            $sql = "INSERT INTO action_utilisateur(date, action, action_object, IDUSER, commentaire, type) VALUES(:dat, :act, :obj,  :iduser, :comment, :typ)";
            $req = $this->pdo->prepare($sql);
            $res = $req->execute(array(
                "dat" => $date,
                "act" => $action,
                "obj" => $object,
                "iduser" => $iduser,
                "comment" => $commentaires,
                "typ" => $type
            ));
        } catch (\PDOException $e) {

            return -2;

        }
        return $res;

    }

    /**************Fichier log txt********************************************/
    public function ecrire_journal($errtxt)
    {

        if (!file_exists(__DIR__ . '/../logs/' . date('Y'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y'), 0777);

        }
        if (!file_exists(__DIR__ . '/../logs/' . date('Y') . "/" . date('m'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y') . '/' . date('m'), 0777);

        }
        if (!file_exists(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W'))) {
            mkdir(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W'), 0777);

        }
        $fp = fopen(__DIR__ . '/../logs/' . date('Y') . '/' . date('m') . '/' . date('W') . '/' . date("d_m_Y") . '' . '.txt', 'a+'); // ouvrir le fichier ou le créer
        fseek($fp, SEEK_END); // poser le point de lecture à la fin du fichier
        $nouvel_ligne = $errtxt . "\r\n"; // ajouter un retour à la ligne au fichier
        fputs($fp, $nouvel_ligne); // ecrire ce texte
        fclose($fp); //fermer le fichier
    }

    /***********************************************Ecrire dans le fichier********************************************/
    public function log_journal($action, $objet, $comment, $module, $iduser)
    {
        $this->insertJournal($action, $objet, $comment, $module, $iduser);
        $log = "Date:" . date('d-m-Y H:i:s') . " - Idutilisateur:" . $iduser . " - Module: " . $module . " - Action:" . $action . " - Object:" . $objet . " - IP:" . $_SERVER["REMOTE_ADDR"];
        $this->ecrire_journal($log);
    }
    /***********************************************fin*******************************************/

    /***********************************************truncate_carte*******************************************/
    public function truncate_carte($carte)
    {
        $nb_caractere = strlen($carte);
        $premier = "";
        if ($nb_caractere > 0) {

            for ($i = 0; $i < $nb_caractere - 4; $i++) {
                $premier .= "*";
            }
            $truncate = $premier . substr($carte, $nb_caractere - 4);
            return $truncate;
        } else {
            return -1;
        }
    }

    /*************************************************date_fr*******************************************/
    public function date_fr($date)
    {

        $datefr = "";
        $jj = substr($date, 8, 2);
        $mm = substr($date, 5, 2);
        $aa = substr($date, 0, 4);
        $datefr = $jj . "-" . $mm . "-" . $aa;
        return $datefr;
    }


    /***********************************************date_fr4*******************************************/
    public function date_fr4($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janv";
                    break;
                case "02":
                    $mm = "Fev";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juil";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Sept";
                    break;
                case "10":
                    $mm = "Oct";
                    break;
                case "11":
                    $mm = "Nov";
                    break;
                case "12":
                    $mm = "Dec";
                    break;
            }
            ////////////////
            $date_fr = $jj . " " . $mm . " " . $aa . "  " . $hh . ":" . $ii . ":" . $ss;
        }
        return $date_fr;
    }

    /***********************************************nombre_format*******************************************/
    public function nombre_format($nombre)
    {
        return @number_format($nombre, 0, ' ', ' ');
    }

    /****************************************************************************************************/
    public function typeProfil($profil)
    {
        try {
            $sql = "Select type_profil
                from profil
                WHERE rowid = :id
                AND etat = :etat";
            $user = $this->pdo->prepare($sql);
            $user->execute(
                array(
                    "id" => $profil,
                    "etat" => 1,
                )
            );
            $a = $user->fetch();
            if ($a != '')
                return $a['type_profil'];
            else
                return -1;
        } catch (\Exception $e) {
            echo -99;
            die;
        }

    }

    /****************************************Liste des pays************************************************************/
    public function listePays()
    {
        try {
            $sql = "Select id, code, alpha2, nom_fr_fr
                from pays ORDER BY alpha2 ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            return $a;
        } catch (\Exception $e) {
            //echo 'Error: -99';
            echo $e;
            die;
        }
    }

    /************************************Liste des villes****************************************************************/
    public function departement($id)
    {

        try {
            $sql = "Select iddepartement, lib_departement
                from departement
                WHERE iddepartement = :etat";
            $user = $this->pdo->prepare($sql);
            $user->execute(
                array(
                    "etat" => intval($id),
                )
            );
            $a = $user->fetch();
            return $a;
        } catch (\Exception $e) {
            echo 'Error: -99';
            die;
        }
    }

    /************************************Liste des nationalités****************************************************************/
    public function nationalites()
    {
        try {
            $sql = "Select rowid, libelle
                from nationalite ORDER BY libelle ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            return $a;
        } catch (\Exception $e) {
            echo 'Error: -99';
            die;
        }
    }

    /************************************** Liste des type de pieces**************************************************************/
    public function typepiece()
    {
        try {
            $sql = "SELECT * FROM typecni ORDER BY lib_typecni ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            return $a;
        } catch (\Exception $e) {
            //echo 'Error: -99';
            echo $e;
            die;
        }
    }

    /***********************************************Liste des profession*****************************************************/
    public function professions()
    {
        try {
            $sql = "SELECT * FROM profession ORDER BY profession.`libelle` ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute();
            $a = $user->fetchAll();
            return $a;
        } catch (\Exception $e) {
            // echo 'Error: -99';
            echo $e;
            die;
        }
    }

    /***********************************************Liste des regions*****************************************************/
    public function allRegion()
    {
        try {
            $sql = "Select idregion, lib_region
                    from region
                    WHERE etat = :etat
					/*AND idregion IN ( 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31 )*/
                    ORDER BY lib_region ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute(
                array(
                    "etat" => 1,
                )
            );
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (\Exception $e) {
            //$this->pdo = NULL;
            //echo 'Error: -99';
            echo $e;
            die;
        }
    }


    /***********************************************Liste des regions par pays == beni*****************************************************/
    public function allRegionByPays($pays = '')
    {
        try {
            if ($pays != '') {
                $sql = "Select idregion, lib_region
                    from region
                    WHERE etat = :etat AND fk_pays = :pays
                    ORDER BY lib_region ASC";
                $user = $this->pdo->prepare($sql);
                $user->execute(
                    array(
                        "etat" => 1,
                        "pays" => $pays
                    )
                );
            } else {
                $sql = "Select idregion, lib_region
                    from region
                    WHERE etat = :etat AND fk_pays = ".ID_PAYS."
                    ORDER BY lib_region ASC";
                $user = $this->pdo->prepare($sql);
                $user->execute(
                    array(
                        "etat" => 1,
                    )
                );
            }

            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (\Exception $e) {
            //$this->pdo = NULL;
            echo 'Error: -99';

        }
    }

    /************************************************ Liste des catégories****************************************************/
    public function categories()
    {
        try {
            $sql = "SELECT * FROM categorie ORDER BY categorie.lib_categorie ASC";
            $user = $this->pdo->prepare($sql);
            $user->execute(
                array(
                    "etat" => 1,
                )
            );
            $a = $user->fetchAll();
            //$this->pdo = NULL;
            return $a;
        } catch (\Exception $e) {
            $this->pdo = NULL;
            echo 'Error: -99';

        }
    }

    /************************************************ NOM PRENOM UTILISATEUR****************************************************/
    public function getUser($id)
    {
        try {
            $sql = "SELECT nom,prenom FROM user WHERE rowid =:id";
            $user = $this->pdo->prepare($sql);
            $user->execute(
                array(
                    "id" => $id,
                )
            );
            $a = $user->fetch();
            return $a['prenom'] . ' ' . $a['nom'];
        } catch (\Exception $e) {
            $this->pdo = NULL;
            return "Pas de nom";
        }
    }

    /************************************************ NOM AGENCE****************************************************/
    public function geAgence($id)
    {
        try {
            $sql = "SELECT label FROM agence WHERE rowid =:id";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("id" => $id));
            $a = $user->fetchObject();
            return $a->label;
        } catch (\Exception $e) {
            $this->pdo = NULL;
            return "Pas de nom";
        }
    }

    /*********Courbe Evolution*********/
    public function montantServiceMensuel($service, $mois, $annee, $bureau, $courbe)
    {
        $cond = '';
        if ($service > 0) {
            $cond .= " AND h.fk_service=" . $service;
        }
        if ($bureau > 0) {
            $cond .= " AND h.fk_agence=" . $bureau;
        }
        $statut = 1;
        if ($courbe == 1) {
            $sql = "SELECT COUNT(h.rowid) AS nbre, MONTH(h.date_transaction) AS mois, YEAR(h.date_transaction) AS annee
                FROM transaction h JOIN agence a ON h.fk_agence = a.rowid
                WHERE h.statut=1 " . $cond . "
                AND MONTH(h.date_transaction) =:mois
                AND YEAR(h.date_transaction) =:annee
                GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 2) {
            $sql = "SELECT SUM(CASE  
                             WHEN h.fk_service=18 THEN 3000 
                             WHEN h.fk_service=19 THEN 3000
                             WHEN h.montant=0 THEN 0  
                             ELSE h.montant
                           END) AS mt ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h JOIN agence a ON h.fk_agence = a.rowid
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";
        } else if ($courbe == 3) {
            $sql = "SELECT 
                       SUM(CASE  
                             WHEN h.fk_service=18 THEN 2000 
                             WHEN h.fk_service=19 THEN 7000
                             ELSE h.commission
                           END) AS frais ,
                           MONTH(h.date_transaction) AS mois,   
            YEAR(h.date_transaction) AS annee
            FROM transaction h JOIN agence a ON h.fk_agence = a.rowid
            WHERE h.statut=1 " . $cond . "
            AND h.fk_service != 6
            AND MONTH(h.date_transaction) =:mois
            AND YEAR(h.date_transaction) =:annee
            GROUP BY mois ORDER BY annee ";

        }

        try {
            $stmt_stat_service = $this->pdo->prepare($sql);
            $stmt_stat_service->bindParam("mois", $mois);
            $stmt_stat_service->bindParam("annee", $annee);
            $stmt_stat_service->execute();
            $returner = $stmt_stat_service->fetchAll();
        } catch (\PDOException $e) {
            $return = 1001;
        }
        return $returner;
    }

    /****************get Nom Beneficiaire par Numéro de Carte****************/
    public function nomBeneficiareParCarte($carte)
    {
        $sql = "SELECT b.nom, b.prenom, b.prenom1 FROM beneficiaire b JOIN carte c ON b.rowid = c.beneficiaire_rowid WHERE c.statut=1 AND b.statut=1 AND c.rowid =:carte ";
        try {
            $stmt_agence = $this->pdo->prepare($sql);
            $stmt_agence->execute(array("carte" => $carte));
            $row_agence = $stmt_agence->fetchObject();
            $count_row = $stmt_agence->rowCount();
            if ($count_row > 0) return $row_agence->prenom . ' ' . $row_agence->prenom1 . ' ' . $row_agence->nom;
            else return -1;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    public function date_fr2($date)
    {
        $date_fr = "";
        if ($date != "") {
            $date_en = substr($date, 0, 10);
            $ss = substr($date, 17, 2);
            $ii = substr($date, 14, 2);
            $hh = substr($date, 11, 2);
            $jj = substr($date, 8, 2);
            $mm = substr($date, 5, 2);
            $aa = substr($date, 0, 4);

            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janvier";
                    break;
                case "02":
                    $mm = "Fevrier";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juillet";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Septembre";
                    break;
                case "10":
                    $mm = "Octobre";
                    break;
                case "11":
                    $mm = "Novembre";
                    break;
                case "12":
                    $mm = "Décembre";
                    break;
            }
            ////////////////
            $date_fr = $jj . " " . $mm . " " . $aa;
        }
        return $date_fr;
    }

    /****************get Nom Beneficiaire par Numéro de Carte****************/
    function nomBeneficiareParCarte1($carte)
    {
        $sql = "SELECT b.nom, b.prenom, b.prenom1 FROM beneficiaire b JOIN carte c ON b.rowid = c.beneficiaire_rowid WHERE c.statut=1 AND b.statut=1 AND c.rowid =:carte ";
        try {
            $stmt_agence = $this->pdo->prepare($sql);
            $stmt_agence->execute(array("carte" => $carte));
            $row_agence = $stmt_agence->fetchObject();
            $count_row = $stmt_agence->rowCount();
            if ($count_row > 0) return $row_agence->prenom . ' ' . $row_agence->prenom1 . ' ' . $row_agence->nom;
            else return -1;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    function nomBeneficiareParCarteBis($carte)
    {
        $sql = "SELECT b.nom, b.prenom, b.prenom1, c.telephone FROM beneficiaire b JOIN carte c ON b.rowid = c.beneficiaire_rowid WHERE c.statut=1 AND b.statut=1 AND c.rowid =:carte ";
        try {
            $stmt_agence = $this->pdo->prepare($sql);
            $stmt_agence->execute(array("carte" => $carte));
            $row_agence = $stmt_agence->fetchObject();
            $count_row = $stmt_agence->rowCount();
            if ($count_row > 0) return $row_agence;
            else return -1;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /************Date comprise entre deux dates************/
    public function getDatesBetween($start, $end)
    {
        if ($start > $end) {
            return false;
        }
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $dates = array();

        for ($i = $sdate; $i <= $edate; $i += strtotime('+1 day', 0)) {
            $dates[] = date('Y-m-d', $i);
        }

        return $dates;
    }

    /****************Nommbre des Retraits Tiers MAURIPOST par Date et Bureau*************************/
    public function nombreRetraitTiers($datereceiver, $agence)
    {
        try {
            $sql = "SELECT COUNT(t.num_transac) as nombre, DATE(t.date_receiver) as  datereceiver 
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid  
                    WHERE DATE(t.date_receiver) =:datereceiver AND s.fk_agence =:agence AND t.statut = 1 
                    GROUP BY datereceiver";

            $rs_carteActive = $this->pdo->prepare($sql);
            $rs_carteActive->bindParam("datereceiver", $datereceiver);
            $rs_carteActive->bindParam("agence", $agence);
            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->nombre;
            else return 0;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /****************Montant des Retraits Tiers MAURIPOST par Date et Bureau*************************/
    public function montantRetraitTiers($datereceiver, $agence)
    {
        try {
            $sql = "SELECT SUM(montant) as montant, DATE(t.date_receiver) as  datereceiver 
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid  
                    WHERE DATE(t.date_receiver) =:datereceiver AND s.fk_agence =:agence AND t.statut = 1 
                    GROUP BY datereceiver";

            $rs_carteActive = $this->pdo->prepare($sql);
            $rs_carteActive->bindParam("datereceiver", $datereceiver);
            $rs_carteActive->bindParam("agence", $agence);
            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->montant;
            else return 0;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /****************Nombre des Retraits Titulaire MAURIPOST par Date et Bureau*************************/
    public function nombreRetraitTitulaire($date_transaction, $agence)
    {

        try {
            $sql = "SELECT DATE(date_transaction) as datetransaction, COUNT(rowid) as nombre
                    FROM transaction  
                    WHERE DATE(date_transaction) =:date_transaction
                    AND fk_agence=:agence
                    AND fk_service = 10
                    AND statut = 1
                    GROUP BY datetransaction";

            $rs_carteActive = $this->pdo->prepare($sql);
            $rs_carteActive->bindParam("date_transaction", $date_transaction);
            $rs_carteActive->bindParam("agence", $agence);
            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->nombre;
            else return 0;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /****************Montant des Retraits Titulaire MAURIPOST par Date et Bureau*************************/
    public function montantRetraitTitulaire($date_transaction, $agence)
    {
        try {
            $sql = "SELECT DATE(date_transaction) as datetransaction, SUM(montant) as montant FROM transaction WHERE DATE(date_transaction) =:date_transaction AND fk_agence=:agence AND fk_service = 10 
              AND statut = 1 GROUP BY datetransaction";

            $rs_carteActive = $this->pdo->prepare($sql);
            $rs_carteActive->bindParam("date_transaction", $date_transaction);
            $rs_carteActive->bindParam("agence", $agence);
            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->montant;
            else return 0;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    /****************Tableau de Bord Général*************************/
    public function nbretableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
        try {
            $sql = '';
            if ($service == 20) {
                $sql = "SELECT COUNT(t.num_transac) as nombre
                  FROM tranfert t JOIN user s ON t.user_receiver = s.rowid
                  WHERE DATE(t.date_receiver) >=:date_debut  
                  AND DATE(t.date_receiver) <=:date_fin 
                  AND s.fk_agence =:agence 
                  AND t.statut = 1 
                  GROUP BY s.fk_agence ";
                $rs_carteActive = $this->pdo->prepare($sql);
                $rs_carteActive->bindParam("date_debut", $date_debut);
                $rs_carteActive->bindParam("date_fin", $date_fin);
                $rs_carteActive->bindParam("agence", $agence);
            } else {
                $sql = "SELECT COUNT(t.num_transac) as nombre
                  FROM transaction t
                  WHERE DATE(t.date_transaction) >=:date_debut
                  AND DATE(t.date_transaction) <=:date_fin 
                  AND t.fk_service =:service
                  AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence";

                $rs_carteActive = $this->pdo->prepare($sql);
                $rs_carteActive->bindParam("date_debut", $date_debut);
                $rs_carteActive->bindParam("date_fin", $date_fin);
                $rs_carteActive->bindParam("service", $service);
                $rs_carteActive->bindParam("fk_agence", $agence);
            }

            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->nombre;
            else return 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function mttableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
        try {
            $sql = '';
            if ($service == 20) {
                $sql = "SELECT SUM(t.montant) as montant
                    FROM tranfert t JOIN user s ON t.user_receiver = s.rowid
                    WHERE DATE(t.date_receiver) >=:date_debut  
                    AND DATE(t.date_receiver) <=:date_fin 
                    AND s.fk_agence =:agence 
                    AND t.statut = 1 
                   GROUP BY s.fk_agence ";
                $rs_carteActive = $this->pdo->prepare($sql);
                $rs_carteActive->bindParam("date_debut", $date_debut);
                $rs_carteActive->bindParam("date_fin", $date_fin);
                $rs_carteActive->bindParam("agence", $agence);
            } else {
                $sql = "SELECT SUM(t.montant) as montant
                    FROM transaction t
                    WHERE DATE(t.date_transaction) >=:date_debut
                    AND DATE(t.date_transaction) <=:date_fin 
                    AND t.fk_service =:service
                    AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence";

                $rs_carteActive = $this->pdo->prepare($sql);
                $rs_carteActive->bindParam("date_debut", $date_debut);
                $rs_carteActive->bindParam("date_fin", $date_fin);
                $rs_carteActive->bindParam("service", $service);
                $rs_carteActive->bindParam("fk_agence", $agence);
            }

            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->montant;
            else return 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function commissiontableauBordParDate($date_debut, $date_fin, $service, $agence)
    {
        try {
            $sql = '';
            if ($service != 20) {
                $sql = "SELECT SUM(t.commission) as commission
                  FROM transaction t
                  WHERE DATE(t.date_transaction) >=:date_debut
                  AND DATE(t.date_transaction) <=:date_fin 
                  AND t.fk_service =:service
                  AND t.fk_agence =:fk_agence
                  AND t.statut = 1 
                  GROUP BY t.fk_agence";

                $rs_carteActive = $this->pdo->prepare($sql);
                $rs_carteActive->bindParam("date_debut", $date_debut);
                $rs_carteActive->bindParam("date_fin", $date_fin);
                $rs_carteActive->bindParam("service", $service);
                $rs_carteActive->bindParam("fk_agence", $agence);
            }

            $rs_carteActive->execute();
            $row_rs_carteActive = $rs_carteActive->fetchObject();
            $totalRows = $rs_carteActive->rowCount();
            if ($totalRows > 0) return $row_rs_carteActive->commission;
            else return 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /****************get Nom Service par rowid****************/
    public function getNomService($service)
    {
        $sql_agence = "SELECT label FROM service WHERE rowid =:service";
        try {
            $stmt_agence = $this->pdo->prepare($sql_agence);
            $stmt_agence->execute(array("service" => $service));
            $row_agence = $stmt_agence->fetchObject();
            $count_row = $stmt_agence->rowCount();
            if ($count_row > 0) return $row_agence->label;
            else return '';
        } catch (\PDOException $e) {
            return -1;
        }
    }

    function moisLettre($date)
    {
        $date_fr = '';
        if ($date != "") {
            $mm = $date;
            //mois en lettre
            switch ($mm) {
                case "01":
                    $mm = "Janvier";
                    break;
                case "02":
                    $mm = "Février";
                    break;
                case "03":
                    $mm = "Mars";
                    break;
                case "04":
                    $mm = "Avril";
                    break;
                case "05":
                    $mm = "Mai";
                    break;
                case "06":
                    $mm = "Juin";
                    break;
                case "07":
                    $mm = "Juillet";
                    break;
                case "08":
                    $mm = "Aout";
                    break;
                case "09":
                    $mm = "Septembre";
                    break;
                case "10":
                    $mm = "Octobre";
                    break;
                case "11":
                    $mm = "Novembre";
                    break;
                case "12":
                    $mm = "Décembre";
                    break;
            }

            $date_fr = $mm;
        }
        return $date_fr;
    }

    /*************************************************rechargement  Mensuel par Service*********************************************/
    public function montantRechargementMensuel($service)
    {
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS mt, SUM(transaction.commission) AS frais, MONTH(transaction.date_transaction) AS mois, YEAR(transaction.date_transaction) AS annee
        FROM transaction
        WHERE transaction.statut=:statut
        AND transaction.fk_service != 6
        AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
        AND transaction.fk_service =:service
        AND transaction.date_transaction > DATE_SUB( now( ) , INTERVAL 6 MONTH )
               GROUP BY mois, annee
                ORDER BY annee ";
        $returner = -1;

        try {
            $resultat = $this->pdo->prepare($sql);
            $resultat->bindParam("statut", $statut);
            $resultat->bindParam("service", $service);
            $resultat->execute();
            $returner = $resultat->fetchAll();
        } catch (\PDOException $e) {
            $returner = 1001;  //Erreur Exception
        }
        //$this->pdo = NULL;

        return $returner;
    }

    /***************************************************Statistique par  service par date********************************************/
    public function getTransactionStat($date_debut, $date_fin, $service, $agency)
    {

        $cdt = '';
        if ($service > 0) {
            $cdt .= "  AND transaction.fk_service=:service ";
        }

        $cdt1 = '';
        if ($agency > 0) {
            $cdt1 .= " AND transaction.fk_agence=:agence ";
        }

        $cdt2 = '';
        if (strlen($date_debut) > 5) {
            $cdt2 .= " AND DATE(transaction.date_transaction)>=:datedebut ";
        }
        if (strlen($date_fin) > 5) {
            $cdt2 .= " AND DATE(transaction.date_transaction)<=:datefin";
        }
        $statut = 1;
        $sql = "SELECT SUM(transaction.montant) AS montant, SUM(transaction.commission) AS frais, service.label, transaction.fk_service
            FROM transaction, service
            WHERE transaction.statut=:statut
            AND transaction.num_transac IS NOT NULL AND transaction.num_transac != ''
            AND transaction.fk_service != 6
            AND service.etat = 1
            AND transaction.fk_service = service.rowid " . $cdt . " " . $cdt1 . " " . $cdt2 . "
            GROUP BY transaction.fk_service";
        $return = -1;
        try {
            $result = $this->pdo->prepare($sql);

            $result->bindParam("statut", $statut);

            if (strlen($date_debut) > 5) {
                $result->bindParam("datedebut", $date_debut);
            }

            if (strlen($date_fin) > 5) {
                $result->bindParam("datefin", $date_fin);
            }

            if ($service > 0) {
                $result->bindParam("service", $service);
            }

            if ($agency > 0) {
                $result->bindParam("agence", $agency);
            }

            $result->execute();

            $return = $result->fetchAll();
        } catch (\PDOException $e) {
            $return = 1001;  //Erreur Exception
        }
        return $return;
    }

    public function getDateNow($arg = '')
    {
        return ($arg == "WITH_TIME") ? \gmstrftime("%Y-%m-%d") . " " . \gmstrftime("%T") : \gmstrftime("%Y-%m-%d");
    }

    /**
     * @param array $paramFiles
     * @param string $url
     * @param string $nameFile
     * @return bool
     */
    public function setUploadFiles($paramFiles = [], $url = "", $nameFile = "")
    {
        if (\count($paramFiles) > 0 && $paramFiles["error"] != "4" && $url != "") {
            if ($nameFile == "") $nameFile = $this->getDateNow('WITH_TIME');
            $nameFile .= "." . \pathinfo($paramFiles['name'], PATHINFO_EXTENSION);

            //return $paramFiles['tmp_name'].'-'. ROOT_FILE . $url . $nameFile;
            $res = move_uploaded_file($paramFiles['tmp_name'], $url . $nameFile);
            return $res;
            //return (move_uploaded_file($paramFiles['tmp_name'], ROOT_FILE . $url . $nameFile)) ? $nameFile : false;
        }
        return false;
    }

    /**
     * @param string $url
     * @return bool
     */
    public static function createDir($url = "")
    {
        return ($url != "") ? ((!\is_dir(ROOT_FILE . $url)) ? \mkdir(ROOT_FILE . $url, 0777, true) : chmod(ROOT_FILE . $url, 0777)) : false;
    }


    /**
     * @param array $paramFiles
     * @param string $url
     * @param string $nameFile
     * @return bool
     */
    public static function setUploadFilesAlmanara($paramFiles = [], $url = "", $nameFile = "")
    {
        if (\count($paramFiles) > 0 && $paramFiles["error"] != "4" && $url != "") {
            if(!self::createDir($url)) return false;
            if($nameFile == "") $nameFile = gmdate("YmdHis");
            $nameFile .= ".".\pathinfo($paramFiles['name'], PATHINFO_EXTENSION);
            //return 'in';
            return (\move_uploaded_file($paramFiles['tmp_name'], ROOT_FILE.$url ."/". $nameFile)) ? $nameFile : false;
        }
        return false;
    }

    /****************************TOKEN PARTENAIRE*****************************************/
    public function getToken($id)
    {
        try {
            $sql = "SELECT token FROM authToken WHERE userId =:userId";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("userId" => $id,));
            $a = $user->fetch();
            return $a['token'];
        } catch (\PDOException $e) {
            return -1;
        }

    }

    //retourner Last 4 Digits
    public function returnLast4Digits($cardid)
    {
        if (strlen($cardid) > 4) return substr($cardid, -4);
        else return -1;
    }

    //retourner CustomerId 7 premiers caracteres de numero
    public function returnCustomerId($cardid)
    {
        if (strlen($cardid) > 7) return substr($cardid, 0, 7);
        else return -1;
    }


    /***********get Solde agence***************/
    public function getSoldeAgence($idagence)
    {
        try {
            $sql = "SELECT solde from agence  WHERE etat=:etat AND rowid=:code";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("etat" => 1, "code" => $idagence));
            $a = $user->fetchObject();
            $totalrows = $user->rowCount();
            if ($totalrows > 0) return $a->solde;
            else return -1;
        } catch (\PDOException $exception) {
            return -2;
        }
    }

    public function random($car)
    {
        $string = "";
        $chaine = "1234567890";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < $car; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }

    /*************generate Code Rechargement**************/
    public function generateCodeRechargement($fkagence)
    {
        $found = 0;
        do {
            $code = $this->random(10);
            $etat = $this->verifyCoderechargement($code);
            if ($etat == 1) {
                $found = 1;
                $this->insertCoderechargement($code, $fkagence);
            }
        } while ($found == 0);
        return $code;
    }

    /*************verify Code rechargement**************/
    public function verifyCoderechargement($code)
    {

        try {
            $sql = "SELECT id from code_rechargement WHERE code = :code";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("code" => $code));
            $a = $user->rowCount();
            if ($a > 0) return 0;
            else return 1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /****************Insert Code Rechargement**************/
    public function insertCoderechargement($code, $fkagence)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO code_rechargement(code, fk_agence, statut, date) VALUES (:code, :num_carte, :statut, :dat)";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array("code" => $code, "num_carte" => $fkagence, "statut" => 0, "dat" => $date));
            if ($res == 1) return 1;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    public function envoiCodeRechargement($dest, $nom="", $code="", $langue="")
    {
        $de_nom = $langue['plateforme_postecash'];
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = $langue['rechargement_carte_particulier'];
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_1'] . "</div></br>";
        $message .= "<div align='left'>" . $langue['envoi_mail_code_rechargement_2'] . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_3'] . " </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_4'] . " : </b>" . date('d-m-Y H:i:s') . "</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }

    public function envoiCodeRetrait($dest, $nom="", $code="")
    {
        $de_nom = 'MAURIPOST';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Retrait Espece";
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>Pour valider votre retrait espece</div></br>";
        $message .= "<div align='left'>Merci de valider votre transaction avec le code ci-dessous</div></br>";
        $message .= "<div align='left'><b>Code de validation </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>Date et heure: </b>" . date('d-m-Y H:i:s') . "</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }


  /*  public function envoiParamsMarchand($dest, $nom, $marchand, $login, $password, $clesecrete, $code="", $compte="")
    {
        $de_nom = 'MAURIPOST';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Informations marchand";
        $message = "<div align='center'><b>" . $de_nom . "</b></div></br>";
        $message .= "<div align='center'><b>" . $sujet . "</b></div>";
        $message .= "</br>";
        $message .= "</br>";
        $message .= "<div align='left'>Cher " . $nom . ",</div></br>";
        $message .= "<div align='left'>votre compte marchand vient d'être créé. Merci de trouver ci-dessous les informations de votre compte:</div></br>";
        $message .= "<div align='left'><b>Numéro de compte: </b>: " . $compte . "</div></br>";
        $message .= "<div align='left'><b>Nom marchand: </b>: " . $marchand . "</div></br>";
        $message .= "<div align='left'><b>Code marchand: </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>Login: </b>: " . $login . "</div></br>";
        $message .= "<div align='left'><b>Mot de passe: </b>: " . $password . "</div></br>";
        $message .= "<div align='left'><b>Clé secrète: </b>: " . $clesecrete . "</div></br>";
        $message .= "<div align='left'><b>Date et heure: </b>" . date('d-m-Y H:i:s') . "</div>";
        //$message .= "<div align='left'>Cliquer <a href='#'>ici</a> pour accéder à l'espace marchand</div>";
        $message .= "<br/>";
        $message .= "<br/>";
        $message .= "<div align='left'>NB: Nous vous rappelons que le login, le mot de passe et la cle secrete sont confidentielles.</div>";
        $message .= "<div align='left'>Merci de votre confiance.</div>";
        $message .= "<div align='left'>MAURIPOST</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@numherit.com>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }*/



    public function envoiParamsMarchand($dest, $nom, $marchand, $login, $password, $clesecrete, $code)
    {
        $de_nom = 'MAURIPOST';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Informations marchand";

        $message = "</br>";
        $message .= "</br>";
        $message .= "<div align='left'>Cher " . $nom . ",</div></br>";
        $message .= "<div align='left'>votre compte marchand vient d'être créé. Merci de trouver ci-dessous les informations de votre compte:</div></br>";
        $message .= "<div align='left'><b>Nom marchand: </b>: " . $marchand . "</div></br>";
        $message .= "<div align='left'><b>Code marchand: </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>Login: </b>: " . $login . "</div></br>";
        $message .= "<div align='left'><b>Mot de passe: </b>: " . $password . "</div></br>";
        $message .= "<div align='left'><b>Clé secrète: </b>: " . $clesecrete . "</div></br>";
        $message .= "<div align='left'><b>Date et heure: </b>" . date('d-m-Y H:i:s') . "</div>";
        $message .= "<div align='left'>Cliquer <a href=".URL_ACCEPTEUR .">ici</a> pour accéder à l'espace marchand</div>";
        $message .= "<br/>";
        $message .= "<br/>";
        $message .= "<div align='left'>NB: Nous vous rappelons que le login, le mot de passe et la cle secrete sont confidentielles.</div>";
        $message .= "<div align='left'>Merci de votre confiance.</div>";
        $message .= "<div align='left'>MAURIPOST</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }


    public static function envoiParamsDistributeur($dest, $nom="", $login="", $password="")
    {
        $link =  $_SERVER['HTTP_HOST'].'/mauripost.mrfs/distributeur';
        $de_nom = 'MAURIPOST';
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = "Informations distributeur";
        $message = "<div align='center'><b>" . $de_nom . "</b></div></br>";
        $message .= "<div align='center'><b>" . $sujet . "</b></div>";
        $message .= "</br>";
        $message .= "</br>";
        $message .= "<div align='left'>Cher " . $nom . ",</div></br>";
        $message .= "<div align='left'>votre compte distributeur vient d'être créé. Merci de trouver ci-dessous les informations de votre compte:</div></br>";
        $message .= "<div align='left'><b>Login: </b>: " . $login . "</div></br>";
        $message .= "<div align='left'><b>Mot de passe: </b>: " . $password . "</div></br>";
        $message .= "<div align='left'><b>Date et heure: </b>" . date('d-m-Y H:i:s') . "</div>";
        $message .= "<br/>";
        $message .= "<div align='left'>Cliquer <a href='http://".$link."'>ici</a> pour accéder à l'espace distributeur</div>";
        $message .= "<br/>";
        $message .= "<br/>";
        $message .= "<div align='left'>NB: Nous vous rappelons que le login et le mot de passe sont confidentielles.</div>";
        $message .= "<div align='left'>Merci de votre confiance.</div>";
        $message .= "<div align='left'>MAURIPOST</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }

    /*********Recuperer mail receveur**********/
    function recup_mailBenef($telephone)
    {
        try {
            $query_rs_coderetrait = "SELECT b.email FROM beneficiaire b INNER JOIN carte c ON c.beneficiaire_rowid = b.rowid WHERE c.telephone = :mail";
            $resultatcode = $this->pdo->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" => $telephone));

            $totalRows_rs_resultatcode = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if ($totalRows_rs_resultatcode > 0) return $rs_resultatcode->email;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /*********Recuperer mail receveur**********/
    function recup_mail($connecter)
    {
        try {
            $query_rs_coderetrait = "SELECT email FROM agence WHERE rowid = :mail";
            $resultatcode = $this->pdo->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" => $connecter));
            $totalRows_rs_resultatcode = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if ($totalRows_rs_resultatcode > 0) return $rs_resultatcode->email;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }


    /*********Recuperer mail receveur**********/
    function recup_tel($connecter)
    {
        try {
            $query_rs_coderetrait = "SELECT tel FROM agence WHERE rowid =:mail";
            $resultatcode = $this->pdo->prepare($query_rs_coderetrait);
            $resultatcode->execute(array("mail" => $connecter));
            $totalRows_rs_resultatcode = $resultatcode->rowCount();
            $rs_resultatcode = $resultatcode->fetchObject();
            if ($totalRows_rs_resultatcode > 0) return $rs_resultatcode->tel;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /*********change Statut Code rechargement**********/
    public function changeStatutCoderechargement($id)
    {
        try {
            $sql = "UPDATE code_rechargement SET statut = :statut WHERE id = :id";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array("statut" => 1, "id" => $id));
            return $res;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /*****************rechercher Code rechargement*************/
    public function rechercherCoderechargement($code, $fkagence)
    {
        try {
            $sql = "SELECT id from code_rechargement WHERE code =:code AND fk_agence =:num AND statut =:statut";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("code" => $code, "num" => $fkagence, "statut" => 0));
            $a = $user->fetchObject();
            $rowtot = $user->rowCount();
            if ($rowtot > 0) return $a->id;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /************************************Generer num transaction***********************************/
    public function Generer_numtransaction()
    {
        $found = 0;
        while ($found == 0) {
            $code_carte = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $colname_rq_code_existe = $code_carte;
            $sql = "SELECT rowid FROM transaction WHERE num_transac =:num";
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam("num", $colname_rq_code_existe);
                $stmt->execute();
                $employee = $stmt->fetchObject();
                $totalRows_rq_code_existe = $stmt->rowCount();
                if ($totalRows_rq_code_existe == 0) {
                    $code_generer = $code_carte;
                    $found = 1;
                    break;
                }
            } catch (\PDOException $e) {
                $code_generer = -2;
                die;
            }
        }
        return $code_generer;
    }

    /*************************************Save transaction************************************/
    public function SaveTransaction($num_transac, $service, $montant, $rowid_carte, $fkuser, $statut = 0, $commentaire = '', $commission = 0, $fk_agence = 0, $transactId = 0, $fkuser_support = 0, $fkagence_support = 0)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO transaction (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_carte, commentaire, commission, fk_agence, transactionID, fkuser_support,fkagence_support )
				    VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_carte, :commentaire, :commission, :fk_agence, :transactionID, :fkuser_support, :fkagence_support )";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => $num_transac,
                "date_transaction" => $datetransaction,
                "montant" => $montant,
                "statut" => $statut,
                "fkuser" => $fkuser,
                "fk_service" => $service,
                "fk_carte" => $rowid_carte,
                "commentaire" => $commentaire,
                "commission" => $commission,
                "fk_agence" => $fk_agence,
                "transactionID" => $transactId,
                "fkuser_support" => $fkuser_support,
                "fkagence_support" => $fkagence_support
            ));
            //var_dump($res);die;
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return $Exception;
        }
    }

    /*******************************Ajouter les detail des transactions******************************************/
    function saveDetailsTranscation($numtransaction, $numcarte, $montant, $sens, $date_op)
    {
        try {

            $insertSQL = "INSERT INTO detail_transaction(numtransaction, numcarte, montant, sens, date_op)";
            $insertSQL .= "VALUES ('" . $numtransaction . "', '" . $numcarte . "', '" . $montant . "', '" . $sens . "', '" . $date_op . "') ";
            $resultat = $this->pdo->prepare($insertSQL);
            $return = $resultat->execute();
            if ($return == 1) return $return;
            else return -1;
        } catch (\PDOException $Exception) {
            return -2;
        }
    }

    /*******************************Ajouter les comsissions******************************************/
    public function addCommission($montant_commission, $idservices, $carte = '', $observations = "", $fk_agence)
    {
        try {
            $query_insert = "INSERT INTO transaction_commission( montant_commission, idservices, observations, idagence, num_carte)
                                  VALUES (:montant_commission,:idservices,:observations,:idagence,:num_carte )";
            $rs_insert = $this->pdo->prepare($query_insert);
            $result = $rs_insert->execute(
                array(
                    "montant_commission" => intval($montant_commission),
                    "idservices" => intval($idservices),
                    "observations" => strval($observations),
                    "idagence" => intval($fk_agence),
                    "num_carte" => intval($carte)
                ));
            if ($result == 1) return 1;
            else return -1;

        } catch (\Exception $e) {
            return -2;
        }
    }

    /*************Ajouter les comsissions a faire************************/

    public function addCommission_afaire($montant_commission, $idservices, $carte = '', $observations = "", $fk_agence)
    {
        try {
            $query_insert = "INSERT INTO transaction_commission_afaire( montant_commission, idservices, observations, idagence, num_carte) 
                              VALUES (:montant_commission,:idservices,:observations,:idagence,:num_carte )";
            $rs_insert = $this->pdo->prepare($query_insert);
            $result = $rs_insert->execute(
                array(
                    "montant_commission" => intval($montant_commission),
                    "idservices" => intval($idservices),
                    "observations" => strval($observations),
                    "idagence" => intval($fk_agence),
                    "num_carte" => intval($carte)
                ));
            if ($result == 1) return 1;
            else return -1;

        } catch (\Exception $e) {
            return -2;
        }
    }

    /********************crediter carte Parametrable****************/
    public function crediter_carteParametrable($montant, $idcarte)
    {
        try {
            $req = $this->pdo->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=:idcarte");
            $Result1 = $req->execute(array("soldes" => $montant, "idcarte" => $idcarte));
            if ($Result1 > 0) return 1;
            else return 0;
        } catch (\PDOException $e) {
            return -2;
        }
    }


    /********************crediter carte Parametrable****************/
    public function getIdCarteByTel($tel)
    {
        try {
            $req = $this->pdo->prepare("SELECT rowid FROM carte WHERE telephone = :tel");
            $req->bindValue('tel', $tel);
            $req->execute();
            if ($req->rowCount() === 1) {
                $object = $req->fetchObject();
                return $object->rowid;
            } else return 0;
        } catch (\PDOException $e) {
            return -2;
        }
    }

    /********************crediter carte commissio****************/
    function crediterCarteCommission($montant)
    {
        $return = 0;
        try {
            $req = $this->pdo->prepare("UPDATE carte_parametrable SET solde=solde+:soldes WHERE idcarte=1");
            $Result1 = $req->execute(array("soldes" => $montant));
            $dbh = null;
            if ($Result1 > 0) {
                $return = 1;
            } else $return = 0;
        } catch (\PDOException $e) {
            $return = -1;
        }
        return $return;
    }

    /*****************get Transaction by Num transaction***************/
    public function transactionByNum($numeroTransact)
    {
        try {
            $sql = "SELECT DISTINCT rowid, fk_carte, num_transac, fkuser, fk_agence, montant, fk_service, date_transaction, statut, commission  FROM transaction WHERE statut=1 AND num_transac = :num ORDER BY rowid DESC ";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("num" => strval($numeroTransact)));
            $a = $user->fetchObject();
            $ligne = $user->rowCount();
            if ($ligne > 0) return $a;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    public function trimUltime($chaine)
    {
        $chaine = trim($chaine);
        $chaine = str_replace("\t", "", $chaine);
        $chaine = str_replace("\r", "", $chaine);
        $chaine = str_replace("\n", "", $chaine);
        $chaine = preg_replace("( +)", "", $chaine);
        $chaine = str_replace("_", "", $chaine);
        $chaine = str_replace("-", "", $chaine);
        $chaine = str_replace("`", "", $chaine);
        $chaine = str_replace("^", "", $chaine);
        $chaine = str_replace("°", "", $chaine);
        $chaine = str_replace("'", "", $chaine);
        $chaine = str_replace("$", "", $chaine);
        $chaine = str_replace("¨", "", $chaine);
        $chaine = str_replace("^", "", $chaine);
        $chaine = str_replace("&", "", $chaine);
        $chaine = str_replace("\"", "", $chaine);
        $chaine = str_replace("?", "", $chaine);
        $chaine = str_replace("÷", "", $chaine);
        $chaine = str_replace("≠", "", $chaine);
        $chaine = str_replace("…", "", $chaine);
        $chaine = str_replace("°", "", $chaine);
        $chaine = str_replace(">", "", $chaine);
        $chaine = str_replace("<", "", $chaine);
        $chaine = str_replace(";", "", $chaine);
        $chaine = str_replace(":", "", $chaine);
        $chaine = str_replace("=", "", $chaine);
        $chaine = str_replace("€", "", $chaine);
        $chaine = strtr($chaine, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
        return $chaine;
    }

//
    public function save_DetailTransaction($numtransaction, $numcarte, $montant, $sens)
    {

        try {
            $sql = "INSERT INTO detail_transaction(numtransac, fkcarte, montant, sens, date_op)
        VALUES (:numtransaction, :numcarte, :montant, :sens, :date_op)";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array(
                "numtransaction" => $numtransaction,
                "numcarte" => strval($numcarte),
                "montant" => $montant,
                "sens" => $sens,
                "date_op" => strval($this->date),
            ));
            //$this->pdo = NULL;
            return $res;
        } catch (\Exception $e) {
            //$this->pdo = NULL;
            echo -99;
            die;
        }
    }

    public function soldeSMSOrange($token)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.orange.com/sms/admin/v1/contracts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token,
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        }
        else{
            $json = json_decode($response);
            //var_dump($json); die;
            //$sms = $json->{'partnerContracts'}->{'contracts'}[0]->{'serviceContracts'}[0]->{'availableUnits'};
            //return $json;
            $sms = $json->{'partnerContracts'}->{'contracts'}[0]->{'serviceContracts'}[1]->{'availableUnits'};
            return $sms;
        }
    }

    public function tokenSMSOrange()
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.orange.com/oauth/v2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Authorization: Basic VUhmUHB2QWt4NlN0c0FZY0V2N2N1QkNidmU4VEIwZEg6S3ZONkRkOU5uTFY2VGNlag==";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        else{
            $messages = '';
            $res = 0;
            $json = json_decode($result);
            if(array_key_exists('token_type', $json) && array_key_exists('access_token', $json) && array_key_exists('expires_in', $json)){
                $date_debut = date('Y-m-d H:i:s');
                $date_fin = date('Y-m-d', strtotime(date($date_debut)) + (int)$json->{'expires_in'});

                try {

                    $query_rq_service = "UPDATE operateur SET token = :token, date_fin = :expire WHERE rowid = 1";
                    $service = $this->pdo->prepare($query_rq_service);
                    $service->bindParam('token', $json->{'access_token'});
                    $service->bindParam('expire', $date_fin);
                    $service->execute();
                    if($service->rowCount() === 1)
                        $res = 1;
                }
                catch (PDOException $e){
                    $messages .= $e;
                }
            }

            if($res === 0){
                $messages .= "<br/>La regénération du token a échoué.<br/>Le token current expire dans moins de trois(3) jours.";
                @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
            }
        }
        curl_close($ch);

    }

    public function sendSMS($sender, $destinataire, $message){

        if($destinataire[0] == '+'){
            $destinataire = substr($destinataire, 1);
        }
        else if($destinataire[0] == '0' && $destinataire[1] == '0'){
            $destinataire = substr($destinataire, 2);
        }
        $operateur = substr($destinataire, 3, 2);

        try {

            $destinataire = str_replace(' ', '', $destinataire);
            $query_rq_service = "SELECT * FROM operateur WHERE statut=1";
            $service = $this->pdo->prepare($query_rq_service);
            $service->execute();
            $row_rq_service = $service->fetchObject();
            $to_day = date('Y-m-d');
            $expire = date('Y-m-d', strtotime($to_day . ' + 3 days'));
            if ($expire >= $row_rq_service->date_fin) {
                $this->tokenSMSOrange();
            }
            if ((int)$row_rq_service->rowid === 1) {

                $destinataire = '+' . $destinataire;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.orange.com/smsmessaging/v1/outbound/tel%3A%2B221000000000/requests',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{"outboundSMSMessageRequest":{"address":"tel:' . $destinataire . '","outboundSMSTextMessage":{"message":"' . $message . '"},"senderAddress":"tel:+221000000000","senderName":"' . $sender . '"}}',
                    CURLOPT_HTTPHEADER => array(
                        'accept: application/json',
                        'authorization: Bearer ' . $row_rq_service->token,
                        'content-type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    $messages = "Mauripost<br/>Erreur WS Envoi SMS Orange: " . $err . "</b>.<br/>Tel: ".$destinataire."<br/>Merci de faire le necessairee (Urgence).";
                    @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                    @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                    @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                    return -1;
                } else {
                    $json = json_decode($response);
                    //var_dump($json); die;
                    if (!array_key_exists('outboundSMSMessageRequest', $json) && array_key_exists('code', $json) && (int)$json->code === 42) {
                        $this->tokenSMSOrange();

                        return -1;
                    } else if (!array_key_exists('outboundSMSMessageRequest', $json) && array_key_exists('code', $json) && (int)$json->code === 41) {
                        $messages = "Mauripost<br/>Le nombre de SMS restant dans le compte est arrive a epuisement.: <b>0 sms</b>.<br/>Merci de recharger le compte (Urgence).";
                        @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                        @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                        @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        return -1;
                    } else if (!array_key_exists('outboundSMSMessageRequest', $json)) {
                        $messages = "Mauripost<br/>Erreur WS Envoi SMS Orange: " . json_encode($json) . "</b>.<br/>Tel: ".$destinataire."<br/>Merci de faire le necessairee (Urgence).";
                        @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                        @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                        @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        return -1;
                    }
                    else{
                        $nb_sms_restant = $this->soldeSMSOrange($row_rq_service->token);
                        if (($nb_sms_restant <= 500 && $nb_sms_restant % 10 === 0) || $nb_sms_restant <= 100) {
                            $messages = "Mauripost<br/>Le nombre de SMS restant dans le compte est faible: <b>" . $nb_sms_restant . " sms</b>.<br/>Merci de recharger le compte (Urgence).";
                            @$this->alerteSMS('madiop@numherit.com', 'Madiop GUEYE', $messages);
                            @$this->alerteSMS('papa.ngom@numherit.com', 'Papa NGOM', $messages);
                            @$this->alerteSMS('alioubalde@numherit.com', 'Aliou BALDE', $messages);
                        }
                        return 1;
                    }
                }


            } else if ((int)$row_rq_service->rowid === 2) {
                $token = base64_encode('jula-login:jula1986');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{ \"from\":\" " . $sender . "\", \"to\":\"" . $destinataire . "\", \"text\":\"" . $message . "\" }",
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "authorization: Basic " . $token . "",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
                if ($err) {
                    return "cURL Error #:" . $err;
                } else {
                    return $response;
                }
            } else if ((int)$row_rq_service->rowid === 3) {
                $destinataire = '+' . $destinataire;
                $url = 'https://api.primotexto.com/v2/notification/messages/send';
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-Primotexto-ApiKey: fd772fc697e680b76a07a71e9cd58209',
                ));
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"number\":\"$destinataire\",\"message\":\"$message\",\"sender\":\"$sender\",\"campaignName\":\"Code de confirmation\",\"category\":\"codeConfirmation\"}");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($curl);
                curl_close($curl);
                return $result;
            } else if ((int)$row_rq_service->rowid === 4) {
                $params = array(

                    'access_token' => 'Jhc5qS0eCRx5s8JEhMe5a39Bht5YDVPqHUsoiZ9O',          //sms api access token
                    'to' => '+' . $destinataire,         //destination number
                    'from' => 'Paositra',                //sender name has to be active
                    'message' => $message,    //message content
                );

                if ($params['access_token'] && $params['to'] && $params['message'] && $params['from']) {
                    $date = '?' . http_build_query($params);
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, 'https://api.smsapi.com/sms.do'.$date);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        // echo 'Error:' . curl_error($ch);
                        return curl_errno($ch);
                    }

                    curl_close ($ch);
                    return $result;
                }

            } else {
                $token = base64_encode('jula-login:jula1986');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{ \"from\":\" " . $sender . "\", \"to\":\"" . $destinataire . "\", \"text\":\"" . $message . "\" }",
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "authorization: Basic " . $token . "",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
                if ($err) {
                    return "cURL Error #:" . $err;
                } else {
                    return $response;
                }
            }
        } catch (Exception $e) {
            return $e;
        }

    }



    /*function sendSMS($sender = '', $destinataire, $message)
    {
        $sender2 = 'MAURIPOST';

        $postUrl = "http://api2.infobip.com/api/sendsms/xml";
        //XML-formatted data
        $xmlString =
            "<SMS> 
                                    <authentification> 
                                        <username>jula-login</username> 
                                        <password>jula1986</password> 
                                    </authentification> 
                                    <message> 
                                        <sender>".$sender2."</sender> 
                                        <text>".$message."</text> 
                                    </message> 
                                    <recipients> 
                                        <gsm>".$destinataire."</gsm> 
                                    </recipients> 
                            </SMS>";
        //previously formatted XML data becomes value of "XML" POST variable
        $fields = "XML=" . urlencode($xmlString);
        //in this example, POST request was made using PHP's CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        //response of the POST request CURLOPT_RETURNTRANSFER
        $response = curl_exec($ch);
        curl_close($ch);

        //write out the response
        return $response;

    }*/

    /***************send SMS*************/
    function sendSMS22($sender = '', $destinataire, $message)
    {
        $sender2 = 'MAURIPOST';


        if ($destinataire[0] == '+') {
            $destinataire = substr($destinataire, 1);
        } else if ($destinataire[0] == '0' && $destinataire[1] == '0') {
            $destinataire = substr($destinataire, 2);
        }

        $test_tel = substr($destinataire, 3, 2);
        $mtn = array('60', '63', '64', '65', '94', '95', '98', '99', '96', '97', '67', '66', '61', '62');

        if (in_array($test_tel, $mtn)) {

            $destinataire = '+' . $destinataire;
            //$destinataire = '+221774246535';
            $url = 'https://api.primotexto.com/v2/notification/messages/send';
            $curl = curl_init($url);
            /*if (isSet(baseManager::$CURLOPT_PROXY)) {
                curl_setopt($curl, CURLOPT_PROXY, baseManager::$CURLOPT_PROXY);
            }*/

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'X-Primotexto-ApiKey: fd772fc697e680b76a07a71e9cd58209',
            ));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"number\":\"$destinataire\",\"message\":\"$message\",\"sender\":\"$sender2\",\"campaignName\":\"Code de confirmation\",\"category\":\"codeConfirmation\"}");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($curl);
            //echo("$result\n");
            curl_close($curl);
            return $result;
        } else {

            $params = array(

                'access_token' => 'Jhc5qS0eCRx5s8JEhMe5a39Bht5YDVPqHUsoiZ9O',          //sms api access token
                'to' => '+' . $destinataire,         //destination number
                'from' => 'MAURIPOST',                //sender name has to be active
                'message' => $message,    //message content
            );

            if ($params['access_token'] && $params['to'] && $params['message'] && $params['from']) {
                $date = '?' . http_build_query($params);
                //echo 'https://api.smsapi.com/sms.do'.$date;
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://api.smsapi.com/sms.do' . $date);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    // echo 'Error:' . curl_error($ch);
                    return curl_errno($ch);
                }

                curl_close($ch);
                return $result;
                /*$file = fopen('https://api.smsapi.com/sms.do'.$date,'r');
                $result = fread($file,1024);
                fclose($file);
                return  $result;*/
            }

        }

    }

    /****************generate Code Retrait******************/
    public function generateCodeRetrait($carte, $montant)
    {
        $found = 0;
        do {
            $code = $this->random(10);
            $etat = $this->verifyCodeRetrait($code);
            if ($etat == 1) {
                $found = 1;
                $this->insertCodeRetrait($code, $carte, $montant);
            }
        } while ($found == 0);
        return $code;
    }

    /************verify Code Retrait**********/
    public function verifyCodeRetrait($code)
    {
        try {
            $sql = "SELECT idcode_retrait from code_retrait WHERE code_retrait = :code";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("code" => strval($code)));
            $a = $user->rowCount();
            if ($a > 0) return 0;
            else return 1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /******************insert Code Retrait*******************/
    public function insertCodeRetrait($code, $numcarte, $montant)
    {
        try {
            $sql = "INSERT INTO code_retrait(code_retrait, montant, num_carte, statut) VALUES (:code, :montant, :num_carte, :statut)";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array("code" => strval($code), "montant" => intval($montant), "num_carte" => intval($numcarte), "statut" => intval(0)));
            if ($res == 1) return 1;
            else return -1;
        } catch (\Exception $e) {
            return -1;
        }
    }

    /***********valider Code Retrait**************/
    public function validerCodeRetrait($id, $cni)
    {
        try {
            $date_retrait = date('Y-m-d H:i:s');
            $sql = "UPDATE code_retrait SET cni =:cni, statut =:statut, date_retrait =:dateretrait WHERE idcode_retrait =:id";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array("cni" => strval($cni), "statut" => intval(1), "dateretrait" => strval($date_retrait), "id" => intval($id)));
            if ($res == 1) return 1;
            else return -1;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /*************Recherchercher code retrait***************/
    public function rechercherCodeRetrait($code, $montant)
    {
        try {
            $sql = "SELECT idcode_retrait from code_retrait WHERE code_retrait = :code AND montant = :num AND statut = :statut";
            $user = $this->pdo->prepare($sql);
            $user->execute(array("code" => strval($code), "num" => intval($montant), "statut" => intval(0)));
            $a = $user->fetchObject();
            $totrows = $user->rowCount();
            if ($totrows > 0) {
                return $a->idcode_retrait;
            } else {
                return -1;
            }
        } catch (\Exception $e) {
            return -2;
        }
    }

    public function addMouvementCompteClient($num_transac, $soldeavant, $soldeapres, $montant, $compte, $operation, $commentaire)
    {
        // include('config.php');
        try {
            $date = date("Y-m-d H:i:s");
            $statut = 1;
            $query_insert = "INSERT INTO releve_comptes_client( num_transac, date_transaction, solde_avant, solde_apres, montant, idcompte, operation, commentaire) ";
            $query_insert .= " VALUES (:num_transac, :date_transaction, :solde_avant, :solde_apres, :montant, :compte, :operation, :commentaire)";
            $rs_insert = $this->pdo->prepare($query_insert);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':date_transaction', $date);
            $rs_insert->bindParam(':solde_avant', $soldeavant);
            $rs_insert->bindParam(':solde_apres', $soldeapres);
            $rs_insert->bindParam(':montant', $montant);
            $rs_insert->bindParam(':compte', $compte);
            $rs_insert->bindParam(':operation', $operation);
            $rs_insert->bindParam(':commentaire', $commentaire);
            $res = $rs_insert->execute();
            return $res;
        } catch (\Exception $e) {
            return -1;
        }
    }



    public function addMouvementCompteAgence($num_transac, $soldeavant, $soldeapres, $montant, $agence, $operation, $commentaire)
    {
        try {
            $date = date("Y-m-d H:i:s");
            $statut = 1;
            $query_insert = "INSERT INTO releve_solde_agence ( num_transac, date_transaction, solde_avant, solde_apres, montant, fk_agence, operation, commentaire) ";
            $query_insert .= " VALUES (:num_transac, :date_transaction, :solde_avant, :solde_apres, :montant, :fk_agence, :operation, :commentaire)";
            $rs_insert = $this->pdo->prepare($query_insert);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':date_transaction', $date);
            $rs_insert->bindParam(':solde_avant', $soldeavant);
            $rs_insert->bindParam(':solde_apres', $soldeapres);
            $rs_insert->bindParam(':montant', $montant);
            $rs_insert->bindParam(':fk_agence', $agence);
            $rs_insert->bindParam(':operation', $operation);
            $rs_insert->bindParam(':commentaire', $commentaire);
            $res = $rs_insert->execute();
            return $res;
        } catch (\Exception $e) {
            return -1;
        }
    }


    //Debiiter compte jula
    public function debiter_compteJula($montant)
    {
        try {
            $sql = "UPDATE compte_jula SET solde=solde-:soldes WHERE rowid = 1";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array("soldes" => intval($montant)));
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $e) {
            return -2;
        }
    }

    public function get_token_id()
    {
        if (isset($_SESSION['token_id'])) {
            return $_SESSION['token_id'];
        } else {
            $token_id = $this->random2(10);
            $_SESSION['token_id'] = $token_id;
            return $token_id;
        }
    }

    public function get_token()
    {
        if (isset($_SESSION['token_value'])) {
            return $_SESSION['token_value'];
        } else {
            $token = hash('sha256', $this->random2(500));
            $_SESSION['token_value'] = $token;
            return $token;
        }

    }

    public function check_valid($method)
    {
        if ($method == 'post' || $method == 'get') {
            $post = $_POST;
            $get = $_GET;
            if (isset(${$method}[$this->get_token_id()]) && (${$method}[$this->get_token_id()] == $this->get_token())) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function random2($len)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $byteLen = intval(($len / 2) + 1);
            $return = substr(bin2hex(openssl_random_pseudo_bytes($byteLen)), 0, $len);
        } elseif (@is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $urandom = fread($f, $len);
            fclose($f);
            $return = '';
        }

        if (empty($return)) {
            for ($i = 0; $i < $len; ++$i) {
                if (!isset($urandom)) {
                    if ($i % 2 == 0) {
                        mt_srand(time() % 2147 * 1000000 + (double)microtime() * 1000000);
                    }
                    $rand = 48 + mt_rand() % 64;
                } else {
                    $rand = 48 + ord($urandom[$i]) % 64;
                }

                if ($rand > 57)
                    $rand += 7;
                if ($rand > 90)
                    $rand += 6;

                if ($rand == 123) $rand = 52;
                if ($rand == 124) $rand = 53;
                $return .= chr($rand);
            }
        }

        return $return;
    }

    /**
     * @param array $array
     * @return array
     */
    public function securite_xss_array(array $array)
    {
        foreach ($array as $key => $value) {
            if (!\is_array($value)) $array[$key] = self::securite_xss($value);
            else self::securite_xss_array($value);
        }
        return $array;
    }

    public function SaveTransactionSupport($num_transac, $service, $montant, $fk_agence, $fkuser, $statut)
    {
        try {
            $datetransaction = date("Y-m-d H:i:s");
            $sql = "INSERT INTO transaction (num_transac, date_transaction, montant, statut, fkuser, fk_service, fk_agence)
				    VALUES (:num_transac, :date_transaction, :montant, :statut, :fkuser, :fk_service, :fk_agence, :fkagence)";
            $user = $this->pdo->prepare($sql);
            $res = $user->execute(array(
                "num_transac" => $num_transac,
                "date_transaction" => $datetransaction,
                "montant" => $montant,
                "statut" => $statut,
                "fkuser" => $fkuser,
                "fk_service" => $service,
                "fk_agence" => $fk_agence
            ));
            //var_dump($res);die;
            if ($res == 1) return 1;
            else return -1;
        } catch (\PDOException $Exception) {
            return $Exception;
        }
    }

    public function getFormatMoney($nombre)
    {
        return @number_format($nombre, 0, ' ', ' ');
    }

    public function getCarteTelephone($string)
    {
        try {
            $sql = "SELECT carte.* FROM carte  WHERE carte.telephone = :num AND carte.statut = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam("num", $string);
            $stmt->execute();
            $employee = $stmt->fetchObject();
            return $employee->rowid;
        } catch (\Exception $e) {
            return "";
        }

    }

    function getMailNotification($type)
    {
        $insertSQL = "SELECT nom, email FROM notifcation_stock WHERE etat = 1 AND type=:type";
        try {
            $dbh = $this->pdo;
            if (is_object($dbh)) {
                $insertlotcarte = $dbh->prepare($insertSQL);
                $insertlotcarte->bindValue('type', $type);
                $insertlotcarte->execute();
                return $insertlotcarte->fetchAll(\PDO::FETCH_OBJ);
            } else {
                return [];
            }
        } catch (\PDOException $e) {
            //echo  $e;
            return [];
            //$res = -1;
        }
    }

    function sendNotification($type, $debut, $fin, $type_carte, $expediteur, $agence, $tab_nom = [])
    {
        //$mail = "papa.ngom@numherit.com";
        if(count($tab_nom) == 0) $tab_nom = $this->getMailNotification($type);

        if(!(count($tab_nom) > 0)) return false;

        if ($type_carte == 1)
            $libtype = 'particuliers';
        else
            $libtype = 'commerçants';
        $nb_carte = intval($fin - $debut + 1);
        $msg = '';
        $sujet = 'Mail erreur';
        if ($type == 1) // Envoi vers Caveau
        {
            $sujet = "MAURIPOST :: Notification envoi lot de carte ";
            $msg =  $expediteur . " vient d'envoyer " . $nb_carte . " cartes " . $libtype . "  MAURIPOST pour reception à l'agence " . $agence . " <br/>";
            $msg .= "Veuillez vous connecter à la plateforme pour receptionner ces cartes. <br/>";
            $msg .= "Merci de votre collaboration. <br/>";
            $msg .= "Cordialement<br/>";
            $msg .= "Equipe MAURIPOST<br/>";
        }
        else if ($type == 2) // Reception  Caveau
        {
            $sujet = "MAURIPOST :: Notification reception lot de carte ";
            $msg =  $expediteur . " d'envoyer " . $nb_carte . " cartes " . $libtype . "  MAURIPOST pour reception à l'agence " . $agence . "  <br/>";
            $msg .= "Veuillez vous connecter à la plateforme pour receptionner ces cartes. <br/>";
            $msg .= "Merci de votre collaboration. <br/>";
            $msg .= "Cordialement<br/>";
            $msg .= "Equipe MAURIPOST<br/>";
        }
        else if ($type == 3) // Envoi  vers Agence
        {
            $sujet = "MAURIPOST :: Notification envoi lot de carte ";
            $msg =  $expediteur . " d'envoyer " . $nb_carte . " cartes " . $libtype . "  MAURIPOST pour reception à l'agence " . $agence . "  <br/>";
            $msg .= "Veuillez vous connecter à la plateforme pour receptionner ces cartes. <br/>";
            $msg .= "Merci de votre collaboration. <br/>";
            $msg .= "Cordialement<br/>";
            $msg .= "Equipe MAURIPOST<br/>";
        }
        else if ($type == 4) // Reception Agence
        {
            $sujet = "MAURIPOST :: Notification reception lot de carte ";
            $msg =  $expediteur . " de receptionner " . $nb_carte . " cartes " . $libtype . "  MAURIPOST pour l'agence " . $agence . "  <br/>";
            $msg .= "Veuillez vous connecter à la plateforme pour suivre l'historique des receptions. <br/>";
            $msg .= "Merci de votre collaboration. <br/>";
            $msg .= "Cordialement<br/>";
            $msg .= "Equipe MAURIPOST<br/>";
        }
        else if ($type == 5) // Retour vers Caveau
        {
            $sujet = "MAURIPOST :: Notification retour lot de carte ";
            $msg =  $expediteur . " vient de retourner " . $nb_carte . " cartes " . $libtype . "  MAURIPOST pour reception à l'agence " . $agence . " <br/>";
            $msg .= "Veuillez vous connecter à la plateforme pour receptionner ces cartes. <br/>";
            $msg .= "Merci de votre collaboration. <br/>";
            $msg .= "Cordialement<br/>";
            $msg .= "Equipe MAURIPOST<br/>";
        }

        foreach ($tab_nom as $item) {
            $vers_mail = $item->email;
            $nom_client = $item->nom;
            $contenue = $msg;
            ob_start();
            include ROOT_FILE.'app/views/compte/tpl-mail.php';
            $message = ob_get_clean();
            /** Envoi du mail **/
            $entete = "Content-type: text/html; charset=utf8\r\n";
            $entete .= "From:  Plateforme MAURIPOST <no-reply@mauripost.mr>\r\n";
            mail($vers_mail, $sujet, $message, $entete);
        }
        return true;
    }

    public function getIntervaleRetour($lot = [], $num_serie_sale = [])
    {
        $retour = [];
        if(count($num_serie_sale) > 0) {
            $i = $lot[0];
            $last = $num_serie_sale[count($num_serie_sale)-1];
            while ($i <= $lot[1]){
                if(count($num_serie_sale) > 0) {
                    if(!in_array($i, $num_serie_sale)){
                        array_push($retour, [$i, ($num_serie_sale[0] - 1)]);
                        $i = $num_serie_sale[0] + 1;
                        unset($num_serie_sale[0]);
                        if(count($num_serie_sale) > 0) $num_serie_sale = array_values($num_serie_sale);
                        else break;
                    }else $i++;
                } else break;
            }
            if($last <= $lot[1]) array_push($retour, [($last + 1), $lot[1]]);
        } else $retour = [$lot];
        return $retour;
    }
    function Generer_numtransactions()
    {
        $found=0;
        $dbh = $this->pdo;
        while ($found==0)
        {
            $code_carte=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
            $colname_rq_code_existe = $code_carte;
            $query_rq_code_existe = $dbh->prepare("SELECT rowid FROM transaction WHERE num_transac ='".$colname_rq_code_existe."'");
            $query_rq_code_existe->execute();
            $totalRows_rq_code_existe = $query_rq_code_existe->rowCount();
            if($totalRows_rq_code_existe==0)
            {
                //CODE GENERER
                $code_generer=$code_carte;
                $found=1;
                break;
            }
        }
        return $code_generer;
    }



    //////////////////////////////////////////////// MTS /////////////////////////////////////////////////////

    public function getTokenMTS(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, URL_MTS."token?id=".SECRETKEY_MTS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


        $headers = array();
        //$headers[] = "Authentication: 521E332F-9B50-4355-B438-54CB60A5BE33:".$token;
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
            return curl_errno($ch);
        }

        curl_close ($ch);
        return $result;
    }

    public function getInfosTransaction($token, $transfertNO){
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, URL_MTS.'transaction?method_name=gettransaction&payeecode=EDK&TransferNO='.$transfertNO);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


        $headers = array();
        $headers[] = 'Authentication: '.SECRETKEY_MTS.':'.$token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
            return curl_errno($ch);
        }

        curl_close ($ch);
        return $result;

    }

    public function payTransaction($token, $transfertNO){


        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        $data = json_encode(array('TransferNO' => $transfertNO));

        curl_setopt($ch, CURLOPT_URL, URL_MTS.'Transaction?method_name=PayTransaction&TransferNO='.$transfertNO);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);


        $headers = array();
        $headers[] = 'Authentication: '.SECRETKEY_MTS.':'.$token;
        $headers[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //echo 'Error:' . curl_error($ch);
            return curl_errno($ch);
        }

        curl_close ($ch);
        return $result;

    }

    public function addMouvementCompteOperation($num_transac, $soldeavant, $soldeapres, $montant, $idcompte, $operation, $commentaire)
    {
        try {
            $date = date("Y-m-d H:i:s");

            $query_insert = "INSERT INTO releve_des_comptes ( num_transac, date_transaction, solde_avant, solde_apres, montant, idcompte, operation, commentaire) ";
            $query_insert .= " VALUES (:num_transac, :date_transaction, :solde_avant, :solde_apres, :montant, :idcompte, :operation, :commentaire)";
            $rs_insert = $this->pdo->prepare($query_insert);
            $rs_insert->bindParam(':num_transac', $num_transac);
            $rs_insert->bindParam(':date_transaction', $date);
            $rs_insert->bindParam(':solde_avant', $soldeavant);
            $rs_insert->bindParam(':solde_apres', $soldeapres);
            $rs_insert->bindParam(':montant', $montant);
            $rs_insert->bindParam(':idcompte', $idcompte);
            $rs_insert->bindParam(':operation', $operation);
            $rs_insert->bindParam(':commentaire', $commentaire);
            $res = $rs_insert->execute();
            return $res;
        }
        catch (\Exception $e) {
            return -1;
        }
    }


    static function sendMailAlert($email,$contenue,$sujet,$nom)
    {
        $destinataire = $email;
        $vers_mail = $destinataire;
        $nom_client = $nom;
        $message ='<html><body></body><table class="table_full editable-bg-color bg_color_e6e6e6 editable-bg-image" bgcolor="#e6e6e6" width="100%" align="center"  mc:repeatable="castellab" mc:variant="Header" cellspacing="0" cellpadding="0" border="0">
   <tr>
      <td>
         <table class="table1 editable-bg-color bg_color_303f9f" bgcolor="#eee" width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
            <tr><td height="25"></td></tr>
            <tr>
               <td>
                  <table class="table1" width="520" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                     <tr>
                        <td>
                           <table width="50%" align="left" border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                 <td align="left">
                                    <a href="#" class="editable-img">
                                       <img src="" style="display:block;height: auto;" width="80" border="0" alt="" />
                                        <!--<img src="https://numherit-labs.com/cdc/assets/plugins/images/admin-cdc-jumbo.png" width="150" height="150" alt="">-->
                                    </a>
                                 </td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     <tr><td height="60"></td></tr>

                     <tr>
                        <td align="center" class="text_color_ffffff" style="color: #ffffff; font-size: 30px; font-weight: 700; font-family: lato, Helvetica, sans-serif; mso-line-height-rule: exactly;">
                           <div class="editable-text">
                              <span class="text_container">
                                 <multiline style="color: #ffbb43">
                                  '.$sujet.'
                                 </multiline>
                              </span>
                           </div>
                        </td>
                     </tr>
                     <tr><td height="30"></td></tr>
                  </table>
               </td>
            </tr>
            <tr><td height="104"></td></tr>
         </table>
      </td>
   </tr>
   <tr>
      <td>
         <table class="table1 editable-bg-color bg_color_ffffff" bgcolor="#ffffff" width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
            <tr><td height="60"></td></tr>
            <tr>
               <td>
                  <table class="table1" width="520" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                     <tr>
                        <td mc:edit="text011" align="left" class="center_content text_color_282828" style="color: #282828; font-size: 20px; font-weight: 700; font-family: lato, Helvetica, sans-serif; mso-line-height-rule: exactly;">
                           <div class="editable-text">
                              <span class="text_container">
                                 <multiline>
                                    Bonjour  cher(e) '.$nom_client.' ,
                                 </multiline>
                              </span>
                           </div>
                        </td>
                     </tr>
                     <tr><td height="10"></td></tr>
                     <tr>
                        <td align="left" class="center_content text_color_a1a2a5" style="color: #a1a2a5; font-size: 14px;line-height: 2; font-weight: 500; font-family: lato, Helvetica, sans-serif; mso-line-height-rule: exactly;">
                           <div class="editable-text" style="line-height: 2;">
                              <span class="text_container">
                                 <multiline>
                                     '.$contenue.'<br/>
                                 </multiline>
                              </span>
                           </div>
                        </td>
                     </tr>
                     <tr><td height="20"></td></tr>
                     <tr>
                        <td align="left" class="center_content text_color_a1a2a5" style="color: #a1a2a5; font-size: 14px;line-height: 2; font-weight: 500; font-family: lato, Helvetica, sans-serif; mso-line-height-rule: exactly;">
                           <div class="editable-text" style="line-height: 2;">
                              <span class="text_container">
                                 <multiline>
                                    Merci
                                 </multiline>
                              </span>
                           </div>
                        </td>
                     </tr>
                     <tr><td height="5"></td></tr>
                     <tr>
                        <td align="left" class="center_content text_color_a1a2a5" style="color: #a1a2a5; font-size: 14px;line-height: 2; font-weight: 500; font-family: lato, Helvetica, sans-serif; mso-line-height-rule: exactly;">
                           <div class="editable-text" style="line-height: 2;">
                              <span class="text_container">
                                 <multiline>
                                 </multiline>
                              </span>
                           </div>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr><td height="60"></td></tr>
         </table>
      </td>
   </tr>
</table></body></html>';

        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= " MIME-Version: 1.0\r\n";
        $entete .= "To: $nom<".$vers_mail."> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        mail($vers_mail, $sujet, $message, $entete);
    }




    /*----------------------------------envoi de mail-------------------------------------*/
    public function envoiMailER($destinataire, $prenom, $nom)
    {
        $sujet = "Rejet création compte Paositra "; //Sujet du mail
        $de_mail = "MAURIPOST";
        $vers_nom = $prenom.' '.$nom;
        $vers_mail = $destinataire;
        $message = "<table width='550px' border='0'>";
        $message.= "<tr>";
        $message.= "<td> Cher ".$vers_nom.", </td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'><p>Votre demande de création compte MAURIPOST a été rejeté.<br />";
        $message.= "<br />";
        $message.= "</p></td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td align='left' valign='top'>Merci .<a href='http://www.numherit.com'> &copy; 2019 By Numherit SA</a></td>";
        $message.= "</tr>";
        $message.= "</table>";
        /** Envoi du mail **/
        $entete = "Content-type: text/html; charset=utf8\r\n";
        $entete .= " MIME-Version: 1.0\r\n";
        $entete .= "To: $vers_nom<".$vers_mail."> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        mail($vers_mail, $sujet, $message, $entete);
    }



    public function envoiCodeDistributeur($destinataire, $email, $code, $responsable)
    {

        $sujet = "Envoi code distributeur"; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> Cher " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>Votre code identifant vient d'être créé.<br />";
        $message .= "<b>Code :</b>" . $code . "<br />";
        $message .= "<b>Responsable : </b>" . $responsable . "<br />";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'>Equipe MAURIPOST vous remercie.</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r";
        return mail($vers_mail, $sujet, $message, $entete);
    }

    public function envoiCodeRechargementDist($dest, $nom, $code, $montant, $code_agence, $langue)
    {
        $de_nom = $langue['plateforme_postecash'];
        $entete = '';
        $vers_nom = $nom;
        $vers_mail = $dest;
        $sujet = $langue['rechargement_carte_dist'];
        $message = "<div align='left'>" . $de_nom . "</div></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $sujet . "</b></b></br>";
        $message .= "</br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_D'] . "</div></br>";
        $message .= "<div align='left'>" . $langue['envoi_mail_code_rechargement_2'] . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_3'] . " </b>: " . $code . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['vo_amount'] . " </b>: " . $this->number_format($montant) . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['code_agence'] . " </b>: " . $code_agence . "</div></br>";
        $message .= "<div align='left'><b>" . $langue['envoi_mail_code_rechargement_4'] . " : </b>" . date('d-m-Y H:i:s') . "</div>";
        $entete .= "Content-type: text/html; charset=utf8\r\n";
        $entete .= "To: $vers_nom <$vers_mail> \r\n";
        $entete .= "From:MAURIPOST <no-reply@mauripost.mr>\r\n";
        $envoi = mail($dest, $sujet, $message, $entete);
        if ($envoi) return 1;
        else return 0;
    }



    public function envoiNotifUpdateEmail($destinataire, $email, $emailOLD, $langue)
    {
        $sujet = $langue['update_email1']; //Sujet du mail
        $vers_nom = $destinataire;
        $vers_mail = $email;
        $entete = '';
        $message = "<table width='550px' border='0'>";
        $message .= "<tr>";
        $message .= "<td> ".$langue['mess_virem_masse1']." " . $destinataire . ", </td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><p>".$langue['update_email2']." ".$email." .<br />";
        $message .= " ".$langue['update_email3']."<br />";
        $message .= " ".$langue['update_email4']." ". $emailOLD . "<br />";
        $message .= " ".$langue['update_email5']." " . $email . "<br />";
        $message .= "<br />";
        $message .= "</p></td>";
        $message .= "</tr>";
        $message .= "<tr>";
        $message .= "<td align='left' valign='top'><br />".$langue['update_email6']."</td>";
        $message .= "</tr>";
        $message .= "</table>";
        /** Envoi du mail **/
        $entete .= "Content-type: text/html; charset=UTF-8\r";
        $entete .= "Content-Transfer-Encoding: 8bit\r";

        $entete .= "To: $vers_nom <> \r\n";
        $entete .= "From:MAURIPOST <no-reply@postecash.mr>\r";
               mail($vers_mail, $sujet, $message, $entete);
        return mail($emailOLD, $sujet, $message, $entete);

    }







}