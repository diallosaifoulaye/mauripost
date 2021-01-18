<?php

/**
 * Created by IntelliJ IDEA.
 * User: khalil
 * Date: 15/02/2017
 * Time: 21:11
 */


class AccueilController extends \app\core\BaseController
{
    private $userModel;


    public function __construct()
    {
        parent::__construct();
        $this->getSession()->est_Connecter('OBJECT_CONNECTION');

    }

    public function accueil()
    {


        $data['lang'] =  $this->lang->getLangFile($this->getSession()->getAttribut('lang'));


        $params = array('view' => 'accueil');


        $this->view($params, $data);
    }





}