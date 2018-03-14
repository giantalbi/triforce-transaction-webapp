<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;
class TransactionRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Transaction", $pContext);
    }
}
