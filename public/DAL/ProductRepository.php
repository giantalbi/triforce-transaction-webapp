<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class ProductRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Product", $pContext);
    }
}
