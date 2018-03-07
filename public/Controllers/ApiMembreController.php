<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;

/**
* @route api/Membre/{View}
* @api
*/
class ApiMembreController extends Controller{

    public function login(){
        //Validate the data
        if(
            !array_key_exists("Email", $_POST) || !is_string($_POST["Email"]) ||
            !array_key_exists("Password", $_POST) || !is_string($_POST["Password"])
        ){
            HttpResponse::badRequest();
            return;
        }
        //Try to get the member object
        $email = $this->_uow->sanitize($_POST["Email"]);
        $password = $this->_uow->sanitize($_POST["Password"]);
        $membre = $this->_uow->MembreRepository->get(array(
            "WHERE" => "Email=\"".$email."\""
        ));
        //Compare the password hash
        if(count($membre) == 0 || !isset($membre[0]->MotDePasse) || !password_verify($password, $membre[0]->MotDePasse)){
            //The password is incorrect
            HttpResponse::notFound();
            echo "Addresse email ou mot de passe incorrect";
            return;
        }else{
            //Connect the client
            $_SESSION["username"] = $email;
            return HttpResponse::success();
        }
    }

    public function password_reset(){
        //Validate the data
        if(
            !array_key_exists("Email", $_POST) || !is_string($_POST["Email"])
        ){
            HttpResponse::badRequest();
            return;
        }
        //Try to get the member object
        $email = $this->_uow->sanitize($_POST["Email"]);
        $membre = $this->_uow->MembreRepository->getByEmail($email);
        if(!$membre){
            HttpResponse::notFound();
            echo "Addresse email incorrecte";
            return;
        }
        $result = $this->_uow->ConfirmationMdpRepository->createPasswordReset($membre);
        if(!$result){
            HttpResponse::internalError();
            return;
        }
        return HttpResponse::success($membre->Email);
    }

    public function membre_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }

        //Validate the data
        if(
            !array_key_exists("Prenom", $_POST) || !is_string($_POST["Prenom"]) ||
            !array_key_exists("Nom", $_POST) || !is_string($_POST["Nom"]) ||
            !array_key_exists("Email", $_POST) || !is_string($_POST["Email"]) ||
            !array_key_exists("NbSession", $_POST) || !is_string($_POST["NbSession"])
        ){
            HttpResponse::badRequest();
            return;
        }

        $nbSession = intval($_POST["NbSession"]);
        $dateDebut = $this->_uow->sanitize($_POST["DateDebut"]);
        //Create a member object with the sanitized data
        $new_member = new Membre();
        $new_member->Prenom = $this->_uow->sanitize($_POST["Prenom"]);
        $new_member->Nom = $this->_uow->sanitize($_POST["Nom"]);
        $new_member->Email = $this->_uow->sanitize($_POST["Email"]);
        $estCA = isset($_POST["EstCA"]);
        $new_member->EstCA = $estCA;
        $new_member->DateInscription = date("Y-m-d");
        //Check if the member does not exist using the email
        $exist_email = $this->_uow->MembreRepository->Get(array(
            "WHERE" => "Email=\"".$new_member->Email."\""
        ));

        //Send an error if there is already a member with the given email
        if(count($exist_email) == 1 ){
            HttpResponse::badRequest();
            echo "Cette adresse email est déja en utilisation";
            return;
        }
        if($nbSession < 1 || $nbSession > 2){
            HttpResponse::badRequest();
            return;
        }
        //If there are no errors, continue the procedure
        //Add the member to the database then recover his ID for the subscription
        $member = $this->_uow->MembreRepository->insert($new_member);
        if(!$member){
            HttpResponse::internalError();
            echo "Erreur d'insertion du Membre dans la base de donnée";
            return;
        }
        //Create the subscription object
        $add_subscription = new Abonnement();
        $add_subscription->DateDebut = $dateDebut;
        $dateFin = AbonnementRepository::getDateFin($dateDebut, $nbSession);
        $add_subscription->DateFin = $dateFin->format("Y-m-d");
        $add_subscription->NbSession =  $nbSession;
        $add_subscription->MembreID = $member->MembreID;

        //Add the subscription to the database
        $insert_abonnement = $this->_uow->AbonnementRepository->Insert($add_subscription);
        if(!$insert_abonnement){
            HttpResponse::internalError();
            echo "Erreur d'insertion de l'abonnement dans la base de donnée";
            return;
        }
        //Send a password creation email and create the request in the Database
        $new_password = new ConfirmationMdp();
        $new_password->MembreID = $member->MembreID;
        //The hash is the index of the confirmation, it consists of the concat of the email and the date encrypt as MD5
        $new_password->Hash = md5($member->Email.$member->DateInscription.uniqid());
        $new_password->Date = date("Y-m-d");
        $this->_uow->ConfirmationMdpRepository->insert($new_password);

        //Send the email
        $to = $member->Email;
        $subject = "Création / réinitialisation de votre mot de passe.";

        Email::send($to, $subject, Controller::getView("Views/Email/ConfirmationMdp.php", array("Hash"=>$new_password->Hash)),
        "http://".$_SERVER['HTTP_HOST']."/Membre/confirmationMotDePasse/".$new_password->Hash
    );
    return HttpResponse::success();
}

