<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
class InventaireController extends Controller{

    public function __construct(){
        //Call the parent Controller with the data's model
        parent::__construct();
    }

    public function index(){
        //Get the filters
        $nom = (isset($_GET["query_nom"]) ? $this->_uow->sanitize($_GET["query_nom"]) : "");
        $consoleID = (isset($_GET["query_console"]) ? $this->_uow->sanitize($_GET["query_console"]) : "");

        $consoles = $this->_uow->ConsoleRepository->Get();
        $jeux = $this->_uow->JeuRepository->Get(array(
            "WHERE" =>
            ($nom == "" ? "" : "Nom LIKE \"".$nom."%\"").
            ($consoleID == "" ? "" : ($nom == "" ? "" : " AND ")."ConsoleID=".$consoleID)
        ));
        $member = $this->_uow->MembreRepository->getConnectedMember();
        return Controller::getView("Views/Inventaire/index.php", array(
            "jeux" => $jeux,
            "consoles" => $consoles,
            "membre" => $member,
            "game_error" => (isset($error_msg) ? $error_msg : null)
        ));
    }
}
?>
