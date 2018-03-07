<?php
namespace OgreWeb\Lib;
class Route{
    /**
    * Get the current URL route
    * @return string
    */
    static function getRoute(){
        return (isset($_GET["params"]) ? $_GET["params"] : $GLOBALS["default_controller"]."/".$GLOBALS["default_view"]);
    }

    /**
    * If a class contains a DocBlock witht the specified route, returns the name of it
    * @param $route Custom route to search for
    * @return The name of the class with the specified route, or false if none
    */
    static function classWithRoute($route){
        //$classes = get_declared_classes();
        $classes = ClassUtils::getAllFullyQualifiedClassNname();
        $route_split = explode("/", $route);
        foreach($classes as $key => $class){
            $classRoute = self::classCustomRoute($class);
            if($classRoute){
                $isMatch = true;
                //Compare every string and param between '/'
                $class_route_split = explode("/", $classRoute);
                foreach($class_route_split as $key => $str){
                    $isParam = preg_match("/\{(.*?)\}/", $str);
                    //Not match if the current string is NOT a param AND does NOT match the route
                    if(!$isParam && strtolower($str) != strtolower($route_split[$key]))
                        $isMatch = false;
                }
                if($isMatch)
                    return $class;
            }
        }
        return false;
    }

    /**
    * Returns the custom route specified in the class's docblock
    * @return string
    */
    static function classCustomRoute($className){
        $attributes = DocBlock::getClassAttributes($className);
        return (array_key_exists("route", $attributes) ? $attributes["route"][0] : false);
    }

    /**
    * Get the value of a parameter from the custom route in the current entered route
    * @param $paramName Name of the param to find in the route
    * @param $route The route definition Ex: {Controller}/{View}/{Param}
    * @return string
    */
    static function getRouteParamValue($paramName, $route){
        $url_split = explode("/", self::getRoute());
        //Check if the route definition contains the given param
        $split = explode("/", $route);

        //Get the index of the parameter from the split
        foreach($split as $key => $value){
            if(strtolower($value) == "{".strtolower($paramName)."}"){
                //Check if the URL has the key
                if(!array_key_exists($key, $url_split))
                return false;
                //Return the value from the current URL split
                return $url_split[$key];
            }
        }
        return false;
    }

    /**
    * Returns the called controller from the URL
    * @return string
    */
    static function getController(){
        $params = isset($_GET['params']) ? explode('/', $_GET['params']) : NULL;
        $controllerName = (($params != null) && array_key_exists(0, $params) && $params[0] != "") ? $params[0]."Controller" : $GLOBALS["default_controller"]."Controller";
        return $controllerName;
    }

    /**
    * Returns the called view name from the URL
    * @return string
    */
    static function getViewName(){
        $params = isset($_GET['params']) ? explode('/', $_GET['params']) : NULL;
        $viewName = (($params != null) && array_key_exists(1, $params) && $params[1] != "") ? strtolower($params[1]) : $GLOBALS["default_view"];
        return $viewName;
    }

    /**
    * Returns the called parameter from the URL
    * @return string
    */
    static function getViewParameter(){
        $params = isset($_GET['params']) ? explode('/', $_GET['params']) : NULL;
        $viewParameter = (($params != null) && array_key_exists(2, $params) && $params[2] != "") ? $params[2] : null;
        return $viewParameter;
    }

}

?>
