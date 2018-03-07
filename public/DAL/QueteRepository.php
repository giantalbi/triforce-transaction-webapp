<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class QueteRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Quete", $pContext);
    }
}

?>
