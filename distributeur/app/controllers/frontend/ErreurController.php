<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 30/03/2017
 * Time: 18:08
 */

class ErreurController extends \app\core\FrontendController
{

    /*
     * Permet de construire les models de l'element
     */

    public function __construct()
    {
        parent::__construct('utilisateur');
    }

    /******* Action liste action ****/
    public function index1()
    {
        $data['lang'] =  $this->lang->getLangFile($this->session->getAttribut('lang'));
        $paramsview = array('view' => sprintf('frontend/error') );
        $this->view($paramsview, $data);
    }




}