<?php
namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;

class ArticleRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Article", $pContext);
    }
}
