<?php
namespace OgreWeb\Lib;
use Exception;

abstract class Repository{

    private $_context;
    private $_model;
    private $_modelClassName;

    public function __construct($pModel, $pContext){
        //Validation: Make sure that the given context is a Database object
        if(get_class($pContext) != "OgreWeb\\Lib\\Database"){
            throw new Exception("The context must by a Database type");
        }
        //Get the model class
        /*
        if(!file_exists("Models/".$pModel.".php"))
            throw new Exception("The model ".$pModel." does not exists!");
        require_once("Models/".$pModel.".php");
        */
        if(!class_exists("OgreWeb\\Models\\" . $pModel))
            throw new Exception("The model ".$pModel." does not exists!");
        $this->_modelClassName = 'OgreWeb\\Models\\' . $pModel;
        $this->_model = $pModel;
        $this->_context = $pContext;
    }

    //TODO: Implements JOIN
    public function get($pQuery = array()){

        //If the SELECT key is not defined, set the SELECT as '*'
        $query_columns = array_key_exists('SELECT', $pQuery) ? $pQuery['SELECT']:'*';

        //Get the query's statement from the param
        $query_from = array_key_exists('FROM', $pQuery) ? $pQuery['FROM'] : $this->_model;
        $query_where = array_key_exists('WHERE', $pQuery) ? $pQuery['WHERE'] : "";
        $query_order = array_key_exists('ORDER BY', $pQuery) ? $pQuery['ORDER BY'] : "";
        $query_limit = array_key_exists('LIMIT', $pQuery) ? $pQuery['LIMIT'] : "";
        $query_offset = array_key_exists('OFFSET', $pQuery) ? $pQuery['OFFSET'] : "";

        //Send the query to the database and store the result in a n array
        $result = $this->_context->query_r(
            "SELECT ".$query_columns.
            " FROM ".$query_from.
            ($query_where == "" ? "": " WHERE ".$query_where).
            ($query_order == "" ? "": " ORDER BY ".$query_order).
            ($query_limit == "" ? "": " LIMIT ".$query_limit).
            ($query_offset == "" ? "": " OFFSET ".$query_offset)
        );

        //Create an array of entity from the query results
        $entity_array = array();
        foreach ($result as $obj) {
            $entity = new $this->_modelClassName();
            foreach ($obj as $key => $value)
            $entity->{$key} = $value;
            array_push($entity_array, $entity);
        }
        return $entity_array;
    }

    //Return the object assigned to the ID of the model
    public function getByID($id){
        // var_dump($this->lastInsertID());
        // die();
        $entity = $this->get(array(
            "SELECT" => "*",
            "FROM" => $this->_model,
            "WHERE" => $this->_model."ID=".$id
        ));

        return (isset($entity[0]) ? $entity[0] : null);
    }


    //Insert the given object in the database
    public function insert($pObj){
        //Validate the model
        $this->validateModel($pObj);
        $model_properties = get_class_vars($this->_modelClassName);
        $model_properties_str = "";
        $model_properties_values_str = "";
        foreach($model_properties as $key => $value){
            //Check if last key
            $last = array_search($key, array_keys($model_properties))+1 == count($model_properties);
            //Don't include the ID
            if(strpos($key, $this->_model."ID") === false){
                //Get the object's properties name
                $model_properties_str .= $key.($last ? "": ", ");
                $value = $pObj->{$key};

                //Get the object's properties value and escape the string
                if(is_numeric($value))
                $value_escape = $value;
                else if(is_bool($value))
                $value_escape = ($value ? "1" : "0");
                else
                $value_escape = isset($pObj->{$key}) && $pObj->{$key} != "" ? $this->_context->escape($pObj->{$key}) : null;
                $model_properties_values_str .= (!is_null($value_escape) ? "\"".$value_escape.($last ? "\"": "\", ") : "null".($last ? "": ", "));
            }
        }

        //Execute the query
        $success = $this->_context->query_cud(
            "INSERT INTO ".$this->_model."(".$model_properties_str.") ".
            "VALUES (".$model_properties_values_str.")");
            //Set the ID to the obj
            $p_key = $this->_model."ID";
            $pObj->$p_key = $this->_context->lastInsertID();

            //Return the success result
            return $success ? $pObj : false;
        }

