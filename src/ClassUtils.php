<?php
namespace OgreWeb\Lib;
use Exception;

class ClassUtils{
    static function getDeclaredNameSpaces(){
        //Parse the composer.json
        if(!file_exists('composer.json'))
            throw new Exception('Unable to find composer.json');
        $composerFile = file_get_contents('composer.json');
        $composer = (array)json_decode($composerFile);
        return ((array)($composer['autoload']->{'psr-4'}));
    }

    static function getAllFullyQualifiedClassNname(){
        $declaredNamespace = self::getDeclaredNameSpaces();
        $fullNames = array();
        //For every namespace, get the FQCN from the files
        foreach($declaredNamespace as $namespace => $dir){
            //echo 'Namespace in ' . $dir . '<br>';
            $fullPath = getcwd() . '//' . $dir;
            self::_getFullyQualifiedClassNameInDirectory($fullPath, $fullNames);
            //var_dump($fullNames);
        }
        return $fullNames;
    }

    private static function _getFullyQualifiedClassNameInDirectory($dir, &$fullNamesArray){
        $dirContent = scandir($dir);
        for($i = 0; $i < count($dirContent); $i++){
            if($dirContent[$i] != '.' && $dirContent[$i] != '..'){
                $contentDir = $dir . '//' . $dirContent[$i];
                //Check if it's a directory of a file
                if(is_dir($contentDir)){
                    self::_getFullyQualifiedClassNameInDirectory($contentDir, $fullNamesArray);
                }else if(is_file($contentDir)){
                    //Check if it's a PHP file
                    $fileInfo = pathinfo($contentDir);
                    if(strtolower($fileInfo['extension']) == 'php'){
                        $fullName = self::getFullClassNameFromFile($contentDir);
                        if($fullName)
                            array_push($fullNamesArray, $fullName);
                    }
                }
            }
        }
    }

    static function getFullClassNameFromFile($pFilePath){
        if(!file_exists($pFilePath))
            throw new Exception("Unable to find " . $pFilePath);
        $file = file_get_contents($pFilePath);
        $tokens = token_get_all($file);
        $count = count($tokens);
        $namespace = '';
        $lineNamespace;
        $class = '';

        for($i = 0; $i < $count; $i++){
            $token = $tokens[$i];

            //Check for the class
            if (is_array($token) && $token[0] === T_CLASS && $class == '') {
                $class = $tokens[$i + 2][1];
            }
            //Check for the namespace
            if (is_array($token) && $token[0] === T_NAMESPACE && !isset($lineNamespace)) {
                $lineNamespace = $token[2];
            }else if(isset($lineNamespace) && is_array($token) && $token[2] == $lineNamespace){
                $namespace .= $token[1];
            }
        }
        if($class == '' || $namespace == '')
            return false;
        //Parse the full qualified class name and add // between
        $nameSpaceSplit = explode('\\', $namespace);
        $fullName = '';
        foreach($nameSpaceSplit as $key => $value){
            $fullName .= trim($value) . '\\';
        }
        $fullName .= trim($class);
        return $fullName;
    }
}
