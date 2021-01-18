<?php
/**
 * Created by PhpStorm.
 * User: madiop.gueye
 * Date: 27/02/2017
 * Time: 16:03
 */


require_once __DIR__.'/Participant.class.php';

class ParticipantModel extends \app\core\BaseModel
{


    /**
     * Fonction pour lister des utilisateurs
     */
        public function test()
        {

            $sql  = " SELECT participant.*  ";
            $sql .= " FROM participant  ";


            try {

                $st = $this->getConnexion()->prepare($sql);
                $st->execute();
                $object = $st->fetchAll();
                $this->closeConnexion();

                if ($object != null) {
                    $lesutilisateurs = [];

                    foreach ($object as $obj) {
                        $lesutilisateurs [] = new Participant($obj);
                    }

                    return $lesutilisateurs;
                } else {
                    return null;
                }

            }
             catch (\PDOException $e) {
                return $e->getMessage();
            }


        }







}