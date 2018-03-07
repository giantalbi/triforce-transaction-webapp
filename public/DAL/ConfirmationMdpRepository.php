<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class ConfirmationMdpRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("ConfirmationMdp", $pContext);
    }

    public function getByMembreID($id){
        $results = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        return $results;
    }

    public function createPasswordReset($member){
        //Send a password creation email and create the request in the Database
        $new_password = new ConfirmationMdp();
        $new_password->MembreID = $member->MembreID;
        //The hash is the index of the confirmation, it consists of the concat of the email and the date encrypt as MD5
        $new_password->Hash = md5($member->Email.$member->DateInscription.uniqid());
        $new_password->Date = date("Y-m-d");
        //Check if the user recieved other password change, if so, delete all the previous ones
        $exists = $this->getByMembreID($member->MembreID);
        foreach($exists as $c){
            $this->remove($c);
        }

        $result = $this->Insert($new_password);
        if(!$result)
        return false;

        //Send the email
        $to = $member->Email;
        $subject = "Création / réinitialisation de votre mot de passe.";
        Email::send($to, $subject, Controller::getView("Views/Email/ConfirmationMdp.php", array("Hash"=>$new_password->Hash)),
        "http://".$_SERVER['HTTP_HOST']."/Membre/confirmationMotDePasse/".$new_password->Hash
    );

    return $result;
}

static function is_password_valid($password){
    //Minimum 8 chars
    $length = strlen($password);
    //At least one capital letter
    $lettre_maj = preg_match_all("/[A-Z]/", $password);
    //At least 1 number
    $numbers = preg_match_all("/[0-9]/", $password);
    //At least 1 special character
    // $special = preg_match_all("/[^a-zA-Z0-9]/", $password);
    return (
        $length >= 8 &&
        $lettre_maj >= 1 &&
        $numbers >= 1
        // && $special >= 1
    );
}
}

?>