public function membre_edit(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the data
    if(
        !array_key_exists("MembreID", $_POST) || !is_numeric($_POST["MembreID"]) ||
        !array_key_exists("Prenom", $_POST) || !is_string($_POST["Prenom"]) ||
        !array_key_exists("Nom", $_POST) || !is_string($_POST["Nom"]) ||
        !array_key_exists("Email", $_POST) || !is_string($_POST["Email"]) ||
        !array_key_exists("XP", $_POST) || !is_numeric($_POST["XP"])
    ){
        HttpResponse::badRequest();
        return;
    }

    //Create a member object with the sanitized data
    $update_member = new Membre();
    $update_member->MembreID = $this->_uow->sanitize($_POST["MembreID"]);
    $update_member->Prenom = $this->_uow->sanitize($_POST["Prenom"]);
    $update_member->Nom = $this->_uow->sanitize($_POST["Nom"]);
    $update_member->Email = $this->_uow->sanitize($_POST["Email"]);
    $update_member->XP = intval($_POST["XP"]);
    $update_member->EstCA = isset($_POST["EstCA"]);
    $exist = $this->_uow->MembreRepository->getByID($update_member->MembreID);
    if(count($exist) == 1){
        $this->_uow->MembreRepository->Update($update_member, $update_member->MembreID);
        return HttpResponse::success();

    }else{
        HttpResponse::notFound();
        return;
    }
}

public function membre_photo_edit(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate file
    if(
        !array_key_exists("Zoom", $_POST) || !is_numeric($_POST["Zoom"]) ||
        !array_key_exists("Offset-x", $_POST) || !is_numeric($_POST["Offset-x"]) ||
        !array_key_exists("Offset-y", $_POST) || !is_numeric($_POST["Offset-y"]) ||
        !array_key_exists("MembreID", $_POST) || !is_numeric($_POST["MembreID"]) ||
        !array_key_exists("edit_photo_file", $_FILES)
    ){
        HttpResponse::badRequest();
        return;
    }
    $data = file_get_contents($_FILES["edit_photo_file"]["tmp_name"]);
    //Get the parameters of the profile picture
    $zoom = intval($_POST["Zoom"]);
    $x = intval($_POST["Offset-x"]);
    $y = intval($_POST["Offset-y"]);
    $id = intval($_POST["MembreID"]);
    $encoded_string = base64_encode($data);
    $full_photo_string = $encoded_string."|".$x."|".$y."|".$zoom;
    //Get the member and insert the photo string
    $membre = $this->_uow->MembreRepository->getByID($id);
    if($membre){
        $membre->Photo = $full_photo_string;
        $this->_uow->MembreRepository->update($membre, $id);
        return HttpResponse::success($full_photo_string);
    }else{
        HttpResponse::notFound();
        return;
    }
}

public function membre_actif(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $isActive = $this->_uow->MembreRepository->isMemberActive($email);
    return HttpResponse::success($isActive);
}

/**
*AJAX Method: /api/membre_get/
*Type POST
*Data email, [username, password]
*/
public function membre_get(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $membre = $this->_uow->MembreRepository->getByEmail($email);
    if(!is_null($membre))
    return HttpResponse::success($membre);
    else
    HttpResponse::internalError();
}

public function abonnement_get(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $abonnements = $this->_uow->AbonnementRepository->getByEmail($email);
    if(count($abonnements) > 0)
    return HttpResponse::success($abonnements[0]);
    else{
        HttpResponse::notFound();
        return;
    }
}

