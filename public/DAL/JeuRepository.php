<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class JeuRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Jeu", $pContext);
    }
}

?>
