<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
use OgreWeb\Lib\UnitOfWork;

class Membre_QueteRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Membre_Quete", $pContext);
    }

    public function getUnfinishedQuestFromMemberID($id){
        $uow = new UnitOfWork();
        $result = $this->get(array(
            "WHERE" => "MembreID=".$id." AND EstComplete=0"
        ));
        $quetes = array();
        foreach($result as $value){
            $quete = $uow->QueteRepository->get(array(
                "WHERE"=>"QueteID=".$value->QueteID
            ))[0];
            array_push($quetes, $quete);
        }

        return $quetes;
    }

    public function getByMembreID($id){
        $result = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        return $result;
    }

    public function getQuestFromMemberID($id){
        $uow = new UnitOfWork();
        $result = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        $quetes = array();
        foreach($result as $value){
            $quete = $uow->QueteRepository->get(array(
                "WHERE"=>"QueteID=".$value->QueteID
            ))[0];
            array_push($quetes, $quete);
        }

        return $quetes;
    }

    public function getQuestByID($id){
        $result = $this->get(array(
            "WHERE" => "QueteID=".$id
        ));
        return (count($result) > 0 ? $result[0] : null);
    }
}

?>
