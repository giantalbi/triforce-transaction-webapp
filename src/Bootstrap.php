<?php

namespace OgreWeb\Lib;
use ReflectionClass;

class Bootstrap{
    static function start(){
        //Start a new session or resume it.
        session_start();

        //Get the default values
        require_once('config/default.php');

        //Load the config as GLOBALS
        $GLOBALS['config'] = ConfigLoader::get();

        //Check if any class is related with the given route
        $class_custom_route = Route::classWithRoute(Route::getRoute());
        $route = ($class_custom_route ? Route::classCustomRoute($class_custom_route) : $GLOBALS["default_route"]);

        //Get the values of the given parameters in the URL
        // Exemple URL: http://localhost/Controller/View/Parameter
        $controllerName = ($class_custom_route ? $class_custom_route : (Route::getRouteParamValue("Controller", $route) ?? $GLOBALS["default_controller"]));
        $controllerClassName = ($class_custom_route ? $controllerName : 'OgreWeb\\Controllers\\' . $controllerName ."Controller");

        $viewName = Route::getRouteParamValue("View", $route);
        if(!$viewName)
            $viewName = $GLOBALS["default_view"];
        //Get the requested controller
        if(class_exists($controllerClassName)){
            $controller = new $controllerClassName;
            $reflect = new ReflectionClass($controllerClassName);
            if(!$reflect->hasMethod($viewName))
                General::page_not_found();
            $viewMethod = $reflect->getMethod($viewName);
            $parameters = array();

            foreach($viewMethod->getParameters() as $param){
                $value = Route::getRouteParamValue($param->getName(), $route);
                if($value)
                    array_push($parameters, $value);
                else{
                    //TODO: Badrequest
                }
            }
            $partial_content = $viewMethod->invokeArgs($controller, $parameters);
        }else
            General::page_not_found();

        //If the request is AJAX or comes from the API, dont include the layout to only return the AJAX response's data
        if(Docblock::isApiClass($controllerClassName) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //Return only the AJAX response
            echo $partial_content;
        }else{
            //Load the layout containing the partial view
            include_once("public/Views/Shared/_layout.php");
        }
    }
}
