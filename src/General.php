<?php

namespace OgreWeb\Lib;
//Class containing general purpose static function that can be use between libraries
class General{
    static function require_directory($path){
        $full_path = getcwd().$path;
        if(file_exists($full_path)){
            $file_array = scandir($full_path);
            foreach ($file_array as $file) {
                //Check if the file's path contains ".php" in his name
                if(substr_count($file, ".php") > 0){
                    require($full_path."/".$file);
                }
            }
        }
    }

    static function require_directory_once($path){
        $full_path = getcwd().$path;
        if(file_exists($full_path)){
            $file_array = scandir($full_path);
            foreach ($file_array as $file) {
                //Check if the file's path contains ".php" in his name
                if(substr_count($file, ".php") > 0){
                    require_once($full_path."/".$file);
                }
            }
        }
    }

    static function page_not_found(){
        header("HTTP/1.0 404 Not Found");
        include_once(getcwd()."/public/Views/Shared/404.php");
        exit();
    }

    static function array_obj_property_where($property, $property_value, $array){
        foreach ($array as $value) {
            if($value->$property == $property_value)
            return $value;
        }
        return null;
    }

    static function array_obj_property_where_first($property, $property_value, $array){
        $result = array();
        foreach ($array as $value) {
            if($value->$property == $property_value)
            array_push($result, $value);
        }
        return $result;
    }

    static function array_obj_property_exist($property, $property_value, $array){
        foreach ($array as $value) {
            if($value->$property == $property_value)
            return true;
        }
        return false;
    }


}

?>
