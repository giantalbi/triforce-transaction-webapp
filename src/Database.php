<?php
namespace OgreWeb\Lib;
use mysqli;

class Database{
    private $_mysqli;
    private $_server;
    private $_user;
    private $_password;
    private $_database;
    private $_insert_id;

    public function __construct($pServer, $pUser, $pPassword, $pDatabase){
        $this->_server = $pServer;
        $this->_user = $pUser;
        $this->_password = $pPassword;
        $this->_database = $pDatabase;
    }

    public function connect(){
        $this->_mysqli = new mysqli($this->_server, $this->_user, $this->_password, $this->_database, 3306);
        if ($this->_mysqli->connect_error){
            echo "Echec lors de la connexion à MySQL : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
    }

    public function escape($str){
        if(!isset($this->_mysqli))
            $this->connect();
        $new_str = "";
        if(is_string($str))
            $str = $this->_mysqli->real_escape_string($str);
        $this->close();

        return $str;
    }

    //Query for CREATE / UPDATE / DELETE ONLY
    function query_cud($pQuery){
        if(!isset($this->_mysqli))
            $this->connect();
        //Do the data manipulation
        $success = ($this->_mysqli->query($pQuery));

        //Set the last insert ID
        $this->_insert_id = $this->_mysqli->insert_id;
        //Close the connection
        $this->close();
        //Return if the data manipulation succeded
        return $success;
    }

    //Query for READ ONLY
    function query_r($pQuery){
        if(!isset($this->_mysqli))
        $this->connect();

        $result = $this->_mysqli->query($pQuery);
        print_r($this->_mysqli->error);
        $data = array();
        if($result){
            while ($row = $result->fetch_assoc()) {
                array_push($data, $row);
            }
        }
        //Closse the connection
        $this->close();
        return $data;
    }

    public function lastInsertID(){
        return $this->_insert_id ?? null;
    }

    public function close(){
        $this->_mysqli->close();
        unset($this->_mysqli);
    }
}
?>
