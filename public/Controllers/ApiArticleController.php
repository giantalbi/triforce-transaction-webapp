<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
/**
* @route api/Article/{View}
* @api
*/
class ApiArticleController extends Controller{

    public function article_create(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        $member = $this->_uow->MembreRepository->getConnectedMember();

        //Validate the data
        if(!array_key_exists("Content", $_POST) ||
        !is_string($_POST["Content"]) ||
        !array_key_exists("Titre", $_POST) ||
        !is_string($_POST["Titre"])){
            HttpResponse::badRequest();
            return;
        }

        $article = new Article();
        $article->Contenu = $this->_uow->sanitize($_POST["Content"]);
        $article->Titre = $this->_uow->sanitize($_POST["Titre"]);
        $current_date = getdate();
        $date = date("Y-m-d", strtotime($current_date["year"].'-'.$current_date["month"].'-'.$current_date["mday"]));
        $article->Date = $date;
        $article->MembreID = $member->MembreID;
        $success = $this->_uow->ArticleRepository->insert($article);
        if($success)
        return HttpResponse::success($success);
        else{
            HttpResponse::internalError();
            return;
        }
    }

    public function article_remove(){
        //Check if an admin is connected or given in the data
        if(!$this->_uow->MembreRepository->isAdmin()){
            HttpResponse::unauthorized();
            return;
        }
        $member = $this->_uow->MembreRepository->getConnectedMember();
        //Validate the ID
        if(!array_key_exists("ArticleID", $_POST) || !is_numeric($_POST["ArticleID"])){
            HttpResponse::badRequest();
            return;
        }
        $articleID = intval($_POST["ArticleID"]);
        $article = $this->_uow->ArticleRepository->getByID($articleID);
        if(!$article){
            HttpResponse::notFound();
            return;
        }
        $this->_uow->ArticleRepository->remove($article);
        return HttpResponse::success();
    }

}

?>
