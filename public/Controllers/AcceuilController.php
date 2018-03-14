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

    public function index(){

        //Get the articles list and the author name
        /*
        $articles = $this->_uow->ArticleRepository->get(array(
            "ORDER BY" => "ArticleID DESC",
            "LIMIT" => $max_article,
            "OFFSET" => $page_offset
        ));
        */

        $products = $this->_uow->ProductRepository->get();

        return Controller::getView("Views/Acceuil/index.php", array(
            "products" => $products
        ));
    }

}
?>