public function abonnement_add(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the data
    if(
        !array_key_exists("MembreID", $_POST) || !is_numeric($_POST["MembreID"]) ||
        !array_key_exists("NbSession", $_POST) || !is_numeric($_POST["NbSession"]) ||
        !array_key_exists("DateDebut", $_POST) || !is_string($_POST["DateDebut"])
    ){
        HttpResponse::badRequest();
        return;
    }
    $membreID = intval($_POST["MembreID"]);
    $dateDebut = $this->_uow->sanitize($_POST["DateDebut"]);
    $nbSession = intval($_POST["NbSession"]);
    if($nbSession < 1 || $nbSession > 2){
        HttpResponse::badRequest();
        return;
    }

    $membre_actif = $this->_uow->MembreRepository->getByID($membreID);
    //The member must not be active
    if(!is_null($membre_actif)){
        $isActive = $this->_uow->MembreRepository->isMemberActive($membre_actif->Email);
        if($isActive){
            HttpResponse::badRequest();
            return;
        }
        //Create the subscription object
        $add_subscription = new Abonnement();
        $add_subscription->DateDebut = $dateDebut;
        $dateFin = $this->_uow->AbonnementRepository->getDateFin($dateDebut, $nbSession);
        $add_subscription->DateFin = $dateFin->format("Y-m-d");
        $add_subscription->NbSession =  $nbSession;
        $add_subscription->MembreID = $membreID;
        //Check if the member had previous subscription
        //IF it had a 2 session sub, reset the level up
        $previous = $this->_uow->AbonnementRepository->getByEmail($membre_actif->Email);
        if(
            count($previous) >= 1 && $previous[0]->NbSession == 2 ||
            count($previous) >= 1 && $previous[0]->NbSession == 1 && $add_subscription->NbSession != 1 ||
            count($previous) >= 2 && $previous[0]->NbSession == 1 && $previous[1] == 1
        ){
            //Reset the level-up
            $membre_actif->XP = 0;
            $this->_uow->MembreRepository->update($membre_actif, $membre_actif->MembreID);
            $membre_quete = $this->_uow->Membre_QueteRepository->getByMembreID($membre_actif->MembreID);
            $membre_succes = $this->_uow->Membre_SuccesRepository->getByMembreID($membre_actif->MembreID);
            //Delete every achievement and quest
            foreach($membre_quete as $q){
                $this->_uow->Membre_QueteRepository->remove($q);
            }
            foreach($membre_succes as $s){
                $this->_uow->Membre_SuccesRepository->remove($s);
            }
        }
        //Add the subscription to the database
        $success = $this->_uow->AbonnementRepository->Insert($add_subscription);
        return HttpResponse::success($success);
    }else{
        HttpResponse::badRequest();
        return;
    }
}

public function quest_unfinished_get(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $membre = $this->_uow->MembreRepository->getByEmail($email);
    if(is_null($membre)){
        HttpResponse::notFound();
        return;
    }
    $quests = $this->_uow->Membre_QueteRepository->getUnfinishedQuestFromMemberID($membre->MembreID);
    return HttpResponse::success($quests);
}

public function succes_available_get(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $membre = $this->_uow->MembreRepository->getByEmail($email);
    if(is_null($membre)){
        HttpResponse::notFound();
        return;
    }
    $succes = $this->_uow->Membre_SuccesRepository->getAvailableSuccesFromMembreID($membre->MembreID);
    // var_dump($succes);
    return HttpResponse::success($succes);
}

public function succes_get(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the email
    if(!array_key_exists("Email", $_POST) || !is_string($_POST["Email"])){
        HttpResponse::badRequest();
        return;
    }
    $email = $this->_uow->sanitize($_POST["Email"]);
    $membre = $this->_uow->MembreRepository->getByEmail($email);
    if(is_null($membre)){
        HttpResponse::notFound();
        return;
    }
    $succes = $this->_uow->Membre_SuccesRepository->getSuccesFromMemberID($membre->MembreID);
    return HttpResponse::success($succes);
}

public function succes_add(){
    //Check if an admin is connected or given in the data
    if(!$this->_uow->MembreRepository->isAdmin()){
        HttpResponse::unauthorized();
        return;
    }
    //Validate the data
    if(!array_key_exists("Email", $_POST) ||
    !is_string($_POST["Email"]) ||
    !array_key_exists("SuccesID", $_POST) ||
    !is_numeric($_POST["SuccesID"])
){
    HttpResponse::badRequest();
    return;
}
$email = $this->_uow->sanitize($_POST["Email"]);
$id = intval($_POST["SuccesID"]);
$membre = $this->_uow->MembreRepository->getByEmail($email);
$succes = $this->_uow->SuccesRepository->getByID($id);
if(is_null($membre) || is_null($succes)){
    HttpResposne::notFound();
    return;
}
$membre_succes = new Membre_Succes();
$membre_succes->MembreID = $membre->MembreID;
$membre_succes->SuccesID = $id;
$result = $this->_uow->Membre_SuccesRepository->insert($membre_succes);
//Update the membre's XP
$membre->XP += $succes->Recompense;
$result_membre = $this->_uow->MembreRepository->update($membre, $membre->MembreID);

if($result && $result_membre){
    Email::send($membre->Email, "Nouveau succes", Controller::getView("Views/Email/Achievement.php", array(
        "succes" => $succes
    )));
    return HttpResponse::success($succes);
}else{
    HttpResponse::badRequest();
    return;
}
}

