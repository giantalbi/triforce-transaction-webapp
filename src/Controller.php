<?php
namespace OgreWeb\Lib;
//use OgreWeb\DAL\UnitOfWork;

abstract class Controller{
    public $_uow;
    public function __construct(){
        // if(class_exists($pModel))
        //     $this->_model = $pModel;
        // else
        //     throw new Exception("Model: ".$pModel." does not exist!");
        $this->_uow = new UnitOfWork();
    }

    //Returns the content of the view's file, execute the code inside a the
    //output of a buffer
    public static function getView($path, $data = null){
        $publicPath = getcwd() . '//public//';
        $viewPath = $publicPath . $path;
        if(file_exists($viewPath)){
            //Start the output buffer
            ob_start();
            //Load the requested view and inserted the data if they exists
            include_once($viewPath);
            //Close the buffer and insert the view and its data inside the variable
            $page = ob_get_clean();
            return $page;
        }
    }

    public static function redirectToView($pPath){
        header("Location: ".$pPath);
        exit();
    }

}

?>