        //Update an object in the databse based on the object's ID
        public function update($pObj, $id){
            //Validate the model
            $this->validateModel($pObj);

            //Check if the item exist
            if(isset($id)){
                $current_entity = $this->getByID($id);
                //Update the entity if it exists in the database
                if(isset($current_entity)){

                    //Parse the data from the new object using the model's properties
                    $model_properties = get_class_vars($this->_modelClassName);
                    $model_properties_str = "";
                    foreach($model_properties as $key => $value){
                        //Check if last key
                        $last = array_search($key, array_keys($model_properties))+1 == count($model_properties);
                        $value = $pObj->{$key};
                        //Get the object's properties name and value and escape the string
                        if(is_numeric($value))
                            $value_escape = $value;
                        else if(is_bool($value))
                            $value_escape = ($value ? "1" : "0");
                        else
                            $value_escape = $this->_context->escape($current_entity->{$key});
                        $model_properties_str .= $key."="."'".$value_escape."'".($last ? "": " AND ");
                    }

                    //Get the property containing the ID
                    $new_model_properties = get_class_vars($this->_modelClassName);
                    $new_model_properties_str = "";
                    //$new_model_properties_values_str = "";
                    foreach($new_model_properties as $key => $value){
                        //Check if last key
                        $last = array_search($key, array_keys($new_model_properties)) + 1 == count($new_model_properties);
                        //Don't include the ID
                        if(strpos($key, $this->_model."ID") === false){
                            //Get the object's properties names and value and escape the string
                            if(is_bool($pObj->{$key}))
                            $new_value_escape = ($pObj->{$key} ? "1" : "0");
                            else
                            $new_value_escape = $this->_context->escape($pObj->{$key});

                            if(is_bool($current_entity->{$key}))
                            $current_value_escape = ($current_entity->{$key} ? "1" : "0");
                            else
                            $current_value_escape = $this->_context->escape($current_entity->{$key});

                            //Check if the new value is different from the current value, if so, add to the query
                            if(!is_null($pObj->{$key}) && isset($pObj->{$key}) && $pObj->{$key} != $current_entity->{$key})
                            $new_model_properties_str .= $key."="."'".$new_value_escape."'".($last ? "": ", ");
                            else
                            $new_model_properties_str .= $key."="."'".$current_value_escape."'".($last ? "": ", ");
                        }
                    }

                    $primary_key = $this->_model."ID";

                    //Parse the data as an update query
                    $update_query = "UPDATE ".$this->_model." SET ".$new_model_properties_str.
                    " WHERE ".$primary_key."=".$id;
                    //Execute the query
                    $success = $this->_context->query_cud($update_query);
                    return ($success ? $success : false);
                }else //Return false if the original record can't be found
                return false;
            }
        }

        public function remove($pObj){
            //Validate the model
            $this->validateModel($pObj);

            //Execute the query
            $id = $this->_model."ID";
            $this->_context->query_cud(
                "DELETE FROM ".$this->_model.
                " WHERE ".$this->_model."ID = ".$pObj->$id);
            }

            //Compare the give object's model to the Repository's model
            public function validateModel($pObj){
                if(get_class($pObj) != $this->_modelClassName)
                throw new Exception("The given object does not match the model, Given: ".get_class($pObj)." Expected: ".$this->_model);
            }

            public function createByForm(Array $pForm){
                //Sanitize the values
                $pForm = $this->sanitize($pForm);
                $obj = new $this->_model();
                foreach (get_class_vars($this->_model) as $key => $value) {
                    if(array_key_exists($key, $pForm)){

                        $obj->{$key} = $pForm[$key];
                    }
                }
                return $obj;
            }

            public function sanitize($array){
                //For each value in the array, execute the character escape method
                foreach ($array as $key => $value) {
                    $array[$key] = $this->_context->escape($array[$key]);
                }
                //Return the sanitized array
                return $array;
            }
        }

        ?>