public function password_change_confirm(){
    if(!array_key_exists("Password", $_POST) ||
    !is_string($_POST["Password"]) ||
    !array_key_exists("Password_confirm", $_POST) ||
    !is_string($_POST["Password_confirm"]) ||
    !array_key_exists("Email", $_POST) ||
    !is_string($_POST["Email"]) ||
    !is_string($_POST["Hash"]) ||
    !array_key_exists("Hash", $_POST)
){
    HttpResponse::badRequest();
    return;
}
$hash = $this->_uow->sanitize($_POST["Hash"]);
//Check if the given hash exists
$exist = $this->_uow->ConfirmationMdpRepository->get(array(
    "WHERE" => "Hash=\"".$hash."\""
))[0];
if(is_null($exist)){
    HttpResponse::notFound();
    return;
}

//If the user has submited the password and the email
$member = $this->_uow->MembreRepository->getByID($exist->MembreID);
$real_email = $member->Email;
$given_email = $this->_uow->sanitize($_POST["Email"]);
$password = $this->_uow->sanitize($_POST["Password"]);
$password_confirm = $this->_uow->sanitize($_POST["Password_confirm"]);
if($password == $password_confirm && $given_email == $real_email){
    //Check if the password has the minimum requirement
    if(!ConfirmationMdpRepository::is_password_valid($password)){
        HttpResponse::badRequest();
        echo "Le mot de passe doit contenir au moins: huit charactère, une lettre majuscule, un numéro";
        return;
    }
    $member->MotDePasse = password_hash($password, PASSWORD_DEFAULT);
    //Insert the hashed password in the DB
    $this->_uow->MembreRepository->update($member, $exist->MembreID);
    //Clear the session
    unset($_SESSION["username"]);
    //Remove the password request from the DB
    $this->_uow->ConfirmationMdpRepository->remove($exist);
    return HttpResponse::success();
}else{
    HttpResponse::badRequest();
    return;
}
}

public function password_change(){
    //Check if an member is connected
    $isConnected = $this->_uow->MembreRepository->isMemberConnected();
    if(!$isConnected){
        HttpResponse::unauthorized();
        return;
    }
    $member = $this->_uow->MembreRepository->getConnectedMember();

    if(!array_key_exists("Password", $_POST) ||
    !is_string($_POST["Password"])){
        HttpResponse::badRequest();
        return;
    }

    $password = $this->_uow->sanitize($_POST["Password"]);
    if(password_verify($password, $member->MotDePasse)){
        $result = $this->_uow->ConfirmationMdpRepository->createPasswordReset($member);
        if(!$result){
            HttpResponse::internalError();
            return;
        }
        return HttpResponse::success($member->Email);
    }else{
        //If the password is incorrect
        HttpResponse::badRequest();
        return;
    }
}

public function email_edit(){
    //Check if an member is connected or given in the data
    $isConnected = $this->_uow->MembreRepository->isMemberConnected();
    if(!$isConnected){
        HttpResponse::unauthorized();
        return;
    }
    $member = $this->_uow->MembreRepository->getConnectedMember();

    //Check the password
    $password = $this->_uow->sanitize($_POST["Password"]);
    if(password_verify($password, $member->MotDePasse)){
        $new_email = new confirmationEmail();
        $new_email->MembreID = $member->MembreID;
        $new_email->Email = $this->_uow->sanitize($_POST["Email"]);
        $new_email->Hash = md5($member->Email.uniqid());
        $new_email->Date = date("Y-m-d");
        //Delete any older email change request
        $exists = $this->_uow->ConfirmationEmailRepository->getByMembreID($member->MembreID);
        foreach($exists as $c){
            $this->_uow->ConfirmationEmailRepository->remove($c);
        }
        $this->_uow->ConfirmationEmailRepository->insert($new_email);
        //Send a email containing the change adress
        //Send the email
        $to = $member->Email;
        $subject = "Changement d'adresse email";
        $message = Controller::getView("Views/Email/ConfirmationEmail.php", array("Hash"=>$new_email->Hash));
        Email::send($to, $subject, $message);
        return HttpResponse::success($to);
    }else{
        //If the password is incorrect
        HttpResponse::badRequest();
        return;
    }
}

}

?>
