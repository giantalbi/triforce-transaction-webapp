<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class ConsoleRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Console", $pContext);
    }
}

?>
