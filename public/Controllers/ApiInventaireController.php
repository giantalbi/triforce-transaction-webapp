<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;

/**
* @route api/Inventaire/{View}
* @api
*/
class ApiInventaireController extends Controller{
    public function jeu_get(){
        $nom = $this->_uow->sanitize($_POST["Nom"]);
        $consoleID = ($_POST["Console"] == "" ? null : intval($_POST["Console"]));
        $filters = ($nom != "" || $consoleID != null ? array(
            "WHERE" =>  ($nom != "" ? "Nom LIKE '".$nom."%'" : "").
            ($nom != "" && $consoleID != null ? " AND " : "").
            ($consoleID != null ? "ConsoleID='".$consoleID."'" : "")
            ) : array());
        $games = $this->_uow->JeuRepository->get($filters);
        return HttpResponse::success($games);
    }

    public function jeu_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //Validate the data
        if(
            !array_key_exists("JeuID", $_POST) || !is_numeric($_POST["JeuID"])
        ){
            HttpResponse::badRequest();
            return;
        }
        $id = intval($_POST["JeuID"]);
        $exist = $this->_uow->JeuRepository->getByID($id);
        if($exist){
            $this->_uow->JeuRepository->remove($exist);
            return HttpResponse::success();
        }else{
            HttpResposne::notFound();
            return;
        }
    }

    public function jeu_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }

        //Validate the data
        if(
            !array_key_exists("Nom", $_POST) || !is_string($_POST["Nom"]) ||
            !array_key_exists("Console", $_POST) || !is_numeric($_POST["Console"]) ||
            !array_key_exists("Quantity", $_POST) || !is_numeric($_POST["Quantity"]) || intval($_POST["Quantity"]) < 0
        ){
            HttpResponse::badRequest();
            return;
        }
        $nom = $this->_uow->sanitize($_POST["Nom"]);
        $consoleID = intval($_POST["Console"]);
        $qty = intval($_POST["Quantity"]);
        $exists = $this->_uow->JeuRepository->Get(array(
            "WHERE" => "Nom=\"".$nom."\" AND ConsoleID=".$consoleID
        ));
        //Check if the game already exists
        if(count($exists) > 0){
            HttpResponse::badRequest();
            echo "Ce jeu existe déja";
            return;
        }
        $game = new Jeu();
        $game->Nom = $nom;
        $game->ConsoleID = $consoleID;
        $game->Qty = $qty;
        $done = $this->_uow->JeuRepository->insert($game);
        if($done)
        return HttpResponse::success($done);
        else{
            HttpResponse::internalError();
            return;
        }
    }

    public function console_get(){
        $consoleID = (!array_key_exists("ConsoleID", $_POST) ||$_POST["ConsoleID"] == "" ? null : intval($_POST["ConsoleID"]));
        $consoles = $this->_uow->ConsoleRepository->get(
            ($consoleID != null ? array(
                "WHERE" => "ConsoleID=".$consoleID
            ) : array())
        );
        return HttpResponse::success($consoles);
    }

    public function console_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //Validate the data
        if(
            !array_key_exists("ConsoleID", $_POST) || !is_numeric($_POST["ConsoleID"])
        ){
            HttpResponse::badRequest();
            return;
        }
        $id = intval($_POST["ConsoleID"]);
        $exist = $this->_uow->ConsoleRepository->getByID($id);
        if($exist){
            $this->_uow->ConsoleRepository->remove($exist);
            return HttpResponse::success();
        }else{
            HttpResposne::notFound();
            return;
        }
    }

    public function console_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        //Validate the data
        if(
            !array_key_exists("Nom", $_POST) || !is_string($_POST["Nom"]) ||
            !array_key_exists("Quantity", $_POST) || !is_numeric($_POST["Quantity"]) || intval($_POST["Quantity"]) < 0
        ){
            HttpResponse::badRequest();
            return;
        }
        $nom = $this->_uow->sanitize($_POST["Nom"]);
        $qty = intval($_POST["Quantity"]);
        $exists = $this->_uow->JeuRepository->Get(array(
            "WHERE" => "Nom=\"".$nom."\""
        ));
        //Check if the game already exists
        if(count($exists) > 0){
            HttpResponse::badRequest();
            echo "Cette console existe déja";
            return;
        }
        $console = new Console();
        $console->Nom = $nom;
        $console->Qty = $qty;
        $done = $this->_uow->ConsoleRepository->insert($console);
        if($done)
        return HttpResponse::success($done);
        else{
            HttpResponse::internalError();
            return;
        }
    }
}

?>
