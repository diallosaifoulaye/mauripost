<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */

date_default_timezone_set('Africa/Dakar');

class LoginController extends \app\core\FrontendController
{

    private $connexion;

    public function __construct()
    {
        parent::__construct('utilisateur');
        $this->connexion = new \app\core\Connexion();

    }

    public function accueil($id)
    {

        $data['lang'] = $this->lang->getLangFile($id[0]);
        $paramsview = array('view' => sprintf('frontend/index'));
        $this->view($paramsview, $data);
    }

    public function index1($id)
    {
        $data['lang'] = $this->lang->getLangFile($id[0]);
        $paramsview = array('view' => sprintf('frontend/index'));
        $this->view($paramsview, $data);
    }

    public function badEntries($id)
    {
        $data['lang'] = $this->lang->getLangFile($id[0]);
        $data['err'] = 1;
        $paramsview = array('view' => sprintf('frontend/index'));
        $this->view($paramsview, $data);
    }

    public function authentification()
    {
        $login = $this->utils->securite_xss($_POST['login']);
        $pass = $this->utils->securite_xss($_POST['password']);
        $obj = $this->element->connecter($login, $pass);

        if (is_object($obj) && $obj !== null) {

            $this->getSession()->setAttribut('objconnect', $obj);

            if ( intval($obj->getConnect()) == 0) {
                $this->rediriger('login', 'editerPass');
            }
            else {
                if ( intval($obj->getSuperviseur()) == 1) {
                    $this->rediriger('accueil', 'index');
                }
                else {
                    $this->rediriger('accueil', 'dashbord');
                }
            }

        } else {
            $this->rediriger('login', 'badEntries');
        }
    }

    private function checkEmailLogin($email, $login)
    {
        $sql = "SELECT rowid FROM user WHERE email=:email AND login=:login";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("email" => $email, "login" => $login));
            $result = $stmt->fetchObject();
            $this->connexion->closeConnexion();
            if ($result > 0) return 1;
            else return 0;
        } catch (Exception $e) {
            return -1;
        }

    }

    private function saveAndSendNewPass($email, $login, $pass)
    {
        $sql = "UPDATE user SET password=:password WHERE email=:email AND login=:login";
        $password = md5($pass . "AZVERTI@RE2015");
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute(array("password" => $password, "email" => $email, "login" => $login));
            $operation = $stmt->rowCount() > 0 ? true : false;
            $this->connexion->closeConnexion();

            $data['lang'] = $this->lang->getLangFile($this->session->getAttribut('lang'));

            $sujet = $data['lang']['change_pass_txt3'];

            $msg = $data['lang']['change_pass_txt2'];

            $vers_mail = $email;
            $message = "<table width='550px' border='0'>";
            $message .= "<tr>";
            $message .= "</tr>";
            $message .= "<tr>";
            $message .= "<td align='left' valign='top'><p>" . $msg . "<br />" . $data['lang']['change_pass_new'] . " : <b>" . $pass . "</b><br />";

            $message .= "</p></td>";
            $message .= "</tr>";

            $message .= "</table>";
            $entete = "Content-type: text/html; charset=utf8\r\n";
            $entete .= "From: POSTECASH <no-reply@postecash.com>\r\n";
            mail($vers_mail, $sujet, $message, $entete);

            return $operation;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function forgottenPass()
    {
        $email = $this->utils->securite_xss($_POST['email']);
        $login = $this->utils->securite_xss($_POST['login_recover']);

        $checkEmailLogin = $this->checkEmailLogin($email, $login);
        if ($checkEmailLogin == 1) {
            $new_pass = $this->utils->generation_code();
            $saveNewPass = $this->saveAndSendNewPass($email, $login, $new_pass);
            if ($saveNewPass == true) {
                $this->rediriger('login', 'logout');
            }
        } else {
            $this->rediriger('login', 'badEntries');
        }
    }

    public function editerPass()
    {
        $data['lang'] = $this->lang->getLangFile($this->session->getAttribut('lang'));

        $paramsview = array('view' => sprintf('frontend/changepassword'));
        $this->view($paramsview, $data);
    }

    public function verifPass()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_id = $obj->getRowid();


        $password = $this->utils->securite_xss($_POST['mdp']);
        $password = sha1('NUMH'.$password);


        $sql = "SELECT user.password as pass FROM user WHERE user.rowid='" . $user_id . "' ";
        try {
            $stmt = $this->connexion->getConnexion()->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetchObject();
            $this->connexion->closeConnexion();

            if ($user->pass === $password) echo 1;
            else echo -2;

        } catch (Exception $e) {
            echo -1;
        }

    }

    public function modifierPass()
    {
        $obj = $this->getSession()->getAttribut('objconnect');
        $user_id = $obj->getRowid();
        $nom = $obj->getNom();
        $prenom = $obj->getPrenom();
        $agence = $obj->getFk_agence();
        $password = $this->utils->securite_xss($_POST['new']);

        $password = sha1('NUMH'.$password);

        $sql = "UPDATE user SET password=:password, connect=1 WHERE rowid=:user";
        try {
            $st = $this->connexion->getConnexion()->prepare($sql);
            $st->execute(array("password" => $password, "user" => $user_id));
            $operation = $st->rowCount() > 0 ? true : false;
            $this->connexion->closeConnexion();

            if ($operation == true) {

                $data['lang'] = $this->lang->getLangFile($this->session->getAttribut('lang'));

                $this->utils->log_journal('Modification mot de passe', 'Utilisateur:' . $nom . ' ' . $prenom . ' Agence:' . $agence, 'succes', 1, $user_id);
                $this->rediriger('login', 'logout');
            } else {
                $this->rediriger('login', 'badEntries');
            }

        } catch (\PDOException $e) {
            return $e->getMessage();
        }

    }


}