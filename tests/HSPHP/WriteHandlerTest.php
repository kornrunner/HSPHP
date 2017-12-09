<?php

namespace HSPHP;
use PHPUnit\Framework\TestCase;

class WriteHandlerTest extends TestCase
{
    public function testInsert()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(0,count($t->select('=',100500)));	// no data with 100500 key

        $t->insert(['k'=>100500,'v'=>'test\nvalue']);
        $this->assertEquals([['k' => 100500, 'v' => 'test\nvalue']],$t->select('=',100500));
    }

    /**
     * @depends testInsert
     */
    function testUpdate()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(1,$t->update('=',['k'=>100500],['k'=>100500,'v'=>42]));
        $this->assertEquals([['k' => 100500, 'v' => '42']],$t->select('=',100500));
    }

    /**
     * @depends testUpdate
     */
    function testDelete()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(1,$t->delete('=',['k'=>100500]));
    }
}
