<?php

namespace OgreWeb\Lib;

class ConfigLoader{

    private static $_cache = array();

    public static function get($name = 'config', $force = false){
        //Check if the file has already been loaded, bypass if force is true
        if(!$force && array_key_exists($name, self::$_cache))
            return $_cache[$name];

        $config = array();
        $cwd = getcwd();
        $configPath = $cwd . '/config/';
        if(!file_exists($configPath . $name . '.ini')){
            //If not, check if a 'default' file exists, if so generate the file out of the default
            if(file_exists($configPath . $name . '-default.ini')){
                //Copy the config file
                $copySuccess = copy($configPath . $name . '-default.ini', $configPath . $name . '.ini');
                if(!$copySuccess)
                    return false;
            }else
                return false;
        }

        $config = parse_ini_file($configPath . $name . '.ini');
        if($config)
            $_cache[$name] = $config;
        return $config;
    }
}
?>
