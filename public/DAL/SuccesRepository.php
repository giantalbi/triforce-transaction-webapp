<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class SuccesRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Succes", $pContext);
    }
}

?>
