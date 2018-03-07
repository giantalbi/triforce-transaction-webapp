<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
use OgreWeb\Lib\UnitOfWork;

class Membre_SuccesRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Membre_Succes", $pContext);
    }

    public function getByMembreID($id){
        $result = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        return $result;
    }

    public function getAvailableSuccesFromMembreID($id){
        $uow = new UnitOfWork();
        $succes = $uow->SuccesRepository->get();
        $membre_succes = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));

        $available = array();
        //For each succes, check if the user already got it OR if it not limited
        foreach ($succes as $key => $value) {
            $exist = false;
            foreach($membre_succes as $s){
                if($s->SuccesID == $value->SuccesID)
                $exist = true;
            }

            if(!$exist || $value->Limite == 0)
            array_push($available, $value);
        }
        return $available;
    }

    public function getSuccesFromMemberID($id){
        $uow = new UnitOfWork();
        $result = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        $succesMembre = array();
        foreach ($result as $key => $value) {
            $s = $uow->SuccesRepository->get(array(
                "WHERE"=> "SuccesID=".$value->SuccesID
            ));
            array_push($succesMembre, $s);
        }

        return $succesMembre;
    }
}

?>
