<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
/**
* Controller use to store different AJAX function for manipulation
* @route api/LevelUp/{view}
* @api
*/
class ApiLevelUpController extends Controller{

    public function quest_finish(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        $id = intval($_POST["QueteID"]);
        $email = $this->_uow->sanitize($_POST["Email"]);
        $member = $this->_uow->MembreRepository->getByEmail($email);
        if(is_null($member)){
            HttpResponse::notFound();
            return;
        }
        $finish_quest = $this->_uow->Membre_QueteRepository->get(array(
            "WHERE"=> "QueteID=".$id." AND MembreID=".$member->MembreID
        ))[0];
        if(is_null($finish_quest)){
            HttpResponse::notFound();
            return;
        }
        $finish_quest->EstComplete = 1;
        $this->_uow->Membre_QueteRepository->update($finish_quest, intval($finish_quest->Membre_QueteID));
        $quete = $this->_uow->QueteRepository->getByID($id);

        //Add XP
        $member->XP += $quete->Recompense;
        $this->_uow->MembreRepository->update($member, $member->MembreID);
        Email::send($member->Email, "Quête terminée", Controller::getView("Views/Email/QuestFinish.php", array(
            "quete" => $quete
        )));

        return HttpResponse::success();
    }

    /**
    *AJAX Method: /api/quest_abandon/
    *Type POST
    *Data QueteID, [username, password]
    */
    public function quest_abandon(){
        //Check if an member is connected or given in the data
        $isConnected = $this->_uow->MembreRepository->isMemberConnected();
        if(!$isConnected){
            HttpResponse::unauthorized();
            return;
        }
        $member = $this->_uow->MembreRepository->getConnectedMember();

        //If no SuccesID is present and validate it
        if(!array_key_exists("QueteID", $_POST) ||
        !is_numeric($_POST["QueteID"])){
            HttpResponse::badRequest();
            return;
        }
        //If the request is valid
        $id = intval($_POST["QueteID"]);
        $membre_quete = $this->_uow->Membre_QueteRepository->getQuestByID($id);
        $quete = $this->_uow->QueteRepository->getByID($id);
        if($membre_quete != null && $quete != null){
            $this->_uow->Membre_QueteRepository->remove($membre_quete);
            return json_encode($quete);
        }
        else{
            HttpResponse::notFound();
        }
    }

    /**
    *AJAX Method: /api/quest_join/
    *Type POST
    *Data QueteID, [username, password]
    */
    public function quest_join(){
        //Check if an member is connected or given in the data
        $isConnected = $this->_uow->MembreRepository->isMemberConnected();
        if(!$isConnected){
            HttpResponse::unauthorized();
            return;
        }
        $member = $this->_uow->MembreRepository->getConnectedMember();

        //If no SuccesID is present and validate it
        if(!array_key_exists("QueteID", $_POST) ||
        !is_numeric($_POST["QueteID"])){
            HttpResponse::badRequest();
            return;
        }
        //If the request is valid
        $id = intval($_POST["QueteID"]);
        $membre_quete = $this->_uow->Membre_QueteRepository->get(array(
            "WHERE" => "QueteID=".$id. " AND MembreID=".$member->MembreID
        ));
        $quete = $this->_uow->QueteRepository->getByID($id);
        if($membre_quete[0] == null && $quete != null){
            $membre_quete = new Membre_Quete();
            $membre_quete->MembreID = $member->MembreID;
            $membre_quete->QueteID = $id;
            $this->_uow->Membre_QueteRepository->insert($membre_quete);
            return HttpResponse::success($quete);
        }
        else{
            HttpResponse::internalError();
            echo json_encode($membre_quete);
            echo json_encode($quete);
        }
    }

