<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
use OgreWeb\Models\Product;
use OgreWeb\Models\Transaction;
use OgreWeb\Models\Transaction_product;

/**
 * @route api/Transaction/{View}
 * @api
 */
class ApiTransactionController extends Controller{

    public function create(){
        //Validate the data
        if(
            !array_key_exists("total", $_POST) || !is_string($_POST["total"]) ||
            !array_key_exists("products", $_POST) || !is_array($_POST["products"]) ||
            array_key_exists("products", $_POST) && is_array($_POST['products']) &&
            count($_POST['products']) == 0
        ){
            HttpResponse::badRequest();
            return;
        }

        $products = $_POST['products'];
        $total = $this->_uow->sanitize($_POST['total']);

        $new_transaction = new Transaction();
        $new_transaction->Total = $total;
        $new_transaction->Timestamp = date('Y-m-d H:i:s');

        $transaction = $this->_uow->TransactionRepository->insert($new_transaction);
        if(!$transaction){
            HttpResponse::internalError();
            return;
        }
        
        foreach($_POST['products'] as $id => $qty){
            $new_transaction_produit = new Transaction_product();
            $new_transaction_produit->TransactionID = $transaction->TransactionID;
            $new_transaction_produit->ProductID = $id;
            $new_transaction_produit->Qty = $qty;
            $transaction_product = $this->_uow->Transaction_productRepository->insert($new_transaction_produit);
            if(!$transaction_product){
                HttpResponse::internalError();
                return;
            }
        }

        return HttpResponse::success();
    }

    public function get(){
        return HttpResponse::success($this->_uow->TransactionRepository->get());
    }

}

?>
