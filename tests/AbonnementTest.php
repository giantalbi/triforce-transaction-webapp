<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OgreWeb\Lib\Repository;
use OgreWeb\DAL\AbonnementRepository;

final class AbonnementTest extends TestCase{
    public function testFinAbonnement() : void{
        $fins = array(
            //Winter
            array(AbonnementRepository::getDateFin("2018-05-01", 1), "2018-08-01"),
            array(AbonnementRepository::getDateFin("2018-02-01", 2), "2019-01-01"),
            //Summer
            array(AbonnementRepository::getDateFin("2018-08-25", 1), "2019-01-01"),
            array(AbonnementRepository::getDateFin("2018-08-25", 2), "2019-08-01")
        );
        
        foreach($fins as $fin){
            $this->assertEquals($fin[0], date_create($fin[1])); 
        }
    }
}
