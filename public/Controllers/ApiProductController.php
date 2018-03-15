<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
use OgreWeb\Models\Product;

/**
* @route api/Product/{View}
* @api
*/
class ApiProductController extends Controller{

    public function create(){
        //Validate the data
        if(
            !array_key_exists("Name", $_POST) || !is_string($_POST["Name"]) ||
            !array_key_exists("Price", $_POST) || !is_numeric($_POST["Price"])
        ){
            HttpResponse::badRequest();
            return;
        }
        
        $name = $this->_uow->sanitize($_POST["Name"]);
        $price = $this->_uow->sanitize($_POST["Price"]);

        // Check if the product already exists
        $exist = $this->_uow->ProductRepository->get(array(
            "WHERE" => "Name='".$name."'"
        ));

        if(count($exist) > 0){
            HttpResponse::badRequest();
            echo "Le produit ".$name." existe déja.";
            return;
        }

        $new_product = new Product();
        $new_product->Name = $name;
        $price = number_format($price, 2);
        $new_product->Price = str_replace( ',', '.', $price);
        $new_product->Deleted = false;
        $product = $this->_uow->ProductRepository->insert($new_product);
        if(!$product){
            HttpResponse::internalError();
            return;
        }
        return HttpResponse::success();
    }

    public function delete(){
        //Validate the data
        if(
            !array_key_exists("id", $_POST) || !is_numeric($_POST["id"])
        ){
            HttpResponse::badRequest();
            return;
        }
        
        $id = $this->_uow->sanitize($_POST["id"]);

        // Check if the product already exists
        $exist = $this->_uow->ProductRepository->get(array(
            "WHERE" => "ProductID='".$id."'"
        ))[0];
        if(!$exist){
            HttpResponse::badRequest();
            return;
        }
        $exist->Deleted = true;

        $this->_uow->ProductRepository->update($exist, $exist->ProductID);

        return HttpResponse::success();
    }

    public function get(){
        return HttpResponse::success($this->_uow->ProductRepository->get());
    }

}

?>
