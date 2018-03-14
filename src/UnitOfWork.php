<?php
namespace OgreWeb\Lib;
use OgreWeb\Lib\Database;

class UnitOfWork{
    //Connection with the database
    private $_db;
    private $_repositories = array();

    public function __construct(){
        $this->_db = new Database($GLOBALS['config']['db_hostname'], $GLOBALS['config']['db_username'], $GLOBALS['config']['db_password'], $GLOBALS['config']['db_name']);
    }
    public function sanitize($str){
        $str = $this->_db->escape($str);
        return $str;
    }
    // Magic function that returns the asked property
    public function __get($name){
        $fullClassName = 'OgreWeb\\DAL\\' . $name;
        if(!array_key_exists($name, $this->_repositories) && class_exists($fullClassName))
            $this->_repositories[$name] = new $fullClassName($this->_db);
        return $this->_repositories[$name];
    }
}

?>