    /**
    *AJAX Method: /api/quest_remove/
    *Type POST
    *Data QueteID, [username, password]
    */
    public function quest_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //If no SuccesID is present and validate it
        if(!array_key_exists("QueteID", $_POST) ||
        !is_numeric($_POST["QueteID"])){
            HttpResponse::badRequest();
            return;
        }
        //If the request is valid
        $id = intval($_POST["QueteID"]);
        $quete = $this->_uow->QueteRepository->getByID($id);
        if($quete != null){
            $this->_uow->QueteRepository->remove($quete);
            return HttpResponse::success();
        }
        else{
            HttpResponse::notFound();
        }
    }

    /**
    *AJAX Method: /api/quest_create/
    *Type POST
    *Data QueteID, [username, password]
    */
    public function quest_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }

        if( !array_key_exists("Nom", $_POST) ||
        !array_key_exists("Recompense", $_POST) ||
        !array_key_exists("Description", $_POST) ||
        !is_numeric($_POST["Recompense"]) ||
        !is_string($_POST["Nom"]) ||
        !is_string($_POST["Description"]) ||
        !(array_key_exists("Description", $_POST) && is_string($_POST["DateLimite"])) ){
            HttpResponse::badRequest();
            return;
        }
        //If the request is valid
        $quete = $this->_uow->QueteRepository->createByForm($_POST);
        //Check if the quest does not exist
        $exist = $this->_uow->QueteRepository->get(array(
            "WHERE" => "Nom='".$quete->Nom."' AND Description='".$quete->Description.
            "' AND Recompense='".$quete->Recompense."'"
        ));
        if($quete != null && count($exist) == 0){
            $this->_uow->QueteRepository->insert($quete);
            $new_quete = $this->_uow->QueteRepository->get(array(
                "WHERE" => "Nom='".$quete->Nom."' AND Description='".$quete->Description.
                "' AND Recompense='".$quete->Recompense."'"
            ));
            return HttpResponse::success($new_quete);
        }
        else{
            HttpResponse::internalError();
        }
    }



    /**
    *AJAX Method: /api/succes_create/
    *Type POST
    *Data SuccesID, [username, password]
    */
    public function succes_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }

        if( !array_key_exists("Nom", $_POST) ||
        !array_key_exists("Recompense", $_POST) ||
        !array_key_exists("Description", $_POST) ||
        !is_numeric($_POST["Recompense"]) ||
        !is_string($_POST["Nom"]) ||
        !is_string($_POST["Description"])){
            HttpResponse::badRequest();
            return;
        }
        //If the limite is present, convert as bool

        $_POST["Limite"] = (array_key_exists("Limite", $_POST) ? 1 : 0);

        //If the request is valid
        $succes = $this->_uow->SuccesRepository->createByForm($_POST);
        //Check if the quest does not exist
        $exist = $this->_uow->SuccesRepository->get(array(
            "WHERE" => "Nom='".$succes->Nom."' AND Description='".$succes->Description.
            "' AND Recompense='".$succes->Recompense."'"
        ));
        if($succes != null && count($exist) == 0){
            $new_succes = $this->_uow->SuccesRepository->insert($succes);
            return HttpResponse::success($new_succes);
        }
        else{
            HttpResponse::internalError();
        }
    }

    /**
    *AJAX Method: /api/succes_remove/
    *Type POST
    *Data QueteID, [username, password]
    */
    public function succes_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //If no SuccesID is present and validate it
        if(!array_key_exists("SuccesID", $_POST) ||
        !is_numeric($_POST["SuccesID"])){
            HttpResponse::badRequest();
            return;
        }
        //If the request is valid
        $id = intval($_POST["SuccesID"]);
        $succes = $this->_uow->SuccesRepository->getByID($id);
        if($succes){
            //TEMP FIX, remove every succes relation by code (needs a db trigger)
            $succes_membres = $this->_uow->Membre_SuccesRepository->get(array(
                "WHERE" => "SuccesID=".$id
            ));
            foreach($succes_membres as $sm)
            $this->_uow->Membre_SuccesRepository->remove($sm);

            //Remove the succes
            $this->_uow->SuccesRepository->remove($succes);
            return HttpResponse::success();
        }
        else{
            HttpResponse::notFound();
        }
    }

    /**
    *AJAX Method: /api/level_remove/
    *Type POST
    *Data Nom, [username, password]
    */
    public function level_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //Check If no name is present and validate it
        if(!array_key_exists("Nom", $_POST) ||
        !is_string($_POST["Nom"])){
            HttpResponse::badRequest();
            return;
        }

        //If the request is valid
        $name = $this->_uow->sanitize($_POST["Nom"]);
        $level = $this->_uow->NiveauRepository->getByName($name);
        if($level != null)
        $this->_uow->NiveauRepository->remove($level);
        else{
            HttpResponse::notFound();
        }

    }

    /**
    *AJAX Method: /api/level_create/
    *Type POST
    *Data Nom, [username, password]
    */
    public function level_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //Check If no name is present and validate it
        if(!array_key_exists("Nom", $_POST) ||
        !is_string($_POST["Nom"])){
            $this->_badRequest();
            return;
        }
        //If the request is valid
        $name = $this->_uow->sanitize($_POST["Nom"]);
        $level = $this->_uow->NiveauRepository->createByForm($_POST);
        $exist = $this->_uow->NiveauRepository->get(array(
            "WHERE" => "Nom='".$level->Nom."'"
        ));
        if($exist > 0){
            $this->_uow->NiveauRepository->insert($level);
            return HttpResponse::success($level);
        }
        else{
            HttpResponse::internalError();
        }
    }
}

?>
