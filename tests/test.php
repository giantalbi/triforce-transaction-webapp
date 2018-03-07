<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThingTest extends TestCase{
    public function testFunction():void{
        $this->assertEquals("1", "1");
    }
}
