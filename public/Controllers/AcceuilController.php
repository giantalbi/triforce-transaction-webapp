<?php

namespace OgreWeb\Controllers;

use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
class AcceuilController extends Controller
{
    public function __construct(){
        //Call the parent Controller with the data's model
        parent::__construct();
    }

    public function index($param = 1){
        $page = $param;
        //Get the max number of pages
        $max_article = 5;
        $page_offset = ($page - 1)*$max_article;
        //Get the articles list and the author name
        $articles = $this->_uow->ArticleRepository->get(array(
            "ORDER BY" => "ArticleID DESC",
            "LIMIT" => $max_article,
            "OFFSET" => $page_offset
        ));
        $all_articles = $this->_uow->ArticleRepository->get();
        $nb_page = ceil(count($all_articles)/$max_article);

        //Validate the current page
        if($nb_page > 0  && $page > $nb_page)
        parent::redirectToView("/Acceuil/Index");

        $article_array = array();
        $author_name = "";
        foreach ($articles as $key => $value) {
            //Get the author name
            if(!is_null($value->MembreID)){
                $author = $this->_uow->MembreRepository->get(array(
                    "WHERE" => "MembreID=".$value->MembreID
                ))[0];
                $author_name = $author->Prenom." ".$author->Nom;
            }
            else
            $author_name = "Anonyme";

            $article = array("article" => $value, "author" => $author_name);
            array_push($article_array, $article);
        }
        //Get the top 5 for the leaderboard
        $classement = $this->_uow->MembreRepository->getClassementLevelUp();
        $membres_data = array();
        foreach($classement as $m){
            $membre_data = array(
                "membre" => $m,
                "niveau_index" => $this->_uow->NiveauRepository->getNiveauIndexByXP($m->XP)
            );
            array_push($membres_data, $membre_data);
        }
        //Return the member object if the connected member is CA
        $connected_member = $this->_uow->MembreRepository->getConnectedMember();
        return Controller::getView("Views/Acceuil/index.php", array(
            "articles" => $article_array,
            "classement" => $classement,
            "nbPage" => $nb_page,
            "page" => $page,
            "membres_data" => $membres_data,
            "membre_data" => ($connected_member ? array(
                "membre" => $connected_member,
                "isActive" => $this->_uow->MembreRepository->isMemberActive($connected_member->Email)
            ) : false)
        ));
    }

    public function info(){
        return Controller::getView("Views/Acceuil/info.php");
    }

    public function disconnect(){
        //Clear the user's session then return to the home index
        $_SESSION = array();
        session_destroy();
        parent::redirectToView("/Acceuil/Index");
    }

    public function connection(){
        //If a user is already connected, go back to index
        if(isset($_SESSION["username"]))
        parent::redirectToView("/Acceuil/Index");

        return Controller::getView("Views/Acceuil/connection.php");
    }

    public function oublie(){
        //If a user is already connected, go back to index
        if(isset($_SESSION["username"]))
        parent::redirectToView("/Acceuil/Index");

        return Controller::getView("Views/Acceuil/oublie.php");
    }
}
?>
