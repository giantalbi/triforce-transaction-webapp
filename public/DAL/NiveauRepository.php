<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class NiveauRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Niveau", $pContext);
    }

    public function getNiveauNumberByXP($xp){
        if(!is_numeric($xp))
        return false;
        $levels = $this->get(array(
            "ORDER BY" => "XP DESC"
        ));
        $lvl_index = 0;
        foreach($levels as $index => $value){
            if($xp > $value->XP)
            $lvl_index++;
        }

        return $lvl_index;
    }

    public function getNiveauIndexByXP($xp){
        if(!is_numeric($xp))
        return false;
        $levels = $this->get(array(
            "ORDER BY" => "XP DESC"
        ));
        $lvl_index = 0;
        foreach($levels as $index => $value){
            if($xp >= $value->XP)
            $lvl_index++;
        }
        return $lvl_index;
    }

    public function getByXP($xp){
        if(!is_numeric($xp))
        return false;
        $levels = $this->get(array(
            "ORDER BY" => "XP ASC"
        ));
        $lvl_index = 0;
        foreach($levels as $index => $value){
            if($xp > $value->XP)
            $lvl_index++;
        }

        return $levels[$lvl_index];
    }

    public function getByName($name){
        if(!is_string($name))
        throw new Exception("The parameter must be a string!");
        $level = $this->get(array(
            "WHERE" => "Nom='".$name."'"
        ));
        return $level[0];
    }
}

?>
