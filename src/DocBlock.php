<?php

namespace OgreWeb\Lib;
use ReflectionClass;
class DocBlock{
    /**
    * Get the docblock from a class
    * @return string
    */
    static function getClassDoc($className){
        $reflect = new ReflectionClass($className);
        $comment = $reflect->getDocComment();
        return $comment;
    }

    /**
    * Gets every attributes from the doclbock
    * @return array Array containing an array of values for each attributes
    */
    static function getClassAttributes($className){
        $attributes = array();
        $doc = self::getClassDoc($className);
        $lines = explode("*", $doc);

        foreach($lines as $value){
            //Check if the line contains the symbol @
            if(substr_count($value, "@") > 0){
                //Split the line by @ if there is more than one attribute per line
                $attr_split = preg_split("/\s*@/", $value);
                $attr_split = array_values(preg_grep("/[\S]+/", $attr_split));
                foreach($attr_split as $full_attr){
                    //Split every word by a blankspace
                    $split = preg_split("/[\s]+/", $full_attr);
                    //Filters to make sure every entry is a word
                    $split = array_values(preg_grep("/[\S]+/", $split));
                    $attr = $split[0];
                    $attr_values = array_slice($split, 1);
                    if(!array_key_exists($attr, $attributes)){
                        $attributes[$attr] = array();
                    }
                    foreach($attr_values as $value){
                        array_push($attributes[$attr], $value);
                    }
                }
            }
        }
        //Return the finale list of attributes
        return $attributes;
    }

    /**
    * Check if a class contains an API tag
    * @param $className Name of the class to check
    * @return boolean
    */
    static function isApiClass($className){
        $attributes = self::getClassAttributes($className);

        //Check if the class has an API attribute
        foreach($attributes as $key => $value){
            if(strtolower($key) == "api")
            return true;
        }
        //Return false when no API attribute
        return false;
    }

}

?>
