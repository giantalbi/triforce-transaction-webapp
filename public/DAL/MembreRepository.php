<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
use OgreWeb\Lib\UnitOfWork;

class MembreRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Membre", $pContext);
    }

    public function getClassementLevelUp(){
        $all = $this->get(array(
            "ORDER BY" => "XP DESC"
        ));
        //Get only the active members
        $active = array();
        foreach($all as $m){
            if($this->isMemberActive($m->Email))
            array_push($active, $m);
        }
        return $active;
    }

    public function getByEmail($email){
        $exist = $this->get(array(
            "WHERE"=> "Email='".$email."'"
        ));
        if(count($exist) > 0)
        return $exist[0];
    }

    public function getConnectedMember(){
        if(!isset($_SESSION["username"]))
        return false;
        $membre = $this->get(array(
            "WHERE" => "Email=\"".$_SESSION["username"]."\""
        ))[0];
        return $membre;
    }

    public function isAdmin(){
        //Check if the session contains an user
        if(isset($_SESSION["username"])){
            //If a user is connected
            $membre = $this->getConnectedMember();
            if($membre === false)
            return;
            return ($membre->EstCA);
        }
    }

    public function isMemberActive($email){
        $_uow = new UnitOfWork();
        $member = $this->getByEmail($email);
        if($member){
            //Try to find a valid subscription for the user
            $abonnement = $_uow->AbonnementRepository->Get(array(
                "WHERE" => "MembreID=".$member->MembreID." AND ".
                "\"".date("Y-m-d")." \" BETWEEN DateDebut AND DateFin"
            ));
            if(count($abonnement) > 0)
            return true;
        }else
        return false;
    }

    public function isMemberConnected(){
        //Check if the session contains an user
        if(isset($_SESSION["username"])){
            //If a user is connected
            $membre = $this->getConnectedMember();
            //Check true only if the username is valid
            return $membre->Email == $_SESSION["username"];
        }
        //The session does not contain a username
        return false;
    }
}

?>
