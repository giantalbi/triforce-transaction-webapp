<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class Transaction_productRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Transaction_product", $pContext);
    }
}
