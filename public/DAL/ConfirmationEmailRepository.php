<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class ConfirmationEmailRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("ConfirmationEmail", $pContext);
    }

    public function getByMembreID(int $id){
        $results = $this->get(array(
            "WHERE" => "MembreID=".$id
        ));
        return $results;
    }
}

?>
