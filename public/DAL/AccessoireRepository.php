<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class AccessoireRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Accessoire", $pContext);
    }
}

?>
